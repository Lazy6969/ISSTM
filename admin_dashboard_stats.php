<?php
// Endpoint AJAX : snapshot des statistiques du tableau de bord admin, interrogé en boucle par
// admin_dashboard.js pour un affichage "temps réel". Mesure aussi le vrai temps de réponse du
// serveur (pas de charge CPU système disponible sous Windows/XAMPP) pour la jauge de performance.
$t_start = microtime(true);

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$stats = [];

$stats['utilisateurs_total'] = (int) $mysqli->query("SELECT COUNT(*) c FROM utilisateurs")->fetch_assoc()['c'];
$stats['etudiants'] = (int) $mysqli->query("SELECT COUNT(*) c FROM utilisateurs WHERE role='etudiant'")->fetch_assoc()['c'];
$stats['enseignants'] = (int) $mysqli->query("SELECT COUNT(*) c FROM utilisateurs WHERE role='enseignant'")->fetch_assoc()['c'];
$stats['bibliotheque'] = (int) $mysqli->query("SELECT COUNT(*) c FROM utilisateurs WHERE role='bibliotheque'")->fetch_assoc()['c'];
$stats['documents'] = (int) $mysqli->query("SELECT COUNT(*) c FROM documents")->fetch_assoc()['c'];
$stats['publications_communaute'] = (int) $mysqli->query("SELECT COUNT(*) c FROM communaute_posts")->fetch_assoc()['c'];
$stats['actualites_publiees'] = (int) $mysqli->query("SELECT COUNT(*) c FROM news_articles WHERE status='publie'")->fetch_assoc()['c'];
$stats['evenements_total'] = (int) $mysqli->query("SELECT COUNT(*) c FROM evenements")->fetch_assoc()['c'];
$stats['evenements_a_venir'] = (int) $mysqli->query("SELECT COUNT(*) c FROM evenements WHERE date_debut >= NOW()")->fetch_assoc()['c'];
$stats['comptes_en_attente'] = (int) $mysqli->query("SELECT COUNT(*) c FROM preinscriptions WHERE status='en_attente'")->fetch_assoc()['c'];
$stats['utilisateurs_en_ligne'] = (int) $mysqli->query("SELECT COUNT(*) c FROM utilisateurs WHERE last_activity > NOW() - INTERVAL 5 MINUTE")->fetch_assoc()['c'];
$stats['vues_total'] = (int) $mysqli->query("SELECT COUNT(*) c FROM page_views")->fetch_assoc()['c'];
$stats['vues_aujourdhui'] = (int) $mysqli->query("SELECT COUNT(*) c FROM page_views WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];
$stats['filieres_total'] = (int) $mysqli->query("SELECT COUNT(*) c FROM filieres")->fetch_assoc()['c'];
$stats['enseignants_fiches'] = (int) $mysqli->query("SELECT COUNT(*) c FROM teachers")->fetch_assoc()['c'];
$stats['galerie_albums'] = (int) $mysqli->query("SELECT COUNT(*) c FROM gallery_albums WHERE status='publie'")->fetch_assoc()['c'];
$stats['newsletter_abonnes'] = (int) $mysqli->query("SELECT COUNT(*) c FROM newsletter_subscribers")->fetch_assoc()['c'];
$stats['partenaires_total'] = (int) $mysqli->query("SELECT COUNT(*) c FROM partenaires")->fetch_assoc()['c'];

// Étudiants par filière (adapté de admin_etudiants.php : total approuvés par filière, toutes années confondues)
$par_filiere = $mysqli->query("
    SELECT f.nom_fr AS filiere, COUNT(*) AS total
    FROM preinscriptions p JOIN filieres f ON f.id = p.filiere_id
    WHERE p.status = 'approuve'
    GROUP BY p.filiere_id
    ORDER BY total DESC
")->fetch_all(MYSQLI_ASSOC);
$stats['par_filiere'] = $par_filiere;

// Prochains événements (mini-calendrier) : depuis le début de la journée en cours (pas NOW() à la
// seconde près) pour que les événements du jour même restent visibles sur toute la journée.
$prochains = $mysqli->query("
    SELECT id, titre_fr, date_debut, categorie
    FROM evenements
    WHERE date_debut >= CURDATE()
    ORDER BY date_debut ASC
    LIMIT 8
")->fetch_all(MYSQLI_ASSOC);
$stats['prochains_evenements'] = array_map(fn($e) => [
    'titre' => $e['titre_fr'],
    'date' => $e['date_debut'],
    'jour' => (int) date('j', strtotime($e['date_debut'])),
    'mois' => (int) date('n', strtotime($e['date_debut'])),
    'annee' => (int) date('Y', strtotime($e['date_debut'])),
    'categorie' => $e['categorie'],
], $prochains);

$stats['temps_reponse_ms'] = round((microtime(true) - $t_start) * 1000, 1);

echo json_encode($stats);
