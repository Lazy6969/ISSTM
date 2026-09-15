<?php
include_once 'language.php';
require_once 'db_connect.php';

$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';
if (!$is_admin) {
    header('Location: index.php');
    exit;
}

$upload_dir = 'uploads/';
$allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
$niveaux_options = ['L1', 'L2', 'L3', 'M1', 'M2'];
$flash = null;

function res_unlink_if_upload($path) {
    if ($path && file_exists($path) && strpos($path, 'uploads/') === 0) {
        unlink($path);
    }
}

// Enregistre un lot de fichiers uploadés (issu de n'importe quel champ new_images[]-like) comme
// images d'une publication, à la suite des images déjà en place. Partagé entre la création d'une
// publication (upload dès la première étape) et l'ajout d'images à une publication existante.
function res_save_uploaded_images(mysqli $mysqli, string $upload_dir, array $allowed_exts, int $publication_id, array $files): int {
    if (empty($files['name']) || !is_array($files['name'])) { return 0; }
    $maxOrderRow = $mysqli->query("SELECT COALESCE(MAX(display_order),0) AS m FROM resultat_images WHERE publication_id = $publication_id")->fetch_assoc();
    $order = (int) $maxOrderRow['m'];
    $stmt = $mysqli->prepare("INSERT INTO resultat_images (publication_id, image_path, display_order) VALUES (?,?,?)");
    $count = 0;
    foreach ($files['name'] as $key => $name) {
        if ($files['error'][$key] != 0) continue;
        $fext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($fext, $allowed_exts, true)) continue;
        $new_name = 'resultat_' . uniqid() . '_' . $key . '.' . $fext;
        if (move_uploaded_file($files['tmp_name'][$key], $upload_dir . $new_name)) {
            $order++;
            $path = $upload_dir . $new_name;
            $stmt->bind_param("isi", $publication_id, $path, $order);
            $stmt->execute();
            $count++;
        }
    }
    $stmt->close();
    return $count;
}

