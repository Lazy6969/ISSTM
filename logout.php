<?php
session_start();

// Détruire toutes les variables de la session.
session_unset();

// Finalement, détruire la session.
session_destroy();

// Rediriger vers la page d'accueil après la déconnexion
header('Location: index.php');
exit;