<?php
include_once '../../language.php';
require_once '../../db_connect.php';
require '../config.php';
require '../functions.php';
requireAdmin();

// Deux façons d'arriver ici : le compte autonome de la bibliothèque gère son propre profil
// (session admin_id), ou un admin ISSTM bridgé (ex : le super admin) gère le compte unique de
// la bibliothèque en son nom. Dans ce second cas, la vérification du mot de passe actuel est
// inutile (l'admin ISSTM n'a pas à le connaître) et les clés de session admin_* ne doivent pas
// être touchées, puisqu'elles n'appartiennent pas à sa propre session.
$estProprietaire = isset($_SESSION['admin_id']);
if ($estProprietaire) {
    $admin_id = (int) $_SESSION['admin_id'];
} else {
    $admin_id = (int) $pdo->query("SELECT id FROM admins ORDER BY id ASC LIMIT 1")->fetchColumn();
}

$upload_dir = '../uploads/avatars/';
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif'];
$flash = null;

$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Changer la photo de profil ---
    if (isset($_POST['update_avatar']) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        if (in_array($_FILES['avatar']['type'], $allowed_types)) {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $new_name = 'avatar_' . $admin_id . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $new_name)) {
                if ($admin['avatar_path'] && strpos($admin['avatar_path'], 'uploads/avatars/') === 0 && file_exists('../' . $admin['avatar_path'])) {
                    unlink('../' . $admin['avatar_path']);
                }
                $new_path = 'uploads/avatars/' . $new_name;
                $pdo->prepare("UPDATE admins SET avatar_path = ? WHERE id = ?")->execute([$new_path, $admin_id]);
                if ($estProprietaire) {
                    $_SESSION['admin_avatar'] = $new_path;
                }
                $admin['avatar_path'] = $new_path;
                $flash = ['type' => 'success', 'msg' => t('bib_profil_photo_succes')];
            } else {
                $flash = ['type' => 'error', 'msg' => t('bib_profil_photo_erreur_envoi')];
            }
        } else {
            $flash = ['type' => 'error', 'msg' => t('bib_profil_photo_erreur_format')];
        }
    }

    // --- Supprimer la photo de profil (retour à l'avatar par défaut) ---
    if (isset($_POST['delete_avatar'])) {
        if ($admin['avatar_path'] && strpos($admin['avatar_path'], 'uploads/avatars/') === 0 && file_exists('../' . $admin['avatar_path'])) {
            unlink('../' . $admin['avatar_path']);
        }
        $pdo->prepare("UPDATE admins SET avatar_path = NULL WHERE id = ?")->execute([$admin_id]);
        if ($estProprietaire) {
            unset($_SESSION['admin_avatar']);
        }
        $admin['avatar_path'] = null;
        $flash = ['type' => 'success', 'msg' => t('bib_profil_photo_supprimee')];
    }

    // --- Mettre à jour le nom d'utilisateur ---
    if (isset($_POST['update_info'])) {
        $nouveauUsername = trim($_POST['username'] ?? '');
        if ($nouveauUsername === '') {
            $flash = ['type' => 'error', 'msg' => t('bib_profil_erreur_username_vide')];
        } else {
            $check = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
            $check->execute([$nouveauUsername, $admin_id]);
            if ($check->fetch()) {
                $flash = ['type' => 'error', 'msg' => t('bib_profil_erreur_username_pris')];
            } else {
                $pdo->prepare("UPDATE admins SET username = ? WHERE id = ?")->execute([$nouveauUsername, $admin_id]);
                if ($estProprietaire) {
                    $_SESSION['admin_username'] = $nouveauUsername;
                }
                $admin['username'] = $nouveauUsername;
                $flash = ['type' => 'success', 'msg' => t('bib_profil_succes')];
            }
        }
    }

    // --- Changer le mot de passe ---
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if ($estProprietaire && !password_verify($current, $admin['password'])) {
            $flash = ['type' => 'error', 'msg' => t('bib_profil_erreur_mdp_actuel')];
        } elseif (strlen($new) < 6) {
            $flash = ['type' => 'error', 'msg' => t('bib_profil_erreur_mdp_court')];
        } elseif ($new !== $confirm) {
            $flash = ['type' => 'error', 'msg' => t('bib_profil_erreur_mdp_confirmation')];
        } else {
            $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?")->execute([password_hash($new, PASSWORD_DEFAULT), $admin_id]);
            $flash = ['type' => 'success', 'msg' => t('bib_profil_mdp_succes')];
        }
    }
}

$page_title = t('bib_mon_profil');
require '../../header.php';
?>

