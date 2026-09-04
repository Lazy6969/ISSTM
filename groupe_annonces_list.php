<?php
require_once 'db_connect.php';
require_once 'groupe_functions.php';
header('Content-Type: application/json');

$groupe_id = (int) ($_GET['groupe_id'] ?? 0);
[$self, $groupe, $membership] = groupe_require_membership($mysqli, $groupe_id, true);

$stmt = $mysqli->prepare("SELECT a.*, u.nom AS enseignant_nom
                           FROM groupe_annonces a
                           JOIN utilisateurs u ON u.id = a.enseignant_id
                           WHERE a.groupe_id = ?
                           ORDER BY a.created_at DESC");
$stmt->bind_param("i", $groupe_id);
$stmt->execute();
$annonces = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Marque le passage sur les annonces comme "lu" (même horodatage que la messagerie du groupe,
// voir groupe_unread_count()) dès l'ouverture du panneau.
$mysqli->query("UPDATE groupe_membres SET last_read_at = NOW() WHERE groupe_id = " . (int) $groupe_id . " AND user_id = " . (int) $self['id']);

echo json_encode([
    'annonces' => $annonces,
    'can_post' => $membership['role_in_group'] === 'enseignant',
]);
