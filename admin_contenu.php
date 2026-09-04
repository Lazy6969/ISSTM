<?php
include_once 'language.php';
require_once 'db_connect.php';

// --- Sécurité : Vérification de l'administrateur ---
$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';
if (!$is_admin) {
    header('Location: index.php');
    exit;
}

/**
 * Fonction pour gérer l'upload d'une image unique.
 * @param string $file_key La clé dans $_FILES (ex: 'directeur_image').
 * @param string $content_key La clé dans la BDD (ex: 'directeur_image_path').
 * @param string $prefix Le préfixe pour le nom du fichier (ex: 'directeur').
 * @param mysqli $mysqli L'objet de connexion à la BDD.
 */
function handle_single_image_upload($file_key, $content_key, $prefix, $mysqli) {
    // ... (la logique sera insérée ici)
}

/**
 * Affiche un champ traduisible (FR/EN/MG) sous forme d'onglets de langue,
 * avec un bouton de traduction automatique (MyMemory) pour pré-remplir EN et MG à partir du FR.
 *
 * @param string $label      Libellé affiché au-dessus du champ.
 * @param string $name_fr    Attribut name du champ FR (ex: "content[directeur_nom][fr]").
 * @param string $val_fr     Valeur actuelle du champ FR.
 * @param string $name_en    Attribut name du champ EN.
 * @param string $val_en     Valeur actuelle du champ EN.
 * @param string $name_mg    Attribut name du champ MG.
 * @param string $val_mg     Valeur actuelle du champ MG.
 * @param string $type       'text' ou 'textarea'.
 * @param int    $rows       Nombre de lignes si textarea.
 */
function translatable_field($label, $name_fr, $val_fr, $name_en, $val_en, $name_mg, $val_mg, $type = 'text', $rows = 3) {
    static $uid = 0;
    $uid++;
    $tag = $type === 'textarea' ? 'textarea' : 'input';
    ?>
    <div class="form-group">
        <label><?php echo htmlspecialchars($label); ?></label>
        <div class="translatable-field-group" data-group-id="tf-<?php echo $uid; ?>">
            <div class="lang-tabs">
                <button type="button" class="lang-tab is-active" data-lang="fr">🇫🇷 FR</button>
                <button type="button" class="lang-tab" data-lang="en">🇬🇧 EN</button>
                <button type="button" class="lang-tab" data-lang="mg">🇲🇬 MG</button>
                <button type="button" class="btn-translate-all" title="Traduire automatiquement le FR vers l'EN et le MG">
                    <i class="fas fa-language"></i> Traduire
                </button>
            </div>
            <div class="lang-panel is-active" data-lang="fr">
                <?php if ($tag === 'textarea'): ?>
                    <textarea name="<?php echo htmlspecialchars($name_fr); ?>" rows="<?php echo (int) $rows; ?>" data-lang-input="fr"><?php echo htmlspecialchars($val_fr); ?></textarea>
                <?php else: ?>
                    <input type="text" name="<?php echo htmlspecialchars($name_fr); ?>" value="<?php echo htmlspecialchars($val_fr); ?>" data-lang-input="fr">
                <?php endif; ?>
            </div>
            <div class="lang-panel" data-lang="en">
                <?php if ($tag === 'textarea'): ?>
                    <textarea name="<?php echo htmlspecialchars($name_en); ?>" rows="<?php echo (int) $rows; ?>" data-lang-input="en"><?php echo htmlspecialchars($val_en); ?></textarea>
                <?php else: ?>
                    <input type="text" name="<?php echo htmlspecialchars($name_en); ?>" value="<?php echo htmlspecialchars($val_en); ?>" data-lang-input="en">
                <?php endif; ?>
            </div>
            <div class="lang-panel" data-lang="mg">
                <?php if ($tag === 'textarea'): ?>
                    <textarea name="<?php echo htmlspecialchars($name_mg); ?>" rows="<?php echo (int) $rows; ?>" data-lang-input="mg"><?php echo htmlspecialchars($val_mg); ?></textarea>
                <?php else: ?>
                    <input type="text" name="<?php echo htmlspecialchars($name_mg); ?>" value="<?php echo htmlspecialchars($val_mg); ?>" data-lang-input="mg">
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

// --- Traitement des actions sur les témoignages (Ajout/Suppression) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Suppression d'un témoignage ---
    if (isset($_POST['delete_testimonial_id'])) {
        $testimonial_id = (int) $_POST['delete_testimonial_id'];

        // 1. Récupérer le chemin de l'image pour la supprimer du serveur
        $stmt = $mysqli->prepare("SELECT image_path FROM testimonials WHERE id = ?");
        $stmt->bind_param("i", $testimonial_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $image_path = $row['image_path'];
            if (file_exists($image_path) && strpos($image_path, 'uploads/') === 0) {
                unlink($image_path);
            }
        }
        $stmt->close();

        // 2. Supprimer le témoignage de la base de données
        $stmt = $mysqli->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->bind_param("i", $testimonial_id);
        $stmt->execute();
        $stmt->close();
        $update_success = true;
    }

    // --- Ajout d'un nouveau témoignage ---
    if (isset($_POST['add_testimonial'])) {
        $new_author = $_POST['new_author'];
        $new_program = $_POST['new_program'];
        $new_quote_fr = $_POST['new_quote_fr'];
        $new_quote_en = $_POST['new_quote_en'];
        $new_quote_mg = $_POST['new_quote_mg'];
        $upload_path = 'images/etudiant/default.png'; // Image par défaut

        if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] == 0) {
            $upload_dir = 'uploads/';
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            if (in_array($_FILES['new_image']['type'], $allowed_types)) {
                $file_extension = pathinfo($_FILES['new_image']['name'], PATHINFO_EXTENSION);
                $new_filename = 'testimonial_' . uniqid() . '.' . $file_extension;
                $new_upload_path = $upload_dir . $new_filename;
                if (move_uploaded_file($_FILES['new_image']['tmp_name'], $new_upload_path)) {
                    $upload_path = $new_upload_path;
                }
            }
        }

        $stmt = $mysqli->prepare("INSERT INTO testimonials (author_name, program, image_path, quote_fr, quote_en, quote_mg, display_order) VALUES (?, ?, ?, ?, ?, ?, 99)");
        $stmt->bind_param("ssssss", $new_author, $new_program, $upload_path, $new_quote_fr, $new_quote_en, $new_quote_mg);
        $stmt->execute();
        $stmt->close();
        $update_success = true;
    }

    // --- Ajout/Suppression de slides du héros ---
    if (isset($_POST['delete_slide_id'])) {
        $slide_id = (int) $_POST['delete_slide_id'];
        // Supprimer l'image du serveur
        $stmt = $mysqli->prepare("SELECT image_path FROM hero_slides WHERE id = ?");
        $stmt->bind_param("i", $slide_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (file_exists($row['image_path']) && strpos($row['image_path'], 'uploads/') === 0) {
                unlink($row['image_path']);
            }
        }
        $stmt->close();
        // Supprimer de la BDD
        $stmt = $mysqli->prepare("DELETE FROM hero_slides WHERE id = ?");
        $stmt->bind_param("i", $slide_id);
        $stmt->execute();
        $stmt->close();
        $update_success = true;
    }

    // --- Réinitialiser le diaporama du héros aux images par défaut ---
    if (isset($_POST['reset_hero_slides'])) {
        $result = $mysqli->query("SELECT image_path FROM hero_slides");
        while ($row = $result->fetch_assoc()) {
            if (file_exists($row['image_path']) && strpos($row['image_path'], 'uploads/') === 0) {
                unlink($row['image_path']);
            }
        }
        $mysqli->query("DELETE FROM hero_slides");
        $default_slides = ['images/slide1.jpg', 'images/slide2.jpg', 'images/slide3.jpg'];
        $stmt = $mysqli->prepare("INSERT INTO hero_slides (image_path, media_type, display_order) VALUES (?, 'image', ?)");
        foreach ($default_slides as $i => $path) {
            $order = $i + 1;
            $stmt->bind_param("si", $path, $order);
            $stmt->execute();
        }
        $stmt->close();
        $update_success = true;
    }

    // --- Réinitialiser le compteur de vues de la page d'accueil ---
    if (isset($_POST['reset_homepage_views'])) {
        if ($mysqli->query("SHOW TABLES LIKE 'site_stats'")->num_rows > 0) {
            $mysqli->query("UPDATE site_stats SET stat_value = 0 WHERE stat_key = 'homepage_views'");
        }
        $update_success = true;
    }

    // Ajout d'une ou plusieurs diapositives du héros : images et/ou vidéos, tous formats courants.
    if (isset($_FILES['new_slide_media']) && !empty(array_filter($_FILES['new_slide_media']['name']))) {
        $upload_dir = 'uploads/';
        $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'avif'];
        $video_exts = ['mp4', 'webm', 'mov', 'ogg', 'avi', 'mkv', 'm4v'];
        $stmt = $mysqli->prepare("INSERT INTO hero_slides (image_path, media_type, display_order) VALUES (?, ?, 99)");
        foreach ($_FILES['new_slide_media']['name'] as $key => $name) {
            if ($_FILES['new_slide_media']['error'][$key] != 0) continue;
            $file_extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $is_image = in_array($file_extension, $image_exts);
            $is_video_file = in_array($file_extension, $video_exts);
            if (!$is_image && !$is_video_file) continue;
            $media_type = $is_image ? 'image' : 'video';
            $new_filename = 'hero_slide_' . uniqid() . '_' . $key . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            if (move_uploaded_file($_FILES['new_slide_media']['tmp_name'][$key], $upload_path)) {
                $stmt->bind_param("ss", $upload_path, $media_type);
                $stmt->execute();
                $update_success = true;
            }
        }
        $stmt->close();
    }

    // --- Supprimer un média du carrousel "Inscription en ligne" ---
    if (isset($_POST['delete_cta_media_id'])) {
        $mid = (int) $_POST['delete_cta_media_id'];
        $stmt = $mysqli->prepare("SELECT media_path FROM preinscription_cta_media WHERE id = ?");
        $stmt->bind_param("i", $mid);
        $stmt->execute();
        if ($row = $stmt->get_result()->fetch_assoc()) {
            if (file_exists($row['media_path']) && strpos($row['media_path'], 'uploads/') === 0) {
                unlink($row['media_path']);
            }
        }
        $stmt->close();
        $stmt = $mysqli->prepare("DELETE FROM preinscription_cta_media WHERE id = ?");
        $stmt->bind_param("i", $mid);
        $stmt->execute();
        $stmt->close();
        $update_success = true;
    }

    // Ajout d'un ou plusieurs médias au carrousel "Inscription en ligne" : images et/ou vidéos.
    if (isset($_FILES['new_cta_media']) && !empty(array_filter($_FILES['new_cta_media']['name']))) {
        $upload_dir = 'uploads/';
        $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'avif'];
        $video_exts = ['mp4', 'webm', 'mov', 'ogg', 'avi', 'mkv', 'm4v'];
        $order = (int) $mysqli->query("SELECT COALESCE(MAX(display_order),0) AS m FROM preinscription_cta_media")->fetch_assoc()['m'];
        $stmt = $mysqli->prepare("INSERT INTO preinscription_cta_media (media_type, media_path, display_order) VALUES (?,?,?)");
        foreach ($_FILES['new_cta_media']['name'] as $key => $name) {
            if ($_FILES['new_cta_media']['error'][$key] != 0) continue;
            $file_extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $is_image = in_array($file_extension, $image_exts);
            $is_video_file = in_array($file_extension, $video_exts);
            if (!$is_image && !$is_video_file) continue;
            $media_type = $is_image ? 'image' : 'video';
            $new_filename = 'preinscription_cta_' . uniqid() . '_' . $key . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            if (move_uploaded_file($_FILES['new_cta_media']['tmp_name'][$key], $upload_path)) {
                $order++;
                $stmt->bind_param("ssi", $media_type, $upload_path, $order);
                $stmt->execute();
                $update_success = true;
            }
        }
        $stmt->close();
    }

    // --- Mise à jour d'un témoignage existant ---
    if (isset($_POST['update_testimonial'])) {
        $testimonial_id = $_POST['testimonial_id'];
        // La logique de mise à jour est similaire à celle de l'ajout.
        // Pour la simplicité, nous nous concentrons sur l'ajout/suppression.
        // La mise à jour des champs texte se fait via le formulaire principal.
    }

}


