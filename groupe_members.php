<?php
require_once 'db_connect.php';
require_once 'groupe_functions.php';
header('Content-Type: application/json');

$groupe_id = (int) ($_GET['groupe_id'] ?? 0);
[$self, $groupe, $membership] = groupe_require_membership($mysqli, $groupe_id, true);

$stmt = $mysqli->prepare("SELECT u.id, u.nom, u.avatar_path, m.role_in_group, m.is_banned, m.is_delegate
                           FROM groupe_membres m
                           JOIN utilisateurs u ON u.id = m.user_id
                           WHERE m.groupe_id = ?
                           ORDER BY m.role_in_group ASC, m.is_banned ASC, u.nom ASC");
$stmt->bind_param("i", $groupe_id);
$stmt->execute();
$members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($members as &$m) {
    $m['id'] = (int) $m['id'];
    $m['is_banned'] = (int) $m['is_banned'] === 1;
    $m['is_delegate'] = (int) $m['is_delegate'] === 1;
    $m['is_self'] = $m['id'] === (int) $self['id'];
}
unset($m);

echo json_encode([
    'members' => $members,
    'can_moderate' => $membership['role_in_group'] === 'enseignant',
]);
