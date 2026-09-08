<?php
include_once 'language.php';
require_once 'db_connect.php';
require_once 'mail_functions.php';
require_once 'security_functions.php';

// Si déjà connecté, ce formulaire n'a pas d'utilité
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: profil.php');
    exit;
}

// Circuit "mot de passe oublié" classique par lien email, en une seule étape pour le visiteur :
//   email -> compte vérifié -> jeton aléatoire -> jeton+expiration enregistrés -> email envoyé
// La suite (clic sur le lien -> vérif jeton+expiration -> nouveau mot de passe -> password_hash()
// -> jeton supprimé) est gérée par reinitialiser_mdp.php, qui applique aussi les 3 critères de
// robustesse et envoie l'email de confirmation.
$sent_notice = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reset_link'])) {
    $email = trim($_POST['email'] ?? '');

    if ($email !== '') {
        // --- Vérifier le compte ---
        $stmt = $mysqli->prepare("SELECT id, nom, email FROM utilisateurs WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            // --- Générer un jeton aléatoire ---
            $token = bin2hex(random_bytes(32));

            // --- Enregistrer jeton + expiration (1 heure) ---
            $stmt = $mysqli->prepare("UPDATE utilisateurs SET reset_token = ?, reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
            $stmt->bind_param('si', $token, $user['id']);
            $stmt->execute();
            $stmt->close();

            // --- Envoyer l'email avec le lien de réinitialisation ---
            // SITE_URL n'est qu'un chemin (ex: "/ISSTM"), sans schéma ni domaine : un lien de mail
            // doit être une URL absolue pour rester cliquable depuis n'importe quel client email.
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base_url = $scheme . '://' . $host . SITE_URL;
            $reset_link = $base_url . '/reinitialiser_mdp.php?email=' . urlencode($user['email']) . '&token=' . $token;
            $subject = t('mdp_oublie_email_sujet');
            $body = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">'
                . '<h2 style="color:#003366;">' . htmlspecialchars(t('mdp_oublie_email_titre')) . '</h2>'
                . '<p>' . htmlspecialchars(sprintf(t('mdp_oublie_email_bonjour'), $user['nom'])) . '</p>'
                . '<p>' . htmlspecialchars(t('mdp_oublie_email_texte')) . '</p>'
                . '<p style="text-align:center;margin:28px 0;"><a href="' . htmlspecialchars($reset_link) . '" style="background:#003366;color:#fff;padding:12px 28px;border-radius:30px;text-decoration:none;font-weight:bold;display:inline-block;">' . htmlspecialchars(t('mdp_oublie_email_bouton')) . '</a></p>'
                . '<p style="font-size:12px;color:#888;word-break:break-all;">' . htmlspecialchars($reset_link) . '</p>'
                . '<p style="font-size:13px;color:#888;">' . htmlspecialchars(t('mdp_oublie_email_expiration')) . '</p>'
                . '<hr><p style="font-size:12px;color:#888;">ISSTM Mahajanga — isstm.univ.umg@gmail.com</p></div>';
            // Le jeton complet n'est jamais journalisé, seul l'événement l'est.
            isstm_send_mail($user['email'], $subject, $body);
            pwreset_log($mysqli, 'reset_link_sent', $email);
        } else {
            // Journalisé pour la visibilité admin (typo, tentative d'énumération...), mais le
            // visiteur reçoit exactement le même message que pour un compte réel, ci-dessous.
            pwreset_log($mysqli, 'reset_link_unknown_email', $email);
        }
    }

    // Message générique commun (compte existant ou non), pour ne jamais révéler si un email est
    // enregistré sur le site.
    $sent_notice = true;
}

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
            <?php else: ?>

                <form action="mot_de_passe_oublie.php" method="POST" class="login-form mdp-oublie-form">
                    <h2><?php echo t('mdp_oublie_form_titre'); ?></h2>
                    <div class="form-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="text" id="email" name="email" required>
                        <label for="email"><?php echo t('mdp_oublie_email_label'); ?></label>
                    </div>
                    <button type="submit" name="send_reset_link" class="btn btn-login"><i class="fas fa-paper-plane"></i> <?php echo t('mdp_oublie_envoyer'); ?></button>

                    <div class="form-footer">
                        <p><a href="login.php"><i class="fas fa-arrow-left"></i> <?php echo t('mdp_oublie_retour_connexion'); ?></a></p>
                    </div>
                </form>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