// --- Traitement des actions POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Créer / mettre à jour une publication ---
    if (isset($_POST['save_publication'])) {
        $publication_id = isset($_POST['publication_id']) && ctype_digit($_POST['publication_id']) ? (int) $_POST['publication_id'] : 0;
        $filiere_id = ctype_digit($_POST['filiere_id'] ?? '') ? (int) $_POST['filiere_id'] : 0;
        $niveau     = in_array($_POST['niveau'] ?? '', $niveaux_options, true) ? $_POST['niveau'] : null;
        $titre      = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $statut     = ($_POST['statut'] ?? 'publie') === 'brouillon' ? 'brouillon' : 'publie';

        if ($filiere_id <= 0 || $titre === '') {
            $flash = ['type' => 'error', 'msg' => t('admin_resultats_erreur_champs_requis')];
            header("Location: admin_resultats.php?view=edit" . ($publication_id > 0 ? "&id=$publication_id" : '') . "&flash=" . urlencode($flash['type'] . '|' . $flash['msg']));
            exit;
        }

        if ($publication_id > 0) {
            $stmt = $mysqli->prepare("UPDATE resultat_publications SET filiere_id=?, niveau=?, titre=?, description=?, statut=?, published_at = IF(? = 'publie' AND published_at IS NULL, NOW(), published_at) WHERE id=?");
            $stmt->bind_param("isssssi", $filiere_id, $niveau, $titre, $description, $statut, $statut, $publication_id);
            $stmt->execute();
            $stmt->close();
            $flash = ['type' => 'success', 'msg' => t('admin_resultats_publication_mise_a_jour')];
        } else {
            $publie_par = (int) $_SESSION['user_id'];
            $stmt = $mysqli->prepare("INSERT INTO resultat_publications (filiere_id, niveau, titre, description, statut, published_at, publie_par) VALUES (?,?,?,?,?, IF(?='publie', NOW(), NULL), ?)");
            $stmt->bind_param("isssssi", $filiere_id, $niveau, $titre, $description, $statut, $statut, $publie_par);
            $stmt->execute();
            $publication_id = $mysqli->insert_id;
            $stmt->close();
            $flash = ['type' => 'success', 'msg' => t('admin_resultats_publication_creee')];
        }

        // Images sélectionnées dès l'étape de création/modification (en plus de la zone d'ajout
        // dédiée plus bas, qui reste disponible pour compléter une publication déjà enregistrée).
        if (!empty($_FILES['initial_images']['name'][0])) {
            $added = res_save_uploaded_images($mysqli, $upload_dir, $allowed_exts, $publication_id, $_FILES['initial_images']);
            if ($added > 0) {
                $flash['msg'] .= ' ' . sprintf(t('admin_resultats_images_ajoutees'), $added);
            }
        }

        header("Location: admin_resultats.php?view=edit&id=$publication_id&flash=" . urlencode($flash['type'] . '|' . $flash['msg']));
        exit;
    }

    // --- Supprimer une publication ---
    if (isset($_POST['delete_publication_id'])) {
        $publication_id = (int) $_POST['delete_publication_id'];
        $imgs = $mysqli->query("SELECT image_path FROM resultat_images WHERE publication_id = $publication_id");
        while ($img = $imgs->fetch_assoc()) { res_unlink_if_upload($img['image_path']); }
        $stmt = $mysqli->prepare("DELETE FROM resultat_publications WHERE id = ?");
        $stmt->bind_param("i", $publication_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_resultats.php?flash=" . urlencode('success|' . t('admin_resultats_publication_supprimee')));
        exit;
    }

    // --- Publier / dépublier une publication ---
    if (isset($_POST['toggle_status_id'])) {
        $publication_id = (int) $_POST['toggle_status_id'];
        $mysqli->query("UPDATE resultat_publications SET statut = IF(statut='publie','brouillon','publie'), published_at = IF(statut='brouillon' AND published_at IS NULL, NOW(), published_at) WHERE id = $publication_id");
        $redirect = isset($_POST['redirect_edit']) ? "admin_resultats.php?view=edit&id=$publication_id" : "admin_resultats.php";
        header("Location: $redirect");
        exit;
    }

    // --- Ajouter des images à une publication (upload multiple, tout format d'image) ---
    if (isset($_POST['upload_images']) && isset($_FILES['new_images'])) {
        $publication_id = (int) $_POST['publication_id'];
        $count_images = res_save_uploaded_images($mysqli, $upload_dir, $allowed_exts, $publication_id, $_FILES['new_images']);
        header("Location: admin_resultats.php?view=edit&id=$publication_id&flash=" . urlencode('success|' . sprintf(t('admin_resultats_images_ajoutees'), $count_images)));
        exit;
    }

    // --- Supprimer une image ---
    if (isset($_POST['delete_image_id'])) {
        $image_id = (int) $_POST['delete_image_id'];
        $publication_id = (int) $_POST['publication_id'];
        $img = $mysqli->query("SELECT image_path FROM resultat_images WHERE id = $image_id")->fetch_assoc();
        if ($img) { res_unlink_if_upload($img['image_path']); }
        $stmt = $mysqli->prepare("DELETE FROM resultat_images WHERE id = ?");
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_resultats.php?view=edit&id=$publication_id&flash=" . urlencode('success|' . t('admin_resultats_image_supprimee')));
        exit;
    }
}

// --- Message flash (après redirection) ---
if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

// --- Filières (pour les selects/filtres) ---
$all_filieres = $mysqli->query("SELECT id, nom_fr, niveaux FROM filieres ORDER BY display_order ASC, nom_fr ASC")->fetch_all(MYSQLI_ASSOC);

$view = $_GET['view'] ?? 'list';

