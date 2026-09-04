<?php
require_once 'db_connect.php';
require_once 'groupe_functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

$message_id = (int) ($_POST['message_id'] ?? 0);
$scope = $_POST['scope'] ?? 'me'; // 'me' ou 'everyone'

$msg = $mysqli->query("SELECT id, groupe_id, sender_id FROM groupe_messages WHERE id = " . $message_id)->fetch_assoc();
if (!$msg) {
    http_response_code(404);
    echo json_encode(['error' => 'not_found']);
    exit;
}

[$self, $groupe, $membership] = groupe_require_membership($mysqli, (int) $msg['groupe_id'], true);

if ($scope === 'everyone') {
    // L'auteur du message, ou l'enseignant créateur du groupe (modération), peuvent supprimer
    // pour tout le monde ; les autres membres ne peuvent que le masquer pour eux ("pour moi").
    $is_author = (int) $msg['sender_id'] === (int) $self['id'];
    $is_group_owner = $membership['role_in_group'] === 'enseignant';
    if (!$is_author && !$is_group_owner) {
        http_response_code(403);
        echo json_encode(['error' => 'forbidden']);
        exit;
    }
    $att = $mysqli->query("SELECT file_path FROM groupe_message_attachments WHERE message_id = " . $message_id);
    while ($row = $att->fetch_assoc()) {
        if (file_exists($row['file_path'])) unlink($row['file_path']);
    }
    $mysqli->query("DELETE FROM groupe_message_attachments WHERE message_id = " . $message_id);
    $mysqli->query("UPDATE groupe_messages SET content = NULL, deleted_for_everyone_at = NOW() WHERE id = " . $message_id);
} else {
    $stmt = $mysqli->prepare("INSERT IGNORE INTO groupe_message_hides (message_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $message_id, $self['id']);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => true, 'message_id' => $message_id, 'scope' => $scope]);
