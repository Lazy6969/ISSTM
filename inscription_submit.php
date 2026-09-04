<?php
require_once 'db_connect.php';
require_once 'language.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'method_not_allowed']);
    exit;
}

function field($key) {
    return trim($_POST[$key] ?? '');
}

$nom = field('nom');
$prenoms = field('prenoms');
$sexe = field('sexe');
$date_naissance = field('date_naissance');
$lieu_naissance = field('lieu_naissance');
$cin = field('cin');
$nationalite = field('nationalite');
$annee_bacc = field('annee_bacc');
$serie_bacc = field('serie_bacc');
$serie_bacc_autre = field('serie_bacc_autre');
$mention_bacc = field('mention_bacc');
$code_redoublement = field('code_redoublement');
$adresse = field('adresse');
$telephone = field('telephone');
$email = field('email');
$nom_pere = field('nom_pere');
$profession_pere = field('profession_pere');
$nom_mere = field('nom_mere');
$profession_mere = field('profession_mere');
$adresse_parents = field('adresse_parents');
$contact_parents = field('contact_parents');
$contact_parents_2 = field('contact_parents_2');
$pays = field('pays');
$filiere_id = ctype_digit(field('filiere_id')) ? (int) field('filiere_id') : 0;
$niveau = field('niveau');

// Champs obligatoires (CIN volontairement absent : laissé vide si le candidat est mineur)
$required = [
    'nom' => $nom, 'prenoms' => $prenoms, 'sexe' => $sexe, 'date_naissance' => $date_naissance,
    'lieu_naissance' => $lieu_naissance, 'nationalite' => $nationalite, 'annee_bacc' => $annee_bacc,
    'serie_bacc' => $serie_bacc, 'mention_bacc' => $mention_bacc, 'code_redoublement' => $code_redoublement,
    'adresse' => $adresse, 'telephone' => $telephone, 'email' => $email, 'pays' => $pays, 'niveau' => $niveau,
];
foreach ($required as $key => $value) {
    if ($value === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'missing_field', 'field' => $key]);
        exit;
    }
}

if (!in_array($sexe, ['M', 'F'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_sexe']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_email']);
    exit;
}
if ($filiere_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'missing_field', 'field' => 'filiere_id']);
    exit;
}
if (!in_array($code_redoublement, ['N', 'R'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_redoublement']);
    exit;
}
if ($serie_bacc === 'AUTRE' && $serie_bacc_autre === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'missing_field', 'field' => 'serie_bacc_autre']);
    exit;
}

// Photo d'identité : requise, n'importe quel format d'image, sans limite de taille applicative
// (les plafonds réels restent ceux, déjà très généreux, de post_max_size/upload_max_filesize).
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'missing_photo']);
    exit;
}
if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'upload_error']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
finfo_close($finfo);
if (strpos($mime, 'image/') !== 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_photo_type']);
    exit;
}

$upload_dir = 'uploads/preinscriptions/';
$ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
$photo_name = 'photo_' . uniqid() . ($ext !== '' ? '.' . $ext : '');
$photo_path = $upload_dir . $photo_name;

if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'upload_failed']);
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO preinscriptions
    (nom, prenoms, sexe, date_naissance, lieu_naissance, cin, nationalite, annee_bacc, serie_bacc, serie_bacc_autre,
     mention_bacc, code_redoublement, adresse, telephone, email, nom_pere, profession_pere, nom_mere, profession_mere,
     adresse_parents, contact_parents, contact_parents_2, pays, filiere_id, niveau, photo_path)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

$stmt->bind_param(
    "sssssssssssssssssssssssiss",
    $nom, $prenoms, $sexe, $date_naissance, $lieu_naissance, $cin, $nationalite, $annee_bacc, $serie_bacc,
    $serie_bacc_autre, $mention_bacc, $code_redoublement, $adresse, $telephone, $email, $nom_pere, $profession_pere,
    $nom_mere, $profession_mere, $adresse_parents, $contact_parents, $contact_parents_2, $pays, $filiere_id, $niveau,
    $photo_path
);

if (!$stmt->execute()) {
    unlink($photo_path);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'db_error']);
    exit;
}

echo json_encode(['success' => true]);