$page_title = t('admin_resultats_breadcrumb');

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
            <span><?php echo t('admin_resultats_breadcrumb'); ?></span>
        </nav>
        <h1><?php echo t('admin_resultats_breadcrumb'); ?></h1>
        <p><?php echo t('admin_resultats_soustitre'); ?></p>
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
            $publication_id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;
            $publication = null;
            if ($publication_id > 0) {
                $stmt = $mysqli->prepare("SELECT * FROM resultat_publications WHERE id = ?");
                $stmt->bind_param("i", $publication_id);
                $stmt->execute();
                $publication = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }
            $images = [];
            if ($publication_id > 0) {
                $stmt = $mysqli->prepare("SELECT * FROM resultat_images WHERE publication_id = ? ORDER BY display_order ASC, id ASC");
                $stmt->bind_param("i", $publication_id);
                $stmt->execute();
                $images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
        ?>
            <a href="admin_resultats.php" class="admin-galerie-back"><i class="fas fa-chevron-left"></i> <?php echo t('admin_resultats_retour_liste'); ?></a>
            <h2 class="admin-section-title"><?php echo $publication ? t('admin_resultats_modifier_publication') : t('admin_resultats_nouvelle_publication'); ?></h2>

            <form action="admin_resultats.php" method="POST" enctype="multipart/form-data" class="admin-form">
                <?php if ($publication): ?><input type="hidden" name="publication_id" value="<?php echo (int) $publication['id']; ?>"><?php endif; ?>

                <div class="album-form-row">
                    <div class="form-group">
                        <label><?php echo t('admin_resultats_filiere_label'); ?></label>
                        <select name="filiere_id" required>
                            <option value=""><?php echo t('admin_aucune_option'); ?></option>
                            <?php foreach ($all_filieres as $f): ?>
                                <option value="<?php echo $f['id']; ?>" <?php echo (isset($publication['filiere_id']) && (int) $publication['filiere_id'] === (int) $f['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['nom_fr']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('admin_resultats_niveau_label'); ?></label>
                        <select name="niveau">
                            <option value=""><?php echo t('admin_resultats_niveau_toutes'); ?></option>
                            <?php foreach ($niveaux_options as $n): ?>
                                <option value="<?php echo $n; ?>" <?php echo (($publication['niveau'] ?? '') === $n) ? 'selected' : ''; ?>><?php echo $n; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label><?php echo t('admin_resultats_titre_label'); ?></label>
                    <input type="text" name="titre" value="<?php echo htmlspecialchars($publication['titre'] ?? ''); ?>" placeholder="<?php echo t('admin_resultats_titre_placeholder'); ?>" required>
                </div>

                <div class="form-group">
                    <label><?php echo t('admin_description_label'); ?></label>
                    <textarea name="description" rows="4" placeholder="<?php echo t('admin_resultats_description_placeholder'); ?>"><?php echo htmlspecialchars($publication['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label><?php echo t('admin_statut_label'); ?></label>
                    <select name="statut">
                        <option value="brouillon" <?php echo (($publication['statut'] ?? 'brouillon') === 'brouillon') ? 'selected' : ''; ?>><?php echo t('admin_statut_brouillon'); ?></option>
                        <option value="publie" <?php echo (($publication['statut'] ?? '') === 'publie') ? 'selected' : ''; ?>><?php echo t('admin_statut_publie'); ?></option>
                    </select>
                </div>

                <?php if (!$publication): ?>
                    <div class="form-group upload-zone-group">
                        <label><?php echo t('admin_resultats_images_publication'); ?></label>
                        <label class="upload-zone upload-zone-multi" id="initial-upload-zone">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <span class="upload-zone-text"><?php echo t('admin_resultats_multi_upload_text'); ?></span>
                            <span class="upload-zone-filename" id="initial-upload-filenames"></span>
                            <input type="file" name="initial_images[]" accept="image/*" class="upload-zone-input" id="initial-upload-input" multiple>
                        </label>
                        <p class="admin-field-hint"><?php echo t('admin_resultats_multi_upload_hint'); ?></p>
                    </div>
                <?php endif; ?>

                <button type="submit" name="save_publication" class="btn-submit"><i class="fas fa-save"></i> <?php echo $publication ? t('admin_enregistrer_modifications') : t('admin_resultats_creer_publication'); ?></button>
            </form>

            <?php if ($publication): ?>
                <div class="admin-photos-section">
                    <h2 class="admin-section-title"><i class="fas fa-images"></i> <?php echo t('admin_resultats_images_publication'); ?> (<?php echo count($images); ?>)</h2>

                    <form action="admin_resultats.php" method="POST" enctype="multipart/form-data" class="admin-upload-photos-form">
                        <input type="hidden" name="publication_id" value="<?php echo (int) $publication['id']; ?>">
                        <label class="upload-zone upload-zone-multi" id="multi-upload-zone">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <span class="upload-zone-text"><?php echo t('admin_resultats_multi_upload_text'); ?></span>
                            <span class="upload-zone-filename" id="multi-upload-filenames"></span>
                            <input type="file" name="new_images[]" accept="image/*" class="upload-zone-input" id="multi-upload-input" multiple>
                        </label>
                        <p class="admin-field-hint"><?php echo t('admin_resultats_multi_upload_hint'); ?></p>
                        <button type="submit" name="upload_images" class="btn-add-item"><i class="fas fa-upload"></i> <?php echo t('admin_resultats_ajouter_images'); ?></button>
                    </form>

                    <?php if (empty($images)): ?>
                        <p class="gallery-empty"><i class="fas fa-image"></i> <?php echo t('admin_resultats_aucune_image'); ?></p>
                    <?php else: ?>
                        <div class="photo-admin-grid">
                            <?php foreach ($images as $image): ?>
                                <div class="photo-admin-item" id="image-<?php echo $image['id']; ?>">
                                    <div class="photo-admin-thumb">
                                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="">
                                        <form action="admin_resultats.php?view=edit&id=<?php echo $publication_id; ?>" method="POST" class="delete-form js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_resultats_confirm_delete_image')); ?>">
                                            <input type="hidden" name="publication_id" value="<?php echo $publication_id; ?>">
                                            <button type="submit" name="delete_image_id" value="<?php echo $image['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else:
            // --- Vue liste ---
            $f_statut = $_GET['status'] ?? '';
            $f_filiere = isset($_GET['ffiliere']) && ctype_digit($_GET['ffiliere']) ? (int) $_GET['ffiliere'] : 0;
            $f_q = trim($_GET['fq'] ?? '');

            $where = ['1=1'];
            $params = [];
            $types = '';
            if ($f_statut === 'publie' || $f_statut === 'brouillon') {
                $where[] = 'p.statut = ?';
                $params[] = $f_statut;
                $types .= 's';
            }
            if ($f_filiere > 0) {
                $where[] = 'p.filiere_id = ?';
                $params[] = $f_filiere;
                $types .= 'i';
            }
            if ($f_q !== '') {
                $where[] = 'p.titre LIKE ?';
                $like = '%' . $f_q . '%';
                $params[] = $like;
                $types .= 's';
            }
            $where_sql = implode(' AND ', $where);
            $sql = "SELECT p.*, f.nom_fr AS filiere_nom,
                        (SELECT COUNT(*) FROM resultat_images i WHERE i.publication_id = p.id) AS image_count,
                        (SELECT i.image_path FROM resultat_images i WHERE i.publication_id = p.id ORDER BY i.display_order ASC, i.id ASC LIMIT 1) AS first_image
                    FROM resultat_publications p LEFT JOIN filieres f ON f.id = p.filiere_id
                    WHERE $where_sql ORDER BY p.created_at DESC";
            $stmt = $mysqli->prepare($sql);
            if ($types !== '') { $stmt->bind_param($types, ...$params); }
            $stmt->execute();
            $all_publications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $stats = $mysqli->query("SELECT
                (SELECT COUNT(*) FROM resultat_publications) AS total_publications,
                (SELECT COUNT(*) FROM resultat_publications WHERE statut='publie') AS publiees,
                (SELECT COUNT(*) FROM resultat_images) AS total_images
            ")->fetch_assoc();
        ?>

            <div class="admin-galerie-stats">
                <div class="admin-stat-card"><i class="fas fa-file-circle-check"></i><div><strong><?php echo (int) $stats['total_publications']; ?></strong><span><?php echo t('admin_resultats_publications_total'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-eye"></i><div><strong><?php echo (int) $stats['publiees']; ?></strong><span><?php echo t('admin_resultats_publications_publiees'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-image"></i><div><strong><?php echo (int) $stats['total_images']; ?></strong><span><?php echo t('admin_resultats_images_total'); ?></span></div></div>
            </div>

            <div class="admin-galerie-toolbar">
                <a href="admin_resultats.php?view=edit" class="btn-add-item admin-new-album-btn"><i class="fas fa-plus"></i> <?php echo t('admin_resultats_nouvelle_publication'); ?></a>
                <form method="GET" action="admin_resultats.php" class="admin-galerie-filters">
                    <input type="hidden" name="view" value="list">
                    <input type="text" name="fq" value="<?php echo htmlspecialchars($f_q); ?>" placeholder="<?php echo t('admin_resultats_rechercher'); ?>">
                    <select name="ffiliere">
                        <option value="0"><?php echo t('admin_resultats_toutes_filieres'); ?></option>
                        <?php foreach ($all_filieres as $f): ?>
                            <option value="<?php echo $f['id']; ?>" <?php echo $f_filiere === (int) $f['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['nom_fr']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status">
                        <option value=""><?php echo t('admin_tous_statuts'); ?></option>
                        <option value="publie" <?php echo $f_statut === 'publie' ? 'selected' : ''; ?>><?php echo t('admin_statut_publie'); ?></option>
                        <option value="brouillon" <?php echo $f_statut === 'brouillon' ? 'selected' : ''; ?>><?php echo t('admin_statut_brouillon'); ?></option>
                    </select>
                    <button type="submit" class="btn-filter-gallery"><i class="fas fa-filter"></i></button>
                </form>
            </div>

            <?php if (empty($all_publications)): ?>
                <p class="gallery-empty"><i class="fas fa-file-circle-check"></i> <?php echo t('admin_resultats_aucune_publication'); ?></p>
            <?php else: ?>
                <div class="admin-album-list">
                    <?php foreach ($all_publications as $pub): ?>
                        <div class="admin-album-row">
                            <img src="<?php echo htmlspecialchars($pub['first_image'] ?: 'images/logo-isstm.jpg'); ?>" alt="" class="admin-album-row-thumb<?php echo $pub['first_image'] ? '' : ' admin-album-row-thumb-placeholder'; ?>">
                            <div class="admin-album-row-info">
                                <h4><?php echo htmlspecialchars($pub['titre']); ?></h4>
                                <div class="admin-album-row-meta">
                                    <span><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($pub['filiere_nom'] ?? '?'); ?></span>
                                    <?php if ($pub['niveau']): ?><span><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($pub['niveau']); ?></span><?php endif; ?>
                                    <span><i class="fas fa-images"></i> <?php echo (int) $pub['image_count']; ?> <?php echo t('admin_galerie_photo_suffix'); ?></span>
                                </div>
                            </div>
                            <span class="admin-status-badge admin-status-<?php echo $pub['statut']; ?>"><?php echo $pub['statut'] === 'publie' ? t('admin_statut_publie') : t('admin_statut_brouillon'); ?></span>
                            <div class="admin-album-row-actions">
                                <a href="admin_resultats.php?view=edit&id=<?php echo $pub['id']; ?>" class="btn-outline" title="<?php echo t('admin_modifier'); ?>"><i class="fas fa-pen"></i></a>
                                <form action="admin_resultats.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="toggle_status_id" value="<?php echo $pub['id']; ?>">
                                    <button type="submit" class="btn-outline" title="<?php echo $pub['statut'] === 'publie' ? t('admin_galerie_depublier') : t('admin_galerie_publier'); ?>">
                                        <i class="fas <?php echo $pub['statut'] === 'publie' ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                    </button>
                                </form>
                                <form action="admin_resultats.php" method="POST" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_resultats_confirm_delete_publication')); ?>">
                                    <button type="submit" name="delete_publication_id" value="<?php echo $pub['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
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
});
</script>

<?php include 'footer.php'; ?>
