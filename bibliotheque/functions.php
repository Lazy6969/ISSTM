<?php
// Accès admin de la bibliothèque : soit le compte autonome de la bibliothèque
// (session admin_id / admin_username / admin_role, posée par login_handler.php à la racine,
// point de connexion unique du site), soit l'admin général de l'ISSTM déjà connecté via le
// site principal (session user_logged_in / user_role).
// Ce pont permet à l'admin ISSTM d'accéder à toute la bibliothèque sans double connexion.
function isAdminLogged(): bool
{
    if (isset($_SESSION['admin_id'])) {
        return true;
    }
    return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && ($_SESSION['user_role'] ?? '') === 'admin';
}

// Nom à afficher pour l'admin actuellement connecté, quelle que soit la voie
// (compte bibliothèque autonome ou admin ISSTM bridgé).
function nomAdminConnecte(): string
{
    if (isset($_SESSION['admin_username'])) {
        return $_SESSION['admin_username'];
    }
    if (isset($_SESSION['user_nom'])) {
        return $_SESSION['user_nom'];
    }
    return '';
}

function requireAdmin(): void
{
    if (!isAdminLogged()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

// Tout compte connecté (via login.php à la racine), quel que soit son rôle : utilisateur
// ISSTM classique, admin ISSTM bridgé, ou compte autonome de la bibliothèque. Condition
// requise pour consulter en ligne et télécharger les canevas/mémoires. isAdminLogged() est
// inclus pour que les admins (qui gèrent la bibliothèque) ne soient jamais bloqués sur ses
// propres documents.
function estUtilisateurConnecte(): bool
{
    return (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) || isAdminLogged();
}

function e(string $texte): string
{
    return htmlspecialchars($texte, ENT_QUOTES, 'UTF-8');
}

// Icône Font Awesome selon le type de fichier (remplace les émojis, cf. convention
// du reste du site ISSTM : icônes Font Awesome plutôt qu'émojis dans les titres/listes).
function iconeFichier(string $type): string
{
    return match ($type) {
        'word'  => 'fa-file-word',
        'pdf'   => 'fa-file-pdf',
        'pptx'  => 'fa-file-powerpoint',
        default => 'fa-file',
    };
}

// --- Extraction du texte des fichiers, pour permettre la recherche dans leur contenu ---
// (voir recherche.php : le texte extrait est mis en cache dans la colonne contenu_texte de
// chaque table, pour ne jamais avoir à ré-extraire à chaque recherche).

// Chemin absolu vers pdftotext.exe (fourni avec Git for Windows, déjà présent sur ce poste).
// Si l'outil n'est pas trouvé (autre poste, autre installation), l'extraction PDF est
// simplement ignorée : la recherche retombe alors sur les seules métadonnées, sans erreur.
function bib_pdftotext_path(): ?string
{
    static $checked = null;
    if ($checked !== null) return $checked ?: null;
    $candidates = [
        'C:\\Program Files\\Git\\mingw64\\bin\\pdftotext.exe',
        'C:\\Program Files (x86)\\Git\\mingw64\\bin\\pdftotext.exe',
    ];
    foreach ($candidates as $c) {
        if (file_exists($c)) { $checked = $c; return $c; }
    }
    $checked = '';
    return null;
}

function bib_extract_pdf_text(string $cheminAbsolu): string
{
    $bin = bib_pdftotext_path();
    if ($bin === null || !file_exists($cheminAbsolu)) return '';
    $cmd = escapeshellarg($bin) . ' -q ' . escapeshellarg($cheminAbsolu) . ' -';
    $texte = @shell_exec($cmd);
    return is_string($texte) ? trim($texte) : '';
}

function bib_extract_docx_text(string $cheminAbsolu): string
{
    if (!file_exists($cheminAbsolu)) return '';
    $zip = new ZipArchive();
    if ($zip->open($cheminAbsolu) !== true) return '';
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    if ($xml === false) return '';
    $xml = str_replace(['</w:p>', '</w:tr>'], "\n", $xml);
    $texte = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
    return trim(preg_replace('/[ \t]+/', ' ', $texte));
}

// Extrait le texte selon l'extension du fichier d'origine. Retourne '' pour les formats non
// pris en charge (ex : .doc binaire, .ppt/.pptx) plutôt que d'échouer : ces documents restent
// simplement cherchables par leurs seules métadonnées (titre, auteur...).
function bib_extract_text(string $cheminAbsolu, string $extension): string
{
    $extension = strtolower($extension);
    if ($extension === 'pdf') return bib_extract_pdf_text($cheminAbsolu);
    if ($extension === 'docx') return bib_extract_docx_text($cheminAbsolu);
    return '';
}
