<?php
require_once 'db_connect.php';

$album_id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $mysqli->prepare("
    SELECT a.*, c.slug AS cat_slug, c.name_fr AS cat_name_fr, c.name_en AS cat_name_en, c.name_mg AS cat_name_mg, c.icon AS cat_icon
    FROM gallery_albums a
    LEFT JOIN gallery_categories c ON c.id = a.category_id
    WHERE a.id = ? AND a.status = 'publie'
");
$stmt->bind_param('i', $album_id);
$stmt->execute();
$album = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$album) {
    header('Location: galerie.php');
    exit;
}

include_once 'language.php';

function gallery_video_info($url) {
    $url = trim((string) $url);
    if ($url === '') return ['type' => 'file', 'embed' => ''];
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/shorts/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
        return ['type' => 'iframe', 'embed' => 'https://www.youtube.com/embed/' . $m[1]];
    }
    if (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
        return ['type' => 'iframe', 'embed' => 'https://player.vimeo.com/video/' . $m[1]];
    }
    return ['type' => 'file', 'embed' => $url];
}

function gallery_album_title2($album, $lang) {
    $key = 'title_' . $lang;
    return !empty($album[$key]) ? $album[$key] : $album['title_fr'];
}

$page_title = gallery_album_title2($album, $lang);

include 'header.php';

$stmt = $mysqli->prepare("SELECT * FROM gallery_photos WHERE album_id = ? ORDER BY display_order ASC, id ASC");
$stmt->bind_param('i', $album_id);
$stmt->execute();
$photos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
function gallery_album_desc2($album, $lang) {
    $key = 'description_' . $lang;
    return !empty($album[$key]) ? $album[$key] : $album['description_fr'];
}
$album_title = gallery_album_title2($album, $lang);
$album_desc = gallery_album_desc2($album, $lang);
$cat_name = $album['cat_name_' . ($lang === 'en' || $lang === 'mg' ? $lang : 'fr')] ?? $album['cat_name_fr'];

$page_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>

<div class="page-banner gallery-banner gallery-album-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="galerie.php"><?php echo t('galerie_page_titre'); ?></a>
            <?php if ($album['cat_slug']): ?>
                <i class="fas fa-chevron-right"></i>
                <a href="galerie.php?cat=<?php echo urlencode($album['cat_slug']); ?>"><?php echo htmlspecialchars($cat_name); ?></a>
            <?php endif; ?>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($album_title); ?></span>
        </nav>
        <h1><?php echo htmlspecialchars($album_title); ?></h1>
        <div class="album-banner-meta">
            <?php if ($album['event_date']): ?><span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($album['event_date'])); ?></span><?php endif; ?>
            <?php if ($album['location']): ?><span><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($album['location']); ?></span><?php endif; ?>
            <?php if ($album['author']): ?><span><i class="fas fa-user-pen"></i> <?php echo htmlspecialchars($album['author']); ?></span><?php endif; ?>
            <span><i class="fas fa-images"></i> <?php echo count($photos); ?> photo<?php echo count($photos) > 1 ? 's' : ''; ?></span>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <a href="galerie.php" class="btn-outline btn-back-sticky"><i class="fas fa-arrow-left"></i> Retour à la Galerie</a>

        <?php if ($album_desc): ?>
            <p class="album-description"><?php echo nl2br(htmlspecialchars($album_desc)); ?></p>
        <?php endif; ?>

        <!-- === Partage === -->
        <div class="album-share-row">
            <span class="album-share-label"><i class="fas fa-share-nodes"></i> Partager cet album :</span>
            <a class="share-btn share-facebook" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($page_url); ?>" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a class="share-btn share-whatsapp" target="_blank" rel="noopener" href="https://wa.me/?text=<?php echo urlencode($album_title . ' - ' . $page_url); ?>" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            <a class="share-btn share-messenger" target="_blank" rel="noopener" href="https://www.facebook.com/dialog/send?link=<?php echo urlencode($page_url); ?>&app_id=0&redirect_uri=<?php echo urlencode($page_url); ?>" title="Messenger"><i class="fab fa-facebook-messenger"></i></a>
            <a class="share-btn share-linkedin" target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($page_url); ?>" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a class="share-btn share-x" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?text=<?php echo urlencode($album_title); ?>&url=<?php echo urlencode($page_url); ?>" title="X"><i class="fab fa-x-twitter"></i></a>
            <a class="share-btn share-email" href="mailto:?subject=<?php echo urlencode($album_title); ?>&body=<?php echo urlencode($page_url); ?>" title="Email"><i class="fas fa-envelope"></i></a>
            <button type="button" class="share-btn share-copy" id="btn-copy-link" title="Copier le lien"><i class="fas fa-link"></i></button>
        </div>

        <!-- === Grille de photos === -->
        <?php if (empty($photos)): ?>
            <p class="gallery-empty-state"><i class="fas fa-image"></i> Cet album ne contient pas encore de photo.</p>
        <?php else: ?>
            <div class="photo-grid" id="album-photo-grid">
                <?php foreach ($photos as $i => $photo):
                    $is_video = ($photo['media_type'] ?? 'photo') === 'video';
                    $video = $is_video ? gallery_video_info($photo['video_url']) : null;
                    $thumb = $photo['image_path'] ?: 'images/logo-isstm.jpg';
                ?>
                    <button type="button" class="photo-grid-item animate-on-scroll<?php echo $is_video ? ' is-video' : ''; ?>" style="--delay: <?php echo ($i % 8) * 0.06; ?>s;"
                            data-index="<?php echo $i; ?>"
                            data-type="<?php echo $is_video ? 'video' : 'photo'; ?>"
                            data-src="<?php echo htmlspecialchars($thumb); ?>"
                            <?php if ($is_video): ?>
                            data-video-type="<?php echo $video['type']; ?>"
                            data-video-embed="<?php echo htmlspecialchars($video['embed']); ?>"
                            <?php endif; ?>
                            data-title="<?php echo htmlspecialchars($photo['title'] ?: ''); ?>"
                            data-desc="<?php echo htmlspecialchars($photo['description'] ?: ''); ?>"
                            data-date="<?php echo $photo['taken_at'] ? date('d/m/Y', strtotime($photo['taken_at'])) : ''; ?>"
                            data-location="<?php echo htmlspecialchars($photo['location'] ?: ''); ?>"
                            data-photographer="<?php echo htmlspecialchars($photo['photographer'] ?: ''); ?>">
                        <img src="<?php echo htmlspecialchars($thumb); ?>" alt="<?php echo htmlspecialchars($photo['alt_text'] ?: $photo['title'] ?: $album_title); ?>" loading="lazy">
                        <?php if ($is_video): ?>
                            <span class="video-play-badge"><i class="fas fa-play"></i></span>
                        <?php else: ?>
                            <span class="photo-grid-zoom"><i class="fas fa-magnifying-glass-plus"></i></span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

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

<?php include 'footer.php'; ?>
