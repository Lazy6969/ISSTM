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

function camp_slugify($text) {
    $text = trim((string) $text);
    $translit = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    if ($translit !== false) { $text = $translit; }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

// --- Traitement des actions POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Créer / mettre à jour un bloc ---
    if (isset($_POST['save_bloc'])) {
        $bloc_id       = isset($_POST['bloc_id']) && ctype_digit($_POST['bloc_id']) ? (int) $_POST['bloc_id'] : 0;
        $bloc_key      = camp_slugify($_POST['bloc_key'] ?? '');
        $nom           = trim($_POST['nom'] ?? '');
        $signification = trim($_POST['signification'] ?? '');
        $fondation     = trim($_POST['fondation'] ?? '');
        $fondateurs    = trim($_POST['fondateurs'] ?? '');
        $slogan_in     = trim($_POST['slogan'] ?? '');
        $slogan        = $slogan_in !== '' ? $slogan_in : null;
        $objectifs     = trim($_POST['objectifs'] ?? '');
        $activites     = trim($_POST['activites'] ?? '');
        $danse_in      = trim($_POST['danse'] ?? '');
        $danse         = $danse_in !== '' ? $danse_in : null;
        $mampiavaka_in = trim($_POST['mampiavaka'] ?? '');
        $mampiavaka    = $mampiavaka_in !== '' ? $mampiavaka_in : null;

        $redirect_view = $bloc_id > 0 ? "view=edit&id=$bloc_id" : "view=edit";

        if ($nom === '') {
            header("Location: admin_campus.php?$redirect_view&flash=" . urlencode('error|' . t('admin_campus_error_nom_requis')));
            exit;
        }
        if ($bloc_key === '') {
            header("Location: admin_campus.php?$redirect_view&flash=" . urlencode('error|' . t('admin_campus_error_bloc_key_requis')));
            exit;
        }

        // Vérification préalable de l'unicité de bloc_key (hors soi-même)
        $check = $mysqli->prepare("SELECT id FROM campus_blocs WHERE bloc_key = ? AND id != ?");
        $notId = $bloc_id ?: 0;
        $check->bind_param("si", $bloc_key, $notId);
        $check->execute();
        $key_taken = $check->get_result()->num_rows > 0;
        $check->close();

        if ($key_taken) {
            header("Location: admin_campus.php?$redirect_view&flash=" . urlencode('error|' . t('admin_campus_error_bloc_key_duplique')));
            exit;
        }

        // --- Images conservées (existantes moins celles cochées "retirer") ---
        $existing_images = $_POST['existing_images'] ?? [];
        $remove_images   = $_POST['remove_images'] ?? [];
        $kept_images     = array_values(array_diff($existing_images, $remove_images));

        // --- Nouvelles images uploadées ---
        $new_paths = [];
        if (isset($_FILES['new_images']) && is_array($_FILES['new_images']['name'])) {
            foreach ($_FILES['new_images']['name'] as $key => $name) {
                if ($_FILES['new_images']['error'][$key] != 0) continue;
                if (!in_array($_FILES['new_images']['type'][$key], $allowed_types)) continue;
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $new_name = 'campus_' . uniqid() . '_' . $key . '.' . $ext;
                if (move_uploaded_file($_FILES['new_images']['tmp_name'][$key], $upload_dir . $new_name)) {
                    $new_paths[] = $upload_dir . $new_name;
                }
            }
        }

        $final_images = implode(',', array_merge($kept_images, $new_paths));

        if ($bloc_id > 0) {
            $stmt = $mysqli->prepare("UPDATE campus_blocs SET bloc_key=?, nom=?, signification=?, fondation=?, fondateurs=?, slogan=?, objectifs=?, activites=?, danse=?, mampiavaka=?, images=? WHERE id=?");
            $stmt->bind_param("sssssssssssi", $bloc_key, $nom, $signification, $fondation, $fondateurs, $slogan, $objectifs, $activites, $danse, $mampiavaka, $final_images, $bloc_id);
            $stmt->execute();
            $stmt->close();
            $flash = 'success|' . t('admin_campus_updated');
        } else {
            $stmt = $mysqli->prepare("INSERT INTO campus_blocs (bloc_key, nom, signification, fondation, fondateurs, slogan, objectifs, activites, danse, mampiavaka, images) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssssssss", $bloc_key, $nom, $signification, $fondation, $fondateurs, $slogan, $objectifs, $activites, $danse, $mampiavaka, $final_images);
            $stmt->execute();
            $bloc_id = $mysqli->insert_id;
            $stmt->close();
            $flash = 'success|' . t('admin_campus_created');
        }

        header("Location: admin_campus.php?view=edit&id=$bloc_id&flash=" . urlencode($flash));
        exit;
    }

    // --- Supprimer un bloc ---
    if (isset($_POST['delete_bloc_id'])) {
        $bloc_id = (int) $_POST['delete_bloc_id'];
        $stmt = $mysqli->prepare("DELETE FROM campus_blocs WHERE id = ?");
        $stmt->bind_param("i", $bloc_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_campus.php?flash=" . urlencode('success|' . t('admin_campus_deleted')));
        exit;
    }
}

// --- Message flash (après redirection) ---
if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
} elseif (is_string($flash)) {
    [$ftype, $fmsg] = array_pad(explode('|', $flash, 2), 2, '');
    $flash = ['type' => $ftype, 'msg' => $fmsg];
}

