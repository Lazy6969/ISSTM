<?php
require_once 'db_connect.php';
require_once 'messagerie_functions.php';
header('Content-Type: application/json');

[$self, $others] = messagerie_require_access($mysqli, true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

$content = trim($_POST['content'] ?? '');
$hasFiles = isset($_FILES['files']) && !empty(array_filter($_FILES['files']['name'] ?? []));

if ($content === '' && !$hasFiles) {
    http_response_code(400);
    echo json_encode(['error' => 'empty']);
    exit;
}

// Alignée sur le choix effectué (limites serveur déjà généreuses dans php.ini) : plafond
// applicatif à 500 Mo par fichier, pour rester sur une valeur "très grande" sans être
// littéralement illimitée (risque de saturation disque/upload interminable).
const MESSAGERIE_MAX_FILE_SIZE = 500 * 1024 * 1024;

$upload_dir = 'uploads/messagerie/';

$mysqli->begin_transaction();
try {
    $stmt = $mysqli->prepare("INSERT INTO messagerie_messages (sender_id, content) VALUES (?, ?)");
    $contentOrNull = $content !== '' ? $content : null;
    $stmt->bind_param("is", $self['id'], $contentOrNull);
    $stmt->execute();
    $message_id = $mysqli->insert_id;
    $stmt->close();

    $attachments = [];
    if ($hasFiles) {
        $stmtAtt = $mysqli->prepare("INSERT INTO messagerie_attachments (message_id, file_path, original_name, file_type, mime_type, file_size) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($_FILES['files']['name'] as $key => $originalName) {
            if ($_FILES['files']['error'][$key] !== UPLOAD_ERR_OK) continue;
            if ($_FILES['files']['size'][$key] > MESSAGERIE_MAX_FILE_SIZE) {
                throw new Exception('file_too_large');
            }
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $mime = $_FILES['files']['type'][$key];
            $type = messagerie_classify_file($mime, $ext);
            $newName = 'msg_' . $message_id . '_' . uniqid() . ($ext !== '' ? '.' . $ext : '');
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

$row = $mysqli->query("SELECT id, sender_id, content, created_at, read_at FROM messagerie_messages WHERE id = $message_id")->fetch_assoc();
$row['attachments'] = $attachments;
$row['sender_nom'] = $self['nom'];
$row['sender_avatar'] = $self['avatar_path'];

echo json_encode(['message' => $row]);
