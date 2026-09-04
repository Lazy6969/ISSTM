<?php
require_once 'db_connect.php';
require_once 'groupe_functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

$groupe_id = (int) ($_POST['groupe_id'] ?? 0);
[$self, $groupe, $membership] = groupe_require_membership($mysqli, $groupe_id, true);

if ($membership['role_in_group'] !== 'enseignant') {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$type = $_POST['type'] ?? 'autre';
if (!in_array($type, ['examen', 'resultat', 'devoir', 'autre'], true)) {
    $type = 'autre';
}
$titre = trim($_POST['titre'] ?? '');
$description = trim($_POST['description'] ?? '');
$date_echeance = trim($_POST['date_echeance'] ?? '');
$date_echeance = $date_echeance !== '' ? $date_echeance : null;

if ($titre === '') {
    http_response_code(400);
    echo json_encode(['error' => 'missing_title']);
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO groupe_annonces (groupe_id, enseignant_id, type, titre, description, date_echeance) VALUES (?,?,?,?,?,?)");
$descOrNull = $description !== '' ? $description : null;
$stmt->bind_param("iissss", $groupe_id, $self['id'], $type, $titre, $descOrNull, $date_echeance);
$stmt->execute();
$new_id = $mysqli->insert_id;
$stmt->close();

$row = $mysqli->query("SELECT a.*, u.nom AS enseignant_nom FROM groupe_annonces a JOIN utilisateurs u ON u.id = a.enseignant_id WHERE a.id = $new_id")->fetch_assoc();
echo json_encode(['success' => true, 'annonce' => $row]);
