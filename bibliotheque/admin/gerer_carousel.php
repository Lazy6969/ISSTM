<?php
include_once '../../language.php';
require_once '../../db_connect.php';
require '../config.php';
require '../functions.php';
requireAdmin();

$page_title = t('bib_gerer_carousel_titre');
$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter'])) {
        $legende = trim($_POST['legende'] ?? '');
        $ordre = (int) ($_POST['ordre'] ?? 0);

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $erreur = t('bib_erreur_image_requise');
        } else {
            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extension, $extensionsAutorisees)) {
                $erreur = t('bib_erreur_format_image');
            } elseif ($_FILES['image']['size'] > 8 * 1024 * 1024) {
                $erreur = t('bib_erreur_taille_8mo');
            } else {
                $nomUnique = uniqid('slide_', true) . '.' . $extension;
                $cheminDestination = __DIR__ . '/../uploads/carousel/' . $nomUnique;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $cheminDestination)) {
                    $stmt = $pdo->prepare("INSERT INTO carousel_images (chemin_fichier, legende, ordre) VALUES (?, ?, ?)");
                    $stmt->execute([$nomUnique, $legende ?: null, $ordre]);
                    $succes = t('bib_succes_image_ajoutee');
                } else {
                    $erreur = t('bib_erreur_enregistrement_fichier');
                }
            }
        }
    } elseif (isset($_POST['supprimer'])) {
        $id = $_POST['supprimer'];
        $stmt = $pdo->prepare("SELECT * FROM carousel_images WHERE id = ?");
        $stmt->execute([$id]);
        $img = $stmt->fetch();
        if ($img) {
            $chemin = __DIR__ . '/../uploads/carousel/' . $img['chemin_fichier'];
            if (file_exists($chemin)) {
                unlink($chemin);
            }
            $pdo->prepare("DELETE FROM carousel_images WHERE id = ?")->execute([$id]);
            $succes = t('bib_succes_image_supprimee');
        }
    }
}

$images = $pdo->query("SELECT * FROM carousel_images ORDER BY ordre, id")->fetchAll();

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
            <span><?php echo t('bib_gerer_carousel_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-images"></i> <?php echo t('bib_gerer_carousel_titre'); ?></h1>
        <p><?php echo t('bib_gerer_carousel_soustitre'); ?></p>
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
                        <label><?php echo t('bib_legende_optionnelle_label'); ?></label>
                        <input type="text" name="legende" placeholder="<?php echo t('bib_legende_placeholder'); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php echo t('bib_ordre_affichage_label'); ?></label>
                        <input type="number" name="ordre" value="<?php echo count($images); ?>" min="0">
                    </div>
                </div>
                <div class="album-form-side">
                    <div class="form-group upload-zone-group">
                        <label><?php echo t('bib_image_carousel_label'); ?></label>
                        <label class="upload-zone">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <span class="upload-zone-text"><?php echo t('admin_choisir_image_deposer'); ?></span>
                            <span class="upload-zone-filename"></span>
                            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="upload-zone-input" required>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" name="ajouter" class="btn-submit"><i class="fas fa-plus"></i> <?php echo t('bib_ajouter_au_carousel'); ?></button>
        </form>

        <?php if (!$images): ?>
            <p class="gallery-empty"><i class="fas fa-images"></i> <?php echo t('bib_aucune_image_carousel'); ?></p>
        <?php else: ?>
            <div class="admin-album-list">
                <?php foreach ($images as $img): ?>
                    <div class="admin-album-row">
                        <img src="../uploads/carousel/<?php echo e($img['chemin_fichier']); ?>" alt="" class="admin-album-row-thumb">
                        <div class="admin-album-row-info">
                            <h4><?php echo $img['legende'] ? e($img['legende']) : '<em>' . t('bib_sans_legende') . '</em>'; ?></h4>
                            <div class="admin-album-row-meta">
                                <span><i class="fas fa-arrow-up-9-1"></i> <?php echo t('bib_ordre'); ?> : <?php echo $img['ordre']; ?></span>
                            </div>
                        </div>
                        <div class="admin-album-row-actions">
                            <form method="post" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('bib_confirmer_suppression_image')); ?>">
                                <input type="hidden" name="supprimer" value="<?php echo $img['id']; ?>">
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
            }
        });
    });
});
</script>

<?php require '../../footer.php'; ?>
