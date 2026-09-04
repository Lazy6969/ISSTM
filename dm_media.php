<?php
// Endpoint AJAX : liste les pièces jointes image/vidéo d'une conversation (archive des médias
// partagés), en excluant les messages supprimés pour tous ou masqués pour l'utilisateur courant.
header('Content-Type: application/json');
include_once 'language.php';
require_once 'db_connect.php';
require_once 'dm_functions.php';

$conversation_id = (int) ($_GET['conversation_id'] ?? 0);
[$self, $conversation, $other_id] = dm_require_participant($mysqli, $conversation_id, true);
$self_id = (int) $self['id'];

$stmt = $mysqli->prepare("SELECT a.file_path, a.original_name, a.file_type
                           FROM dm_attachments a
                           JOIN dm_messages m ON m.id = a.message_id
                           LEFT JOIN dm_message_hides h ON h.message_id = m.id AND h.user_id = ?
                           WHERE m.conversation_id = ? AND h.message_id IS NULL AND m.deleted_for_everyone_at IS NULL
                           AND a.file_type IN ('image', 'video')
                           ORDER BY a.id DESC");
$stmt->bind_param('ii', $self_id, $conversation_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['items' => $items]);
