<?php
// Endpoint AJAX (POST) : actions explicites sur les notifications de l'utilisateur courant —
// bascule lue/non lue ou suppression d'une notification précise (id), ou action groupée
// (mark_all_read / delete_all), optionnellement scopée à une section de date (group).
header('Content-Type: application/json');
include_once 'language.php';
require_once 'db_connect.php';
require_once 'communaute_functions.php';
communaute_require_login(true);

$self_id = (int) $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$valid_groups = ['today', 'yesterday', 'week', 'older'];
$group = in_array($_POST['group'] ?? '', $valid_groups, true) ? $_POST['group'] : null;

switch ($action) {
    case 'toggle_read': {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $mysqli->prepare("UPDATE communaute_notifications SET is_read = NOT is_read WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $id, $self_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        break;
    }
    case 'delete': {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $mysqli->prepare("DELETE FROM communaute_notifications WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $id, $self_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        break;
    }
    case 'mark_all_read': {
        $sql = "UPDATE communaute_notifications SET is_read = 1 WHERE user_id = $self_id";
        if ($group) $sql .= " AND " . communaute_notification_group_sql($group);
        $mysqli->query($sql);
        echo json_encode(['success' => true]);
        break;
    }
    case 'delete_all': {
        $sql = "DELETE FROM communaute_notifications WHERE user_id = $self_id";
        if ($group) $sql .= " AND " . communaute_notification_group_sql($group);
        $mysqli->query($sql);
        echo json_encode(['success' => true]);
        break;
    }
    default:
        http_response_code(400);
        echo json_encode(['error' => 'invalid_action']);
}
