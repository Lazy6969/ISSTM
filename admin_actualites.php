<?php
include_once 'language.php';
require_once 'db_connect.php';

$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';
if (!$is_admin) {
    header('Location: index.php');
    exit;
}

$upload_dir = 'uploads/';
$allowed_img_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
$allowed_doc_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg',
    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$flash = null;

function news_unlink_if_upload($path) {
    if ($path && file_exists($path) && strpos($path, 'uploads/') === 0) {
        unlink($path);
    }
}

function news_slugify($text) {
    $text = trim($text);
    $translit = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    if ($translit !== false) { $text = $translit; }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: ('article-' . uniqid());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Créer / mettre à jour un article ---
    if (isset($_POST['save_article'])) {
        $article_id = isset($_POST['article_id']) && ctype_digit($_POST['article_id']) ? (int) $_POST['article_id'] : 0;
        $title_fr   = trim($_POST['title_fr'] ?? '');
        $title_en   = trim($_POST['title_en'] ?? '');
        $title_mg   = trim($_POST['title_mg'] ?? '');
        $slug_input = trim($_POST['slug'] ?? '');
        $excerpt_fr = trim($_POST['excerpt_fr'] ?? '');
        $excerpt_en = trim($_POST['excerpt_en'] ?? '');
        $excerpt_mg = trim($_POST['excerpt_mg'] ?? '');
        $content_fr = trim($_POST['content_fr'] ?? '');
        $content_en = trim($_POST['content_en'] ?? '');
        $content_mg = trim($_POST['content_mg'] ?? '');
        $category_id = ctype_digit($_POST['category_id'] ?? '') ? (int) $_POST['category_id'] : null;
        $author     = trim($_POST['author'] ?? '');
        $tags       = trim($_POST['tags'] ?? '');
        $status     = in_array($_POST['status'] ?? '', ['brouillon', 'publie', 'archive']) ? $_POST['status'] : 'brouillon';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $published_at = !empty($_POST['published_at']) ? str_replace('T', ' ', $_POST['published_at']) . ':00' : null;
        $video_url  = trim($_POST['video_url'] ?? '');

        $slug = news_slugify($slug_input !== '' ? $slug_input : $title_fr);
        // Assure l'unicité du slug
        $base_slug = $slug; $n = 1;
        while (true) {
            $check = $mysqli->prepare("SELECT id FROM news_articles WHERE slug = ? AND id != ?");
            $notId = $article_id ?: 0;
            $check->bind_param("si", $slug, $notId);
            $check->execute();
            if ($check->get_result()->num_rows === 0) { $check->close(); break; }
            $check->close();
            $n++;
            $slug = $base_slug . '-' . $n;
        }

        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && in_array($_FILES['image']['type'], $allowed_img_types)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_name = 'news_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_name)) {
                $image_path = $upload_dir . $new_name;
            }
        }

        if ($title_fr === '') {
            $flash = ['type' => 'error', 'msg' => "Le titre (FR) de l'article est obligatoire."];
            header("Location: admin_actualites.php?view=" . ($article_id ? "edit&id=$article_id" : "edit") . "&flash=" . urlencode($flash['type'] . '|' . $flash['msg']));
            exit;
        }

        if ($article_id > 0) {
            if ($image_path) {
                $old = $mysqli->query("SELECT image_path FROM news_articles WHERE id = $article_id")->fetch_assoc();
                if ($old) news_unlink_if_upload($old['image_path']);
                $stmt = $mysqli->prepare("UPDATE news_articles SET title_fr=?, title_en=?, title_mg=?, slug=?, excerpt_fr=?, excerpt_en=?, excerpt_mg=?, content_fr=?, content_en=?, content_mg=?, image_path=?, video_url=?, category_id=?, author=?, tags=?, status=?, is_featured=?, published_at=? WHERE id=?");
                $stmt->bind_param("ssssssssssssisssisi", $title_fr, $title_en, $title_mg, $slug, $excerpt_fr, $excerpt_en, $excerpt_mg, $content_fr, $content_en, $content_mg, $image_path, $video_url, $category_id, $author, $tags, $status, $is_featured, $published_at, $article_id);
            } else {
                $stmt = $mysqli->prepare("UPDATE news_articles SET title_fr=?, title_en=?, title_mg=?, slug=?, excerpt_fr=?, excerpt_en=?, excerpt_mg=?, content_fr=?, content_en=?, content_mg=?, video_url=?, category_id=?, author=?, tags=?, status=?, is_featured=?, published_at=? WHERE id=?");
                $stmt->bind_param("sssssssssssisssisi", $title_fr, $title_en, $title_mg, $slug, $excerpt_fr, $excerpt_en, $excerpt_mg, $content_fr, $content_en, $content_mg, $video_url, $category_id, $author, $tags, $status, $is_featured, $published_at, $article_id);
            }
            $stmt->execute();
            $stmt->close();
            $flash = ['type' => 'success', 'msg' => 'Article mis à jour avec succès.'];
        } else {
            $stmt = $mysqli->prepare("INSERT INTO news_articles (title_fr, title_en, title_mg, slug, excerpt_fr, excerpt_en, excerpt_mg, content_fr, content_en, content_mg, image_path, video_url, category_id, author, tags, status, is_featured, published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssssssssisssis", $title_fr, $title_en, $title_mg, $slug, $excerpt_fr, $excerpt_en, $excerpt_mg, $content_fr, $content_en, $content_mg, $image_path, $video_url, $category_id, $author, $tags, $status, $is_featured, $published_at);
            $stmt->execute();
            $article_id = $mysqli->insert_id;
            $stmt->close();
            $flash = ['type' => 'success', 'msg' => 'Article créé avec succès.'];
        }
        header("Location: admin_actualites.php?view=edit&id=$article_id&flash=" . urlencode($flash['type'] . '|' . $flash['msg']));
        exit;
    }

    // --- Supprimer un article ---
    if (isset($_POST['delete_article_id'])) {
        $article_id = (int) $_POST['delete_article_id'];
        $old = $mysqli->query("SELECT image_path FROM news_articles WHERE id = $article_id")->fetch_assoc();
        if ($old) news_unlink_if_upload($old['image_path']);
        $atts = $mysqli->query("SELECT file_path FROM news_attachments WHERE article_id = $article_id");
        while ($a = $atts->fetch_assoc()) { news_unlink_if_upload($a['file_path']); }
        $stmt = $mysqli->prepare("DELETE FROM news_articles WHERE id = ?");
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_actualites.php?flash=" . urlencode('success|Article supprimé.'));
        exit;
    }

    // --- Changer le statut ---
    if (isset($_POST['set_status_id'])) {
        $article_id = (int) $_POST['set_status_id'];
        $new_status = in_array($_POST['new_status'] ?? '', ['brouillon', 'publie', 'archive']) ? $_POST['new_status'] : 'brouillon';
        $stmt = $mysqli->prepare("UPDATE news_articles SET status = ?, published_at = IF(? = 'publie' AND published_at IS NULL, NOW(), published_at) WHERE id = ?");
        $stmt->bind_param("ssi", $new_status, $new_status, $article_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_actualites.php?flash=" . urlencode('success|Statut mis à jour.'));
        exit;
    }

    // --- Basculer "à la une" ---
    if (isset($_POST['toggle_featured_id'])) {
        $article_id = (int) $_POST['toggle_featured_id'];
        $mysqli->query("UPDATE news_articles SET is_featured = IF(is_featured=1,0,1) WHERE id = $article_id");
        header("Location: admin_actualites.php?flash=" . urlencode('success|Mise à la une mise à jour.'));
        exit;
    }

    // --- Ajouter une pièce jointe ---
    if (isset($_POST['add_attachment']) && isset($_FILES['attachment_file'])) {
        $article_id = (int) $_POST['article_id'];
        if ($_FILES['attachment_file']['error'] == 0 && in_array($_FILES['attachment_file']['type'], $allowed_doc_types)) {
            $orig = $_FILES['attachment_file']['name'];
            $ext = pathinfo($orig, PATHINFO_EXTENSION);
            $new_name = 'doc_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['attachment_file']['tmp_name'], $upload_dir . $new_name)) {
                $label = trim($_POST['attachment_label'] ?? '') ?: $orig;
                $path = $upload_dir . $new_name;
                $stmt = $mysqli->prepare("INSERT INTO news_attachments (article_id, file_path, label) VALUES (?,?,?)");
                $stmt->bind_param("iss", $article_id, $path, $label);
                $stmt->execute();
                $stmt->close();
            }
        }
        header("Location: admin_actualites.php?view=edit&id=$article_id&flash=" . urlencode('success|Document ajouté.'));
        exit;
    }

    // --- Supprimer une pièce jointe ---
    if (isset($_POST['delete_attachment_id'])) {
        $att_id = (int) $_POST['delete_attachment_id'];
        $article_id = (int) $_POST['article_id'];
        $old = $mysqli->query("SELECT file_path FROM news_attachments WHERE id = $att_id")->fetch_assoc();
        if ($old) news_unlink_if_upload($old['file_path']);
        $stmt = $mysqli->prepare("DELETE FROM news_attachments WHERE id = ?");
        $stmt->bind_param("i", $att_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_actualites.php?view=edit&id=$article_id&flash=" . urlencode('success|Document supprimé.'));
        exit;
    }

    // --- Ajouter des photos et vidéos à l'article (upload multiple, sans limite de nombre) ---
    if (isset($_POST['upload_news_photos']) && isset($_FILES['new_news_photos'])) {
        $article_id = (int) $_POST['article_id'];
        $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $video_exts = ['mp4', 'webm', 'mov', 'ogg', 'avi', 'mkv', 'm4v'];
        $maxOrderRow = $mysqli->query("SELECT COALESCE(MAX(display_order),0) AS m FROM news_photos WHERE article_id = $article_id")->fetch_assoc();
        $order = (int) $maxOrderRow['m'];
        $stmt = $mysqli->prepare("INSERT INTO news_photos (article_id, image_path, media_type, video_url, title, display_order) VALUES (?,?,?,?,?,?)");
        $count_photos = 0;
        $count_videos = 0;
        foreach ($_FILES['new_news_photos']['name'] as $key => $name) {
            if ($_FILES['new_news_photos']['error'][$key] != 0) continue;
            $fext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $is_image = in_array($fext, $image_exts);
            $is_video_file = in_array($fext, $video_exts);
            if (!$is_image && !$is_video_file) continue;
            $title = pathinfo($name, PATHINFO_FILENAME);

            if ($is_image) {
                $new_name = 'news_photo_' . uniqid() . '_' . $key . '.' . $fext;
                if (move_uploaded_file($_FILES['new_news_photos']['tmp_name'][$key], $upload_dir . $new_name)) {
                    $order++;
                    $path = $upload_dir . $new_name;
                    $media_type = 'photo';
                    $video_url = '';
                    $stmt->bind_param("issssi", $article_id, $path, $media_type, $video_url, $title, $order);
                    $stmt->execute();
                    $count_photos++;
                }
            } else {
                $new_name = 'news_video_' . uniqid() . '_' . $key . '.' . $fext;
                if (move_uploaded_file($_FILES['new_news_photos']['tmp_name'][$key], $upload_dir . $new_name)) {
                    $order++;
                    $video_path = $upload_dir . $new_name;
                    $empty_image_path = '';
                    $media_type = 'video';
                    $stmt->bind_param("issssi", $article_id, $empty_image_path, $media_type, $video_path, $title, $order);
                    $stmt->execute();
                    $count_videos++;
                }
            }
        }
        $stmt->close();
        header("Location: admin_actualites.php?view=edit&id=$article_id&flash=" . urlencode('success|' . $count_photos . ' photo(s) et ' . $count_videos . ' vidéo(s) ajoutées.'));
        exit;
    }

    // --- Importer un dossier entier de photos/vidéos pour l'article ---
    if (isset($_POST['import_news_folder']) && isset($_FILES['news_folder_files'])) {
        $article_id = (int) $_POST['article_id'];
        $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $video_exts = ['mp4', 'webm', 'mov', 'ogg', 'avi', 'mkv', 'm4v'];
        $maxOrderRow = $mysqli->query("SELECT COALESCE(MAX(display_order),0) AS m FROM news_photos WHERE article_id = $article_id")->fetch_assoc();
        $order = (int) $maxOrderRow['m'];
        $stmt = $mysqli->prepare("INSERT INTO news_photos (article_id, image_path, media_type, video_url, title, display_order) VALUES (?,?,?,?,?,?)");
        $count_photos = 0;
        $count_videos = 0;
        $count_skipped = 0;
        foreach ($_FILES['news_folder_files']['name'] as $key => $name) {
            if ($_FILES['news_folder_files']['error'][$key] != 0) { $count_skipped++; continue; }
            $basename = basename($name);
            if ($basename === '' || $basename[0] === '.') { $count_skipped++; continue; }
            $fext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
            $is_image = in_array($fext, $image_exts);
            $is_video_file = in_array($fext, $video_exts);
            if (!$is_image && !$is_video_file) { $count_skipped++; continue; }
            $title = pathinfo($basename, PATHINFO_FILENAME);

            if ($is_image) {
                $new_name = 'news_photo_' . uniqid() . '_' . $key . '.' . $fext;
                if (!move_uploaded_file($_FILES['news_folder_files']['tmp_name'][$key], $upload_dir . $new_name)) { $count_skipped++; continue; }
                $order++;
                $path = $upload_dir . $new_name;
                $media_type = 'photo';
                $video_url = '';
                $stmt->bind_param("issssi", $article_id, $path, $media_type, $video_url, $title, $order);
                $stmt->execute();
                $count_photos++;
            } else {
                $new_name = 'news_video_' . uniqid() . '_' . $key . '.' . $fext;
                if (!move_uploaded_file($_FILES['news_folder_files']['tmp_name'][$key], $upload_dir . $new_name)) { $count_skipped++; continue; }
                $order++;
                $video_path = $upload_dir . $new_name;
                $empty_image_path = '';
                $media_type = 'video';
                $stmt->bind_param("issssi", $article_id, $empty_image_path, $media_type, $video_path, $title, $order);
                $stmt->execute();
                $count_videos++;
            }
        }
        $stmt->close();
        $msg = "Import terminé : $count_photos photo(s) et $count_videos vidéo(s) importées";
        if ($count_skipped > 0) $msg .= ", $count_skipped fichier(s) ignoré(s)";
        $msg .= '.';
        header("Location: admin_actualites.php?view=edit&id=$article_id&flash=" . urlencode('success|' . $msg));
        exit;
    }

    // --- Supprimer une photo/vidéo de l'article ---
    if (isset($_POST['delete_news_photo_id'])) {
        $photo_id = (int) $_POST['delete_news_photo_id'];
        $article_id = (int) $_POST['article_id'];
        $old = $mysqli->query("SELECT image_path, video_url FROM news_photos WHERE id = $photo_id")->fetch_assoc();
        if ($old) { news_unlink_if_upload($old['image_path']); news_unlink_if_upload($old['video_url']); }
        $stmt = $mysqli->prepare("DELETE FROM news_photos WHERE id = ?");
        $stmt->bind_param("i", $photo_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_actualites.php?view=edit&id=$article_id&flash=" . urlencode('success|Photo supprimée.'));
        exit;
    }

    // --- Définir une photo existante comme image principale de l'article ---
    if (isset($_POST['set_news_cover_id'])) {
        $photo_id = (int) $_POST['set_news_cover_id'];
        $article_id = (int) $_POST['article_id'];
        $photo = $mysqli->query("SELECT image_path, media_type FROM news_photos WHERE id = $photo_id AND article_id = $article_id")->fetch_assoc();
        if ($photo && ($photo['media_type'] ?? 'photo') === 'photo' && $photo['image_path'] && file_exists($photo['image_path'])) {
            $ext = pathinfo($photo['image_path'], PATHINFO_EXTENSION);
            $new_cover_path = $upload_dir . 'news_' . uniqid() . '.' . $ext;
            if (copy($photo['image_path'], $new_cover_path)) {
                $old = $mysqli->query("SELECT image_path FROM news_articles WHERE id = $article_id")->fetch_assoc();
                if ($old) news_unlink_if_upload($old['image_path']);
                $stmt = $mysqli->prepare("UPDATE news_articles SET image_path = ? WHERE id = ?");
                $stmt->bind_param("si", $new_cover_path, $article_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        header("Location: admin_actualites.php?view=edit&id=$article_id&flash=" . urlencode('success|Image principale mise à jour.'));
        exit;
    }
}

if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

$all_categories = $mysqli->query("SELECT * FROM news_categories ORDER BY display_order ASC")->fetch_all(MYSQLI_ASSOC);
$view = $_GET['view'] ?? 'list';

$page_title = t('admin_actualites_breadcrumb');

include 'header.php';
?>

<a href="administrateur.php" class="btn-outline btn-back-sticky"><i class="fas fa-arrow-left"></i> <?php echo t('admin_retour_tableau_bord'); ?></a>

<div class="page-banner news-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="administrateur.php"><?php echo t('administration'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('admin_actualites_breadcrumb'); ?></span>
        </nav>
        <h1><?php echo t('admin_actualites_breadcrumb'); ?></h1>
        <p><?php echo t('admin_actualites_soustitre'); ?></p>
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
            $article_id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;
            $article = null;
            if ($article_id > 0) {
                $stmt = $mysqli->prepare("SELECT * FROM news_articles WHERE id = ?");
                $stmt->bind_param("i", $article_id);
                $stmt->execute();
                $article = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }
            $attachments = [];
            if ($article_id > 0) {
                $stmt = $mysqli->prepare("SELECT * FROM news_attachments WHERE article_id = ? ORDER BY id ASC");
                $stmt->bind_param("i", $article_id);
                $stmt->execute();
                $attachments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
            $news_photos = [];
            if ($article_id > 0) {
                $stmt = $mysqli->prepare("SELECT * FROM news_photos WHERE article_id = ? ORDER BY display_order ASC, id ASC");
                $stmt->bind_param("i", $article_id);
                $stmt->execute();
                $news_photos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
            $published_at_value = '';
            if (!empty($article['published_at'])) {
                $published_at_value = str_replace(' ', 'T', substr($article['published_at'], 0, 16));
            }
        ?>
            <a href="admin_actualites.php" class="admin-galerie-back"><i class="fas fa-chevron-left"></i> <?php echo t('admin_actualites_retour_liste'); ?></a>
            <h2 class="admin-section-title"><?php echo $article ? t('admin_actualites_modifier_article') : t('admin_actualites_nouvel_article'); ?></h2>

            <form action="admin_actualites.php" method="POST" enctype="multipart/form-data" class="admin-form admin-album-form">
                <?php if ($article): ?><input type="hidden" name="article_id" value="<?php echo (int) $article['id']; ?>"><?php endif; ?>

                <div class="album-form-grid">
                    <div class="album-form-main">
                        <div class="form-group">
                            <label><?php echo t('admin_actualites_titre_article_label'); ?></label>
                            <div class="translatable-field-group" data-group-id="news-title">
                                <div class="lang-tabs">
                                    <button type="button" class="lang-tab is-active" data-lang="fr">🇫🇷 FR</button>
                                    <button type="button" class="lang-tab" data-lang="en">🇬🇧 EN</button>
                                    <button type="button" class="lang-tab" data-lang="mg">🇲🇬 MG</button>
                                    <button type="button" class="btn-translate-all"><i class="fas fa-language"></i> Traduire</button>
                                </div>
                                <div class="lang-panel is-active" data-lang="fr"><input type="text" name="title_fr" id="news-title-fr" value="<?php echo htmlspecialchars($article['title_fr'] ?? ''); ?>" data-lang-input="fr" placeholder="<?php echo t('admin_galerie_titre_fr_placeholder'); ?>" required></div>
                                <div class="lang-panel" data-lang="en"><input type="text" name="title_en" value="<?php echo htmlspecialchars($article['title_en'] ?? ''); ?>" data-lang-input="en" placeholder="<?php echo t('admin_galerie_titre_en_placeholder'); ?>"></div>
                                <div class="lang-panel" data-lang="mg"><input type="text" name="title_mg" value="<?php echo htmlspecialchars($article['title_mg'] ?? ''); ?>" data-lang-input="mg" placeholder="<?php echo t('admin_galerie_titre_mg_placeholder'); ?>"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_actualites_slug_label'); ?> <span class="admin-field-hint"><?php echo t('admin_actualites_slug_hint'); ?></span></label>
                            <input type="text" name="slug" id="news-slug" value="<?php echo htmlspecialchars($article['slug'] ?? ''); ?>" placeholder="ex-titre-de-larticle">
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_actualites_resume_label'); ?></label>
                            <div class="translatable-field-group" data-group-id="news-excerpt">
                                <div class="lang-tabs">
                                    <button type="button" class="lang-tab is-active" data-lang="fr">🇫🇷 FR</button>
                                    <button type="button" class="lang-tab" data-lang="en">🇬🇧 EN</button>
                                    <button type="button" class="lang-tab" data-lang="mg">🇲🇬 MG</button>
                                    <button type="button" class="btn-translate-all"><i class="fas fa-language"></i> Traduire</button>
                                </div>
                                <div class="lang-panel is-active" data-lang="fr"><textarea name="excerpt_fr" rows="2" data-lang-input="fr" placeholder="<?php echo t('admin_actualites_resume_placeholder'); ?>"><?php echo htmlspecialchars($article['excerpt_fr'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="en"><textarea name="excerpt_en" rows="2" data-lang-input="en"><?php echo htmlspecialchars($article['excerpt_en'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="mg"><textarea name="excerpt_mg" rows="2" data-lang-input="mg"><?php echo htmlspecialchars($article['excerpt_mg'] ?? ''); ?></textarea></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_actualites_contenu_label'); ?></label>
                            <div class="translatable-field-group" data-group-id="news-content">
                                <div class="lang-tabs">
                                    <button type="button" class="lang-tab is-active" data-lang="fr">🇫🇷 FR</button>
                                    <button type="button" class="lang-tab" data-lang="en">🇬🇧 EN</button>
                                    <button type="button" class="lang-tab" data-lang="mg">🇲🇬 MG</button>
                                    <button type="button" class="btn-translate-all"><i class="fas fa-language"></i> Traduire</button>
                                </div>
                                <div class="lang-panel is-active" data-lang="fr"><textarea name="content_fr" rows="10" data-lang-input="fr" placeholder="<?php echo t('admin_actualites_contenu_placeholder'); ?>"><?php echo htmlspecialchars($article['content_fr'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="en"><textarea name="content_en" rows="10" data-lang-input="en"><?php echo htmlspecialchars($article['content_en'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="mg"><textarea name="content_mg" rows="10" data-lang-input="mg"><?php echo htmlspecialchars($article['content_mg'] ?? ''); ?></textarea></div>
                            </div>
                        </div>

                        <div class="album-form-row">
                            <div class="form-group">
                                <label><?php echo t('admin_categorie_label'); ?></label>
                                <select name="category_id">
                                    <option value=""><?php echo t('admin_aucune_option'); ?></option>
                                    <?php foreach ($all_categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($article['category_id']) && (int) $article['category_id'] === (int) $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name_fr']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><?php echo t('admin_statut_label'); ?></label>
                                <select name="status">
                                    <option value="brouillon" <?php echo (($article['status'] ?? 'brouillon') === 'brouillon') ? 'selected' : ''; ?>><?php echo t('admin_statut_brouillon'); ?></option>
                                    <option value="publie" <?php echo (($article['status'] ?? '') === 'publie') ? 'selected' : ''; ?>><?php echo t('admin_statut_publie'); ?></option>
                                    <option value="archive" <?php echo (($article['status'] ?? '') === 'archive') ? 'selected' : ''; ?>><?php echo t('admin_actualites_statut_archive'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="album-form-row">
                            <div class="form-group">
                                <label><?php echo t('admin_actualites_auteur_label'); ?></label>
                                <input type="text" name="author" value="<?php echo htmlspecialchars($article['author'] ?? ''); ?>" placeholder="<?php echo t('admin_galerie_auteur_placeholder'); ?>">
                            </div>
                            <div class="form-group">
                                <label><?php echo t('admin_actualites_tags_label'); ?> <span class="admin-field-hint"><?php echo t('admin_actualites_tags_hint'); ?></span></label>
                                <input type="text" name="tags" value="<?php echo htmlspecialchars($article['tags'] ?? ''); ?>" placeholder="<?php echo t('admin_actualites_tags_placeholder'); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_actualites_date_publication_label'); ?> <span class="admin-field-hint"><?php echo t('admin_actualites_date_publication_hint'); ?></span></label>
                            <input type="datetime-local" name="published_at" value="<?php echo htmlspecialchars($published_at_value); ?>">
                        </div>

                        <div class="form-group admin-checkbox-group">
                            <label><input type="checkbox" name="is_featured" value="1" <?php echo !empty($article['is_featured']) ? 'checked' : ''; ?>> <?php echo t('admin_actualites_mettre_a_la_une'); ?></label>
                        </div>
                    </div>

                    <div class="album-form-side">
                        <div class="form-group upload-zone-group">
                            <label><?php echo t('admin_actualites_image_principale'); ?></label>
                            <p class="profile-card-hint"><?php echo t('admin_actualites_image_principale_hint'); ?></p>
                            <?php if (!empty($article['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($article['image_path']); ?>" class="album-cover-preview" alt="Image actuelle">
                            <?php endif; ?>
                            <label class="upload-zone">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <span class="upload-zone-text"><?php echo t('admin_choisir_image_deposer'); ?></span>
                                <span class="upload-zone-filename"></span>
                                <input type="file" name="image" accept="image/jpeg, image/png, image/jpg, image/webp" class="upload-zone-input">
                            </label>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-video"></i> <?php echo t('admin_actualites_video_label'); ?></label>
                            <p class="profile-card-hint"><?php echo t('admin_actualites_video_hint'); ?></p>
                            <input type="url" name="video_url" value="<?php echo htmlspecialchars($article['video_url'] ?? ''); ?>" placeholder="<?php echo t('admin_galerie_video_url_placeholder'); ?>">
                        </div>
                    </div>
                </div>

                <button type="submit" name="save_article" class="btn-submit"><i class="fas fa-save"></i> <?php echo $article ? t('admin_enregistrer_modifications') : t('admin_actualites_creer_article'); ?></button>
            </form>

            <?php if ($article): ?>
                <div class="admin-photos-section">
                    <h2 class="admin-section-title"><i class="fas fa-paperclip"></i> <?php echo t('admin_actualites_documents_joints'); ?> (<?php echo count($attachments); ?>)</h2>

                    <form action="admin_actualites.php" method="POST" enctype="multipart/form-data" class="admin-upload-photos-form">
                        <input type="hidden" name="article_id" value="<?php echo (int) $article['id']; ?>">
                        <div class="album-form-row">
                            <div class="form-group">
                                <label><?php echo t('admin_actualites_libelle_document'); ?></label>
                                <input type="text" name="attachment_label" placeholder="<?php echo t('admin_actualites_libelle_placeholder'); ?>">
                            </div>
                            <div class="form-group upload-zone-group">
                                <label><?php echo t('admin_actualites_fichier_label'); ?></label>
                                <label class="upload-zone">
                                    <i class="fas fa-cloud-arrow-up"></i>
                                    <span class="upload-zone-text"><?php echo t('admin_actualites_choisir_fichier'); ?></span>
                                    <span class="upload-zone-filename"></span>
                                    <input type="file" name="attachment_file" accept=".pdf,.doc,.docx,image/*" class="upload-zone-input">
                                </label>
                            </div>
                        </div>
                        <button type="submit" name="add_attachment" class="btn-add-item"><i class="fas fa-upload"></i> <?php echo t('admin_actualites_ajouter_document'); ?></button>
                    </form>

                    <?php if (!empty($attachments)): ?>
                        <ul class="admin-attachments-list">
                            <?php foreach ($attachments as $att): ?>
                                <li>
                                    <a href="<?php echo htmlspecialchars($att['file_path']); ?>" target="_blank" rel="noopener"><i class="fas fa-file-arrow-down"></i> <?php echo htmlspecialchars($att['label'] ?: basename($att['file_path'])); ?></a>
                                    <form action="admin_actualites.php?view=edit&id=<?php echo $article_id; ?>" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_actualites_confirm_delete_document')); ?>">
                                        <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                        <button type="submit" name="delete_attachment_id" value="<?php echo $att['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="admin-photos-section">
                    <h2 class="admin-section-title"><i class="fas fa-images"></i> <?php echo t('admin_actualites_photos_titre'); ?> (<?php echo count($news_photos); ?>)</h2>

                    <form action="admin_actualites.php" method="POST" enctype="multipart/form-data" class="admin-upload-photos-form">
                        <input type="hidden" name="article_id" value="<?php echo (int) $article['id']; ?>">
                        <label class="upload-zone upload-zone-multi" id="news-multi-upload-zone">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <span class="upload-zone-text"><?php echo t('admin_galerie_multi_upload_text'); ?></span>
                            <span class="upload-zone-filename" id="news-multi-upload-filenames"></span>
                            <input type="file" name="new_news_photos[]" accept="image/jpeg, image/png, image/jpg, image/webp, video/mp4, video/webm, video/quicktime, video/ogg" class="upload-zone-input" id="news-multi-upload-input" multiple>
                        </label>
                        <p class="admin-field-hint"><?php echo t('admin_galerie_multi_upload_hint'); ?></p>
                        <button type="submit" name="upload_news_photos" class="btn-add-item"><i class="fas fa-upload"></i> <?php echo t('admin_galerie_ajouter_photos'); ?></button>
                    </form>

                    <form action="admin_actualites.php" method="POST" enctype="multipart/form-data" class="admin-upload-photos-form">
                        <input type="hidden" name="article_id" value="<?php echo (int) $article['id']; ?>">
                        <label class="upload-zone upload-zone-multi" id="news-folder-upload-zone">
                            <i class="fas fa-folder-open"></i>
                            <span class="upload-zone-text"><?php echo t('admin_galerie_import_dossier_text'); ?></span>
                            <span class="upload-zone-filename" id="news-folder-upload-filenames"></span>
                            <input type="file" name="news_folder_files[]" class="upload-zone-input" id="news-folder-upload-input" webkitdirectory directory multiple>
                        </label>
                        <p class="admin-field-hint"><?php echo t('admin_galerie_import_dossier_hint'); ?></p>
                        <button type="submit" name="import_news_folder" class="btn-add-item"><i class="fas fa-folder-plus"></i> <?php echo t('admin_galerie_importer_dossier'); ?></button>
                    </form>

                    <?php if (empty($news_photos)): ?>
                        <p class="gallery-empty"><i class="fas fa-image"></i> <?php echo t('admin_galerie_aucune_photo'); ?></p>
                    <?php else: ?>
                        <div class="photo-admin-grid">
                            <?php foreach ($news_photos as $photo):
                                $is_video = ($photo['media_type'] ?? 'photo') === 'video';
                                $thumb = $photo['image_path'] ?: 'images/logo-isstm.jpg';
                            ?>
                                <div class="photo-admin-item<?php echo $is_video ? ' is-video' : ''; ?>">
                                    <div class="photo-admin-thumb">
                                        <img src="<?php echo htmlspecialchars($thumb); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                                        <?php if ($is_video): ?><span class="video-play-badge"><i class="fas fa-play"></i></span><?php endif; ?>
                                        <form action="admin_actualites.php?view=edit&id=<?php echo $article_id; ?>" method="POST" class="delete-form js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_galerie_confirm_delete_photo')); ?>">
                                            <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                            <button type="submit" name="delete_news_photo_id" value="<?php echo $photo['id']; ?>" class="btn-delete" title="<?php echo t('admin_galerie_supprimer_cette_photo'); ?>"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                        <?php if (!$is_video): ?>
                                            <form action="admin_actualites.php?view=edit&id=<?php echo $article_id; ?>" method="POST" class="set-cover-form">
                                                <input type="hidden" name="article_id" value="<?php echo $article_id; ?>">
                                                <button type="submit" name="set_news_cover_id" value="<?php echo $photo['id']; ?>" class="btn-set-cover" title="<?php echo t('admin_actualites_definir_image_principale'); ?>"><i class="fas fa-star"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    <div class="gallery-admin-info"><strong><?php echo htmlspecialchars($photo['title']); ?></strong></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else:
            $f_status = $_GET['status'] ?? '';
            $f_cat = isset($_GET['fcat']) && ctype_digit($_GET['fcat']) ? (int) $_GET['fcat'] : 0;
            $f_q = trim($_GET['fq'] ?? '');

            $where = ['1=1']; $params = []; $types = '';
            if (in_array($f_status, ['brouillon', 'publie', 'archive'])) { $where[] = 'a.status = ?'; $params[] = $f_status; $types .= 's'; }
            if ($f_cat > 0) { $where[] = 'a.category_id = ?'; $params[] = $f_cat; $types .= 'i'; }
            if ($f_q !== '') { $where[] = '(a.title_fr LIKE ? OR a.author LIKE ?)'; $like = '%' . $f_q . '%'; $params[] = $like; $params[] = $like; $types .= 'ss'; }
            $where_sql = implode(' AND ', $where);

            $sql = "SELECT a.*, c.name_fr AS cat_name, c.icon AS cat_icon FROM news_articles a LEFT JOIN news_categories c ON c.id = a.category_id WHERE $where_sql ORDER BY a.created_at DESC";
            $stmt = $mysqli->prepare($sql);
            if ($types !== '') { $stmt->bind_param($types, ...$params); }
            $stmt->execute();
            $all_articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $stats = $mysqli->query("SELECT
                (SELECT COUNT(*) FROM news_articles) AS total,
                (SELECT COUNT(*) FROM news_articles WHERE status='publie') AS published,
                (SELECT COUNT(*) FROM news_articles WHERE status='brouillon') AS drafts,
                (SELECT COALESCE(SUM(views),0) FROM news_articles) AS total_views
            ")->fetch_assoc();
            $subscriber_count = $mysqli->query("SELECT COUNT(*) c FROM newsletter_subscribers")->fetch_assoc()['c'];
        ?>
            <div class="admin-galerie-stats">
                <div class="admin-stat-card"><i class="fas fa-newspaper"></i><div><strong><?php echo (int) $stats['total']; ?></strong><span><?php echo t('admin_actualites_articles_total'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-eye"></i><div><strong><?php echo (int) $stats['published']; ?></strong><span><?php echo t('admin_actualites_articles_publies'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-pen"></i><div><strong><?php echo (int) $stats['drafts']; ?></strong><span><?php echo t('admin_actualites_brouillons_stat'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-chart-line"></i><div><strong><?php echo format_compact_number($stats['total_views']); ?></strong><span><?php echo t('admin_actualites_vues_cumulees'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-envelope-open-text"></i><div><strong><?php echo (int) $subscriber_count; ?></strong><span><?php echo t('admin_actualites_abonnes_newsletter'); ?></span></div></div>
            </div>

            <div class="admin-galerie-toolbar">
                <a href="admin_actualites.php?view=edit" class="btn-add-item admin-new-album-btn"><i class="fas fa-plus"></i> <?php echo t('admin_actualites_nouvel_article'); ?></a>
                <form method="GET" action="admin_actualites.php" class="admin-galerie-filters">
                    <input type="hidden" name="view" value="list">
                    <input type="text" name="fq" value="<?php echo htmlspecialchars($f_q); ?>" placeholder="<?php echo t('admin_actualites_rechercher_article'); ?>">
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
                        <option value="archive" <?php echo $f_status === 'archive' ? 'selected' : ''; ?>><?php echo t('admin_actualites_statut_archive'); ?></option>
                    </select>
                    <button type="submit" class="btn-filter-gallery"><i class="fas fa-filter"></i></button>
                </form>
            </div>

            <?php if (empty($all_articles)): ?>
                <p class="gallery-empty"><i class="fas fa-newspaper"></i> <?php echo t('admin_actualites_aucun_article'); ?></p>
            <?php else: ?>
                <div class="admin-album-list">
                    <?php foreach ($all_articles as $article): ?>
                        <div class="admin-album-row">
                            <img src="<?php echo htmlspecialchars($article['image_path'] ?: 'images/logo-isstm.jpg'); ?>" alt="" class="admin-album-row-thumb">
                            <div class="admin-album-row-info">
                                <h4>
                                    <?php if ($article['is_featured']): ?><i class="fas fa-star" style="color:var(--secondary-color)" title="<?php echo t('admin_actualites_a_la_une_title'); ?>"></i><?php endif; ?>
                                    <?php echo htmlspecialchars($article['title_fr']); ?>
                                </h4>
                                <div class="admin-album-row-meta">
                                    <?php if ($article['cat_name']): ?><span><i class="fas <?php echo htmlspecialchars($article['cat_icon']); ?>"></i> <?php echo htmlspecialchars($article['cat_name']); ?></span><?php endif; ?>
                                    <?php if ($article['published_at']): ?><span><i class="far fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($article['published_at'])); ?></span><?php endif; ?>
                                    <span><i class="far fa-eye"></i> <?php echo format_compact_number($article['views']); ?> <?php echo t('admin_actualites_vue_suffix'); ?></span>
                                </div>
                            </div>
                            <form action="admin_actualites.php" method="POST" class="admin-status-select-form">
                                <input type="hidden" name="set_status_id" value="<?php echo $article['id']; ?>">
                                <select name="new_status" onchange="this.form.submit()" class="admin-status-select admin-status-select-<?php echo $article['status']; ?>">
                                    <option value="brouillon" <?php echo $article['status'] === 'brouillon' ? 'selected' : ''; ?>><?php echo t('admin_statut_brouillon'); ?></option>
                                    <option value="publie" <?php echo $article['status'] === 'publie' ? 'selected' : ''; ?>><?php echo t('admin_statut_publie'); ?></option>
                                    <option value="archive" <?php echo $article['status'] === 'archive' ? 'selected' : ''; ?>><?php echo t('admin_actualites_statut_archive'); ?></option>
                                </select>
                            </form>
                            <div class="admin-album-row-actions">
                                <a href="admin_actualites.php?view=edit&id=<?php echo $article['id']; ?>" class="btn-outline" title="<?php echo t('admin_modifier'); ?>"><i class="fas fa-pen"></i></a>
                                <form action="admin_actualites.php" method="POST" style="display:inline;">
                                    <button type="submit" name="toggle_featured_id" value="<?php echo $article['id']; ?>" class="btn-outline" title="<?php echo $article['is_featured'] ? t('admin_actualites_retirer_une') : t('admin_actualites_mettre_une'); ?>">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </form>
                                <form action="admin_actualites.php" method="POST" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_actualites_confirm_delete_article')); ?>">
                                    <button type="submit" name="delete_article_id" value="<?php echo $article['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
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

    document.querySelectorAll('.upload-zone-input').forEach(input => {
        input.addEventListener('change', () => {
            const zone = input.closest('.upload-zone');
            const filenameSpan = zone.querySelector('.upload-zone-filename');
            if (input.files.length >= 1) {
                filenameSpan.textContent = input.files.length > 1 ? (input.files.length + ' fichiers sélectionnés') : input.files[0].name;
                zone.classList.add('has-file');
            }
        });
    });

    // Génération automatique du slug à partir du titre FR (uniquement si le champ slug est vide)
    const titleInput = document.getElementById('news-title-fr');
    const slugInput = document.getElementById('news-slug');
    if (titleInput && slugInput) {
        titleInput.addEventListener('blur', () => {
            if (slugInput.value.trim() === '' && titleInput.value.trim() !== '') {
                slugInput.value = titleInput.value.trim()
                    .toLowerCase()
                    .normalize('NFD').replace(/[̀-ͯ]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
            }
        });
    }
});
</script>

<?php include 'footer.php'; ?>
