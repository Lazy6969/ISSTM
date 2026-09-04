<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once 'communaute_functions.php';
communaute_require_login(true);

$self_id = (int) $_SESSION['user_id'];
$is_admin = $_SESSION['user_role'] === 'admin';
$comment_id = (int) ($_POST['comment_id'] ?? 0);

if (!$comment_id) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_request']);
    exit;
}

$comment = $mysqli->query("SELECT * FROM communaute_comments WHERE id = $comment_id")->fetch_assoc();
if (!$comment) {
    http_response_code(404);
    echo json_encode(['error' => 'not_found']);
    exit;
}
if (!$is_admin && (int) $comment['user_id'] !== $self_id) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

// La suppression d'un commentaire de premier niveau supprime aussi ses réponses (FK ON DELETE CASCADE).
$stmt = $mysqli->prepare("DELETE FROM communaute_comments WHERE id = ?");
$stmt->bind_param('i', $comment_id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'comment_id' => $comment_id]);
