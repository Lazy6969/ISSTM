<?php
require_once 'db_connect.php';
require_once 'messagerie_functions.php';
header('Content-Type: application/json');

[$self, $others] = messagerie_require_access($mysqli, true);

$since_id = isset($_GET['since_id']) ? (int) $_GET['since_id'] : 0;

// Marque comme lus tous les messages des autres participants qui ne l'étaient pas encore
// (l'utilisateur courant vient d'ouvrir/de rafraîchir la conversation).
if (!empty($others)) {
    $mysqli->query("UPDATE messagerie_messages SET read_at = NOW() WHERE sender_id != " . (int) $self['id'] . " AND read_at IS NULL");
}

$stmt = $mysqli->prepare("SELECT m.id, m.sender_id, m.content, m.created_at, m.read_at, u.nom AS sender_nom, u.avatar_path AS sender_avatar
                           FROM messagerie_messages m
                           JOIN utilisateurs u ON u.id = m.sender_id
                           LEFT JOIN messagerie_message_hides h ON h.message_id = m.id AND h.user_id = " . (int) $self['id'] . "
                           WHERE m.id > ? AND h.message_id IS NULL AND m.deleted_for_everyone_at IS NULL
                           ORDER BY m.id ASC");
$stmt->bind_param("i", $since_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Messages supprimés "pour tout le monde" récemment : permet aux onglets déjà ouverts des
// autres participants de les retirer en direct, même s'ils avaient un id <= since_id.
$deleted_ids = [];
$delRes = $mysqli->query("SELECT id FROM messagerie_messages WHERE deleted_for_everyone_at IS NOT NULL AND deleted_for_everyone_at > (NOW() - INTERVAL 20 SECOND)");
while ($row = $delRes->fetch_assoc()) { $deleted_ids[] = (int) $row['id']; }

foreach ($messages as &$msg) {
    $att = $mysqli->query("SELECT file_path, original_name, file_type, mime_type, file_size FROM messagerie_attachments WHERE message_id = " . (int) $msg['id']);
    $msg['attachments'] = $att->fetch_all(MYSQLI_ASSOC);
}
unset($msg);

// Pour les messages ENVOYÉS par l'utilisateur courant (pas ceux reçus dans ce lot), on renvoie
// aussi leur statut de lecture à jour, pour rafraîchir les coches "envoyé/lu" côté expéditeur.
$sent_status = [];
if ($since_id > 0) {
    $res = $mysqli->query("SELECT id, read_at FROM messagerie_messages WHERE sender_id = " . (int) $self['id'] . " AND id > $since_id - 200 ORDER BY id DESC LIMIT 50");
    while ($row = $res->fetch_assoc()) { $sent_status[$row['id']] = $row['read_at']; }
}

// Statut de présence de chaque autre participant, pour les puces individuelles dans l'en-tête
// et le texte de statut ("au moins un autre participant en ligne").
$others_status = [];
$anyone_online = false;
foreach ($others as $person) {
    $online = messagerie_is_online($person['last_activity']);
    if ($online) $anyone_online = true;
    $others_status[] = [
        'id' => (int) $person['id'],
        'nom' => $person['nom'],
        'online' => $online,
        'last_activity' => $person['last_activity'],
    ];
}

echo json_encode([
    'messages' => $messages,
    'deleted_ids' => $deleted_ids,
    'sent_status' => $sent_status,
    'others_status' => $others_status,
    'other_online' => $anyone_online,
]);
