<?php
include_once '../language.php';
require_once '../db_connect.php';
require 'config.php';
require 'functions.php';

$page_title = t('bib_memoires_titre');

$filieres = $pdo->query("SELECT f.*, m.nom AS mention_nom, m.abreviation AS mention_abrev
                          FROM filieres f
                          JOIN mentions m ON m.id = f.mention_id
                          ORDER BY m.nom, f.niveau, f.nom")->fetchAll();
$annees = $pdo->query("SELECT * FROM annees_universitaires ORDER BY libelle DESC")->fetchAll();

$categorie = $_GET['categorie'] ?? '';
$filiere_id = $_GET['filiere'] ?? '';
$annee_id = $_GET['annee'] ?? '';

$sql = "SELECT m.*, f.nom AS filiere_nom, f.abreviation AS filiere_abrev, f.niveau,
               men.nom AS mention_nom, men.abreviation AS mention_abrev,
               a.libelle AS annee
        FROM memoires m
        JOIN filieres f ON f.id = m.filiere_id
        JOIN mentions men ON men.id = f.mention_id
        JOIN annees_universitaires a ON a.id = m.annee_id
        WHERE 1=1";
$params = [];

if ($categorie !== '') {
    $sql .= " AND m.categorie = :categorie";
    $params['categorie'] = $categorie;
}
if ($filiere_id !== '') {
    $sql .= " AND m.filiere_id = :filiere_id";
    $params['filiere_id'] = $filiere_id;
}
if ($annee_id !== '') {
    $sql .= " AND m.annee_id = :annee_id";
    $params['annee_id'] = $annee_id;
}
$sql .= " ORDER BY a.libelle DESC, f.nom, m.titre";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$memoires = $stmt->fetchAll();

require '../header.php';
?>

<div class="page-banner bib-page-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php"><?php echo t('bib_nav_titre'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('bib_memoires_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-graduation-cap"></i> <?php echo t('bib_memoires_titre'); ?></h1>
        <p><?php echo t('bib_memoires_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <form class="bib-filtres" method="get">
            <select name="categorie">
                <option value=""><?php echo t('bib_memoire_et_projet'); ?></option>
                <option value="Mémoire" <?php echo $categorie === 'Mémoire' ? 'selected' : ''; ?>><?php echo t('bib_memoire'); ?></option>
                <option value="Projet" <?php echo $categorie === 'Projet' ? 'selected' : ''; ?>><?php echo t('bib_projet'); ?></option>
            </select>

            <select name="filiere">
                <option value=""><?php echo t('bib_toutes_filieres'); ?></option>
                <?php $mentionCourante = null; foreach ($filieres as $f): ?>
                    <?php if ($mentionCourante !== $f['mention_nom']): ?>
                        <?php if ($mentionCourante !== null) echo '</optgroup>'; ?>
                        <optgroup label="<?php echo e($f['mention_nom']); ?> (<?php echo e($f['mention_abrev']); ?>)">
                        <?php $mentionCourante = $f['mention_nom']; ?>
                    <?php endif; ?>
                    <option value="<?php echo $f['id']; ?>" <?php echo $filiere_id == $f['id'] ? 'selected' : ''; ?>>
                        <?php echo e($f['niveau']); ?> - <?php echo e($f['nom']); ?> (<?php echo e($f['abreviation']); ?>)
                    </option>
                <?php endforeach; if ($mentionCourante !== null) echo '</optgroup>'; ?>
            </select>

            <select name="annee">
                <option value=""><?php echo t('bib_toutes_annees'); ?></option>
                <?php foreach ($annees as $a): ?>
                    <option value="<?php echo $a['id']; ?>" <?php echo $annee_id == $a['id'] ? 'selected' : ''; ?>>
                        <?php echo e($a['libelle']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-submit"><i class="fas fa-filter"></i> <?php echo t('bib_filtrer'); ?></button>
        </form>

        <div class="dashboard-actions bib-cards-grid">
            <?php foreach ($memoires as $m): ?>
                <div class="action-card bib-doc-card">
                    <div class="action-card-icon"><i class="fas fa-file-pdf"></i></div>
                    <span class="bib-badge bib-badge-<?php echo $m['categorie'] === 'Mémoire' ? 'memoire' : 'projet'; ?>"><?php echo e($m['categorie']); ?></span>
                    <h3><?php echo e($m['titre']); ?></h3>
                    <p>
                        <strong><?php echo t('bib_auteur'); ?> :</strong> <?php echo e($m['auteur']); ?><br>
                        <?php if ($m['encadreur']): ?><strong><?php echo t('bib_encadreur'); ?> :</strong> <?php echo e($m['encadreur']); ?><br><?php endif; ?>
                        <?php echo e($m['niveau']); ?> - <?php echo e($m['filiere_nom']); ?> (<?php echo e($m['filiere_abrev']); ?>)<br>
                        <?php echo t('bib_mention'); ?> : <?php echo e($m['mention_abrev']); ?><br>
                        <?php echo t('bib_annee'); ?> : <?php echo e($m['annee']); ?>
                    </p>
                    <?php if ($m['resume']): ?>
                        <p class="bib-resume"><?php echo e(mb_strimwidth($m['resume'], 0, 120, '...')); ?></p>
                    <?php endif; ?>
                    <?php if (estUtilisateurConnecte()): ?>
                        <a class="action-card-cta" href="consulter_memoire.php?id=<?php echo $m['id']; ?>" target="_blank" rel="noopener">
                            <i class="fas fa-book-open"></i> <?php echo t('bib_consulter_en_ligne'); ?>
                        </a>
                    <?php else: ?>
                        <a class="action-card-cta bib-cta-locked" href="<?php echo SITE_URL; ?>/login.php"><i class="fas fa-lock"></i> <?php echo t('bib_connexion_requise'); ?></a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (!$memoires): ?>
                <p class="gallery-empty"><i class="fas fa-file-circle-xmark"></i> <?php echo t('bib_aucun_memoire_filtre'); ?></p>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require '../footer.php'; ?>
