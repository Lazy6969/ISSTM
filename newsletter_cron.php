<?php
// Point d'entrée pour un envoi programmé garanti à l'heure, indépendamment des visites de
// l'administrateur sur admin_newsletter.php (qui ne fait qu'un dispatch opportuniste).
// À appeler périodiquement (ex : toutes les 5 minutes) via le Planificateur de tâches Windows :
//   php.exe C:\xampp\htdocs\ISSTM\newsletter_cron.php
// ou une requête HTTP vers ce fichier depuis une tâche planifiée / un service de cron externe.
require_once __DIR__ . '/db_connect.php';

function nl_unsubscribe_link($email) {
    $token = hash_hmac('sha256', $email, NEWSLETTER_SECRET);
    return SITE_URL . '/newsletter_unsubscribe.php?email=' . urlencode($email) . '&token=' . $token;
}

function nl_build_email_body($subject, $message, $email) {
    $unsub = nl_unsubscribe_link($email);
    return '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">'
          . '<h2 style="color:#003366;">' . htmlspecialchars($subject) . '</h2>'
          . '<div>' . nl2br(htmlspecialchars($message)) . '</div>'
          . '<hr><p style="font-size:12px;color:#888;">ISSTM Mahajanga — isstm.univ.umg@gmail.com<br>'
          . '<a href="' . htmlspecialchars($unsub) . '" style="color:#888;">Se désabonner</a></p></div>';
}

$due = $mysqli->query("SELECT * FROM newsletter_campaigns WHERE status='programme' AND scheduled_at <= NOW()")->fetch_all(MYSQLI_ASSOC);
$total_dispatched = 0;

foreach ($due as $campaign) {
    $recipients = $mysqli->query("SELECT email FROM newsletter_subscribers")->fetch_all(MYSQLI_ASSOC);
    $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: ISSTM Mahajanga <isstm.univ.umg@gmail.com>\r\n";
    $sent = 0;
    foreach ($recipients as $r) {
        $body = nl_build_email_body($campaign['subject'], $campaign['message'], $r['email']);
        if (@mail($r['email'], $campaign['subject'], $body, $headers)) {
            $sent++;
        }
    }
    $total = count($recipients);
    $stmt = $mysqli->prepare("UPDATE newsletter_campaigns SET status='envoye', sent_at=NOW(), recipients_count=?, sent_count=? WHERE id=?");
    $stmt->bind_param("iii", $total, $sent, $campaign['id']);
    $stmt->execute();
    $stmt->close();
    $total_dispatched++;
    echo "Campagne #{$campaign['id']} \"{$campaign['subject']}\" envoyée à $sent/$total abonné(s).\n";
}

if ($total_dispatched === 0) {
    echo "Aucune campagne programmée arrivée à échéance.\n";
}
