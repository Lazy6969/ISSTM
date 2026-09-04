<?php
// La session doit être démarrée avant toute lecture de $_SESSION (sinon les données
// persistées par une requête précédente ne sont pas encore chargées pour celle-ci).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si l'utilisateur n'est pas connecté, on le redirige vers la page d'accueil.
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'db_connect.php';

$user_id = (int) $_SESSION['user_id'];
$profil_row = $mysqli->query("SELECT telephone, bio, date_naissance, ville, centres_interet, lien_facebook, lien_linkedin, site_web FROM utilisateurs WHERE id = $user_id")->fetch_assoc();
$current_telephone = $profil_row['telephone'] ?? '';
$current_bio = $profil_row['bio'] ?? '';
$current_date_naissance = $profil_row['date_naissance'] ?? '';
$current_ville = $profil_row['ville'] ?? '';
$current_centres_interet = $profil_row['centres_interet'] ?? '';
$current_lien_facebook = $profil_row['lien_facebook'] ?? '';
$current_lien_linkedin = $profil_row['lien_linkedin'] ?? '';
$current_site_web = $profil_row['site_web'] ?? '';
$upload_dir = 'uploads/';
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif'];
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Changer la photo de profil ---
    if (isset($_POST['update_avatar']) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        if (in_array($_FILES['avatar']['type'], $allowed_types)) {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $new_name = 'avatar_' . $user_id . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $new_name)) {
                $old = $mysqli->query("SELECT avatar_path FROM utilisateurs WHERE id = $user_id")->fetch_assoc();
                if ($old && $old['avatar_path'] && strpos($old['avatar_path'], 'uploads/') === 0 && file_exists($old['avatar_path'])) {
                    unlink($old['avatar_path']);
                }
                $new_path = $upload_dir . $new_name;
                $stmt = $mysqli->prepare("UPDATE utilisateurs SET avatar_path = ? WHERE id = ?");
                $stmt->bind_param("si", $new_path, $user_id);
                $stmt->execute();
                $stmt->close();
                $_SESSION['user_avatar'] = $new_path;
                $flash = ['type' => 'success', 'msg' => 'Photo de profil mise à jour.'];
            } else {
                $flash = ['type' => 'error', 'msg' => "Échec de l'envoi de l'image."];
            }
        } else {
            $flash = ['type' => 'error', 'msg' => 'Format d\'image non supporté (JPEG, PNG, GIF ou WEBP uniquement).'];
        }
    }

    // --- Supprimer la photo de profil (retour à l'avatar par défaut) ---
    if (isset($_POST['delete_avatar'])) {
        $old = $mysqli->query("SELECT avatar_path FROM utilisateurs WHERE id = $user_id")->fetch_assoc();
        if ($old && $old['avatar_path'] && strpos($old['avatar_path'], 'uploads/') === 0 && file_exists($old['avatar_path'])) {
            unlink($old['avatar_path']);
        }
        $stmt = $mysqli->prepare("UPDATE utilisateurs SET avatar_path = NULL WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        unset($_SESSION['user_avatar']);
        $flash = ['type' => 'success', 'msg' => 'Photo de profil supprimée. Avatar par défaut restauré.'];
    }

    // --- Mettre à jour nom / email ---
    if (isset($_POST['update_info'])) {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $date_naissance = trim($_POST['date_naissance'] ?? '');
        $date_naissance = $date_naissance !== '' ? $date_naissance : null;
        $ville = trim($_POST['ville'] ?? '');
        $centres_interet = trim($_POST['centres_interet'] ?? '');
        $lien_facebook = trim($_POST['lien_facebook'] ?? '');
        $lien_linkedin = trim($_POST['lien_linkedin'] ?? '');
        $site_web = trim($_POST['site_web'] ?? '');
        // Certains comptes (messagerie interne, comptes étudiants créés par la scolarité)
        // utilisent un identifiant de connexion qui n'est pas une adresse email réelle
        // (ex: USER_ISSTM_7) : on n'impose le format email que s'il a été modifié.
        $identifiant_inchange = ($email === $_SESSION['user_email']);
        if ($nom === '' || $email === '') {
            $flash = ['type' => 'error', 'msg' => 'Le nom et l\'email sont obligatoires.'];
        } elseif (!$identifiant_inchange && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $flash = ['type' => 'error', 'msg' => 'Adresse email invalide.'];
        } else {
            $check = $mysqli->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
            $check->bind_param("si", $email, $user_id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $flash = ['type' => 'error', 'msg' => 'Cette adresse email est déjà utilisée par un autre compte.'];
            } else {
                $stmt = $mysqli->prepare("UPDATE utilisateurs SET nom = ?, email = ?, telephone = ?, bio = ?, date_naissance = ?, ville = ?, centres_interet = ?, lien_facebook = ?, lien_linkedin = ?, site_web = ? WHERE id = ?");
                $stmt->bind_param("ssssssssssi", $nom, $email, $telephone, $bio, $date_naissance, $ville, $centres_interet, $lien_facebook, $lien_linkedin, $site_web, $user_id);
                $stmt->execute();
                $stmt->close();
                $_SESSION['user_nom'] = $nom;
                $_SESSION['user_email'] = $email;
                $current_telephone = $telephone;
                $current_bio = $bio;
                $current_date_naissance = $date_naissance ?? '';
                $current_ville = $ville;
                $current_centres_interet = $centres_interet;
                $current_lien_facebook = $lien_facebook;
                $current_lien_linkedin = $lien_linkedin;
                $current_site_web = $site_web;
                $flash = ['type' => 'success', 'msg' => 'Informations mises à jour avec succès.'];
            }
            $check->close();
        }
    }

    // --- Changer le mot de passe ---
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $row = $mysqli->query("SELECT mot_de_passe FROM utilisateurs WHERE id = $user_id")->fetch_assoc();
        if (!$row || !password_verify($current, $row['mot_de_passe'])) {
            $flash = ['type' => 'error', 'msg' => 'Le mot de passe actuel est incorrect.'];
        } elseif (strlen($new) < 6) {
            $flash = ['type' => 'error', 'msg' => 'Le nouveau mot de passe doit contenir au moins 6 caractères.'];
        } elseif ($new !== $confirm) {
            $flash = ['type' => 'error', 'msg' => 'La confirmation ne correspond pas au nouveau mot de passe.'];
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $user_id);
            $stmt->execute();
            $stmt->close();
            $flash = ['type' => 'success', 'msg' => 'Mot de passe modifié avec succès.'];
        }
    }
}

