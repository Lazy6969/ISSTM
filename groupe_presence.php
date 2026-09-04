<?php
require_once 'db_connect.php';
require_once 'groupe_functions.php';
header('Content-Type: application/json');

$groupe_id = (int) ($_GET['groupe_id'] ?? 0);
[$self, $groupe, $membership] = groupe_require_membership($mysqli, $groupe_id, true);

$stmt = $mysqli->prepare("SELECT s.id, s.session_date,
                                  (SELECT COUNT(*) FROM groupe_presence_marks WHERE session_id = s.id AND status = 'present') AS nb_present,
                                  (SELECT COUNT(*) FROM groupe_presence_marks WHERE session_id = s.id) AS nb_total
                           FROM groupe_presence_sessions s
                           WHERE s.groupe_id = ?
                           ORDER BY s.session_date DESC");
$stmt->bind_param("i", $groupe_id);
$stmt->execute();
$sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($sessions as &$s) {
    $s['id'] = (int) $s['id'];
    $s['nb_present'] = (int) $s['nb_present'];
    $s['nb_total'] = (int) $s['nb_total'];
}
unset($s);

$members_count = (int) $mysqli->query("SELECT COUNT(*) c FROM groupe_membres WHERE groupe_id = " . (int) $groupe_id . " AND is_banned = 0 AND role_in_group = 'etudiant'")->fetch_assoc()['c'];

echo json_encode([
    'sessions' => $sessions,
    'members_count' => $members_count,
    'is_teacher' => $membership['role_in_group'] === 'enseignant',
    'can_download' => groupe_can_download_presence($membership),
]);
