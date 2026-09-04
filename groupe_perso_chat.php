<?php
include_once 'language.php';
require_once 'db_connect.php';
require_once 'messagerie_functions.php';
require_once 'groupe_perso_functions.php';

$groupe_id = (int) ($_GET['id'] ?? 0);
[$self, $groupe, $membership] = groupe_perso_require_membership($mysqli, $groupe_id);

// Marque immédiatement le passage comme lu à l'ouverture (le sondage s'en charge aussi ensuite).
$mysqli->query("UPDATE groupe_utilisateurs_membres SET last_read_at = NOW() WHERE groupe_id = " . (int) $groupe_id . " AND user_id = " . (int) $self['id']);

$membres_count = (int) $mysqli->query("SELECT COUNT(*) c FROM groupe_utilisateurs_membres WHERE groupe_id = " . (int) $groupe_id . " AND is_banned = 0")->fetch_assoc()['c'];
$is_owner = (int) $groupe['createur_id'] === (int) $self['id'];

$stmt = $mysqli->prepare("SELECT m.id, m.sender_id, m.content, m.created_at, u.nom AS sender_nom, u.avatar_path AS sender_avatar
                           FROM groupe_utilisateurs_messages m
                           JOIN utilisateurs u ON u.id = m.sender_id
                           LEFT JOIN groupe_utilisateurs_hides h ON h.message_id = m.id AND h.user_id = ?
                           WHERE m.groupe_id = ? AND h.message_id IS NULL AND m.deleted_for_everyone_at IS NULL
                           ORDER BY m.id DESC LIMIT 80");
$stmt->bind_param("ii", $self['id'], $groupe_id);
$stmt->execute();
$messages = array_reverse($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
$stmt->close();
foreach ($messages as &$msg) {
    $att = $mysqli->query("SELECT file_path, original_name, file_type, mime_type, file_size FROM groupe_utilisateurs_attachments WHERE message_id = " . (int) $msg['id']);
    $msg['attachments'] = $att->fetch_all(MYSQLI_ASSOC);
}
unset($msg);

$last_message_id = !empty($messages) ? (int) end($messages)['id'] : 0;
$page_title = htmlspecialchars($groupe['nom']);
include 'header.php';
?>

<div class="page-content messagerie-page-content">
    <div class="messagerie-shell" id="groupe-perso-shell"
         data-groupe-id="<?php echo (int) $groupe_id; ?>"
         data-self-id="<?php echo (int) $self['id']; ?>"
         data-last-id="<?php echo $last_message_id; ?>">

        <header class="messagerie-header">
            <div class="messagerie-header-identity">
                <a href="mes_groupes_perso.php" class="messagerie-icon-btn groupe-back-btn" title="<?php echo t('groupe_perso_titre'); ?>"><i class="fas fa-arrow-left"></i></a>
                <div class="groupe-header-icon"><i class="fas fa-people-group"></i></div>
                <div>
                    <h1><?php echo htmlspecialchars($groupe['nom']); ?></h1>
                    <p class="messagerie-status-text"><i class="fas fa-user-group"></i> <?php echo $membres_count; ?> <?php echo t('groupe_membres'); ?><?php if ($is_owner): ?> · <?php echo t('groupe_code_label'); ?>: <strong><?php echo htmlspecialchars($groupe['code_unique']); ?></strong><?php endif; ?></p>
                </div>
            </div>
            <div class="messagerie-header-actions">
                <button type="button" class="messagerie-icon-btn" id="btn-open-membres" title="<?php echo t('groupe_membres_titre'); ?>"><i class="fas fa-users"></i></button>
                <?php if ($is_owner): ?>
                    <form action="mes_groupes_perso.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('groupe_confirm_suppr')); ?>">
                        <button type="submit" name="delete_group_id" value="<?php echo (int) $groupe_id; ?>" class="messagerie-icon-btn" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                    </form>
                <?php else: ?>
                    <form action="mes_groupes_perso.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('groupe_confirm_quitter')); ?>">
                        <button type="submit" name="leave_group_id" value="<?php echo (int) $groupe_id; ?>" class="messagerie-icon-btn" title="<?php echo t('groupe_quitter'); ?>"><i class="fas fa-right-from-bracket"></i></button>
                    </form>
                <?php endif; ?>
            </div>
        </header>

        <div class="messagerie-body">
            <div class="messagerie-messages" id="messagerie-messages">
                <?php foreach ($messages as $msg): ?>
                    <?php include 'groupe_perso_message_partial.php'; ?>
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

    <!-- Panneau latéral : Membres du groupe -->
    <div class="messagerie-panel-overlay" id="messagerie-panel-overlay"></div>
    <aside class="messagerie-side-panel" id="groupe-membres-panel">
        <div class="messagerie-side-panel-header">
            <h2><i class="fas fa-users"></i> <?php echo t('groupe_membres_titre'); ?></h2>
            <button type="button" class="messagerie-icon-btn" id="btn-close-membres"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="groupe-membres-list" id="groupe-membres-list"></div>
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
    window.GROUPE_PERSO_LABELS = {
        erreur_envoi: <?php echo json_encode(t('messagerie_erreur_envoi')); ?>,
        erreur: <?php echo json_encode(t('messagerie_erreur')); ?>,
        telecharger: <?php echo json_encode(t('messagerie_telecharger')); ?>,
        options: <?php echo json_encode(t('messagerie_options')); ?>,
        supprimer_pour_moi: <?php echo json_encode(t('messagerie_supprimer_pour_moi')); ?>,
        supprimer_pour_tous: <?php echo json_encode(t('messagerie_supprimer_pour_tous')); ?>,
        titre_suppr_message: <?php echo json_encode(t('messagerie_titre_suppr_message')); ?>,
        desc_suppr_message: <?php echo json_encode(t('messagerie_desc_suppr_message')); ?>,
        chargement: <?php echo json_encode(t('messagerie_chargement')); ?>,
        aucun_membre: <?php echo json_encode(t('groupe_aucun_membre')); ?>,
        bannir: <?php echo json_encode(t('groupe_bannir')); ?>,
        retablir: <?php echo json_encode(t('groupe_retablir')); ?>,
        confirm_bannir: <?php echo json_encode(t('groupe_confirm_bannir')); ?>,
        confirm_retablir: <?php echo json_encode(t('groupe_confirm_retablir')); ?>,
        banni_badge: <?php echo json_encode(t('groupe_banni_badge')); ?>
    };
    window.GROUPE_PERSO_ID = <?php echo (int) $groupe_id; ?>;
    window.GROUPE_PERSO_IS_OWNER = <?php echo $is_owner ? 'true' : 'false'; ?>;
</script>
<script src="<?php echo SITE_URL; ?>/groupe_perso.js?v=<?php echo filemtime(__DIR__ . '/groupe_perso.js'); ?>"></script>

<?php include 'footer.php'; ?>
