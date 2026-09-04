<?php
include_once '../../language.php';
require_once '../../db_connect.php';
require '../config.php';

// On ne détruit que les clés de session propres au compte bibliothèque autonome
// (admin_id / admin_username / admin_role) : si l'utilisateur est arrivé ici via
// le pont avec l'admin ISSTM (user_logged_in / user_role), sa session sur le
// reste du site ISSTM doit rester intacte.
unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_role']);

header('Location: ' . SITE_URL . '/index.php');
exit;
