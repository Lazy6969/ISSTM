<?php
include_once '../language.php';
require_once '../db_connect.php';
require 'config.php';
require 'functions.php';

$page_title = t('bib_canevas_titre');

$annees = $pdo->query("SELECT * FROM annees_universitaires ORDER BY libelle DESC")->fetchAll();

$niveau = $_GET['niveau'] ?? '';
$annee_id = $_GET['annee'] ?? '';

$sql = "SELECT c.*, a.libelle AS annee
        FROM canevas c
        JOIN annees_universitaires a ON a.id = c.annee_id
        WHERE 1=1";
$params = [];

if ($niveau !== '') {
    $sql .= " AND c.niveau = :niveau";
    $params['niveau'] = $niveau;
}
if ($annee_id !== '') {
    $sql .= " AND c.annee_id = :annee_id";
    $params['annee_id'] = $annee_id;
}
$sql .= " ORDER BY a.libelle DESC, c.niveau, c.titre";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$canevas = $stmt->fetchAll();

require '../header.php';
?>

<div class="page-banner bib-page-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php"><?php echo t('bib_nav_titre'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('bib_canevas_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-file-lines"></i> <?php echo t('bib_canevas_titre'); ?></h1>
        <p><?php echo t('bib_canevas_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <form class="bib-filtres" method="get">
            <select name="niveau">
                <option value=""><?php echo t('bib_tous_niveaux'); ?></option>
                <option value="Licence" <?php echo $niveau === 'Licence' ? 'selected' : ''; ?>><?php echo t('bib_licence'); ?></option>
                <option value="Master" <?php echo $niveau === 'Master' ? 'selected' : ''; ?>><?php echo t('bib_master'); ?></option>
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
            <?php foreach ($canevas as $c): ?>
                <div class="action-card bib-doc-card">
                    <div class="action-card-icon"><i class="fas <?php echo iconeFichier($c['type_fichier']); ?>"></i></div>
                    <h3><?php echo e($c['titre']); ?></h3>
                    <p><?php echo e($c['niveau']); ?><br>
                       <?php echo t('bib_annee'); ?> : <?php echo e($c['annee']); ?><br>
                       <?php echo t('bib_format'); ?> : <?php echo strtoupper(e($c['type_fichier'])); ?></p>
                    <?php if (estUtilisateurConnecte()): ?>
                        <a class="action-card-cta" href="telecharger.php?id=<?php echo $c['id']; ?>"><?php echo t('bib_telecharger'); ?> <i class="fas fa-download"></i></a>
                    <?php else: ?>
                        <a class="action-card-cta bib-cta-locked" href="<?php echo SITE_URL; ?>/login.php"><i class="fas fa-lock"></i> <?php echo t('bib_connexion_requise'); ?></a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (!$canevas): ?>
                <p class="gallery-empty"><i class="fas fa-file-circle-xmark"></i> <?php echo t('bib_aucun_canevas_filtre'); ?></p>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require '../footer.php'; ?>
