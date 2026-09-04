<?php
include_once 'language.php';
require_once 'db_connect.php';
require_once 'messagerie_functions.php';
require_once 'dm_functions.php';

dm_require_login();
$self_id = (int) $_SESSION['user_id'];

// --- Détermine la conversation active : soit ?with=<ami_id> (créée si besoin, ami requis),
// soit ?id=<conversation_id> (déjà existante) ---
$active_conversation_id = 0;
if (isset($_GET['with'])) {
    $with_id = (int) $_GET['with'];
    if (amis_get_statut($mysqli, $self_id, $with_id) === 'amis') {
        $active_conversation_id = dm_get_or_create_conversation($mysqli, $self_id, $with_id);
    }
} elseif (isset($_GET['id'])) {
    $active_conversation_id = (int) $_GET['id'];
}

$active = null;
$other = null;
$messages = [];
if ($active_conversation_id) {
    [$self, $active, $other_id] = dm_require_participant($mysqli, $active_conversation_id);
    $other = $mysqli->query("SELECT id, nom, avatar_path, last_activity FROM utilisateurs WHERE id = $other_id")->fetch_assoc();

    // Marque immédiatement comme lus les messages de l'autre participant à l'ouverture.
    $mysqli->query("UPDATE dm_messages SET read_at = NOW() WHERE conversation_id = " . (int) $active_conversation_id . " AND sender_id != " . (int) $self_id . " AND read_at IS NULL");

    $stmt = $mysqli->prepare("SELECT m.id, m.sender_id, m.content, m.created_at, m.read_at
                               FROM dm_messages m
                               LEFT JOIN dm_message_hides h ON h.message_id = m.id AND h.user_id = ?
                               WHERE m.conversation_id = ? AND h.message_id IS NULL AND m.deleted_for_everyone_at IS NULL
                               ORDER BY m.id DESC LIMIT 80");
    $stmt->bind_param("ii", $self_id, $active_conversation_id);
    $stmt->execute();
    $messages = array_reverse($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    $stmt->close();
    foreach ($messages as &$msg) {
        $att = $mysqli->query("SELECT file_path, original_name, file_type, mime_type, file_size FROM dm_attachments WHERE message_id = " . (int) $msg['id']);
        $msg['attachments'] = $att->fetch_all(MYSQLI_ASSOC);
        $msg['sender_nom'] = (int) $msg['sender_id'] === $self_id ? $_SESSION['user_nom'] : $other['nom'];
        $msg['sender_avatar'] = (int) $msg['sender_id'] === $self_id ? ($_SESSION['user_avatar'] ?? '') : $other['avatar_path'];
    }
    unset($msg);
}

$conversations = dm_get_conversations($mysqli, $self_id);
$last_message_id = !empty($messages) ? (int) end($messages)['id'] : 0;

// --- Amis sans conversation encore ouverte : affichés sous les conversations existantes pour
// permettre de démarrer une discussion directement depuis cette page (recherche unique amis+fils).
$conversation_partner_ids = array_map('intval', array_column(array_column($conversations, 'other'), 'id'));
$amis_sans_conversation = array_filter(amis_get_liste($mysqli, $self_id), function ($a) use ($conversation_partner_ids) {
    return !in_array((int) $a['id'], $conversation_partner_ids, true);
});

$page_title = t('dm_titre');
include 'header.php';
?>

<div class="page-content messagerie-page-content">
    <div class="dm-layout">
        <aside class="dm-conversations-list">
            <h2><i class="fas fa-comments"></i> <?php echo t('dm_titre'); ?></h2>
            <div class="dm-search-wrap">
                <i class="fas fa-magnifying-glass"></i>
                <input type="search" id="dm-search-input" placeholder="<?php echo t('dm_rechercher_placeholder'); ?>" autocomplete="off">
            </div>
            <?php if (empty($conversations) && empty($amis_sans_conversation)): ?>
                <p class="messagerie-panel-empty"><?php echo t('dm_aucune_conversation'); ?></p>
            <?php else: ?>
                <div id="dm-conversation-items">
                    <?php foreach ($conversations as $c): ?>
                        <a href="messages_prives.php?id=<?php echo $c['id']; ?>" class="dm-conversation-item <?php echo $active_conversation_id === $c['id'] ? 'is-active' : ''; ?>" data-search-name="<?php echo htmlspecialchars(mb_strtolower($c['other']['nom'])); ?>">
                            <img src="<?php echo $c['other']['avatar_path'] ? htmlspecialchars($c['other']['avatar_path']) : 'images/teachers/default-avatar.svg'; ?>" alt="" class="dm-conversation-avatar" onerror="this.src='images/teachers/default-avatar.svg'">
                            <div class="dm-conversation-info">
                                <strong><?php echo htmlspecialchars($c['other']['nom']); ?></strong>
                                <span><?php echo $c['last_message'] ? htmlspecialchars(mb_strimwidth((string) $c['last_message']['content'], 0, 40, '...')) : t('dm_pas_de_message'); ?></span>
                            </div>
                            <?php if ($c['unread'] > 0): ?><span class="groupe-unread-badge"><?php echo $c['unread'] > 99 ? '99+' : $c['unread']; ?></span><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                    <?php foreach ($amis_sans_conversation as $a): ?>
                        <a href="messages_prives.php?with=<?php echo (int) $a['id']; ?>" class="dm-conversation-item" data-search-name="<?php echo htmlspecialchars(mb_strtolower($a['nom'])); ?>">
                            <img src="<?php echo $a['avatar_path'] ? htmlspecialchars($a['avatar_path']) : 'images/teachers/default-avatar.svg'; ?>" alt="" class="dm-conversation-avatar" onerror="this.src='images/teachers/default-avatar.svg'">
                            <div class="dm-conversation-info">
                                <strong><?php echo htmlspecialchars($a['nom']); ?></strong>
                                <span class="dm-conversation-nouvelle"><?php echo t('dm_nouvelle_conversation'); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <p class="messagerie-panel-empty dm-search-no-result" id="dm-search-no-result" style="display:none;"><?php echo t('annuaire_aucun_resultat'); ?></p>
                </div>
            <?php endif; ?>
        </aside>

        <?php if (!$active): ?>
            <div class="messagerie-shell dm-empty-shell">
                <div class="messagerie-empty-state">
                    <i class="fas fa-comments"></i>
                    <p><?php echo t('dm_choisir_conversation'); ?></p>
                    <a href="annuaire.php" class="btn-add-item"><i class="fas fa-magnifying-glass"></i> <?php echo t('annuaire_titre'); ?></a>
                </div>
            </div>
        <?php else: ?>
            <div class="messagerie-shell" id="dm-shell"
                 data-conversation-id="<?php echo (int) $active_conversation_id; ?>"
                 data-self-id="<?php echo (int) $self_id; ?>"
                 data-last-id="<?php echo $last_message_id; ?>">

                <header class="messagerie-header">
                    <div class="messagerie-header-identity">
                        <div class="messagerie-avatar-wrap">
                            <img src="<?php echo $other['avatar_path'] ? htmlspecialchars($other['avatar_path']) : 'images/teachers/default-avatar.svg'; ?>" alt="" class="messagerie-avatar" onerror="this.src='images/teachers/default-avatar.svg'">
                            <span class="messagerie-status-dot" id="dm-status-dot"></span>
                        </div>
                        <div>
                            <h1><a href="profil_public.php?id=<?php echo (int) $other['id']; ?>"><?php echo htmlspecialchars($other['nom']); ?></a></h1>
                            <p class="messagerie-status-text" id="dm-status-text"><?php echo messagerie_is_online($other['last_activity']) ? t('messagerie_actif_maintenant') : t('messagerie_hors_ligne'); ?></p>
                        </div>
                    </div>
                    <div class="messagerie-header-actions">
                        <button type="button" class="messagerie-icon-btn" id="btn-open-medias" title="<?php echo t('dm_medias_titre'); ?>"><i class="fas fa-photo-film"></i></button>
                    </div>
                </header>

                <div class="messagerie-body">
                    <div class="messagerie-messages" id="messagerie-messages">
                        <?php foreach ($messages as $msg): ?>
                            <?php include 'dm_message_partial.php'; ?>
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
        <?php endif; ?>

        <!-- Panneau latéral : Médias partagés dans la conversation -->
        <div class="messagerie-panel-overlay" id="messagerie-panel-overlay"></div>
        <aside class="messagerie-side-panel" id="dm-medias-panel">
            <div class="messagerie-side-panel-header">
                <h2><i class="fas fa-photo-film"></i> <?php echo t('dm_medias_titre'); ?></h2>
                <button type="button" class="messagerie-icon-btn" id="btn-close-medias"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="dm-media-grid" id="dm-media-grid"></div>
        </aside>

        <!-- Fenêtre centrée : options de suppression -->
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
</div>

<script>
    window.DM_LABELS = {
        erreur_envoi: <?php echo json_encode(t('messagerie_erreur_envoi')); ?>,
        erreur: <?php echo json_encode(t('messagerie_erreur')); ?>,
        telecharger: <?php echo json_encode(t('messagerie_telecharger')); ?>,
        options: <?php echo json_encode(t('messagerie_options')); ?>,
        supprimer_pour_moi: <?php echo json_encode(t('messagerie_supprimer_pour_moi')); ?>,
        supprimer_pour_tous: <?php echo json_encode(t('messagerie_supprimer_pour_tous')); ?>,
        titre_suppr_message: <?php echo json_encode(t('messagerie_titre_suppr_message')); ?>,
        desc_suppr_message: <?php echo json_encode(t('messagerie_desc_suppr_message')); ?>,
        actif_maintenant: <?php echo json_encode(t('messagerie_actif_maintenant')); ?>,
        hors_ligne: <?php echo json_encode(t('messagerie_hors_ligne')); ?>,
        plus_amis: <?php echo json_encode(t('dm_plus_amis')); ?>,
        medias_vide: <?php echo json_encode(t('dm_medias_vide')); ?>,
        chargement: <?php echo json_encode(t('messagerie_chargement')); ?>
    };
</script>
<script src="<?php echo SITE_URL; ?>/messages_prives.js?v=<?php echo filemtime(__DIR__ . '/messages_prives.js'); ?>"></script>

<?php include 'footer.php'; ?>
