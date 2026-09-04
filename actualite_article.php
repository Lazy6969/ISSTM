<?php
require_once 'db_connect.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

$stmt = $mysqli->prepare("
    SELECT a.*, c.slug AS cat_slug, c.name_fr AS cat_name_fr, c.name_en AS cat_name_en, c.name_mg AS cat_name_mg, c.icon AS cat_icon
    FROM news_articles a LEFT JOIN news_categories c ON c.id = a.category_id
    WHERE a.slug = ? AND a.status = 'publie' AND (a.published_at IS NULL OR a.published_at <= NOW())
");
$stmt->bind_param('s', $slug);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$article) {
    header('Location: actualite.php');
    exit;
}

include_once 'language.php';

function news_title2($a, $lang) { $k = 'title_' . $lang; return !empty($a[$k]) ? $a[$k] : $a['title_fr']; }
function news_content2($a, $lang) { $k = 'content_' . $lang; return !empty($a[$k]) ? $a[$k] : $a['content_fr']; }

$page_title = news_title2($article, $lang);

include 'header.php';

// --- Compteur de vues (une fois par session pour éviter le gonflement artificiel) ---
if (!isset($_SESSION['viewed_articles'])) { $_SESSION['viewed_articles'] = []; }
if (!in_array($article['id'], $_SESSION['viewed_articles'])) {
    $mysqli->query("UPDATE news_articles SET views = views + 1 WHERE id = " . (int) $article['id']);
    $_SESSION['viewed_articles'][] = $article['id'];
    $article['views']++;
}
function news_video_embed($url) {
    $url = trim((string) $url);
    if ($url === '') return null;
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/shorts/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
        return ['type' => 'iframe', 'src' => 'https://www.youtube.com/embed/' . $m[1]];
    }
    if (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
        return ['type' => 'iframe', 'src' => 'https://player.vimeo.com/video/' . $m[1]];
    }
    return ['type' => 'file', 'src' => $url];
}
$article_title = news_title2($article, $lang);
$article_content = news_content2($article, $lang);
$cat_name = $article['cat_name_' . ($lang === 'en' || $lang === 'mg' ? $lang : 'fr')] ?? $article['cat_name_fr'];