include_once 'language.php';
$page_title = t('profil_titre');

include 'header.php';
?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('profil_titre'); ?></span>
        </nav>
        <h1><?php echo t('profil_titre'); ?></h1>
        <p><?php echo t('profil_soustitre'); ?></p>
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

        <div class="profile-header-card">
            <?php if (!empty($_SESSION['user_avatar'])): ?>
                <img src="<?php echo htmlspecialchars($_SESSION['user_avatar']); ?>" alt="Photo de profil" class="profile-header-avatar">
            <?php else: ?>
                <div class="profile-header-avatar profile-header-avatar-placeholder"><i class="fas fa-user"></i></div>
            <?php endif; ?>
            <?php
            $role_badges = [
                'admin' => ['label' => 'Administrateur', 'icon' => 'fa-user-shield', 'class' => 'is-admin'],
                'enseignant' => ['label' => 'Enseignant', 'icon' => 'fa-chalkboard-user', 'class' => 'is-enseignant'],
                'etudiant' => ['label' => 'Étudiant', 'icon' => 'fa-user-graduate', 'class' => 'is-etudiant'],
                'bibliotheque' => ['label' => 'Bibliothèque', 'icon' => 'fa-book', 'class' => 'is-bibliotheque'],
                'user' => ['label' => 'Utilisateur', 'icon' => 'fa-user', 'class' => ''],
            ];
            $role_badge = $role_badges[$_SESSION['user_role']] ?? $role_badges['user'];
            ?>
            <div>
                <h2><?php echo t('profil_bienvenue'); ?> <?php echo htmlspecialchars($_SESSION['user_nom']); ?> !</h2>
                <span class="profile-role-badge <?php echo $role_badge['class']; ?>">
                    <i class="fas <?php echo $role_badge['icon']; ?>"></i>
                    <?php echo $role_badge['label']; ?>
                </span>
            </div>
        </div>

        <div class="profile-settings-grid">

            <!-- === Photo de profil === -->
            <div class="profile-settings-card">
                <h3><i class="fas fa-camera"></i> Photo de profil</h3>
                <p class="profile-card-hint">Format JPEG, PNG, GIF ou WEBP. Cette image apparaît dans le menu "Mon Compte" en haut du site.</p>
                <form id="avatar-upload-form" action="profil.php" method="POST" enctype="multipart/form-data" class="upload-zone-group">
                    <label class="upload-zone">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <span class="upload-zone-text">Choisir une nouvelle photo</span>
                        <span class="upload-zone-filename"></span>
                        <input type="file" name="avatar" accept="image/jpeg, image/png, image/jpg, image/webp, image/gif" class="upload-zone-input">
                    </label>
                </form>
                <?php if (!empty($_SESSION['user_avatar'])): ?>
                    <form id="avatar-delete-form" action="profil.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars('Supprimer votre photo de profil et revenir à l\'avatar par défaut ?'); ?>"></form>
                <?php endif; ?>
                <div class="profile-avatar-actions">
                    <button type="submit" form="avatar-upload-form" name="update_avatar" class="btn-add-item"><i class="fas fa-upload"></i> Mettre à jour la photo</button>
                    <?php if (!empty($_SESSION['user_avatar'])): ?>
                        <button type="submit" form="avatar-delete-form" name="delete_avatar" class="btn-add-item btn-delete-avatar"><i class="fas fa-trash-alt"></i> Supprimer la photo</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- === Informations personnelles === -->
            <div class="profile-settings-card">
                <h3><i class="fas fa-id-card"></i> Informations personnelles</h3>
                <form action="profil.php" method="POST">
                    <div class="form-group">
                        <label><?php echo t('nom'); ?></label>
                        <input type="text" name="nom" value="<?php echo htmlspecialchars($_SESSION['user_nom']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('email'); ?></label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('admin_utilisateurs_telephone_label'); ?></label>
                        <input type="text" name="telephone" value="<?php echo htmlspecialchars($current_telephone); ?>">
                        <small class="preinscription-hint"><?php echo t('profil_telephone_hint'); ?></small>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('profil_bio_label'); ?></label>
                        <textarea name="bio" rows="4" maxlength="500" placeholder="<?php echo t('profil_bio_placeholder'); ?>"><?php echo htmlspecialchars($current_bio); ?></textarea>
                        <small class="preinscription-hint"><?php echo t('profil_bio_hint'); ?></small>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('profil_date_naissance'); ?></label>
                        <input type="date" name="date_naissance" value="<?php echo htmlspecialchars($current_date_naissance); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php echo t('profil_ville'); ?></label>
                        <input type="text" name="ville" value="<?php echo htmlspecialchars($current_ville); ?>" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label><?php echo t('profil_centres_interet'); ?></label>
                        <input type="text" name="centres_interet" value="<?php echo htmlspecialchars($current_centres_interet); ?>" maxlength="255" placeholder="<?php echo t('profil_centres_interet_placeholder'); ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-facebook"></i> <?php echo t('profil_lien_facebook'); ?></label>
                        <input type="url" name="lien_facebook" value="<?php echo htmlspecialchars($current_lien_facebook); ?>" maxlength="255" placeholder="https://facebook.com/...">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-linkedin"></i> <?php echo t('profil_lien_linkedin'); ?></label>
                        <input type="url" name="lien_linkedin" value="<?php echo htmlspecialchars($current_lien_linkedin); ?>" maxlength="255" placeholder="https://linkedin.com/...">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-globe"></i> <?php echo t('profil_site_web'); ?></label>
                        <input type="url" name="site_web" value="<?php echo htmlspecialchars($current_site_web); ?>" maxlength="255" placeholder="https://...">
                    </div>
                    <button type="submit" name="update_info" class="btn-add-item"><i class="fas fa-check"></i> Enregistrer les informations</button>
                </form>
            </div>

            <!-- === Sécurité / Mot de passe === -->
            <div class="profile-settings-card">
                <h3><i class="fas fa-lock"></i> Sécurité</h3>
                <form action="profil.php" method="POST">
                    <div class="form-group">
                        <label>Mot de passe actuel</label>
                        <input type="password" name="current_password" autocomplete="current-password" required>
                    </div>
                    <div class="form-group">
                        <label>Nouveau mot de passe</label>
                        <input type="password" name="new_password" autocomplete="new-password" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmer le nouveau mot de passe</label>
                        <input type="password" name="confirm_password" autocomplete="new-password" minlength="6" required>
                    </div>
                    <button type="submit" name="change_password" class="btn-add-item"><i class="fas fa-key"></i> Changer le mot de passe</button>
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

<?php include 'footer.php'; ?>
