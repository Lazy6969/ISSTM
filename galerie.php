<?php
include_once 'language.php';
$page_title = t('galerie_page_titre');
include 'header.php';

// --- Catégories avec compteur d'albums publiés ---
$categories = [];
$catResult = $mysqli->query("
    SELECT c.*, COUNT(a.id) AS album_count
    FROM gallery_categories c
    LEFT JOIN gallery_albums a ON a.category_id = c.id AND a.status = 'publie'
    GROUP BY c.id
    ORDER BY c.display_order ASC
");
if ($catResult) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// --- Années disponibles (albums publiés uniquement) ---
$years = [];
$yearResult = $mysqli->query("
    SELECT DISTINCT YEAR(event_date) AS y
    FROM gallery_albums
    WHERE status = 'publie' AND event_date IS NOT NULL
    ORDER BY y DESC
");
if ($yearResult) {
    while ($row = $yearResult->fetch_assoc()) {
        $years[] = (int) $row['y'];
    }
}

// --- Lecture des filtres ---
$filter_cat   = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$filter_year  = isset($_GET['year']) && ctype_digit($_GET['year']) ? (int) $_GET['year'] : 0;
$filter_month = isset($_GET['month']) && ctype_digit($_GET['month']) && (int) $_GET['month'] >= 1 && (int) $_GET['month'] <= 12 ? (int) $_GET['month'] : 0;
$filter_q     = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_sort  = (isset($_GET['sort']) && $_GET['sort'] === 'asc') ? 'asc' : 'desc';
$view_mode    = (isset($_GET['view']) && $_GET['view'] === 'masonry') ? 'masonry' : 'grid';
$page         = isset($_GET['page']) && ctype_digit($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$per_page     = 9;

$mois_labels = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
    7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
];

// --- Construction de la requête ---
$where = ["a.status = 'publie'"];
$params = [];
$types = '';

if ($filter_cat !== '') {
    $where[] = "c.slug = ?";
    $params[] = $filter_cat;
    $types .= 's';
}
if ($filter_year > 0) {
    $where[] = "YEAR(a.event_date) = ?";
    $params[] = $filter_year;
    $types .= 'i';
}
if ($filter_month > 0) {
    $where[] = "MONTH(a.event_date) = ?";
    $params[] = $filter_month;
    $types .= 'i';
}
if ($filter_q !== '') {
    $where[] = "(a.title_fr LIKE ? OR a.title_en LIKE ? OR a.title_mg LIKE ? OR a.description_fr LIKE ? OR a.location LIKE ? OR EXISTS (SELECT 1 FROM gallery_photos gp WHERE gp.album_id = a.id AND gp.tags LIKE ?))";
    $like = '%' . $filter_q . '%';
    array_push($params, $like, $like, $like, $like, $like, $like);
    $types .= 'ssssss';
}

$where_sql = implode(' AND ', $where);

// --- Total pour la pagination ---
$countSql = "SELECT COUNT(*) AS c FROM gallery_albums a LEFT JOIN gallery_categories c ON c.id = a.category_id WHERE $where_sql";
$countStmt = $mysqli->prepare($countSql);
if ($types !== '') { $countStmt->bind_param($types, ...$params); }
$countStmt->execute();
$total_items = (int) $countStmt->get_result()->fetch_assoc()['c'];
$countStmt->close();
$total_pages = max(1, (int) ceil($total_items / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

// --- Récupération des albums ---
$sql = "
    SELECT a.*, c.slug AS cat_slug, c.name_fr AS cat_name_fr, c.name_en AS cat_name_en, c.name_mg AS cat_name_mg, c.icon AS cat_icon,
        (SELECT COUNT(*) FROM gallery_photos p WHERE p.album_id = a.id) AS photo_count
    FROM gallery_albums a
    LEFT JOIN gallery_categories c ON c.id = a.category_id
    WHERE $where_sql
    ORDER BY a.event_date " . ($filter_sort === 'asc' ? 'ASC' : 'DESC') . ", a.id " . ($filter_sort === 'asc' ? 'ASC' : 'DESC') . "
    LIMIT ? OFFSET ?
";
$stmt = $mysqli->prepare($sql);
$allTypes = $types . 'ii';
$allParams = $params;
$allParams[] = $per_page;
$allParams[] = $offset;
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$albums = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function gallery_cat_name($album, $lang) {
    $key = 'cat_name_' . $lang;
    return $album[$key] ?? $album['cat_name_fr'] ?? '';
}
function gallery_album_title($album, $lang) {
    $key = 'title_' . $lang;
    return !empty($album[$key]) ? $album[$key] : $album['title_fr'];
}

// --- Regroupement par année pour l'affichage (en-têtes d'année) ---
$grouped = [];
foreach ($albums as $album) {
    $y = $album['event_date'] ? date('Y', strtotime($album['event_date'])) : 'Sans date';
    $grouped[$y][] = $album;
}
?>

<div class="page-banner gallery-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('galerie_page_titre'); ?></span>
        </nav>
        <h1><?php echo t('galerie_page_titre'); ?></h1>
        <p><?php echo t('galerie_page_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <?php include 'background_animation.php'; ?>
    <div class="container">

        <!-- === Barre d'outils : catégorie, recherche, filtres, tri, affichage === -->
        <form method="GET" action="galerie.php" class="gallery-toolbar" id="gallery-toolbar-form">
            <div class="gallery-toolbar-field">
                <select name="cat" onchange="this.form.submit()">
                    <option value=""><?php echo t('galerie_toutes_categories'); ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['slug']); ?>" <?php echo $filter_cat === $cat['slug'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat[$lang === 'en' ? 'name_en' : ($lang === 'mg' ? 'name_mg' : 'name_fr')]); ?> (<?php echo (int) $cat['album_count']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="search-bar-container gallery-search-bar">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" id="gallery-search-input" value="<?php echo htmlspecialchars($filter_q); ?>" placeholder="Rechercher un album, un lieu, un tag..." autocomplete="off">
                <?php if ($filter_q !== ''): ?>
                    <a href="galerie.php?<?php echo http_build_query(array_filter(['cat' => $filter_cat ?: null, 'year' => $filter_year ?: null, 'month' => $filter_month ?: null, 'sort' => $filter_sort])); ?>" class="search-reset-btn show" title="Réinitialiser">&times;</a>
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

            <div class="gallery-toolbar-field">
                <select name="sort" onchange="this.form.submit()">
                    <option value="desc" <?php echo $filter_sort === 'desc' ? 'selected' : ''; ?>>Plus récent → plus ancien</option>
                    <option value="asc" <?php echo $filter_sort === 'asc' ? 'selected' : ''; ?>>Plus ancien → plus récent</option>
                </select>
            </div>

            <div class="gallery-view-toggle" role="group" aria-label="Mode d'affichage">
                <a href="galerie.php?<?php echo http_build_query(array_merge($_GET, ['view' => 'grid'])); ?>" class="gallery-view-btn <?php echo $view_mode === 'grid' ? 'is-active' : ''; ?>" title="Grille"><i class="fas fa-table-cells"></i></a>
                <a href="galerie.php?<?php echo http_build_query(array_merge($_GET, ['view' => 'masonry'])); ?>" class="gallery-view-btn <?php echo $view_mode === 'masonry' ? 'is-active' : ''; ?>" title="Mosaïque"><i class="fas fa-grip"></i></a>
            </div>
        </form>

        <!-- === Résultats === -->
        <?php if (empty($albums)): ?>
            <div class="gallery-empty-state">
                <i class="fas fa-camera-retro"></i>
                <h3>Aucun album ne correspond à votre recherche</h3>
                <p>Essayez d'autres filtres ou réinitialisez la recherche.</p>
                <a href="galerie.php" class="btn btn-outline">Réinitialiser les filtres</a>
            </div>
        <?php else: ?>
            <?php $masonry_i = 0; $masonry_heights = [240, 190, 300, 210, 260, 175]; ?>
            <?php foreach ($grouped as $year => $yearAlbums): ?>
                <div class="gallery-year-block">
                    <h2 class="gallery-year-heading"><i class="fas fa-calendar-days"></i> <?php echo htmlspecialchars($year); ?></h2>
                    <div class="album-grid album-grid-<?php echo $view_mode; ?>">
                        <?php foreach ($yearAlbums as $i => $album):
                            // En mode Mosaïque, la hauteur varie sur un cycle continu sur TOUS les albums affichés
                            // (et non remise à zéro à chaque groupe année), sinon l'effet mosaïque est invisible
                            // dès qu'un groupe année ne contient qu'un ou deux albums.
                            $cover_style = '';
                            if ($view_mode === 'masonry') {
                                $cover_style = ' style="height:' . $masonry_heights[$masonry_i % count($masonry_heights)] . 'px;"';
                                $masonry_i++;
                            }
                        ?>
                            <a href="galerie_album.php?id=<?php echo (int) $album['id']; ?>" class="album-card animate-on-scroll" style="--delay: <?php echo ($i % 6) * 0.08; ?>s;">
                                <div class="album-card-cover"<?php echo $cover_style; ?>>
                                    <img src="<?php echo htmlspecialchars($album['cover_image'] ?: 'images/logo-isstm.jpg'); ?>" alt="<?php echo htmlspecialchars(gallery_album_title($album, $lang)); ?>" loading="lazy">
                                    <span class="album-card-photocount"><i class="fas fa-images"></i> <?php echo (int) $album['photo_count']; ?></span>
                                    <?php if ($album['cat_slug']): ?>
                                        <span class="album-card-category"><i class="fas <?php echo htmlspecialchars($album['cat_icon']); ?>"></i> <?php echo htmlspecialchars(gallery_cat_name($album, $lang)); ?></span>
                                    <?php endif; ?>
                                    <div class="album-card-overlay">
                                        <span><i class="fas fa-eye"></i> Voir l'album</span>
                                    </div>
                                </div>
                                <div class="album-card-info">
                                    <h3><?php echo htmlspecialchars(gallery_album_title($album, $lang)); ?></h3>
                                    <div class="album-card-meta">
                                        <?php if ($album['event_date']): ?><span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($album['event_date'])); ?></span><?php endif; ?>
                                        <?php if ($album['location']): ?><span><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($album['location']); ?></span><?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- === Pagination === -->
            <?php if ($total_pages > 1): ?>
                <nav class="gallery-pagination" aria-label="Pagination de la galerie">
                    <?php if ($page > 1): ?>
                        <a href="galerie.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-nav-btn"><i class="fas fa-chevron-left"></i> Précédent</a>
                    <?php endif; ?>
                    <div class="page-numbers">
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <a href="galerie.php?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>" class="page-number <?php echo $p === $page ? 'is-active' : ''; ?>"><?php echo $p; ?></a>
                        <?php endfor; ?>
                    </div>
                    <?php if ($page < $total_pages): ?>
                        <a href="galerie.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-nav-btn">Suivant <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<?php include 'footer.php'; ?>
