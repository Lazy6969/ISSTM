<?php
include_once 'language.php';
require_once 'db_connect.php';

$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';
if (!$is_admin) {
    header('Location: index.php');
    exit;
}

$upload_dir = 'uploads/';
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
$flash = null;

function part_unlink_if_upload($path) {
    if ($path && file_exists($path) && strpos($path, 'uploads/') === 0) {
        unlink($path);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Créer / mettre à jour un partenaire ---
    if (isset($_POST['save_partenaire'])) {
        $partenaire_id = isset($_POST['partenaire_id']) && ctype_digit($_POST['partenaire_id']) ? (int) $_POST['partenaire_id'] : 0;
        $nom = trim($_POST['nom'] ?? '');
        $site_url = trim($_POST['site_url'] ?? '');
        $display_order = ctype_digit($_POST['display_order'] ?? '') ? (int) $_POST['display_order'] : 0;

        if ($nom === '' || $site_url === '') {
            $flash = ['type' => 'error', 'msg' => 'Le nom et le lien du site sont obligatoires.'];
            header("Location: admin_partenaires.php?view=" . ($partenaire_id ? "edit&id=$partenaire_id" : "edit") . "&flash=" . urlencode($flash['type'] . '|' . $flash['msg']));
            exit;
        }
        if (!preg_match('~^https?://~i', $site_url)) {
            $site_url = 'https://' . $site_url;
        }

        $logo_path = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0 && in_array($_FILES['logo']['type'], $allowed_types)) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $new_name = 'partenaire_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $new_name)) {
                $logo_path = $upload_dir . $new_name;
            }
        }

        if ($partenaire_id > 0) {
            if ($logo_path) {
                $old = $mysqli->query("SELECT logo_path FROM partenaires WHERE id = $partenaire_id")->fetch_assoc();
                if ($old) part_unlink_if_upload($old['logo_path']);
                $stmt = $mysqli->prepare("UPDATE partenaires SET nom=?, site_url=?, logo_path=?, display_order=? WHERE id=?");
                $stmt->bind_param("sssii", $nom, $site_url, $logo_path, $display_order, $partenaire_id);
            } else {
                $stmt = $mysqli->prepare("UPDATE partenaires SET nom=?, site_url=?, display_order=? WHERE id=?");
                $stmt->bind_param("ssii", $nom, $site_url, $display_order, $partenaire_id);
            }
            $stmt->execute();
            $stmt->close();
            $flash = ['type' => 'success', 'msg' => 'Partenaire mis à jour avec succès.'];
        } else {
            if (!$logo_path) {
                $flash = ['type' => 'error', 'msg' => 'Le logo est obligatoire pour un nouveau partenaire.'];
                header("Location: admin_partenaires.php?view=edit&flash=" . urlencode($flash['type'] . '|' . $flash['msg']));
                exit;
            }
            $stmt = $mysqli->prepare("INSERT INTO partenaires (nom, site_url, logo_path, display_order) VALUES (?,?,?,?)");
            $stmt->bind_param("sssi", $nom, $site_url, $logo_path, $display_order);
            $stmt->execute();
            $partenaire_id = $mysqli->insert_id;
            $stmt->close();
            $flash = ['type' => 'success', 'msg' => 'Partenaire créé avec succès.'];
        }
        header("Location: admin_partenaires.php?view=edit&id=$partenaire_id&flash=" . urlencode($flash['type'] . '|' . $flash['msg']));
        exit;
    }

    // --- Supprimer un partenaire ---
    if (isset($_POST['delete_partenaire_id'])) {
        $partenaire_id = (int) $_POST['delete_partenaire_id'];
        $old = $mysqli->query("SELECT logo_path FROM partenaires WHERE id = $partenaire_id")->fetch_assoc();
        if ($old) part_unlink_if_upload($old['logo_path']);
        $stmt = $mysqli->prepare("DELETE FROM partenaires WHERE id = ?");
        $stmt->bind_param("i", $partenaire_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_partenaires.php?flash=" . urlencode('success|Partenaire supprimé.'));
        exit;
    }
}

if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

$view = $_GET['view'] ?? 'list';

$page_title = t('admin_partenaires_titre');

include 'header.php';
?>

