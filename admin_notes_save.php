<?php
// Endpoint AJAX : enregistre le bloc-notes personnel de l'admin connecté (tableau de bord,
// administrateur.php) — un mémo libre par compte admin, sauvegardé automatiquement.
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$contenu = $_POST['contenu'] ?? '';
if (strlen($contenu) > 20000) {
    $contenu = substr($contenu, 0, 20000);
}

$stmt = $mysqli->prepare("INSERT INTO admin_notes (user_id, contenu) VALUES (?, ?) ON DUPLICATE KEY UPDATE contenu = VALUES(contenu)");
$stmt->bind_param('is', $user_id, $contenu);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
