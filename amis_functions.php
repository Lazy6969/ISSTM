<?php
// Fonctions partagées par l'annuaire (annuaire.php), les profils publics (profil_public.php),
// la liste d'amis (mes_amis.php) et les endpoints AJAX associés (ami_demande.php,
// ami_repondre.php, ami_supprimer.php). Une amitié acceptée EST une ligne de amis_demandes avec
// statut='acceptee' (pas de table séparée) — la relation est donc toujours interrogée dans les
// deux sens (demandeur_id, destinataire_id).

function amis_require_login($isAjax = false) {
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

// Retourne l'état de la relation entre $a et $b : 'aucune', 'en_attente_envoyee' (a a demandé à
// b), 'en_attente_recue' (b a demandé à a), ou 'amis' (acceptée, peu importe qui a demandé).
function amis_get_statut($mysqli, $a, $b) {
    if ((int) $a === (int) $b) return 'soi_meme';
    $stmt = $mysqli->prepare("SELECT demandeur_id, destinataire_id, statut FROM amis_demandes WHERE (demandeur_id = ? AND destinataire_id = ?) OR (demandeur_id = ? AND destinataire_id = ?)");
    $stmt->bind_param('iiii', $a, $b, $b, $a);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || $row['statut'] === 'refusee') return 'aucune';
    if ($row['statut'] === 'acceptee') return 'amis';
    // en_attente
    return (int) $row['demandeur_id'] === (int) $a ? 'en_attente_envoyee' : 'en_attente_recue';
}

// Liste des amis (relations acceptées) d'un utilisateur, avec leurs infos de base.
function amis_get_liste($mysqli, $user_id) {
    $sql = "SELECT u.id, u.nom, u.avatar_path, u.role
            FROM amis_demandes d
            JOIN utilisateurs u ON u.id = (CASE WHEN d.demandeur_id = ? THEN d.destinataire_id ELSE d.demandeur_id END)
            WHERE (d.demandeur_id = ? OR d.destinataire_id = ?) AND d.statut = 'acceptee'
            ORDER BY u.nom ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('iii', $user_id, $user_id, $user_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// Amis communs entre $a et $b (intersection de leurs listes d'amis acceptées) — utilisé sur
// profil_public.php pour "X amis en commun". Listes bornées à la taille d'un réseau d'institut,
// une intersection PHP simple reste largement suffisante sans complexifier la requête SQL.
function amis_get_mutuels($mysqli, $a, $b) {
    $friendsA = amis_get_liste($mysqli, $a);
    $idsB = array_map('intval', array_column(amis_get_liste($mysqli, $b), 'id'));
    return array_values(array_filter($friendsA, function ($f) use ($idsB) {
        return in_array((int) $f['id'], $idsB, true);
    }));
}

// Filière + niveau d'un étudiant (aucune colonne directe sur utilisateurs : passe par sa
// préinscription approuvée). Retourne null si l'utilisateur n'est pas étudiant ou n'a pas de
// préinscription approuvée liée (ex: compte créé manuellement par l'admin).
function amis_get_filiere($mysqli, $user_id) {
    $stmt = $mysqli->prepare("SELECT f.nom_fr AS filiere, p.niveau
                               FROM preinscriptions p JOIN filieres f ON f.id = p.filiere_id
                               WHERE p.user_id = ? AND p.status = 'approuve'
                               ORDER BY p.created_at DESC LIMIT 1");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

// Suggestions d'amis : utilisateurs sans aucune relation (acceptée ou en attente, dans les deux
// sens) avec $user_id, priorité aux étudiants de la même filière que $user_id, complété au besoin
// par un tirage aléatoire parmi les autres comptes éligibles.
function amis_get_suggestions($mysqli, $user_id, $limit = 8) {
    $limit = (int) $limit;
    $filiere = amis_get_filiere($mysqli, $user_id);

    $suggestions = [];
    if ($filiere) {
        $stmt = $mysqli->prepare("SELECT u.id, u.nom, u.avatar_path, u.role
                                   FROM utilisateurs u
                                   JOIN preinscriptions p ON p.user_id = u.id AND p.status = 'approuve'
                                   WHERE u.role IN ('enseignant', 'etudiant', 'admin') AND u.id != ?
                                   AND p.filiere_id = (SELECT filiere_id FROM preinscriptions WHERE user_id = ? AND status = 'approuve' ORDER BY created_at DESC LIMIT 1)
                                   AND NOT EXISTS (SELECT 1 FROM amis_demandes d WHERE (d.demandeur_id = ? AND d.destinataire_id = u.id) OR (d.demandeur_id = u.id AND d.destinataire_id = ?))
                                   ORDER BY RAND() LIMIT ?");
        $stmt->bind_param('iiiii', $user_id, $user_id, $user_id, $user_id, $limit);
        $stmt->execute();
        $suggestions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    $remaining = $limit - count($suggestions);
    if ($remaining > 0) {
        $exclude_ids = array_merge([$user_id], array_column($suggestions, 'id'));
        $placeholders = implode(',', array_fill(0, count($exclude_ids), '?'));
        $types = str_repeat('i', count($exclude_ids)) . 'iii';
        $stmt = $mysqli->prepare("SELECT u.id, u.nom, u.avatar_path, u.role
                                   FROM utilisateurs u
                                   WHERE u.role IN ('enseignant', 'etudiant', 'admin') AND u.id NOT IN ($placeholders)
                                   AND NOT EXISTS (SELECT 1 FROM amis_demandes d WHERE (d.demandeur_id = ? AND d.destinataire_id = u.id) OR (d.demandeur_id = u.id AND d.destinataire_id = ?))
                                   ORDER BY RAND() LIMIT ?");
        $params = array_merge($exclude_ids, [$user_id, $user_id, $remaining]);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $more = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $suggestions = array_merge($suggestions, $more);
    }

    foreach ($suggestions as &$s) {
        $s['filiere'] = $s['role'] === 'etudiant' ? amis_get_filiere($mysqli, $s['id']) : null;
    }
    unset($s);

    return $suggestions;
}
