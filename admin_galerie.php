<?php
include_once 'language.php';
require_once 'db_connect.php';

$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';
if (!$is_admin) {
    header('Location: index.php');
    exit;
}

$upload_dir = 'uploads/';
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
$flash = null;

function gal_unlink_if_upload($path) {
    if ($path && file_exists($path) && strpos($path, 'uploads/') === 0) {
        unlink($path);
    }
}

function gal_video_info($url) {
    $url = trim((string) $url);
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/shorts/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
        return ['embed' => 'https://www.youtube.com/embed/' . $m[1], 'thumb' => 'https://img.youtube.com/vi/' . $m[1] . '/hqdefault.jpg'];
    }
    if (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
        return ['embed' => 'https://player.vimeo.com/video/' . $m[1], 'thumb' => ''];
    }
    return ['embed' => $url, 'thumb' => ''];
}

// --- Traitement des actions POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Créer / mettre à jour un album ---
    if (isset($_POST['save_album'])) {
        $album_id   = isset($_POST['album_id']) && ctype_digit($_POST['album_id']) ? (int) $_POST['album_id'] : 0;
        $title_fr   = trim($_POST['title_fr'] ?? '');
        $title_en   = trim($_POST['title_en'] ?? '');
        $title_mg   = trim($_POST['title_mg'] ?? '');
        $desc_fr    = trim($_POST['description_fr'] ?? '');
        $desc_en    = trim($_POST['description_en'] ?? '');
        $desc_mg    = trim($_POST['description_mg'] ?? '');
        $category_id = ctype_digit($_POST['category_id'] ?? '') ? (int) $_POST['category_id'] : null;
        $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
        $location   = trim($_POST['location'] ?? '');
        $author     = trim($_POST['author'] ?? '');
        $status     = ($_POST['status'] ?? 'brouillon') === 'publie' ? 'publie' : 'brouillon';

        $cover_path = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0 && in_array($_FILES['cover_image']['type'], $allowed_types)) {
            $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $new_name = 'album_cover_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_dir . $new_name)) {
                $cover_path = $upload_dir . $new_name;
            }
        }

        if ($title_fr === '') {
            $flash = ['type' => 'error', 'msg' => "Le titre (FR) de l'album est obligatoire."];
        } elseif ($album_id > 0) {
            // Mise à jour
            if ($cover_path) {
                $old = $mysqli->query("SELECT cover_image FROM gallery_albums WHERE id = $album_id")->fetch_assoc();
                if ($old) gal_unlink_if_upload($old['cover_image']);
                $stmt = $mysqli->prepare("UPDATE gallery_albums SET title_fr=?, title_en=?, title_mg=?, description_fr=?, description_en=?, description_mg=?, category_id=?, event_date=?, location=?, author=?, status=?, cover_image=?, published_at = IF(? = 'publie' AND published_at IS NULL, NOW(), published_at) WHERE id=?");
                $stmt->bind_param("ssssssissssssi", $title_fr, $title_en, $title_mg, $desc_fr, $desc_en, $desc_mg, $category_id, $event_date, $location, $author, $status, $cover_path, $status, $album_id);
            } else {
                $stmt = $mysqli->prepare("UPDATE gallery_albums SET title_fr=?, title_en=?, title_mg=?, description_fr=?, description_en=?, description_mg=?, category_id=?, event_date=?, location=?, author=?, status=?, published_at = IF(? = 'publie' AND published_at IS NULL, NOW(), published_at) WHERE id=?");
                $stmt->bind_param("ssssssisssssi", $title_fr, $title_en, $title_mg, $desc_fr, $desc_en, $desc_mg, $category_id, $event_date, $location, $author, $status, $status, $album_id);
            }
            $stmt->execute();
            $stmt->close();
            $flash = ['type' => 'success', 'msg' => 'Album mis à jour avec succès.'];
        } else {
            // Création
            $stmt = $mysqli->prepare("INSERT INTO gallery_albums (title_fr, title_en, title_mg, description_fr, description_en, description_mg, category_id, event_date, location, author, status, cover_image, published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?, IF(?='publie', NOW(), NULL))");
            $stmt->bind_param("ssssssissssss", $title_fr, $title_en, $title_mg, $desc_fr, $desc_en, $desc_mg, $category_id, $event_date, $location, $author, $status, $cover_path, $status);
            $stmt->execute();
            $album_id = $mysqli->insert_id;
            $stmt->close();
            $flash = ['type' => 'success', 'msg' => 'Album créé avec succès. Vous pouvez maintenant y ajouter des photos.'];
        }
        header("Location: admin_galerie.php?view=edit&id=$album_id&flash=" . urlencode($flash['type'] . '|' . $flash['msg']));
        exit;
    }

    // --- Supprimer un album ---
    if (isset($_POST['delete_album_id'])) {
        $album_id = (int) $_POST['delete_album_id'];
        $old = $mysqli->query("SELECT cover_image FROM gallery_albums WHERE id = $album_id")->fetch_assoc();
        if ($old) gal_unlink_if_upload($old['cover_image']);
        $photos = $mysqli->query("SELECT image_path FROM gallery_photos WHERE album_id = $album_id");
        while ($p = $photos->fetch_assoc()) { gal_unlink_if_upload($p['image_path']); }
        $stmt = $mysqli->prepare("DELETE FROM gallery_albums WHERE id = ?");
        $stmt->bind_param("i", $album_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_galerie.php?flash=" . urlencode('success|Album supprimé.'));
        exit;
    }

    // --- Publier / dépublier un album ---
    if (isset($_POST['toggle_status_id'])) {
        $album_id = (int) $_POST['toggle_status_id'];
        $mysqli->query("UPDATE gallery_albums SET status = IF(status='publie','brouillon','publie'), published_at = IF(status='brouillon' AND published_at IS NULL, NOW(), published_at) WHERE id = $album_id");
        $redirect = isset($_POST['redirect_edit']) ? "admin_galerie.php?view=edit&id=$album_id" : "admin_galerie.php";
        header("Location: $redirect");
        exit;
    }

    // --- Ajouter des photos et vidéos (upload multiple, sans limite de nombre) ---
    if (isset($_POST['upload_photos']) && isset($_FILES['new_photos'])) {
        $album_id = (int) $_POST['album_id'];
        $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $video_exts = ['mp4', 'webm', 'mov', 'ogg', 'avi', 'mkv', 'm4v'];
        $maxOrderRow = $mysqli->query("SELECT COALESCE(MAX(display_order),0) AS m FROM gallery_photos WHERE album_id = $album_id")->fetch_assoc();
        $order = (int) $maxOrderRow['m'];
        $stmt = $mysqli->prepare("INSERT INTO gallery_photos (album_id, image_path, media_type, video_url, title, display_order, alt_text) VALUES (?,?,?,?,?,?,?)");
        $count_photos = 0;
        $count_videos = 0;
        foreach ($_FILES['new_photos']['name'] as $key => $name) {
            if ($_FILES['new_photos']['error'][$key] != 0) continue;
            $fext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $is_image = in_array($fext, $image_exts);
            $is_video_file = in_array($fext, $video_exts);
            if (!$is_image && !$is_video_file) continue;
            $title = pathinfo($name, PATHINFO_FILENAME);

            if ($is_image) {
                $new_name = 'photo_' . uniqid() . '_' . $key . '.' . $fext;
                if (move_uploaded_file($_FILES['new_photos']['tmp_name'][$key], $upload_dir . $new_name)) {
                    $order++;
                    $path = $upload_dir . $new_name;
                    $media_type = 'photo';
                    $video_url = '';
                    $stmt->bind_param("issssis", $album_id, $path, $media_type, $video_url, $title, $order, $title);
                    $stmt->execute();
                    $count_photos++;
                }
            } else {
                $new_name = 'video_' . uniqid() . '_' . $key . '.' . $fext;
                if (move_uploaded_file($_FILES['new_photos']['tmp_name'][$key], $upload_dir . $new_name)) {
                    $order++;
                    $video_path = $upload_dir . $new_name;
                    $empty_image_path = '';
                    $media_type = 'video';
                    $stmt->bind_param("issssis", $album_id, $empty_image_path, $media_type, $video_path, $title, $order, $title);
                    $stmt->execute();
                    $count_videos++;
                }
            }
        }
        $stmt->close();
        header("Location: admin_galerie.php?view=edit&id=$album_id&flash=" . urlencode('success|' . $count_photos . ' photo(s) et ' . $count_videos . ' vidéo(s) ajoutées.'));
        exit;
    }

    // --- Définir une photo existante de l'album comme image de couverture ---
    if (isset($_POST['set_cover_photo_id'])) {
        $photo_id = (int) $_POST['set_cover_photo_id'];
        $album_id = (int) $_POST['album_id'];
        $photo = $mysqli->query("SELECT image_path, media_type FROM gallery_photos WHERE id = $photo_id AND album_id = $album_id")->fetch_assoc();
        if ($photo && ($photo['media_type'] ?? 'photo') === 'photo' && $photo['image_path'] && file_exists($photo['image_path'])) {
            $ext = pathinfo($photo['image_path'], PATHINFO_EXTENSION);
            $new_cover_path = $upload_dir . 'album_cover_' . uniqid() . '.' . $ext;
            if (copy($photo['image_path'], $new_cover_path)) {
                $old = $mysqli->query("SELECT cover_image FROM gallery_albums WHERE id = $album_id")->fetch_assoc();
                if ($old) gal_unlink_if_upload($old['cover_image']);
                $stmt = $mysqli->prepare("UPDATE gallery_albums SET cover_image = ? WHERE id = ?");
                $stmt->bind_param("si", $new_cover_path, $album_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        header("Location: admin_galerie.php?view=edit&id=$album_id&flash=" . urlencode('success|Couverture mise à jour.'));
        exit;
    }

    // --- Ajouter une vidéo (URL YouTube / Vimeo / fichier direct) ---
    if (isset($_POST['add_video'])) {
        $album_id = (int) $_POST['album_id'];
        $video_url = trim($_POST['video_url'] ?? '');
        $video_title = trim($_POST['video_title'] ?? '');
        if ($video_url === '') {
            header("Location: admin_galerie.php?view=edit&id=$album_id&flash=" . urlencode('error|Veuillez indiquer une URL de vidéo.'));
            exit;
        }
        $info = gal_video_info($video_url);
        $maxOrderRow = $mysqli->query("SELECT COALESCE(MAX(display_order),0) AS m FROM gallery_photos WHERE album_id = $album_id")->fetch_assoc();
        $order = (int) $maxOrderRow['m'] + 1;
        $thumb = $info['thumb'];
        $stmt = $mysqli->prepare("INSERT INTO gallery_photos (album_id, image_path, media_type, video_url, title, display_order) VALUES (?, ?, 'video', ?, ?, ?)");
        $stmt->bind_param("isssi", $album_id, $thumb, $video_url, $video_title, $order);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_galerie.php?view=edit&id=$album_id&flash=" . urlencode('success|Vidéo ajoutée.'));
        exit;
    }

    // --- Importer une archive ZIP (photos + vidéos en masse) ---
    if (isset($_POST['import_zip']) && isset($_FILES['zip_file'])) {
        $album_id = (int) $_POST['album_id'];
        $zip_file = $_FILES['zip_file'];
        $zip_ext = strtolower(pathinfo($zip_file['name'], PATHINFO_EXTENSION));

        if ($zip_file['error'] != 0 || $zip_file['name'] === '' || $zip_ext !== 'zip') {
            header("Location: admin_galerie.php?view=edit&id=$album_id&flash=" . urlencode('error|Veuillez sélectionner un fichier ZIP valide.'));
            exit;
        }
        if (!class_exists('ZipArchive')) {
            header("Location: admin_galerie.php?view=edit&id=$album_id&flash=" . urlencode("error|L'extension ZIP n'est pas disponible sur le serveur."));
            exit;
        }

        $tmp_zip = $upload_dir . 'zip_' . uniqid() . '.zip';
        if (!move_uploaded_file($zip_file['tmp_name'], $tmp_zip)) {
            header("Location: admin_galerie.php?view=edit&id=$album_id&flash=" . urlencode("error|Impossible de traiter le fichier ZIP."));
            exit;
        }

        $zip = new ZipArchive();
        if ($zip->open($tmp_zip) !== true) {
            unlink($tmp_zip);
            header("Location: admin_galerie.php?view=edit&id=$album_id&flash=" . urlencode("error|Le fichier ZIP est invalide ou corrompu."));
            exit;
        }

        $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $video_exts = ['mp4', 'webm', 'mov', 'ogg'];
        $max_zip_files = 300;

        $maxOrderRow = $mysqli->query("SELECT COALESCE(MAX(display_order),0) AS m FROM gallery_photos WHERE album_id = $album_id")->fetch_assoc();
        $order = (int) $maxOrderRow['m'];
        $stmt = $mysqli->prepare("INSERT INTO gallery_photos (album_id, image_path, media_type, video_url, title, display_order, alt_text) VALUES (?,?,?,?,?,?,?)");

        $count_photos = 0;
        $count_videos = 0;
        $count_skipped = 0;
        $entries = min($zip->numFiles, $max_zip_files);

        for ($i = 0; $i < $entries; $i++) {
            $stat = $zip->statIndex($i);
            $name = $stat['name'];
            if ($name === '' || substr($name, -1) === '/') continue;
            $basename = basename($name);
            if ($basename === '' || $basename[0] === '.' || strpos($name, '__MACOSX/') === 0) continue;

            $fext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
            $is_image = in_array($fext, $image_exts);
            $is_video_file = in_array($fext, $video_exts);
            if (!$is_image && !$is_video_file) { $count_skipped++; continue; }

            $data = $zip->getFromIndex($i);
            if ($data === false) { $count_skipped++; continue; }

            $title = pathinfo($basename, PATHINFO_FILENAME);

            if ($is_image) {
                $new_name = 'photo_' . uniqid() . '_' . $i . '.' . $fext;
                if (file_put_contents($upload_dir . $new_name, $data) === false) { $count_skipped++; continue; }
                $order++;
                $path = $upload_dir . $new_name;
                $media_type = 'photo';
                $video_url = '';
                $stmt->bind_param("issssis", $album_id, $path, $media_type, $video_url, $title, $order, $title);
                $stmt->execute();
                $count_photos++;
            } else {
                $new_name = 'video_' . uniqid() . '_' . $i . '.' . $fext;
                if (file_put_contents($upload_dir . $new_name, $data) === false) { $count_skipped++; continue; }
                $order++;
                $video_path = $upload_dir . $new_name;
                $empty_image_path = '';
                $media_type = 'video';
                $stmt->bind_param("issssis", $album_id, $empty_image_path, $media_type, $video_path, $title, $order, $title);
                $stmt->execute();
                $count_videos++;
            }
        }
        $stmt->close();
        $zip->close();
        unlink($tmp_zip);

        $msg = "Import terminé : $count_photos photo(s) et $count_videos vidéo(s) importées";
        if ($count_skipped > 0) $msg .= ", $count_skipped fichier(s) ignoré(s)";
        $msg .= '.';
        header("Location: admin_galerie.php?view=edit&id=$album_id&flash=" . urlencode('success|' . $msg));
        exit;
    }

    // --- Importer un dossier entier (photos + vidéos, sans limite de taille) ---
    if (isset($_POST['import_folder']) && isset($_FILES['folder_files'])) {
        $album_id = (int) $_POST['album_id'];
        $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $video_exts = ['mp4', 'webm', 'mov', 'ogg', 'avi', 'mkv', 'm4v'];

        $maxOrderRow = $mysqli->query("SELECT COALESCE(MAX(display_order),0) AS m FROM gallery_photos WHERE album_id = $album_id")->fetch_assoc();
        $order = (int) $maxOrderRow['m'];
        $stmt = $mysqli->prepare("INSERT INTO gallery_photos (album_id, image_path, media_type, video_url, title, display_order, alt_text) VALUES (?,?,?,?,?,?,?)");

        $count_photos = 0;
        $count_videos = 0;
        $count_skipped = 0;

        foreach ($_FILES['folder_files']['name'] as $key => $name) {
            if ($_FILES['folder_files']['error'][$key] != 0) { $count_skipped++; continue; }
            $basename = basename($name);
            if ($basename === '' || $basename[0] === '.') { $count_skipped++; continue; }

            $fext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
            $is_image = in_array($fext, $image_exts);
            $is_video_file = in_array($fext, $video_exts);
            if (!$is_image && !$is_video_file) { $count_skipped++; continue; }

            $title = pathinfo($basename, PATHINFO_FILENAME);

            if ($is_image) {
                $new_name = 'photo_' . uniqid() . '_' . $key . '.' . $fext;
                if (!move_uploaded_file($_FILES['folder_files']['tmp_name'][$key], $upload_dir . $new_name)) { $count_skipped++; continue; }
                $order++;
                $path = $upload_dir . $new_name;
                $media_type = 'photo';
                $video_url = '';
                $stmt->bind_param("issssis", $album_id, $path, $media_type, $video_url, $title, $order, $title);
                $stmt->execute();
                $count_photos++;
            } else {
                $new_name = 'video_' . uniqid() . '_' . $key . '.' . $fext;
                if (!move_uploaded_file($_FILES['folder_files']['tmp_name'][$key], $upload_dir . $new_name)) { $count_skipped++; continue; }
                $order++;
                $video_path = $upload_dir . $new_name;
                $empty_image_path = '';
                $media_type = 'video';
                $stmt->bind_param("issssis", $album_id, $empty_image_path, $media_type, $video_path, $title, $order, $title);
                $stmt->execute();
                $count_videos++;
            }
        }
        $stmt->close();

        $msg = "Import terminé : $count_photos photo(s) et $count_videos vidéo(s) importées";
        if ($count_skipped > 0) $msg .= ", $count_skipped fichier(s) ignoré(s)";
        $msg .= '.';
        header("Location: admin_galerie.php?view=edit&id=$album_id&flash=" . urlencode('success|' . $msg));
        exit;
    }

    // --- Supprimer une photo ---
    if (isset($_POST['delete_photo_id'])) {
        $photo_id = (int) $_POST['delete_photo_id'];
        $album_id = (int) $_POST['album_id'];
        $old = $mysqli->query("SELECT image_path, video_url FROM gallery_photos WHERE id = $photo_id")->fetch_assoc();
        if ($old) { gal_unlink_if_upload($old['image_path']); gal_unlink_if_upload($old['video_url']); }
        $stmt = $mysqli->prepare("DELETE FROM gallery_photos WHERE id = ?");
        $stmt->bind_param("i", $photo_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_galerie.php?view=edit&id=$album_id&flash=" . urlencode('success|Photo supprimée.'));
        exit;
    }

    // --- Réordonner une photo ---
    if (isset($_POST['reorder_photo_id'])) {
        $photo_id = (int) $_POST['reorder_photo_id'];
        $album_id = (int) $_POST['album_id'];
        $direction = $_POST['direction'] === 'up' ? 'up' : 'down';
        $current = $mysqli->query("SELECT id, display_order FROM gallery_photos WHERE id = $photo_id")->fetch_assoc();
        if ($current) {
            if ($direction === 'up') {
                $neighbor = $mysqli->query("SELECT id, display_order FROM gallery_photos WHERE album_id = $album_id AND display_order < {$current['display_order']} ORDER BY display_order DESC LIMIT 1")->fetch_assoc();
            } else {
                $neighbor = $mysqli->query("SELECT id, display_order FROM gallery_photos WHERE album_id = $album_id AND display_order > {$current['display_order']} ORDER BY display_order ASC LIMIT 1")->fetch_assoc();
            }
            if ($neighbor) {
                $stmt = $mysqli->prepare("UPDATE gallery_photos SET display_order = ? WHERE id = ?");
                $stmt->bind_param("ii", $neighbor['display_order'], $current['id']);
                $stmt->execute();
                $stmt->bind_param("ii", $current['display_order'], $neighbor['id']);
                $stmt->execute();
                $stmt->close();
            }
        }
        header("Location: admin_galerie.php?view=edit&id=$album_id&flash=" . urlencode('success|Ordre mis à jour.'));
        exit;
    }

    // --- Mettre à jour les métadonnées d'une photo ---
    if (isset($_POST['update_photo_id'])) {
        $photo_id = (int) $_POST['update_photo_id'];
        $album_id = (int) $_POST['album_id'];
        $title = trim($_POST['photo_title'] ?? '');
        $description = trim($_POST['photo_description'] ?? '');
        $taken_at = !empty($_POST['photo_taken_at']) ? $_POST['photo_taken_at'] : null;
        $location = trim($_POST['photo_location'] ?? '');
        $photographer = trim($_POST['photo_photographer'] ?? '');
        $tags = trim($_POST['photo_tags'] ?? '');
        $alt_text = trim($_POST['photo_alt_text'] ?? '');
        $stmt = $mysqli->prepare("UPDATE gallery_photos SET title=?, description=?, taken_at=?, location=?, photographer=?, tags=?, alt_text=? WHERE id=?");
        $stmt->bind_param("sssssssi", $title, $description, $taken_at, $location, $photographer, $tags, $alt_text, $photo_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_galerie.php?view=edit&id=$album_id&flash=" . urlencode('success|Photo mise à jour.') . "#photo-$photo_id");
        exit;
    }
}

// --- Message flash (après redirection) ---
if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

// --- Catégories (pour les selects) ---
$all_categories = $mysqli->query("SELECT * FROM gallery_categories ORDER BY display_order ASC")->fetch_all(MYSQLI_ASSOC);

$view = $_GET['view'] ?? 'list';

$page_title = t('admin_galerie_breadcrumb');

include 'header.php';
?>

<a href="administrateur.php" class="btn-outline btn-back-sticky"><i class="fas fa-arrow-left"></i> <?php echo t('admin_retour_tableau_bord'); ?></a>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="administrateur.php"><?php echo t('administration'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('admin_galerie_breadcrumb'); ?></span>
        </nav>
        <h1><?php echo t('admin_galerie_breadcrumb'); ?></h1>
        <p><?php echo t('admin_galerie_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <?php if ($flash): ?>
            <div class="admin-flash admin-flash-<?php echo htmlspecialchars($flash['type']); ?>">
                <i class="fas <?php echo $flash['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'; ?>"></i>
                <?php echo htmlspecialchars($flash['msg']); ?>
            </div>
        <?php endif; ?>

        <?php if ($view === 'edit'):
            $album_id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;
            $album = null;
            if ($album_id > 0) {
                $stmt = $mysqli->prepare("SELECT * FROM gallery_albums WHERE id = ?");
                $stmt->bind_param("i", $album_id);
                $stmt->execute();
                $album = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }
            $photos = [];
            if ($album_id > 0) {
                $stmt = $mysqli->prepare("SELECT * FROM gallery_photos WHERE album_id = ? ORDER BY display_order ASC, id ASC");
                $stmt->bind_param("i", $album_id);
                $stmt->execute();
                $photos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
        ?>
            <a href="admin_galerie.php" class="admin-galerie-back"><i class="fas fa-chevron-left"></i> <?php echo t('admin_galerie_retour_liste'); ?></a>
            <h2 class="admin-section-title"><?php echo $album ? t('admin_galerie_modifier_album') : t('admin_galerie_nouvel_album'); ?></h2>

            <form action="admin_galerie.php" method="POST" enctype="multipart/form-data" class="admin-form admin-album-form">
                <?php if ($album): ?><input type="hidden" name="album_id" value="<?php echo (int) $album['id']; ?>"><?php endif; ?>

                <div class="album-form-grid">
                    <div class="album-form-main">
                        <div class="form-group">
                            <label><?php echo t('admin_galerie_titre_album_label'); ?></label>
                            <div class="translatable-field-group" data-group-id="album-title">
                                <div class="lang-tabs">
                                    <button type="button" class="lang-tab is-active" data-lang="fr">🇫🇷 FR</button>
                                    <button type="button" class="lang-tab" data-lang="en">🇬🇧 EN</button>
                                    <button type="button" class="lang-tab" data-lang="mg">🇲🇬 MG</button>
                                    <button type="button" class="btn-translate-all" title="Traduire automatiquement le FR vers l'EN et le MG"><i class="fas fa-language"></i> Traduire</button>
                                </div>
                                <div class="lang-panel is-active" data-lang="fr"><input type="text" name="title_fr" value="<?php echo htmlspecialchars($album['title_fr'] ?? ''); ?>" data-lang-input="fr" placeholder="<?php echo t('admin_galerie_titre_fr_placeholder'); ?>" required></div>
                                <div class="lang-panel" data-lang="en"><input type="text" name="title_en" value="<?php echo htmlspecialchars($album['title_en'] ?? ''); ?>" data-lang-input="en" placeholder="<?php echo t('admin_galerie_titre_en_placeholder'); ?>"></div>
                                <div class="lang-panel" data-lang="mg"><input type="text" name="title_mg" value="<?php echo htmlspecialchars($album['title_mg'] ?? ''); ?>" data-lang-input="mg" placeholder="<?php echo t('admin_galerie_titre_mg_placeholder'); ?>"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_description_label'); ?></label>
                            <div class="translatable-field-group" data-group-id="album-desc">
                                <div class="lang-tabs">
                                    <button type="button" class="lang-tab is-active" data-lang="fr">🇫🇷 FR</button>
                                    <button type="button" class="lang-tab" data-lang="en">🇬🇧 EN</button>
                                    <button type="button" class="lang-tab" data-lang="mg">🇲🇬 MG</button>
                                    <button type="button" class="btn-translate-all" title="Traduire automatiquement le FR vers l'EN et le MG"><i class="fas fa-language"></i> Traduire</button>
                                </div>
                                <div class="lang-panel is-active" data-lang="fr"><textarea name="description_fr" rows="4" data-lang-input="fr" placeholder="<?php echo t('admin_galerie_description_fr_placeholder'); ?>"><?php echo htmlspecialchars($album['description_fr'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="en"><textarea name="description_en" rows="4" data-lang-input="en" placeholder="<?php echo t('admin_galerie_description_en_placeholder'); ?>"><?php echo htmlspecialchars($album['description_en'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="mg"><textarea name="description_mg" rows="4" data-lang-input="mg" placeholder="<?php echo t('admin_galerie_description_mg_placeholder'); ?>"><?php echo htmlspecialchars($album['description_mg'] ?? ''); ?></textarea></div>
                            </div>
                        </div>

                        <div class="album-form-row">
                            <div class="form-group">
                                <label><?php echo t('admin_categorie_label'); ?></label>
                                <select name="category_id">
                                    <option value=""><?php echo t('admin_aucune_option'); ?></option>
                                    <?php foreach ($all_categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($album['category_id']) && (int) $album['category_id'] === (int) $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name_fr']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><?php echo t('admin_statut_label'); ?></label>
                                <select name="status">
                                    <option value="brouillon" <?php echo (($album['status'] ?? 'brouillon') === 'brouillon') ? 'selected' : ''; ?>><?php echo t('admin_statut_brouillon'); ?></option>
                                    <option value="publie" <?php echo (($album['status'] ?? '') === 'publie') ? 'selected' : ''; ?>><?php echo t('admin_statut_publie'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="album-form-row">
                            <div class="form-group">
                                <label><?php echo t('admin_galerie_date_evenement'); ?></label>
                                <input type="date" name="event_date" value="<?php echo htmlspecialchars($album['event_date'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label><?php echo t('admin_galerie_lieu'); ?></label>
                                <input type="text" name="location" value="<?php echo htmlspecialchars($album['location'] ?? ''); ?>" placeholder="<?php echo t('admin_galerie_lieu_placeholder'); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_galerie_auteur_photographe'); ?></label>
                            <input type="text" name="author" value="<?php echo htmlspecialchars($album['author'] ?? ''); ?>" placeholder="<?php echo t('admin_galerie_auteur_placeholder'); ?>">
                        </div>
                    </div>

                    <div class="album-form-side">
                        <div class="form-group upload-zone-group">
                            <label><?php echo t('admin_galerie_image_couverture'); ?></label>
                            <?php if (!empty($album['cover_image'])): ?>
                                <img src="<?php echo htmlspecialchars($album['cover_image']); ?>" class="album-cover-preview" alt="Couverture actuelle">
                            <?php endif; ?>
                            <label class="upload-zone">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <span class="upload-zone-text"><?php echo t('admin_choisir_image_deposer'); ?></span>
                                <span class="upload-zone-filename"></span>
                                <input type="file" name="cover_image" accept="image/jpeg, image/png, image/jpg, image/webp" class="upload-zone-input">
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" name="save_album" class="btn-submit"><i class="fas fa-save"></i> <?php echo $album ? t('admin_enregistrer_modifications') : t('admin_galerie_creer_album'); ?></button>
            </form>

            <?php if ($album): ?>
                <div class="admin-photos-section">
                    <h2 class="admin-section-title"><i class="fas fa-images"></i> <?php echo t('admin_galerie_photos_album'); ?> (<?php echo count($photos); ?>)</h2>

                    <form action="admin_galerie.php" method="POST" enctype="multipart/form-data" class="admin-upload-photos-form">
                        <input type="hidden" name="album_id" value="<?php echo (int) $album['id']; ?>">
                        <label class="upload-zone upload-zone-multi" id="multi-upload-zone">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <span class="upload-zone-text"><?php echo t('admin_galerie_multi_upload_text'); ?></span>
                            <span class="upload-zone-filename" id="multi-upload-filenames"></span>
                            <input type="file" name="new_photos[]" accept="image/jpeg, image/png, image/jpg, image/webp, video/mp4, video/webm, video/quicktime, video/ogg" class="upload-zone-input" id="multi-upload-input" multiple>
                        </label>
                        <p class="admin-field-hint"><?php echo t('admin_galerie_multi_upload_hint'); ?></p>
                        <button type="submit" name="upload_photos" class="btn-add-item"><i class="fas fa-upload"></i> <?php echo t('admin_galerie_ajouter_photos'); ?></button>
                    </form>

                    <form action="admin_galerie.php" method="POST" class="admin-add-video-form">
                        <input type="hidden" name="album_id" value="<?php echo (int) $album['id']; ?>">
                        <div class="form-group">
                            <label><i class="fas fa-video"></i> <?php echo t('admin_galerie_ajouter_video'); ?></label>
                            <input type="url" name="video_url" placeholder="<?php echo t('admin_galerie_video_url_placeholder'); ?>" required>
                        </div>
                        <div class="form-group">
                            <input type="text" name="video_title" placeholder="<?php echo t('admin_galerie_video_titre_placeholder'); ?>">
                        </div>
                        <button type="submit" name="add_video" class="btn-add-item"><i class="fas fa-plus"></i> <?php echo t('admin_galerie_ajouter_video'); ?></button>
                    </form>

                    <form action="admin_galerie.php" method="POST" enctype="multipart/form-data" class="admin-upload-photos-form">
                        <input type="hidden" name="album_id" value="<?php echo (int) $album['id']; ?>">
                        <label class="upload-zone upload-zone-multi" id="zip-upload-zone">
                            <i class="fas fa-file-zipper"></i>
                            <span class="upload-zone-text"><?php echo t('admin_galerie_import_zip_text'); ?></span>
                            <span class="upload-zone-filename" id="zip-upload-filename"></span>
                            <input type="file" name="zip_file" accept=".zip" class="upload-zone-input" id="zip-upload-input">
                        </label>
                        <p class="admin-field-hint"><?php echo t('admin_galerie_import_zip_hint'); ?></p>
                        <button type="submit" name="import_zip" class="btn-add-item"><i class="fas fa-file-import"></i> <?php echo t('admin_galerie_importer_zip'); ?></button>
                    </form>

                    <form action="admin_galerie.php" method="POST" enctype="multipart/form-data" class="admin-upload-photos-form">
                        <input type="hidden" name="album_id" value="<?php echo (int) $album['id']; ?>">
                        <label class="upload-zone upload-zone-multi" id="folder-upload-zone">
                            <i class="fas fa-folder-open"></i>
                            <span class="upload-zone-text"><?php echo t('admin_galerie_import_dossier_text'); ?></span>
                            <span class="upload-zone-filename" id="folder-upload-filenames"></span>
                            <input type="file" name="folder_files[]" class="upload-zone-input" id="folder-upload-input" webkitdirectory directory multiple>
                        </label>
                        <p class="admin-field-hint"><?php echo t('admin_galerie_import_dossier_hint'); ?></p>
                        <button type="submit" name="import_folder" class="btn-add-item"><i class="fas fa-folder-plus"></i> <?php echo t('admin_galerie_importer_dossier'); ?></button>
                    </form>

                    <?php if (empty($photos)): ?>
                        <p class="gallery-empty"><i class="fas fa-image"></i> <?php echo t('admin_galerie_aucune_photo'); ?></p>
                    <?php else: ?>
                        <div class="photo-admin-grid">
                            <?php foreach ($photos as $i => $photo):
                                $is_video = ($photo['media_type'] ?? 'photo') === 'video';
                                $thumb = $photo['image_path'] ?: 'images/logo-isstm.jpg';
                            ?>
                                <div class="photo-admin-item<?php echo $is_video ? ' is-video' : ''; ?>" id="photo-<?php echo $photo['id']; ?>">
                                    <div class="photo-admin-thumb">
                                        <img src="<?php echo htmlspecialchars($thumb); ?>" alt="<?php echo htmlspecialchars($photo['alt_text'] ?: $photo['title']); ?>">
                                        <?php if ($is_video): ?><span class="video-play-badge"><i class="fas fa-play"></i></span><?php endif; ?>
                                        <div class="photo-admin-reorder">
                                            <form action="admin_galerie.php?view=edit&id=<?php echo $album_id; ?>" method="POST">
                                                <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                                                <input type="hidden" name="reorder_photo_id" value="<?php echo $photo['id']; ?>">
                                                <button type="submit" name="direction" value="up" class="btn-reorder" title="<?php echo t('admin_galerie_monter'); ?>" <?php echo $i === 0 ? 'disabled' : ''; ?>><i class="fas fa-arrow-up"></i></button>
                                            </form>
                                            <form action="admin_galerie.php?view=edit&id=<?php echo $album_id; ?>" method="POST">
                                                <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                                                <input type="hidden" name="reorder_photo_id" value="<?php echo $photo['id']; ?>">
                                                <button type="submit" name="direction" value="down" class="btn-reorder" title="<?php echo t('admin_galerie_descendre'); ?>" <?php echo $i === count($photos) - 1 ? 'disabled' : ''; ?>><i class="fas fa-arrow-down"></i></button>
                                            </form>
                                        </div>
                                        <form action="admin_galerie.php?view=edit&id=<?php echo $album_id; ?>" method="POST" class="delete-form js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_galerie_confirm_delete_photo')); ?>">
                                            <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                                            <button type="submit" name="delete_photo_id" value="<?php echo $photo['id']; ?>" class="btn-delete" title="<?php echo t('admin_galerie_supprimer_cette_photo'); ?>"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                        <?php if (!$is_video): ?>
                                            <form action="admin_galerie.php?view=edit&id=<?php echo $album_id; ?>" method="POST" class="set-cover-form">
                                                <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                                                <button type="submit" name="set_cover_photo_id" value="<?php echo $photo['id']; ?>" class="btn-set-cover" title="<?php echo t('admin_galerie_definir_couverture'); ?>"><i class="fas fa-star"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    <form action="admin_galerie.php?view=edit&id=<?php echo $album_id; ?>#photo-<?php echo $photo['id']; ?>" method="POST" class="photo-meta-form">
                                        <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                                        <input type="hidden" name="update_photo_id" value="<?php echo $photo['id']; ?>">
                                        <div class="form-group"><label><?php echo t('admin_galerie_photo_titre_label'); ?></label><input type="text" name="photo_title" value="<?php echo htmlspecialchars($photo['title'] ?? ''); ?>"></div>
                                        <div class="form-group"><label><?php echo t('admin_description_label'); ?></label><textarea name="photo_description" rows="2"><?php echo htmlspecialchars($photo['description'] ?? ''); ?></textarea></div>
                                        <div class="photo-meta-row">
                                            <div class="form-group"><label><?php echo t('admin_galerie_photo_date_prise'); ?></label><input type="date" name="photo_taken_at" value="<?php echo htmlspecialchars($photo['taken_at'] ?? ''); ?>"></div>
                                            <div class="form-group"><label><?php echo t('admin_galerie_lieu'); ?></label><input type="text" name="photo_location" value="<?php echo htmlspecialchars($photo['location'] ?? ''); ?>"></div>
                                        </div>
                                        <div class="photo-meta-row">
                                            <div class="form-group"><label><?php echo t('admin_galerie_photo_photographe'); ?></label><input type="text" name="photo_photographer" value="<?php echo htmlspecialchars($photo['photographer'] ?? ''); ?>"></div>
                                            <div class="form-group"><label><?php echo t('admin_galerie_photo_tags'); ?></label><input type="text" name="photo_tags" value="<?php echo htmlspecialchars($photo['tags'] ?? ''); ?>"></div>
                                        </div>
                                        <div class="form-group"><label><?php echo t('admin_galerie_photo_alt'); ?></label><input type="text" name="photo_alt_text" value="<?php echo htmlspecialchars($photo['alt_text'] ?? ''); ?>"></div>
                                        <button type="submit" class="btn-outline btn-save-photo-meta"><i class="fas fa-check"></i> <?php echo t('admin_galerie_enregistrer'); ?></button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else:
            // --- Vue liste ---
            $f_status = $_GET['status'] ?? '';
            $f_cat = isset($_GET['fcat']) && ctype_digit($_GET['fcat']) ? (int) $_GET['fcat'] : 0;
            $f_q = trim($_GET['fq'] ?? '');

            $where = ['1=1'];
            $params = [];
            $types = '';
            if ($f_status === 'publie' || $f_status === 'brouillon') {
                $where[] = 'a.status = ?';
                $params[] = $f_status;
                $types .= 's';
            }
            if ($f_cat > 0) {
                $where[] = 'a.category_id = ?';
                $params[] = $f_cat;
                $types .= 'i';
            }
            if ($f_q !== '') {
                $where[] = '(a.title_fr LIKE ? OR a.location LIKE ?)';
                $like = '%' . $f_q . '%';
                $params[] = $like; $params[] = $like;
                $types .= 'ss';
            }
            $where_sql = implode(' AND ', $where);
            $sql = "SELECT a.*, c.name_fr AS cat_name, c.icon AS cat_icon, (SELECT COUNT(*) FROM gallery_photos p WHERE p.album_id = a.id) AS photo_count
                    FROM gallery_albums a LEFT JOIN gallery_categories c ON c.id = a.category_id
                    WHERE $where_sql ORDER BY a.created_at DESC";
            $stmt = $mysqli->prepare($sql);
            if ($types !== '') { $stmt->bind_param($types, ...$params); }
            $stmt->execute();
            $all_albums = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $stats = $mysqli->query("SELECT
                (SELECT COUNT(*) FROM gallery_albums) AS total_albums,
                (SELECT COUNT(*) FROM gallery_albums WHERE status='publie') AS published_albums,
                (SELECT COUNT(*) FROM gallery_photos) AS total_photos
            ")->fetch_assoc();
        ?>

            <div class="admin-galerie-stats">
                <div class="admin-stat-card"><i class="fas fa-folder-open"></i><div><strong><?php echo (int) $stats['total_albums']; ?></strong><span><?php echo t('admin_galerie_albums_total'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-eye"></i><div><strong><?php echo (int) $stats['published_albums']; ?></strong><span><?php echo t('admin_galerie_albums_publies'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-image"></i><div><strong><?php echo (int) $stats['total_photos']; ?></strong><span><?php echo t('admin_galerie_photos_total'); ?></span></div></div>
            </div>

            <div class="admin-galerie-toolbar">
                <a href="admin_galerie.php?view=edit" class="btn-add-item admin-new-album-btn"><i class="fas fa-plus"></i> <?php echo t('admin_galerie_nouvel_album'); ?></a>
                <form method="GET" action="admin_galerie.php" class="admin-galerie-filters">
                    <input type="hidden" name="view" value="list">
                    <input type="text" name="fq" value="<?php echo htmlspecialchars($f_q); ?>" placeholder="<?php echo t('admin_galerie_rechercher_album'); ?>">
                    <select name="fcat">
                        <option value="0"><?php echo t('admin_toutes_categories'); ?></option>
                        <?php foreach ($all_categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $f_cat === (int) $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name_fr']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status">
                        <option value=""><?php echo t('admin_tous_statuts'); ?></option>
                        <option value="publie" <?php echo $f_status === 'publie' ? 'selected' : ''; ?>><?php echo t('admin_statut_publie'); ?></option>
                        <option value="brouillon" <?php echo $f_status === 'brouillon' ? 'selected' : ''; ?>><?php echo t('admin_statut_brouillon'); ?></option>
                    </select>
                    <button type="submit" class="btn-filter-gallery"><i class="fas fa-filter"></i></button>
                </form>
            </div>

            <?php if (empty($all_albums)): ?>
                <p class="gallery-empty"><i class="fas fa-folder-open"></i> <?php echo t('admin_galerie_aucun_album'); ?></p>
            <?php else: ?>
                <div class="admin-album-list">
                    <?php foreach ($all_albums as $album): ?>
                        <div class="admin-album-row">
                            <img src="<?php echo htmlspecialchars($album['cover_image'] ?: 'images/logo-isstm.jpg'); ?>" alt="" class="admin-album-row-thumb">
                            <div class="admin-album-row-info">
                                <h4><?php echo htmlspecialchars($album['title_fr']); ?></h4>
                                <div class="admin-album-row-meta">
                                    <?php if ($album['cat_name']): ?><span><i class="fas <?php echo htmlspecialchars($album['cat_icon']); ?>"></i> <?php echo htmlspecialchars($album['cat_name']); ?></span><?php endif; ?>
                                    <?php if ($album['event_date']): ?><span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($album['event_date'])); ?></span><?php endif; ?>
                                    <span><i class="fas fa-images"></i> <?php echo (int) $album['photo_count']; ?> <?php echo t('admin_galerie_photo_suffix'); ?></span>
                                </div>
                            </div>
                            <span class="admin-status-badge admin-status-<?php echo $album['status']; ?>"><?php echo $album['status'] === 'publie' ? t('admin_statut_publie') : t('admin_statut_brouillon'); ?></span>
                            <div class="admin-album-row-actions">
                                <a href="admin_galerie.php?view=edit&id=<?php echo $album['id']; ?>" class="btn-outline" title="<?php echo t('admin_modifier'); ?>"><i class="fas fa-pen"></i></a>
                                <form action="admin_galerie.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="toggle_status_id" value="<?php echo $album['id']; ?>">
                                    <button type="submit" class="btn-outline" title="<?php echo $album['status'] === 'publie' ? t('admin_galerie_depublier') : t('admin_galerie_publier'); ?>">
                                        <i class="fas <?php echo $album['status'] === 'publie' ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                    </button>
                                </form>
                                <form action="admin_galerie.php" method="POST" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_galerie_confirm_delete_album')); ?>">
                                    <button type="submit" name="delete_album_id" value="<?php echo $album['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Onglets de langue + traduction automatique (réutilisés depuis admin_contenu.php)
    document.querySelectorAll('.translatable-field-group').forEach(group => {
        const tabs = group.querySelectorAll('.lang-tab');
        const panels = group.querySelectorAll('.lang-panel');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('is-active'));
                panels.forEach(p => p.classList.remove('is-active'));
                tab.classList.add('is-active');
                group.querySelector(`.lang-panel[data-lang="${tab.dataset.lang}"]`).classList.add('is-active');
            });
        });
        const translateBtn = group.querySelector('.btn-translate-all');
        if (translateBtn) {
            translateBtn.addEventListener('click', async () => {
                const frInput = group.querySelector('[data-lang-input="fr"]');
                const enInput = group.querySelector('[data-lang-input="en"]');
                const mgInput = group.querySelector('[data-lang-input="mg"]');
                const text = frInput.value.trim();
                if (!text) return;
                translateBtn.disabled = true;
                translateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traduction...';
                try {
                    const [enRes, mgRes] = await Promise.all([
                        fetch('translate_api.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ text, source_lang: 'fr', target_lang: 'en' }) }),
                        fetch('translate_api.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ text, source_lang: 'fr', target_lang: 'mg' }) })
                    ]);
                    const enData = await enRes.json();
                    const mgData = await mgRes.json();
                    if (enData.translatedText) enInput.value = enData.translatedText;
                    if (mgData.translatedText) mgInput.value = mgData.translatedText;
                    translateBtn.innerHTML = '<i class="fas fa-check"></i> Traduit !';
                } catch (e) {
                    translateBtn.innerHTML = '<i class="fas fa-triangle-exclamation"></i> Erreur';
                } finally {
                    setTimeout(() => { translateBtn.disabled = false; translateBtn.innerHTML = '<i class="fas fa-language"></i> Traduire'; }, 1800);
                }
            });
        }
    });

    // Zones de dépôt : afficher le nom du fichier choisi
    document.querySelectorAll('.upload-zone-input').forEach(input => {
        input.addEventListener('change', () => {
            const zone = input.closest('.upload-zone');
            const filenameSpan = zone.querySelector('.upload-zone-filename');
            if (input.files.length === 1) {
                filenameSpan.textContent = input.files[0].name;
                zone.classList.add('has-file');
            } else if (input.files.length > 1) {
                filenameSpan.textContent = input.files.length + ' fichiers sélectionnés';
                zone.classList.add('has-file');
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>
