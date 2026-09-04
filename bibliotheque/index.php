<?php
include_once '../language.php';
require_once '../db_connect.php';
require 'config.php';
require 'functions.php';

$page_title = t('bib_nav_titre');

// Images du carrousel d'accueil (public, sans connexion requise)
$carousel = $pdo->query("SELECT * FROM carousel_images ORDER BY ordre, id")->fetchAll();

// Horaire d'ouverture et dernières actualités liées : réservés aux comptes connectés,
// comme la consultation/le téléchargement des canevas et mémoires.
$jours_ordre = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
$jours_traduction = [
    'Lundi' => 'bib_jour_lundi', 'Mardi' => 'bib_jour_mardi', 'Mercredi' => 'bib_jour_mercredi',
    'Jeudi' => 'bib_jour_jeudi', 'Vendredi' => 'bib_jour_vendredi', 'Samedi' => 'bib_jour_samedi',
    'Dimanche' => 'bib_jour_dimanche',
];
$horaires = [];
$actualites = [];
$photosParActualite = [];

if (estUtilisateurConnecte()) {
    $horaires = $pdo->query("SELECT * FROM horaires")->fetchAll(PDO::FETCH_ASSOC);
    usort($horaires, fn($a, $b) => array_search($a['jour'], $jours_ordre) <=> array_search($b['jour'], $jours_ordre));

    $stmt = $pdo->query("SELECT * FROM actualites_horaire
                          WHERE date_fin IS NULL OR date_fin >= CURDATE()
                          ORDER BY date_publication DESC LIMIT 3");
    $actualites = $stmt->fetchAll();

    if ($actualites) {
        $ids = implode(',', array_map('intval', array_column($actualites, 'id')));
        foreach ($pdo->query("SELECT * FROM actualite_photos WHERE actualite_id IN ($ids) ORDER BY id")->fetchAll() as $p) {
            $photosParActualite[$p['actualite_id']][] = $p;
        }
    }
}

// Derniers canevas ajoutés
$stmt = $pdo->query("SELECT c.*, a.libelle AS annee
                      FROM canevas c
                      JOIN annees_universitaires a ON a.id = c.annee_id
                      ORDER BY c.date_ajout DESC LIMIT 5");
$derniers_canevas = $stmt->fetchAll();

// Derniers mémoires/projets ajoutés
$stmt = $pdo->query("SELECT m.*, f.nom AS filiere_nom, f.abreviation AS filiere_abrev, f.niveau,
                             men.abreviation AS mention_abrev, a.libelle AS annee
                      FROM memoires m
                      JOIN filieres f ON f.id = m.filiere_id
                      JOIN mentions men ON men.id = f.mention_id
                      JOIN annees_universitaires a ON a.id = m.annee_id
                      ORDER BY m.date_ajout DESC LIMIT 5");
$derniers_memoires = $stmt->fetchAll();

require '../header.php';
?>

<div class="page-banner bib-page-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('bib_nav_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-book-open"></i> <?php echo t('bib_nav_titre'); ?></h1>
        <p><?php echo t('bib_accueil_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">
        <div class="intro-with-image">
            <div class="intro-image">
                <img src="uploads/bibli2.png" alt="<?php echo t('bib_nav_titre'); ?>">
            </div>
            <div class="page-intro-text">
                <p><?php echo t('bib_intro_phrase'); ?></p>
            </div>
        </div>
    </div>
</div>

<?php if ($carousel): ?>
<section class="news-hero-section">
    <div class="news-hero-carousel" id="bib-hero-carousel">
        <?php foreach ($carousel as $i => $img): ?>
            <div class="news-hero-slide <?php echo $i === 0 ? 'active' : ''; ?>">
                <img src="uploads/carousel/<?php echo e($img['chemin_fichier']); ?>" alt="<?php echo e($img['legende'] ?? ''); ?>" loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
                <?php if ($img['legende']): ?>
                    <div class="news-hero-overlay">
                        <div class="news-hero-text">
                            <h3><?php echo e($img['legende']); ?></h3>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <?php if (count($carousel) > 1): ?>
            <button type="button" class="news-hero-arrow news-hero-prev" id="bib-hero-prev" aria-label="<?php echo t('bib_carousel_precedent'); ?>"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="news-hero-arrow news-hero-next" id="bib-hero-next" aria-label="<?php echo t('bib_carousel_suivant'); ?>"><i class="fas fa-chevron-right"></i></button>
            <div class="news-hero-dots" id="bib-hero-dots">
                <?php foreach ($carousel as $i => $img): ?>
                    <button type="button" class="news-hero-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>" aria-label="<?php echo t('bib_carousel_image'); ?> <?php echo $i + 1; ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php if (count($carousel) > 1): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.getElementById('bib-hero-carousel');
    if (!carousel) return;
    const slides = Array.from(carousel.querySelectorAll('.news-hero-slide'));
    const dots = Array.from(carousel.querySelectorAll('.news-hero-dot'));
    const prevBtn = document.getElementById('bib-hero-prev');
    const nextBtn = document.getElementById('bib-hero-next');
    let current = 0;
    let timer = null;
    const DELAY = 4000;

    function goTo(index) {
        slides[current].classList.remove('active');
        dots[current]?.classList.remove('active');
        current = (index + slides.length) % slides.length;
        slides[current].classList.add('active');
        dots[current]?.classList.add('active');
    }
    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }
    function startAuto() { stopAuto(); timer = setInterval(next, DELAY); }
    function stopAuto() { if (timer) { clearInterval(timer); timer = null; } }

    nextBtn?.addEventListener('click', () => { next(); startAuto(); });
    prevBtn?.addEventListener('click', () => { prev(); startAuto(); });
    dots.forEach(dot => dot.addEventListener('click', () => { goTo(parseInt(dot.dataset.index, 10)); startAuto(); }));
    carousel.addEventListener('mouseenter', stopAuto);
    carousel.addEventListener('mouseleave', startAuto);
    startAuto();
});
</script>
<?php endif; ?>
<?php endif; ?>

<section class="bib-intro-section">
    <div class="container bib-intro-grid">
        <div class="bib-intro-text">
            <h2><?php echo t('bib_intro_titre'); ?></h2>
            <p><?php echo t('bib_intro_texte_1'); ?></p>
            <p><?php echo t('bib_intro_texte_2'); ?></p>
            <?php if (!estUtilisateurConnecte()): ?>
                <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-primary bib-intro-cta"><i class="fas fa-right-to-bracket"></i> <?php echo t('bib_connexion_requise'); ?></a>
            <?php endif; ?>
        </div>
        <div class="bib-intro-image">
            <img src="uploads/bibli.png" alt="<?php echo t('bib_nav_titre'); ?>" loading="lazy">
        </div>
    </div>
</section>

<div class="page-content">
    <div class="container">

        <section class="bib-section">
            <h2><i class="fas fa-clock"></i> <?php echo t('bib_horaire_titre'); ?></h2>
            <?php if (estUtilisateurConnecte()): ?>

                <?php if ($actualites): ?>
                    <?php foreach ($actualites as $act): ?>
                        <div class="alerte-bib">
                            <strong><?php echo e($act['message']); ?></strong>
                            <span class="bib-date-actu">
                                (<?php echo t('bib_du'); ?> <?php echo date('d/m/Y', strtotime($act['date_debut'])); ?>
                                <?php echo $act['date_fin'] ? ' ' . t('bib_au') . ' ' . date('d/m/Y', strtotime($act['date_fin'])) : ''; ?>)
                            </span>
                            <?php if (!empty($photosParActualite[$act['id']])): ?>
                                <div class="bib-actu-photos">
                                    <?php foreach ($photosParActualite[$act['id']] as $p): ?>
                                        <img src="uploads/actualites/<?php echo e($p['chemin_fichier']); ?>" alt="" loading="lazy">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <table class="bib-table-horaire">
                    <thead>
                        <tr><th><?php echo t('bib_jour'); ?></th><th><?php echo t('bib_ouverture'); ?></th><th><?php echo t('bib_fermeture'); ?></th><th><?php echo t('bib_statut'); ?></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($horaires as $h): ?>
                            <tr class="<?php echo $h['actif'] ? '' : 'bib-jour-ferme'; ?>">
                                <td><?php echo t($jours_traduction[$h['jour']]); ?></td>
                                <td><?php echo $h['actif'] ? date('H:i', strtotime($h['heure_ouverture'])) : '-'; ?></td>
                                <td><?php echo $h['actif'] ? date('H:i', strtotime($h['heure_fermeture'])) : '-'; ?></td>
                                <td>
                                    <?php if ($h['actif']): ?>
                                        <i class="fas fa-circle-check bib-statut-ouvert"></i> <?php echo t('bib_ouvert'); ?>
                                    <?php else: ?>
                                        <i class="fas fa-circle-xmark bib-statut-ferme"></i> <?php echo t('bib_ferme'); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="horaire.php" class="news-carousel-viewall"><?php echo t('bib_voir_horaire_complet'); ?> <i class="fas fa-arrow-right"></i></a>

            <?php else: ?>
                <div class="bib-locked-teaser">
                    <p><i class="fas fa-lock"></i> <?php echo t('bib_horaire_verrouille'); ?></p>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-primary"><?php echo t('bib_connexion_requise'); ?></a>
                </div>
            <?php endif; ?>
        </section>

        <section class="bib-section">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:18px;">
                <h2 style="margin:0;"><i class="fas fa-file-lines"></i> <?php echo t('bib_derniers_canevas_titre'); ?></h2>
                <a href="canevas.php" class="news-carousel-viewall"><?php echo t('bib_voir_tous_canevas'); ?> <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="dashboard-actions bib-cards-grid">
                <?php foreach ($derniers_canevas as $c): ?>
                    <div class="action-card bib-doc-card">
                        <div class="action-card-icon"><i class="fas <?php echo iconeFichier($c['type_fichier']); ?>"></i></div>
                        <h3><?php echo e($c['titre']); ?></h3>
                        <p><?php echo e($c['niveau']); ?><br><?php echo t('bib_annee'); ?> : <?php echo e($c['annee']); ?></p>
                        <?php if (estUtilisateurConnecte()): ?>
                            <a class="action-card-cta" href="telecharger.php?id=<?php echo $c['id']; ?>"><?php echo t('bib_telecharger'); ?> <i class="fas fa-download"></i></a>
                        <?php else: ?>
                            <a class="action-card-cta bib-cta-locked" href="<?php echo SITE_URL; ?>/login.php"><i class="fas fa-lock"></i> <?php echo t('bib_connexion_requise'); ?></a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (!$derniers_canevas): ?>
                    <p class="gallery-empty"><i class="fas fa-file-circle-xmark"></i> <?php echo t('bib_aucun_canevas'); ?></p>
                <?php endif; ?>
            </div>
        </section>

        <section class="bib-section">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:18px;">
                <h2 style="margin:0;"><i class="fas fa-graduation-cap"></i> <?php echo t('bib_derniers_memoires_titre'); ?></h2>
                <a href="memoires.php" class="news-carousel-viewall"><?php echo t('bib_voir_tous_memoires'); ?> <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="dashboard-actions bib-cards-grid">
                <?php foreach ($derniers_memoires as $m): ?>
                    <div class="action-card bib-doc-card">
                        <div class="action-card-icon"><i class="fas fa-file-pdf"></i></div>
                        <span class="bib-badge bib-badge-<?php echo $m['categorie'] === 'Mémoire' ? 'memoire' : 'projet'; ?>"><?php echo e($m['categorie']); ?></span>
                        <h3><?php echo e($m['titre']); ?></h3>
                        <p><strong><?php echo t('bib_auteur'); ?> :</strong> <?php echo e($m['auteur']); ?><br>
                           <?php echo e($m['niveau']); ?> - <?php echo e($m['filiere_nom']); ?> (<?php echo e($m['filiere_abrev']); ?>)<br>
                           <?php echo t('bib_annee'); ?> : <?php echo e($m['annee']); ?></p>
                        <?php if (estUtilisateurConnecte()): ?>
                            <a class="action-card-cta" href="consulter_memoire.php?id=<?php echo $m['id']; ?>" target="_blank" rel="noopener"><i class="fas fa-book-open"></i> <?php echo t('bib_consulter_en_ligne'); ?></a>
                        <?php else: ?>
                            <a class="action-card-cta bib-cta-locked" href="<?php echo SITE_URL; ?>/login.php"><i class="fas fa-lock"></i> <?php echo t('bib_connexion_requise'); ?></a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (!$derniers_memoires): ?>
                    <p class="gallery-empty"><i class="fas fa-file-circle-xmark"></i> <?php echo t('bib_aucun_memoire'); ?></p>
                <?php endif; ?>
            </div>
        </section>

    </div>
</div>

<?php require '../footer.php'; ?>
