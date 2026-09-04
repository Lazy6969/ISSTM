<?php
// Fonctions partagées par les pages de groupes créés librement par les utilisateurs
// (mes_groupes_perso.php, groupe_perso_chat.php et les endpoints AJAX associés).
// Distinct des groupes de classe (groupe_functions.php, table groupes_classe) qui sont réservés
// aux enseignants/admin et liés à une filière/niveau : ici n'importe quel utilisateur (enseignant,
// étudiant ou admin) peut créer, rejoindre, quitter ou supprimer un groupe.

function groupe_perso_require_login($isAjax = false) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
        if ($isAjax) { http_response_code(403); echo json_encode(['error' => 'unauthorized']); exit; }
        header('Location: index.php');
        exit;
    }
    if (!in_array($_SESSION['user_role'], ['enseignant', 'etudiant', 'admin'], true)) {
        if ($isAjax) { http_response_code(403); echo json_encode(['error' => 'unauthorized']); exit; }
        header('Location: index.php');
        exit;
    }
}

// Vérifie que l'utilisateur courant est bien membre du groupe demandé et retourne
// [utilisateur, ligne du groupe, ligne d'appartenance]. Arrête sinon.
function groupe_perso_require_membership($mysqli, $groupe_id, $isAjax = false) {
    groupe_perso_require_login($isAjax);
    $user_id = (int) $_SESSION['user_id'];

    $stmt = $mysqli->prepare("SELECT id, nom, email, avatar_path, role FROM utilisateurs WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $self = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT g.*, m.is_banned, m.last_read_at
                               FROM groupes_utilisateurs g
                               JOIN groupe_utilisateurs_membres m ON m.groupe_id = g.id AND m.user_id = ?
                               WHERE g.id = ?");
    $stmt->bind_param("ii", $user_id, $groupe_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$self || !$row || (int) $row['is_banned'] === 1) {
        if ($isAjax) { http_response_code(403); echo json_encode(['error' => 'unauthorized']); exit; }
        header('Location: mes_groupes_perso.php');
        exit;
    }

    $groupe = $row;
    $membership = ['last_read_at' => $row['last_read_at']];
    return [$self, $groupe, $membership];
}

// Code court, lisible à l'oral/au tableau (sans caractères ambigus 0/O/1/I), utilisé pour
// rejoindre un groupe.
function groupe_perso_generate_code($mysqli, $length = 6) {
    $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    do {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $exists = $mysqli->query("SELECT id FROM groupes_utilisateurs WHERE code_unique = '" . $mysqli->real_escape_string($code) . "'")->num_rows > 0;
    } while ($exists);
    return $code;
}

// Nombre de messages non lus dans un groupe pour un membre donné.
function groupe_perso_unread_count($mysqli, $groupe_id, $user_id, $last_read_at) {
    $dateClause = $last_read_at ? " AND created_at > '" . $mysqli->real_escape_string($last_read_at) . "'" : "";
    $sql = "SELECT COUNT(*) c FROM groupe_utilisateurs_messages
            WHERE groupe_id = " . (int) $groupe_id . "
            AND sender_id != " . (int) $user_id . "
            AND deleted_for_everyone_at IS NULL" . $dateClause;
    return (int) $mysqli->query($sql)->fetch_assoc()['c'];
}

function groupe_perso_unlink_file($path) {
    if ($path && strpos($path, 'uploads/') === 0 && file_exists($path)) {
        unlink($path);
    }
}
