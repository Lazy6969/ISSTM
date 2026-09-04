<?php
// Envoie une demande d'ami (ou la fait passer de "refusee" à "en_attente" si une ancienne demande
// refusée existe déjà entre les deux comptes, pour permettre de retenter plus tard).
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once 'amis_functions.php';
amis_require_login(true);

$self_id = (int) $_SESSION['user_id'];
$target_id = (int) ($_POST['user_id'] ?? 0);

if (!$target_id || $target_id === $self_id) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_request']);
    exit;
}

$target = $mysqli->query("SELECT id FROM utilisateurs WHERE id = $target_id")->fetch_assoc();
if (!$target) {
    http_response_code(404);
    echo json_encode(['error' => 'not_found']);
    exit;
}

$statut = amis_get_statut($mysqli, $self_id, $target_id);
if ($statut !== 'aucune') {
    http_response_code(409);
    echo json_encode(['error' => 'already_exists', 'statut' => $statut]);
    exit;
}

// Une éventuelle ancienne ligne "refusee" entre ces deux mêmes comptes (dans n'importe quel sens)
// est réutilisée plutôt que dupliquée, pour respecter la contrainte d'unicité de la paire.
$existing = $mysqli->query("SELECT id FROM amis_demandes WHERE (demandeur_id = $self_id AND destinataire_id = $target_id) OR (demandeur_id = $target_id AND destinataire_id = $self_id)")->fetch_assoc();
if ($existing) {
    $stmt = $mysqli->prepare("UPDATE amis_demandes SET demandeur_id = ?, destinataire_id = ?, statut = 'en_attente', updated_at = NOW() WHERE id = ?");
    $stmt->bind_param('iii', $self_id, $target_id, $existing['id']);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $mysqli->prepare("INSERT INTO amis_demandes (demandeur_id, destinataire_id, statut) VALUES (?, ?, 'en_attente')");
    $stmt->bind_param('ii', $self_id, $target_id);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => true, 'statut' => 'en_attente_envoyee']);
