<?php
include_once 'language.php';
require_once 'db_connect.php';

$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';
if (!$is_admin) {
    header('Location: index.php');
    exit;
}

$upload_dir = 'images/teachers/';
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
$flash = null;

function ens_slug($nom) {
    $accents = ['à'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','î'=>'i','ï'=>'i','ô'=>'o','ö'=>'o','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c'];
    $slug = mb_strtolower(strtr($nom, $accents), 'UTF-8');
    $slug = preg_replace('/[^a-z0-9]+/', '.', $slug);
    return trim($slug, '.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Créer ou mettre à jour un enseignant ---
    if (isset($_POST['save_teacher'])) {
        $teacher_id  = isset($_POST['teacher_id']) && ctype_digit($_POST['teacher_id']) ? (int) $_POST['teacher_id'] : 0;
        $nom         = trim($_POST['nom'] ?? '');
        $categorie   = ($_POST['categorie'] ?? '') === 'vacataire' ? 'vacataire' : 'permanent';
        $specialite  = trim($_POST['specialite'] ?? '');
        $email_in    = trim($_POST['email'] ?? '');
        $email       = $email_in !== '' ? $email_in : (ens_slug($nom) . '@isstm.mg');
        $statut      = $categorie === 'permanent' ? 'permanent(e)' : 'vacataire';
        $description = "Enseignant(e) $statut spécialisé(e) en $specialite, au service de la réussite des étudiants de l'ISSTM.";

        if ($nom === '' || $specialite === '') {
            header("Location: admin_enseignants.php?flash=" . urlencode('error|' . t('admin_enseignants_error_champs')));
            exit;
        }

        $photo_name = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0 && in_array($_FILES['photo']['type'], $allowed_types)) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $photo_name = 'teacher_' . uniqid() . '.' . $ext;
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo_name)) {
                $photo_name = null;
            }
        }

        if ($teacher_id > 0) {
            if ($photo_name) {
                $stmt = $mysqli->prepare("UPDATE teachers SET nom=?, categorie=?, specialite_fr=?, specialite_en=NULL, specialite_mg=NULL, description_fr=?, description_en=NULL, description_mg=NULL, email=?, photo=? WHERE id=?");
                $stmt->bind_param("ssssssi", $nom, $categorie, $specialite, $description, $email, $photo_name, $teacher_id);
            } else {
                $stmt = $mysqli->prepare("UPDATE teachers SET nom=?, categorie=?, specialite_fr=?, specialite_en=NULL, specialite_mg=NULL, description_fr=?, description_en=NULL, description_mg=NULL, email=? WHERE id=?");
                $stmt->bind_param("sssssi", $nom, $categorie, $specialite, $description, $email, $teacher_id);
            }
            $stmt->execute();
            $stmt->close();
            header("Location: admin_enseignants.php?flash=" . urlencode('success|' . t('admin_enseignants_succes_maj')));
            exit;
        } else {
            $max_order = (int) $mysqli->query("SELECT COALESCE(MAX(display_order),0) m FROM teachers")->fetch_assoc()['m'];
            $max_order++;
            $stmt = $mysqli->prepare("INSERT INTO teachers (nom, categorie, specialite_fr, description_fr, email, photo, display_order) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssi", $nom, $categorie, $specialite, $description, $email, $photo_name, $max_order);
            $stmt->execute();
            $stmt->close();
            header("Location: admin_enseignants.php?flash=" . urlencode('success|' . t('admin_enseignants_succes_ajout')));
            exit;
        }
    }

    // --- Supprimer un enseignant ---
    if (isset($_POST['delete_teacher_id'])) {
        $id = (int) $_POST['delete_teacher_id'];
        $old = $mysqli->query("SELECT photo FROM teachers WHERE id = $id")->fetch_assoc();
        if ($old && $old['photo'] && file_exists($upload_dir . $old['photo'])) {
            unlink($upload_dir . $old['photo']);
        }
        $stmt = $mysqli->prepare("DELETE FROM teachers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_enseignants.php?flash=" . urlencode('success|' . t('admin_enseignants_succes_suppr')));
        exit;
    }
}

if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

