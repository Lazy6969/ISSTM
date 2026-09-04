<?php
require_once 'db_connect.php';
require_once 'dm_functions.php';
header('Content-Type: application/json');

$conversation_id = (int) ($_GET['conversation_id'] ?? 0);
[$self, $conversation, $other_id] = dm_require_participant($mysqli, $conversation_id, true);

$since_id = isset($_GET['since_id']) ? (int) $_GET['since_id'] : 0;

$stmt = $mysqli->prepare("SELECT m.id, m.sender_id, m.content, m.created_at, m.read_at
                           FROM dm_messages m
                           LEFT JOIN dm_message_hides h ON h.message_id = m.id AND h.user_id = ?
                           WHERE m.conversation_id = ? AND m.id > ? AND h.message_id IS NULL AND m.deleted_for_everyone_at IS NULL
                           ORDER BY m.id ASC");
$stmt->bind_param("iii", $self['id'], $conversation_id, $since_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$other = $mysqli->query("SELECT nom, avatar_path FROM utilisateurs WHERE id = " . (int) $other_id)->fetch_assoc();
foreach ($messages as &$msg) {
    $att = $mysqli->query("SELECT file_path, original_name, file_type, mime_type, file_size FROM dm_attachments WHERE message_id = " . (int) $msg['id']);
    $msg['attachments'] = $att->fetch_all(MYSQLI_ASSOC);
    $msg['sender_nom'] = (int) $msg['sender_id'] === (int) $self['id'] ? $self['nom'] : $other['nom'];
    $msg['sender_avatar'] = (int) $msg['sender_id'] === (int) $self['id'] ? $self['avatar_path'] : $other['avatar_path'];
}
unset($msg);

$deleted_ids = [];
$delRes = $mysqli->query("SELECT id FROM dm_messages WHERE conversation_id = " . (int) $conversation_id . " AND deleted_for_everyone_at IS NOT NULL AND deleted_for_everyone_at > (NOW() - INTERVAL 20 SECOND)");
while ($row = $delRes->fetch_assoc()) { $deleted_ids[] = (int) $row['id']; }

// L'utilisateur regarde activement cette conversation : marque les messages de l'autre comme lus.
$mysqli->query("UPDATE dm_messages SET read_at = NOW() WHERE conversation_id = " . (int) $conversation_id . " AND sender_id != " . (int) $self['id'] . " AND read_at IS NULL");

// Statut lu/en ligne de l'autre participant, pour mettre à jour l'en-tête sans recharger la page.
$other_full = $mysqli->query("SELECT last_activity FROM utilisateurs WHERE id = " . (int) $other_id)->fetch_assoc();
$is_online = $other_full && (time() - strtotime($other_full['last_activity'] ?? '1970-01-01')) < 25;

// Marque aussi les propres messages envoyés comme "lus" côté UI si l'autre participant vient de
// les lire (poll précédent) — recalculé simplement en relisant leur read_at.
$own_read_ids = [];
$readRes = $mysqli->query("SELECT id FROM dm_messages WHERE conversation_id = " . (int) $conversation_id . " AND sender_id = " . (int) $self['id'] . " AND read_at IS NOT NULL AND read_at > (NOW() - INTERVAL 10 SECOND)");
while ($row = $readRes->fetch_assoc()) { $own_read_ids[] = (int) $row['id']; }

echo json_encode([
    'messages' => $messages,
    'deleted_ids' => $deleted_ids,
    'is_online' => $is_online,
    'newly_read_ids' => $own_read_ids,
]);
