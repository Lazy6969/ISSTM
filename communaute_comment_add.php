<?php
header('Content-Type: application/json');
include_once 'language.php';
require_once 'db_connect.php';
require_once 'communaute_functions.php';
communaute_require_login(true);

$self_id = (int) $_SESSION['user_id'];
$is_admin = $_SESSION['user_role'] === 'admin';
$post_id = (int) ($_POST['post_id'] ?? 0);
$contenu = trim($_POST['contenu'] ?? '');
$parent_id = isset($_POST['parent_id']) && ctype_digit((string) $_POST['parent_id']) ? (int) $_POST['parent_id'] : null;

if (!$post_id || $contenu === '') {
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

// Un seul niveau de réponse : si le commentaire ciblé est déjà une réponse, on rattache la
// nouvelle réponse à SON parent (le commentaire de premier niveau), pas à la réponse elle-même.
if ($parent_id !== null) {
    $parent_check = $mysqli->query("SELECT id, parent_id FROM communaute_comments WHERE id = $parent_id AND post_id = $post_id")->fetch_assoc();
    if (!$parent_check) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_parent']);
        exit;
    }
    if ($parent_check['parent_id'] !== null) {
        $parent_id = (int) $parent_check['parent_id'];
    }
}

$stmt = $mysqli->prepare("INSERT INTO communaute_comments (post_id, parent_id, user_id, contenu) VALUES (?, ?, ?, ?)");
$stmt->bind_param('iiis', $post_id, $parent_id, $self_id, $contenu);
$stmt->execute();
$comment_id = $mysqli->insert_id;
$stmt->close();

// --- Notifie l'auteur du commentaire de premier niveau auquel on répond (sauf s'il répond à
// son propre commentaire) ---
if ($parent_id !== null) {
    $parent_author = $mysqli->query("SELECT user_id FROM communaute_comments WHERE id = $parent_id")->fetch_assoc();
    if ($parent_author && (int) $parent_author['user_id'] !== $self_id) {
        $notif_stmt = $mysqli->prepare("INSERT INTO communaute_notifications (user_id, type, post_id, comment_id, actor_id) VALUES (?, 'reponse_commentaire', ?, ?, ?)");
        $notif_user_id = (int) $parent_author['user_id'];
        $notif_stmt->bind_param('iiii', $notif_user_id, $post_id, $parent_id, $self_id);
        $notif_stmt->execute();
        $notif_stmt->close();
    }
}

$row = $mysqli->query("
    SELECT c.*, u.nom, u.avatar_path
    FROM communaute_comments c JOIN utilisateurs u ON u.id = c.user_id
    WHERE c.id = $comment_id
")->fetch_assoc();

$html = communaute_render_comment($row, $self_id, $is_admin, $parent_id !== null);

echo json_encode([
    'html' => $html,
    'comment_id' => $comment_id,
    'parent_id' => $parent_id,
    'is_reply' => $parent_id !== null,
]);
