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
$valid_categories = ['general', 'examen', 'ceremonie', 'atelier', 'vacances', 'inscription'];
$flash = null;

function evt_unlink_if_upload($path) {
    if ($path && file_exists($path) && strpos($path, 'uploads/') === 0) {
        unlink($path);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Créer / mettre à jour un événement ---
    if (isset($_POST['save_event'])) {
        $event_id = isset($_POST['event_id']) && ctype_digit($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
        $titre_fr = trim($_POST['titre_fr'] ?? '');
        $titre_en = trim($_POST['titre_en'] ?? '');
        $titre_mg = trim($_POST['titre_mg'] ?? '');
        $description_fr = trim($_POST['description_fr'] ?? '');
        $description_en = trim($_POST['description_en'] ?? '');
        $description_mg = trim($_POST['description_mg'] ?? '');
        $date_debut = !empty($_POST['date_debut']) ? str_replace('T', ' ', $_POST['date_debut']) . ':00' : '';
        $date_fin = !empty($_POST['date_fin']) ? str_replace('T', ' ', $_POST['date_fin']) . ':00' : null;
        $lieu = trim($_POST['lieu'] ?? '');
        $categorie = in_array($_POST['categorie'] ?? '', $valid_categories, true) ? $_POST['categorie'] : 'general';

        if ($titre_fr === '' || $date_debut === '') {
            $flash = ['type' => 'error', 'msg' => t('admin_evenements_champs_obligatoires')];
            header("Location: admin_evenements.php?view=" . ($event_id ? "edit&id=$event_id" : "edit") . "&flash=" . urlencode($flash['type'] . '|' . $flash['msg']));
            exit;
        }

        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && in_array($_FILES['image']['type'], $allowed_img_types)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_name = 'event_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_name)) {
                $image_path = $upload_dir . $new_name;
            }
        }

        if ($event_id > 0) {
            if ($image_path) {
                $old = $mysqli->query("SELECT image_path FROM evenements WHERE id = $event_id")->fetch_assoc();
                if ($old) evt_unlink_if_upload($old['image_path']);
                $stmt = $mysqli->prepare("UPDATE evenements SET titre_fr=?, titre_en=?, titre_mg=?, description_fr=?, description_en=?, description_mg=?, date_debut=?, date_fin=?, lieu=?, categorie=?, image_path=? WHERE id=?");
                $stmt->bind_param("sssssssssssi", $titre_fr, $titre_en, $titre_mg, $description_fr, $description_en, $description_mg, $date_debut, $date_fin, $lieu, $categorie, $image_path, $event_id);
            } else {
                $stmt = $mysqli->prepare("UPDATE evenements SET titre_fr=?, titre_en=?, titre_mg=?, description_fr=?, description_en=?, description_mg=?, date_debut=?, date_fin=?, lieu=?, categorie=? WHERE id=?");
                $stmt->bind_param("ssssssssssi", $titre_fr, $titre_en, $titre_mg, $description_fr, $description_en, $description_mg, $date_debut, $date_fin, $lieu, $categorie, $event_id);
            }
            $stmt->execute();
            $stmt->close();
            $flash = ['type' => 'success', 'msg' => t('admin_evenements_maj_reussie')];
        } else {
            $stmt = $mysqli->prepare("INSERT INTO evenements (titre_fr, titre_en, titre_mg, description_fr, description_en, description_mg, date_debut, date_fin, lieu, categorie, image_path) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssssssss", $titre_fr, $titre_en, $titre_mg, $description_fr, $description_en, $description_mg, $date_debut, $date_fin, $lieu, $categorie, $image_path);
            $stmt->execute();
            $event_id = $mysqli->insert_id;
            $stmt->close();
            $flash = ['type' => 'success', 'msg' => t('admin_evenements_creation_reussie')];
        }
        header("Location: admin_evenements.php?view=edit&id=$event_id&flash=" . urlencode($flash['type'] . '|' . $flash['msg']));
        exit;
    }

    // --- Supprimer un événement ---
    if (isset($_POST['delete_event_id'])) {
        $event_id = (int) $_POST['delete_event_id'];
        $old = $mysqli->query("SELECT image_path FROM evenements WHERE id = $event_id")->fetch_assoc();
        if ($old) evt_unlink_if_upload($old['image_path']);
        $stmt = $mysqli->prepare("DELETE FROM evenements WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_evenements.php?flash=" . urlencode('success|' . t('admin_evenements_suppression_reussie')));
        exit;
    }
}

if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

$view = $_GET['view'] ?? 'list';
$filter = $_GET['filter'] ?? 'venir';
if (!in_array($filter, ['venir', 'passes', 'tous'], true)) $filter = 'venir';

$category_icons = [
    'general' => 'fa-calendar-day', 'examen' => 'fa-file-pen', 'ceremonie' => 'fa-graduation-cap',
    'atelier' => 'fa-chalkboard-user', 'vacances' => 'fa-umbrella-beach', 'inscription' => 'fa-user-plus',
];

