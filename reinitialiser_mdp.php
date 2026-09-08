<?php
include_once 'language.php';
require_once 'db_connect.php';
require_once 'mail_functions.php';
require_once 'security_functions.php';

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

// Politique de robustesse du mot de passe (3 critères), appliquée à toute réinitialisation
// déclenchée depuis le circuit "mot de passe oublié" du login. Voir aussi le contrôle en
// direct côté client (checklist ci-dessous) : la vérification serveur reste la seule qui
// compte, le JS n'est là que pour le confort de saisie.
function pwreset_criteres($password) {
    return [
        'longueur' => strlen($password) >= 8,
        'casse'    => preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password),
        'chiffre'  => (bool) preg_match('/[0-9]/', $password),
    ];
}

if ($user && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $criteres = pwreset_criteres($new_password);
    if (in_array(false, $criteres, true)) {
        $error_message = t('reinitialiser_mdp_criteres_non_remplis');
        // Distinct de otp_fail/otp_mismatch (étape identité) : ici l'identité était déjà
        // vérifiée, seul le mot de passe choisi ne respecte pas les 3 critères.
        pwreset_log($mysqli, 'pwd_criteria_fail', $user['email']);
    } elseif ($new_password !== $confirm_password) {
        $error_message = t('reinitialiser_mdp_ne_correspond_pas');
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("UPDATE utilisateurs SET mot_de_passe = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $stmt->bind_param('si', $hashed, $user['id']);
        $stmt->execute();
        $stmt->close();
        $success = true;
        // Seul événement qui reflète un mot de passe VRAIMENT changé (otp_success ne veut dire
        // que "code OTP validé" — l'utilisateur peut encore abandonner ou échouer aux 3 critères
        // après coup). C'est celui-ci qui doit compter comme une réinitialisation réussie.
        pwreset_log($mysqli, 'pwd_reset_success', $user['email']);

        // Email de confirmation envoyé après coup, une fois le nouveau mot de passe enregistré
        // (le mot de passe lui-même n'est jamais inclus dans l'email, uniquement une notification
        // de sécurité — voir mot_de_passe_oublie.php pour le même principe sur le code OTP).
        $subject = t('reinitialiser_mdp_email_sujet');
        $when = date('d/m/Y à H:i');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $body = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">'
            . '<h2 style="color:#003366;">' . htmlspecialchars(t('reinitialiser_mdp_email_titre')) . '</h2>'
            . '<p>' . htmlspecialchars(sprintf(t('mdp_oublie_email_bonjour'), $user['nom'])) . '</p>'
            . '<p>' . htmlspecialchars(sprintf(t('reinitialiser_mdp_email_texte'), $when)) . '</p>'
            . '<p style="font-size:13px;color:#888;">' . htmlspecialchars(sprintf(t('reinitialiser_mdp_email_ip'), $ip)) . '</p>'
            . '<p style="font-size:13px;color:#888;">' . htmlspecialchars(t('reinitialiser_mdp_email_si_pas_vous')) . '</p>'
            . '<hr><p style="font-size:12px;color:#888;">ISSTM Mahajanga — isstm.univ.umg@gmail.com</p></div>';
        isstm_send_mail($user['email'], $subject, $body);
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
                        <input type="password" id="new_password" name="new_password" required minlength="8">
                        <label for="new_password"><?php echo t('reinitialiser_mdp_nouveau_label'); ?></label>
                    </div>
                    <ul class="pwd-criteria-list" id="pwd-criteria-list">
                        <li data-criteria="longueur"><i class="fas fa-circle"></i> <?php echo t('reinitialiser_mdp_critere_longueur'); ?></li>
                        <li data-criteria="casse"><i class="fas fa-circle"></i> <?php echo t('reinitialiser_mdp_critere_casse'); ?></li>
                        <li data-criteria="chiffre"><i class="fas fa-circle"></i> <?php echo t('reinitialiser_mdp_critere_chiffre'); ?></li>
                    </ul>
                    <div class="form-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                        <label for="confirm_password"><?php echo t('reinitialiser_mdp_confirmer_label'); ?></label>
                    </div>
                    <button type="submit" class="btn btn-login"><i class="fas fa-check"></i> <?php echo t('reinitialiser_mdp_valider'); ?></button>
                </form>

                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var input = document.getElementById('new_password');
                    var list = document.getElementById('pwd-criteria-list');
                    if (!input || !list) return;
                    var items = list.querySelectorAll('li');
                    input.addEventListener('input', function () {
                        var value = input.value;
                        var criteres = {
                            longueur: value.length >= 8,
                            casse: /[a-z]/.test(value) && /[A-Z]/.test(value),
                            chiffre: /[0-9]/.test(value)
                        };
                        items.forEach(function (li) {
                            var met = criteres[li.dataset.criteria];
                            li.classList.toggle('is-met', !!met);
                            var icon = li.querySelector('i');
                            icon.className = met ? 'fas fa-circle-check' : 'fas fa-circle';
                        });
                    });
                });
                </script>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
