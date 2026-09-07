<?php
// Endpoint AJAX : reinitialise le compteur de vues (page_views) depuis le tableau de bord admin.
// type=total  -> vide tout l'historique (vues totales ET vues du jour retombent a 0)
// type=jour   -> ne retire que les lignes du jour courant, l'historique des jours precedents reste intact
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'unauthorized']);
    exit;
}

$type = $_POST['type'] ?? '';

if ($type === 'total') {
    $mysqli->query("TRUNCATE TABLE page_views");
} elseif ($type === 'jour') {
    $mysqli->query("DELETE FROM page_views WHERE DATE(created_at) = CURDATE()");
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_type']);
    exit;
}

echo json_encode(['success' => true]);
