<?php
require_once 'db_connect.php';
require_once 'groupe_functions.php';
header('Content-Type: application/json');

$groupe_id = (int) ($_GET['groupe_id'] ?? 0);
[$self, $groupe, $membership] = groupe_require_membership($mysqli, $groupe_id, true);

$since_id = isset($_GET['since_id']) ? (int) $_GET['since_id'] : 0;

$stmt = $mysqli->prepare("SELECT m.id, m.sender_id, m.content, m.created_at, u.nom AS sender_nom, u.avatar_path AS sender_avatar, u.role AS sender_role
                           FROM groupe_messages m
                           JOIN utilisateurs u ON u.id = m.sender_id
                           LEFT JOIN groupe_message_hides h ON h.message_id = m.id AND h.user_id = ?
                           WHERE m.groupe_id = ? AND m.id > ? AND h.message_id IS NULL AND m.deleted_for_everyone_at IS NULL
                           ORDER BY m.id ASC");
$stmt->bind_param("iii", $self['id'], $groupe_id, $since_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$deleted_ids = [];
$delRes = $mysqli->query("SELECT id FROM groupe_messages WHERE groupe_id = " . (int) $groupe_id . " AND deleted_for_everyone_at IS NOT NULL AND deleted_for_everyone_at > (NOW() - INTERVAL 20 SECOND)");
while ($row = $delRes->fetch_assoc()) { $deleted_ids[] = (int) $row['id']; }

foreach ($messages as &$msg) {
    $att = $mysqli->query("SELECT file_path, original_name, file_type, mime_type, file_size FROM groupe_message_attachments WHERE message_id = " . (int) $msg['id']);
    $msg['attachments'] = $att->fetch_all(MYSQLI_ASSOC);
}
unset($msg);

// L'utilisateur est en train de regarder cette conversation (sondage actif) : on marque son
// passage à jour, ce qui fait redescendre sa jauge de notification pour ce groupe à zéro.
$mysqli->query("UPDATE groupe_membres SET last_read_at = NOW() WHERE groupe_id = " . (int) $groupe_id . " AND user_id = " . (int) $self['id']);

echo json_encode([
    'messages' => $messages,
    'deleted_ids' => $deleted_ids,
]);
