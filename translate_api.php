<?php
// --- Sécurité : S'assurer que la requête vient bien de notre site et est de type POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Méthode non autorisée.']);
    exit;
}

header('Content-Type: application/json');

require_once 'db_connect.php';

// --- Récupération des données envoyées par JavaScript ---
$input = json_decode(file_get_contents('php://input'), true);
$text_to_translate = $input['text'] ?? '';
$target_language = $input['target_lang'] ?? 'en';
$source_language = $input['source_lang'] ?? 'fr'; // Utilise la langue source envoyée, avec 'fr' comme secours

if (trim((string) $text_to_translate) === '') {
    echo json_encode(['translatedText' => '']);
    exit;
}

// Utilise le même moteur que le reste du site (Google en priorité, MyMemory en secours) :
// voir mymemory_translate() dans db_connect.php.
$translated_text = mymemory_translate($text_to_translate, $target_language, $source_language);

if ($translated_text === '') {
    echo json_encode(['error' => 'Traduction indisponible pour le moment (quota atteint ou service inaccessible).']);
    exit;
}

echo json_encode(['translatedText' => $translated_text]);
