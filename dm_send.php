<?php
require_once 'db_connect.php';
require_once 'messagerie_functions.php';
require_once 'dm_functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

$conversation_id = (int) ($_POST['conversation_id'] ?? 0);
[$self, $conversation, $other_id] = dm_require_participant($mysqli, $conversation_id, true);

// Il faut rester amis pour continuer à échanger : si l'un des deux a retiré l'amitié entre-temps,
// l'historique reste consultable mais aucun nouveau message ne peut être envoyé.
if (amis_get_statut($mysqli, $self['id'], $other_id) !== 'amis') {
    http_response_code(403);
    echo json_encode(['error' => 'not_friends']);
    exit;
}

$content = trim($_POST['content'] ?? '');
$hasFiles = isset($_FILES['files']) && !empty(array_filter($_FILES['files']['name'] ?? []));

if ($content === '' && !$hasFiles) {
    http_response_code(400);
    echo json_encode(['error' => 'empty']);
    exit;
}

const DM_MAX_FILE_SIZE = 500 * 1024 * 1024;

$upload_dir = 'uploads/dm/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$mysqli->begin_transaction();
try {
    $stmt = $mysqli->prepare("INSERT INTO dm_messages (conversation_id, sender_id, content) VALUES (?, ?, ?)");
    $contentOrNull = $content !== '' ? $content : null;
    $stmt->bind_param("iis", $conversation_id, $self['id'], $contentOrNull);
    $stmt->execute();
    $message_id = $mysqli->insert_id;
    $stmt->close();

    $attachments = [];
    if ($hasFiles) {
        $stmtAtt = $mysqli->prepare("INSERT INTO dm_attachments (message_id, file_path, original_name, file_type, mime_type, file_size) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($_FILES['files']['name'] as $key => $originalName) {
            if ($_FILES['files']['error'][$key] !== UPLOAD_ERR_OK) continue;
            if ($_FILES['files']['size'][$key] > DM_MAX_FILE_SIZE) {
                throw new Exception('file_too_large');
            }
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $mime = $_FILES['files']['type'][$key];
            $type = messagerie_classify_file($mime, $ext);
            $newName = 'dm_' . $message_id . '_' . uniqid() . ($ext !== '' ? '.' . $ext : '');
            $destination = $upload_dir . $newName;
            if (!move_uploaded_file($_FILES['files']['tmp_name'][$key], $destination)) {
                throw new Exception('upload_failed');
            }
            $size = filesize($destination);
            $stmtAtt->bind_param("issssi", $message_id, $destination, $originalName, $type, $mime, $size);
            $stmtAtt->execute();
            $attachments[] = [
                'file_path' => $destination,
                'original_name' => $originalName,
                'file_type' => $type,
                'mime_type' => $mime,
                'file_size' => $size,
            ];
        }
        $stmtAtt->close();
    }

    $mysqli->commit();
} catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

$row = $mysqli->query("SELECT id, sender_id, content, created_at, read_at FROM dm_messages WHERE id = $message_id")->fetch_assoc();
$row['attachments'] = $attachments;
$row['sender_nom'] = $self['nom'];
$row['sender_avatar'] = $self['avatar_path'];

echo json_encode(['message' => $row]);