$stmt = $mysqli->prepare("SELECT * FROM news_attachments WHERE article_id = ? ORDER BY id ASC");
$stmt->bind_param('i', $article['id']);
$stmt->execute();
$attachments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$news_photos = [];
if ($mysqli->query("SHOW TABLES LIKE 'news_photos'")->num_rows > 0) {
    $stmt = $mysqli->prepare("SELECT * FROM news_photos WHERE article_id = ? ORDER BY display_order ASC, id ASC");
    $stmt->bind_param('i', $article['id']);
    $stmt->execute();
    $news_photos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// --- Articles similaires : même catégorie en priorité, sinon les plus récents ---
$related = [];
if ($article['category_id']) {
    $stmt = $mysqli->prepare("
        SELECT id, title_fr, title_en, title_mg, slug, image_path, published_at
        FROM news_articles
        WHERE category_id = ? AND id != ? AND status='publie' AND (published_at IS NULL OR published_at <= NOW())
        ORDER BY published_at DESC LIMIT 3
    ");
    $stmt->bind_param('ii', $article['category_id'], $article['id']);
    $stmt->execute();
    $related = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
if (count($related) < 3) {
    $exclude_ids = array_merge([$article['id']], array_column($related, 'id'));
    $placeholders = implode(',', array_fill(0, count($exclude_ids), '?'));
    $types2 = str_repeat('i', count($exclude_ids));
    $need = 3 - count($related);
    $stmt = $mysqli->prepare("SELECT id, title_fr, title_en, title_mg, slug, image_path, published_at FROM news_articles WHERE id NOT IN ($placeholders) AND status='publie' AND (published_at IS NULL OR published_at <= NOW()) ORDER BY published_at DESC LIMIT $need");
    $stmt->bind_param($types2, ...$exclude_ids);
    $stmt->execute();
    $more = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $related = array_merge($related, $more);
}

$tags_list = $article['tags'] ? array_map('trim', explode(',', $article['tags'])) : [];
$page_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>

<div class="page-banner news-banner news-article-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="actualite.php"><?php echo t('actualites_page_titre'); ?></a>
            <?php if ($article['cat_slug']): ?>
                <i class="fas fa-chevron-right"></i>
                <a href="actualite.php?cat=<?php echo urlencode($article['cat_slug']); ?>"><?php echo htmlspecialchars($cat_name); ?></a>
            <?php endif; ?>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($article_title); ?></span>
        </nav>
        <h1><?php echo htmlspecialchars($article_title); ?></h1>
        <div class="album-banner-meta">
            <?php if ($article['published_at']): ?><span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($article['published_at'])); ?></span><?php endif; ?>
            <?php if ($article['author']): ?><span><i class="far fa-user"></i> <?php echo htmlspecialchars($article['author']); ?></span><?php endif; ?>
            <span><i class="far fa-eye"></i> <?php echo format_compact_number($article['views']); ?> vue(s)</span>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <a href="actualite.php" class="btn-outline btn-back-sticky"><i class="fas fa-arrow-left"></i> Retour aux Actualités</a>

        <div class="news-article-layout">
            <article class="news-article-main">
                <?php if ($article['image_path']): ?>
                    <img src="<?php echo htmlspecialchars($article['image_path']); ?>" alt="<?php echo htmlspecialchars($article_title); ?>" class="news-article-image">
                <?php endif; ?>

                <?php $video = news_video_embed($article['video_url'] ?? ''); if ($video): ?>
                    <div class="news-article-video">
                        <h3><i class="fas fa-video"></i> <?php echo t('actualite_video_titre'); ?></h3>
                        <?php if ($video['type'] === 'iframe'): ?>
                            <iframe src="<?php echo htmlspecialchars($video['src']); ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                        <?php else: ?>
                            <video src="<?php echo htmlspecialchars($video['src']); ?>" controls></video>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="news-article-content">
                    <?php echo nl2br(htmlspecialchars($article_content)); ?>
                </div>

                <?php if (!empty($news_photos)): ?>
                    <div class="news-article-gallery">
                        <h3><i class="fas fa-images"></i> <?php echo t('admin_actualites_photos_titre'); ?></h3>
                        <div class="photo-grid" id="album-photo-grid">
                            <?php foreach ($news_photos as $i => $photo):
                                $is_video = ($photo['media_type'] ?? 'photo') === 'video';
                                $video = $is_video ? news_video_embed($photo['video_url']) : null;
                                $thumb = $photo['image_path'] ?: 'images/logo-isstm.jpg';
                            ?>
                                <button type="button" class="photo-grid-item animate-on-scroll<?php echo $is_video ? ' is-video' : ''; ?>" style="--delay: <?php echo ($i % 8) * 0.06; ?>s;"
                                        data-index="<?php echo $i; ?>"
                                        data-type="<?php echo $is_video ? 'video' : 'photo'; ?>"
                                        data-src="<?php echo htmlspecialchars($thumb); ?>"
                                        <?php if ($is_video): ?>
                                        data-video-type="<?php echo $video['type'] === 'iframe' ? 'iframe' : 'file'; ?>"
                                        data-video-embed="<?php echo htmlspecialchars($video['src']); ?>"
                                        <?php endif; ?>
                                        data-title="<?php echo htmlspecialchars($photo['title'] ?: ''); ?>">
                                    <img src="<?php echo htmlspecialchars($thumb); ?>" alt="<?php echo htmlspecialchars($photo['title'] ?: $article_title); ?>" loading="lazy">
                                    <?php if ($is_video): ?>
                                        <span class="video-play-badge"><i class="fas fa-play"></i></span>
                                    <?php else: ?>
                                        <span class="photo-grid-zoom"><i class="fas fa-magnifying-glass-plus"></i></span>
                                    <?php endif; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($tags_list)): ?>
                    <div class="news-tags-row">
                        <?php foreach ($tags_list as $tag): if ($tag === '') continue; ?>
                            <a href="actualite.php?q=<?php echo urlencode($tag); ?>" class="news-tag">#<?php echo htmlspecialchars($tag); ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($attachments)): ?>
                    <div class="news-attachments-box">
                        <h3><i class="fas fa-paperclip"></i> Documents joints</h3>
                        <ul>
                            <?php foreach ($attachments as $att): ?>
                                <li>
                                    <a href="<?php echo htmlspecialchars($att['file_path']); ?>" target="_blank" rel="noopener">
                                        <i class="fas fa-file-arrow-down"></i> <?php echo htmlspecialchars($att['label'] ?: basename($att['file_path'])); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="album-share-row">
                    <span class="album-share-label"><i class="fas fa-share-nodes"></i> Partager cet article :</span>
                    <a class="share-btn share-facebook" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($page_url); ?>" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a class="share-btn share-whatsapp" target="_blank" rel="noopener" href="https://wa.me/?text=<?php echo urlencode($article_title . ' - ' . $page_url); ?>" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    <a class="share-btn share-messenger" target="_blank" rel="noopener" href="https://www.facebook.com/dialog/send?link=<?php echo urlencode($page_url); ?>&app_id=0&redirect_uri=<?php echo urlencode($page_url); ?>" title="Messenger"><i class="fab fa-facebook-messenger"></i></a>
                    <a class="share-btn share-linkedin" target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($page_url); ?>" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a class="share-btn share-x" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?text=<?php echo urlencode($article_title); ?>&url=<?php echo urlencode($page_url); ?>" title="X"><i class="fab fa-x-twitter"></i></a>
                    <a class="share-btn share-email" href="mailto:?subject=<?php echo urlencode($article_title); ?>&body=<?php echo urlencode($page_url); ?>" title="Email"><i class="fas fa-envelope"></i></a>
                    <button type="button" class="share-btn share-copy" id="btn-copy-link" title="Copier le lien"><i class="fas fa-link"></i></button>
                </div>
            </article>

            <aside class="news-article-side">
                <h3><i class="fas fa-newspaper"></i> Articles similaires</h3>
                <?php foreach ($related as $r): ?>
                    <a href="actualite_article.php?slug=<?php echo urlencode($r['slug']); ?>" class="news-related-card">
                        <img src="<?php echo htmlspecialchars($r['image_path'] ?: 'images/logo-isstm.jpg'); ?>" alt="" loading="lazy">
                        <div>
                            <h4><?php echo htmlspecialchars(news_title2($r, $lang)); ?></h4>
                            <?php if ($r['published_at']): ?><span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($r['published_at'])); ?></span><?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </aside>
        </div>

    </div>
