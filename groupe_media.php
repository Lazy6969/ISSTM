<?php
require_once 'db_connect.php';
require_once 'messagerie_functions.php';
require_once 'groupe_functions.php';
header('Content-Type: application/json');

$groupe_id = (int) ($_GET['groupe_id'] ?? 0);
[$self, $groupe, $membership] = groupe_require_membership($mysqli, $groupe_id, true);

$filter = $_GET['type'] ?? 'all';
$self_id = (int) $self['id'];
$typeClause = '';
if (in_array($filter, ['image', 'video', 'file'], true)) {
    $typeClause = "AND a.file_type = '" . $mysqli->real_escape_string($filter) . "'";
}

$sql = "SELECT a.id, a.message_id, a.file_path, a.original_name, a.file_type, a.mime_type, a.file_size,
               m.created_at, m.sender_id, u.nom AS sender_nom
        FROM groupe_message_attachments a
        JOIN groupe_messages m ON m.id = a.message_id
        JOIN utilisateurs u ON u.id = m.sender_id
        LEFT JOIN groupe_message_hides h ON h.message_id = m.id AND h.user_id = $self_id
        WHERE m.groupe_id = $groupe_id AND h.message_id IS NULL $typeClause
        ORDER BY m.created_at DESC";
$result = $mysqli->query($sql);
$items = [];
while ($row = $result->fetch_assoc()) {
    $row['file_size_label'] = messagerie_format_size($row['file_size']);
    $row['icon'] = messagerie_file_icon(pathinfo($row['original_name'], PATHINFO_EXTENSION));
    $items[] = $row;
}

$counts = ['image' => 0, 'video' => 0, 'file' => 0];
$countsResult = $mysqli->query("SELECT a.file_type, COUNT(*) c
                                 FROM groupe_message_attachments a
                                 JOIN groupe_messages m ON m.id = a.message_id
                                 LEFT JOIN groupe_message_hides h ON h.message_id = m.id AND h.user_id = $self_id
                                 WHERE m.groupe_id = $groupe_id AND h.message_id IS NULL
                                 GROUP BY a.file_type");
while ($row = $countsResult->fetch_assoc()) { $counts[$row['file_type']] = (int) $row['c']; }

echo json_encode(['items' => $items, 'counts' => $counts]);
