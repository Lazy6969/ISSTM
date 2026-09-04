<?php
// Endpoint déclenché par le bouton "Traduire toute la page" de filiere_detail.php : traduit
// d'un coup tous les champs et blocs de contenu d'une filière qui n'ont pas encore de
// traduction pour la langue demandée, puis les sauvegarde en base (traduction définitive,
// jamais refaite ensuite). Contrairement à la traduction automatique au fil des visites
// (limitée à quelques champs par page pour ne pas ralentir le chargement), cette action est
// un clic explicite de l'utilisateur : on ne plafonne donc pas le nombre d'appels, quitte à
// prendre plusieurs dizaines de secondes sur une filière encore entièrement en français.
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée.']);
    exit;
}

set_time_limit(0);

$input = json_decode(file_get_contents('php://input'), true);
$filiere_id = (int) ($input['filiere_id'] ?? 0);
$lang = $input['lang'] ?? '';

if ($filiere_id <= 0 || !in_array($lang, ['en', 'mg'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres invalides.']);
    exit;
}

// $needed est incrémenté dès qu'un champ a du contenu FR mais pas encore de traduction, que la
// traduction réussisse ou non : ça permet ensuite de distinguer "rien à traduire" (déjà fait)
// de "des traductions étaient nécessaires mais ont échoué" (quota/API indisponible), au lieu
// de dire faussement à l'utilisateur que tout est déjà traduit dans ce second cas.
function tf_field($mysqli, $table, $id, $row, $base, $lang, &$needed) {
    $key = $base . '_' . $lang;
    if (!empty($row[$key])) return 0;
    $fr = trim((string) ($row[$base . '_fr'] ?? ''));
    if ($fr === '') return 0;
    $needed++;
    $t = mymemory_translate($fr, $lang);
    if ($t === '') return 0;
    $stmt = $mysqli->prepare("UPDATE `$table` SET `$key` = ? WHERE id = ?");
    $stmt->bind_param('si', $t, $id);
    $stmt->execute();
    $stmt->close();
    return 1;
}

// Traduit un champ multi-lignes ligne par ligne (pour ne pas casser le découpage en cartes),
// et ne sauvegarde que si toutes les lignes ont pu être traduites (pas de mélange de langues).
function tf_field_lines($mysqli, $table, $id, $row, $base, $lang, &$needed) {
    $key = $base . '_' . $lang;
    if (!empty($row[$key])) return 0;
    $fr = trim((string) ($row[$base . '_fr'] ?? ''));
    if ($fr === '') return 0;
    $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $fr)), fn($l) => $l !== ''));
    if (empty($lines)) return 0;
    $needed++;
    $translated_lines = [];
    foreach ($lines as $line) {
        $t = mymemory_translate($line, $lang);
        if ($t === '') return 0;
        $translated_lines[] = $t;
    }
    $translated = implode("\n", $translated_lines);
    $stmt = $mysqli->prepare("UPDATE `$table` SET `$key` = ? WHERE id = ?");
    $stmt->bind_param('si', $translated, $id);
    $stmt->execute();
    $stmt->close();
    return count($translated_lines);
}

$stmt = $mysqli->prepare("SELECT * FROM filieres WHERE id = ?");
$stmt->bind_param('i', $filiere_id);
$stmt->execute();
$filiere = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$filiere) {
    http_response_code(404);
    echo json_encode(['error' => 'Filière introuvable.']);
    exit;
}

$total = 0;
$needed = 0;
$total += tf_field($mysqli, 'filieres', $filiere_id, $filiere, 'nom', $lang, $needed);
$total += tf_field($mysqli, 'filieres', $filiere_id, $filiere, 'description', $lang, $needed);
$total += tf_field($mysqli, 'filieres', $filiere_id, $filiere, 'historique', $lang, $needed);
$total += tf_field_lines($mysqli, 'filieres', $filiere_id, $filiere, 'avantages', $lang, $needed);
$total += tf_field_lines($mysqli, 'filieres', $filiere_id, $filiere, 'debouches', $lang, $needed);

$stmt = $mysqli->prepare("SELECT * FROM filiere_blocks WHERE filiere_id = ?");
$stmt->bind_param('i', $filiere_id);
$stmt->execute();
$blocks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($blocks as $block) {
    if ($block['block_type'] === 'image') {
        $total += tf_field($mysqli, 'filiere_blocks', $block['id'], $block, 'caption', $lang, $needed);
    } else {
        $total += tf_field($mysqli, 'filiere_blocks', $block['id'], $block, 'content', $lang, $needed);
    }
}

if ($needed > 0 && $total === 0) {
    // Des traductions étaient nécessaires mais aucune n'a abouti : très probablement le quota
    // journalier gratuit du service de traduction est épuisé (ou l'API est indisponible).
    http_response_code(503);
    echo json_encode(['error' => 'quota', 'message' => "Service de traduction temporairement indisponible (quota journalier atteint). Réessayez plus tard."]);
    exit;
}

echo json_encode(['success' => true, 'translated_count' => $total, 'already_done' => $needed === 0]);
