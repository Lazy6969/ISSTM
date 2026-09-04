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

$message_id = (int) ($_POST['message_id'] ?? 0);
$scope = $_POST['scope'] ?? 'me'; // 'me' ou 'everyone'

$msg = $mysqli->query("SELECT id, sender_id FROM messagerie_messages WHERE id = " . $message_id)->fetch_assoc();
if (!$msg) {
    http_response_code(404);
    echo json_encode(['error' => 'not_found']);
    exit;
}

if ($scope === 'everyone') {
    // Seul l'auteur du message peut le supprimer pour tout le monde (comme WhatsApp) : les
    // autres participants ne peuvent que le masquer de leur propre côté ("pour moi").
    if ((int) $msg['sender_id'] !== (int) $self['id']) {
        http_response_code(403);
        echo json_encode(['error' => 'forbidden']);
        exit;
    }
    $att = $mysqli->query("SELECT file_path FROM messagerie_attachments WHERE message_id = " . $message_id);
    while ($row = $att->fetch_assoc()) {
        if (file_exists($row['file_path'])) unlink($row['file_path']);
    }
    $mysqli->query("DELETE FROM messagerie_attachments WHERE message_id = " . $message_id);
    // Suppression "douce" (contenu vidé + horodatage) plutôt qu'un DELETE brut : le sondage des
    // autres participants s'appuie sur cet horodatage pour retirer le message en direct de leur
    // conversation déjà ouverte (voir messagerie_poll.php::deleted_ids).
    $mysqli->query("UPDATE messagerie_messages SET content = NULL, deleted_for_everyone_at = NOW() WHERE id = " . $message_id);
} else {
    $stmt = $mysqli->prepare("INSERT IGNORE INTO messagerie_message_hides (message_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $message_id, $self['id']);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => true, 'message_id' => $message_id, 'scope' => $scope]);
