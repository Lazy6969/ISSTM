<?php
// Endpoint AJAX : liste les notifications récentes de l'utilisateur (nouvelles publications,
// réponses à ses commentaires). Ne marque plus tout comme lu automatiquement à l'ouverture (voir
// notification_ouvrir.php pour le marquage individuel au clic, et notifications.php pour les
// actions explicites lues/non lues/suppression).
header('Content-Type: application/json');
include_once 'language.php';
require_once 'db_connect.php';
require_once 'communaute_functions.php';
communaute_require_login(true);

$self_id = (int) $_SESSION['user_id'];

$rows = $mysqli->query("
    SELECT n.*, u.nom AS actor_nom
    FROM communaute_notifications n
    LEFT JOIN utilisateurs u ON u.id = n.actor_id
    WHERE n.user_id = $self_id
    ORDER BY n.created_at DESC
    LIMIT 20
")->fetch_all(MYSQLI_ASSOC);

$items = array_map('communaute_notification_render', $rows);
foreach ($items as &$it) { unset($it['created_at_raw']); }
unset($it);

echo json_encode(['items' => $items]);
