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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_org'])) {
    $names = $_POST['name'] ?? [];
    $stmt_name = $mysqli->prepare("UPDATE org_people SET name = ? WHERE title_key = ?");
    foreach ($names as $title_key => $name) {
        $name = trim($name);
        if ($name === '') continue;
        $stmt_name->bind_param("ss", $name, $title_key);
        $stmt_name->execute();
    }
    $stmt_name->close();

    if (isset($_FILES['photo']) && is_array($_FILES['photo']['name'])) {
        $stmt_photo = $mysqli->prepare("UPDATE org_people SET photo = ? WHERE title_key = ?");
        foreach ($_FILES['photo']['name'] as $title_key => $filename) {
            if ($_FILES['photo']['error'][$title_key] !== 0 || $filename === '') continue;
            $file_type = $_FILES['photo']['type'][$title_key];
            if (!in_array($file_type, $allowed_types)) continue;
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $new_filename = 'og_' . preg_replace('/[^a-z0-9_]/', '', $title_key) . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'][$title_key], $upload_dir . $new_filename)) {
                $stmt_photo->bind_param("ss", $new_filename, $title_key);
                $stmt_photo->execute();
            }
        }
        $stmt_photo->close();
    }

    header("Location: admin_organigramme.php?flash=" . urlencode('success|' . t('admin_organigramme_succes_maj')));
    exit;
}

if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

$org_people = $mysqli->query("SELECT * FROM org_people ORDER BY sort_order ASC")->fetch_all(MYSQLI_ASSOC);

$page_title = t('admin_organigramme_titre');
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
            <span><?php echo t('admin_organigramme_titre'); ?></span>
        </nav>
        <h1><?php echo t('admin_organigramme_titre'); ?></h1>
        <p><?php echo t('admin_organigramme_soustitre'); ?></p>
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

        <p class="admin-panel-hint"><?php echo t('admin_organigramme_hint'); ?></p>

        <form action="admin_organigramme.php" method="POST" enctype="multipart/form-data">
            <div class="photo-admin-grid admin-org-grid">
                <?php foreach ($org_people as $person): ?>
                    <div class="photo-admin-item admin-org-item">
                        <div class="photo-admin-thumb">
                            <img src="<?php echo htmlspecialchars($person['photo'] ? 'images/teachers/' . $person['photo'] : 'images/teachers/default-avatar.svg'); ?>" alt="<?php echo htmlspecialchars($person['name']); ?>">
                        </div>
                        <div class="gallery-admin-info">
                            <label class="admin-org-label"><?php echo t($person['title_key']); ?></label>
                            <input type="text" name="name[<?php echo htmlspecialchars($person['title_key']); ?>]" value="<?php echo htmlspecialchars($person['name']); ?>" required>
                            <label class="upload-zone" style="margin-top:8px;">
                                <i class="fas fa-camera"></i>
                                <span class="upload-zone-text"><?php echo t('admin_remplacer'); ?></span>
                                <span class="upload-zone-filename"></span>
                                <input type="file" name="photo[<?php echo htmlspecialchars($person['title_key']); ?>]" accept="image/*" class="upload-zone-input">
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" name="save_org" class="btn-add-item admin-org-save-btn"><i class="fas fa-floppy-disk"></i> <?php echo t('admin_enregistrer_modifications'); ?></button>
        </form>

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
