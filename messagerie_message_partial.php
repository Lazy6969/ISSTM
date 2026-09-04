<?php
// Partial de rendu d'une bulle de message. Inclus depuis messagerie.php pour le chargement
// initial ; la même structure est reproduite en JS (messagerie.js::renderMessage) pour les
// messages reçus/envoyés en direct via le sondage et l'envoi AJAX.
$is_own = (int) $msg['sender_id'] === (int) $self['id'];
$avatar = $msg['sender_avatar'] ? htmlspecialchars($msg['sender_avatar']) : 'images/teachers/default-avatar.svg';
?>
<div class="messagerie-msg <?php echo $is_own ? 'is-own' : 'is-other'; ?>" data-id="<?php echo (int) $msg['id']; ?>">
    <img src="<?php echo $avatar; ?>" alt="" class="messagerie-msg-avatar" onerror="this.src='images/teachers/default-avatar.svg'">
    <div class="messagerie-msg-body">
        <span class="messagerie-msg-author"><?php echo htmlspecialchars($msg['sender_nom']); ?></span>
        <div class="messagerie-msg-bubble-row">
            <div class="messagerie-msg-bubble">
                <?php if (!empty($msg['content'])): ?>
                    <p class="messagerie-msg-text"><?php echo messagerie_linkify(nl2br(htmlspecialchars($msg['content']))); ?></p>
                <?php endif; ?>
                <?php foreach ($msg['attachments'] as $att): ?>
                    <?php if ($att['file_type'] === 'image'): ?>
                        <div class="messagerie-att-media-wrap">
                            <a href="<?php echo htmlspecialchars($att['file_path']); ?>" target="_blank" rel="noopener" class="messagerie-att-image-link">
                                <img src="<?php echo htmlspecialchars($att['file_path']); ?>" alt="<?php echo htmlspecialchars($att['original_name']); ?>" class="messagerie-att-image">
                            </a>
                            <a href="<?php echo htmlspecialchars($att['file_path']); ?>" download="<?php echo htmlspecialchars($att['original_name']); ?>" class="messagerie-att-download-btn" title="<?php echo t('messagerie_telecharger'); ?>"><i class="fas fa-download"></i></a>
                        </div>
                    <?php elseif ($att['file_type'] === 'video'): ?>
                        <div class="messagerie-att-media-wrap">
                            <video src="<?php echo htmlspecialchars($att['file_path']); ?>" controls class="messagerie-att-video"></video>
                            <a href="<?php echo htmlspecialchars($att['file_path']); ?>" download="<?php echo htmlspecialchars($att['original_name']); ?>" class="messagerie-att-download-btn" title="<?php echo t('messagerie_telecharger'); ?>"><i class="fas fa-download"></i></a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($att['file_path']); ?>" download="<?php echo htmlspecialchars($att['original_name']); ?>" class="messagerie-att-file">
                            <i class="fas <?php echo messagerie_file_icon(pathinfo($att['original_name'], PATHINFO_EXTENSION)); ?>"></i>
                            <span class="messagerie-att-file-info">
                                <strong><?php echo htmlspecialchars($att['original_name']); ?></strong>
                                <small><?php echo messagerie_format_size($att['file_size']); ?></small>
                            </span>
                            <i class="fas fa-download messagerie-att-download-icon"></i>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <button type="button" class="messagerie-msg-options-btn" data-is-own="<?php echo $is_own ? '1' : '0'; ?>" title="<?php echo t('messagerie_options'); ?>"><i class="fas fa-ellipsis-vertical"></i></button>
        </div>
        <div class="messagerie-msg-meta">
            <span class="messagerie-msg-time"><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></span>
            <?php if ($is_own): ?>
                <span class="messagerie-msg-status" data-status="<?php echo $msg['read_at'] ? 'read' : 'sent'; ?>">
                    <?php echo $msg['read_at'] ? '<i class="fas fa-check-double"></i> ' . t('messagerie_lu') : '<i class="fas fa-check"></i> ' . t('messagerie_envoye'); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>
