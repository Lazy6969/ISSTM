<?php
require_once 'db_connect.php';
require_once 'groupe_functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

$groupe_id = (int) ($_POST['groupe_id'] ?? 0);
[$self, $groupe, $membership] = groupe_require_membership($mysqli, $groupe_id, true);

if ($membership['role_in_group'] !== 'enseignant') {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$target_id = (int) ($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? 'ban'; // 'ban' ou 'unban'

$target = $mysqli->query("SELECT role_in_group FROM groupe_membres WHERE groupe_id = " . (int) $groupe_id . " AND user_id = " . $target_id)->fetch_assoc();
if (!$target) {
    http_response_code(404);
    echo json_encode(['error' => 'not_found']);
    exit;
}
// Seuls les étudiants peuvent être bannis : jamais un autre enseignant, jamais soi-même.
if ($target['role_in_group'] === 'enseignant') {
    http_response_code(403);
    echo json_encode(['error' => 'cannot_ban_teacher']);
    exit;
}

$is_banned = $action === 'unban' ? 0 : 1;
$stmt = $mysqli->prepare("UPDATE groupe_membres SET is_banned = ? WHERE groupe_id = ? AND user_id = ?");
$stmt->bind_param("iii", $is_banned, $groupe_id, $target_id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'is_banned' => (bool) $is_banned]);
