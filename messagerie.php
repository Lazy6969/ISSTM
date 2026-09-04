<?php
include_once 'language.php';
require_once 'db_connect.php';
require_once 'messagerie_functions.php';

[$self, $others] = messagerie_require_access($mysqli);

// Marque immédiatement comme lus les messages des autres participants à l'ouverture de la page
// (le sondage périodique s'en charge aussi ensuite, mais on ne veut pas attendre le premier
// cycle pour refléter la lecture).
if (!empty($others)) {
    $mysqli->query("UPDATE messagerie_messages SET read_at = NOW() WHERE sender_id != " . (int) $self['id'] . " AND read_at IS NULL");
}

$messages = [];
if (!empty($others)) {
    $result = $mysqli->query("SELECT m.id, m.sender_id, m.content, m.created_at, m.read_at, u.nom AS sender_nom, u.avatar_path AS sender_avatar
                               FROM messagerie_messages m
                               JOIN utilisateurs u ON u.id = m.sender_id
                               LEFT JOIN messagerie_message_hides h ON h.message_id = m.id AND h.user_id = " . (int) $self['id'] . "
                               WHERE h.message_id IS NULL AND m.deleted_for_everyone_at IS NULL
                               ORDER BY m.id DESC LIMIT 80");
    $messages = array_reverse($result->fetch_all(MYSQLI_ASSOC));
    foreach ($messages as &$msg) {
        $att = $mysqli->query("SELECT file_path, original_name, file_type, mime_type, file_size FROM messagerie_attachments WHERE message_id = " . (int) $msg['id']);
        $msg['attachments'] = $att->fetch_all(MYSQLI_ASSOC);
    }
    unset($msg);
}

$last_message_id = !empty($messages) ? (int) end($messages)['id'] : 0;
$page_title = t('messagerie_titre');
include 'header.php';
?>

<div class="page-content messagerie-page-content">
    <div class="messagerie-shell" id="messagerie-shell"
         data-self-id="<?php echo (int) $self['id']; ?>"
         data-last-id="<?php echo $last_message_id; ?>"
         data-other-ids="<?php echo htmlspecialchars(json_encode(array_column($others, 'id'))); ?>">

        <?php if (empty($others)): ?>
            <div class="messagerie-empty-state">
                <i class="fas fa-comments"></i>
                <p><?php echo t('messagerie_pas_interlocuteur'); ?></p>
            </div>
        <?php else: ?>

        <header class="messagerie-header">
            <div class="messagerie-header-identity">
                <div class="messagerie-group-avatars">
                    <?php foreach ($others as $person): ?>
                        <div class="messagerie-avatar-wrap">
                            <img src="<?php echo $person['avatar_path'] ? htmlspecialchars($person['avatar_path']) : 'images/teachers/default-avatar.svg'; ?>" alt="" class="messagerie-avatar" onerror="this.src='images/teachers/default-avatar.svg'">
                            <span class="messagerie-status-dot" data-user-id="<?php echo (int) $person['id']; ?>"></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div>
                    <h1><?php echo htmlspecialchars(implode(', ', array_column($others, 'nom'))); ?></h1>
                    <p class="messagerie-status-text" id="messagerie-status-text"><?php echo t('messagerie_statut_chargement'); ?></p>
                </div>
            </div>
            <div class="messagerie-header-actions">
                <button type="button" class="messagerie-icon-btn" id="btn-open-search" title="<?php echo t('messagerie_rechercher'); ?>"><i class="fas fa-magnifying-glass"></i></button>
                <button type="button" class="messagerie-icon-btn" id="btn-open-media" title="<?php echo t('messagerie_medias_titre'); ?>"><i class="fas fa-photo-film"></i></button>
                <button type="button" class="messagerie-icon-btn" id="btn-open-conversation-menu" title="<?php echo t('messagerie_options'); ?>"><i class="fas fa-ellipsis-vertical"></i></button>
            </div>
        </header>

        <div class="messagerie-body">
            <div class="messagerie-messages" id="messagerie-messages">
                <?php foreach ($messages as $msg): ?>
                    <?php include 'messagerie_message_partial.php'; ?>
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

        <?php endif; ?>
    </div>

    <!-- Panneau latéral : Médias & fichiers partagés -->
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

    <!-- Panneau latéral : Recherche dans les messages -->
    <aside class="messagerie-side-panel" id="messagerie-search-panel">
        <div class="messagerie-side-panel-header">
            <h2><i class="fas fa-magnifying-glass"></i> <?php echo t('messagerie_rechercher'); ?></h2>
            <button type="button" class="messagerie-icon-btn" id="btn-close-search"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="messagerie-search-input-wrap">
            <input type="search" id="messagerie-search-input" placeholder="<?php echo t('messagerie_rechercher_placeholder'); ?>">
        </div>
        <div class="messagerie-search-results" id="messagerie-search-results"></div>
    </aside>

    <!-- Fenêtre centrée : options de suppression (message ou discussion entière) -->
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
    window.MESSAGERIE_LABELS = {
        lu: <?php echo json_encode(t('messagerie_lu')); ?>,
        envoye: <?php echo json_encode(t('messagerie_envoye')); ?>,
        actif_maintenant: <?php echo json_encode(t('messagerie_actif_maintenant')); ?>,
        hors_ligne: <?php echo json_encode(t('messagerie_hors_ligne')); ?>,
        erreur_envoi: <?php echo json_encode(t('messagerie_erreur_envoi')); ?>,
        chargement: <?php echo json_encode(t('messagerie_chargement')); ?>,
        aucun_media: <?php echo json_encode(t('messagerie_aucun_media')); ?>,
        aucun_resultat: <?php echo json_encode(t('messagerie_aucun_resultat')); ?>,
        erreur: <?php echo json_encode(t('messagerie_erreur')); ?>,
        telecharger: <?php echo json_encode(t('messagerie_telecharger')); ?>,
        options: <?php echo json_encode(t('messagerie_options')); ?>,
        supprimer_pour_moi: <?php echo json_encode(t('messagerie_supprimer_pour_moi')); ?>,
        supprimer_pour_tous: <?php echo json_encode(t('messagerie_supprimer_pour_tous')); ?>,
        confirm_suppr_moi: <?php echo json_encode(t('messagerie_confirm_suppr_moi')); ?>,
        confirm_suppr_tous: <?php echo json_encode(t('messagerie_confirm_suppr_tous')); ?>,
        confirm_effacer_discussion_moi: <?php echo json_encode(t('messagerie_confirm_effacer_discussion_moi')); ?>,
        confirm_suppr_discussion_tous: <?php echo json_encode(t('messagerie_confirm_suppr_discussion_tous')); ?>,
        annuler: <?php echo json_encode(t('admin_annuler')); ?>,
        effacer_pour_moi: <?php echo json_encode(t('messagerie_effacer_pour_moi')); ?>,
        titre_suppr_message: <?php echo json_encode(t('messagerie_titre_suppr_message')); ?>,
        desc_suppr_message: <?php echo json_encode(t('messagerie_desc_suppr_message')); ?>,
        titre_suppr_discussion: <?php echo json_encode(t('messagerie_titre_suppr_discussion')); ?>,
        desc_suppr_discussion: <?php echo json_encode(t('messagerie_desc_suppr_discussion')); ?>
    };
</script>
<script src="<?php echo SITE_URL; ?>/messagerie.js?v=<?php echo filemtime(__DIR__ . '/messagerie.js'); ?>"></script>

<?php include 'footer.php'; ?>
