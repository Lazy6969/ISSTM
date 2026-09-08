<?php
// Journalisation partagée du circuit "mot de passe oublié" (table security_log), utilisée par
// mot_de_passe_oublie.php (vérification identité + OTP) ET reinitialiser_mdp.php (choix du
// nouveau mot de passe) — centralisée ici pour que les deux étapes journalisent de la même
// façon, et que admin_securite.php puisse tout afficher au même endroit.
if (!function_exists('pwreset_log')) {
    function pwreset_log($mysqli, $event_type, $identifiant, $details = '') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $stmt = $mysqli->prepare("INSERT INTO security_log (event_type, identifiant, ip_address, details) VALUES (?,?,?,?)");
        $stmt->bind_param('ssss', $event_type, $identifiant, $ip, $details);
        $stmt->execute();
        $stmt->close();
    }
}

if (!function_exists('security_generate_password')) {
    function security_generate_password($length = 10) {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $pwd = '';
        for ($i = 0; $i < $length; $i++) {
            $pwd .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $pwd;
    }
}
