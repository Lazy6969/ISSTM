<?php
// Partiel partagé par mes_groupes_perso.php (traite le POST puis redirige vers mes_amis.php) et
// l'onglet "Groupes" du hub mes_amis.php. Suppose que $mysqli, $self_id sont déjà définis, ainsi
// que $flash (tableau ['type'=>..,'msg'=>..] ou null) par l'includeur.

$sql = "SELECT g.*, u.nom AS createur_nom,
        (SELECT COUNT(*) FROM groupe_utilisateurs_membres gm WHERE gm.groupe_id = g.id AND gm.is_banned = 0) AS membres_count
        FROM groupes_utilisateurs g
        JOIN groupe_utilisateurs_membres m ON m.groupe_id = g.id AND m.user_id = $self_id AND m.is_banned = 0
        JOIN utilisateurs u ON u.id = g.createur_id
        ORDER BY g.created_at DESC";
$my_groups = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
foreach ($my_groups as &$g) {
    $g['unread'] = groupe_perso_unread_count($mysqli, $g['id'], $self_id, $g['last_read_at']);
}
unset($g);
?>

<?php if ($flash): ?>
    <div class="admin-flash admin-flash-<?php echo htmlspecialchars($flash['type']); ?>">
        <i class="fas <?php echo $flash['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'; ?>"></i>
        <?php echo htmlspecialchars($flash['msg']); ?>
    </div>
<?php endif; ?>

<div class="groupe-actions-row">
    <div class="groupe-action-card">
        <h3><i class="fas fa-plus"></i> <?php echo t('groupe_perso_creer_titre'); ?></h3>
        <form action="mes_groupes_perso.php" method="POST" class="groupe-inline-form">
            <input type="text" name="nom" placeholder="<?php echo t('groupe_perso_nom_placeholder'); ?>" required maxlength="150">
            <input type="text" name="description" placeholder="<?php echo t('groupe_perso_description_placeholder'); ?>" maxlength="255">
            <button type="submit" name="create_group" class="btn-add-item"><i class="fas fa-users"></i> <?php echo t('groupe_creer_bouton'); ?></button>
        </form>
    </div>

    <div class="groupe-action-card">
        <h3><i class="fas fa-key"></i> <?php echo t('groupe_rejoindre_titre'); ?></h3>
        <form action="mes_groupes_perso.php" method="POST" class="groupe-inline-form">
            <input type="text" name="code" placeholder="<?php echo t('groupe_code_placeholder'); ?>" maxlength="10" style="text-transform:uppercase;" required>
            <button type="submit" name="join_group" class="btn-add-item"><i class="fas fa-right-to-bracket"></i> <?php echo t('groupe_rejoindre_bouton'); ?></button>
        </form>
    </div>
</div>

<?php if (empty($my_groups)): ?>
    <p class="gallery-empty"><i class="fas fa-people-group"></i> <?php echo t('groupe_perso_aucun'); ?></p>
<?php else: ?>
    <div class="groupe-list">
        <?php foreach ($my_groups as $g): ?>
            <div class="groupe-card">
                <?php if ($g['unread'] > 0): ?><span class="groupe-unread-badge"><?php echo $g['unread'] > 99 ? '99+' : $g['unread']; ?></span><?php endif; ?>
                <a href="groupe_perso_chat.php?id=<?php echo (int) $g['id']; ?>" class="groupe-card-link">
                    <div class="groupe-card-icon"><i class="fas fa-people-group"></i></div>
                    <h4><?php echo htmlspecialchars($g['nom']); ?></h4>
                    <?php if (!empty($g['description'])): ?>
                        <div class="groupe-card-meta"><span><?php echo htmlspecialchars($g['description']); ?></span></div>
                    <?php endif; ?>
                    <div class="groupe-card-meta">
                        <span><i class="fas fa-user-group"></i> <?php echo (int) $g['membres_count']; ?></span>
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($g['createur_nom']); ?></span>
                    </div>
                    <?php if ((int) $g['createur_id'] === $self_id): ?>
                        <div class="groupe-card-meta"><span class="groupe-card-code"><i class="fas fa-key"></i> <?php echo htmlspecialchars($g['code_unique']); ?></span></div>
                    <?php endif; ?>
                </a>
                <div class="groupe-card-actions">
                    <?php if ((int) $g['createur_id'] === $self_id): ?>
                        <form action="mes_groupes_perso.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('groupe_confirm_suppr')); ?>">
                            <button type="submit" name="delete_group_id" value="<?php echo (int) $g['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    <?php else: ?>
                        <form action="mes_groupes_perso.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('groupe_confirm_quitter')); ?>">
                            <button type="submit" name="leave_group_id" value="<?php echo (int) $g['id']; ?>" class="btn-outline" title="<?php echo t('groupe_quitter'); ?>"><i class="fas fa-right-from-bracket"></i></button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
