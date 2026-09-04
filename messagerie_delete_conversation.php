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

$scope = $_POST['scope'] ?? 'me'; // 'me' ou 'everyone'

if ($scope === 'everyone') {
    // Suppression de toute la conversation pour tous les participants : les fichiers physiques
    // sont supprimés du disque, et les messages sont marqués supprimés (plutôt qu'un DELETE brut)
    // pour que les onglets déjà ouverts des autres participants les retirent en direct via le
    // sondage (voir messagerie_poll.php::deleted_ids).
    $att = $mysqli->query("SELECT file_path FROM messagerie_attachments");
    while ($row = $att->fetch_assoc()) {
        if (file_exists($row['file_path'])) unlink($row['file_path']);
    }
    $mysqli->query("DELETE FROM messagerie_attachments");
    $mysqli->query("DELETE FROM messagerie_message_hides");
    $mysqli->query("UPDATE messagerie_messages SET content = NULL, deleted_for_everyone_at = NOW() WHERE deleted_for_everyone_at IS NULL");
} else {
    // Masque tout l'historique existant pour l'utilisateur courant uniquement (les autres
    // participants continuent de voir tous les messages normalement).
    $self_id = (int) $self['id'];
    $mysqli->query("INSERT IGNORE INTO messagerie_message_hides (message_id, user_id)
                     SELECT id, $self_id FROM messagerie_messages");
}

echo json_encode(['success' => true, 'scope' => $scope]);
