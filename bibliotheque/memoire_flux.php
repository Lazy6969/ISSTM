<?php
include_once '../language.php';
require_once '../db_connect.php';
require 'config.php';
require 'functions.php';

// Ce fichier sert les octets du PDF, mais UNIQUEMENT si :
//  - un jeton valide (généré par consulter_memoire.php) est fourni
//  - la requête vient bien du lecteur JS (en-tête personnalisé)
// Il n'est jamais lié directement depuis une page ou un menu.

$id = $_GET['id'] ?? null;
$token = $_GET['token'] ?? null;

$enteteVisionneuse = $_SERVER['HTTP_X_REQUETE_VISIONNEUSE'] ?? null;

if (!$id || !$token || !$enteteVisionneuse) {
    http_response_code(403);
    exit('Accès refusé.');
}

$session = $_SESSION['memoire_token_' . $id] ?? null;

if (!$session || !hash_equals($session['token'], $token) || $session['expire'] < time()) {
    http_response_code(403);
    exit('Lien expiré ou invalide. Merci de recharger la page de consultation.');
}

$stmt = $pdo->prepare("SELECT * FROM memoires WHERE id = ?");
$stmt->execute([$id]);
$memoire = $stmt->fetch();

if (!$memoire) {
    http_response_code(404);
    exit('Document introuvable.');
}

$chemin = __DIR__ . '/uploads/' . $memoire['chemin_fichier'];

if (!file_exists($chemin)) {
    http_response_code(404);
    exit('Fichier manquant sur le serveur.');
}

header('Content-Type: application/pdf');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Length: ' . filesize($chemin));
readfile($chemin);
exit;
