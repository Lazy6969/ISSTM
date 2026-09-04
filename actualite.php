<?php
include_once 'language.php';
$page_title = t('actualites_page_titre');
include 'header.php';

// --- Newsletter : traitement de l'inscription ---
$newsletter_msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    $email = trim($_POST['newsletter_email']);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $mysqli->prepare("INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $newsletter_msg = ['type' => 'success', 'text' => 'Merci ! Votre inscription à la newsletter est confirmée.'];
        $stmt->close();
    } else {
        $newsletter_msg = ['type' => 'error', 'text' => 'Veuillez saisir une adresse email valide.'];
    }
}

// --- Catégories avec compteur d'articles publiés ---
$categories = [];
$catResult = $mysqli->query("
    SELECT c.*, COUNT(a.id) AS article_count
    FROM news_categories c
    LEFT JOIN news_articles a ON a.category_id = c.id AND a.status = 'publie' AND (a.published_at IS NULL OR a.published_at <= NOW())
    GROUP BY c.id
    ORDER BY c.display_order ASC
");
if ($catResult) {
    while ($row = $catResult->fetch_assoc()) { $categories[] = $row; }
}

// --- Années disponibles ---
$years = [];
$yearResult = $mysqli->query("SELECT DISTINCT YEAR(published_at) AS y FROM news_articles WHERE status = 'publie' AND published_at IS NOT NULL AND published_at <= NOW() ORDER BY y DESC");
if ($yearResult) {
    while ($row = $yearResult->fetch_assoc()) { $years[] = (int) $row['y']; }
}

// --- Filtres ---
$filter_cat   = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$filter_year  = isset($_GET['year']) && ctype_digit($_GET['year']) ? (int) $_GET['year'] : 0;
$filter_month = isset($_GET['month']) && ctype_digit($_GET['month']) && (int) $_GET['month'] >= 1 && (int) $_GET['month'] <= 12 ? (int) $_GET['month'] : 0;
$filter_q     = isset($_GET['q']) ? trim($_GET['q']) : '';
$page         = isset($_GET['page']) && ctype_digit($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$per_page     = 9;

$mois_labels = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
    7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
];

$base_where = "a.status = 'publie' AND (a.published_at IS NULL OR a.published_at <= NOW())";
$where = [$base_where];
$params = [];
$types = '';
if ($filter_cat !== '') { $where[] = "c.slug = ?"; $params[] = $filter_cat; $types .= 's'; }
if ($filter_year > 0) { $where[] = "YEAR(a.published_at) = ?"; $params[] = $filter_year; $types .= 'i'; }
if ($filter_month > 0) { $where[] = "MONTH(a.published_at) = ?"; $params[] = $filter_month; $types .= 'i'; }
if ($filter_q !== '') {
    $where[] = "(a.title_fr LIKE ? OR a.excerpt_fr LIKE ? OR a.content_fr LIKE ? OR a.author LIKE ? OR a.tags LIKE ?)";
    $like = '%' . $filter_q . '%';
    array_push($params, $like, $like, $like, $like, $like);
    $types .= 'sssss';
}
$where_sql = implode(' AND ', $where);

$countStmt = $mysqli->prepare("SELECT COUNT(*) AS c FROM news_articles a LEFT JOIN news_categories c ON c.id = a.category_id WHERE $where_sql");
if ($types !== '') { $countStmt->bind_param($types, ...$params); }
$countStmt->execute();
$total_items = (int) $countStmt->get_result()->fetch_assoc()['c'];
$countStmt->close();
$total_pages = max(1, (int) ceil($total_items / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$sql = "SELECT a.*, c.slug AS cat_slug, c.name_fr AS cat_name_fr, c.name_en AS cat_name_en, c.name_mg AS cat_name_mg, c.icon AS cat_icon
        FROM news_articles a LEFT JOIN news_categories c ON c.id = a.category_id
        WHERE $where_sql ORDER BY a.published_at DESC, a.id DESC LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($sql);
$allTypes = $types . 'ii';
$allParams = $params;
$allParams[] = $per_page;
$allParams[] = $offset;
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- Article à la une (uniquement en l'absence de filtres, page 1) ---
$featured = null;
if ($filter_cat === '' && $filter_year === 0 && $filter_month === 0 && $filter_q === '' && $page === 1) {
    $fres = $mysqli->query("
        SELECT a.*, c.slug AS cat_slug, c.name_fr AS cat_name_fr, c.name_en AS cat_name_en, c.name_mg AS cat_name_mg, c.icon AS cat_icon
        FROM news_articles a LEFT JOIN news_categories c ON c.id = a.category_id
        WHERE a.status='publie' AND a.is_featured = 1 AND (a.published_at IS NULL OR a.published_at <= NOW())
        ORDER BY a.published_at DESC LIMIT 1
    ");
    $featured = $fres->fetch_assoc();
    if ($featured) {
        // Ne pas dupliquer l'article à la une dans la liste ci-dessous
        $articles = array_values(array_filter($articles, fn($a) => $a['id'] != $featured['id']));
    }
}

function news_title($a, $lang) { $k = 'title_' . $lang; return !empty($a[$k]) ? $a[$k] : $a['title_fr']; }
function news_excerpt($a, $lang) { $k = 'excerpt_' . $lang; return !empty($a[$k]) ? $a[$k] : $a['excerpt_fr']; }
function news_cat_name($a, $lang) { $k = 'cat_name_' . $lang; return $a[$k] ?? ($a['cat_name_fr'] ?? ''); }

$grouped = [];
foreach ($articles as $a) {
    $y = $a['published_at'] ? date('Y', strtotime($a['published_at'])) : 'Sans date';
    $grouped[$y][] = $a;
}
?>

<div class="page-banner news-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('actualites_page_titre'); ?></span>
        </nav>
        <h1><?php echo t('actualites_page_titre'); ?></h1>
        <p><?php echo t('actualites_page_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <?php include 'background_animation.php'; ?>
    <div class="container">

        <?php if ($featured): ?>
            <a href="actualite_article.php?slug=<?php echo urlencode($featured['slug']); ?>" class="news-featured-card">
                <div class="news-featured-img">
                    <img src="<?php echo htmlspecialchars($featured['image_path'] ?: 'images/logo-isstm.jpg'); ?>" alt="<?php echo htmlspecialchars(news_title($featured, $lang)); ?>">
                    <span class="news-featured-badge"><i class="fas fa-star"></i> À la une</span>
                </div>
                <div class="news-featured-info">
                    <?php if ($featured['cat_slug']): ?><span class="news-cat-badge"><i class="fas <?php echo htmlspecialchars($featured['cat_icon']); ?>"></i> <?php echo htmlspecialchars(news_cat_name($featured, $lang)); ?></span><?php endif; ?>
                    <h2><?php echo htmlspecialchars(news_title($featured, $lang)); ?></h2>
                    <p><?php echo htmlspecialchars(news_excerpt($featured, $lang)); ?></p>
                    <div class="news-featured-meta">
                        <?php if ($featured['published_at']): ?><span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($featured['published_at'])); ?></span><?php endif; ?>
                        <?php if ($featured['author']): ?><span><i class="far fa-user"></i> <?php echo htmlspecialchars($featured['author']); ?></span><?php endif; ?>
                    </div>
                    <span class="btn btn-primary news-featured-cta">Lire l'article <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
        <?php endif; ?>

        <form method="GET" action="actualite.php" class="gallery-toolbar">
            <div class="gallery-toolbar-field">
                <select name="cat" onchange="this.form.submit()">
                    <option value=""><?php echo t('galerie_toutes_categories'); ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['slug']); ?>" <?php echo $filter_cat === $cat['slug'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat[$lang === 'en' ? 'name_en' : ($lang === 'mg' ? 'name_mg' : 'name_fr')]); ?> (<?php echo (int) $cat['article_count']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="search-bar-container gallery-search-bar">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" value="<?php echo htmlspecialchars($filter_q); ?>" placeholder="Rechercher un titre, un auteur, un mot-clé..." autocomplete="off">
                <?php if ($filter_q !== ''): ?>
                    <a href="actualite.php?<?php echo http_build_query(array_filter(['cat' => $filter_cat ?: null, 'year' => $filter_year ?: null, 'month' => $filter_month ?: null])); ?>" class="search-reset-btn show" title="Réinitialiser">&times;</a>
                <?php endif; ?>
            </div>
            <div class="gallery-toolbar-field">
                <select name="year" onchange="this.form.submit()">
                    <option value="">Toutes les années</option>
                    <?php foreach ($years as $y): ?>
                        <option value="<?php echo $y; ?>" <?php echo $filter_year === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="gallery-toolbar-field">
                <select name="month" onchange="this.form.submit()">
                    <option value="">Tous les mois</option>
                    <?php foreach ($mois_labels as $num => $label): ?>
                        <option value="<?php echo $num; ?>" <?php echo $filter_month === $num ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if (empty($articles) && !$featured): ?>
            <div class="gallery-empty-state">
                <i class="fas fa-newspaper"></i>
                <h3>Aucune actualité ne correspond à votre recherche</h3>
                <p>Essayez d'autres filtres ou réinitialisez la recherche.</p>
                <a href="actualite.php" class="btn btn-outline">Réinitialiser les filtres</a>
            </div>
        <?php elseif (!empty($articles)): ?>
            <?php foreach ($grouped as $year => $yearArticles): ?>
                <div class="gallery-year-block">
                    <h2 class="gallery-year-heading"><i class="fas fa-calendar-days"></i> <?php echo htmlspecialchars($year); ?></h2>
                    <div class="news-grid">
                        <?php foreach ($yearArticles as $i => $article): ?>
                            <a href="actualite_article.php?slug=<?php echo urlencode($article['slug']); ?>" class="news-card animate-on-scroll" style="--delay: <?php echo ($i % 6) * 0.08; ?>s;">
                                <div class="news-card-img">
                                    <img src="<?php echo htmlspecialchars($article['image_path'] ?: 'images/logo-isstm.jpg'); ?>" alt="<?php echo htmlspecialchars(news_title($article, $lang)); ?>" loading="lazy">
                                    <?php if ($article['cat_slug']): ?><span class="news-cat-badge"><i class="fas <?php echo htmlspecialchars($article['cat_icon']); ?>"></i> <?php echo htmlspecialchars(news_cat_name($article, $lang)); ?></span><?php endif; ?>
                                </div>
                                <div class="news-card-info">
                                    <h3><?php echo htmlspecialchars(news_title($article, $lang)); ?></h3>
                                    <p><?php echo htmlspecialchars(news_excerpt($article, $lang)); ?></p>
                                    <div class="news-card-meta">
                                        <?php if ($article['published_at']): ?><span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($article['published_at'])); ?></span><?php endif; ?>
                                        <?php if ($article['author']): ?><span><i class="far fa-user"></i> <?php echo htmlspecialchars($article['author']); ?></span><?php endif; ?>
                                        <span><i class="far fa-eye"></i> <?php echo format_compact_number($article['views']); ?></span>
                                    </div>
                                    <span class="news-card-cta">Lire la suite <i class="fas fa-arrow-right"></i></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($total_pages > 1): ?>
                <nav class="gallery-pagination" aria-label="Pagination des actualités">
                    <?php if ($page > 1): ?><a href="actualite.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-nav-btn"><i class="fas fa-chevron-left"></i> Précédent</a><?php endif; ?>
                    <div class="page-numbers">
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <a href="actualite.php?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>" class="page-number <?php echo $p === $page ? 'is-active' : ''; ?>"><?php echo $p; ?></a>
                        <?php endfor; ?>
                    </div>
                    <?php if ($page < $total_pages): ?><a href="actualite.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-nav-btn">Suivant <i class="fas fa-chevron-right"></i></a><?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>

        <!-- === Newsletter === -->
        <div class="newsletter-box">
            <div class="newsletter-box-icon"><i class="fas fa-envelope-open-text"></i></div>
            <div class="newsletter-box-text">
                <h3>Ne manquez aucune actualité</h3>
                <p>Inscrivez-vous à la newsletter pour recevoir les dernières actualités de l'ISSTM directement par email.</p>
            </div>
            <form method="POST" action="actualite.php#newsletter" class="newsletter-box-form">
                <input type="email" name="newsletter_email" placeholder="Votre adresse email" required>
                <button type="submit"><i class="fas fa-paper-plane"></i> S'inscrire</button>
            </form>
        </div>
        <?php if ($newsletter_msg): ?>
            <div class="admin-flash admin-flash-<?php echo $newsletter_msg['type']; ?>" id="newsletter">
                <i class="fas <?php echo $newsletter_msg['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'; ?>"></i>
                <?php echo htmlspecialchars($newsletter_msg['text']); ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include 'footer.php'; ?>
