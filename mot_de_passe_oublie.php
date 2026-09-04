<?php
include_once 'language.php';
require_once 'db_connect.php';
require_once 'mail_functions.php';

// Si déjà connecté, ce formulaire n'a pas d'utilité
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: profil.php');
    exit;
}

// Journalise les tentatives (succès et échecs) sans jamais enregistrer le code OTP lui-même.
function pwreset_log($mysqli, $event_type, $identifiant, $details = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $mysqli->prepare("INSERT INTO security_log (event_type, identifiant, ip_address, details) VALUES (?,?,?,?)");
    $stmt->bind_param('ssss', $event_type, $identifiant, $ip, $details);
    $stmt->execute();
    $stmt->close();
}

$error_message = '';
$info_message = '';   // cas "compte réel mais sans téléphone enregistré" -> circuit administratif
$sent_notice = false; // message générique final (compte inexistant, infos incorrectes, ou succès réel)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Étape 1/3 : email ---
    if (isset($_POST['step1_email'])) {
        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            $error_message = t('mdp_oublie_champ_requis');
        } else {
            $_SESSION['pwreset_email'] = $email;
            $_SESSION['pwreset_step'] = 2;
            header('Location: mot_de_passe_oublie.php');
            exit;
        }
    }

    // --- Étape 2/3 : nom complet ---
    if (isset($_POST['step2_nom'])) {
        $nom = trim($_POST['nom'] ?? '');
        if ($nom === '' || empty($_SESSION['pwreset_email'])) {
            unset($_SESSION['pwreset_step'], $_SESSION['pwreset_email']);
            header('Location: mot_de_passe_oublie.php');
            exit;
        }
        $_SESSION['pwreset_nom'] = $nom;
        $_SESSION['pwreset_step'] = 3;
        header('Location: mot_de_passe_oublie.php');
        exit;
    }

    // --- Étape 3/3 : téléphone -> tentative d'envoi du code OTP ---
    if (isset($_POST['step3_telephone'])) {
        $telephone = trim($_POST['telephone'] ?? '');
        $email = $_SESSION['pwreset_email'] ?? '';
        $nom = $_SESSION['pwreset_nom'] ?? '';
        if ($email === '' || $nom === '') {
            unset($_SESSION['pwreset_step'], $_SESSION['pwreset_email'], $_SESSION['pwreset_nom']);
            header('Location: mot_de_passe_oublie.php');
            exit;
        }

        $stmt = $mysqli->prepare("SELECT id, nom, email, telephone, otp_requested_at, otp_request_count FROM utilisateurs WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $nom_match = $user && mb_strtolower(trim($user['nom'])) === mb_strtolower($nom);
        $has_phone = $user && trim((string) $user['telephone']) !== '';
        $tel_match = $has_phone && trim($user['telephone']) === $telephone;

        if ($user && $nom_match && !$has_phone) {
            // Compte réel, nom correct, mais aucun téléphone enregistré : le self-service par OTP
            // n'est pas possible pour ce compte -> on l'oriente vers le circuit administratif.
            pwreset_log($mysqli, 'otp_no_phone', $email);
            $info_message = t('mdp_oublie_pas_telephone');
            unset($_SESSION['pwreset_step'], $_SESSION['pwreset_email'], $_SESSION['pwreset_nom']);
        } elseif ($user && $nom_match && $tel_match) {
            // Limitation des demandes : au plus 3 codes envoyés par tranche de 15 minutes.
            $within_window = $user['otp_requested_at'] && strtotime($user['otp_requested_at']) > time() - 900;
            if ($within_window && (int) $user['otp_request_count'] >= 3) {
                pwreset_log($mysqli, 'otp_rate_limited', $email);
            } else {
                $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $otp_hash = password_hash($otp, PASSWORD_DEFAULT);
                $new_count = $within_window ? (int) $user['otp_request_count'] + 1 : 1;

                $stmt = $mysqli->prepare("UPDATE utilisateurs SET otp_code=?, otp_expires=DATE_ADD(NOW(), INTERVAL 5 MINUTE), otp_attempts=0, otp_requested_at=NOW(), otp_request_count=? WHERE id=?");
                $stmt->bind_param('sii', $otp_hash, $new_count, $user['id']);
                $stmt->execute();
                $stmt->close();

                $subject = t('mdp_oublie_email_sujet');
                $body = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">'
                    . '<h2 style="color:#003366;">' . htmlspecialchars(t('mdp_oublie_email_titre')) . '</h2>'
                    . '<p>' . htmlspecialchars(sprintf(t('mdp_oublie_email_bonjour'), $user['nom'])) . '</p>'
                    . '<p>' . htmlspecialchars(t('mdp_oublie_otp_email_texte')) . '</p>'
                    . '<p style="margin:24px 0;font-size:32px;font-weight:bold;letter-spacing:6px;color:#d4a017;text-align:center;">' . htmlspecialchars($otp) . '</p>'
                    . '<p style="font-size:13px;color:#888;">' . htmlspecialchars(t('mdp_oublie_otp_email_expiration')) . '</p>'
                    . '<hr><p style="font-size:12px;color:#888;">ISSTM Mahajanga — isstm.umg@gmail.com</p></div>';
                // Le code n'est jamais journalisé : seul l'événement "otp_request" l'est, sans le corps du message.
                isstm_send_mail($user['email'], $subject, $body);
                pwreset_log($mysqli, 'otp_request', $email);

                $_SESSION['pwreset_verified_user_id'] = $user['id'];
                $_SESSION['pwreset_step'] = 4;
                header('Location: mot_de_passe_oublie.php');
                exit;
            }
        } else {
            pwreset_log($mysqli, 'otp_mismatch', $email);
        }

        // Cas générique commun (compte inexistant, nom/téléphone incorrects, ou trop de demandes) :
        // même message que si tout s'était bien passé, sans jamais préciser quel champ posait problème.
        if ($info_message === '') {
            $sent_notice = true;
            unset($_SESSION['pwreset_step'], $_SESSION['pwreset_email'], $_SESSION['pwreset_nom']);
        }
    }

    // --- Étape 4 : saisie du code OTP reçu par email ---
    if (isset($_POST['step4_otp'])) {
        $otp_input = trim($_POST['otp'] ?? '');
        $uid = (int) ($_SESSION['pwreset_verified_user_id'] ?? 0);
        if (!$uid) {
            unset($_SESSION['pwreset_step'], $_SESSION['pwreset_verified_user_id']);
            header('Location: mot_de_passe_oublie.php');
            exit;
        }

        $stmt = $mysqli->prepare("SELECT id, email, otp_code, otp_expires, otp_attempts FROM utilisateurs WHERE id = ?");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user || !$user['otp_code'] || strtotime($user['otp_expires']) < time()) {
            $error_message = t('mdp_oublie_otp_expire');
            if ($user) pwreset_log($mysqli, 'otp_fail', $user['email'], 'expire_ou_absent');
            unset($_SESSION['pwreset_step'], $_SESSION['pwreset_verified_user_id']);
        } elseif (!password_verify($otp_input, $user['otp_code'])) {
            $attempts = (int) $user['otp_attempts'] + 1;
            if ($attempts >= 5) {
                $mysqli->query("UPDATE utilisateurs SET otp_code=NULL, otp_expires=NULL, otp_attempts=0 WHERE id=" . (int) $user['id']);
                pwreset_log($mysqli, 'otp_locked', $user['email']);
                $error_message = t('mdp_oublie_otp_verrouille');
                unset($_SESSION['pwreset_step'], $_SESSION['pwreset_verified_user_id']);
            } else {
                $stmt = $mysqli->prepare("UPDATE utilisateurs SET otp_attempts = ? WHERE id = ?");
                $stmt->bind_param('ii', $attempts, $user['id']);
                $stmt->execute();
                $stmt->close();
                pwreset_log($mysqli, 'otp_fail', $user['email'], "tentative $attempts/5");
                $error_message = sprintf(t('mdp_oublie_otp_incorrect'), 5 - $attempts);
            }
        } else {
            // Code correct : on le consomme immédiatement et on génère un jeton de réinitialisation
            // classique à usage unique (réutilise reinitialiser_mdp.php tel quel), valable 10 minutes.
            $token = bin2hex(random_bytes(32));
            $stmt = $mysqli->prepare("UPDATE utilisateurs SET otp_code=NULL, otp_expires=NULL, otp_attempts=0, otp_request_count=0, reset_token=?, reset_token_expires=DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE id=?");
            $stmt->bind_param('si', $token, $user['id']);
            $stmt->execute();
            $stmt->close();
            pwreset_log($mysqli, 'otp_success', $user['email']);
            unset($_SESSION['pwreset_step'], $_SESSION['pwreset_verified_user_id'], $_SESSION['pwreset_email'], $_SESSION['pwreset_nom']);
            header('Location: reinitialiser_mdp.php?email=' . urlencode($user['email']) . '&token=' . $token);
            exit;
        }
    }

    // --- Recommencer depuis le début ---
    if (isset($_POST['restart'])) {
        unset($_SESSION['pwreset_step'], $_SESSION['pwreset_email'], $_SESSION['pwreset_nom'], $_SESSION['pwreset_verified_user_id']);
        header('Location: mot_de_passe_oublie.php');
        exit;
    }
}

