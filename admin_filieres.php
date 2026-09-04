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

function fil_unlink_if_upload($path) {
    if ($path && file_exists($path) && strpos($path, 'uploads/') === 0) {
        unlink($path);
    }
}

function fil_slugify($text) {
    $text = trim((string) $text);
    $translit = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    if ($translit !== false) { $text = $translit; }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

// --- Traitement des actions POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Créer / mettre à jour une filière ---
    if (isset($_POST['save_filiere'])) {
        $filiere_id   = isset($_POST['filiere_id']) && ctype_digit($_POST['filiere_id']) ? (int) $_POST['filiere_id'] : 0;
        $code         = trim($_POST['code'] ?? '');
        $mention      = trim($_POST['mention'] ?? '');
        $niveaux_allowed = ['L1', 'L2', 'L3', 'M1', 'M2'];
        $niveaux      = implode(',', array_intersect($niveaux_allowed, $_POST['niveaux'] ?? []));
        $slug_input   = trim($_POST['slug'] ?? '');
        $nom_fr       = trim($_POST['nom_fr'] ?? '');
        $nom_en       = trim($_POST['nom_en'] ?? '');
        $nom_mg       = trim($_POST['nom_mg'] ?? '');
        $desc_fr      = trim($_POST['description_fr'] ?? '');
        $desc_en      = trim($_POST['description_en'] ?? '');
        $desc_mg      = trim($_POST['description_mg'] ?? '');
        $debouches_fr = trim($_POST['debouches_fr'] ?? '');
        $debouches_en = trim($_POST['debouches_en'] ?? '');
        $debouches_mg = trim($_POST['debouches_mg'] ?? '');
        $historique_fr = trim($_POST['historique_fr'] ?? '');
        $historique_en = trim($_POST['historique_en'] ?? '');
        $historique_mg = trim($_POST['historique_mg'] ?? '');
        $avantages_fr = trim($_POST['avantages_fr'] ?? '');
        $avantages_en = trim($_POST['avantages_en'] ?? '');
        $avantages_mg = trim($_POST['avantages_mg'] ?? '');
        $display_order = ctype_digit((string) ($_POST['display_order'] ?? '')) ? (int) $_POST['display_order'] : 0;

        $slug = fil_slugify($slug_input);
        $redirect_view = $filiere_id > 0 ? "view=edit&id=$filiere_id" : "view=edit";

        if ($nom_fr === '') {
            header("Location: admin_filieres.php?$redirect_view&flash=" . urlencode('error|' . t('admin_filieres_error_nom_requis')));
            exit;
        }
        if ($slug === '') {
            header("Location: admin_filieres.php?$redirect_view&flash=" . urlencode('error|' . t('admin_filieres_error_slug_requis')));
            exit;
        }

        // Vérification préalable de l'unicité du slug (hors soi-même)
        $check = $mysqli->prepare("SELECT id FROM filieres WHERE slug = ? AND id != ?");
        $notId = $filiere_id ?: 0;
        $check->bind_param("si", $slug, $notId);
        $check->execute();
        $slug_taken = $check->get_result()->num_rows > 0;
        $check->close();

        if ($slug_taken) {
            header("Location: admin_filieres.php?$redirect_view&flash=" . urlencode('error|' . t('admin_filieres_error_slug_duplique')));
            exit;
        }

        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && in_array($_FILES['image']['type'], $allowed_types)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_name = 'filiere_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_name)) {
                $image_path = $upload_dir . $new_name;
            }
        }

        try {
            if ($filiere_id > 0) {
                if ($image_path) {
                    $old = $mysqli->query("SELECT image_path FROM filieres WHERE id = $filiere_id")->fetch_assoc();
                    if ($old) fil_unlink_if_upload($old['image_path']);
                    $stmt = $mysqli->prepare("UPDATE filieres SET code=?, mention=?, niveaux=?, slug=?, nom_fr=?, nom_en=?, nom_mg=?, description_fr=?, description_en=?, description_mg=?, debouches_fr=?, debouches_en=?, debouches_mg=?, historique_fr=?, historique_en=?, historique_mg=?, avantages_fr=?, avantages_en=?, avantages_mg=?, image_path=?, display_order=? WHERE id=?");
                    $stmt->bind_param("ssssssssssssssssssssii", $code, $mention, $niveaux, $slug, $nom_fr, $nom_en, $nom_mg, $desc_fr, $desc_en, $desc_mg, $debouches_fr, $debouches_en, $debouches_mg, $historique_fr, $historique_en, $historique_mg, $avantages_fr, $avantages_en, $avantages_mg, $image_path, $display_order, $filiere_id);
                } else {
                    $stmt = $mysqli->prepare("UPDATE filieres SET code=?, mention=?, niveaux=?, slug=?, nom_fr=?, nom_en=?, nom_mg=?, description_fr=?, description_en=?, description_mg=?, debouches_fr=?, debouches_en=?, debouches_mg=?, historique_fr=?, historique_en=?, historique_mg=?, avantages_fr=?, avantages_en=?, avantages_mg=?, display_order=? WHERE id=?");
                    $stmt->bind_param("sssssssssssssssssssii", $code, $mention, $niveaux, $slug, $nom_fr, $nom_en, $nom_mg, $desc_fr, $desc_en, $desc_mg, $debouches_fr, $debouches_en, $debouches_mg, $historique_fr, $historique_en, $historique_mg, $avantages_fr, $avantages_en, $avantages_mg, $display_order, $filiere_id);
                }
                $stmt->execute();
                $stmt->close();
                $flash = 'success|' . t('admin_filieres_updated');
            } else {
                $stmt = $mysqli->prepare("INSERT INTO filieres (code, mention, niveaux, slug, nom_fr, nom_en, nom_mg, description_fr, description_en, description_mg, debouches_fr, debouches_en, debouches_mg, historique_fr, historique_en, historique_mg, avantages_fr, avantages_en, avantages_mg, image_path, display_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->bind_param("ssssssssssssssssssssi", $code, $mention, $niveaux, $slug, $nom_fr, $nom_en, $nom_mg, $desc_fr, $desc_en, $desc_mg, $debouches_fr, $debouches_en, $debouches_mg, $historique_fr, $historique_en, $historique_mg, $avantages_fr, $avantages_en, $avantages_mg, $image_path, $display_order);
                $stmt->execute();
                $filiere_id = $mysqli->insert_id;
                $stmt->close();
                $flash = 'success|' . t('admin_filieres_created');
            }
        } catch (mysqli_sql_exception $e) {
            // Filet de sécurité si la contrainte UNIQUE sur slug est déclenchée malgré la pré-vérification.
            header("Location: admin_filieres.php?$redirect_view&flash=" . urlencode('error|' . t('admin_filieres_error_slug_duplique')));
            exit;
        }

        header("Location: admin_filieres.php?view=edit&id=$filiere_id&flash=" . urlencode($flash));
        exit;
    }

    // --- Ajouter un bloc de paragraphe au contenu de la filière ---
    if (isset($_POST['add_filiere_text_block'])) {
        $filiere_id = (int) $_POST['filiere_id'];
        $content_fr = trim($_POST['block_content_fr'] ?? '');
        $content_en = trim($_POST['block_content_en'] ?? '');
        $content_mg = trim($_POST['block_content_mg'] ?? '');
        if ($content_fr !== '') {
            $maxOrderRow = $mysqli->query("SELECT COALESCE(MAX(display_order),0) AS m FROM filiere_blocks WHERE filiere_id = $filiere_id")->fetch_assoc();
            $order = (int) $maxOrderRow['m'] + 1;
            $stmt = $mysqli->prepare("INSERT INTO filiere_blocks (filiere_id, block_type, content_fr, content_en, content_mg, display_order) VALUES (?, 'text', ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $filiere_id, $content_fr, $content_en, $content_mg, $order);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: admin_filieres.php?view=edit&id=$filiere_id&flash=" . urlencode('success|Paragraphe ajouté.'));
        exit;
    }

    // --- Ajouter un bloc image au contenu de la filière ---
    if (isset($_POST['add_filiere_image_block']) && isset($_FILES['block_image'])) {
        $filiere_id = (int) $_POST['filiere_id'];
        $caption_fr = trim($_POST['block_caption_fr'] ?? '');
        $caption_en = trim($_POST['block_caption_en'] ?? '');
        $caption_mg = trim($_POST['block_caption_mg'] ?? '');
        if ($_FILES['block_image']['error'] == 0 && in_array($_FILES['block_image']['type'], $allowed_types)) {
            $ext = pathinfo($_FILES['block_image']['name'], PATHINFO_EXTENSION);
            $new_name = 'filiere_block_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['block_image']['tmp_name'], $upload_dir . $new_name)) {
                $path = $upload_dir . $new_name;
                $maxOrderRow = $mysqli->query("SELECT COALESCE(MAX(display_order),0) AS m FROM filiere_blocks WHERE filiere_id = $filiere_id")->fetch_assoc();
                $order = (int) $maxOrderRow['m'] + 1;
                $stmt = $mysqli->prepare("INSERT INTO filiere_blocks (filiere_id, block_type, image_path, caption_fr, caption_en, caption_mg, display_order) VALUES (?, 'image', ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssi", $filiere_id, $path, $caption_fr, $caption_en, $caption_mg, $order);
                $stmt->execute();
                $stmt->close();
            }
        }
        header("Location: admin_filieres.php?view=edit&id=$filiere_id&flash=" . urlencode('success|Image ajoutée.'));
        exit;
    }

    // --- Déplacer un bloc vers le haut ou le bas (échange d'ordre avec son voisin) ---
    if (isset($_POST['move_filiere_block_id'])) {
        $block_id = (int) $_POST['move_filiere_block_id'];
        $filiere_id = (int) $_POST['filiere_id'];
        $direction = $_POST['direction'] ?? '';
        $current = $mysqli->query("SELECT id, display_order FROM filiere_blocks WHERE id = $block_id")->fetch_assoc();
        if ($current) {
            $neighbor = $direction === 'up'
                ? $mysqli->query("SELECT id, display_order FROM filiere_blocks WHERE filiere_id = $filiere_id AND display_order < {$current['display_order']} ORDER BY display_order DESC LIMIT 1")->fetch_assoc()
                : $mysqli->query("SELECT id, display_order FROM filiere_blocks WHERE filiere_id = $filiere_id AND display_order > {$current['display_order']} ORDER BY display_order ASC LIMIT 1")->fetch_assoc();
            if ($neighbor) {
                $stmt = $mysqli->prepare("UPDATE filiere_blocks SET display_order = ? WHERE id = ?");
                $stmt->bind_param("ii", $neighbor['display_order'], $current['id']);
                $stmt->execute();
                $stmt->bind_param("ii", $current['display_order'], $neighbor['id']);
                $stmt->execute();
                $stmt->close();
            }
        }
        header("Location: admin_filieres.php?view=edit&id=$filiere_id&flash=" . urlencode('success|Ordre mis à jour.'));
        exit;
    }

    // --- Supprimer un bloc de contenu de la filière ---
    if (isset($_POST['delete_filiere_block_id'])) {
        $block_id = (int) $_POST['delete_filiere_block_id'];
        $filiere_id = (int) $_POST['filiere_id'];
        $old = $mysqli->query("SELECT image_path FROM filiere_blocks WHERE id = $block_id")->fetch_assoc();
        if ($old) fil_unlink_if_upload($old['image_path']);
        $stmt = $mysqli->prepare("DELETE FROM filiere_blocks WHERE id = ?");
        $stmt->bind_param("i", $block_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_filieres.php?view=edit&id=$filiere_id&flash=" . urlencode('success|Bloc supprimé.'));
        exit;
    }

    // --- Supprimer une filière ---
    if (isset($_POST['delete_filiere_id'])) {
        $filiere_id = (int) $_POST['delete_filiere_id'];
        $old = $mysqli->query("SELECT image_path FROM filieres WHERE id = $filiere_id")->fetch_assoc();
        if ($old) fil_unlink_if_upload($old['image_path']);
        $stmt = $mysqli->prepare("DELETE FROM filieres WHERE id = ?");
        $stmt->bind_param("i", $filiere_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_filieres.php?flash=" . urlencode('success|' . t('admin_filieres_deleted')));
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

$page_title = t('admin_filieres_breadcrumb');

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
            <span><?php echo t('admin_filieres_breadcrumb'); ?></span>
        </nav>
        <h1><?php echo t('admin_filieres_breadcrumb'); ?></h1>
        <p><?php echo t('admin_filieres_soustitre'); ?></p>
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
            $filiere_id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;
            $filiere = null;
            if ($filiere_id > 0) {
                $stmt = $mysqli->prepare("SELECT * FROM filieres WHERE id = ?");
                $stmt->bind_param("i", $filiere_id);
                $stmt->execute();
                $filiere = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }
            $filiere_blocks = [];
            if ($filiere_id > 0) {
                $stmt = $mysqli->prepare("SELECT * FROM filiere_blocks WHERE filiere_id = ? ORDER BY display_order ASC, id ASC");
                $stmt->bind_param("i", $filiere_id);
                $stmt->execute();
                $filiere_blocks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
        ?>
            <a href="admin_filieres.php" class="admin-galerie-back"><i class="fas fa-chevron-left"></i> <?php echo t('admin_filieres_retour_liste'); ?></a>
            <h2 class="admin-section-title"><?php echo $filiere ? t('admin_filieres_modifier_filiere') : t('admin_filieres_nouvelle_filiere'); ?></h2>

            <form action="admin_filieres.php" method="POST" enctype="multipart/form-data" class="admin-form admin-album-form">
                <?php if ($filiere): ?><input type="hidden" name="filiere_id" value="<?php echo (int) $filiere['id']; ?>"><?php endif; ?>

                <div class="album-form-grid">
                    <div class="album-form-main">
                        <div class="form-group">
                            <label><?php echo t('admin_filieres_nom_label'); ?></label>
                            <div class="translatable-field-group" data-group-id="filiere-nom">
                                <div class="lang-tabs">
                                    <button type="button" class="lang-tab is-active" data-lang="fr">🇫🇷 FR</button>
                                    <button type="button" class="lang-tab" data-lang="en">🇬🇧 EN</button>
                                    <button type="button" class="lang-tab" data-lang="mg">🇲🇬 MG</button>
                                    <button type="button" class="btn-translate-all" title="Traduire automatiquement le FR vers l'EN et le MG"><i class="fas fa-language"></i> Traduire</button>
                                </div>
                                <div class="lang-panel is-active" data-lang="fr"><input type="text" name="nom_fr" id="filiere-nom-fr" value="<?php echo htmlspecialchars($filiere['nom_fr'] ?? ''); ?>" data-lang-input="fr" placeholder="<?php echo t('admin_filieres_nom_fr_placeholder'); ?>" required></div>
                                <div class="lang-panel" data-lang="en"><input type="text" name="nom_en" value="<?php echo htmlspecialchars($filiere['nom_en'] ?? ''); ?>" data-lang-input="en" placeholder="<?php echo t('admin_filieres_nom_en_placeholder'); ?>"></div>
                                <div class="lang-panel" data-lang="mg"><input type="text" name="nom_mg" value="<?php echo htmlspecialchars($filiere['nom_mg'] ?? ''); ?>" data-lang-input="mg" placeholder="<?php echo t('admin_filieres_nom_mg_placeholder'); ?>"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_filieres_slug_label'); ?> <span class="admin-field-hint"><?php echo t('admin_filieres_slug_hint'); ?></span></label>
                            <input type="text" name="slug" id="filiere-slug" value="<?php echo htmlspecialchars($filiere['slug'] ?? ''); ?>" placeholder="ex-genie-informatique">
                        </div>

                        <div class="album-form-row">
                            <div class="form-group">
                                <label><?php echo t('admin_filieres_code_label'); ?></label>
                                <input type="text" name="code" value="<?php echo htmlspecialchars($filiere['code'] ?? ''); ?>" placeholder="<?php echo t('admin_filieres_code_placeholder'); ?>">
                            </div>
                            <div class="form-group">
                                <label><?php echo t('admin_filieres_mention_label'); ?></label>
                                <input type="text" name="mention" list="mention-suggestions" value="<?php echo htmlspecialchars($filiere['mention'] ?? ''); ?>" placeholder="<?php echo t('admin_filieres_mention_placeholder'); ?>">
                                <datalist id="mention-suggestions">
                                    <option value="STNPA">
                                    <option value="STI">
                                    <option value="GC">
                                </datalist>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_filieres_niveaux_label'); ?></label>
                            <?php
                                $filiere_niveaux_actuels = !empty($filiere['niveaux']) ? explode(',', $filiere['niveaux']) : [];
                            ?>
                            <div class="admin-niveaux-checkboxes">
                                <?php foreach (['L1', 'L2', 'L3', 'M1', 'M2'] as $niv): ?>
                                    <label class="admin-niveau-pill">
                                        <input type="checkbox" name="niveaux[]" value="<?php echo $niv; ?>" <?php echo in_array($niv, $filiere_niveaux_actuels, true) ? 'checked' : ''; ?>>
                                        <span><?php echo $niv; ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_description_label'); ?></label>
                            <div class="translatable-field-group" data-group-id="filiere-desc">
                                <div class="lang-tabs">
                                    <button type="button" class="lang-tab is-active" data-lang="fr">🇫🇷 FR</button>
                                    <button type="button" class="lang-tab" data-lang="en">🇬🇧 EN</button>
                                    <button type="button" class="lang-tab" data-lang="mg">🇲🇬 MG</button>
                                    <button type="button" class="btn-translate-all" title="Traduire automatiquement le FR vers l'EN et le MG"><i class="fas fa-language"></i> Traduire</button>
                                </div>
                                <div class="lang-panel is-active" data-lang="fr"><textarea name="description_fr" rows="5" data-lang-input="fr" placeholder="<?php echo t('admin_filieres_description_placeholder'); ?>"><?php echo htmlspecialchars($filiere['description_fr'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="en"><textarea name="description_en" rows="5" data-lang-input="en"><?php echo htmlspecialchars($filiere['description_en'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="mg"><textarea name="description_mg" rows="5" data-lang-input="mg"><?php echo htmlspecialchars($filiere['description_mg'] ?? ''); ?></textarea></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_filieres_debouches_label'); ?></label>
                            <div class="translatable-field-group" data-group-id="filiere-debouches">
                                <div class="lang-tabs">
                                    <button type="button" class="lang-tab is-active" data-lang="fr">🇫🇷 FR</button>
                                    <button type="button" class="lang-tab" data-lang="en">🇬🇧 EN</button>
                                    <button type="button" class="lang-tab" data-lang="mg">🇲🇬 MG</button>
                                    <button type="button" class="btn-translate-all" title="Traduire automatiquement le FR vers l'EN et le MG"><i class="fas fa-language"></i> Traduire</button>
                                </div>
                                <div class="lang-panel is-active" data-lang="fr"><textarea name="debouches_fr" rows="5" data-lang-input="fr" placeholder="<?php echo t('admin_filieres_debouches_placeholder'); ?>"><?php echo htmlspecialchars($filiere['debouches_fr'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="en"><textarea name="debouches_en" rows="5" data-lang-input="en"><?php echo htmlspecialchars($filiere['debouches_en'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="mg"><textarea name="debouches_mg" rows="5" data-lang-input="mg"><?php echo htmlspecialchars($filiere['debouches_mg'] ?? ''); ?></textarea></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_filieres_historique_label'); ?></label>
                            <p class="admin-field-hint"><?php echo t('admin_filieres_historique_hint'); ?></p>
                            <div class="translatable-field-group" data-group-id="filiere-historique">
                                <div class="lang-tabs">
                                    <button type="button" class="lang-tab is-active" data-lang="fr">🇫🇷 FR</button>
                                    <button type="button" class="lang-tab" data-lang="en">🇬🇧 EN</button>
                                    <button type="button" class="lang-tab" data-lang="mg">🇲🇬 MG</button>
                                    <button type="button" class="btn-translate-all" title="Traduire automatiquement le FR vers l'EN et le MG"><i class="fas fa-language"></i> Traduire</button>
                                </div>
                                <div class="lang-panel is-active" data-lang="fr"><textarea name="historique_fr" rows="5" data-lang-input="fr" placeholder="<?php echo t('admin_filieres_historique_placeholder'); ?>"><?php echo htmlspecialchars($filiere['historique_fr'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="en"><textarea name="historique_en" rows="5" data-lang-input="en"><?php echo htmlspecialchars($filiere['historique_en'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="mg"><textarea name="historique_mg" rows="5" data-lang-input="mg"><?php echo htmlspecialchars($filiere['historique_mg'] ?? ''); ?></textarea></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_filieres_avantages_label'); ?></label>
                            <p class="admin-field-hint"><?php echo t('admin_filieres_avantages_hint'); ?></p>
                            <div class="translatable-field-group" data-group-id="filiere-avantages">
                                <div class="lang-tabs">
                                    <button type="button" class="lang-tab is-active" data-lang="fr">🇫🇷 FR</button>
                                    <button type="button" class="lang-tab" data-lang="en">🇬🇧 EN</button>
                                    <button type="button" class="lang-tab" data-lang="mg">🇲🇬 MG</button>
                                    <button type="button" class="btn-translate-all" title="Traduire automatiquement le FR vers l'EN et le MG"><i class="fas fa-language"></i> Traduire</button>
                                </div>
                                <div class="lang-panel is-active" data-lang="fr"><textarea name="avantages_fr" rows="5" data-lang-input="fr" placeholder="<?php echo t('admin_filieres_avantages_placeholder'); ?>"><?php echo htmlspecialchars($filiere['avantages_fr'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="en"><textarea name="avantages_en" rows="5" data-lang-input="en"><?php echo htmlspecialchars($filiere['avantages_en'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="mg"><textarea name="avantages_mg" rows="5" data-lang-input="mg"><?php echo htmlspecialchars($filiere['avantages_mg'] ?? ''); ?></textarea></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_filieres_ordre_label'); ?></label>
                            <input type="number" name="display_order" value="<?php echo (int) ($filiere['display_order'] ?? 0); ?>" min="0" step="1" style="max-width: 160px;">
                        </div>
                    </div>

                    <div class="album-form-side">
                        <div class="form-group upload-zone-group">
                            <label><?php echo t('admin_filieres_image_label'); ?></label>
                            <?php if (!empty($filiere['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($filiere['image_path']); ?>" class="album-cover-preview" alt="Image actuelle">
                            <?php endif; ?>
                            <label class="upload-zone">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <span class="upload-zone-text"><?php echo t('admin_choisir_image_deposer'); ?></span>
                                <span class="upload-zone-filename"></span>
                                <input type="file" name="image" accept="image/jpeg, image/png, image/jpg, image/webp" class="upload-zone-input">
                            </label>
                        </div>
                        <?php if ($filiere): ?>
                            <a href="filiere_detail.php?slug=<?php echo urlencode($filiere['slug']); ?>" target="_blank" rel="noopener" class="btn-outline" style="display:block; text-align:center; margin-top: 10px;">
                                <i class="fas fa-arrow-up-right-from-square"></i> <?php echo t('admin_filieres_voir_page_publique'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" name="save_filiere" class="btn-submit"><i class="fas fa-save"></i> <?php echo $filiere ? t('admin_enregistrer_modifications') : t('admin_filieres_creer_filiere'); ?></button>
            </form>

            <?php if ($filiere): ?>
                <div class="admin-photos-section">
                    <h2 class="admin-section-title"><i class="fas fa-align-left"></i> <?php echo t('admin_filieres_blocs_titre'); ?></h2>
                    <p class="admin-field-hint"><?php echo t('admin_filieres_blocs_hint'); ?></p>

                    <div class="admin-block-add-forms">
                        <form action="admin_filieres.php" method="POST" class="admin-block-form">
                            <input type="hidden" name="filiere_id" value="<?php echo (int) $filiere['id']; ?>">
                            <label><i class="fas fa-paragraph"></i> <?php echo t('admin_filieres_ajouter_paragraphe'); ?></label>
                            <div class="translatable-field-group" data-group-id="filiere-block-text">
                                <div class="lang-tabs">
                                    <button type="button" class="lang-tab is-active" data-lang="fr">🇫🇷 FR</button>
                                    <button type="button" class="lang-tab" data-lang="en">🇬🇧 EN</button>
                                    <button type="button" class="lang-tab" data-lang="mg">🇲🇬 MG</button>
                                    <button type="button" class="btn-translate-all" title="Traduire automatiquement le FR vers l'EN et le MG"><i class="fas fa-language"></i> Traduire</button>
                                </div>
                                <div class="lang-panel is-active" data-lang="fr"><textarea name="block_content_fr" rows="6" data-lang-input="fr" placeholder="<?php echo t('admin_filieres_paragraphe_placeholder'); ?>"></textarea></div>
                                <div class="lang-panel" data-lang="en"><textarea name="block_content_en" rows="6" data-lang-input="en"></textarea></div>
                                <div class="lang-panel" data-lang="mg"><textarea name="block_content_mg" rows="6" data-lang-input="mg"></textarea></div>
                            </div>
                            <button type="submit" name="add_filiere_text_block" class="btn-add-item"><i class="fas fa-plus"></i> <?php echo t('admin_filieres_ajouter_paragraphe'); ?></button>
                        </form>

                        <form action="admin_filieres.php" method="POST" enctype="multipart/form-data" class="admin-block-form">
                            <input type="hidden" name="filiere_id" value="<?php echo (int) $filiere['id']; ?>">
                            <label><i class="fas fa-image"></i> <?php echo t('admin_filieres_ajouter_image'); ?></label>
                            <label class="upload-zone">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <span class="upload-zone-text"><?php echo t('admin_choisir_image_deposer'); ?></span>
                                <span class="upload-zone-filename"></span>
                                <input type="file" name="block_image" accept="image/jpeg, image/png, image/jpg, image/webp" class="upload-zone-input">
                            </label>
                            <input type="text" name="block_caption_fr" placeholder="<?php echo t('admin_filieres_legende_placeholder'); ?>">
                            <button type="submit" name="add_filiere_image_block" class="btn-add-item"><i class="fas fa-plus"></i> <?php echo t('admin_filieres_ajouter_image'); ?></button>
                        </form>
                    </div>

                    <?php if (empty($filiere_blocks)): ?>
                        <p class="gallery-empty"><i class="fas fa-align-left"></i> <?php echo t('admin_filieres_aucun_bloc'); ?></p>
                    <?php else: ?>
                        <div class="admin-block-list">
                            <?php foreach ($filiere_blocks as $i => $block): ?>
                                <div class="admin-block-item">
                                    <div class="admin-block-item-preview">
                                        <?php if ($block['block_type'] === 'image'): ?>
                                            <img src="<?php echo htmlspecialchars($block['image_path']); ?>" alt="">
                                            <span class="admin-block-type-badge"><i class="fas fa-image"></i> <?php echo t('admin_filieres_bloc_image'); ?><?php echo $block['caption_fr'] ? ' — ' . htmlspecialchars($block['caption_fr']) : ''; ?></span>
                                        <?php else: ?>
                                            <p><?php echo htmlspecialchars(mb_strimwidth($block['content_fr'], 0, 220, '…')); ?></p>
                                            <span class="admin-block-type-badge"><i class="fas fa-paragraph"></i> <?php echo t('admin_filieres_bloc_texte'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="admin-block-item-actions">
                                        <form action="admin_filieres.php?view=edit&id=<?php echo $filiere['id']; ?>" method="POST">
                                            <input type="hidden" name="filiere_id" value="<?php echo $filiere['id']; ?>">
                                            <input type="hidden" name="move_filiere_block_id" value="<?php echo $block['id']; ?>">
                                            <button type="submit" name="direction" value="up" class="btn-outline" title="<?php echo t('admin_filieres_monter'); ?>" <?php echo $i === 0 ? 'disabled' : ''; ?>><i class="fas fa-arrow-up"></i></button>
                                        </form>
                                        <form action="admin_filieres.php?view=edit&id=<?php echo $filiere['id']; ?>" method="POST">
                                            <input type="hidden" name="filiere_id" value="<?php echo $filiere['id']; ?>">
                                            <input type="hidden" name="move_filiere_block_id" value="<?php echo $block['id']; ?>">
                                            <button type="submit" name="direction" value="down" class="btn-outline" title="<?php echo t('admin_filieres_descendre'); ?>" <?php echo $i === count($filiere_blocks) - 1 ? 'disabled' : ''; ?>><i class="fas fa-arrow-down"></i></button>
                                        </form>
                                        <form action="admin_filieres.php?view=edit&id=<?php echo $filiere['id']; ?>" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_filieres_confirm_delete_bloc')); ?>">
                                            <input type="hidden" name="filiere_id" value="<?php echo $filiere['id']; ?>">
                                            <button type="submit" name="delete_filiere_block_id" value="<?php echo $block['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
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
            $all_filieres = $mysqli->query("SELECT * FROM filieres ORDER BY display_order ASC, id ASC")->fetch_all(MYSQLI_ASSOC);

            $stats = $mysqli->query("SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN image_path IS NOT NULL AND image_path != '' THEN 1 ELSE 0 END) AS avec_image,
                SUM(CASE WHEN debouches_fr IS NOT NULL AND debouches_fr != '' THEN 1 ELSE 0 END) AS avec_debouches
                FROM filieres
            ")->fetch_assoc();
        ?>

            <div class="admin-galerie-stats">
                <div class="admin-stat-card"><i class="fas fa-graduation-cap"></i><div><strong><?php echo (int) $stats['total']; ?></strong><span><?php echo t('admin_filieres_total'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-image"></i><div><strong><?php echo (int) $stats['avec_image']; ?></strong><span><?php echo t('admin_filieres_avec_image'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-briefcase"></i><div><strong><?php echo (int) $stats['avec_debouches']; ?></strong><span><?php echo t('admin_filieres_avec_debouches'); ?></span></div></div>
            </div>

            <div class="admin-galerie-toolbar">
                <a href="admin_filieres.php?view=edit" class="btn-add-item admin-new-album-btn"><i class="fas fa-plus"></i> <?php echo t('admin_filieres_nouvelle_filiere'); ?></a>
            </div>

            <?php if (empty($all_filieres)): ?>
                <p class="gallery-empty"><i class="fas fa-graduation-cap"></i> <?php echo t('admin_filieres_aucune_filiere'); ?></p>
            <?php else: ?>
                <div class="admin-album-list">
                    <?php foreach ($all_filieres as $filiere): ?>
                        <div class="admin-album-row">
                            <?php if (!empty($filiere['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($filiere['image_path']); ?>" alt="" class="admin-album-row-thumb">
                            <?php else: ?>
                                <div class="admin-album-row-thumb admin-filiere-thumb-placeholder"><i class="fas fa-graduation-cap"></i></div>
                            <?php endif; ?>
                            <div class="admin-album-row-info">
                                <h4><?php echo htmlspecialchars($filiere['nom_fr']); ?></h4>
                                <div class="admin-album-row-meta">
                                    <?php if ($filiere['code']): ?><span><i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($filiere['code']); ?></span><?php endif; ?>
                                    <span><i class="fas fa-link"></i> <?php echo htmlspecialchars($filiere['slug']); ?></span>
                                    <?php if (!empty($filiere['niveaux'])): ?><span><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($filiere['niveaux']); ?></span><?php endif; ?>
                                </div>
                            </div>
                            <?php if ($filiere['mention']): ?><span class="mention-badge"><?php echo htmlspecialchars($filiere['mention']); ?></span><?php endif; ?>
                            <div class="admin-album-row-actions">
                                <a href="filiere_detail.php?slug=<?php echo urlencode($filiere['slug']); ?>" target="_blank" rel="noopener" class="btn-outline" title="<?php echo t('admin_filieres_voir_page_publique'); ?>"><i class="fas fa-arrow-up-right-from-square"></i></a>
                                <a href="admin_filieres.php?view=edit&id=<?php echo $filiere['id']; ?>" class="btn-outline" title="<?php echo t('admin_modifier'); ?>"><i class="fas fa-pen"></i></a>
                                <form action="admin_filieres.php" method="POST" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_filieres_confirm_delete')); ?>">
                                    <button type="submit" name="delete_filiere_id" value="<?php echo $filiere['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
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

    // Génération automatique du slug à partir du nom FR (uniquement si le champ slug est vide)
    const nomInput = document.getElementById('filiere-nom-fr');
    const slugInput = document.getElementById('filiere-slug');
    if (nomInput && slugInput) {
        nomInput.addEventListener('blur', () => {
            if (slugInput.value.trim() === '' && nomInput.value.trim() !== '') {
                slugInput.value = nomInput.value.trim()
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
