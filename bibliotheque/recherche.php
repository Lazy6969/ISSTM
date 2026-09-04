<?php
include_once '../language.php';
require_once '../db_connect.php';
require 'config.php';
require 'functions.php';

$page_title = t('bib_recherche_titre');

$q = trim($_GET['q'] ?? '');
$canevasResultats = [];
$memoiresResultats = [];

if ($q !== '') {
    $sql = "SELECT c.*, a.libelle AS annee
            FROM canevas c
            JOIN annees_universitaires a ON a.id = c.annee_id
            WHERE c.titre LIKE :q
               OR c.niveau LIKE :q
               OR a.libelle LIKE :q
               OR c.contenu_texte LIKE :q
            ORDER BY c.titre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['q' => '%' . $q . '%']);
    $canevasResultats = $stmt->fetchAll();

    $sql = "SELECT m.*, f.nom AS filiere_nom, f.abreviation AS filiere_abrev, f.niveau,
                   men.abreviation AS mention_abrev, a.libelle AS annee
            FROM memoires m
            JOIN filieres f ON f.id = m.filiere_id
            JOIN mentions men ON men.id = f.mention_id
            JOIN annees_universitaires a ON a.id = m.annee_id
            WHERE m.titre LIKE :q
               OR m.auteur LIKE :q
               OR m.categorie LIKE :q
               OR m.resume LIKE :q
               OR f.nom LIKE :q
               OR f.abreviation LIKE :q
               OR f.niveau LIKE :q
               OR a.libelle LIKE :q
               OR m.contenu_texte LIKE :q
            ORDER BY m.titre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['q' => '%' . $q . '%']);
    $memoiresResultats = $stmt->fetchAll();
}

require '../header.php';
?>

<div class="page-banner bib-page-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php"><?php echo t('bib_nav_titre'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('bib_recherche_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-magnifying-glass"></i> <?php echo t('bib_resultats_pour'); ?> « <?php echo e($q); ?> »</h1>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <h2><i class="fas fa-file-lines"></i> <?php echo t('bib_canevas_titre'); ?> (<?php echo count($canevasResultats); ?>)</h2>
        <div class="dashboard-actions bib-cards-grid">
            <?php foreach ($canevasResultats as $c): ?>
                <div class="action-card bib-doc-card">
                    <div class="action-card-icon"><i class="fas <?php echo iconeFichier($c['type_fichier']); ?>"></i></div>
                    <h3><?php echo e($c['titre']); ?></h3>
                    <p><?php echo e($c['niveau']); ?><br>
                       <?php echo t('bib_annee'); ?> : <?php echo e($c['annee']); ?></p>
                    <?php if (estUtilisateurConnecte()): ?>
                        <a class="action-card-cta" href="telecharger.php?id=<?php echo $c['id']; ?>"><?php echo t('bib_telecharger'); ?> <i class="fas fa-download"></i></a>
                    <?php else: ?>
                        <a class="action-card-cta bib-cta-locked" href="<?php echo SITE_URL; ?>/login.php"><i class="fas fa-lock"></i> <?php echo t('bib_connexion_requise'); ?></a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (!$canevasResultats): ?>
                <p class="gallery-empty"><i class="fas fa-file-circle-xmark"></i> <?php echo t('bib_aucun_canevas_trouve'); ?></p>
            <?php endif; ?>
        </div>

        <h2><i class="fas fa-graduation-cap"></i> <?php echo t('bib_memoires_titre'); ?> (<?php echo count($memoiresResultats); ?>)</h2>
        <div class="dashboard-actions bib-cards-grid">
            <?php foreach ($memoiresResultats as $m): ?>
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
            <?php if (!$memoiresResultats): ?>
                <p class="gallery-empty"><i class="fas fa-file-circle-xmark"></i> <?php echo t('bib_aucun_memoire_trouve'); ?></p>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require '../footer.php'; ?>
