<?php
include_once '../../language.php';
require_once '../../db_connect.php';
require '../config.php';
require '../functions.php';
requireAdmin();

$page_title = t('bib_gerer_canevas_titre');

if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $stmt = $pdo->prepare("SELECT * FROM canevas WHERE id = ?");
    $stmt->execute([$id]);
    $c = $stmt->fetch();

    if ($c) {
        $chemin = __DIR__ . '/../uploads/' . $c['chemin_fichier'];
        if (file_exists($chemin)) {
            unlink($chemin);
        }
        $stmt = $pdo->prepare("DELETE FROM canevas WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: gerer_canevas.php');
    exit;
}

$stmt = $pdo->query("SELECT c.*, a.libelle AS annee
                      FROM canevas c
                      JOIN annees_universitaires a ON a.id = c.annee_id
                      ORDER BY c.date_ajout DESC");
$canevas = $stmt->fetchAll();

require '../../header.php';
?>

<a href="dashboard.php" class="btn-outline btn-back-sticky"><i class="fas fa-arrow-left"></i> <?php echo t('admin_retour_tableau_bord'); ?></a>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="dashboard.php"><?php echo t('bib_admin_dashboard_titre'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('bib_gerer_canevas_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-folder-open"></i> <?php echo t('bib_gerer_canevas_titre'); ?></h1>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <div class="admin-galerie-stats">
            <div class="admin-stat-card"><i class="fas fa-file-lines"></i><div><strong><?php echo count($canevas); ?></strong><span><?php echo t('bib_stat_canevas'); ?></span></div></div>
        </div>

        <div class="admin-galerie-toolbar">
            <a href="ajouter_canevas.php" class="btn-add-item"><i class="fas fa-plus"></i> <?php echo t('bib_ajouter_canevas_titre'); ?></a>
            <div class="bib-quick-filter" data-target="#bib-liste-canevas">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" placeholder="<?php echo t('bib_rechercher_canevas_placeholder'); ?>">
            </div>
        </div>

        <?php if (!$canevas): ?>
            <p class="gallery-empty"><i class="fas fa-file-circle-xmark"></i> <?php echo t('bib_aucun_canevas'); ?></p>
        <?php else: ?>
            <div class="admin-album-list" id="bib-liste-canevas">
                <?php foreach ($canevas as $c): ?>
                    <div class="admin-album-row">
                        <div class="admin-album-row-thumb bib-row-icon"><i class="fas <?php echo iconeFichier($c['type_fichier']); ?>"></i></div>
                        <div class="admin-album-row-info">
                            <h4><?php echo e($c['titre']); ?></h4>
                            <div class="admin-album-row-meta">
                                <span><i class="fas fa-graduation-cap"></i> <?php echo e($c['niveau']); ?></span>
                                <span><i class="fas fa-calendar"></i> <?php echo e($c['annee']); ?></span>
                                <span><i class="fas fa-file"></i> <?php echo strtoupper(e($c['type_fichier'])); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo date('d/m/Y', strtotime($c['date_ajout'])); ?></span>
                            </div>
                        </div>
                        <div class="admin-album-row-actions">
                            <a href="../telecharger.php?id=<?php echo $c['id']; ?>" class="btn-outline" title="<?php echo t('bib_telecharger'); ?>"><i class="fas fa-download"></i></a>
                            <form action="gerer_canevas.php" method="get" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('bib_confirmer_suppression_canevas')); ?>">
                                <input type="hidden" name="supprimer" value="<?php echo $c['id']; ?>">
                                <button type="submit" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require '../../footer.php'; ?>
