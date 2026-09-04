<?php
// Accepte ou refuse une demande d'ami reçue.
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once 'amis_functions.php';
amis_require_login(true);

$self_id = (int) $_SESSION['user_id'];
$from_user_id = (int) ($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$from_user_id || !in_array($action, ['accepter', 'refuser'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_request']);
    exit;
}

$stmt = $mysqli->prepare("SELECT id FROM amis_demandes WHERE demandeur_id = ? AND destinataire_id = ? AND statut = 'en_attente'");
$stmt->bind_param('ii', $from_user_id, $self_id);
$stmt->execute();
$demande = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$demande) {
    http_response_code(404);
    echo json_encode(['error' => 'not_found']);
    exit;
}

$new_statut = $action === 'accepter' ? 'acceptee' : 'refusee';
$stmt = $mysqli->prepare("UPDATE amis_demandes SET statut = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param('si', $new_statut, $demande['id']);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'statut' => $new_statut === 'acceptee' ? 'amis' : 'aucune']);