$view = $_GET['view'] ?? 'list';

$page_title = t('admin_campus_breadcrumb');

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
            <span><?php echo t('admin_campus_breadcrumb'); ?></span>
        </nav>
        <h1><?php echo t('admin_campus_breadcrumb'); ?></h1>
        <p><?php echo t('admin_campus_soustitre'); ?></p>
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
            $bloc_id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;
            $bloc = null;
            if ($bloc_id > 0) {
                $stmt = $mysqli->prepare("SELECT * FROM campus_blocs WHERE id = ?");
                $stmt->bind_param("i", $bloc_id);
                $stmt->execute();
                $bloc = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }
            $bloc_images = ($bloc && !empty($bloc['images'])) ? array_values(array_filter(explode(',', $bloc['images']), fn($v) => trim($v) !== '')) : [];
        ?>
            <a href="admin_campus.php" class="admin-galerie-back"><i class="fas fa-chevron-left"></i> <?php echo t('admin_campus_retour_liste'); ?></a>
            <h2 class="admin-section-title"><?php echo $bloc ? t('admin_campus_modifier_bloc') : t('admin_campus_nouveau_bloc'); ?></h2>

            <form action="admin_campus.php" method="POST" enctype="multipart/form-data" class="admin-form admin-album-form">
                <?php if ($bloc): ?><input type="hidden" name="bloc_id" value="<?php echo (int) $bloc['id']; ?>"><?php endif; ?>

                <div class="album-form-grid">
                    <div class="album-form-main">
                        <div class="form-group">
                            <label><?php echo t('admin_campus_nom_label'); ?></label>
                            <input type="text" name="nom" id="campus-nom" value="<?php echo htmlspecialchars($bloc['nom'] ?? ''); ?>" placeholder="<?php echo t('admin_campus_nom_placeholder'); ?>" required>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_campus_bloc_key_label'); ?> <span class="admin-field-hint"><?php echo t('admin_campus_bloc_key_hint'); ?></span></label>
                            <input type="text" name="bloc_key" id="campus-bloc-key" value="<?php echo htmlspecialchars($bloc['bloc_key'] ?? ''); ?>" placeholder="ex-mafami">
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_campus_signification_label'); ?></label>
                            <textarea name="signification" rows="3"><?php echo htmlspecialchars($bloc['signification'] ?? ''); ?></textarea>
                        </div>

                        <div class="album-form-row">
                            <div class="form-group">
                                <label><?php echo t('admin_campus_fondation_label'); ?></label>
                                <input type="text" name="fondation" value="<?php echo htmlspecialchars($bloc['fondation'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label><?php echo t('admin_campus_slogan_label'); ?></label>
                                <input type="text" name="slogan" value="<?php echo htmlspecialchars($bloc['slogan'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_campus_fondateurs_label'); ?></label>
                            <input type="text" name="fondateurs" value="<?php echo htmlspecialchars($bloc['fondateurs'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_campus_objectifs_label'); ?> <span class="admin-field-hint"><?php echo t('admin_campus_objectifs_hint'); ?></span></label>
                            <textarea name="objectifs" rows="5"><?php echo htmlspecialchars($bloc['objectifs'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_campus_activites_label'); ?> <span class="admin-field-hint"><?php echo t('admin_campus_activites_hint'); ?></span></label>
                            <textarea name="activites" rows="5"><?php echo htmlspecialchars($bloc['activites'] ?? ''); ?></textarea>
                        </div>

                        <div class="album-form-row">
                            <div class="form-group">
                                <label><?php echo t('admin_campus_danse_label'); ?></label>
                                <input type="text" name="danse" value="<?php echo htmlspecialchars($bloc['danse'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label><?php echo t('admin_campus_mampiavaka_label'); ?></label>
                                <input type="text" name="mampiavaka" value="<?php echo htmlspecialchars($bloc['mampiavaka'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="album-form-side">
                        <div class="form-group image-upload-group">
                            <label><?php echo t('admin_campus_images_actuelles_label'); ?></label>
                            <?php if (empty($bloc_images)): ?>
                                <p class="gallery-empty" style="padding: 15px;"><i class="fas fa-image"></i> <?php echo t('admin_campus_aucune_image'); ?></p>
                            <?php else: ?>
                                <div class="image-previews-grid">
                                    <?php foreach ($bloc_images as $img_path): ?>
                                        <div class="image-preview-container">
                                            <img src="<?php echo htmlspecialchars($img_path); ?>" alt="">
                                            <input type="hidden" name="existing_images[]" value="<?php echo htmlspecialchars($img_path); ?>">
                                            <label class="delete-form" title="<?php echo t('admin_campus_retirer_image'); ?>">
                                                <input type="checkbox" name="remove_images[]" value="<?php echo htmlspecialchars($img_path); ?>" class="campus-remove-checkbox" style="display:none;">
                                                <span class="btn-delete"><i class="fas fa-trash-alt"></i></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group upload-zone-group">
                            <label><?php echo t('admin_campus_ajouter_images_label'); ?></label>
                            <label class="upload-zone upload-zone-multi">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <span class="upload-zone-text"><?php echo t('admin_choisir_image_deposer'); ?></span>
                                <span class="upload-zone-filename"></span>
                                <input type="file" name="new_images[]" accept="image/jpeg, image/png, image/jpg, image/webp" class="upload-zone-input" multiple>
                            </label>
                        </div>

                        <?php if ($bloc): ?>
                            <a href="campus.php" target="_blank" rel="noopener" class="btn-outline" style="display:block; text-align:center; margin-top: 10px;">
                                <i class="fas fa-arrow-up-right-from-square"></i> <?php echo t('admin_campus_voir_page_publique'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" name="save_bloc" class="btn-submit"><i class="fas fa-save"></i> <?php echo $bloc ? t('admin_enregistrer_modifications') : t('admin_campus_creer_bloc'); ?></button>
            </form>

        <?php else:
            // --- Vue liste ---
            $all_blocs = $mysqli->query("SELECT * FROM campus_blocs ORDER BY nom ASC")->fetch_all(MYSQLI_ASSOC);
            $total_blocs = count($all_blocs);
        ?>

            <div class="admin-galerie-stats">
                <div class="admin-stat-card"><i class="fas fa-school"></i><div><strong><?php echo $total_blocs; ?></strong><span><?php echo t('admin_campus_total'); ?></span></div></div>
            </div>

            <div class="admin-galerie-toolbar">
                <a href="admin_campus.php?view=edit" class="btn-add-item admin-new-album-btn"><i class="fas fa-plus"></i> <?php echo t('admin_campus_nouveau_bloc'); ?></a>
            </div>

            <?php if (empty($all_blocs)): ?>
                <p class="gallery-empty"><i class="fas fa-school"></i> <?php echo t('admin_campus_aucun_bloc'); ?></p>
            <?php else: ?>
                <div class="admin-album-list">
                    <?php foreach ($all_blocs as $b):
                        $b_images = !empty($b['images']) ? array_values(array_filter(explode(',', $b['images']), fn($v) => trim($v) !== '')) : [];
                        $b_thumb = $b_images[0] ?? null;
                    ?>
                        <div class="admin-album-row">
                            <?php if ($b_thumb): ?>
                                <img src="<?php echo htmlspecialchars($b_thumb); ?>" alt="" class="admin-album-row-thumb">
                            <?php else: ?>
                                <div class="admin-album-row-thumb admin-filiere-thumb-placeholder"><i class="fas fa-school"></i></div>
                            <?php endif; ?>
                            <div class="admin-album-row-info">
                                <h4><?php echo htmlspecialchars($b['nom']); ?></h4>
                                <div class="admin-album-row-meta">
                                    <span><i class="fas fa-key"></i> <?php echo htmlspecialchars($b['bloc_key']); ?></span>
                                    <span><i class="fas fa-images"></i> <?php echo count($b_images); ?></span>
                                </div>
                            </div>
                            <div class="admin-album-row-actions">
                                <a href="admin_campus.php?view=edit&id=<?php echo $b['id']; ?>" class="btn-outline" title="<?php echo t('admin_modifier'); ?>"><i class="fas fa-pen"></i></a>
                                <form action="admin_campus.php" method="POST" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_campus_confirm_delete')); ?>">
                                    <button type="submit" name="delete_bloc_id" value="<?php echo $b['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
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

    // Génération automatique de bloc_key à partir du nom (uniquement si le champ est vide)
    const nomInput = document.getElementById('campus-nom');
    const keyInput = document.getElementById('campus-bloc-key');
    if (nomInput && keyInput) {
        nomInput.addEventListener('blur', () => {
            if (keyInput.value.trim() === '' && nomInput.value.trim() !== '') {
                keyInput.value = nomInput.value.trim()
                    .toLowerCase()
                    .normalize('NFD').replace(/[̀-ͯ]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
            }
        });
    }

    // Grise visuellement une image marquée pour suppression
    document.querySelectorAll('.campus-remove-checkbox').forEach(cb => {
        cb.addEventListener('change', () => {
            const container = cb.closest('.image-preview-container');
            container.style.opacity = cb.checked ? '0.35' : '1';
        });
    });
});
</script>

<?php include 'footer.php'; ?>
