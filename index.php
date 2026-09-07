<?php
include_once 'language.php';
$page_title = t('accueil');
include 'header.php';
require_once 'db_connect.php';

// Récupérer le contenu dynamique de la base de données
$contenus_db = [];
$result = $mysqli->query("SELECT content_key, content_value_fr, content_value_en, content_value_mg FROM site_content");
while ($row = $result->fetch_assoc()) {
    $contenus_db[$row['content_key']] = $row;
}

// Récupérer les témoignages depuis la nouvelle table
$testimonials_db = [];
$result = $mysqli->query("SELECT * FROM testimonials ORDER BY display_order ASC, id ASC");
while ($row = $result->fetch_assoc()) {
    $testimonials_db[] = $row;
}

// Récupérer les actualités récentes pour le grand carrousel d'accueil (si le module Actualités existe)
$news_carousel_slides = [];
if ($check_news_table = $mysqli->query("SHOW TABLES LIKE 'news_articles'")) {
    if ($check_news_table->num_rows > 0) {
        $result = $mysqli->query("
            SELECT a.title_fr, a.title_en, a.title_mg, a.excerpt_fr, a.excerpt_en, a.excerpt_mg, a.slug, a.image_path, a.published_at,
                   c.name_fr AS cat_name_fr, c.name_en AS cat_name_en, c.name_mg AS cat_name_mg, c.icon AS cat_icon
            FROM news_articles a LEFT JOIN news_categories c ON c.id = a.category_id
            WHERE a.status = 'publie' AND (a.published_at IS NULL OR a.published_at <= NOW())
            ORDER BY a.published_at DESC LIMIT 8
        ");
        while ($row = $result->fetch_assoc()) { $news_carousel_slides[] = $row; }
    }
}

// Récupérer les 5 prochains événements pour la section "Événements à venir" (si le module existe)
$events_home = [];
if ($check_events_table = $mysqli->query("SHOW TABLES LIKE 'evenements'")) {
    if ($check_events_table->num_rows > 0) {
        $result = $mysqli->query("SELECT * FROM evenements WHERE date_debut >= NOW() ORDER BY date_debut ASC LIMIT 5");
        while ($row = $result->fetch_assoc()) { $events_home[] = $row; }
    }
}

// Récupérer les slides du héros
$hero_slides_db = [];
$result = $mysqli->query("SELECT * FROM hero_slides ORDER BY display_order ASC, id ASC");
while ($row = $result->fetch_assoc()) {
    $hero_slides_db[] = $row;
}

// Récupérer les filières / parcours à mettre en avant sur l'accueil
$filieres_db = [];
if ($check_filieres = $mysqli->query("SHOW TABLES LIKE 'filieres'")) {
    if ($check_filieres->num_rows > 0) {
        $result = $mysqli->query("SELECT * FROM filieres ORDER BY display_order ASC, id ASC");
        while ($row = $result->fetch_assoc()) { $filieres_db[] = $row; }
    }
}

// Récupérer les partenaires
$partenaires_db = [];
if ($check_partenaires = $mysqli->query("SHOW TABLES LIKE 'partenaires'")) {
    if ($check_partenaires->num_rows > 0) {
        $result = $mysqli->query("SELECT * FROM partenaires ORDER BY display_order ASC, id ASC");
        while ($row = $result->fetch_assoc()) { $partenaires_db[] = $row; }
    }
}

// Photo de couverture pour la carte "Galeries" du carrousel "Découvrez notre univers" :
// la plus récente photo publiée, pour ne pas dépendre d'une image statique à maintenir.
$galerie_cover = 'images/portal_campus_1.jpg'; // repli si la galerie est encore vide
if ($check_gallery = $mysqli->query("SHOW TABLES LIKE 'gallery_photos'")) {
    if ($check_gallery->num_rows > 0) {
        $result = $mysqli->query("SELECT image_path FROM gallery_photos ORDER BY id DESC LIMIT 1");
        if ($row = $result->fetch_assoc()) { $galerie_cover = $row['image_path']; }
    }
}

// Compteur de vues de la page d'accueil : une seule fois par visite virtuelle (session),
// pas à chaque actualisation de la page.
$homepage_views = 0;
if ($check_stats = $mysqli->query("SHOW TABLES LIKE 'site_stats'")) {
    if ($check_stats->num_rows > 0) {
        if (!isset($_SESSION['homepage_visit_counted'])) {
            $mysqli->query("UPDATE site_stats SET stat_value = stat_value + 1 WHERE stat_key = 'homepage_views'");
            $_SESSION['homepage_visit_counted'] = true;
        }
        $row = $mysqli->query("SELECT stat_value FROM site_stats WHERE stat_key = 'homepage_views'")->fetch_assoc();
        $homepage_views = $row ? (int) $row['stat_value'] : 0;
    }
}

$mysqli->close();

// Fonction pour afficher le contenu dans la bonne langue
function dc($key) { // dc = dynamic content
    global $contenus_db, $lang;
    return htmlspecialchars($contenus_db[$key]['content_value_' . $lang] ?? '');
}
?>

<?php if (isset($_GET['deja_connecte'])): ?>
<div class="site-notice-banner" id="site-notice-banner">
    <i class="fas fa-circle-info"></i>
    <span><?php echo t('deja_connecte_notice'); ?></span>
    <button type="button" class="site-notice-close" onclick="document.getElementById('site-notice-banner').remove();" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
</div>
<?php endif; ?>

<!-- Section Héros avec diaporama -->
<section class="hero-section">
    <div class="slideshow-container">
        <!-- Les images du diaporama -->
        <?php foreach ($hero_slides_db as $index => $slide): ?>
            <div class="slide <?php if ($index === 0) echo 'active'; ?>">
                <?php if (($slide['media_type'] ?? 'image') === 'video'): ?>
                    <video src="<?php echo htmlspecialchars($slide['image_path']); ?>" muted playsinline></video>
                <?php else: ?>
                    <img src="<?php echo htmlspecialchars($slide['image_path']); ?>" alt="Diapositive ISSTM <?php echo $index + 1; ?>" loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="hero-overlay">
        <div class="hero-content">
            <div id="hero-particles"></div>
            <?php if (!empty($news_carousel_slides)):
                $hero_news = $news_carousel_slides[0];
                $hero_news_title = $hero_news['title_' . $lang] ?: $hero_news['title_fr'];
            ?>
                <a href="actualite_article.php?slug=<?php echo urlencode($hero_news['slug']); ?>" class="hero-news-card">
                    <span class="hero-news-card-media">
                        <?php if (!empty($hero_news['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($hero_news['image_path']); ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <span class="hero-news-card-media-fallback"><i class="fas fa-newspaper"></i></span>
                        <?php endif; ?>
                    </span>
                    <span class="hero-news-card-body">
                        <span class="hero-news-card-badge"><span class="hero-news-card-dot"></span><?php echo t('hero_derniere_actu'); ?></span>
                        <span class="hero-news-card-title"><?php echo htmlspecialchars($hero_news_title); ?></span>
                    </span>
                    <span class="hero-news-card-arrow"><i class="fas fa-arrow-right"></i></span>
                </a>
            <?php endif; ?>
            <h1>
                <span class="animated-hero-title" data-text="<?php echo t('hero_title_l1'); ?>"></span>
                <span class="animated-hero-title" data-text="<?php echo t('hero_title_l2'); ?>"></span>
            </h1>
            <p><?php echo t('hero_subtitle'); ?></p>
            <div class="hero-buttons">
                <?php if (!$header_is_site_user && !$header_is_bib_admin_only): ?>
                    <a href="inscription.php" class="btn btn-primary"><?php echo t('inscrivez_vous'); ?></a>
                    <a href="login.php" class="btn btn-secondary"><?php echo t('se_connecter'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Section des statistiques -->
<section class="stats-section">
    <?php include 'background_animation.php'; ?>
    <div class="container stats-grid-paired">
        <div class="stat-pair">
            <div class="stat-box">
                <i class="fas fa-user-graduate"></i>
                <h3 class="stat-number" data-target="<?php echo dc('stat_students'); ?>">+0</h3>
                <p><?php echo t('etudiants'); ?></p>
            </div>
            <div class="stat-box">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3 class="stat-number" data-target="<?php echo dc('stat_teachers'); ?>">+0</h3>
                <p><?php echo t('enseignants'); ?></p>
            </div>
        </div>
        <div class="stat-pair">
            <div class="stat-box">
                <i class="fas fa-sitemap"></i>
                <h3 class="stat-number" data-target="<?php echo dc('stat_majors'); ?>">+0</h3>
                <p><?php echo t('filieres'); ?></p>
            </div>
            <div class="stat-box">
                <i class="fas fa-eye"></i>
                <h3 class="stat-number" data-target="<?php echo (int) $homepage_views; ?>">+0</h3>
                <p><?php echo t('nombre_de_vues'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Section Mot du Directeur -->
<section class="director-section">
    <?php include 'background_animation.php'; ?>
    <div class="container">
        <div class="director-image">
            <img src="<?php echo dc('directeur_image_path'); ?>" alt="Photo du Directeur de l'ISSTM" loading="lazy">
        </div>
        <div class="director-speech">
            <h2><?php echo t('mot_directeur_titre'); ?></h2>
            <blockquote>"<?php echo dc('mot_directeur_contenu'); ?>"</blockquote>
            <p class="director-name"><?php echo dc('directeur_nom'); ?><br><?php echo t('directeur_poste'); ?></p>
        </div>
    </div>
</section>

<!-- Section Mission & Vision -->
<section class="mission-vision-section-new">
    <div class="feature-block mission-block" style="background-image: url('<?php echo dc('mission_image_path'); ?>');">
        <div class="container">
            <div class="feature-content text-left slide-in-left">
                <h3><?php echo t('notre_mission'); ?></h3>
                <p><?php echo dc('mission_contenu'); ?></p>
            </div>
        </div>
    </div>
    <div class="feature-block vision-block" style="background-image: url('<?php echo dc('vision_image_path'); ?>');">
        <div class="container">
            <div class="feature-content text-right slide-in-right">
                <h3><?php echo t('notre_vision'); ?></h3>
                <p><?php echo dc('vision_contenu'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Section Carousel "Découvrir nos pages" -->
<section class="pages-carousel-section">
    <div class="container">
        <h2 class="section-title"><?php echo t('decouvrir_titre'); ?></h2>
        <p class="pages-carousel-subtitle"><?php echo t('decouvrir_soustitre'); ?></p>
    </div>
    <div class="pages-carousel-wrapper">
        <button class="carousel-nav carousel-prev" id="pagesCarouselPrev" aria-label="Précédent">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="pages-carousel-track" id="pagesCarouselTrack">
            <a href="historique.php" class="page-card">
                <div class="page-card-img" style="background-image: url('images/historique.jpg');"></div>
                <div class="page-card-content">
                    <h3><?php echo t('historiques'); ?></h3>
                    <p><?php echo t('carte_historique_desc'); ?></p>
                    <span class="page-card-btn"><?php echo t('voir_plus'); ?> <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <a href="parcours.php" class="page-card">
                <div class="page-card-img" style="background-image: url('images/parcours.jpg');"></div>
                <div class="page-card-content">
                    <h3><?php echo t('menu_organigramme'); ?></h3>
                    <p><?php echo t('carte_parcours_desc'); ?></p>
                    <span class="page-card-btn"><?php echo t('voir_plus'); ?> <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <a href="enseignants.php" class="page-card">
                <div class="page-card-img" style="background-image: url('images/prof.jpg');"></div>
                <div class="page-card-content">
                    <h3><?php echo t('enseignants'); ?></h3>
                    <p><?php echo t('carte_enseignants_desc'); ?></p>
                    <span class="page-card-btn"><?php echo t('voir_plus'); ?> <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <a href="inscription.php" class="page-card">
                <div class="page-card-img" style="background-image: url('images/ins.jpg');"></div>
                <div class="page-card-content">
                    <h3><?php echo t('form_inscription'); ?></h3>
                    <p><?php echo t('carte_inscription_desc'); ?></p>
                    <span class="page-card-btn"><?php echo t('voir_plus'); ?> <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <a href="bourse.php" class="page-card">
                <div class="page-card-img" style="background-image: url('images/bourse.jpg');"></div>
                <div class="page-card-content">
                    <h3><?php echo t('bourse'); ?></h3>
                    <p><?php echo t('carte_bourse_desc'); ?></p>
                    <span class="page-card-btn"><?php echo t('voir_plus'); ?> <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <a href="vie_etudiante.php" class="page-card">
                <div class="page-card-img" style="background-image: url('images/salle.jpg');"></div>
                <div class="page-card-content">
                    <h3><?php echo t('vie_etudiante'); ?></h3>
                    <p><?php echo t('carte_vie_etudiante_desc'); ?></p>
                    <span class="page-card-btn"><?php echo t('voir_plus'); ?> <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <a href="campus.php" class="page-card">
                <div class="page-card-img" style="background-image: url('images/portal_campus_1.jpg');"></div>
                <div class="page-card-content">
                    <h3>Campus</h3>
                    <p><?php echo t('carte_campus_desc'); ?></p>
                    <span class="page-card-btn"><?php echo t('voir_plus'); ?> <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <a href="associations.php" class="page-card">
                <div class="page-card-img" style="background-image: url('images/portal_assoc_4.jpg');"></div>
                <div class="page-card-content">
                    <h3>Associations</h3>
                    <p><?php echo t('carte_associations_desc'); ?></p>
                    <span class="page-card-btn"><?php echo t('voir_plus'); ?> <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <a href="bibliotheque/index.php" class="page-card">
                <div class="page-card-img" style="background-image: url('bibliotheque/uploads/carousel/slide_isstm_07.jpg');"></div>
                <div class="page-card-content">
                    <h3><?php echo t('bib_nav_titre'); ?></h3>
                    <p><?php echo t('carte_bibliotheque_desc'); ?></p>
                    <span class="page-card-btn"><?php echo t('voir_plus'); ?> <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <a href="galerie.php" class="page-card">
                <div class="page-card-img" style="background-image: url('<?php echo htmlspecialchars($galerie_cover); ?>');"></div>
                <div class="page-card-content">
                    <h3><?php echo t('galeries'); ?></h3>
                    <p><?php echo t('carte_galerie_desc'); ?></p>
                    <span class="page-card-btn"><?php echo t('voir_plus'); ?> <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <a href="documents.php" class="page-card">
                <div class="page-card-img page-card-img-icon"><i class="fas fa-file-lines"></i></div>
                <div class="page-card-content">
                    <h3><?php echo t('menu_documents'); ?></h3>
                    <p><?php echo t('carte_documents_desc'); ?></p>
                    <span class="page-card-btn"><?php echo t('voir_plus'); ?> <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
        </div>
        <button class="carousel-nav carousel-next" id="pagesCarouselNext" aria-label="Suivant">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</section>

<?php if (!empty($filieres_db)): ?>
<!-- Section Filières & Parcours : même affichage que le carrousel Actualités (le détail et les filtres complets sont sur filieres.php) -->
<section class="news-hero-section" id="filieres">
    <div class="container news-carousel-header">
        <h2 class="section-title"><i class="fas fa-graduation-cap"></i> <?php echo t('filieres_section_titre'); ?></h2>
        <a href="filieres.php" class="news-carousel-viewall"><?php echo t('filieres_voir_toutes'); ?> <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="news-hero-carousel" id="filieres-hero-carousel">
        <?php foreach ($filieres_db as $i => $f):
            $nom = !empty($f['nom_' . $lang]) ? $f['nom_' . $lang] : $f['nom_fr'];
            $desc = !empty($f['description_' . $lang]) ? $f['description_' . $lang] : $f['description_fr'];
        ?>
            <div class="news-hero-slide <?php echo $i === 0 ? 'active' : ''; ?>">
                <img src="<?php echo htmlspecialchars($f['image_path'] ?: 'images/logo-isstm.jpg'); ?>" alt="<?php echo htmlspecialchars($nom); ?>" loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
                <div class="news-hero-overlay">
                    <div class="news-hero-text">
                        <?php if (!empty($f['mention'])): ?><span class="news-carousel-cat"><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($f['mention']); ?></span><?php endif; ?>
                        <h3><?php echo htmlspecialchars($nom); ?></h3>
                        <?php if ($desc): ?><p><?php echo htmlspecialchars(mb_strimwidth($desc, 0, 160, '…')); ?></p><?php endif; ?>
                        <a href="filiere_detail.php?slug=<?php echo urlencode($f['slug']); ?>" class="btn btn-primary"><?php echo t('en_savoir_plus'); ?> <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (count($filieres_db) > 1): ?>
            <button type="button" class="news-hero-arrow news-hero-prev" id="filieres-hero-prev" aria-label="Filière précédente"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="news-hero-arrow news-hero-next" id="filieres-hero-next" aria-label="Filière suivante"><i class="fas fa-chevron-right"></i></button>
            <div class="news-hero-dots" id="filieres-hero-dots">
                <?php foreach ($filieres_db as $i => $f): ?>
                    <button type="button" class="news-hero-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>" aria-label="Aller à la filière <?php echo $i + 1; ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php if (count($filieres_db) > 1): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.getElementById('filieres-hero-carousel');
    if (!carousel) return;
    const slides = Array.from(carousel.querySelectorAll('.news-hero-slide'));
    const dots = Array.from(carousel.querySelectorAll('.news-hero-dot'));
    const prevBtn = document.getElementById('filieres-hero-prev');
    const nextBtn = document.getElementById('filieres-hero-next');
    let current = 0;
    let timer = null;
    const DELAY = 2000;

    function goTo(index) {
        slides[current].classList.remove('active');
        dots[current]?.classList.remove('active');
        current = (index + slides.length) % slides.length;
        slides[current].classList.add('active');
        dots[current]?.classList.add('active');
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function startAuto() {
        stopAuto();
        timer = setInterval(next, DELAY);
    }
    function stopAuto() {
        if (timer) { clearInterval(timer); timer = null; }
    }

    nextBtn?.addEventListener('click', () => { next(); startAuto(); });
    prevBtn?.addEventListener('click', () => { prev(); startAuto(); });
    dots.forEach(dot => {
        dot.addEventListener('click', () => { goTo(parseInt(dot.dataset.index, 10)); startAuto(); });
    });

    carousel.addEventListener('mouseenter', stopAuto);
    carousel.addEventListener('mouseleave', startAuto);

    startAuto();
});
</script>
<?php endif; ?>
<?php endif; ?>

<!-- Section Témoignages -->
<section class="testimonials-section">
    <div class="container">
        <h2 class="section-title"><?php echo t('paroles_etudiants'); ?></h2>
        <div class="testimonial-showcase" id="testimonial-showcase" data-bg-index="0">
            <div class="testimonial-photo-stack" id="testimonial-photo-stack">
                <?php foreach ($testimonials_db as $index => $testimonial): ?>
                    <div class="testimonial-photo-card" data-index="<?php echo $index; ?>">
                        <img src="<?php echo htmlspecialchars($testimonial['image_path']); ?>" alt="Photo de <?php echo htmlspecialchars($testimonial['author_name']); ?>" loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="testimonial-text-panel">
                <i class="fas fa-quote-left testimonial-quote-icon" aria-hidden="true"></i>
                <?php foreach ($testimonials_db as $index => $testimonial): ?>
                    <div class="testimonial-text-slide <?php if ($index === 0) echo 'active'; ?>" data-index="<?php echo $index; ?>">
                        <blockquote>"<?php echo htmlspecialchars($testimonial['quote_' . $lang] ?? $testimonial['quote_fr']); ?>"</blockquote>
                        <p class="testimonial-author"><?php echo htmlspecialchars($testimonial['author_name']); ?></p>
                        <p class="testimonial-program"><?php echo htmlspecialchars($testimonial['program']); ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (count($testimonials_db) > 1): ?>
                    <div class="testimonial-dots" id="testimonial-dots">
                        <?php foreach ($testimonials_db as $index => $testimonial): ?>
                            <button type="button" class="testimonial-dot <?php if ($index === 0) echo 'active'; ?>" data-index="<?php echo $index; ?>" aria-label="Témoignage <?php echo $index + 1; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($news_carousel_slides)): ?>
<!-- Grand carrousel plein écran des actualités récentes (du plus récent au plus ancien) -->
<section class="news-hero-section">
    <div class="container news-carousel-header">
        <h2 class="section-title"><i class="fas fa-bullhorn"></i> <?php echo t('actualites_page_titre'); ?></h2>
        <a href="actualite.php" class="news-carousel-viewall">Voir toutes les actualités <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="news-hero-carousel" id="news-hero-carousel">
        <?php foreach ($news_carousel_slides as $i => $n):
            $t_title = $n['title_' . $lang] ?: $n['title_fr'];
            $t_excerpt = $n['excerpt_' . $lang] ?: $n['excerpt_fr'];
            $t_cat = $n['cat_name_' . ($lang === 'en' || $lang === 'mg' ? $lang : 'fr')] ?? $n['cat_name_fr'];
        ?>
            <div class="news-hero-slide <?php echo $i === 0 ? 'active' : ''; ?>">
                <img src="<?php echo htmlspecialchars($n['image_path'] ?: 'images/logo-isstm.jpg'); ?>" alt="<?php echo htmlspecialchars($t_title); ?>" loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
                <div class="news-hero-overlay">
                    <div class="news-hero-text">
                        <?php if ($t_cat): ?><span class="news-carousel-cat"><?php if (!empty($n['cat_icon'])): ?><i class="fas <?php echo htmlspecialchars($n['cat_icon']); ?>"></i><?php endif; ?> <?php echo htmlspecialchars($t_cat); ?></span><?php endif; ?>
                        <h3><?php echo htmlspecialchars($t_title); ?></h3>
                        <?php if ($t_excerpt): ?><p><?php echo htmlspecialchars($t_excerpt); ?></p><?php endif; ?>
                        <div class="news-hero-meta">
                            <?php if ($n['published_at']): ?><span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($n['published_at'])); ?></span><?php endif; ?>
                        </div>
                        <a href="actualite_article.php?slug=<?php echo urlencode($n['slug']); ?>" class="btn btn-primary"><?php echo t('en_savoir_plus'); ?> <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (count($news_carousel_slides) > 1): ?>
            <button type="button" class="news-hero-arrow news-hero-prev" id="news-hero-prev" aria-label="Actualité précédente"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="news-hero-arrow news-hero-next" id="news-hero-next" aria-label="Actualité suivante"><i class="fas fa-chevron-right"></i></button>
            <div class="news-hero-dots" id="news-hero-dots">
                <?php foreach ($news_carousel_slides as $i => $n): ?>
                    <button type="button" class="news-hero-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>" aria-label="Aller à l'actualité <?php echo $i + 1; ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($events_home)):
    $events_home_mois = ['fr' => ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'],
        'en' => ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        'mg' => ['Jan','Feb','Mar','Apr','Mey','Jon','Jol','Aog','Sep','Okt','Nov','Des']];
    $eh_mois = $events_home_mois[$lang] ?? $events_home_mois['fr'];
    $eh_category_icons = ['general' => 'fa-calendar-day', 'examen' => 'fa-file-pen', 'ceremonie' => 'fa-graduation-cap',
        'atelier' => 'fa-chalkboard-user', 'vacances' => 'fa-umbrella-beach', 'inscription' => 'fa-user-plus'];
    $eh_category_colors = ['general' => '#003366', 'examen' => '#c0392b', 'ceremonie' => '#8e44ad',
        'atelier' => '#2980b9', 'vacances' => '#2ecc71', 'inscription' => '#d4a017'];
?>
<!-- Section Événements à venir -->
<section class="events-home-section">
    <div class="container">
        <h2 class="section-title"><i class="fas fa-calendar-days"></i> <?php echo t('evenements_a_venir_titre'); ?></h2>
        <div class="events-home-grid">
            <?php foreach ($events_home as $i => $e):
                $dt = new DateTime($e['date_debut']);
                $cat = $e['categorie'];
                $titre = ($e['titre_' . $lang] ?? '') ?: $e['titre_fr'];
            ?>
                <a href="evenements.php" class="event-home-card animate-on-scroll" style="--evt-color: <?php echo $eh_category_colors[$cat] ?? '#003366'; ?>; transition-delay: <?php echo $i * 0.08; ?>s;">
                    <div class="event-home-card-img" <?php if (!empty($e['image_path'])): ?>style="background-image:url('<?php echo htmlspecialchars($e['image_path']); ?>');"<?php endif; ?>>
                        <div class="event-home-date-badge">
                            <strong><?php echo $dt->format('d'); ?></strong>
                            <span><?php echo $eh_mois[(int) $dt->format('n') - 1]; ?></span>
                        </div>
                    </div>
                    <div class="event-home-body">
                        <span class="event-home-cat"><i class="fas <?php echo $eh_category_icons[$cat] ?? 'fa-calendar-day'; ?>"></i> <?php echo t('admin_evenements_categorie_' . $cat); ?></span>
                        <h3><?php echo htmlspecialchars($titre); ?></h3>
                        <?php if (!empty($e['lieu'])): ?><p class="event-home-lieu"><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($e['lieu']); ?></p><?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="news-carousel-header" style="margin-top: 26px; justify-content: center;">
            <a href="evenements.php" class="news-carousel-viewall"><?php echo t('evenements_voir_tout'); ?> <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($partenaires_db)): ?>
<!-- Section Partenaires -->
<section class="partenaires-section" id="partenaires">
    <div class="container">
        <h2 class="section-title"><?php echo t('partenaires_section_titre'); ?></h2>
        <p class="filieres-section-subtitle"><?php echo t('partenaires_section_soustitre'); ?></p>
    </div>
    <div class="partenaires-marquee">
        <div class="partenaires-track">
            <?php foreach (array_merge($partenaires_db, $partenaires_db) as $p): ?>
                <a href="<?php echo htmlspecialchars($p['site_url']); ?>" target="_blank" rel="noopener" class="partenaire-logo" title="<?php echo htmlspecialchars($p['nom']); ?>">
                    <img src="<?php echo htmlspecialchars($p['logo_path']); ?>" alt="<?php echo htmlspecialchars($p['nom']); ?>" loading="lazy">
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Section Contact -->
<section class="contact-home-section" id="contact">
    <div class="container">
        <h2 class="section-title"><?php echo t('contact_section_titre'); ?></h2>
        <p class="filieres-section-subtitle"><?php echo t('contact_section_soustitre'); ?></p>
        <?php
            $home_contact_email = dc_footer('contact_email');
            $home_contact_tels = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', dc_footer('contact_telephone')))));
            $home_contact_fb = dc_footer('contact_facebook');
        ?>
        <div class="contact-home-grid">
            <a href="mailto:<?php echo $home_contact_email; ?>" class="contact-home-card">
                <i class="fas fa-envelope"></i>
                <h4><?php echo t('email'); ?></h4>
                <p><?php echo $home_contact_email; ?></p>
            </a>
            <div class="contact-home-card contact-home-card-tel">
                <i class="fas fa-phone"></i>
                <h4><?php echo t('telephone'); ?></h4>
                <?php foreach ($home_contact_tels as $home_tel): ?>
                    <p><a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $home_tel); ?>"><?php echo $home_tel; ?></a></p>
                <?php endforeach; ?>
            </div>
            <div class="contact-home-card">
                <i class="fas fa-location-dot"></i>
                <h4><?php echo dc_footer('contact_adresse'); ?></h4>
                <p><?php echo dc_footer('contact_adresse_detail'); ?></p>
            </div>
            <a href="<?php echo $home_contact_fb; ?>" target="_blank" rel="noopener" class="contact-home-card">
                <i class="fab fa-facebook-f"></i>
                <h4>Facebook</h4>
                <p>@isstm.umg</p>
            </a>
        </div>
    </div>
</section>

<!-- Section Localisation -->
<section class="map-section">
    <div class="container">
        <h2 class="section-title"><?php echo t('localisation'); ?></h2>
        <div class="maps-container">
            <div class="map-wrapper">
                <h3><?php echo t('localisation_principale'); ?></h3>
                <iframe
                    src="https://www.google.com/maps?q=-15.702528,46.353861(ISSTM+-+Campus+Principal)&amp;hl=fr&amp;z=17&amp;t=k&amp;output=embed"
                    width="100%"
                    height="400"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
                <div class="map-links-row">
                    <a class="map-external-link" href="https://www.google.com/maps?q=-15.702528,46.353861&z=17&t=k" target="_blank" rel="noopener"><i class="fas fa-up-right-from-square"></i> <?php echo t('ouvrir_google_maps'); ?></a>
                    <a class="map-directions-link" href="https://www.google.com/maps/dir/?api=1&destination=-15.702528,46.353861" target="_blank" rel="noopener"><i class="fas fa-diamond-turn-right"></i> <?php echo t('itineraire'); ?></a>
                </div>
            </div>
            <div class="map-wrapper">
                <h3><?php echo t('localisation_annexe_titre'); ?></h3>
                <iframe
                    src="https://www.google.com/maps?q=-15.72335804693739,46.31172101165267(ISSTM+-+Campus+Majunga+Be)&amp;hl=fr&amp;z=17&amp;t=k&amp;output=embed"
                    width="100%"
                    height="400"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
                <div class="map-links-row">
                    <a class="map-external-link" href="https://www.google.com/maps?q=-15.72335804693739,46.31172101165267&z=17&t=k" target="_blank" rel="noopener"><i class="fas fa-up-right-from-square"></i> <?php echo t('ouvrir_google_maps'); ?></a>
                    <a class="map-directions-link" href="https://www.google.com/maps/dir/?api=1&destination=-15.72335804693739,46.31172101165267" target="_blank" rel="noopener"><i class="fas fa-diamond-turn-right"></i> <?php echo t('itineraire'); ?></a>
                </div>
                <a class="map-history-link" href="historique.php"><i class="fas fa-landmark-flag"></i> <?php echo t('localisation_annexe_historique'); ?></a>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($news_carousel_slides) && count($news_carousel_slides) > 1): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.getElementById('news-hero-carousel');
    if (!carousel) return;
    const slides = Array.from(carousel.querySelectorAll('.news-hero-slide'));
    const dots = Array.from(carousel.querySelectorAll('.news-hero-dot'));
    const prevBtn = document.getElementById('news-hero-prev');
    const nextBtn = document.getElementById('news-hero-next');
    let current = 0;
    let timer = null;
    const DELAY = 2000;

    function goTo(index) {
        slides[current].classList.remove('active');
        dots[current]?.classList.remove('active');
        current = (index + slides.length) % slides.length;
        slides[current].classList.add('active');
        dots[current]?.classList.add('active');
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function startAuto() {
        stopAuto();
        timer = setInterval(next, DELAY);
    }
    function stopAuto() {
        if (timer) { clearInterval(timer); timer = null; }
    }

    nextBtn?.addEventListener('click', () => { next(); startAuto(); });
    prevBtn?.addEventListener('click', () => { prev(); startAuto(); });
    dots.forEach(dot => {
        dot.addEventListener('click', () => { goTo(parseInt(dot.dataset.index, 10)); startAuto(); });
    });

    carousel.addEventListener('mouseenter', stopAuto);
    carousel.addEventListener('mouseleave', startAuto);

    startAuto();
});
</script>
<?php endif; ?>

<?php include 'footer.php'; ?>