// --- Traitement du formulaire de mise à jour ---
$update_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Traitement de l'upload de l'image du directeur ---
    if (isset($_FILES['directeur_image']) && $_FILES['directeur_image']['error'] == 0) {
        $upload_dir = 'uploads/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_type = $_FILES['directeur_image']['type'];

        if (in_array($file_type, $allowed_types)) {
            // Générer un nom de fichier unique pour éviter les conflits
            $file_extension = pathinfo($_FILES['directeur_image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'directeur_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['directeur_image']['tmp_name'], $upload_path)) {
                // Récupérer l'ancien chemin d'image pour le supprimer
                $old_path_query = $mysqli->query("SELECT content_value_fr FROM site_content WHERE content_key = 'directeur_image_path'");
                if ($old_path_row = $old_path_query->fetch_assoc()) {
                    $old_path = $old_path_row['content_value_fr'];
                    // On ne supprime pas les images du dossier 'images/' par sécurité
                    if (file_exists($old_path) && strpos($old_path, 'uploads/') === 0) {
                        unlink($old_path);
                    }
                }

                // Mettre à jour le chemin dans la base de données
                $update_img_sql = "UPDATE site_content SET content_value_fr = ?, content_value_en = ?, content_value_mg = ? WHERE content_key = 'directeur_image_path'";
                $img_stmt = $mysqli->prepare($update_img_sql);
                $img_stmt->bind_param("sss", $upload_path, $upload_path, $upload_path);
                $img_stmt->execute();
                $img_stmt->close();
            }
        }
    }

    // --- Traitement de l'upload de l'image de fond du header ---
    if (isset($_FILES['header_bg_image']) && $_FILES['header_bg_image']['error'] == 0) {
        $upload_dir = 'uploads/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $file_type = $_FILES['header_bg_image']['type'];

        if (in_array($file_type, $allowed_types)) {
            $file_extension = pathinfo($_FILES['header_bg_image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'header_bg_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['header_bg_image']['tmp_name'], $upload_path)) {
                $old_path_query = $mysqli->query("SELECT content_value_fr FROM site_content WHERE content_key = 'header_bg_image_path'");
                if ($old_path_row = $old_path_query->fetch_assoc()) {
                    $old_path = $old_path_row['content_value_fr'];
                    if ($old_path && file_exists($old_path) && strpos($old_path, 'uploads/') === 0) {
                        unlink($old_path);
                    }
                }

                $img_stmt = $mysqli->prepare("INSERT INTO site_content (content_key, content_value_fr, content_value_en, content_value_mg) VALUES ('header_bg_image_path', ?, ?, ?) ON DUPLICATE KEY UPDATE content_value_fr = VALUES(content_value_fr), content_value_en = VALUES(content_value_en), content_value_mg = VALUES(content_value_mg)");
                $img_stmt->bind_param("sss", $upload_path, $upload_path, $upload_path);
                $img_stmt->execute();
                $img_stmt->close();
                $update_success = true;
            }
        }
    }

    // --- Suppression de l'image de fond du header (retour au fond par défaut) ---
    if (isset($_POST['remove_header_bg'])) {
        $old_path_query = $mysqli->query("SELECT content_value_fr FROM site_content WHERE content_key = 'header_bg_image_path'");
        if ($old_path_row = $old_path_query->fetch_assoc()) {
            $old_path = $old_path_row['content_value_fr'];
            if ($old_path && file_exists($old_path) && strpos($old_path, 'uploads/') === 0) {
                unlink($old_path);
            }
        }
        $mysqli->query("UPDATE site_content SET content_value_fr = '', content_value_en = '', content_value_mg = '' WHERE content_key = 'header_bg_image_path'");
        $update_success = true;
    }

    // --- Traitement de l'upload des images du diaporama ---
    if (isset($_FILES['slide_images'])) {
        $upload_dir = 'uploads/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];

        foreach ($_FILES['slide_images']['name'] as $key => $name) {
            if ($_FILES['slide_images']['error'][$key] == 0) {
                $file_type = $_FILES['slide_images']['type'][$key];
                if (in_array($file_type, $allowed_types)) {
                    // Générer un nom de fichier unique
                    $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                    $new_filename = 'slide_' . str_replace('_image_path', '', $key) . '_' . uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;

                    if (move_uploaded_file($_FILES['slide_images']['tmp_name'][$key], $upload_path)) {
                        // Supprimer l'ancienne image si elle est dans le dossier 'uploads'
                        $old_path_query = $mysqli->query("SELECT content_value_fr FROM site_content WHERE content_key = '$key'");
                        if ($old_path_row = $old_path_query->fetch_assoc()) {
                            $old_path = $old_path_row['content_value_fr'];
                            if (file_exists($old_path) && strpos($old_path, 'uploads/') === 0) {
                                unlink($old_path);
                            }
                        }

                        // Mettre à jour la base de données
                        $update_img_sql = "UPDATE site_content SET content_value_fr = ?, content_value_en = ?, content_value_mg = ? WHERE content_key = ?";
                        $img_stmt = $mysqli->prepare($update_img_sql);
                        $img_stmt->bind_param("ssss", $upload_path, $upload_path, $upload_path, $key);
                        $img_stmt->execute();
                        $img_stmt->close();
                    }
                }
            }
        }
    }

    // --- Traitement des nouvelles images uniques (Mission, Vision, Logo) ---
    $single_images_map = [
        'mission_image' => ['key' => 'mission_image_path', 'prefix' => 'mission'],
        'vision_image' => ['key' => 'vision_image_path', 'prefix' => 'vision'],
        'logo_image' => ['key' => 'logo_image_path', 'prefix' => 'logo'],
    ];

    foreach ($single_images_map as $file_key => $info) {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == 0) {
            $upload_dir = 'uploads/';
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/svg+xml'];
            $file_type = $_FILES[$file_key]['type'];

            if (in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION);
                $new_filename = $info['prefix'] . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $upload_path)) {
                    // Supprimer l'ancienne image
                    $old_path_query = $mysqli->query("SELECT content_value_fr FROM site_content WHERE content_key = '{$info['key']}'");
                    if ($old_path_row = $old_path_query->fetch_assoc()) {
                        $old_path = $old_path_row['content_value_fr'];
                        if (file_exists($old_path) && strpos($old_path, 'uploads/') === 0) {
                            unlink($old_path);
                        }
                    }
                    // Mettre à jour la BDD
                    $update_img_sql = "UPDATE site_content SET content_value_fr = ?, content_value_en = ?, content_value_mg = ? WHERE content_key = ?";
                    $img_stmt = $mysqli->prepare($update_img_sql);
                    $img_stmt->bind_param("ssss", $upload_path, $upload_path, $upload_path, $info['key']);
                    $img_stmt->execute();
                    $img_stmt->close();
                }
            }
        }
    }

    // Préparer la requête de mise à jour
    $sql = "UPDATE site_content SET content_value_fr = ?, content_value_en = ?, content_value_mg = ? WHERE content_key = ?";
    $stmt = $mysqli->prepare($sql);

    // Mise à jour des témoignages existants
    if (isset($_POST['testimonials'])) {
        $update_testimonial_stmt = $mysqli->prepare("UPDATE testimonials SET author_name = ?, program = ?, quote_fr = ?, quote_en = ?, quote_mg = ? WHERE id = ?");
        foreach ($_POST['testimonials'] as $id => $data) {
            $update_testimonial_stmt->bind_param("sssssi", $data['author'], $data['program'], $data['quote_fr'], $data['quote_en'], $data['quote_mg'], $id);
            $update_testimonial_stmt->execute();
        }
        $update_testimonial_stmt->close();
    }


    if (isset($_POST['content'])) {
        foreach ($_POST['content'] as $key => $values) {
            // Certains champs (ex: statistiques numériques) n'ont qu'un input FR ; on réutilise sa valeur
            // pour EN/MG au lieu d'écraser ces colonnes avec du vide à chaque enregistrement.
            $val_fr = $values['fr'] ?? '';
            $val_en = ($values['en'] ?? '') !== '' ? $values['en'] : $val_fr;
            $val_mg = ($values['mg'] ?? '') !== '' ? $values['mg'] : $val_fr;
            $stmt->bind_param("ssss", $val_fr, $val_en, $val_mg, $key);
            $stmt->execute();
        }
    }
    $stmt->close();
    $update_success = true;
}

