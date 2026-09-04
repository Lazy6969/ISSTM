<?php
include_once 'language.php';
require_once 'db_connect.php';
require_once 'messagerie_functions.php';
require_once 'groupe_functions.php';

$groupe_id = (int) ($_GET['id'] ?? 0);
[$self, $groupe, $membership] = groupe_require_membership($mysqli, $groupe_id);

// Marque immédiatement le passage comme lu à l'ouverture (le sondage s'en charge aussi ensuite).
$mysqli->query("UPDATE groupe_membres SET last_read_at = NOW() WHERE groupe_id = " . (int) $groupe_id . " AND user_id = " . (int) $self['id']);

$membres_count = (int) $mysqli->query("SELECT COUNT(*) c FROM groupe_membres WHERE groupe_id = " . (int) $groupe_id . " AND is_banned = 0")->fetch_assoc()['c'];
$is_owner = (int) $groupe['enseignant_id'] === (int) $self['id'];
$is_teacher_here = $membership['role_in_group'] === 'enseignant';
$can_download_presence = groupe_can_download_presence($membership);

$presence_students = [];
if ($is_teacher_here) {
    $presence_students = $mysqli->query("SELECT u.id, u.nom FROM groupe_membres m JOIN utilisateurs u ON u.id = m.user_id
                                          WHERE m.groupe_id = " . (int) $groupe_id . " AND m.is_banned = 0 AND m.role_in_group = 'etudiant'
                                          ORDER BY u.nom ASC")->fetch_all(MYSQLI_ASSOC);
}

$stmt = $mysqli->prepare("SELECT m.id, m.sender_id, m.content, m.created_at, u.nom AS sender_nom, u.avatar_path AS sender_avatar, u.role AS sender_role
                           FROM groupe_messages m
                           JOIN utilisateurs u ON u.id = m.sender_id
                           LEFT JOIN groupe_message_hides h ON h.message_id = m.id AND h.user_id = ?
                           WHERE m.groupe_id = ? AND h.message_id IS NULL AND m.deleted_for_everyone_at IS NULL
                           ORDER BY m.id DESC LIMIT 80");
$stmt->bind_param("ii", $self['id'], $groupe_id);
$stmt->execute();
$messages = array_reverse($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
$stmt->close();
foreach ($messages as &$msg) {
    $att = $mysqli->query("SELECT file_path, original_name, file_type, mime_type, file_size FROM groupe_message_attachments WHERE message_id = " . (int) $msg['id']);
    $msg['attachments'] = $att->fetch_all(MYSQLI_ASSOC);
}
unset($msg);

$last_message_id = !empty($messages) ? (int) end($messages)['id'] : 0;
$page_title = htmlspecialchars($groupe['nom']);
include 'header.php';
?>

<div class="page-content messagerie-page-content">
    <div class="messagerie-shell" id="groupe-shell"
         data-groupe-id="<?php echo (int) $groupe_id; ?>"
         data-self-id="<?php echo (int) $self['id']; ?>"
         data-last-id="<?php echo $last_message_id; ?>">

        <header class="messagerie-header">
            <div class="messagerie-header-identity">
                <a href="mes_groupes.php" class="messagerie-icon-btn groupe-back-btn" title="<?php echo t('groupe_mes_groupes_titre'); ?>"><i class="fas fa-arrow-left"></i></a>
                <div class="groupe-header-icon"><i class="fas fa-people-group"></i></div>
                <div>
                    <h1><?php echo htmlspecialchars($groupe['nom']); ?></h1>
                    <p class="messagerie-status-text"><i class="fas fa-user-group"></i> <?php echo $membres_count; ?> <?php echo t('groupe_membres'); ?><?php if ($is_owner): ?> · <?php echo t('groupe_code_label'); ?>: <strong><?php echo htmlspecialchars($groupe['code_unique']); ?></strong><?php endif; ?></p>
                </div>
            </div>
            <div class="messagerie-header-actions">
                <button type="button" class="messagerie-icon-btn" id="btn-open-annonces" title="<?php echo t('groupe_annonces_titre'); ?>"><i class="fas fa-bullhorn"></i></button>
                <button type="button" class="messagerie-icon-btn" id="btn-open-membres" title="<?php echo t('groupe_membres_titre'); ?>"><i class="fas fa-users"></i></button>
                <button type="button" class="messagerie-icon-btn" id="btn-open-media" title="<?php echo t('messagerie_medias_titre'); ?>"><i class="fas fa-photo-film"></i></button>
                <button type="button" class="messagerie-icon-btn" id="btn-open-presence" title="<?php echo t('groupe_presence_titre'); ?>"><i class="fas fa-clipboard-check"></i></button>
                <?php if ($is_owner): ?>
                    <form action="mes_groupes.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('groupe_confirm_suppr')); ?>">
                        <button type="submit" name="delete_group_id" value="<?php echo (int) $groupe_id; ?>" class="messagerie-icon-btn" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                    </form>
                <?php else: ?>
                    <form action="mes_groupes.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('groupe_confirm_quitter')); ?>">
                        <button type="submit" name="leave_group_id" value="<?php echo (int) $groupe_id; ?>" class="messagerie-icon-btn" title="<?php echo t('groupe_quitter'); ?>"><i class="fas fa-right-from-bracket"></i></button>
                    </form>
                <?php endif; ?>
            </div>
        </header>

        <div class="messagerie-body">
            <div class="messagerie-messages" id="messagerie-messages">
                <?php foreach ($messages as $msg): ?>
                    <?php include 'groupe_message_partial.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <form id="messagerie-compose-form" class="messagerie-compose" enctype="multipart/form-data">
            <div class="messagerie-file-preview" id="messagerie-file-preview"></div>
            <div class="messagerie-compose-row">
                <button type="button" class="messagerie-icon-btn" id="btn-attach-file" title="<?php echo t('messagerie_joindre_fichier'); ?>"><i class="fas fa-paperclip"></i></button>
                <input type="file" id="messagerie-file-input" multiple hidden>
                <button type="button" class="messagerie-icon-btn" id="btn-toggle-emoji" title="<?php echo t('messagerie_emoji'); ?>"><i class="fas fa-face-smile"></i></button>
                <textarea id="messagerie-text-input" placeholder="<?php echo t('messagerie_placeholder_texte'); ?>" rows="1"></textarea>
                <button type="submit" class="messagerie-send-btn" title="<?php echo t('messagerie_envoyer'); ?>"><i class="fas fa-paper-plane"></i></button>
            </div>
            <div class="messagerie-emoji-panel" id="messagerie-emoji-panel"></div>
        </form>
    </div>

    <!-- Panneau latéral : Médias & fichiers partagés du groupe -->
    <div class="messagerie-panel-overlay" id="messagerie-panel-overlay"></div>
    <aside class="messagerie-side-panel" id="messagerie-media-panel">
        <div class="messagerie-side-panel-header">
            <h2><i class="fas fa-photo-film"></i> <?php echo t('messagerie_medias_titre'); ?></h2>
            <button type="button" class="messagerie-icon-btn" id="btn-close-media"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="messagerie-media-tabs">
            <button type="button" class="messagerie-media-tab is-active" data-type="all"><?php echo t('messagerie_tous'); ?></button>
            <button type="button" class="messagerie-media-tab" data-type="image"><?php echo t('messagerie_images'); ?></button>
            <button type="button" class="messagerie-media-tab" data-type="video"><?php echo t('messagerie_videos'); ?></button>
            <button type="button" class="messagerie-media-tab" data-type="file"><?php echo t('messagerie_fichiers'); ?></button>
        </div>
        <div class="messagerie-media-grid" id="messagerie-media-grid"></div>
    </aside>

    <!-- Panneau latéral : Membres du groupe -->
    <aside class="messagerie-side-panel" id="groupe-membres-panel">
        <div class="messagerie-side-panel-header">
            <h2><i class="fas fa-users"></i> <?php echo t('groupe_membres_titre'); ?></h2>
            <button type="button" class="messagerie-icon-btn" id="btn-close-membres"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="groupe-membres-list" id="groupe-membres-list"></div>
    </aside>

    <!-- Panneau latéral : Annonces (examens, résultats, devoirs...) -->
    <aside class="messagerie-side-panel" id="groupe-annonces-panel">
        <div class="messagerie-side-panel-header">
            <h2><i class="fas fa-bullhorn"></i> <?php echo t('groupe_annonces_titre'); ?></h2>
            <button type="button" class="messagerie-icon-btn" id="btn-close-annonces"><i class="fas fa-xmark"></i></button>
        </div>
        <?php if ($is_teacher_here): ?>
            <form id="groupe-annonce-form" class="groupe-annonce-form">
                <select name="type" id="annonce-type">
                    <option value="examen"><?php echo t('groupe_annonce_type_examen'); ?></option>
                    <option value="resultat"><?php echo t('groupe_annonce_type_resultat'); ?></option>
                    <option value="devoir"><?php echo t('groupe_annonce_type_devoir'); ?></option>
                    <option value="autre"><?php echo t('groupe_annonce_type_autre'); ?></option>
                </select>
                <input type="text" name="titre" id="annonce-titre" placeholder="<?php echo t('groupe_annonce_titre_placeholder'); ?>" required>
                <textarea name="description" id="annonce-description" placeholder="<?php echo t('groupe_annonce_description_placeholder'); ?>" rows="2"></textarea>
                <label class="groupe-annonce-date-label"><?php echo t('groupe_annonce_date_label'); ?> <input type="date" name="date_echeance" id="annonce-date"></label>
                <button type="submit" class="btn-add-item"><i class="fas fa-paper-plane"></i> <?php echo t('groupe_annonce_publier'); ?></button>
            </form>
        <?php endif; ?>
        <div class="groupe-annonces-list" id="groupe-annonces-list"></div>
    </aside>

    <!-- Panneau latéral : Liste de présence (séances datées + délégués) -->
    <aside class="messagerie-side-panel" id="groupe-presence-panel">
        <div class="messagerie-side-panel-header">
            <h2><i class="fas fa-clipboard-check"></i> <?php echo t('groupe_presence_titre'); ?></h2>
            <button type="button" class="messagerie-icon-btn" id="btn-close-presence"><i class="fas fa-xmark"></i></button>
        </div>
        <?php if ($is_teacher_here): ?>
            <form id="groupe-presence-form" class="groupe-presence-form">
                <div class="groupe-presence-form-head">
                    <strong><?php echo t('groupe_presence_nouvelle_seance'); ?></strong>
                    <label class="groupe-presence-date-label"><?php echo t('groupe_presence_date_label'); ?>
                        <input type="date" name="session_date" id="presence-session-date" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                    </label>
                </div>
                <?php if (empty($presence_students)): ?>
                    <p class="messagerie-panel-empty"><?php echo t('groupe_aucun_etudiant'); ?></p>
                <?php else: ?>
                    <div class="groupe-presence-checklist">
                        <?php foreach ($presence_students as $stu): ?>
                            <label class="groupe-presence-check-row">
                                <input type="checkbox" name="present_user_ids[]" value="<?php echo (int) $stu['id']; ?>" checked>
                                <?php echo htmlspecialchars($stu['nom']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn-add-item"><i class="fas fa-check"></i> <?php echo t('groupe_presence_enregistrer'); ?></button>
                <?php endif; ?>
            </form>
        <?php endif; ?>
        <div class="groupe-presence-list" id="groupe-presence-list" data-can-download="<?php echo $can_download_presence ? '1' : '0'; ?>" data-groupe-id="<?php echo (int) $groupe_id; ?>"></div>
    </aside>

    <!-- Fenêtre centrée : options de suppression (message) -->
    <div class="messagerie-confirm-overlay" id="messagerie-confirm-overlay">
        <div class="messagerie-confirm-modal">
            <button type="button" class="messagerie-confirm-close" id="messagerie-confirm-close"><i class="fas fa-xmark"></i></button>
            <div class="messagerie-confirm-icon"><i class="fas fa-trash-alt"></i></div>
            <h3 class="messagerie-confirm-title" id="messagerie-confirm-title"></h3>
            <p class="messagerie-confirm-desc" id="messagerie-confirm-desc"></p>
            <div class="messagerie-confirm-actions">
                <button type="button" class="messagerie-confirm-btn messagerie-confirm-btn-me" id="messagerie-confirm-btn-me"></button>
                <button type="button" class="messagerie-confirm-btn messagerie-confirm-btn-everyone" id="messagerie-confirm-btn-everyone"></button>
                <button type="button" class="messagerie-confirm-btn messagerie-confirm-btn-cancel" id="messagerie-confirm-btn-cancel"></button>
            </div>
        </div>
    </div>
</div>

<script>
    window.GROUPE_LABELS = {
        erreur_envoi: <?php echo json_encode(t('messagerie_erreur_envoi')); ?>,
        erreur: <?php echo json_encode(t('messagerie_erreur')); ?>,
        telecharger: <?php echo json_encode(t('messagerie_telecharger')); ?>,
        options: <?php echo json_encode(t('messagerie_options')); ?>,
        supprimer_pour_moi: <?php echo json_encode(t('messagerie_supprimer_pour_moi')); ?>,
        supprimer_pour_tous: <?php echo json_encode(t('messagerie_supprimer_pour_tous')); ?>,
        titre_suppr_message: <?php echo json_encode(t('messagerie_titre_suppr_message')); ?>,
        desc_suppr_message: <?php echo json_encode(t('messagerie_desc_suppr_message')); ?>,
        chargement: <?php echo json_encode(t('messagerie_chargement')); ?>,
        aucun_media: <?php echo json_encode(t('messagerie_aucun_media')); ?>,
        aucun_membre: <?php echo json_encode(t('groupe_aucun_membre')); ?>,
        bannir: <?php echo json_encode(t('groupe_bannir')); ?>,
        retablir: <?php echo json_encode(t('groupe_retablir')); ?>,
        confirm_bannir: <?php echo json_encode(t('groupe_confirm_bannir')); ?>,
        confirm_retablir: <?php echo json_encode(t('groupe_confirm_retablir')); ?>,
        banni_badge: <?php echo json_encode(t('groupe_banni_badge')); ?>,
        aucune_annonce: <?php echo json_encode(t('groupe_aucune_annonce')); ?>,
        confirm_suppr_annonce: <?php echo json_encode(t('groupe_confirm_suppr_annonce')); ?>,
        erreur_titre_requis: <?php echo json_encode(t('groupe_annonce_erreur_titre')); ?>,
        rendre_delegue: <?php echo json_encode(t('groupe_delegue_rendre')); ?>,
        retirer_delegue: <?php echo json_encode(t('groupe_delegue_retirer')); ?>,
        delegue_badge: <?php echo json_encode(t('groupe_delegue_badge')); ?>,
        confirm_rendre_delegue: <?php echo json_encode(t('groupe_confirm_rendre_delegue')); ?>,
        confirm_retirer_delegue: <?php echo json_encode(t('groupe_confirm_retirer_delegue')); ?>,
        presence_historique_vide: <?php echo json_encode(t('groupe_presence_historique_vide')); ?>,
        presence_count: <?php echo json_encode(t('groupe_presence_present_count')); ?>,
        presence_imprimer: <?php echo json_encode(t('groupe_presence_imprimer')); ?>,
        presence_imprimer_tout: <?php echo json_encode(t('groupe_presence_imprimer_tout')); ?>
    };
    window.GROUPE_ID = <?php echo (int) $groupe_id; ?>;
    window.GROUPE_IS_OWNER = <?php echo $is_owner ? 'true' : 'false'; ?>;
    window.GROUPE_IS_TEACHER_HERE = <?php echo $is_teacher_here ? 'true' : 'false'; ?>;
</script>
<script src="<?php echo SITE_URL; ?>/groupe.js?v=<?php echo filemtime(__DIR__ . '/groupe.js'); ?>"></script>

<?php include 'footer.php'; ?>
