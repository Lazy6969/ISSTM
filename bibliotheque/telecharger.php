<?php
include_once '../language.php';
require_once '../db_connect.php';
require 'config.php';
require 'functions.php';

if (!estUtilisateurConnecte()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die('Fichier introuvable.');
}

$stmt = $pdo->prepare("SELECT * FROM canevas WHERE id = ?");
$stmt->execute([$id]);
$canevas = $stmt->fetch();

if (!$canevas) {
    die('Fichier introuvable.');
}

$chemin = __DIR__ . '/uploads/' . $canevas['chemin_fichier'];

if (!file_exists($chemin)) {
    die('Le fichier physique est manquant sur le serveur.');
}

$mimeTypes = [
    'word' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'pdf'  => 'application/pdf',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
];

header('Content-Description: File Transfer');
header('Content-Type: ' . ($mimeTypes[$canevas['type_fichier']] ?? 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . basename($canevas['chemin_fichier']) . '"');
header('Content-Length: ' . filesize($chemin));
header('Cache-Control: no-cache, must-revalidate');
readfile($chemin);
exit;