// --- Récupération du contenu depuis la BDD ---
$contenus = [];
$result = $mysqli->query("SELECT content_key, content_value_fr, content_value_en, content_value_mg FROM site_content");
while ($row = $result->fetch_assoc()) {
    $contenus[$row['content_key']] = $row;
}

// --- Récupération des témoignages depuis la nouvelle table ---
$testimonials = [];
$result = $mysqli->query("SELECT * FROM testimonials ORDER BY display_order ASC, id ASC");
while ($row = $result->fetch_assoc()) {
    $testimonials[] = $row;
}
// --- Récupération des slides du héros ---
$hero_slides = [];
$result = $mysqli->query("SELECT * FROM hero_slides ORDER BY display_order ASC, id ASC");
while ($row = $result->fetch_assoc()) {
    $hero_slides[] = $row;
}

// --- Récupération des médias du carrousel "Inscription en ligne" (bas de inscription.php) ---
$cta_media = $mysqli->query("SELECT * FROM preinscription_cta_media ORDER BY display_order ASC, id ASC")->fetch_all(MYSQLI_ASSOC);

// --- Compteur de vues de la page d'accueil (voir index.php) ---
$homepage_views = 0;
if ($check_stats = $mysqli->query("SHOW TABLES LIKE 'site_stats'")) {
    if ($check_stats->num_rows > 0) {
        $row = $mysqli->query("SELECT stat_value FROM site_stats WHERE stat_key = 'homepage_views'")->fetch_assoc();
        $homepage_views = $row ? (int) $row['stat_value'] : 0;
    }
}

// --- Comptage des blocs du Campus (gérés depuis la page dédiée admin_campus.php) ---
$campus_blocs_count = 0;
if ($check_campus_table = $mysqli->query("SHOW TABLES LIKE 'campus_blocs'")) {
    if ($check_campus_table->num_rows > 0) {
        $campus_blocs_count = (int) $mysqli->query("SELECT COUNT(*) AS c FROM campus_blocs")->fetch_assoc()['c'];
    }
}

$page_title = t('admin_contenu_breadcrumb');

