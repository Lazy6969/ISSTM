<?php
// Racine du site calculée depuis l'emplacement réel de ce fichier (et non depuis la page
// appelante), pour que les liens du header/footer partagés restent corrects même quand une
// page est incluse depuis un sous-dossier (ex : bibliotheque/).
if (!defined('SITE_URL')) {
    $doc_root = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\'));
    $site_dir = str_replace('\\', '/', __DIR__);
    define('SITE_URL', substr($site_dir, strlen($doc_root)));
}

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Utilisateur par défaut de XAMPP
define('DB_PASSWORD', '');     // Mot de passe par défaut de XAMPP (vide)
define('DB_NAME', 'isstm_db'); // Le nom de votre base de données

// Secret utilisé pour signer les liens de désabonnement de la newsletter (évite qu'un lien
// « Se désabonner » puisse être deviné/forgé pour un autre email que le sien).
define('NEWSLETTER_SECRET', 'isstm-newsletter-2026-secret-key');

/* Tentative de connexion à la base de données MySQL */
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Vérifier la connexion
if($mysqli === false){
    die("ERREUR : Impossible de se connecter. " . $mysqli->connect_error);
}

// S'assurer que la connexion utilise l'encodage UTF-8
$mysqli->set_charset("utf8mb4");

// Formatage compact des compteurs (vues, statistiques...) : 1500 -> "1.5K", 100000 -> "100K",
// 5000000 -> "5M", 1000000000 -> "1B". Sous 1000, le nombre est affiché tel quel.
if (!function_exists('format_compact_number')) {
    function format_compact_number($n) {
        $n = (float) $n;
        $neg = $n < 0;
        $n = abs($n);
        $units = [
            ['value' => 1_000_000_000, 'suffix' => 'B'],
            ['value' => 1_000_000, 'suffix' => 'M'],
            ['value' => 1_000, 'suffix' => 'K'],
        ];
        foreach ($units as $i => $u) {
            if ($n >= $u['value']) {
                $v = round($n / $u['value'], 1);
                // Si l'arrondi fait basculer dans le palier supérieur (ex : 999999 -> "1000K"), on remonte d'un cran.
                if ($v >= 1000 && $i > 0) {
                    $u = $units[$i - 1];
                    $v = round($n / $u['value'], 1);
                }
                $str = (fmod($v, 1) === 0.0) ? (string) (int) $v : rtrim(rtrim(number_format($v, 1), '0'), '.');
                return ($neg ? '-' : '') . $str . $u['suffix'];
            }
        }
        return ($neg ? '-' : '') . (string) (int) $n;
    }
}

// Traduction automatique du site : point de terminaison public (non officiel) de Google
// Traduction en premier choix (pas de clé API, pas de quota journalier observé, bien meilleure
// qualité constatée sur le malgache), avec repli sur l'API gratuite MyMemory (quota journalier
// limité) si Google est injoignable. Retourne '' si les deux échouent, pour permettre à
// l'appelant de retomber silencieusement sur le texte source plutôt que d'afficher une erreur.
if (!function_exists('google_translate_unofficial')) {
    function google_translate_unofficial($text, $target_lang, $source_lang = 'fr') {
        $url = 'https://translate.googleapis.com/translate_a/single?' . http_build_query([
            'client' => 'gtx',
            'sl' => $source_lang,
            'tl' => $target_lang,
            'dt' => 't',
            'q' => $text,
        ]);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        // Sous Apache multi-thread (Windows), curl utilise par défaut des alarmes signal pour
        // faire respecter CURLOPT_TIMEOUT pendant la résolution DNS — or les signaux ne
        // fonctionnent pas de façon fiable en contexte multi-thread, ce qui peut rendre le
        // timeout totalement inopérant et bloquer la requête indéfiniment. CURLOPT_NOSIGNAL
        // force un mode d'attente sans signal, où le timeout est bien respecté.
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($res === false || $http_code !== 200) return '';
        $data = json_decode($res, true);
        if (!is_array($data) || !isset($data[0]) || !is_array($data[0])) return '';
        $translated = '';
        foreach ($data[0] as $chunk) {
            $translated .= $chunk[0] ?? '';
        }
        return trim($translated);
    }
}

