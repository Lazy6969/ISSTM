<?php
// Fonctions partagées par les pages de groupes de classe (mes_groupes.php, groupe_chat.php et
// les endpoints AJAX associés). Contrairement à messagerie_functions.php (une seule conversation
// fixe), ici un utilisateur (enseignant ou étudiant) peut appartenir à plusieurs groupes, chacun
// avec sa propre liste de membres — d'où une vraie table de groupes + une table d'appartenance.

// Retourne l'id du groupe si l'utilisateur appartient à exactement 1 groupe (cas simple : bouton
// direct vers ce groupe), ou null s'il en a 0 ou plusieurs (cas ambigu : renvoyer vers
// mes_groupes.php, la liste complète). Utilisé par la barre latérale de communaute.php.
function groupe_get_single_group_id($mysqli, $user_id) {
    $res = $mysqli->query("SELECT groupe_id, nom FROM groupe_membres gm JOIN groupes_classe g ON g.id = gm.groupe_id WHERE gm.user_id = " . (int) $user_id . " AND gm.is_banned = 0");
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    return count($rows) === 1 ? $rows[0] : null;
}

function groupe_require_login($isAjax = false) {
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
// [utilisateur, ligne du groupe, ligne d'appartenance (rôle + last_read_at)]. Arrête sinon.
function groupe_require_membership($mysqli, $groupe_id, $isAjax = false) {
    groupe_require_login($isAjax);
    $user_id = (int) $_SESSION['user_id'];

    $stmt = $mysqli->prepare("SELECT id, nom, email, avatar_path, role FROM utilisateurs WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $self = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT g.*, m.role_in_group, m.last_read_at, m.is_banned, m.is_delegate
                               FROM groupes_classe g
                               JOIN groupe_membres m ON m.groupe_id = g.id AND m.user_id = ?
                               WHERE g.id = ?");
    $stmt->bind_param("ii", $user_id, $groupe_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Un membre banni perd tout accès au groupe (messages compris) jusqu'à ce qu'un enseignant
    // le rétablisse — sa ligne d'appartenance reste en base pour rendre ce rétablissement simple.
    if (!$self || !$row || (int) $row['is_banned'] === 1) {
        if ($isAjax) { http_response_code(403); echo json_encode(['error' => 'unauthorized']); exit; }
        header('Location: mes_groupes.php');
        exit;
    }

    $groupe = $row;
    $membership = ['role_in_group' => $row['role_in_group'], 'last_read_at' => $row['last_read_at'], 'is_delegate' => (int) $row['is_delegate']];
    return [$self, $groupe, $membership];
}

// Enseignant du groupe OU délégué désigné : seuls ces deux profils peuvent consulter/imprimer
// l'historique complet de la liste de présence (les autres étudiants voient juste les séances).
function groupe_can_download_presence($membership) {
    return $membership['role_in_group'] === 'enseignant' || (int) $membership['is_delegate'] === 1;
}

// Code court, lisible à l'oral/au tableau (sans caractères ambigus 0/O/1/I), utilisé par les
// étudiants pour rejoindre un groupe.
function groupe_generate_code($mysqli, $length = 6) {
    $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    do {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $exists = $mysqli->query("SELECT id FROM groupes_classe WHERE code_unique = '" . $mysqli->real_escape_string($code) . "'")->num_rows > 0;
    } while ($exists);
    return $code;
}

// Nombre de messages non lus dans un groupe pour un membre donné (messages des AUTRES membres,
// postérieurs à son dernier passage), plus les annonces (examens, résultats, devoirs...) publiées
// par les enseignants depuis le même dernier passage — sert de jauge de notification unique.
function groupe_unread_count($mysqli, $groupe_id, $user_id, $last_read_at) {
    $dateClause = $last_read_at ? " AND created_at > '" . $mysqli->real_escape_string($last_read_at) . "'" : "";

    $sql = "SELECT COUNT(*) c FROM groupe_messages
            WHERE groupe_id = " . (int) $groupe_id . "
            AND sender_id != " . (int) $user_id . "
            AND deleted_for_everyone_at IS NULL" . $dateClause;
    $messages = (int) $mysqli->query($sql)->fetch_assoc()['c'];

    $sqlAnnonces = "SELECT COUNT(*) c FROM groupe_annonces
                     WHERE groupe_id = " . (int) $groupe_id . "
                     AND enseignant_id != " . (int) $user_id . $dateClause;
    $annonces = (int) $mysqli->query($sqlAnnonces)->fetch_assoc()['c'];

    return $messages + $annonces;
}
