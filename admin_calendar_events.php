<?php
// Endpoint AJAX : événements d'un mois donné, pour la navigation du mini-calendrier du tableau
// de bord admin (administrateur.php / admin_dashboard.js) — permet de parcourir n'importe quel
// mois, pas seulement les événements à venir depuis aujourd'hui.
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$year = (int) ($_GET['year'] ?? date('Y'));
$month = (int) ($_GET['month'] ?? date('n'));
if ($month < 1 || $month > 12) { $month = (int) date('n'); }

$stmt = $mysqli->prepare("SELECT id, titre_fr, date_debut, categorie FROM evenements WHERE YEAR(date_debut) = ? AND MONTH(date_debut) = ? ORDER BY date_debut ASC");
$stmt->bind_param('ii', $year, $month);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$events = array_map(function ($e) {
    return [
        'id' => (int) $e['id'],
        'titre' => $e['titre_fr'],
        'date' => $e['date_debut'],
        'jour' => (int) date('j', strtotime($e['date_debut'])),
        'mois' => (int) date('n', strtotime($e['date_debut'])),
        'annee' => (int) date('Y', strtotime($e['date_debut'])),
        'categorie' => $e['categorie'],
    ];
}, $rows);

echo json_encode(['year' => $year, 'month' => $month, 'events' => $events]);