$edit_teacher = null;
if (isset($_GET['edit']) && ctype_digit($_GET['edit'])) {
    $stmt = $mysqli->prepare("SELECT * FROM teachers WHERE id = ?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $edit_teacher = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$teachers = $mysqli->query("SELECT * FROM teachers ORDER BY categorie ASC, display_order ASC, id ASC")->fetch_all(MYSQLI_ASSOC);

$page_title = t('admin_enseignants_titre');
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
            <span><?php echo t('admin_enseignants_titre'); ?></span>
        </nav>
        <h1><?php echo t('admin_enseignants_titre'); ?></h1>
        <p><?php echo t('admin_enseignants_soustitre'); ?></p>
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

        <div class="add-new-item">
            <h4><?php echo $edit_teacher ? t('admin_enseignants_modifier_titre') : t('admin_enseignants_ajouter_titre'); ?></h4>
            <form action="admin_enseignants.php" method="POST" enctype="multipart/form-data" class="admin-form">
                <?php if ($edit_teacher): ?>
                    <input type="hidden" name="teacher_id" value="<?php echo (int) $edit_teacher['id']; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label><?php echo t('admin_enseignants_nom_label'); ?></label>
                    <input type="text" name="nom" value="<?php echo htmlspecialchars($edit_teacher['nom'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label><?php echo t('admin_enseignants_categorie_label'); ?></label>
                    <select name="categorie">
                        <option value="permanent" <?php echo (($edit_teacher['categorie'] ?? 'permanent') === 'permanent') ? 'selected' : ''; ?>><?php echo t('admin_enseignants_categorie_permanent'); ?></option>
                        <option value="vacataire" <?php echo (($edit_teacher['categorie'] ?? '') === 'vacataire') ? 'selected' : ''; ?>><?php echo t('admin_enseignants_categorie_vacataire'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo t('specialite'); ?></label>
                    <input type="text" name="specialite" value="<?php echo htmlspecialchars($edit_teacher['specialite_fr'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label><?php echo t('email'); ?></label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($edit_teacher['email'] ?? ''); ?>" placeholder="<?php echo t('admin_enseignants_email_hint'); ?>">
                </div>
                <div class="form-group upload-zone-group">
                    <label><?php echo t('admin_enseignants_photo_label'); ?></label>
                    <label class="upload-zone">
                        <i class="fas fa-camera"></i>
                        <span class="upload-zone-text"><?php echo t('admin_remplacer'); ?></span>
                        <span class="upload-zone-filename"></span>
                        <input type="file" name="photo" accept="image/*" class="upload-zone-input">
                    </label>
                </div>
                <div class="admin-newsletter-compose-actions">
                    <button type="submit" name="save_teacher" class="btn-add-item"><i class="fas fa-floppy-disk"></i> <?php echo $edit_teacher ? t('admin_enregistrer_modifications') : t('admin_enseignants_ajouter_btn'); ?></button>
                    <?php if ($edit_teacher): ?>
                        <a href="admin_enseignants.php" class="btn-outline"><?php echo t('admin_annuler'); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <h2 class="admin-section-title"><i class="fas fa-users"></i> <?php echo t('admin_enseignants_liste_titre'); ?></h2>
        <div class="admin-subscriber-list">
            <div class="admin-subscriber-row admin-subscriber-head">
                <span><?php echo t('admin_enseignants_nom_label'); ?></span>
                <span><?php echo t('specialite'); ?></span>
                <span></span>
            </div>
            <?php foreach ($teachers as $t_row): ?>
                <div class="admin-subscriber-row">
                    <span class="admin-subscriber-email">
                        <img src="<?php echo htmlspecialchars($t_row['photo'] ? 'images/teachers/' . $t_row['photo'] : 'images/teachers/default-avatar.svg'); ?>" alt="" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                        <?php echo htmlspecialchars($t_row['nom']); ?>
                        <span class="admin-status-badge <?php echo $t_row['categorie'] === 'permanent' ? 'admin-status-publie' : 'admin-status-brouillon'; ?>">
                            <?php echo $t_row['categorie'] === 'permanent' ? t('admin_enseignants_categorie_permanent') : t('admin_enseignants_categorie_vacataire'); ?>
                        </span>
                    </span>
                    <span><?php echo htmlspecialchars($t_row['specialite_fr']); ?></span>
                    <div style="display:flex; gap:8px;">
                        <a href="admin_enseignants.php?edit=<?php echo (int) $t_row['id']; ?>#top" class="btn-outline" title="<?php echo t('admin_modifier'); ?>"><i class="fas fa-pen"></i></a>
                        <form action="admin_enseignants.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_enseignants_confirm_delete')); ?>">
                            <button type="submit" name="delete_teacher_id" value="<?php echo (int) $t_row['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.upload-zone-input').forEach(input => {
        input.addEventListener('change', () => {
            const zone = input.closest('.upload-zone');
            const filenameSpan = zone.querySelector('.upload-zone-filename');
            if (input.files.length >= 1) {
                filenameSpan.textContent = input.files[0].name;
                zone.classList.add('has-file');
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>