<a href="administrateur.php" class="btn-outline btn-back-sticky"><i class="fas fa-arrow-left"></i> <?php echo t('admin_retour_tableau_bord'); ?></a>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="administrateur.php"><?php echo t('administration'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('admin_partenaires_titre'); ?></span>
        </nav>
        <h1><?php echo t('admin_partenaires_titre'); ?></h1>
        <p><?php echo t('admin_partenaires_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <?php if ($flash): ?>
            <div class="admin-flash admin-flash-<?php echo htmlspecialchars($flash['type']); ?>">
                <i class="fas <?php echo $flash['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'; ?>"></i>
                <?php echo htmlspecialchars($flash['msg']); ?>
            </div>
        <?php endif; ?>

        <?php if ($view === 'edit'):
            $partenaire_id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;
            $partenaire = null;
            if ($partenaire_id > 0) {
                $stmt = $mysqli->prepare("SELECT * FROM partenaires WHERE id = ?");
                $stmt->bind_param("i", $partenaire_id);
                $stmt->execute();
                $partenaire = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }
        ?>
            <a href="admin_partenaires.php" class="admin-galerie-back"><i class="fas fa-chevron-left"></i> <?php echo t('admin_partenaires_retour_liste'); ?></a>
            <h2 class="admin-section-title"><?php echo $partenaire ? t('admin_partenaires_modifier') : t('admin_partenaires_nouveau'); ?></h2>

            <form action="admin_partenaires.php" method="POST" enctype="multipart/form-data" class="admin-form admin-album-form">
                <?php if ($partenaire): ?><input type="hidden" name="partenaire_id" value="<?php echo (int) $partenaire['id']; ?>"><?php endif; ?>

                <div class="album-form-grid">
                    <div class="album-form-main">
                        <div class="form-group">
                            <label><?php echo t('admin_partenaires_nom_label'); ?></label>
                            <input type="text" name="nom" value="<?php echo htmlspecialchars($partenaire['nom'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><?php echo t('admin_partenaires_url_label'); ?></label>
                            <input type="url" name="site_url" value="<?php echo htmlspecialchars($partenaire['site_url'] ?? ''); ?>" placeholder="https://..." required>
                        </div>
                        <div class="form-group">
                            <label><?php echo t('admin_partenaires_ordre_label'); ?></label>
                            <input type="number" name="display_order" value="<?php echo (int) ($partenaire['display_order'] ?? 0); ?>" min="0">
                        </div>
                    </div>
                    <div class="album-form-side">
                        <div class="form-group upload-zone-group">
                            <label><?php echo t('admin_partenaires_logo_label'); ?></label>
                            <?php if (!empty($partenaire['logo_path'])): ?>
                                <img src="<?php echo htmlspecialchars($partenaire['logo_path']); ?>" class="album-cover-preview" alt="Logo actuel">
                            <?php endif; ?>
                            <label class="upload-zone">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <span class="upload-zone-text"><?php echo t('admin_choisir_image_deposer'); ?></span>
                                <span class="upload-zone-filename"></span>
                                <input type="file" name="logo" accept="image/jpeg, image/png, image/jpg, image/webp" class="upload-zone-input">
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" name="save_partenaire" class="btn-submit"><i class="fas fa-save"></i> <?php echo $partenaire ? t('admin_enregistrer_modifications') : t('admin_partenaires_creer'); ?></button>
            </form>

        <?php else:
            $all_partenaires = $mysqli->query("SELECT * FROM partenaires ORDER BY display_order ASC, id ASC")->fetch_all(MYSQLI_ASSOC);
        ?>
            <div class="admin-galerie-stats">
                <div class="admin-stat-card"><i class="fas fa-handshake"></i><div><strong><?php echo count($all_partenaires); ?></strong><span><?php echo t('admin_partenaires_total'); ?></span></div></div>
            </div>

            <div class="admin-galerie-toolbar">
                <a href="admin_partenaires.php?view=edit" class="btn-add-item admin-new-album-btn"><i class="fas fa-plus"></i> <?php echo t('admin_partenaires_nouveau'); ?></a>
            </div>

            <?php if (empty($all_partenaires)): ?>
                <p class="gallery-empty"><i class="fas fa-handshake"></i> <?php echo t('admin_partenaires_aucun'); ?></p>
            <?php else: ?>
                <div class="admin-album-list">
                    <?php foreach ($all_partenaires as $p): ?>
                        <div class="admin-album-row">
                            <img src="<?php echo htmlspecialchars($p['logo_path']); ?>" alt="" class="admin-album-row-thumb" style="object-fit: contain; background: #fff;">
                            <div class="admin-album-row-info">
                                <h4><?php echo htmlspecialchars($p['nom']); ?></h4>
                                <div class="admin-album-row-meta">
                                    <span><i class="fas fa-link"></i> <?php echo htmlspecialchars($p['site_url']); ?></span>
                                    <span><i class="fas fa-arrow-up-9-1"></i> <?php echo (int) $p['display_order']; ?></span>
                                </div>
                            </div>
                            <div class="admin-album-row-actions">
                                <a href="admin_partenaires.php?view=edit&id=<?php echo $p['id']; ?>" class="btn-outline" title="<?php echo t('admin_modifier'); ?>"><i class="fas fa-pen"></i></a>
                                <form action="admin_partenaires.php" method="POST" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_partenaires_confirm_delete')); ?>">
                                    <button type="submit" name="delete_partenaire_id" value="<?php echo $p['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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

<?php include 'footer.php'; ?>
