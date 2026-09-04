<?php
require_once 'db_connect.php';
require_once 'groupe_perso_functions.php';
header('Content-Type: application/json');

$groupe_id = (int) ($_GET['groupe_id'] ?? 0);
[$self, $groupe, $membership] = groupe_perso_require_membership($mysqli, $groupe_id, true);

$stmt = $mysqli->prepare("SELECT u.id, u.nom, u.avatar_path, m.is_banned
                           FROM groupe_utilisateurs_membres m
                           JOIN utilisateurs u ON u.id = m.user_id
                           WHERE m.groupe_id = ?
                           ORDER BY m.is_banned ASC, u.nom ASC");
$stmt->bind_param("i", $groupe_id);
$stmt->execute();
$members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$createur_id = (int) $groupe['createur_id'];
foreach ($members as &$m) {
    $m['id'] = (int) $m['id'];
    $m['is_banned'] = (int) $m['is_banned'] === 1;
    $m['is_self'] = $m['id'] === (int) $self['id'];
    $m['is_createur'] = $m['id'] === $createur_id;
}
unset($m);

echo json_encode([
    'members' => $members,
    'can_moderate' => $createur_id === (int) $self['id'],
]);