<div class="page-banner bib-page-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="../index.php"><?php echo t('bib_nav_titre'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('bib_mon_profil'); ?></span>
        </nav>
        <h1><i class="fas fa-user-gear"></i> <?php echo t('bib_mon_profil'); ?></h1>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <?php if (!$estProprietaire): ?>
            <div class="admin-flash admin-flash-success"><i class="fas fa-user-shield"></i> <?php echo t('bib_profil_gestion_super_admin'); ?></div>
        <?php endif; ?>

        <?php if ($flash): ?>
            <div class="admin-flash admin-flash-<?php echo htmlspecialchars($flash['type']); ?>">
                <i class="fas <?php echo $flash['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'; ?>"></i>
                <?php echo htmlspecialchars($flash['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="profile-header-card">
            <?php if (!empty($admin['avatar_path'])): ?>
                <img src="../<?php echo e($admin['avatar_path']); ?>" alt="Photo de profil" class="profile-header-avatar">
            <?php else: ?>
                <div class="profile-header-avatar profile-header-avatar-placeholder"><i class="fas fa-user"></i></div>
            <?php endif; ?>
            <div>
                <h2><?php echo t($estProprietaire ? 'bib_admin_dashboard_connecte_en_tant_que' : 'bib_profil_titre_compte'); ?> <?php echo e($admin['username']); ?> !</h2>
                <span class="profile-role-badge is-admin">
                    <i class="fas fa-book-open"></i>
                    <?php echo t('bib_profil_badge_role'); ?>
                </span>
            </div>
        </div>

        <div class="profile-settings-grid">

            <!-- === Photo de profil === -->
            <div class="profile-settings-card">
                <h3><i class="fas fa-camera"></i> <?php echo t('bib_profil_photo_titre'); ?></h3>
                <p class="profile-card-hint"><?php echo t('bib_profil_photo_hint'); ?></p>
                <form id="avatar-upload-form" action="profil.php" method="POST" enctype="multipart/form-data" class="upload-zone-group">
                    <label class="upload-zone">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <span class="upload-zone-text"><?php echo t('bib_profil_photo_choisir'); ?></span>
                        <span class="upload-zone-filename"></span>
                        <input type="file" name="avatar" accept="image/jpeg, image/png, image/jpg, image/webp, image/gif" class="upload-zone-input">
                    </label>
                </form>
                <?php if (!empty($admin['avatar_path'])): ?>
                    <form id="avatar-delete-form" action="profil.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo e(t('bib_profil_photo_confirmer_suppression')); ?>"></form>
                <?php endif; ?>
                <div class="profile-avatar-actions">
                    <button type="submit" form="avatar-upload-form" name="update_avatar" class="btn-add-item"><i class="fas fa-upload"></i> <?php echo t('bib_profil_photo_mettre_a_jour'); ?></button>
                    <?php if (!empty($admin['avatar_path'])): ?>
                        <button type="submit" form="avatar-delete-form" name="delete_avatar" class="btn-add-item btn-delete-avatar"><i class="fas fa-trash-alt"></i> <?php echo t('bib_profil_photo_supprimer'); ?></button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- === Informations du compte === -->
            <div class="profile-settings-card">
                <h3><i class="fas fa-id-card"></i> <?php echo t('bib_profil_infos_titre'); ?></h3>
                <form action="profil.php" method="POST">
                    <div class="form-group">
                        <label><?php echo t('bib_nom_utilisateur'); ?></label>
                        <input type="text" name="username" value="<?php echo e($admin['username']); ?>" required>
                    </div>
                    <button type="submit" name="update_info" class="btn-add-item"><i class="fas fa-check"></i> <?php echo t('bib_profil_enregistrer'); ?></button>
                </form>
            </div>

            <!-- === Sécurité / Mot de passe === -->
            <div class="profile-settings-card">
                <h3><i class="fas fa-lock"></i> <?php echo t('bib_profil_securite_titre'); ?></h3>
                <form action="profil.php" method="POST">
                    <?php if ($estProprietaire): ?>
                        <div class="form-group">
                            <label><?php echo t('bib_profil_mdp_actuel'); ?></label>
                            <input type="password" name="current_password" autocomplete="current-password" required>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label><?php echo t('bib_profil_nouveau_mdp'); ?></label>
                        <input type="password" name="new_password" autocomplete="new-password" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('bib_profil_confirmer_mdp'); ?></label>
                        <input type="password" name="confirm_password" autocomplete="new-password" minlength="6" required>
                    </div>
                    <button type="submit" name="change_password" class="btn-add-item"><i class="fas fa-key"></i> <?php echo t('bib_profil_changer_mdp'); ?></button>
                </form>
            </div>

        </div>

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
