<?php
include_once '../../language.php';
require_once '../../db_connect.php';
require '../config.php';
require '../functions.php';
requireAdmin();

$page_title = t('bib_gerer_actualites_titre');
$erreur = '';
$succes = '';

$extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['supprimer'])) {
        $pdo->prepare("DELETE FROM actualites_horaire WHERE id = ?")->execute([$_POST['supprimer']]);
        // Les photos liées sont supprimées automatiquement (ON DELETE CASCADE),
        // mais on nettoie aussi les fichiers physiques.
        $stmt = $pdo->prepare("SELECT chemin_fichier FROM actualite_photos WHERE actualite_id = ?");
        $stmt->execute([$_POST['supprimer']]);
        foreach ($stmt->fetchAll() as $p) {
            $chemin = __DIR__ . '/../uploads/actualites/' . $p['chemin_fichier'];
            if (file_exists($chemin)) unlink($chemin);
        }
        $succes = t('bib_succes_actualite_supprimee');
    } elseif (isset($_POST['supprimer_photo'])) {
        $stmt = $pdo->prepare("SELECT * FROM actualite_photos WHERE id = ?");
        $stmt->execute([$_POST['supprimer_photo']]);
        $p = $stmt->fetch();
        if ($p) {
            $chemin = __DIR__ . '/../uploads/actualites/' . $p['chemin_fichier'];
            if (file_exists($chemin)) unlink($chemin);
            $pdo->prepare("DELETE FROM actualite_photos WHERE id = ?")->execute([$_POST['supprimer_photo']]);
        }
        $succes = t('bib_succes_photo_supprimee');
    } else {
        $message = trim($_POST['message'] ?? '');
        $date_debut = $_POST['date_debut'] ?? date('Y-m-d');
        $date_fin = $_POST['date_fin'] ?: null;

        if ($message === '') {
            $erreur = t('bib_erreur_message_requis');
        } else {
            $stmt = $pdo->prepare("INSERT INTO actualites_horaire (message, date_debut, date_fin) VALUES (?, ?, ?)");
            $stmt->execute([$message, $date_debut, $date_fin]);
            $actualite_id = $pdo->lastInsertId();

            // Une seule photo, ou plusieurs : le champ accepte "multiple"
            if (!empty($_FILES['photos']['name'][0])) {
                foreach ($_FILES['photos']['name'] as $i => $nomOriginal) {
                    if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) continue;

                    $extension = strtolower(pathinfo($nomOriginal, PATHINFO_EXTENSION));
                    if (!in_array($extension, $extensionsAutorisees)) continue;
                    if ($_FILES['photos']['size'][$i] > 8 * 1024 * 1024) continue;

                    $nomUnique = uniqid('actu_', true) . '.' . $extension;
                    $destination = __DIR__ . '/../uploads/actualites/' . $nomUnique;

                    if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $destination)) {
                        $pdo->prepare("INSERT INTO actualite_photos (actualite_id, chemin_fichier) VALUES (?, ?)")
                            ->execute([$actualite_id, $nomUnique]);
                    }
                }
            }
            $succes = t('bib_succes_actualite_publiee');
        }
    }
}

$actualites = $pdo->query("SELECT * FROM actualites_horaire ORDER BY date_publication DESC")->fetchAll();
$photosParActualite = [];
if ($actualites) {
    $stmt = $pdo->query("SELECT * FROM actualite_photos ORDER BY id");
    foreach ($stmt->fetchAll() as $p) {
        $photosParActualite[$p['actualite_id']][] = $p;
    }
}

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
            <span><?php echo t('bib_gerer_actualites_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-bullhorn"></i> <?php echo t('bib_gerer_actualites_titre'); ?></h1>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <?php if ($erreur): ?><div class="admin-flash admin-flash-error"><i class="fas fa-circle-exclamation"></i> <?php echo e($erreur); ?></div><?php endif; ?>
        <?php if ($succes): ?><div class="admin-flash admin-flash-success"><i class="fas fa-circle-check"></i> <?php echo e($succes); ?></div><?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="admin-form">
            <div class="album-form-grid">
                <div class="album-form-main">
                    <div class="form-group">
                        <label><?php echo t('bib_message_label'); ?></label>
                        <input type="text" name="message" placeholder="<?php echo t('bib_message_placeholder'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('bib_date_debut_label'); ?></label>
                        <input type="date" name="date_debut" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('bib_date_fin_optionnelle_label'); ?></label>
                        <input type="date" name="date_fin">
                    </div>
                </div>
                <div class="album-form-side">
                    <div class="form-group upload-zone-group">
                        <label><?php echo t('bib_photos_optionnelles_label'); ?></label>
                        <label class="upload-zone upload-zone-multi">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <span class="upload-zone-text"><?php echo t('bib_deposer_photos'); ?></span>
                            <span class="upload-zone-filename"></span>
                            <input type="file" name="photos[]" accept=".jpg,.jpeg,.png,.webp" class="upload-zone-input" multiple>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit"><i class="fas fa-bullhorn"></i> <?php echo t('bib_publier_actualite'); ?></button>
        </form>

        <h2 class="admin-section-title"><?php echo t('bib_actualites_existantes'); ?></h2>

        <?php if (!$actualites): ?>
            <p class="gallery-empty"><i class="fas fa-bullhorn"></i> <?php echo t('bib_aucune_actualite'); ?></p>
        <?php else: ?>
            <div class="admin-album-list">
                <?php foreach ($actualites as $a): ?>
                    <div class="admin-album-row bib-actu-row">
                        <div class="admin-album-row-info">
                            <h4><?php echo e($a['message']); ?></h4>
                            <div class="admin-album-row-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($a['date_debut'])); ?><?php echo $a['date_fin'] ? ' — ' . date('d/m/Y', strtotime($a['date_fin'])) : ''; ?></span>
                            </div>
                            <?php if (!empty($photosParActualite[$a['id']])): ?>
                                <div class="bib-actu-photos">
                                    <?php foreach ($photosParActualite[$a['id']] as $p): ?>
                                        <div class="bib-actu-photo-wrap">
                                            <img src="../uploads/actualites/<?php echo e($p['chemin_fichier']); ?>" alt="">
                                            <form method="post" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('bib_confirmer_suppression_photo')); ?>">
                                                <input type="hidden" name="supprimer_photo" value="<?php echo $p['id']; ?>">
                                                <button type="submit" class="bib-actu-photo-remove" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-xmark"></i></button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="admin-album-row-actions">
                            <form method="post" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('bib_confirmer_suppression_actualite')); ?>">
                                <input type="hidden" name="supprimer" value="<?php echo $a['id']; ?>">
                                <button type="submit" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.upload-zone-input').forEach(input => {
        input.addEventListener('change', () => {
            const zone = input.closest('.upload-zone');
            const filenameSpan = zone.querySelector('.upload-zone-filename');
            if (input.files.length === 1) {
                filenameSpan.textContent = input.files[0].name;
                zone.classList.add('has-file');
            } else if (input.files.length > 1) {
                filenameSpan.textContent = input.files.length + ' fichiers sélectionnés';
                zone.classList.add('has-file');
            }
        });
    });
});
</script>

<?php require '../../footer.php'; ?>
