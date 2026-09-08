<?php
include_once 'language.php';
require_once 'db_connect.php';

$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';
if (!$is_admin) {
    header('Location: index.php');
    exit;
}

$upload_dir = 'uploads/';
$flash = null;

// Liste EXHAUSTIVE de toutes les pages du site qui affichent une ".page-banner" — la clé est
// le chemin relatif au site (identique au simple nom de fichier pour une page racine ; préfixé
// par son sous-dossier pour la bibliothèque, qui partage certains noms avec des pages racine :
// voir le commentaire sur $current_page_key dans header.php), utilisé pour savoir quelle
// bannière personnalisée appliquer sur la page en cours de rendu.
// 'group' vaut 'global' (site public / espace connecté) ou 'admin' (tableaux de bord
// d'administration, y compris ceux de la bibliothèque), pour permettre le filtre par bouton.
$manageable_pages = [
    // --- Site public ---
    'historique.php'               => ['label' => 'Historique', 'group' => 'global'],
    'parcours.php'                 => ['label' => 'Organigramme / Parcours', 'group' => 'global'],
    'vie_etudiante.php'            => ['label' => 'Vie étudiante', 'group' => 'global'],
    'bourse.php'                   => ['label' => 'Bourses', 'group' => 'global'],
    'campus.php'                   => ['label' => 'Vie au campus', 'group' => 'global'],
    'enseignants.php'               => ['label' => 'Enseignants', 'group' => 'global'],
    'filieres.php'                  => ['label' => 'Filières (liste)', 'group' => 'global'],
    'filiere_detail.php'            => ['label' => 'Filière (détail)', 'group' => 'global'],
    'actualite.php'                 => ['label' => 'Actualités (liste)', 'group' => 'global'],
    'actualite_article.php'         => ['label' => 'Actualité (détail)', 'group' => 'global'],
    'galerie.php'                   => ['label' => 'Galerie (liste)', 'group' => 'global'],
    'galerie_album.php'             => ['label' => 'Galerie — Album (détail)', 'group' => 'global'],
    'associations.php'              => ['label' => 'Associations', 'group' => 'global'],
    'inscription.php'               => ['label' => 'Inscription', 'group' => 'global'],
    'preinscription.php'            => ['label' => 'Formulaire de pré-inscription', 'group' => 'global'],
    'login.php'                     => ['label' => 'Connexion', 'group' => 'global'],
    'mot_de_passe_oublie.php'       => ['label' => 'Mot de passe oublié', 'group' => 'global'],
    'reinitialiser_mdp.php'         => ['label' => 'Réinitialiser le mot de passe', 'group' => 'global'],
    'recherche.php'                 => ['label' => 'Recherche', 'group' => 'global'],
    'profil.php'                    => ['label' => 'Mon profil', 'group' => 'global'],
    'mentions_legales.php'          => ['label' => 'Mentions légales', 'group' => 'global'],
    'confidentialite.php'           => ['label' => 'Confidentialité', 'group' => 'global'],
    'documents.php'                 => ['label' => 'Documents (public)', 'group' => 'global'],
    'evenements.php'                => ['label' => 'Calendrier des événements', 'group' => 'global'],
    'newsletter_unsubscribe.php'    => ['label' => 'Désabonnement newsletter', 'group' => 'global'],
    '404.php'                       => ['label' => 'Page introuvable (404)', 'group' => 'global'],

    // --- Espace connecté (enseignant / étudiant) ---
    'mes_groupes.php'               => ['label' => 'Mes groupes (enseignant/étudiant)', 'group' => 'global'],
    'communaute.php'                => ['label' => 'Communauté (enseignant/étudiant)', 'group' => 'global'],

    // --- Bibliothèque (site public) ---
    'bibliotheque/index.php'            => ['label' => 'Bibliothèque — Accueil', 'group' => 'global'],
    'bibliotheque/horaire.php'          => ['label' => 'Bibliothèque — Horaires', 'group' => 'global'],
    'bibliotheque/recherche.php'        => ['label' => 'Bibliothèque — Recherche', 'group' => 'global'],
    'bibliotheque/memoires.php'         => ['label' => 'Bibliothèque — Mémoires', 'group' => 'global'],
    'bibliotheque/canevas.php'          => ['label' => 'Bibliothèque — Canevas', 'group' => 'global'],
    'bibliotheque/consulter_memoire.php' => ['label' => 'Bibliothèque — Consulter un mémoire', 'group' => 'global'],

    // --- Espace administration (site principal) ---
    'administrateur.php'      => ['label' => 'Tableau de bord (Administration)', 'group' => 'admin'],
    'admin_utilisateurs.php'  => ['label' => 'Admin — Utilisateurs', 'group' => 'admin'],
    'admin_etudiants.php'     => ['label' => 'Admin — Étudiants', 'group' => 'admin'],
    'admin_enseignants.php'   => ['label' => 'Admin — Enseignants', 'group' => 'admin'],
    'admin_organigramme.php'  => ['label' => 'Admin — Organigramme', 'group' => 'admin'],
    'admin_filieres.php'      => ['label' => 'Admin — Filières', 'group' => 'admin'],
    'admin_galerie.php'       => ['label' => 'Admin — Galerie', 'group' => 'admin'],
    'admin_actualites.php'    => ['label' => 'Admin — Actualités', 'group' => 'admin'],
    'admin_contenu.php'       => ['label' => 'Admin — Contenu du site', 'group' => 'admin'],
    'admin_campus.php'        => ['label' => 'Admin — Campus', 'group' => 'admin'],
    'admin_documents.php'     => ['label' => 'Admin — Documents', 'group' => 'admin'],
    'admin_partenaires.php'   => ['label' => 'Admin — Partenaires', 'group' => 'admin'],
    'admin_newsletter.php'    => ['label' => 'Admin — Newsletter', 'group' => 'admin'],
    'admin_banners.php'       => ['label' => 'Admin — Bannières', 'group' => 'admin'],
    'admin_securite.php'      => ['label' => 'Admin — Sécurité', 'group' => 'admin'],
    'admin_evenements.php'    => ['label' => 'Admin — Événements', 'group' => 'admin'],
    'admin_communaute.php'    => ['label' => 'Admin — Communauté', 'group' => 'admin'],

    // --- Espace administration (bibliothèque) ---
    'bibliotheque/admin/dashboard.php'       => ['label' => 'Bibliothèque Admin — Tableau de bord', 'group' => 'admin'],
    'bibliotheque/admin/gerer_actualite.php' => ['label' => 'Bibliothèque Admin — Actualités', 'group' => 'admin'],
    'bibliotheque/admin/gerer_canevas.php'   => ['label' => 'Bibliothèque Admin — Canevas', 'group' => 'admin'],
    'bibliotheque/admin/gerer_carousel.php'  => ['label' => 'Bibliothèque Admin — Carrousel', 'group' => 'admin'],
    'bibliotheque/admin/gerer_filieres.php'  => ['label' => 'Bibliothèque Admin — Filières', 'group' => 'admin'],
    'bibliotheque/admin/gerer_memoires.php'  => ['label' => 'Bibliothèque Admin — Mémoires', 'group' => 'admin'],
    'bibliotheque/admin/gerer_horaire.php'   => ['label' => 'Bibliothèque Admin — Horaires', 'group' => 'admin'],
    'bibliotheque/admin/profil.php'          => ['label' => 'Bibliothèque Admin — Mon profil', 'group' => 'admin'],
    'bibliotheque/admin/ajouter_memoire.php' => ['label' => 'Bibliothèque Admin — Ajouter un mémoire', 'group' => 'admin'],
    'bibliotheque/admin/ajouter_canevas.php' => ['label' => 'Bibliothèque Admin — Ajouter un canevas', 'group' => 'admin'],
];

