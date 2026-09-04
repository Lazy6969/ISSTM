<?php
// ==== Paramètres de connexion à adapter selon votre environnement ====
// Base de données séparée de isstm_db (voir bibliotheque/README.md) : cette
// mini-application garde sa propre connexion PDO, réutilisée sur toutes ses pages.
// Constantes préfixées BIB_ pour ne jamais entrer en collision avec les constantes
// DB_SERVER / DB_USERNAME / DB_PASSWORD / DB_NAME définies par le db_connect.php
// principal de l'ISSTM (inclus juste avant celui-ci sur chaque page de la bibliothèque).
define('BIB_DB_HOST', 'localhost');
define('BIB_DB_NAME', 'bibliotheque');
define('BIB_DB_USER', 'root');
define('BIB_DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . BIB_DB_HOST . ";dbname=" . BIB_DB_NAME . ";charset=utf8mb4",
        BIB_DB_USER,
        BIB_DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// La session est déjà démarrée par language.php (inclus avant ce fichier sur
// chaque page de la bibliothèque, pour unifier la session avec le reste du site
// ISSTM). On ne la redémarre que si, pour une raison quelconque, ce fichier est
// utilisé seul.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