include 'header.php';
?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="administrateur.php"><?php echo t('administration'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('admin_contenu_breadcrumb'); ?></span>
        </nav>
        <h1><?php echo t('admin_contenu_breadcrumb'); ?></h1>
        <p><?php echo t('admin_contenu_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <?php include 'background_animation.php'; ?>

    <!-- Bouton de retour (positionné hors du conteneur, complètement à gauche) -->
    <a href="administrateur.php" class="btn-outline btn-back-sticky">
        <i class="fas fa-arrow-left"></i> <?php echo t('admin_retour_tableau_bord'); ?>
    </a>

    <div class="container">
        <form action="admin_contenu.php" method="POST" class="admin-form" enctype="multipart/form-data">
            <h2 class="admin-form-title"><?php echo t('admin_contenu_form_titre'); ?></h2>

            <?php if ($update_success): ?>
                <p class="admin-success">
                    <i class="fas fa-check-circle"></i> <?php echo t('admin_contenu_succes'); ?>
                </p>
            <?php endif; ?>

            <div class="admin-layout">
                <!-- Navigation des sections -->
                <nav class="admin-sidebar">
                    <button type="button" class="admin-nav-link" data-target="panel-hero"><i class="fas fa-images"></i> <?php echo t('admin_contenu_nav_hero'); ?></button>
                    <button type="button" class="admin-nav-link" data-target="panel-directeur"><i class="fas fa-user-tie"></i> <?php echo t('admin_contenu_nav_directeur'); ?></button>
                    <button type="button" class="admin-nav-link" data-target="panel-header-bg"><i class="fas fa-image"></i> <?php echo t('admin_contenu_nav_header_bg'); ?></button>
                    <button type="button" class="admin-nav-link" data-target="panel-mission"><i class="fas fa-bullseye"></i> <?php echo t('admin_contenu_nav_mission'); ?></button>
                    <button type="button" class="admin-nav-link" data-target="panel-stats"><i class="fas fa-chart-bar"></i> <?php echo t('admin_contenu_nav_stats'); ?></button>
                    <button type="button" class="admin-nav-link" data-target="panel-testimonials"><i class="fas fa-comment-dots"></i> <?php echo t('admin_contenu_nav_temoignages'); ?></button>
                    <button type="button" class="admin-nav-link" data-target="panel-images"><i class="fas fa-globe-americas"></i> <?php echo t('admin_contenu_images_globales'); ?></button>
                    <button type="button" class="admin-nav-link" data-target="panel-campus"><i class="fas fa-school"></i> <?php echo t('admin_contenu_nav_campus'); ?></button>
                    <button type="button" class="admin-nav-link" data-target="panel-inscription-infos"><i class="fas fa-calendar-days"></i> <?php echo t('admin_contenu_nav_inscription_infos'); ?></button>
                    <button type="button" class="admin-nav-link" data-target="panel-inscription-cta"><i class="fas fa-user-graduate"></i> <?php echo t('admin_contenu_nav_cta'); ?></button>
                    <button type="button" class="admin-nav-link" data-target="panel-footer"><i class="fas fa-shoe-prints"></i> <?php echo t('admin_contenu_nav_footer'); ?></button>
                    <button type="button" class="admin-nav-link" data-target="panel-contact"><i class="fas fa-address-book"></i> <?php echo t('admin_contenu_nav_contact'); ?></button>
                </nav>

                <div class="admin-panels">

            <!-- Catégorie : Héros (Diaporama) -->
            <fieldset class="form-category" id="panel-hero">
                <legend><i class="fas fa-images"></i> <?php echo t('admin_contenu_legend_hero'); ?></legend>
                <p class="admin-panel-hint"><?php echo t('admin_contenu_hint_hero'); ?></p>
                <div class="form-group image-upload-group">
                    <label><?php echo t('admin_contenu_label_images_diaporama'); ?></label>
                    <div class="image-previews-grid">
                        <?php foreach ($hero_slides as $slide):
                            $slide_is_video = ($slide['media_type'] ?? 'image') === 'video';
                        ?>
                            <div class="image-preview-container<?php echo $slide_is_video ? ' is-video' : ''; ?>">
                                <?php if ($slide_is_video): ?>
                                    <video src="<?php echo htmlspecialchars($slide['image_path']); ?>" muted></video>
                                    <span class="video-play-badge"><i class="fas fa-play"></i></span>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($slide['image_path']); ?>" alt="Aperçu Diapositive">
                                <?php endif; ?>
                                <div class="delete-form">
                                    <button type="submit" name="delete_slide_id" value="<?php echo (int) $slide['id']; ?>" formaction="admin_contenu.php?panel=panel-hero#panel-hero" formnovalidate class="btn-delete js-confirm-btn" title="<?php echo t('admin_contenu_supprimer_diapositive'); ?>" data-confirm-msg="<?php echo htmlspecialchars(t('admin_contenu_confirm_delete_diapositive')); ?>"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="admin-panel-actions">
                    <button type="submit" name="reset_hero_slides" value="1" formaction="admin_contenu.php?panel=panel-hero#panel-hero" formnovalidate class="btn-outline js-confirm-btn" data-confirm-msg="<?php echo htmlspecialchars(t('admin_contenu_confirm_reset_hero')); ?>"><i class="fas fa-rotate-left"></i> <?php echo t('admin_contenu_reinitialiser_hero'); ?></button>
                </div>
                <!-- Formulaire pour ajouter une nouvelle diapositive (image ou vidéo, plusieurs à la fois) -->
                <div class="add-new-item">
                    <h4><?php echo t('admin_contenu_ajouter_diapositive'); ?></h4>
                    <div class="form-group upload-zone-group">
                        <label><?php echo t('admin_contenu_nouvelle_image'); ?></label>
                        <label class="upload-zone upload-zone-multi">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <span class="upload-zone-text"><?php echo t('admin_galerie_multi_upload_text'); ?></span>
                            <span class="upload-zone-filename"></span>
                            <input type="file" name="new_slide_media[]" accept="image/*,video/*" class="upload-zone-input" multiple>
                        </label>
                        <p class="admin-field-hint"><?php echo t('admin_banners_upload_hint'); ?></p>
                    </div>
                </div>
            </fieldset>

            <!-- Catégorie : Mot du Directeur -->
            <fieldset class="form-category" id="panel-directeur">
                <legend><i class="fas fa-user-tie"></i> <?php echo t('admin_contenu_legend_directeur'); ?></legend>
                <p class="admin-panel-hint"><?php echo t('admin_contenu_hint_directeur'); ?></p>
                <div class="form-group image-upload-group">
                    <label><?php echo t('admin_contenu_label_photo_directeur'); ?></label>
                    <div class="image-preview-container director-photo">
                        <img src="<?php echo htmlspecialchars($contenus['directeur_image_path']['content_value_fr']); ?>" alt="Aperçu Directeur">
                        <div class="upload-overlay">
                            <i class="fas fa-camera"></i> <?php echo t('admin_remplacer'); ?>
                        </div>
                        <input type="file" name="directeur_image" accept="image/jpeg, image/png, image/jpg" class="hidden-file-input">
                    </div>
                </div>
                <?php translatable_field(
                    t('admin_contenu_nom_directeur'),
                    'content[directeur_nom][fr]', $contenus['directeur_nom']['content_value_fr'],
                    'content[directeur_nom][en]', $contenus['directeur_nom']['content_value_en'],
                    'content[directeur_nom][mg]', $contenus['directeur_nom']['content_value_mg'],
                    'text'
                ); ?>
                <?php translatable_field(
                    t('admin_contenu_mot_directeur'),
                    'content[mot_directeur_contenu][fr]', $contenus['mot_directeur_contenu']['content_value_fr'],
                    'content[mot_directeur_contenu][en]', $contenus['mot_directeur_contenu']['content_value_en'],
                    'content[mot_directeur_contenu][mg]', $contenus['mot_directeur_contenu']['content_value_mg'],
                    'textarea', 5
                ); ?>
            </fieldset>

            <!-- Catégorie : Image de fond du header -->
            <fieldset class="form-category" id="panel-header-bg">
                <legend><i class="fas fa-image"></i> <?php echo t('admin_contenu_nav_header_bg'); ?></legend>
                <p class="admin-panel-hint"><?php echo t('admin_header_bg_soustitre'); ?></p>
                <div class="form-group image-upload-group">
                    <label><?php echo t('admin_header_bg_upload'); ?></label>
                    <?php $header_bg_current = $contenus['header_bg_image_path']['content_value_fr'] ?? ''; ?>
                    <?php if ($header_bg_current !== ''): ?>
                        <div class="image-preview-container">
                            <img src="<?php echo htmlspecialchars($header_bg_current); ?>" alt="Aperçu fond du header" style="opacity: 0.5;">
                            <div class="upload-overlay">
                                <i class="fas fa-camera"></i> <?php echo t('admin_remplacer'); ?>
                            </div>
                            <input type="file" name="header_bg_image" accept="image/jpeg, image/png, image/jpg, image/webp" class="hidden-file-input">
                        </div>
                        <button type="submit" name="remove_header_bg" value="1" formaction="admin_contenu.php?panel=panel-header-bg#panel-header-bg" formnovalidate class="btn-outline js-confirm-btn" style="margin-top: 10px;" data-confirm-msg="<?php echo htmlspecialchars(t('admin_header_bg_supprimer') . ' ?'); ?>"><i class="fas fa-trash-alt"></i> <?php echo t('admin_header_bg_supprimer'); ?></button>
                    <?php else: ?>
                        <p class="admin-panel-hint"><?php echo t('admin_header_bg_aucune'); ?></p>
                        <label class="upload-zone">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <span class="upload-zone-text"><?php echo t('admin_choisir_image_deposer'); ?></span>
                            <span class="upload-zone-filename"></span>
                            <input type="file" name="header_bg_image" accept="image/jpeg, image/png, image/jpg, image/webp" class="upload-zone-input">
                        </label>
                    <?php endif; ?>
                </div>
            </fieldset>

            <!-- Catégorie : Mission & Vision -->
            <fieldset class="form-category" id="panel-mission">
                <legend><i class="fas fa-bullseye"></i> <?php echo t('admin_contenu_legend_mission'); ?></legend>
                <p class="admin-panel-hint"><?php echo t('admin_contenu_hint_mission'); ?></p>
                <?php translatable_field(
                    t('notre_mission'),
                    'content[mission_contenu][fr]', $contenus['mission_contenu']['content_value_fr'],
                    'content[mission_contenu][en]', $contenus['mission_contenu']['content_value_en'],
                    'content[mission_contenu][mg]', $contenus['mission_contenu']['content_value_mg'],
                    'textarea', 3
                ); ?>
                <?php translatable_field(
                    t('notre_vision'),
                    'content[vision_contenu][fr]', $contenus['vision_contenu']['content_value_fr'],
                    'content[vision_contenu][en]', $contenus['vision_contenu']['content_value_en'],
                    'content[vision_contenu][mg]', $contenus['vision_contenu']['content_value_mg'],
                    'textarea', 3
                ); ?>
            </fieldset>

            <!-- Catégorie : Statistiques -->
            <fieldset class="form-category" id="panel-stats">
                <legend><i class="fas fa-chart-bar"></i> <?php echo t('admin_contenu_legend_stats'); ?></legend>
                <p class="admin-panel-hint"><?php echo t('admin_contenu_hint_stats'); ?></p>
                <div class="form-group">
                    <label><?php echo t('admin_contenu_nb_etudiants'); ?></label>
                    <input type="number" name="content[stat_students][fr]" value="<?php echo htmlspecialchars($contenus['stat_students']['content_value_fr']); ?>" placeholder="<?php echo t('admin_contenu_ex_1500'); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo t('admin_contenu_nb_enseignants'); ?></label>
                    <input type="number" name="content[stat_teachers][fr]" value="<?php echo htmlspecialchars($contenus['stat_teachers']['content_value_fr']); ?>" placeholder="<?php echo t('admin_contenu_ex_100'); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo t('admin_contenu_nb_filieres'); ?></label>
                    <input type="number" name="content[stat_majors][fr]" value="<?php echo htmlspecialchars($contenus['stat_majors']['content_value_fr']); ?>" placeholder="<?php echo t('admin_contenu_ex_20'); ?>">
                </div>
                <div class="form-group admin-homepage-views-group">
                    <label><?php echo t('admin_contenu_vues_page_label'); ?></label>
                    <div class="admin-homepage-views-row">
                        <span class="admin-homepage-views-count"><i class="fas fa-eye"></i> <?php echo number_format($homepage_views, 0, ',', ' '); ?></span>
                        <button type="submit" name="reset_homepage_views" value="1" formaction="admin_contenu.php?panel=panel-stats#panel-stats" formnovalidate class="btn-outline js-confirm-btn" data-confirm-msg="<?php echo htmlspecialchars(t('admin_contenu_confirm_reset_vues')); ?>"><i class="fas fa-rotate-left"></i> <?php echo t('admin_contenu_reinitialiser_vues'); ?></button>
                    </div>
                </div>
            </fieldset>

            <!-- Catégorie : Témoignages -->
            <fieldset class="form-category" id="panel-testimonials">
                <legend><i class="fas fa-comment-dots"></i> <?php echo t('admin_contenu_legend_temoignages'); ?></legend>
                <p class="admin-panel-hint"><?php echo t('admin_contenu_hint_temoignages'); ?></p>
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="testimonial-admin-group">
                        <div class="testimonial-admin-header">
                            <h4><?php echo t('admin_contenu_temoignage_prefix'); ?> <?php echo htmlspecialchars($testimonial['author_name']); ?></h4>
                            <button type="submit" name="delete_testimonial_id" value="<?php echo (int) $testimonial['id']; ?>" formaction="admin_contenu.php?panel=panel-testimonials#panel-testimonials" formnovalidate class="btn-delete js-confirm-btn" title="<?php echo t('admin_contenu_supprimer_temoignage'); ?>" data-confirm-msg="<?php echo htmlspecialchars(t('admin_contenu_confirm_delete_temoignage')); ?>"><i class="fas fa-trash-alt"></i></button>
                        </div>
                        <div class="form-group image-upload-group">
                            <label><?php echo t('admin_contenu_image_etudiant'); ?></label>
                            <div class="image-preview-container director-photo">
                                <img src="<?php echo htmlspecialchars($testimonial['image_path']); ?>" alt="Aperçu de <?php echo htmlspecialchars($testimonial['author_name']); ?>">
                                <div class="upload-overlay">
                                    <i class="fas fa-camera"></i> <span><?php echo t('admin_remplacer'); ?></span>
                                </div>
                                <input type="file" name="testimonial_image[<?php echo $testimonial['id']; ?>]" accept="image/jpeg, image/png, image/jpg" class="hidden-file-input">
                            </div>
                        </div>
                        <?php translatable_field(
                            t('admin_contenu_citation'),
                            "testimonials[{$testimonial['id']}][quote_fr]", $testimonial['quote_fr'],
                            "testimonials[{$testimonial['id']}][quote_en]", $testimonial['quote_en'],
                            "testimonials[{$testimonial['id']}][quote_mg]", $testimonial['quote_mg'],
                            'textarea', 2
                        ); ?>
                        <div class="form-group">
                            <label><?php echo t('admin_contenu_nom_auteur'); ?></label>
                            <input type="text" name="testimonials[<?php echo $testimonial['id']; ?>][author]" value="<?php echo htmlspecialchars($testimonial['author_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label><?php echo t('parcours'); ?></label>
                            <input type="text" name="testimonials[<?php echo $testimonial['id']; ?>][program]" value="<?php echo htmlspecialchars($testimonial['program']); ?>">
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Formulaire pour ajouter un nouveau témoignage -->
                <div class="testimonial-admin-group add-new-testimonial">
                    <h4><?php echo t('admin_contenu_ajouter_temoignage'); ?></h4>
                    <div class="form-group"><label><?php echo t('admin_contenu_nom_auteur'); ?></label><input type="text" name="new_author" placeholder="<?php echo t('admin_contenu_ex_jean_dupont'); ?>"></div>
                    <div class="form-group"><label><?php echo t('parcours'); ?></label><input type="text" name="new_program" placeholder="<?php echo t('admin_contenu_ex_genie_info'); ?>"></div>
                    <?php translatable_field(
                        t('admin_contenu_citation'),
                        'new_quote_fr', '',
                        'new_quote_en', '',
                        'new_quote_mg', '',
                        'textarea', 2
                    ); ?>
                    <div class="form-group upload-zone-group">
                        <label><?php echo t('admin_contenu_image_label'); ?></label>
                        <label class="upload-zone">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <span class="upload-zone-text"><?php echo t('admin_choisir_image_deposer'); ?></span>
                            <span class="upload-zone-filename"></span>
                            <input type="file" name="new_image" accept="image/jpeg, image/png, image/jpg" class="upload-zone-input">
                        </label>
                    </div>
                    <button type="submit" name="add_testimonial" class="btn-add-item"><i class="fas fa-plus"></i> <?php echo t('admin_contenu_ajouter_temoignage_btn'); ?></button>
                </div>
            </fieldset>

            <!-- Catégorie : Images Globales -->
            <fieldset class="form-category" id="panel-images">
                <legend><i class="fas fa-globe-americas"></i> <?php echo t('admin_contenu_images_globales'); ?></legend>
                <p class="admin-panel-hint"><?php echo t('admin_contenu_hint_images'); ?></p>
                <div class="form-group image-upload-group">
                    <label><?php echo t('admin_contenu_logo_principal'); ?></label>
                    <div class="image-preview-container director-photo">
                        <img src="<?php echo htmlspecialchars($contenus['logo_image_path']['content_value_fr']); ?>" alt="Aperçu Logo" style="background-color: #eee;">
                        <div class="upload-overlay">
                            <i class="fas fa-camera"></i> <span><?php echo t('admin_remplacer'); ?></span>
                        </div>
                        <input type="file" name="logo_image" accept="image/jpeg, image/png, image/jpg, image/svg+xml" class="hidden-file-input">
                    </div>
                </div>
                 <div class="form-group image-upload-group">
                    <label><?php echo t('admin_contenu_image_fond_mission'); ?></label>
                    <div class="image-preview-container">
                        <img src="<?php echo htmlspecialchars($contenus['mission_image_path']['content_value_fr']); ?>" alt="Aperçu Mission">
                        <div class="upload-overlay">
                            <i class="fas fa-camera"></i> <span><?php echo t('admin_remplacer'); ?></span>
                        </div>
                        <input type="file" name="mission_image" accept="image/jpeg, image/png, image/jpg" class="hidden-file-input">
                    </div>
                </div>
                 <div class="form-group image-upload-group">
                    <label><?php echo t('admin_contenu_image_fond_vision'); ?></label>
                    <div class="image-preview-container">
                        <img src="<?php echo htmlspecialchars($contenus['vision_image_path']['content_value_fr']); ?>" alt="Aperçu Vision">
                        <div class="upload-overlay">
                            <i class="fas fa-camera"></i> <span><?php echo t('admin_remplacer'); ?></span>
                        </div>
                        <input type="file" name="vision_image" accept="image/jpeg, image/png, image/jpg" class="hidden-file-input">
                    </div>
                </div>
            </fieldset>

            <!-- Catégorie : Pied de page -->
            <fieldset class="form-category" id="panel-footer">
                <legend><i class="fas fa-shoe-prints"></i> <?php echo t('admin_contenu_legend_footer'); ?></legend>
                <p class="admin-panel-hint"><?php echo t('admin_contenu_hint_footer'); ?></p>
                <?php translatable_field(
                    t('admin_contenu_heure_ouverture'),
                    'content[footer_horaires][fr]', $contenus['footer_horaires']['content_value_fr'] ?? '',
                    'content[footer_horaires][en]', $contenus['footer_horaires']['content_value_en'] ?? '',
                    'content[footer_horaires][mg]', $contenus['footer_horaires']['content_value_mg'] ?? '',
                    'text'
                ); ?>
            </fieldset>

            <!-- Catégorie : Contact du site (email, téléphone, Facebook, adresse) -->
            <fieldset class="form-category" id="panel-contact">
                <legend><i class="fas fa-address-book"></i> <?php echo t('admin_contenu_legend_contact'); ?></legend>
                <p class="admin-panel-hint"><?php echo t('admin_contenu_hint_contact'); ?></p>
                <div class="form-group">
                    <label><?php echo t('email'); ?></label>
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" name="content[contact_email][fr]" value="<?php echo htmlspecialchars($contenus['contact_email']['content_value_fr'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo t('admin_contenu_contact_telephones'); ?></label>
                    <div class="contact-phone-list" id="contact-phone-list">
                        <?php
                            $phone_lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $contenus['contact_telephone']['content_value_fr'] ?? '')), fn($l) => $l !== ''));
                            if (empty($phone_lines)) { $phone_lines = ['']; }
                            foreach ($phone_lines as $phone_line):
                        ?>
                            <div class="contact-phone-row">
                                <i class="fas fa-phone"></i>
                                <input type="tel" class="contact-phone-input" value="<?php echo htmlspecialchars($phone_line); ?>" placeholder="+261 38 15 439 77">
                                <button type="button" class="contact-phone-remove" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-xmark"></i></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn-outline" id="contact-phone-add"><i class="fas fa-plus"></i> <?php echo t('admin_contenu_contact_ajouter_numero'); ?></button>
                    <input type="hidden" name="content[contact_telephone][fr]" id="contact-telephone-hidden" value="<?php echo htmlspecialchars($contenus['contact_telephone']['content_value_fr'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo t('admin_contenu_contact_facebook'); ?></label>
                    <i class="fab fa-facebook-f input-icon input-icon-facebook"></i>
                    <input type="url" name="content[contact_facebook][fr]" value="<?php echo htmlspecialchars($contenus['contact_facebook']['content_value_fr'] ?? ''); ?>" placeholder="https://web.facebook.com/...">
                </div>
                <?php translatable_field(
                    t('footer_adresse'),
                    'content[contact_adresse][fr]', $contenus['contact_adresse']['content_value_fr'] ?? '',
                    'content[contact_adresse][en]', $contenus['contact_adresse']['content_value_en'] ?? '',
                    'content[contact_adresse][mg]', $contenus['contact_adresse']['content_value_mg'] ?? '',
                    'text'
                ); ?>
                <?php translatable_field(
                    t('admin_contenu_contact_adresse_detail'),
                    'content[contact_adresse_detail][fr]', $contenus['contact_adresse_detail']['content_value_fr'] ?? '',
                    'content[contact_adresse_detail][en]', $contenus['contact_adresse_detail']['content_value_en'] ?? '',
                    'content[contact_adresse_detail][mg]', $contenus['contact_adresse_detail']['content_value_mg'] ?? '',
                    'text'
                ); ?>
            </fieldset>

            <!-- Catégorie : Campus (Blocs / associations régionales) -->
            <fieldset class="form-category" id="panel-campus">
                <legend><i class="fas fa-school"></i> <?php echo t('admin_contenu_legend_campus'); ?></legend>
                <p class="admin-panel-hint"><?php echo t('admin_contenu_hint_campus'); ?></p>

                <div class="admin-galerie-stats">
                    <div class="admin-stat-card"><i class="fas fa-school"></i><div><strong><?php echo $campus_blocs_count; ?></strong><span><?php echo t('admin_contenu_campus_total'); ?></span></div></div>
                </div>

                <a href="admin_campus.php" class="btn-add-item admin-new-album-btn" style="text-decoration:none;">
                    <i class="fas fa-arrow-up-right-from-square"></i> <?php echo t('admin_contenu_campus_gerer_btn'); ?>
                </a>
            </fieldset>

            <!-- Catégorie : Informations et dates de la page Inscription (frais, date limite, adresse, compte bancaire) -->
            <fieldset class="form-category" id="panel-inscription-infos">
                <legend><i class="fas fa-calendar-days"></i> <?php echo t('admin_contenu_nav_inscription_infos'); ?></legend>
                <p class="admin-panel-hint"><?php echo t('admin_inscription_infos_hint'); ?></p>

                <div class="form-group">
                    <label><?php echo t('admin_inscription_annee'); ?></label>
                    <input type="text" name="content[inscription_annee_universitaire][fr]" value="<?php echo htmlspecialchars($contenus['inscription_annee_universitaire']['content_value_fr'] ?? '2026'); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo t('admin_inscription_date_limite'); ?></label>
                    <input type="date" name="content[inscription_date_limite][fr]" value="<?php echo htmlspecialchars($contenus['inscription_date_limite']['content_value_fr'] ?? '2026-10-09'); ?>">
                </div>
                <?php translatable_field(
                    t('admin_inscription_adresse'),
                    'content[inscription_adresse_bloc][fr]', $contenus['inscription_adresse_bloc']['content_value_fr'] ?? "Mme le Chef de Service de la Scolarité Centrale\nUniversité de Mahajanga, BP 652, Mahajanga (401)\nTél : 034 44 889 86",
                    'content[inscription_adresse_bloc][en]', $contenus['inscription_adresse_bloc']['content_value_en'] ?? '',
                    'content[inscription_adresse_bloc][mg]', $contenus['inscription_adresse_bloc']['content_value_mg'] ?? '',
                    'textarea', 3
                ); ?>
                <div class="form-group">
                    <label><?php echo t('admin_inscription_compte'); ?></label>
                    <input type="text" name="content[inscription_compte_bancaire][fr]" value="<?php echo htmlspecialchars($contenus['inscription_compte_bancaire']['content_value_fr'] ?? '00650 05004012981-07'); ?>">
                </div>

                <?php
                $inscription_fee_groups = [
                    'nat' => ['title' => t('admin_inscription_frais_nationaux'), 'lic_droit' => '750 000 Ar', 'lic_v1' => '250 000 Ar', 'lic_v2' => '250 000 Ar', 'lic_v3' => '250 000 Ar', 'mas_droit' => '1 050 000 Ar', 'mas_v1' => '550 000 Ar', 'mas_v2' => '250 000 Ar', 'mas_v3' => '250 000 Ar', 'tenue' => '20 000 Ar'],
                    'etr' => ['title' => t('admin_inscription_frais_etrangers'), 'lic_droit' => '1 050 000 Ar', 'lic_v1' => '350 000 Ar', 'lic_v2' => '350 000 Ar', 'lic_v3' => '350 000 Ar', 'mas_droit' => '1 500 000 Ar', 'mas_v1' => '750 000 Ar', 'mas_v2' => '375 000 Ar', 'mas_v3' => '375 000 Ar', 'tenue' => '20 000 Ar'],
                ];
                foreach ($inscription_fee_groups as $fee_prefix => $fee_group):
                ?>
                    <h4 class="admin-subsection-title"><?php echo htmlspecialchars($fee_group['title']); ?></h4>
                    <div class="admin-fees-grid">
                        <div class="admin-fees-col">
                            <p class="admin-fees-col-title"><?php echo t('frais_licence'); ?></p>
                            <div class="form-group"><label><?php echo t('frais_droit_annuel'); ?></label><input type="text" name="content[frais_<?php echo $fee_prefix; ?>_lic_droit][fr]" value="<?php echo htmlspecialchars($contenus["frais_{$fee_prefix}_lic_droit"]['content_value_fr'] ?? $fee_group['lic_droit']); ?>"></div>
                            <div class="form-group"><label><?php echo t('frais_versement1'); ?></label><input type="text" name="content[frais_<?php echo $fee_prefix; ?>_lic_v1][fr]" value="<?php echo htmlspecialchars($contenus["frais_{$fee_prefix}_lic_v1"]['content_value_fr'] ?? $fee_group['lic_v1']); ?>"></div>
                            <div class="form-group"><label><?php echo t('frais_versement2'); ?></label><input type="text" name="content[frais_<?php echo $fee_prefix; ?>_lic_v2][fr]" value="<?php echo htmlspecialchars($contenus["frais_{$fee_prefix}_lic_v2"]['content_value_fr'] ?? $fee_group['lic_v2']); ?>"></div>
                            <div class="form-group"><label><?php echo t('frais_versement3'); ?></label><input type="text" name="content[frais_<?php echo $fee_prefix; ?>_lic_v3][fr]" value="<?php echo htmlspecialchars($contenus["frais_{$fee_prefix}_lic_v3"]['content_value_fr'] ?? $fee_group['lic_v3']); ?>"></div>
                        </div>
                        <div class="admin-fees-col">
                            <p class="admin-fees-col-title"><?php echo t('frais_master'); ?></p>
                            <div class="form-group"><label><?php echo t('frais_droit_annuel'); ?></label><input type="text" name="content[frais_<?php echo $fee_prefix; ?>_mas_droit][fr]" value="<?php echo htmlspecialchars($contenus["frais_{$fee_prefix}_mas_droit"]['content_value_fr'] ?? $fee_group['mas_droit']); ?>"></div>
                            <div class="form-group"><label><?php echo t('frais_versement1'); ?></label><input type="text" name="content[frais_<?php echo $fee_prefix; ?>_mas_v1][fr]" value="<?php echo htmlspecialchars($contenus["frais_{$fee_prefix}_mas_v1"]['content_value_fr'] ?? $fee_group['mas_v1']); ?>"></div>
                            <div class="form-group"><label><?php echo t('frais_versement2'); ?></label><input type="text" name="content[frais_<?php echo $fee_prefix; ?>_mas_v2][fr]" value="<?php echo htmlspecialchars($contenus["frais_{$fee_prefix}_mas_v2"]['content_value_fr'] ?? $fee_group['mas_v2']); ?>"></div>
                            <div class="form-group"><label><?php echo t('frais_versement3'); ?></label><input type="text" name="content[frais_<?php echo $fee_prefix; ?>_mas_v3][fr]" value="<?php echo htmlspecialchars($contenus["frais_{$fee_prefix}_mas_v3"]['content_value_fr'] ?? $fee_group['mas_v3']); ?>"></div>
                        </div>
                        <div class="admin-fees-col">
                            <p class="admin-fees-col-title"><?php echo t('frais_supplementaires'); ?></p>
                            <div class="form-group"><label><?php echo t('frais_tenue'); ?></label><input type="text" name="content[frais_<?php echo $fee_prefix; ?>_tenue][fr]" value="<?php echo htmlspecialchars($contenus["frais_{$fee_prefix}_tenue"]['content_value_fr'] ?? $fee_group['tenue']); ?>"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </fieldset>

            <!-- Catégorie : Bannière "Inscription en ligne" (bas de la page Inscription) -->
            <fieldset class="form-category" id="panel-inscription-cta">
                <legend><i class="fas fa-user-graduate"></i> <?php echo t('admin_contenu_nav_cta'); ?></legend>
                <p class="admin-panel-hint"><?php echo t('admin_cta_soustitre'); ?></p>
                <div class="form-group image-upload-group">
                    <label><?php echo t('admin_contenu_label_images_diaporama'); ?></label>
                    <div class="image-previews-grid">
                        <?php foreach ($cta_media as $m):
                            $m_is_video = $m['media_type'] === 'video';
                        ?>
                            <div class="image-preview-container<?php echo $m_is_video ? ' is-video' : ''; ?>">
                                <?php if ($m_is_video): ?>
                                    <video src="<?php echo htmlspecialchars($m['media_path']); ?>" muted></video>
                                    <span class="video-play-badge"><i class="fas fa-play"></i></span>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($m['media_path']); ?>" alt="">
                                <?php endif; ?>
                                <div class="delete-form">
                                    <button type="submit" name="delete_cta_media_id" value="<?php echo (int) $m['id']; ?>" formaction="admin_contenu.php?panel=panel-inscription-cta#panel-inscription-cta" formnovalidate class="btn-delete js-confirm-btn" title="<?php echo t('admin_supprimer'); ?>" data-confirm-msg="<?php echo htmlspecialchars(t('admin_galerie_confirm_delete_photo')); ?>"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($cta_media)): ?>
                            <p class="gallery-empty" style="grid-column:1/-1;"><i class="fas fa-photo-film"></i> <?php echo t('admin_cta_aucun'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="add-new-item">
                    <h4><?php echo t('admin_cta_ajouter'); ?></h4>
                    <div class="form-group upload-zone-group">
                        <label class="upload-zone upload-zone-multi">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <span class="upload-zone-text"><?php echo t('admin_galerie_multi_upload_text'); ?></span>
                            <span class="upload-zone-filename"></span>
                            <input type="file" name="new_cta_media[]" accept="image/*,video/*" class="upload-zone-input" multiple>
                        </label>
                        <p class="admin-field-hint"><?php echo t('admin_cta_upload_hint'); ?></p>
                    </div>
                </div>
            </fieldset>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> <?php echo t('admin_contenu_mettre_a_jour'); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Navigation par sections ---
    const navLinks = document.querySelectorAll('.admin-nav-link');
    const panels = document.querySelectorAll('.admin-panels .form-category');
    if (navLinks.length > 0 && panels.length > 0) {
        const showPanel = (targetId) => {
            panels.forEach(panel => panel.classList.toggle('is-active', panel.id === targetId));
            navLinks.forEach(link => link.classList.toggle('is-active', link.dataset.target === targetId));
        };

        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                showPanel(link.dataset.target);
                document.querySelector('.admin-panels').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        // Rouvre la section concernée après un rechargement (ex : filtre ou suppression dans la Galerie),
        // sinon affiche la première section par défaut.
        const params = new URLSearchParams(window.location.search);
        const requestedPanel = params.get('panel') || window.location.hash.replace('#', '');
        const targetExists = requestedPanel && document.getElementById(requestedPanel);
        showPanel(targetExists ? requestedPanel : navLinks[0].dataset.target);

        // Conserve la section active lors de l'enregistrement principal du formulaire.
        const mainForm = document.querySelector('form.admin-form');
        if (mainForm) {
            mainForm.addEventListener('submit', () => {
                const activePanel = document.querySelector('.admin-panels .form-category.is-active');
                if (activePanel) {
                    mainForm.action = 'admin_contenu.php?panel=' + activePanel.id + '#' + activePanel.id;
                }
            });
        }
    }

    // --- Contact : liste de numéros de téléphone dynamique (ajout/suppression), synchronisée
    // dans un champ caché unique (une valeur par ligne) avant l'envoi du formulaire. ---
    const phoneList = document.getElementById('contact-phone-list');
    const phoneHidden = document.getElementById('contact-telephone-hidden');
    const phoneAddBtn = document.getElementById('contact-phone-add');
    if (phoneList && phoneHidden && phoneAddBtn) {
        function syncContactPhones() {
            const values = Array.from(phoneList.querySelectorAll('.contact-phone-input'))
                .map(input => input.value.trim())
                .filter(v => v !== '');
            phoneHidden.value = values.join('\n');
        }
        function makePhoneRow(value) {
            const row = document.createElement('div');
            row.className = 'contact-phone-row';
            row.innerHTML = '<i class="fas fa-phone"></i><input type="tel" class="contact-phone-input" placeholder="+261 38 15 439 77"><button type="button" class="contact-phone-remove" title="Supprimer"><i class="fas fa-xmark"></i></button>';
            row.querySelector('.contact-phone-input').value = value || '';
            return row;
        }
        phoneAddBtn.addEventListener('click', () => {
            const row = makePhoneRow('');
            phoneList.appendChild(row);
            row.querySelector('.contact-phone-input').focus();
        });
        phoneList.addEventListener('click', (e) => {
            const btn = e.target.closest('.contact-phone-remove');
            if (!btn) return;
            // Toujours garder au moins une ligne (vide) pour que l'utilisateur puisse ressaisir un numéro.
            if (phoneList.querySelectorAll('.contact-phone-row').length > 1) {
                btn.closest('.contact-phone-row').remove();
            } else {
                btn.closest('.contact-phone-row').querySelector('.contact-phone-input').value = '';
            }
        });
        const mainFormPhones = document.querySelector('form.admin-form');
        if (mainFormPhones) {
            mainFormPhones.addEventListener('submit', syncContactPhones);
        }
    }

    // --- Onglets de langue + traduction automatique (MyMemory) ---
    document.querySelectorAll('.translatable-field-group').forEach(group => {
        const tabs = group.querySelectorAll('.lang-tab');
        const panelsByLang = {};
        group.querySelectorAll('.lang-panel').forEach(p => { panelsByLang[p.dataset.lang] = p; });

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.toggle('is-active', t === tab));
                Object.values(panelsByLang).forEach(p => p.classList.toggle('is-active', p.dataset.lang === tab.dataset.lang));
            });
        });

        const translateBtn = group.querySelector('.btn-translate-all');
        if (translateBtn) {
            translateBtn.addEventListener('click', async () => {
                const frInput = group.querySelector('[data-lang-input="fr"]');
                const enInput = group.querySelector('[data-lang-input="en"]');
                const mgInput = group.querySelector('[data-lang-input="mg"]');
                const sourceText = frInput ? frInput.value.trim() : '';
                if (!sourceText) {
                    frInput && frInput.focus();
                    return;
                }

                const originalHtml = translateBtn.innerHTML;
                translateBtn.disabled = true;
                translateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traduction...';

                const translateTo = async (targetLang) => {
                    const response = await fetch('translate_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ text: sourceText, source_lang: 'fr', target_lang: targetLang }),
                    });
                    const data = await response.json();
                    if (data.error) throw new Error(data.error);
                    return data.translatedText || '';
                };

                try {
                    const [enText, mgText] = await Promise.all([translateTo('en'), translateTo('mg')]);
                    if (enInput && enText) enInput.value = enText;
                    if (mgInput && mgText) mgInput.value = mgText;
                    translateBtn.innerHTML = '<i class="fas fa-check"></i> Traduit !';
                } catch (e) {
                    translateBtn.innerHTML = '<i class="fas fa-triangle-exclamation"></i> Échec';
                } finally {
                    setTimeout(() => {
                        translateBtn.disabled = false;
                        translateBtn.innerHTML = originalHtml;
                    }, 1800);
                }
            });
        }
    });

    // --- Zones de dépôt de fichier stylées ---
    document.querySelectorAll('.upload-zone-input').forEach(input => {
        const zone = input.closest('.upload-zone');
        const filenameEl = zone.querySelector('.upload-zone-filename');
        input.addEventListener('change', () => {
            const hasFile = input.files && input.files.length > 0;
            zone.classList.toggle('has-file', hasFile);
            filenameEl.textContent = hasFile ? input.files[0].name : '';
        });
    });

});
</script>

<?php include 'footer.php'; ?>