function ban_unlink_if_upload($path) {
    if ($path && file_exists($path) && strpos($path, 'uploads/') === 0) {
        unlink($path);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Ajouter des bannières (photos et/ou vidéos, tous formats) pour une page précise ---
    if (isset($_POST['upload_banners']) && isset($_FILES['new_banners'])) {
        $page_key = $_POST['page_key'] ?? '';
        if (!array_key_exists($page_key, $manageable_pages)) {
            header("Location: admin_banners.php?flash=" . urlencode('error|Page invalide.'));
            exit;
        }
        $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'avif'];
        $video_exts = ['mp4', 'webm', 'mov', 'ogg', 'avi', 'mkv', 'm4v'];
        $stmt2 = $mysqli->prepare("SELECT COALESCE(MAX(display_order),0) AS m FROM site_banners WHERE page_key = ?");
        $stmt2->bind_param("s", $page_key);
        $stmt2->execute();
        $order = (int) $stmt2->get_result()->fetch_assoc()['m'];
        $stmt2->close();
        $stmt = $mysqli->prepare("INSERT INTO site_banners (page_key, media_type, media_path, title, display_order) VALUES (?,?,?,?,?)");
        $count_images = 0;
        $count_videos = 0;
        foreach ($_FILES['new_banners']['name'] as $key => $name) {
            if ($_FILES['new_banners']['error'][$key] != 0) continue;
            $fext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $is_image = in_array($fext, $image_exts);
            $is_video_file = in_array($fext, $video_exts);
            if (!$is_image && !$is_video_file) continue;
            $title = pathinfo($name, PATHINFO_FILENAME);
            $prefix = $is_image ? 'banner_img_' : 'banner_video_';
            $new_name = $prefix . uniqid() . '_' . $key . '.' . $fext;
            if (move_uploaded_file($_FILES['new_banners']['tmp_name'][$key], $upload_dir . $new_name)) {
                $order++;
                $path = $upload_dir . $new_name;
                $media_type = $is_image ? 'image' : 'video';
                $stmt->bind_param("ssssi", $page_key, $media_type, $path, $title, $order);
                $stmt->execute();
                if ($is_image) $count_images++; else $count_videos++;
            }
        }
        $stmt->close();
        header("Location: admin_banners.php?page=" . urlencode($page_key) . "&flash=" . urlencode('success|' . $count_images . ' image(s) et ' . $count_videos . ' vidéo(s) ajoutées.'));
        exit;
    }

    // --- Définir une bannière comme active pour SA page (les autres bannières de cette même page sont désactivées) ---
    if (isset($_POST['set_active_banner_id'])) {
        $banner_id = (int) $_POST['set_active_banner_id'];
        $page_key = $_POST['page_key'] ?? '';
        $stmt = $mysqli->prepare("UPDATE site_banners SET is_active = 0 WHERE page_key = ?");
        $stmt->bind_param("s", $page_key);
        $stmt->execute();
        $stmt->close();
        $stmt = $mysqli->prepare("UPDATE site_banners SET is_active = 1 WHERE id = ? AND page_key = ?");
        $stmt->bind_param("is", $banner_id, $page_key);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_banners.php?page=" . urlencode($page_key) . "&flash=" . urlencode('success|Bannière activée pour cette page.'));
        exit;
    }

    // --- Désactiver la bannière active d'une page (retour à son image par défaut) ---
    if (isset($_POST['deactivate_banner'])) {
        $page_key = $_POST['page_key'] ?? '';
        $stmt = $mysqli->prepare("UPDATE site_banners SET is_active = 0 WHERE page_key = ?");
        $stmt->bind_param("s", $page_key);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_banners.php?page=" . urlencode($page_key) . "&flash=" . urlencode('success|Bannière désactivée, la page retrouve son image par défaut.'));
        exit;
    }

    // --- Supprimer une bannière ---
    if (isset($_POST['delete_banner_id'])) {
        $banner_id = (int) $_POST['delete_banner_id'];
        $page_key = $_POST['page_key'] ?? '';
        $old = $mysqli->query("SELECT media_path FROM site_banners WHERE id = $banner_id")->fetch_assoc();
        if ($old) ban_unlink_if_upload($old['media_path']);
        $stmt = $mysqli->prepare("DELETE FROM site_banners WHERE id = ?");
        $stmt->bind_param("i", $banner_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_banners.php?page=" . urlencode($page_key) . "&flash=" . urlencode('success|Bannière supprimée.'));
        exit;
    }
}

if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

// Filtre par bouton "Pages globales" / "Administration" : le groupe suit la page choisie
// (?page=...) quand il y en a une, sinon celui explicitement demandé (?group=...), sinon 'global'.
$current_page_req = $_GET['page'] ?? null;
if ($current_page_req !== null && array_key_exists($current_page_req, $manageable_pages)) {
    $current_group = $manageable_pages[$current_page_req]['group'];
} else {
    $current_page_req = null;
    $current_group = ($_GET['group'] ?? '') === 'admin' ? 'admin' : 'global';
}

$group_pages = array_filter($manageable_pages, fn($p) => $p['group'] === $current_group);

$current_page = $current_page_req;
if ($current_page === null || !isset($group_pages[$current_page])) {
    $current_page = array_key_first($group_pages);
}

$stmt = $mysqli->prepare("SELECT * FROM site_banners WHERE page_key = ? ORDER BY display_order ASC, id ASC");
$stmt->bind_param("s", $current_page);
$stmt->execute();
$banners = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$active_banner = null;
foreach ($banners as $b) { if ($b['is_active']) { $active_banner = $b; break; } }

// Petit récapitulatif (pages qui ont déjà une bannière active), pour l'affichage de la liste
$active_pages_result = $mysqli->query("SELECT DISTINCT page_key FROM site_banners WHERE is_active = 1");
$active_pages = [];
while ($row = $active_pages_result->fetch_assoc()) { $active_pages[$row['page_key']] = true; }

$page_title = t('admin_banners_titre');

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
            <span><?php echo t('admin_banners_titre'); ?></span>
        </nav>
        <h1><?php echo t('admin_banners_titre'); ?></h1>
        <p><?php echo t('admin_banners_soustitre_perpage'); ?></p>
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

        <div class="admin-galerie-toolbar etu-tabs">
            <a href="admin_banners.php?group=global" class="btn-outline <?php echo $current_group !== 'admin' ? 'is-active' : ''; ?>"><i class="fas fa-earth-americas"></i> <?php echo t('admin_banners_group_global'); ?></a>
            <a href="admin_banners.php?group=admin" class="btn-outline <?php echo $current_group === 'admin' ? 'is-active' : ''; ?>"><i class="fas fa-user-shield"></i> <?php echo t('admin_banners_group_admin'); ?></a>
        </div>

        <div class="banner-page-picker">
            <?php foreach ($group_pages as $key => $info): ?>
                <a href="admin_banners.php?page=<?php echo urlencode($key); ?>" class="banner-page-picker-item<?php echo $key === $current_page ? ' is-active' : ''; ?>">
                    <?php if (isset($active_pages[$key])): ?><i class="fas fa-circle-check banner-page-picker-dot"></i><?php endif; ?>
                    <?php echo htmlspecialchars($info['label']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <h2 class="admin-section-title"><i class="fas fa-panorama"></i> <?php echo htmlspecialchars($manageable_pages[$current_page]['label']); ?></h2>

        <?php if ($active_banner): ?>
            <div class="admin-banner-active-notice">
                <i class="fas fa-circle-check"></i>
                <span><?php echo t('admin_banners_active_notice_page'); ?></span>
                <form action="admin_banners.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_banners_confirm_deactivate')); ?>">
                    <input type="hidden" name="page_key" value="<?php echo htmlspecialchars($current_page); ?>">
                    <button type="submit" name="deactivate_banner" value="1" class="btn-outline"><?php echo t('admin_banners_desactiver'); ?></button>
                </form>
            </div>
        <?php else: ?>
            <div class="admin-banner-active-notice admin-banner-active-notice-off">
                <i class="fas fa-circle-info"></i>
                <span><?php echo t('admin_banners_aucune_active_page'); ?></span>
            </div>
        <?php endif; ?>

        <form action="admin_banners.php" method="POST" enctype="multipart/form-data" class="admin-upload-photos-form">
            <input type="hidden" name="page_key" value="<?php echo htmlspecialchars($current_page); ?>">
            <label class="upload-zone upload-zone-multi" id="banners-multi-upload-zone">
                <i class="fas fa-cloud-arrow-up"></i>
                <span class="upload-zone-text"><?php echo t('admin_galerie_multi_upload_text'); ?></span>
                <span class="upload-zone-filename" id="banners-multi-upload-filenames"></span>
                <input type="file" name="new_banners[]" accept="image/*,video/*" class="upload-zone-input" id="banners-multi-upload-input" multiple>
            </label>
            <p class="admin-field-hint"><?php echo t('admin_banners_upload_hint'); ?></p>
            <button type="submit" name="upload_banners" class="btn-add-item"><i class="fas fa-upload"></i> <?php echo t('admin_banners_ajouter'); ?></button>
        </form>

        <?php if (empty($banners)): ?>
            <p class="gallery-empty"><i class="fas fa-image"></i> <?php echo t('admin_banners_aucune'); ?></p>
        <?php else: ?>
            <div class="photo-admin-grid">
                <?php foreach ($banners as $banner):
                    $is_video = $banner['media_type'] === 'video';
                ?>
                    <div class="photo-admin-item<?php echo $is_video ? ' is-video' : ''; ?><?php echo $banner['is_active'] ? ' is-active-banner' : ''; ?>">
                        <div class="photo-admin-thumb">
                            <?php if ($is_video): ?>
                                <video src="<?php echo htmlspecialchars($banner['media_path']); ?>" muted></video>
                                <span class="video-play-badge"><i class="fas fa-play"></i></span>
                            <?php else: ?>
                                <img src="<?php echo htmlspecialchars($banner['media_path']); ?>" alt="<?php echo htmlspecialchars($banner['title']); ?>">
                            <?php endif; ?>
                            <?php if ($banner['is_active']): ?><span class="banner-active-badge"><i class="fas fa-star"></i> <?php echo t('admin_banners_actif_badge'); ?></span><?php endif; ?>
                            <form action="admin_banners.php" method="POST" class="delete-form js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_galerie_confirm_delete_photo')); ?>">
                                <input type="hidden" name="page_key" value="<?php echo htmlspecialchars($current_page); ?>">
                                <button type="submit" name="delete_banner_id" value="<?php echo $banner['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </div>
                        <div class="gallery-admin-info">
                            <strong><?php echo htmlspecialchars($banner['title']); ?></strong>
                            <?php if (!$banner['is_active']): ?>
                                <form action="admin_banners.php" method="POST" class="admin-set-banner-form">
                                    <input type="hidden" name="page_key" value="<?php echo htmlspecialchars($current_page); ?>">
                                    <button type="submit" name="set_active_banner_id" value="<?php echo $banner['id']; ?>" class="btn-outline"><?php echo t('admin_banners_activer_page'); ?></button>
                                </form>
                            <?php endif; ?>
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
            if (input.files.length >= 1) {
                filenameSpan.textContent = input.files.length > 1 ? (input.files.length + ' fichiers sélectionnés') : input.files[0].name;
                zone.classList.add('has-file');
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>