</div>

<?php if (!empty($news_photos)): ?>
<!-- === Lightbox Galerie enrichie === -->
<div class="gal-lightbox" id="gal-lightbox">
    <div class="gal-lightbox-top">
        <span class="gal-lightbox-counter"><span id="gal-lb-current">1</span> / <span id="gal-lb-total">1</span></span>
        <div class="gal-lightbox-actions">
            <button type="button" id="gal-lb-zoom" title="Zoomer"><i class="fas fa-magnifying-glass-plus"></i></button>
            <button type="button" id="gal-lb-fullscreen" title="Plein écran"><i class="fas fa-expand"></i></button>
            <a id="gal-lb-download" title="Télécharger" download><i class="fas fa-download"></i></a>
            <button type="button" id="gal-lb-share" title="Partager"><i class="fas fa-share-nodes"></i></button>
            <button type="button" id="gal-lb-close" title="Fermer (Échap)"><i class="fas fa-xmark"></i></button>
        </div>
    </div>
    <button type="button" class="gal-lightbox-nav gal-lb-prev" id="gal-lb-prev" title="Précédente"><i class="fas fa-chevron-left"></i></button>
    <div class="gal-lightbox-stage">
        <img id="gal-lb-img" src="" alt="">
    </div>
    <button type="button" class="gal-lightbox-nav gal-lb-next" id="gal-lb-next" title="Suivante"><i class="fas fa-chevron-right"></i></button>
    <div class="gal-lightbox-caption">
        <h4 id="gal-lb-title"></h4>
        <p id="gal-lb-desc"></p>
        <div class="gal-lightbox-meta">
            <span id="gal-lb-date"></span>
            <span id="gal-lb-location"></span>
            <span id="gal-lb-photographer"></span>
        </div>
    </div>
</div>
<script src="galerie.js?v=<?php echo filemtime('galerie.js'); ?>"></script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const copyBtn = document.getElementById('btn-copy-link');
    if (copyBtn) {
        copyBtn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(window.location.href);
                copyBtn.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => { copyBtn.innerHTML = '<i class="fas fa-link"></i>'; }, 1500);
            } catch (e) {}
        });
    }
});
</script>

<?php include 'footer.php'; ?>
