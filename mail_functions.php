<?php
// Envoi d'emails réels via SMTP (PHPMailer), pour remplacer le mail() natif peu fiable sous
// XAMPP/Windows sans relais SMTP configuré. Voir mail_config.php pour les identifiants.
require_once __DIR__ . '/vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// Envoie un email HTML via SMTP. Retourne true/false selon le succès ; en cas d'échec, seul le
// message d'erreur technique (hôte, refus SMTP...) est journalisé — jamais le corps du message,
// pour ne jamais faire fuiter un code OTP dans les logs applicatifs.
if (!function_exists('isstm_send_mail')) {
    function isstm_send_mail($to, $subject, $htmlBody) {
        $config = require __DIR__ . '/mail_config.php';
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port = $config['port'];
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log('isstm_send_mail: échec envoi vers ' . $to . ' - ' . $mail->ErrorInfo);
            return false;
        }
    }
}
