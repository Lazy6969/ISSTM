<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once 'communaute_functions.php';
communaute_require_login(true);

$self_id = (int) $_SESSION['user_id'];
$post_id = (int) ($_POST['post_id'] ?? 0);
$type = in_array($_POST['type'] ?? '', ['like', 'love'], true) ? $_POST['type'] : null;

if (!$post_id || !$type) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_request']);
    exit;
}

$check = $mysqli->query("SELECT id FROM communaute_posts WHERE id = $post_id");
if ($check->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'not_found']);
    exit;
}

$existing = $mysqli->prepare("SELECT type FROM communaute_reactions WHERE post_id = ? AND user_id = ?");
$existing->bind_param('ii', $post_id, $self_id);
$existing->execute();
$current = $existing->get_result()->fetch_assoc();
$existing->close();

if ($current && $current['type'] === $type) {
    // Un clic sur la réaction déjà active la retire (toggle off).
    $stmt = $mysqli->prepare("DELETE FROM communaute_reactions WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param('ii', $post_id, $self_id);
    $stmt->execute();
    $stmt->close();
    $my_reaction = null;
} else {
    $stmt = $mysqli->prepare("INSERT INTO communaute_reactions (post_id, user_id, type) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE type = VALUES(type)");
    $stmt->bind_param('iis', $post_id, $self_id, $type);
    $stmt->execute();
    $stmt->close();
    $my_reaction = $type;
}

$counts = ['like' => 0, 'love' => 0];
$res = $mysqli->query("SELECT type, COUNT(*) AS total FROM communaute_reactions WHERE post_id = $post_id GROUP BY type");
while ($row = $res->fetch_assoc()) { $counts[$row['type']] = (int) $row['total']; }

echo json_encode(['likes' => $counts['like'], 'loves' => $counts['love'], 'my_reaction' => $my_reaction]);
