<?php
require_once 'db_connect.php';
require_once 'groupe_functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

$annonce_id = (int) ($_POST['annonce_id'] ?? 0);
$annonce = $mysqli->query("SELECT groupe_id FROM groupe_annonces WHERE id = " . $annonce_id)->fetch_assoc();
if (!$annonce) {
    http_response_code(404);
    echo json_encode(['error' => 'not_found']);
    exit;
}

[$self, $groupe, $membership] = groupe_require_membership($mysqli, (int) $annonce['groupe_id'], true);

if ($membership['role_in_group'] !== 'enseignant') {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$mysqli->query("DELETE FROM groupe_annonces WHERE id = " . $annonce_id);
echo json_encode(['success' => true]);
