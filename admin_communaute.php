<?php
include_once 'language.php';
require_once 'db_connect.php';

$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';
$can_publish = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && in_array($_SESSION['user_role'], ['admin', 'enseignant'], true);
if (!$can_publish) {
    header('Location: index.php');
    exit;
}
$self_id = (int) ($_SESSION['user_id'] ?? 0);

$upload_dir = 'uploads/';
$type_map = [
    'image/jpeg' => 'image', 'image/png' => 'image', 'image/jpg' => 'image', 'image/webp' => 'image',
    'video/mp4' => 'video', 'video/webm' => 'video', 'video/quicktime' => 'video',
    'application/pdf' => 'pdf',
];
$valid_types = ['actualite', 'resultat', 'emploi_du_temps', 'examen', 'media', 'autre'];
$flash = null;

function com_unlink_if_upload($path) {
    if ($path && file_exists($path) && strpos($path, 'uploads/') === 0) {
        unlink($path);
    }
}

// Un enseignant ne peut gérer (modifier/supprimer/modérer) que ses propres publications ;
// l'administrateur peut tout gérer.
function com_can_manage_post($auteur_id, $is_admin, $self_id) {
    return $is_admin || (int) $auteur_id === (int) $self_id;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Créer / mettre à jour une publication (plusieurs images/vidéos/documents possibles) ---
    if (isset($_POST['save_post'])) {
        $post_id = isset($_POST['post_id']) && ctype_digit($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        $type = in_array($_POST['type'] ?? '', $valid_types, true) ? $_POST['type'] : 'autre';
        $contenu = trim($_POST['contenu'] ?? '');

        if ($post_id > 0) {
            $owner = $mysqli->query("SELECT auteur_id FROM communaute_posts WHERE id = $post_id")->fetch_assoc();
            if (!$owner || !com_can_manage_post($owner['auteur_id'], $is_admin, $self_id)) {
                header('Location: admin_communaute.php');
                exit;
            }
        }

        // Nombre de fichiers déjà attachés (pour la validation "contenu ou média requis" et pour
        // poursuivre la numérotation display_order lors d'un ajout sur une publication existante).
        $existing_media_count = $post_id > 0
            ? (int) $mysqli->query("SELECT COUNT(*) c FROM communaute_post_media WHERE post_id = $post_id")->fetch_assoc()['c']
            : 0;
        $has_new_files = isset($_FILES['media']) && !empty(array_filter($_FILES['media']['name']));

        if ($contenu === '' && !$has_new_files && $existing_media_count === 0) {
            header("Location: admin_communaute.php?view=" . ($post_id ? "edit&id=$post_id" : "edit") . "&flash=" . urlencode('error|' . t('admin_communaute_contenu_requis')));
            exit;
        }

        $is_new_post = $post_id === 0;
        $auteur_id = (int) $_SESSION['user_id'];

        if ($post_id > 0) {
            $stmt = $mysqli->prepare("UPDATE communaute_posts SET type=?, contenu=?, updated_at=NOW() WHERE id=?");
            $stmt->bind_param("ssi", $type, $contenu, $post_id);
            $stmt->execute();
            $stmt->close();
            $flash = ['type' => 'success', 'msg' => t('admin_communaute_maj_reussie')];
        } else {
            $stmt = $mysqli->prepare("INSERT INTO communaute_posts (auteur_id, type, contenu) VALUES (?,?,?)");
            $stmt->bind_param("iss", $auteur_id, $type, $contenu);
            $stmt->execute();
            $post_id = $mysqli->insert_id;
            $stmt->close();
            $flash = ['type' => 'success', 'msg' => t('admin_communaute_creation_reussie')];
        }

        // --- Enregistre chaque fichier valide comme une ligne de média séparée ---
        if ($has_new_files) {
            $order = $existing_media_count;
            $media_stmt = $mysqli->prepare("INSERT INTO communaute_post_media (post_id, media_path, media_type, display_order) VALUES (?, ?, ?, ?)");
            foreach ($_FILES['media']['name'] as $i => $name) {
                if ($_FILES['media']['error'][$i] != 0 || $name === '') continue;
                $mime = $_FILES['media']['type'][$i];
                if (!isset($type_map[$mime])) continue;
                $media_type = $type_map[$mime];
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $new_name = 'communaute_' . uniqid() . '_' . $i . '.' . $ext;
                if (move_uploaded_file($_FILES['media']['tmp_name'][$i], $upload_dir . $new_name)) {
                    $path = $upload_dir . $new_name;
                    $order++;
                    $media_stmt->bind_param("issi", $post_id, $path, $media_type, $order);
                    $media_stmt->execute();
                }
            }
            $media_stmt->close();
        }

        // --- Notifie tous les autres membres de la Communauté lors d'une nouvelle publication ---
        if ($is_new_post) {
            $notif_stmt = $mysqli->prepare("
                INSERT INTO communaute_notifications (user_id, type, post_id, actor_id)
                SELECT id, 'nouvelle_publication', ?, ? FROM utilisateurs WHERE role IN ('enseignant','etudiant','admin') AND id != ?
            ");
            $notif_stmt->bind_param("iii", $post_id, $auteur_id, $auteur_id);
            $notif_stmt->execute();
            $notif_stmt->close();
        }

        header("Location: admin_communaute.php?flash=" . urlencode($flash['type'] . '|' . $flash['msg']));
        exit;
    }

    // --- Supprimer un fichier média d'une publication (depuis l'écran d'édition) ---
    if (isset($_POST['delete_media_id'])) {
        $media_id = (int) $_POST['delete_media_id'];
        $post_id = (int) ($_POST['post_id'] ?? 0);
        $owner = $mysqli->query("SELECT auteur_id FROM communaute_posts WHERE id = $post_id")->fetch_assoc();
        if (!$owner || !com_can_manage_post($owner['auteur_id'], $is_admin, $self_id)) {
            header('Location: admin_communaute.php');
            exit;
        }
        $old = $mysqli->query("SELECT media_path FROM communaute_post_media WHERE id = $media_id")->fetch_assoc();
        if ($old) com_unlink_if_upload($old['media_path']);
        $stmt = $mysqli->prepare("DELETE FROM communaute_post_media WHERE id = ?");
        $stmt->bind_param("i", $media_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_communaute.php?view=edit&id=$post_id&flash=" . urlencode('success|' . t('admin_communaute_media_supprime')));
        exit;
    }

    // --- Supprimer une publication ---
    if (isset($_POST['delete_post_id'])) {
        $post_id = (int) $_POST['delete_post_id'];
        $owner = $mysqli->query("SELECT auteur_id FROM communaute_posts WHERE id = $post_id")->fetch_assoc();
        if (!$owner || !com_can_manage_post($owner['auteur_id'], $is_admin, $self_id)) {
            header('Location: admin_communaute.php');
            exit;
        }
        $media_rows = $mysqli->query("SELECT media_path FROM communaute_post_media WHERE post_id = $post_id");
        while ($m = $media_rows->fetch_assoc()) { com_unlink_if_upload($m['media_path']); }
        $stmt = $mysqli->prepare("DELETE FROM communaute_posts WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_communaute.php?flash=" . urlencode('success|' . t('admin_communaute_suppression_reussie')));
        exit;
    }

    // --- Modération : supprimer un commentaire directement depuis sa publication ---
    if (isset($_POST['delete_comment_id'])) {
        $comment_id = (int) $_POST['delete_comment_id'];
        $row = $mysqli->query("
            SELECT c.post_id, p.auteur_id
            FROM communaute_comments c JOIN communaute_posts p ON p.id = c.post_id
            WHERE c.id = $comment_id
        ")->fetch_assoc();
        if (!$row || !com_can_manage_post($row['auteur_id'], $is_admin, $self_id)) {
            header('Location: admin_communaute.php');
            exit;
        }
        $redirect_post_id = (int) $row['post_id'];
        $stmt = $mysqli->prepare("DELETE FROM communaute_comments WHERE id = ?");
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_communaute.php?view=edit&id=$redirect_post_id&flash=" . urlencode('success|' . t('admin_communaute_commentaire_supprime')));
        exit;
    }
}

if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

$view = $_GET['view'] ?? 'list';

$type_label_keys = [
    'actualite' => 'communaute_type_actualite', 'resultat' => 'communaute_type_resultat',
    'emploi_du_temps' => 'communaute_type_emploi_du_temps', 'examen' => 'communaute_type_examen',
    'media' => 'communaute_type_media', 'autre' => 'communaute_type_autre',
];

// --- Données de la vue édition, récupérées AVANT le header (redirection possible si l'auteur
// n'est pas celui qui essaie d'éditer) ---
$post = null;
$existing_media = [];
$post_comments = [];
if ($view === 'edit') {
    $post_id_get = (int) ($_GET['id'] ?? 0);
    if ($post_id_get) {
        $post = $mysqli->query("SELECT * FROM communaute_posts WHERE id = $post_id_get")->fetch_assoc();
        if ($post && !com_can_manage_post($post['auteur_id'], $is_admin, $self_id)) {
            header('Location: admin_communaute.php');
            exit;
        }
        if ($post) {
            $existing_media = $mysqli->query("SELECT * FROM communaute_post_media WHERE post_id = $post_id_get ORDER BY display_order ASC")->fetch_all(MYSQLI_ASSOC);
            $post_comments = $mysqli->query("
                SELECT c.*, u.nom, u.avatar_path
                FROM communaute_comments c JOIN utilisateurs u ON u.id = c.user_id
                WHERE c.post_id = $post_id_get
                ORDER BY c.created_at ASC
            ")->fetch_all(MYSQLI_ASSOC);
        }
    }
}

$page_title = t('admin_communaute_titre');
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
            <span><?php echo t('admin_communaute_titre'); ?></span>
        </nav>
        <h1><?php echo t('admin_communaute_titre'); ?></h1>
        <p><?php echo t('admin_communaute_soustitre'); ?></p>
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

        <a href="communaute.php" class="btn-outline" style="margin-bottom:16px;display:inline-flex;"><i class="fas fa-eye"></i> <?php echo t('admin_communaute_voir_page'); ?></a>

        <div class="admin-galerie-toolbar etu-tabs">
            <a href="admin_communaute.php" class="btn-outline <?php echo $view === 'list' ? 'is-active' : ''; ?>"><i class="fas fa-list"></i> <?php echo t('admin_communaute_publications'); ?></a>
            <?php if ($view !== 'edit'): ?><a href="admin_communaute.php?view=edit" class="btn-add-item admin-new-album-btn"><i class="fas fa-plus"></i> <?php echo t('admin_communaute_nouvelle_publication'); ?></a><?php endif; ?>
        </div>

        <?php if ($view === 'edit'): ?>
            <h2 class="admin-section-title"><?php echo $post ? t('admin_communaute_modifier') : t('admin_communaute_nouvelle_publication'); ?></h2>

            <?php if (!empty($existing_media)): ?>
                <div class="admin-communaute-media-grid">
                    <?php foreach ($existing_media as $m): ?>
                        <div class="admin-communaute-media-item">
                            <?php if ($m['media_type'] === 'image'): ?>
                                <img src="<?php echo htmlspecialchars($m['media_path']); ?>" alt="">
                            <?php elseif ($m['media_type'] === 'video'): ?>
                                <video src="<?php echo htmlspecialchars($m['media_path']); ?>" controls></video>
                            <?php else: ?>
                                <div class="admin-communaute-media-pdf"><i class="fas fa-file-pdf"></i></div>
                            <?php endif; ?>
                            <form action="admin_communaute.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_communaute_media_supprimer_confirm')); ?>">
                                <input type="hidden" name="post_id" value="<?php echo (int) $post['id']; ?>">
                                <button type="submit" name="delete_media_id" value="<?php echo (int) $m['id']; ?>" class="btn-delete admin-communaute-media-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="admin_communaute.php" method="POST" enctype="multipart/form-data" class="admin-form admin-album-form">
                <?php if ($post): ?><input type="hidden" name="post_id" value="<?php echo (int) $post['id']; ?>"><?php endif; ?>
                <div class="album-form-grid">
                    <div class="album-form-main">
                        <div class="form-group">
                            <label><?php echo t('admin_categorie_label'); ?></label>
                            <select name="type">
                                <?php foreach ($valid_types as $t_key): ?>
                                    <option value="<?php echo $t_key; ?>" <?php echo (($post['type'] ?? 'autre') === $t_key) ? 'selected' : ''; ?>><?php echo t($type_label_keys[$t_key]); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?php echo t('admin_communaute_legende_label'); ?></label>
                            <textarea name="contenu" rows="6" placeholder="<?php echo t('admin_communaute_legende_placeholder'); ?>"><?php echo htmlspecialchars($post['contenu'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="album-form-side">
                        <div class="form-group upload-zone-group">
                            <label><?php echo t('admin_communaute_media_label'); ?></label>
                            <p class="profile-card-hint"><?php echo t('admin_communaute_media_hint'); ?></p>
                            <label class="upload-zone upload-zone-multi">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <span class="upload-zone-text"><?php echo t('admin_galerie_multi_upload_text'); ?></span>
                                <span class="upload-zone-filename"></span>
                                <input type="file" name="media[]" accept="image/jpeg,image/png,image/jpg,image/webp,video/mp4,video/webm,application/pdf" class="upload-zone-input" multiple>
                            </label>
                        </div>
                    </div>
                </div>
                <button type="submit" name="save_post" class="btn-submit"><i class="fas fa-save"></i> <?php echo $post ? t('admin_enregistrer_modifications') : t('admin_communaute_publier'); ?></button>
            </form>

            <?php if ($post): ?>
                <h2 class="admin-section-title admin-communaute-comments-title"><i class="fas fa-comments"></i> <?php echo t('admin_communaute_commentaires_publication'); ?> (<?php echo count($post_comments); ?>)</h2>
                <?php if (empty($post_comments)): ?>
                    <p class="gallery-empty"><i class="fas fa-comments"></i> <?php echo t('admin_communaute_aucun_commentaire'); ?></p>
                <?php else: ?>
                    <div class="admin-communaute-comments-list">
                        <?php foreach ($post_comments as $c): ?>
                            <div class="admin-communaute-comment-row<?php echo $c['parent_id'] ? ' is-reply' : ''; ?>">
                                <div class="admin-communaute-comment-avatar">
                                    <?php if (!empty($c['avatar_path'])): ?>
                                        <img src="<?php echo SITE_URL . '/' . htmlspecialchars($c['avatar_path']); ?>" alt="">
                                    <?php else: ?>
                                        <i class="fas fa-circle-user"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="admin-communaute-comment-body">
                                    <strong><?php echo htmlspecialchars($c['nom']); ?></strong>
                                    <span class="admin-communaute-comment-date"><?php echo date('d/m/Y H:i', strtotime($c['created_at'])); ?></span>
                                    <p><?php echo nl2br(htmlspecialchars($c['contenu'])); ?></p>
                                </div>
                                <form action="admin_communaute.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('communaute_supprimer_commentaire_confirm')); ?>">
                                    <button type="submit" name="delete_comment_id" value="<?php echo (int) $c['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        <?php else:
            $all_posts_sql = "
                SELECT p.*, u.nom AS auteur_nom,
                    (SELECT COUNT(*) FROM communaute_reactions r WHERE r.post_id = p.id) AS reactions_count,
                    (SELECT COUNT(*) FROM communaute_comments cm WHERE cm.post_id = p.id) AS comments_count,
                    (SELECT COUNT(*) FROM communaute_post_media pm WHERE pm.post_id = p.id) AS media_count,
                    (SELECT media_path FROM communaute_post_media pm WHERE pm.post_id = p.id AND pm.media_type = 'image' ORDER BY display_order ASC LIMIT 1) AS thumb_path
                FROM communaute_posts p JOIN utilisateurs u ON u.id = p.auteur_id
                ORDER BY p.created_at DESC
            ";
            $all_posts = $mysqli->query($all_posts_sql)->fetch_all(MYSQLI_ASSOC);
        ?>
            <?php if (empty($all_posts)): ?>
                <p class="gallery-empty"><i class="fas fa-users-rectangle"></i> <?php echo t('communaute_aucune_publication'); ?></p>
            <?php else: ?>
                <div class="admin-album-list">
                    <?php foreach ($all_posts as $p): $can_manage = com_can_manage_post($p['auteur_id'], $is_admin, $self_id); ?>
                        <div class="admin-album-row">
                            <?php if (!empty($p['thumb_path'])): ?>
                                <img src="<?php echo htmlspecialchars($p['thumb_path']); ?>" alt="" class="admin-album-row-thumb">
                            <?php else: ?>
                                <div class="admin-album-row-thumb" style="display:flex;align-items:center;justify-content:center;background:var(--box-bg-color-alt,#f1f1f1);"><i class="fas fa-thumbtack" style="font-size:1.4rem;color:var(--secondary-color);"></i></div>
                            <?php endif; ?>
                            <div class="admin-album-row-info">
                                <h4><?php echo htmlspecialchars(mb_strimwidth($p['contenu'] ?: t($type_label_keys[$p['type']]), 0, 70, '...')); ?></h4>
                                <div class="admin-album-row-meta">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($p['auteur_nom']); ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($p['created_at'])); ?></span>
                                    <span><i class="fas fa-heart"></i> <?php echo (int) $p['reactions_count']; ?></span>
                                    <span><i class="fas fa-comment"></i> <?php echo (int) $p['comments_count']; ?></span>
                                    <?php if ((int) $p['media_count'] > 0): ?><span><i class="fas fa-paperclip"></i> <?php echo (int) $p['media_count']; ?></span><?php endif; ?>
                                </div>
                            </div>
                            <?php if ($can_manage): ?>
                                <div class="admin-album-row-actions">
                                    <a href="admin_communaute.php?view=edit&id=<?php echo $p['id']; ?>" class="btn-outline" title="<?php echo t('admin_modifier'); ?>"><i class="fas fa-pen"></i></a>
                                    <form action="admin_communaute.php" method="POST" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_communaute_supprimer_confirm')); ?>">
                                        <button type="submit" name="delete_post_id" value="<?php echo (int) $p['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            <?php endif; ?>
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
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>
