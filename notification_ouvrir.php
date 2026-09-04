<?php
// Marque une notification comme lue puis redirige vers son lien réel — utilisé quand on clique
// une notification depuis le menu cloche (header.php) ou la page notifications.php.
include_once 'language.php';
require_once 'db_connect.php';
require_once 'communaute_functions.php';
communaute_require_login();

$self_id = (int) $_SESSION['user_id'];
$id = (int) ($_GET['id'] ?? 0);
$redirect = $_GET['redirect'] ?? 'communaute.php';

// Anti-open-redirect : on n'autorise que les redirections vers ce même site.
if (strpos($redirect, SITE_URL) !== 0) {
    $redirect = SITE_URL . '/communaute.php';
}

if ($id > 0) {
    $stmt = $mysqli->prepare("UPDATE communaute_notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $id, $self_id);
    $stmt->execute();
    $stmt->close();
}

header('Location: ' . $redirect);
exit;
