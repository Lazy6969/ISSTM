<?php
// Fonctions partagées par communaute.php et les endpoints AJAX associés (communaute_react.php,
// communaute_comment_add.php, communaute_comment_edit.php, communaute_comment_delete.php).
// Calque de groupe_require_login() (groupe_functions.php) : même liste de rôles autorisés.

function communaute_require_login($isAjax = false) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
        if ($isAjax) { http_response_code(403); echo json_encode(['error' => 'unauthorized']); exit; }
        header('Location: index.php');
        exit;
    }
    if (!in_array($_SESSION['user_role'], ['enseignant', 'etudiant', 'admin'], true)) {
        if ($isAjax) { http_response_code(403); echo json_encode(['error' => 'unauthorized']); exit; }
        header('Location: index.php');
        exit;
    }
}

// Rend le HTML d'un seul commentaire (utilisé à la fois par le rendu initial de communaute.php
// et par communaute_comment_add.php pour l'insertion AJAX du nouveau commentaire).
function communaute_render_comment($c, $self_id, $is_admin, $is_reply = false) {
    $can_manage = $is_admin || (int) $c['user_id'] === (int) $self_id;
    $avatar = !empty($c['avatar_path']) ? SITE_URL . '/' . htmlspecialchars($c['avatar_path']) : '';
    $updated_badge = !empty($c['updated_at']) ? ' <span class="communaute-comment-edited">(' . t('communaute_modifie') . ')</span>' : '';
    $author_url = 'profil_public.php?id=' . (int) $c['user_id'];

    ob_start();
    ?>
    <div class="communaute-comment<?php echo $is_reply ? ' communaute-comment-reply' : ''; ?>" id="communaute-comment-<?php echo (int) $c['id']; ?>" data-comment-id="<?php echo (int) $c['id']; ?>" <?php if ($is_reply): ?>data-parent-id="<?php echo (int) $c['parent_id']; ?>"<?php endif; ?>>
        <a href="<?php echo $author_url; ?>" class="communaute-comment-avatar">
            <?php if ($avatar): ?>
                <img src="<?php echo $avatar; ?>" alt="">
            <?php else: ?>
                <i class="fas fa-circle-user"></i>
            <?php endif; ?>
        </a>
        <div class="communaute-comment-body">
            <div class="communaute-comment-bubble">
                <a href="<?php echo $author_url; ?>" class="communaute-comment-author"><?php echo htmlspecialchars($c['nom']); ?></a>
                <p class="communaute-comment-text"><?php echo nl2br(htmlspecialchars($c['contenu'])); ?></p>
            </div>
            <div class="communaute-comment-actions">
                <span class="communaute-comment-date"><?php echo date('d/m/Y H:i', strtotime($c['created_at'])); ?><?php echo $updated_badge; ?></span>
                <button type="button" class="communaute-comment-reply-btn" data-comment-id="<?php echo (int) $c['id']; ?>"><?php echo t('communaute_repondre'); ?></button>
                <?php if ($can_manage): ?>
                    <button type="button" class="communaute-comment-edit-btn" data-comment-id="<?php echo (int) $c['id']; ?>"><?php echo t('admin_modifier'); ?></button>
                    <button type="button" class="communaute-comment-delete-btn" data-confirm-msg="<?php echo htmlspecialchars(t('communaute_supprimer_commentaire_confirm')); ?>" data-comment-id="<?php echo (int) $c['id']; ?>"><?php echo t('admin_supprimer'); ?></button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Formate un datetime en durée relative courte ("à l'instant", "5 min", "3 h", "2 j") pour
// l'affichage compact du menu cloche — au-delà de 6 jours, on retombe sur la date absolue
// (peu utile de dire "12 j" quand une date précise se lit aussi vite).
function communaute_time_ago($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return t('temps_a_linstant');
    if ($diff < 3600) return sprintf(t('temps_il_y_a_min'), (int) floor($diff / 60));
    if ($diff < 86400) return sprintf(t('temps_il_y_a_heure'), (int) floor($diff / 3600));
    if ($diff < 604800) return sprintf(t('temps_il_y_a_jour'), (int) floor($diff / 86400));
    return date('d/m/Y', strtotime($datetime));
}

// Calcule le message affiché et le lien cible d'une notification (table communaute_notifications,
// aucune colonne message/link stockée : tout est recalculé ici à partir de type+post_id/comment_id).
// Utilisé à la fois par communaute_notifications.php (menu cloche) et notifications.php (page complète).
function communaute_notification_render($n) {
    $actor = $n['actor_nom'] ?? '';
    if ($n['type'] === 'nouvelle_publication') {
        $message = str_replace('%nom%', htmlspecialchars($actor), t('notification_nouvelle_publication'));
        $link = SITE_URL . '/communaute.php#post-' . (int) $n['post_id'];
    } else {
        $message = str_replace('%nom%', htmlspecialchars($actor), t('notification_reponse_commentaire'));
        $link = SITE_URL . '/communaute.php#communaute-comment-' . (int) $n['comment_id'];
    }
    $avatar = !empty($n['actor_avatar']) ? SITE_URL . '/' . $n['actor_avatar'] : SITE_URL . '/images/teachers/default-avatar.svg';
    return [
        'id' => (int) $n['id'],
        'type' => $n['type'],
        'message' => $message,
        'link' => $link,
        'is_read' => (bool) $n['is_read'],
        'actor_nom' => $actor,
        'actor_avatar' => $avatar,
        'time_ago' => communaute_time_ago($n['created_at']),
        'created_at_raw' => $n['created_at'],
        'created_at' => date('d/m/Y H:i', strtotime($n['created_at'])),
    ];
}

// Classe une date de notification dans l'une des 4 sections utilisées par notifications.php :
// 'today', 'yesterday', 'week' (2 à 7 jours), 'older'. Utilisé aussi bien pour l'affichage groupé
// que pour scoper les actions groupées (notifications_action.php) aux mêmes bornes exactement.
function communaute_notification_date_group($created_at) {
    $ts = strtotime($created_at);
    $today = strtotime('today');
    $yesterday = strtotime('yesterday');
    $week_ago = strtotime('-7 days', $today);
    if ($ts >= $today) return 'today';
    if ($ts >= $yesterday) return 'yesterday';
    if ($ts >= $week_ago) return 'week';
    return 'older';
}

// Fragment SQL (sans "WHERE"/"AND" en tête) bornant created_at à un groupe de date donné, pour les
// actions groupées (mark_all_read/delete_all avec un "group" précis dans notifications_action.php).
function communaute_notification_group_sql($group) {
    switch ($group) {
        case 'today': return "created_at >= CURDATE()";
        case 'yesterday': return "created_at >= CURDATE() - INTERVAL 1 DAY AND created_at < CURDATE()";
        case 'week': return "created_at >= CURDATE() - INTERVAL 7 DAY AND created_at < CURDATE() - INTERVAL 1 DAY";
        case 'older': return "created_at < CURDATE() - INTERVAL 7 DAY";
        default: return "1=1";
    }
}
