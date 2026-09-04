<?php
include_once 'language.php';
require_once 'db_connect.php';

// --- Accès réservé à l'administrateur (rôle admin) et au compte scolarité ---
// On revérifie is_scolarite en base plutôt que de se fier uniquement à la session,
// pour couvrir aussi les sessions ouvertes avant l'ajout de cette page (voir admin_etudiants.php).
$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';
$is_scolarite = false;
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && !$is_admin) {
    $row = $mysqli->query("SELECT is_scolarite FROM utilisateurs WHERE id = " . (int) $_SESSION['user_id'])->fetch_assoc();
    $is_scolarite = $row && (bool) $row['is_scolarite'];
}
if (!$is_admin && !$is_scolarite) {
    header('Location: index.php');
    exit;
}

$upload_dir = 'uploads/';
$allowed_doc_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg',
    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$flash = null;

function doc_unlink_if_upload($path) {
    if ($path && file_exists($path) && strpos($path, 'uploads/') === 0) {
        unlink($path);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Ajouter un document ---
    if (isset($_POST['add_document'])) {
        $title = trim($_POST['title'] ?? '');
        $category = ($_POST['category'] ?? 'public') === 'etudiant' ? 'etudiant' : 'public';

        if ($title === '') {
            $flash = ['type' => 'error', 'msg' => t('admin_documents_titre_champ') . ' ' . t('admin_champ_obligatoire')];
        } elseif (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] != 0 || !in_array($_FILES['document_file']['type'], $allowed_doc_types)) {
            $flash = ['type' => 'error', 'msg' => t('admin_documents_fichier_champ') . ' ' . t('admin_champ_obligatoire')];
        } else {
            $ext = pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION);
            $new_name = 'doc_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['document_file']['tmp_name'], $upload_dir . $new_name)) {
                $path = $upload_dir . $new_name;
                $stmt = $mysqli->prepare("INSERT INTO documents (title, file_path, category) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $title, $path, $category);
                $stmt->execute();
                $stmt->close();
                $flash = ['type' => 'success', 'msg' => t('admin_ajout_reussi')];
            } else {
                $flash = ['type' => 'error', 'msg' => t('admin_erreur_generique')];
            }
        }
        if ($flash) {
            header("Location: admin_documents.php?flash=" . urlencode($flash['type'] . '|' . $flash['msg']));
            exit;
        }
    }

    // --- Supprimer un document ---
    if (isset($_POST['delete_document_id'])) {
        $doc_id = (int) $_POST['delete_document_id'];
        $old = $mysqli->query("SELECT file_path FROM documents WHERE id = $doc_id")->fetch_assoc();
        if ($old) doc_unlink_if_upload($old['file_path']);
        $stmt = $mysqli->prepare("DELETE FROM documents WHERE id = ?");
        $stmt->bind_param("i", $doc_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_documents.php?flash=" . urlencode('success|' . t('admin_suppression_reussie')));
        exit;
    }
}

if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

$all_documents = $mysqli->query("SELECT * FROM documents ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

$page_title = t('admin_documents_titre');
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
            <span><?php echo t('admin_documents_titre'); ?></span>
        </nav>
        <h1><?php echo t('admin_documents_titre'); ?></h1>
        <p><?php echo t('admin_documents_soustitre'); ?></p>
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

        <form action="admin_documents.php" method="POST" enctype="multipart/form-data" class="admin-form admin-album-form">
            <div class="album-form-grid">
                <div class="album-form-main">
                    <div class="form-group">
                        <label><?php echo t('admin_documents_titre_champ'); ?></label>
                        <input type="text" name="title" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('admin_documents_categorie_champ'); ?></label>
                        <select name="category">
                            <option value="public"><?php echo t('admin_documents_categorie_public'); ?></option>
                            <option value="etudiant"><?php echo t('admin_documents_categorie_etudiant'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="album-form-side">
                    <div class="form-group upload-zone-group">
                        <label><?php echo t('admin_documents_fichier_champ'); ?></label>
                        <label class="upload-zone">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <span class="upload-zone-text"><?php echo t('admin_choisir_image_deposer'); ?></span>
                            <span class="upload-zone-filename"></span>
                            <input type="file" name="document_file" accept=".pdf,.doc,.docx,image/jpeg,image/png,image/jpg" class="upload-zone-input" required>
                        </label>
                    </div>
                </div>
            </div>
            <button type="submit" name="add_document" class="btn-submit"><i class="fas fa-plus"></i> <?php echo t('admin_documents_ajouter'); ?></button>
        </form>

        <h2 class="admin-section-title"><?php echo t('admin_documents_liste_titre'); ?></h2>

        <?php if (empty($all_documents)): ?>
            <p class="gallery-empty"><i class="fas fa-file-lines"></i> <?php echo t('admin_documents_aucun'); ?></p>
        <?php else: ?>
            <div class="admin-album-list">
                <?php foreach ($all_documents as $d): ?>
                    <div class="admin-album-row">
                        <div class="admin-album-row-thumb" style="display:flex;align-items:center;justify-content:center;background:var(--box-bg-color-alt,#f1f1f1);"><i class="fas fa-file-lines" style="font-size:1.6rem;color:var(--secondary-color);"></i></div>
                        <div class="admin-album-row-info">
                            <h4><?php echo htmlspecialchars($d['title']); ?></h4>
                            <div class="admin-album-row-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($d['created_at'])); ?></span>
                                <?php if ($d['category'] === 'etudiant'): ?>
                                    <span class="documents-badge-etudiant"><i class="fas fa-lock"></i> <?php echo t('documents_badge_etudiant'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="admin-album-row-actions">
                            <a href="<?php echo htmlspecialchars($d['file_path']); ?>" download class="btn-outline" title="<?php echo t('documents_telecharger'); ?>"><i class="fas fa-download"></i></a>
                            <form action="admin_documents.php" method="POST" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_documents_supprimer_confirm')); ?>">
                                <button type="submit" name="delete_document_id" value="<?php echo (int) $d['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>