if (!function_exists('mymemory_translate_api')) {
    function mymemory_translate_api($text, $target_lang, $source_lang = 'fr') {
        $url = 'https://api.mymemory.translated.net/get?' . http_build_query([
            'q' => $text,
            'langpair' => $source_lang . '|' . $target_lang,
            'de' => 'mirindraramanana2@gmail.com',
        ]);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1); // voir commentaire dans google_translate_unofficial()
        $res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($res === false || $http_code !== 200) return '';
        $data = json_decode($res, true);
        $translated = $data['responseData']['translatedText'] ?? '';
        if ($translated === '' || stripos($translated, 'MYMEMORY WARNING') !== false || stripos($translated, 'QUOTA') !== false) {
            return '';
        }
        return $translated;
    }
}

// Contenu de contact/pied de page modifiable depuis l'administration (admin_contenu.php,
// onglet "Contact"), avec repli sur la traduction statique si la clé n'existe pas encore en
// base. Définie ici (plutôt que dans footer.php) pour être disponible aussi dans les sections
// qui affichent ces informations AVANT l'inclusion du footer (ex : section Contact d'index.php).
if (!function_exists('dc_footer')) {
    function dc_footer($content_key) {
        global $lang;
        static $cache = null;
        // Mise en cache mémoire pour toute la durée de la requête : une seule connexion/requête
        // (toutes les clés d'un coup) au lieu d'une connexion dédiée à chaque appel de dc_footer().
        if ($cache === null) {
            $cache = [];
            try {
                // Connexion dédiée : certaines pages (ex : index.php) ferment déjà $mysqli
                // avant d'afficher ces informations (section Contact, avant le footer),
                // donc on ne peut pas compter sur $mysqli étant encore valide ici.
                $db = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
                $result = $db->query("SELECT content_key, content_value_fr, content_value_en, content_value_mg FROM site_content");
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $cache[$row['content_key']] = $row;
                    }
                    $result->free();
                }
                $db->close();
            } catch (\Throwable $e) {
                // Repli silencieux sur la traduction statique en cas de souci de connexion.
            }
        }
        if (isset($cache[$content_key]) && !empty($cache[$content_key]['content_value_' . $lang])) {
            return htmlspecialchars($cache[$content_key]['content_value_' . $lang]);
        }
        return t($content_key);
    }
}

// Formate une date (YYYY-MM-DD) en toutes lettres dans la langue courante (ex : "9 octobre 2026"),
// utilisé pour les dates administrables (ex : date limite de dépôt des dossiers d'inscription)
// dont seule la valeur brute est stockée en base, le libellé traduit restant dans translations.php.
if (!function_exists('format_date_localized')) {
    function format_date_localized($date_str, $lang = 'fr') {
        $date_str = trim((string) $date_str);
        if ($date_str === '') return '';
        $ts = strtotime($date_str);
        if ($ts === false) return $date_str;
        $months = [
            'fr' => ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
            'en' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            'mg' => ['Janoary', 'Febroary', 'Martsa', 'Aprily', 'Mey', 'Jona', 'Jolay', 'Aogositra', 'Septambra', 'Oktobra', 'Novambra', 'Desambra'],
        ];
        $m = $months[$lang] ?? $months['fr'];
        return date('j', $ts) . ' ' . $m[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
    }
}

if (!function_exists('mymemory_translate')) {
    function mymemory_translate($text, $target_lang, $source_lang = 'fr') {
        $text = trim((string) $text);
        if ($text === '') return '';
        $translated = google_translate_unofficial($text, $target_lang, $source_lang);
        if ($translated !== '') return $translated;
        return mymemory_translate_api($text, $target_lang, $source_lang);
    }
}
?>