<?php
// On ne démarre la session que si elle n'est pas déjà active.
// Cela évite les notices d'erreur et centralise la gestion.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure le fichier contenant toutes les traductions
require_once 'translations.php';

// 1. Définir les langues disponibles
$available_langs = ['fr', 'en', 'mg'];

// 2. Définir la langue par défaut
$default_lang = 'fr';

// 3. Vérifier si l'utilisateur change de langue via l'URL (?lang=en)
if (isset($_GET['lang']) && in_array($_GET['lang'], $available_langs)) {
    $_SESSION['lang'] = $_GET['lang']; // Stocker la langue dans la session
}

// 4. Définir la langue actuelle (celle de la session ou celle par défaut)
$lang = $_SESSION['lang'] ?? $default_lang;

// 5. Créer la fonction de traduction `t()`
function t($key) {
    global $translations, $lang, $default_lang;
    // Retourne la traduction si elle existe, sinon le texte en français, ou la clé si rien n'est trouvé.
    return $translations[$key][$lang]
        ?? $translations[$key][$default_lang]
        ?? $key;
}

// 6. Construit le lien de changement de langue en conservant les autres paramètres de l'URL
// actuelle (slug, id, view...), pour éviter de perdre le contexte de la page (ex: filiere_detail.php?slug=...)
// et de retomber sur une page de repli sans rapport (voir header.php).
function lang_switch_url($target_lang) {
    $params = $_GET;
    $params['lang'] = $target_lang;
    return '?' . http_build_query($params);
}
?>