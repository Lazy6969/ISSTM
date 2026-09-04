<?php
// Fonctions partagées par messagerie.php et les endpoints AJAX associés (messagerie_send.php,
// messagerie_poll.php, messagerie_media.php, messagerie_search.php).
// Principe : une seule conversation de groupe fixe entre tous les comptes marqués
// is_messagerie = 1 (Kakal, Scolarité, et l'administrateur Mirindra) — pas de système
// multi-conversations, donc pas de table "conversations" ; tout le monde voit tous les messages.

// Vérifie l'accès et retourne [utilisateur courant, liste des autres participants]. Redirige/arrête sinon.
function messagerie_require_access($mysqli, $isAjax = false) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
        if ($isAjax) { http_response_code(403); echo json_encode(['error' => 'unauthorized']); exit; }
        header('Location: index.php');
        exit;
    }
    $user_id = (int) $_SESSION['user_id'];
    $stmt = $mysqli->prepare("SELECT id, nom, email, avatar_path, is_messagerie, last_activity FROM utilisateurs WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $self = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$self || (int) $self['is_messagerie'] !== 1) {
        if ($isAjax) { http_response_code(403); echo json_encode(['error' => 'unauthorized']); exit; }
        header('Location: index.php');
        exit;
    }

    $others = $mysqli->query("SELECT id, nom, email, avatar_path, is_messagerie, last_activity FROM utilisateurs WHERE is_messagerie = 1 AND id != " . (int) $self['id'] . " ORDER BY nom ASC")->fetch_all(MYSQLI_ASSOC);

    // Marque l'utilisateur courant comme actif à chaque accès (page ou requête AJAX).
    $mysqli->query("UPDATE utilisateurs SET last_activity = NOW() WHERE id = " . (int) $self['id']);

    return [$self, $others];
}

// En ligne si une activité a été enregistrée il y a moins de 25 secondes (le sondage côté
// client interroge toutes les 8 secondes environ, une marge est laissée pour la latence réseau).
function messagerie_is_online($last_activity) {
    if (!$last_activity) return false;
    return (time() - strtotime($last_activity)) < 25;
}

function messagerie_format_size($bytes) {
    $bytes = (int) $bytes;
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' Go';
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' Mo';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' Ko';
    return $bytes . ' o';
}

function messagerie_classify_file($mime, $ext) {
    $ext = strtolower($ext);
    $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'avif', 'svg'];
    $video_exts = ['mp4', 'webm', 'mov', 'ogg', 'avi', 'mkv', 'm4v'];
    if (strpos((string) $mime, 'image/') === 0 || in_array($ext, $image_exts)) return 'image';
    if (strpos((string) $mime, 'video/') === 0 || in_array($ext, $video_exts)) return 'video';
    return 'file';
}

// Icône Font Awesome selon l'extension, pour les pièces jointes de type "fichier" générique.
function messagerie_file_icon($ext) {
    $ext = strtolower($ext);
    return match (true) {
        in_array($ext, ['pdf']) => 'fa-file-pdf',
        in_array($ext, ['doc', 'docx']) => 'fa-file-word',
        in_array($ext, ['xls', 'xlsx', 'csv']) => 'fa-file-excel',
        in_array($ext, ['ppt', 'pptx']) => 'fa-file-powerpoint',
        in_array($ext, ['zip', 'rar', '7z']) => 'fa-file-zipper',
        in_array($ext, ['mp3', 'wav', 'ogg', 'm4a']) => 'fa-file-audio',
        default => 'fa-file',
    };
}

// Transforme les URLs présentes dans un texte en liens cliquables (le texte est déjà échappé
// en amont via htmlspecialchars avant d'appeler cette fonction).
function messagerie_linkify($escapedText) {
    return preg_replace(
        '/(https?:\/\/[^\s<]+)/i',
        '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
        $escapedText
    );
}
