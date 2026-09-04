<?php
require_once 'db_connect.php';

$redirect = $_POST['redirect'] ?? 'index.php';
// Sécurité : n'autoriser que les redirections internes (chemin relatif du site)
if (!preg_match('/^[a-zA-Z0-9_\-\.\/]+\.php(\?[^\s"\']*)?$/', $redirect)) {
    $redirect = 'index.php';
}
$separator = strpos($redirect, '?') !== false ? '&' : '?';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    $email = trim($_POST['newsletter_email']);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $mysqli->prepare("INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->close();
        header("Location: $redirect{$separator}newsletter=success#footer-newsletter");
        exit;
    }
}

header("Location: $redirect{$separator}newsletter=error#footer-newsletter");
exit;
