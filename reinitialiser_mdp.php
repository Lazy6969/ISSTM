<?php
include_once 'language.php';
require_once 'db_connect.php';

$email = trim($_GET['email'] ?? $_POST['email'] ?? '');
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');

$user = null;
if ($email !== '' && $token !== '') {
    $stmt = $mysqli->prepare("SELECT id, nom, email FROM utilisateurs WHERE email = ? AND reset_token = ? AND reset_token_expires > NOW()");
    $stmt->bind_param('ss', $email, $token);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$success = false;
$error_message = '';

if ($user && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (strlen($new_password) < 6) {
        $error_message = t('reinitialiser_mdp_trop_court');
    } elseif ($new_password !== $confirm_password) {
        $error_message = t('reinitialiser_mdp_ne_correspond_pas');
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("UPDATE utilisateurs SET mot_de_passe = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $stmt->bind_param('si', $hashed, $user['id']);
        $stmt->execute();
        $stmt->close();
        $success = true;
    }
}

$page_title = t('reinitialiser_mdp_titre');
include 'header.php';
?>

<div class="page-banner inscription-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('reinitialiser_mdp_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-lock-open"></i> <?php echo t('reinitialiser_mdp_titre'); ?></h1>
    </div>
</div>

<div class="page-content">
    <div class="container">
        <div class="mdp-oublie-wrap">

            <?php if ($success): ?>
                <div class="mdp-oublie-contact-box">
                    <i class="fas fa-circle-check" style="color:#2ecc71;"></i>
                    <div>
                        <h4><?php echo t('reinitialiser_mdp_succes_titre'); ?></h4>
                        <p><?php echo t('reinitialiser_mdp_succes_texte'); ?></p>
                        <a href="login.php" class="btn btn-primary" style="margin-top:14px;display:inline-block;"><?php echo t('se_connecter'); ?></a>
                    </div>
                </div>
            <?php elseif (!$user): ?>
                <div class="mdp-oublie-contact-box">
                    <i class="fas fa-triangle-exclamation" style="color:#c0392b;"></i>
                    <div>
                        <h4><?php echo t('reinitialiser_mdp_invalide_titre'); ?></h4>
                        <p><?php echo t('reinitialiser_mdp_invalide_texte'); ?></p>
                        <a href="mot_de_passe_oublie.php" class="btn btn-outline" style="margin-top:14px;display:inline-block;"><?php echo t('mdp_oublie_titre'); ?></a>
                    </div>
                </div>
            <?php else: ?>
                <form action="reinitialiser_mdp.php" method="POST" class="login-form mdp-oublie-form">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <h2><?php echo t('reinitialiser_mdp_form_titre'); ?></h2>
                    <p class="preinscription-hint"><?php echo htmlspecialchars(sprintf(t('reinitialiser_mdp_bonjour'), $user['nom'])); ?></p>

                    <?php if ($error_message): ?>
                        <p class="admin-error" style="display: block;"><?php echo htmlspecialchars($error_message); ?></p>
                    <?php endif; ?>

                    <div class="form-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="new_password" name="new_password" required minlength="6">
                        <label for="new_password"><?php echo t('reinitialiser_mdp_nouveau_label'); ?></label>
                    </div>
                    <div class="form-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                        <label for="confirm_password"><?php echo t('reinitialiser_mdp_confirmer_label'); ?></label>
                    </div>
                    <button type="submit" class="btn btn-login"><i class="fas fa-check"></i> <?php echo t('reinitialiser_mdp_valider'); ?></button>
                </form>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