$step = (int) ($_SESSION['pwreset_step'] ?? 1);
if ($sent_notice || $info_message !== '') { $step = 0; } // écran final, plus de formulaire à afficher

$page_title = t('mdp_oublie_titre');
include 'header.php';
?>

<div class="page-banner inscription-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="login.php"><?php echo t('se_connecter'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('mdp_oublie_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-lock-open"></i> <?php echo t('mdp_oublie_titre'); ?></h1>
        <p><?php echo t('mdp_oublie_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">
        <div class="mdp-oublie-wrap">

            <div class="mdp-oublie-contact-box">
                <i class="fas fa-circle-info"></i>
                <div>
                    <h4><?php echo t('mdp_oublie_contact_titre'); ?></h4>
                    <p><?php echo t('mdp_oublie_contact_texte'); ?></p>
                </div>
            </div>

            <?php if ($sent_notice): ?>
                <div class="mdp-oublie-contact-box">
                    <i class="fas fa-circle-check" style="color:#2ecc71;"></i>
                    <div>
                        <p><?php echo t('mdp_oublie_envoi_confirmation'); ?></p>
                        <a href="login.php" class="btn btn-outline" style="margin-top:14px;display:inline-block;"><?php echo t('mdp_oublie_retour_connexion'); ?></a>
                    </div>
                </div>
            <?php elseif ($info_message !== ''): ?>
                <div class="mdp-oublie-contact-box">
                    <i class="fas fa-triangle-exclamation" style="color:#d4a017;"></i>
                    <div>
                        <p><?php echo htmlspecialchars($info_message); ?></p>
                        <a href="login.php" class="btn btn-outline" style="margin-top:14px;display:inline-block;"><?php echo t('mdp_oublie_retour_connexion'); ?></a>
                    </div>
                </div>
            <?php else: ?>

                <form action="mot_de_passe_oublie.php" method="POST" class="login-form mdp-oublie-form">
                    <p class="mdp-oublie-etape-indicateur"><?php echo sprintf(t('mdp_oublie_etape_indicateur'), min($step, 3)); ?></p>

                    <?php if ($error_message): ?>
                        <p class="admin-error" style="display: block;"><?php echo htmlspecialchars($error_message); ?></p>
                    <?php endif; ?>

                    <?php if ($step === 1): ?>
                        <h2><?php echo t('mdp_oublie_etape1_titre'); ?></h2>
                        <div class="form-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="text" id="email" name="email" required>
                            <label for="email"><?php echo t('mdp_oublie_email_label'); ?></label>
                        </div>
                        <button type="submit" name="step1_email" class="btn btn-login"><?php echo t('mdp_oublie_suivant'); ?> <i class="fas fa-arrow-right"></i></button>

                    <?php elseif ($step === 2): ?>
                        <h2><?php echo t('mdp_oublie_etape2_titre'); ?></h2>
                        <div class="form-group">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="nom" name="nom" required>
                            <label for="nom"><?php echo t('nom'); ?></label>
                        </div>
                        <button type="submit" name="step2_nom" class="btn btn-login"><?php echo t('mdp_oublie_suivant'); ?> <i class="fas fa-arrow-right"></i></button>

                    <?php elseif ($step === 3): ?>
                        <h2><?php echo t('mdp_oublie_etape3_titre'); ?></h2>
                        <div class="form-group">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="text" id="telephone" name="telephone" required>
                            <label for="telephone"><?php echo t('admin_utilisateurs_telephone_label'); ?></label>
                        </div>
                        <button type="submit" name="step3_telephone" class="btn btn-login"><i class="fas fa-paper-plane"></i> <?php echo t('mdp_oublie_envoyer'); ?></button>

                    <?php elseif ($step === 4): ?>
                        <h2><?php echo t('mdp_oublie_etape4_titre'); ?></h2>
                        <p class="preinscription-hint"><?php echo t('mdp_oublie_otp_hint'); ?></p>
                        <div class="form-group">
                            <i class="fas fa-shield-halved input-icon"></i>
                            <input type="text" id="otp" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code">
                            <label for="otp"><?php echo t('mdp_oublie_otp_label'); ?></label>
                        </div>
                        <button type="submit" name="step4_otp" class="btn btn-login"><i class="fas fa-check"></i> <?php echo t('mdp_oublie_otp_valider'); ?></button>
                    <?php endif; ?>

                    <div class="form-footer">
                        <?php if ($step > 1): ?>
                            <button type="submit" name="restart" class="mdp-oublie-restart-btn"><i class="fas fa-rotate-left"></i> <?php echo t('mdp_oublie_recommencer'); ?></button>
                        <?php endif; ?>
                        <p><a href="login.php"><i class="fas fa-arrow-left"></i> <?php echo t('mdp_oublie_retour_connexion'); ?></a></p>
                    </div>
                </form>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
