<?php
include_once '../../language.php';
require_once '../../db_connect.php';
require '../config.php';
require '../functions.php';
requireAdmin();

$page_title = t('bib_gerer_memoires_titre');

if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $stmt = $pdo->prepare("SELECT * FROM memoires WHERE id = ?");
    $stmt->execute([$id]);
    $m = $stmt->fetch();

    if ($m) {
        $chemin = __DIR__ . '/../uploads/' . $m['chemin_fichier'];
        if (file_exists($chemin)) {
            unlink($chemin);
        }
        $stmt = $pdo->prepare("DELETE FROM memoires WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: gerer_memoires.php');
    exit;
}

$stmt = $pdo->query("SELECT m.*, f.nom AS filiere_nom, f.abreviation AS filiere_abrev, f.niveau,
                             men.abreviation AS mention_abrev, a.libelle AS annee
                      FROM memoires m
                      JOIN filieres f ON f.id = m.filiere_id
                      JOIN mentions men ON men.id = f.mention_id
                      JOIN annees_universitaires a ON a.id = m.annee_id
                      ORDER BY m.date_ajout DESC");
$memoires = $stmt->fetchAll();

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
            <span><?php echo t('bib_gerer_memoires_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-book"></i> <?php echo t('bib_gerer_memoires_titre'); ?></h1>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <div class="admin-galerie-stats">
            <div class="admin-stat-card"><i class="fas fa-book"></i><div><strong><?php echo count($memoires); ?></strong><span><?php echo t('bib_stat_documents'); ?></span></div></div>
        </div>

        <div class="admin-galerie-toolbar">
            <a href="ajouter_memoire.php" class="btn-add-item"><i class="fas fa-plus"></i> <?php echo t('bib_ajouter_memoire_titre'); ?></a>
            <div class="bib-quick-filter" data-target="#bib-liste-memoires">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" placeholder="<?php echo t('bib_rechercher_memoire_placeholder'); ?>">
            </div>
        </div>

        <?php if (!$memoires): ?>
            <p class="gallery-empty"><i class="fas fa-file-circle-xmark"></i> <?php echo t('bib_aucun_memoire'); ?></p>
        <?php else: ?>
            <div class="admin-album-list" id="bib-liste-memoires">
                <?php foreach ($memoires as $m): ?>
                    <div class="admin-album-row">
                        <div class="admin-album-row-thumb bib-row-icon"><i class="fas fa-file-pdf"></i></div>
                        <div class="admin-album-row-info">
                            <h4>
                                <?php echo e($m['titre']); ?>
                                <span class="bib-badge bib-badge-<?php echo $m['categorie'] === 'Mémoire' ? 'memoire' : 'projet'; ?>"><?php echo e($m['categorie']); ?></span>
                            </h4>
                            <div class="admin-album-row-meta">
                                <span><i class="fas fa-user"></i> <?php echo e($m['auteur']); ?></span>
                                <span><i class="fas fa-school"></i> <?php echo e($m['niveau']); ?> - <?php echo e($m['filiere_nom']); ?> (<?php echo e($m['filiere_abrev']); ?>) — <?php echo e($m['mention_abrev']); ?></span>
                                <span><i class="fas fa-calendar"></i> <?php echo e($m['annee']); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo date('d/m/Y', strtotime($m['date_ajout'])); ?></span>
                            </div>
                        </div>
                        <div class="admin-album-row-actions">
                            <a href="../consulter_memoire.php?id=<?php echo $m['id']; ?>" target="_blank" class="btn-outline" title="<?php echo t('bib_consulter'); ?>"><i class="fas fa-eye"></i></a>
                            <form action="gerer_memoires.php" method="get" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('bib_confirmer_suppression_memoire')); ?>">
                                <input type="hidden" name="supprimer" value="<?php echo $m['id']; ?>">
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
