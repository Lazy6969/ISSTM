<?php
require_once 'db_connect.php';
require_once 'messagerie_functions.php';
header('Content-Type: application/json');

[$self, $others] = messagerie_require_access($mysqli, true);

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    echo json_encode(['results' => []]);
    exit;
}

$stmt = $mysqli->prepare("SELECT m.id, m.sender_id, m.content, m.created_at, u.nom AS sender_nom
                           FROM messagerie_messages m
                           JOIN utilisateurs u ON u.id = m.sender_id
                           LEFT JOIN messagerie_message_hides h ON h.message_id = m.id AND h.user_id = ?
                           WHERE m.content LIKE ? AND h.message_id IS NULL AND m.deleted_for_everyone_at IS NULL
                           ORDER BY m.created_at DESC
                           LIMIT 50");
$self_id = (int) $self['id'];
$like = '%' . $q . '%';
$stmt->bind_param("is", $self_id, $like);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['results' => $results]);