$page_title = t('admin_evenements_titre');
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
            <span><?php echo t('admin_evenements_titre'); ?></span>
        </nav>
        <h1><?php echo t('admin_evenements_titre'); ?></h1>
        <p><?php echo t('admin_evenements_soustitre'); ?></p>
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
            $event_id = (int) ($_GET['id'] ?? 0);
            $event = $event_id ? $mysqli->query("SELECT * FROM evenements WHERE id = $event_id")->fetch_assoc() : null;
            $date_debut_value = !empty($event['date_debut']) ? str_replace(' ', 'T', substr($event['date_debut'], 0, 16)) : '';
            $date_fin_value = !empty($event['date_fin']) ? str_replace(' ', 'T', substr($event['date_fin'], 0, 16)) : '';
        ?>
            <a href="admin_evenements.php" class="admin-galerie-back"><i class="fas fa-chevron-left"></i> <?php echo t('admin_evenements_retour_liste'); ?></a>
            <h2 class="admin-section-title"><?php echo $event ? t('admin_evenements_modifier') : t('admin_evenements_nouveau'); ?></h2>

            <form action="admin_evenements.php" method="POST" enctype="multipart/form-data" class="admin-form admin-album-form">
                <?php if ($event): ?><input type="hidden" name="event_id" value="<?php echo (int) $event['id']; ?>"><?php endif; ?>

                <div class="album-form-grid">
                    <div class="album-form-main">
                        <div class="form-group">
                            <label><?php echo t('admin_evenements_titre_label'); ?></label>
                            <div class="translatable-field-group" data-group-id="event-title">
                                <div class="lang-tabs">
                                    <button type="button" class="lang-tab is-active" data-lang="fr">🇫🇷 FR</button>
                                    <button type="button" class="lang-tab" data-lang="en">🇬🇧 EN</button>
                                    <button type="button" class="lang-tab" data-lang="mg">🇲🇬 MG</button>
                                    <button type="button" class="btn-translate-all"><i class="fas fa-language"></i> Traduire</button>
                                </div>
                                <div class="lang-panel is-active" data-lang="fr"><input type="text" name="titre_fr" value="<?php echo htmlspecialchars($event['titre_fr'] ?? ''); ?>" data-lang-input="fr" required></div>
                                <div class="lang-panel" data-lang="en"><input type="text" name="titre_en" value="<?php echo htmlspecialchars($event['titre_en'] ?? ''); ?>" data-lang-input="en"></div>
                                <div class="lang-panel" data-lang="mg"><input type="text" name="titre_mg" value="<?php echo htmlspecialchars($event['titre_mg'] ?? ''); ?>" data-lang-input="mg"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo t('admin_evenements_description_label'); ?></label>
                            <div class="translatable-field-group" data-group-id="event-desc">
                                <div class="lang-tabs">
                                    <button type="button" class="lang-tab is-active" data-lang="fr">🇫🇷 FR</button>
                                    <button type="button" class="lang-tab" data-lang="en">🇬🇧 EN</button>
                                    <button type="button" class="lang-tab" data-lang="mg">🇲🇬 MG</button>
                                    <button type="button" class="btn-translate-all"><i class="fas fa-language"></i> Traduire</button>
                                </div>
                                <div class="lang-panel is-active" data-lang="fr"><textarea name="description_fr" rows="5" data-lang-input="fr"><?php echo htmlspecialchars($event['description_fr'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="en"><textarea name="description_en" rows="5" data-lang-input="en"><?php echo htmlspecialchars($event['description_en'] ?? ''); ?></textarea></div>
                                <div class="lang-panel" data-lang="mg"><textarea name="description_mg" rows="5" data-lang-input="mg"><?php echo htmlspecialchars($event['description_mg'] ?? ''); ?></textarea></div>
                            </div>
                        </div>

                        <div class="album-form-row">
                            <div class="form-group">
                                <label><?php echo t('admin_evenements_date_debut_label'); ?></label>
                                <input type="datetime-local" name="date_debut" value="<?php echo htmlspecialchars($date_debut_value); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><?php echo t('admin_evenements_date_fin_label'); ?> <span class="admin-field-hint"><?php echo t('admin_evenements_date_fin_hint'); ?></span></label>
                                <input type="datetime-local" name="date_fin" value="<?php echo htmlspecialchars($date_fin_value); ?>">
                            </div>
                        </div>

                        <div class="album-form-row">
                            <div class="form-group">
                                <label><?php echo t('admin_evenements_lieu_label'); ?></label>
                                <input type="text" name="lieu" value="<?php echo htmlspecialchars($event['lieu'] ?? ''); ?>" placeholder="<?php echo t('admin_evenements_lieu_placeholder'); ?>">
                            </div>
                            <div class="form-group">
                                <label><?php echo t('admin_categorie_label'); ?></label>
                                <select name="categorie">
                                    <?php foreach ($valid_categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>" <?php echo (($event['categorie'] ?? 'general') === $cat) ? 'selected' : ''; ?>><?php echo t('admin_evenements_categorie_' . $cat); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="album-form-side">
                        <div class="form-group upload-zone-group">
                            <label><?php echo t('admin_evenements_image_label'); ?></label>
                            <?php if (!empty($event['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($event['image_path']); ?>" class="album-cover-preview" alt="Image actuelle">
                            <?php endif; ?>
                            <label class="upload-zone">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <span class="upload-zone-text"><?php echo t('admin_choisir_image_deposer'); ?></span>
                                <span class="upload-zone-filename"></span>
                                <input type="file" name="image" accept="image/jpeg, image/png, image/jpg, image/webp" class="upload-zone-input">
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" name="save_event" class="btn-submit"><i class="fas fa-save"></i> <?php echo $event ? t('admin_enregistrer_modifications') : t('admin_evenements_creer'); ?></button>
            </form>

        <?php else:
            $where_clause = '';
            if ($filter === 'venir') { $where_clause = "WHERE date_debut >= NOW() ORDER BY date_debut ASC"; }
            elseif ($filter === 'passes') { $where_clause = "WHERE date_debut < NOW() ORDER BY date_debut DESC"; }
            else { $where_clause = "ORDER BY date_debut DESC"; }
            $all_events = $mysqli->query("SELECT * FROM evenements $where_clause")->fetch_all(MYSQLI_ASSOC);
            $total_events = (int) $mysqli->query("SELECT COUNT(*) c FROM evenements")->fetch_assoc()['c'];
            $upcoming_count = (int) $mysqli->query("SELECT COUNT(*) c FROM evenements WHERE date_debut >= NOW()")->fetch_assoc()['c'];
        ?>
            <div class="admin-galerie-stats">
                <div class="admin-stat-card"><i class="fas fa-calendar-days"></i><div><strong><?php echo $total_events; ?></strong><span><?php echo t('admin_evenements_total'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-hourglass-half"></i><div><strong><?php echo $upcoming_count; ?></strong><span><?php echo t('admin_evenements_a_venir'); ?></span></div></div>
            </div>

            <div class="admin-galerie-toolbar etu-tabs">
                <a href="admin_evenements.php?filter=venir" class="btn-outline <?php echo $filter === 'venir' ? 'is-active' : ''; ?>"><i class="fas fa-hourglass-half"></i> <?php echo t('admin_evenements_a_venir'); ?></a>
                <a href="admin_evenements.php?filter=passes" class="btn-outline <?php echo $filter === 'passes' ? 'is-active' : ''; ?>"><i class="fas fa-clock-rotate-left"></i> <?php echo t('admin_evenements_passes'); ?></a>
                <a href="admin_evenements.php?filter=tous" class="btn-outline <?php echo $filter === 'tous' ? 'is-active' : ''; ?>"><i class="fas fa-list"></i> <?php echo t('admin_evenements_tous'); ?></a>
                <a href="admin_evenements.php?view=edit" class="btn-add-item admin-new-album-btn"><i class="fas fa-plus"></i> <?php echo t('admin_evenements_nouveau'); ?></a>
            </div>

            <?php if (empty($all_events)): ?>
                <p class="gallery-empty"><i class="fas fa-calendar-days"></i> <?php echo t('admin_evenements_aucun'); ?></p>
            <?php else: ?>
                <div class="admin-album-list">
                    <?php foreach ($all_events as $e): ?>
                        <div class="admin-album-row">
                            <?php if (!empty($e['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($e['image_path']); ?>" alt="" class="admin-album-row-thumb">
                            <?php else: ?>
                                <div class="admin-album-row-thumb" style="display:flex;align-items:center;justify-content:center;background:var(--box-bg-color-alt,#f1f1f1);"><i class="fas <?php echo $category_icons[$e['categorie']] ?? 'fa-calendar-day'; ?>" style="font-size:1.6rem;color:var(--secondary-color);"></i></div>
                            <?php endif; ?>
                            <div class="admin-album-row-info">
                                <h4><?php echo htmlspecialchars($e['titre_fr']); ?></h4>
                                <div class="admin-album-row-meta">
                                    <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($e['date_debut'])); ?></span>
                                    <?php if (!empty($e['lieu'])): ?><span><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($e['lieu']); ?></span><?php endif; ?>
                                    <span class="documents-badge-etudiant"><i class="fas <?php echo $category_icons[$e['categorie']] ?? 'fa-calendar-day'; ?>"></i> <?php echo t('admin_evenements_categorie_' . $e['categorie']); ?></span>
                                </div>
                            </div>
                            <div class="admin-album-row-actions">
                                <a href="admin_evenements.php?view=edit&id=<?php echo $e['id']; ?>" class="btn-outline" title="<?php echo t('admin_modifier'); ?>"><i class="fas fa-pen"></i></a>
                                <form action="admin_evenements.php" method="POST" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_evenements_supprimer_confirm')); ?>">
                                    <button type="submit" name="delete_event_id" value="<?php echo (int) $e['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
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
            if (input.files.length === 1) {
                filenameSpan.textContent = input.files[0].name;
                zone.classList.add('has-file');
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>
