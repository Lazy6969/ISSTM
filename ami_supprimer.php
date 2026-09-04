<?php
// Retire un ami (supprime la relation acceptée) ou annule une demande envoyée en attente.
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once 'amis_functions.php';
amis_require_login(true);

$self_id = (int) $_SESSION['user_id'];
$other_id = (int) ($_POST['user_id'] ?? 0);

if (!$other_id) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_request']);
    exit;
}

$stmt = $mysqli->prepare("DELETE FROM amis_demandes WHERE (demandeur_id = ? AND destinataire_id = ?) OR (demandeur_id = ? AND destinataire_id = ?)");
$stmt->bind_param('iiii', $self_id, $other_id, $other_id, $self_id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'statut' => 'aucune']);
