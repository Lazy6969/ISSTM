<?php
// Fonctions partagées par messages_prives.php et les endpoints AJAX associés (dm_send.php,
// dm_poll.php, dm_delete_message.php). Contrairement à messagerie_functions.php (une seule
// conversation de groupe fixe), ici chaque paire d'amis a sa propre conversation 1-à-1 — d'où une
// vraie table de conversations. L'accès à une conversation exige que les deux comptes soient
// amis (voir amis_functions.php) : si l'un des deux retire l'amitié, l'accès à l'historique existant
// reste possible en lecture (les messages ne sont pas perdus) mais aucun nouveau message ne peut
// être envoyé (voir dm_send.php).

require_once 'amis_functions.php';

function dm_require_login($isAjax = false) {
    amis_require_login($isAjax);
}

// Retourne l'id de la conversation entre $a et $b, la créant si besoin. La paire est toujours
// stockée avec user_a_id < user_b_id pour que la contrainte d'unicité fonctionne dans les 2 sens.
function dm_get_or_create_conversation($mysqli, $a, $b) {
    $lo = min((int) $a, (int) $b);
    $hi = max((int) $a, (int) $b);
    $stmt = $mysqli->prepare("SELECT id FROM dm_conversations WHERE user_a_id = ? AND user_b_id = ?");
    $stmt->bind_param('ii', $lo, $hi);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) return (int) $row['id'];

    $stmt = $mysqli->prepare("INSERT INTO dm_conversations (user_a_id, user_b_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $lo, $hi);
    $stmt->execute();
    $id = $mysqli->insert_id;
    $stmt->close();
    return $id;
}

// Vérifie que l'utilisateur courant est bien participant de la conversation demandée et retourne
// [utilisateur, ligne de conversation, id de l'autre participant]. Arrête sinon.
function dm_require_participant($mysqli, $conversation_id, $isAjax = false) {
    dm_require_login($isAjax);
    $user_id = (int) $_SESSION['user_id'];

    $stmt = $mysqli->prepare("SELECT id, nom, email, avatar_path FROM utilisateurs WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $self = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT * FROM dm_conversations WHERE id = ? AND (user_a_id = ? OR user_b_id = ?)");
    $stmt->bind_param("iii", $conversation_id, $user_id, $user_id);
    $stmt->execute();
    $conversation = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$self || !$conversation) {
        if ($isAjax) { http_response_code(403); echo json_encode(['error' => 'unauthorized']); exit; }
        header('Location: messages_prives.php');
        exit;
    }

    $other_id = (int) $conversation['user_a_id'] === $user_id ? (int) $conversation['user_b_id'] : (int) $conversation['user_a_id'];
    return [$self, $conversation, $other_id];
}

// Liste des conversations de l'utilisateur, avec l'autre participant, le dernier message et le
// nombre de messages non lus — sert à afficher la colonne de gauche de messages_prives.php.
function dm_get_conversations($mysqli, $user_id) {
    $sql = "SELECT c.id, c.user_a_id, c.user_b_id,
            (CASE WHEN c.user_a_id = ? THEN c.user_b_id ELSE c.user_a_id END) AS other_id
            FROM dm_conversations c
            WHERE c.user_a_id = ? OR c.user_b_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('iii', $user_id, $user_id, $user_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $conversations = [];
    foreach ($rows as $row) {
        $other = $mysqli->query("SELECT id, nom, avatar_path FROM utilisateurs WHERE id = " . (int) $row['other_id'])->fetch_assoc();
        if (!$other) continue;
        $last = $mysqli->query("
            SELECT content, created_at, sender_id FROM dm_messages
            WHERE conversation_id = " . (int) $row['id'] . " AND deleted_for_everyone_at IS NULL
            AND id NOT IN (SELECT message_id FROM dm_message_hides WHERE user_id = " . (int) $user_id . ")
            ORDER BY id DESC LIMIT 1
        ")->fetch_assoc();
        $unread = (int) $mysqli->query("
            SELECT COUNT(*) c FROM dm_messages
            WHERE conversation_id = " . (int) $row['id'] . " AND sender_id != " . (int) $user_id . " AND read_at IS NULL
        ")->fetch_assoc()['c'];
        $conversations[] = [
            'id' => (int) $row['id'],
            'other' => $other,
            'last_message' => $last,
            'unread' => $unread,
        ];
    }
    // Plus récentes en premier (par dernier message, conversations sans message à la fin).
    usort($conversations, function ($a, $b) {
        $ta = $a['last_message']['created_at'] ?? '';
        $tb = $b['last_message']['created_at'] ?? '';
        return strcmp($tb, $ta);
    });
    return $conversations;
}

// Nombre total de messages non lus de $user_id, tous fils de discussion confondus — utilisé pour
// la carte "Messages privés" de la sidebar communaute.php.
function dm_get_unread_total($mysqli, $user_id) {
    $stmt = $mysqli->prepare("SELECT COUNT(*) c FROM dm_messages m
                               JOIN dm_conversations c ON c.id = m.conversation_id
                               WHERE (c.user_a_id = ? OR c.user_b_id = ?) AND m.sender_id != ? AND m.read_at IS NULL AND m.deleted_for_everyone_at IS NULL");
    $stmt->bind_param('iii', $user_id, $user_id, $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['c'] ?? 0);
}
