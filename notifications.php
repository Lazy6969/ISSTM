<?php
include_once 'language.php';
require_once 'db_connect.php';
require_once 'communaute_functions.php';
communaute_require_login();

$self_id = (int) $_SESSION['user_id'];
$per_page = 20;
$page = max(1, (int) ($_GET['page'] ?? 1));

$total_items = (int) $mysqli->query("SELECT COUNT(*) c FROM communaute_notifications WHERE user_id = $self_id")->fetch_assoc()['c'];
$total_pages = max(1, (int) ceil($total_items / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$stmt = $mysqli->prepare("
    SELECT n.*, u.nom AS actor_nom
    FROM communaute_notifications n
    LEFT JOIN utilisateurs u ON u.id = n.actor_id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param('iii', $self_id, $per_page, $offset);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$sections = ['today' => [], 'yesterday' => [], 'week' => [], 'older' => []];
foreach ($rows as $row) {
    $item = communaute_notification_render($row);
    $sections[communaute_notification_date_group($row['created_at'])][] = $item;
}

$section_labels = [
    'today' => t('notifications_section_aujourdhui'),
    'yesterday' => t('notifications_section_hier'),
    'week' => t('notifications_section_semaine'),
    'older' => t('notifications_section_plus_ancien'),
];

$page_title = t('notifications_page_titre');
include 'header.php';
?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('notifications_page_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-bell"></i> <?php echo t('notifications_page_titre'); ?></h1>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <div class="notif-global-actions">
            <button type="button" class="btn-outline" id="notif-mark-all-read"><i class="fas fa-check-double"></i> <?php echo t('notifications_tout_marquer_lu'); ?></button>
            <button type="button" class="btn-delete" id="notif-delete-all"><i class="fas fa-trash-alt"></i> <?php echo t('notifications_tout_supprimer'); ?></button>
        </div>

        <?php if (empty($rows)): ?>
            <p class="gallery-empty"><i class="fas fa-bell-slash"></i> <?php echo t('notifications_vide_page'); ?></p>
        <?php else: ?>
            <?php foreach ($sections as $group => $items): if (empty($items)) continue; ?>
                <div class="notif-section" data-group="<?php echo $group; ?>">
                    <div class="notif-section-header">
                        <h3><?php echo $section_labels[$group]; ?></h3>
                        <div class="notif-section-actions">
                            <button type="button" class="notif-section-mark-read" data-group="<?php echo $group; ?>" title="<?php echo t('notifications_marquer_lu'); ?>"><i class="fas fa-check-double"></i></button>
                            <button type="button" class="notif-section-delete-all" data-group="<?php echo $group; ?>" title="<?php echo t('notifications_supprimer_section'); ?>"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                    <div class="notif-list">
                        <?php foreach ($items as $it): ?>
                            <div class="notif-item <?php echo $it['is_read'] ? '' : 'is-unread'; ?>" data-id="<?php echo $it['id']; ?>">
                                <a href="notification_ouvrir.php?id=<?php echo $it['id']; ?>&redirect=<?php echo urlencode($it['link']); ?>" class="notif-item-link">
                                    <p><?php echo $it['message']; ?></p>
                                    <span><?php echo $it['created_at']; ?></span>
                                </a>
                                <div class="notif-item-actions">
                                    <button type="button" class="notif-toggle-btn" data-id="<?php echo $it['id']; ?>" title="<?php echo $it['is_read'] ? t('notifications_marquer_non_lu') : t('notifications_marquer_lu'); ?>">
                                        <i class="fas <?php echo $it['is_read'] ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                    </button>
                                    <button type="button" class="notif-delete-btn" data-id="<?php echo $it['id']; ?>" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($total_pages > 1): ?>
                <nav class="gallery-pagination" aria-label="Pagination des notifications">
                    <?php if ($page > 1): ?><a href="notifications.php?page=<?php echo $page - 1; ?>" class="page-nav-btn"><i class="fas fa-chevron-left"></i> Précédent</a><?php endif; ?>
                    <div class="page-numbers">
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <a href="notifications.php?page=<?php echo $p; ?>" class="page-number <?php echo $p === $page ? 'is-active' : ''; ?>"><?php echo $p; ?></a>
                        <?php endfor; ?>
                    </div>
                    <?php if ($page < $total_pages): ?><a href="notifications.php?page=<?php echo $page + 1; ?>" class="page-nav-btn">Suivant <i class="fas fa-chevron-right"></i></a><?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<script>
window.NOTIF_LABELS = {
    confirmSupprimer: <?php echo json_encode(t('notifications_supprimer_confirm')); ?>,
    confirmSupprimerTout: <?php echo json_encode(t('notifications_supprimer_tout_confirm')); ?>,
    marquerLu: <?php echo json_encode(t('notifications_marquer_lu')); ?>,
    marquerNonLu: <?php echo json_encode(t('notifications_marquer_non_lu')); ?>
};
document.addEventListener('DOMContentLoaded', () => {
    const L = window.NOTIF_LABELS;

    async function postAction(data) {
        const params = new URLSearchParams(data);
        const res = await fetch('notifications_action.php', { method: 'POST', body: params });
        return res.json();
    }

    document.querySelectorAll('.notif-toggle-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const item = btn.closest('.notif-item');
            const wasUnread = item.classList.contains('is-unread');
            await postAction({ action: 'toggle_read', id: btn.dataset.id });
            item.classList.toggle('is-unread', !wasUnread);
            const icon = btn.querySelector('i');
            icon.className = wasUnread ? 'fas fa-eye-slash' : 'fas fa-eye';
            btn.title = wasUnread ? L.marquerNonLu : L.marquerLu;
        });
    });

    document.querySelectorAll('.notif-delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!(await window.iSSTMConfirm(L.confirmSupprimer))) return;
            await postAction({ action: 'delete', id: btn.dataset.id });
            btn.closest('.notif-item').remove();
        });
    });

    document.querySelectorAll('.notif-section-mark-read').forEach(btn => {
        btn.addEventListener('click', async () => {
            await postAction({ action: 'mark_all_read', group: btn.dataset.group });
            const section = btn.closest('.notif-section');
            section.querySelectorAll('.notif-item').forEach(item => item.classList.remove('is-unread'));
            section.querySelectorAll('.notif-toggle-btn').forEach(b => { b.querySelector('i').className = 'fas fa-eye-slash'; b.title = L.marquerNonLu; });
        });
    });

    document.querySelectorAll('.notif-section-delete-all').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!(await window.iSSTMConfirm(L.confirmSupprimerTout))) return;
            await postAction({ action: 'delete_all', group: btn.dataset.group });
            btn.closest('.notif-section').remove();
        });
    });

    document.getElementById('notif-mark-all-read').addEventListener('click', async () => {
        await postAction({ action: 'mark_all_read' });
        document.querySelectorAll('.notif-item').forEach(item => item.classList.remove('is-unread'));
        document.querySelectorAll('.notif-toggle-btn').forEach(b => { b.querySelector('i').className = 'fas fa-eye-slash'; b.title = L.marquerNonLu; });
    });

    document.getElementById('notif-delete-all').addEventListener('click', async () => {
        if (!(await window.iSSTMConfirm(L.confirmSupprimerTout))) return;
        await postAction({ action: 'delete_all' });
        document.querySelectorAll('.notif-section').forEach(s => s.remove());
        document.querySelector('.notif-global-actions').insertAdjacentHTML('afterend', '<p class="gallery-empty"><i class="fas fa-bell-slash"></i> ' + <?php echo json_encode(t('notifications_vide_page')); ?> + '</p>');
    });
});
</script>

<?php include 'footer.php'; ?>
