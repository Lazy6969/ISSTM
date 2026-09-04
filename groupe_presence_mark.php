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

$session_date = trim($_POST['session_date'] ?? '');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $session_date)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_date']);
    exit;
}

$present_ids = $_POST['present_user_ids'] ?? [];
if (!is_array($present_ids)) { $present_ids = []; }
$present_ids = array_map('intval', $present_ids);

// Crée la séance si elle n'existe pas encore pour cette date, sinon réutilise l'existante
// (une séance par groupe et par jour) afin de permettre de corriger une présence déjà saisie.
$stmt = $mysqli->prepare("INSERT INTO groupe_presence_sessions (groupe_id, session_date, created_by) VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)");
$stmt->bind_param("isi", $groupe_id, $session_date, $self['id']);
$stmt->execute();
$session_id = $mysqli->insert_id;
$stmt->close();

// Tous les étudiants actifs du groupe reçoivent une marque : présent si coché, absent sinon.
$students = $mysqli->query("SELECT user_id FROM groupe_membres WHERE groupe_id = " . (int) $groupe_id . " AND is_banned = 0 AND role_in_group = 'etudiant'")->fetch_all(MYSQLI_ASSOC);

$stmt = $mysqli->prepare("INSERT INTO groupe_presence_marks (session_id, user_id, status) VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE status = VALUES(status)");
foreach ($students as $s) {
    $uid = (int) $s['user_id'];
    $status = in_array($uid, $present_ids, true) ? 'present' : 'absent';
    $stmt->bind_param("iis", $session_id, $uid, $status);
    $stmt->execute();
}
$stmt->close();

$nb_present = count($present_ids);
echo json_encode(['success' => true, 'session_id' => (int) $session_id, 'session_date' => $session_date, 'nb_present' => $nb_present, 'nb_total' => count($students)]);
