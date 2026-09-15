<?php
include_once 'language.php';
require_once 'db_connect.php';
require_once 'communaute_functions.php';
require_once 'messagerie_functions.php';
require_once 'groupe_functions.php';
require_once 'dm_functions.php';
require_once 'groupe_perso_functions.php';

communaute_require_login();

$self_id = (int) $_SESSION['user_id'];
$is_admin = $_SESSION['user_role'] === 'admin';
$can_publish = $is_admin || $_SESSION['user_role'] === 'enseignant';

// --- Barre latérale : mon groupe (enseignant/étudiant uniquement) ---
$sidebar_has_groupe = in_array($_SESSION['user_role'], ['enseignant', 'etudiant'], true);
$sidebar_group_count = 0;
$sidebar_single_group = null;
if ($sidebar_has_groupe) {
    $sidebar_group_count = (int) $mysqli->query("SELECT COUNT(*) c FROM groupe_membres WHERE user_id = $self_id AND is_banned = 0")->fetch_assoc()['c'];
    if ($sidebar_group_count === 1) {
        $sidebar_single_group = groupe_get_single_group_id($mysqli, $self_id);
    }
}

// --- Barre latérale : messages privés (accès déplacé ici depuis le header) — affiche directement
// les conversations existantes, complétées par les amis sans conversation encore ouverte, jusqu'à
// 5 entrées (même logique de dédoublonnage que messages_prives.php : ids castés en int).
$sidebar_dm_unread = dm_get_unread_total($mysqli, $self_id);
$sidebar_conversations = dm_get_conversations($mysqli, $self_id);
$sidebar_conv_partner_ids = array_map('intval', array_column(array_column($sidebar_conversations, 'other'), 'id'));
$sidebar_amis_sans_conv = array_values(array_filter(amis_get_liste($mysqli, $self_id), function ($a) use ($sidebar_conv_partner_ids) {
    return !in_array((int) $a['id'], $sidebar_conv_partner_ids, true);
}));
$sidebar_dm_entries = array_slice(array_merge($sidebar_conversations, array_map(function ($a) {
    return ['id' => null, 'other' => $a, 'last_message' => null, 'unread' => 0];
}, $sidebar_amis_sans_conv)), 0, 5);

// --- Barre latérale : mes groupes personnels (groupes_utilisateurs, distincts des groupes de
// classe ci-dessus) — jusqu'à 5, non-lus en premier.
$sidebar_groupes_perso = $mysqli->query("
    SELECT g.id, g.nom,
           (SELECT COUNT(*) FROM groupe_utilisateurs_membres gm WHERE gm.groupe_id = g.id AND gm.is_banned = 0) AS membres_count,
           m.last_read_at
    FROM groupes_utilisateurs g
    JOIN groupe_utilisateurs_membres m ON m.groupe_id = g.id AND m.user_id = $self_id AND m.is_banned = 0
    ORDER BY g.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
foreach ($sidebar_groupes_perso as &$sg) {
    $sg['unread'] = groupe_perso_unread_count($mysqli, $sg['id'], $self_id, $sg['last_read_at']);
}
unset($sg);
usort($sidebar_groupes_perso, fn($a, $b) => $b['unread'] <=> $a['unread']);
$sidebar_groupes_perso = array_slice($sidebar_groupes_perso, 0, 5);

$type_icons = [
    'actualite' => 'fa-bullhorn', 'resultat' => 'fa-clipboard-check', 'emploi_du_temps' => 'fa-calendar-week',
    'examen' => 'fa-file-pen', 'media' => 'fa-photo-film', 'autre' => 'fa-thumbtack',
];
$type_label_keys = [
    'actualite' => 'communaute_type_actualite', 'resultat' => 'communaute_type_resultat',
    'emploi_du_temps' => 'communaute_type_emploi_du_temps', 'examen' => 'communaute_type_examen',
    'media' => 'communaute_type_media', 'autre' => 'communaute_type_autre',
];

$posts = $mysqli->query("
    SELECT p.*, u.nom AS auteur_nom, u.avatar_path AS auteur_avatar, u.role AS auteur_role
    FROM communaute_posts p
    JOIN utilisateurs u ON u.id = p.auteur_id
    ORDER BY p.created_at DESC
    LIMIT 30
")->fetch_all(MYSQLI_ASSOC);

$post_ids = array_column($posts, 'id');
$reactions_by_post = [];
$self_reactions = [];
$comments_by_post = [];
$media_by_post = [];

if (!empty($post_ids)) {
    $ids_list = implode(',', array_map('intval', $post_ids));

    $media_rows = $mysqli->query("SELECT * FROM communaute_post_media WHERE post_id IN ($ids_list) ORDER BY display_order ASC")->fetch_all(MYSQLI_ASSOC);
    foreach ($media_rows as $m) {
        $media_by_post[$m['post_id']][] = $m;
    }

    $reaction_rows = $mysqli->query("SELECT post_id, type, COUNT(*) AS total FROM communaute_reactions WHERE post_id IN ($ids_list) GROUP BY post_id, type")->fetch_all(MYSQLI_ASSOC);
    foreach ($reaction_rows as $r) {
        $reactions_by_post[$r['post_id']][$r['type']] = (int) $r['total'];
    }

    $self_rows = $mysqli->query("SELECT post_id, type FROM communaute_reactions WHERE user_id = $self_id AND post_id IN ($ids_list)")->fetch_all(MYSQLI_ASSOC);
    foreach ($self_rows as $r) { $self_reactions[$r['post_id']] = $r['type']; }

    $comment_rows = $mysqli->query("
        SELECT c.*, u.nom, u.avatar_path
        FROM communaute_comments c
        JOIN utilisateurs u ON u.id = c.user_id
        WHERE c.post_id IN ($ids_list)
        ORDER BY c.created_at ASC
    ")->fetch_all(MYSQLI_ASSOC);
    foreach ($comment_rows as $c) {
        $comments_by_post[$c['post_id']][] = $c;
    }
}

$page_title = t('communaute_titre');
include 'header.php';
?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('communaute_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-users-rectangle"></i> <?php echo t('communaute_titre'); ?></h1>
        <p><?php echo t('communaute_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container communaute-container">
        <div class="communaute-layout">
        <div class="communaute-main">

        <?php if ($can_publish): ?>
            <a href="admin_communaute.php" class="btn-outline" style="margin-bottom: 20px; display:inline-flex;"><i class="fas fa-gear"></i> <?php echo t('communaute_gerer_publications'); ?></a>
        <?php endif; ?>

        <?php if (empty($posts)): ?>
            <p class="gallery-empty"><i class="fas fa-users-rectangle"></i> <?php echo t('communaute_aucune_publication'); ?></p>
        <?php else: ?>
            <div class="communaute-feed" data-i18n-jaime="<?php echo htmlspecialchars(t('communaute_jaime')); ?>" data-i18n-jadore="<?php echo htmlspecialchars(t('communaute_jadore')); ?>">
                <?php foreach ($posts as $post):
                    $pid = (int) $post['id'];
                    $likes = $reactions_by_post[$pid]['like'] ?? 0;
                    $loves = $reactions_by_post[$pid]['love'] ?? 0;
                    $my_reaction = $self_reactions[$pid] ?? null;
                    $top_comments = array_values(array_filter($comments_by_post[$pid] ?? [], fn($c) => $c['parent_id'] === null));
                    $replies_by_parent = [];
                    foreach (($comments_by_post[$pid] ?? []) as $c) {
                        if ($c['parent_id'] !== null) { $replies_by_parent[$c['parent_id']][] = $c; }
                    }
                    $post_url = 'http://' . $_SERVER['HTTP_HOST'] . SITE_URL . '/communaute.php?post=' . $pid . '#post-' . $pid;
                    $post_media = $media_by_post[$pid] ?? [];
                    $images = array_values(array_filter($post_media, fn($m) => $m['media_type'] === 'image'));
                    $videos = array_values(array_filter($post_media, fn($m) => $m['media_type'] === 'video'));
                    $docs = array_values(array_filter($post_media, fn($m) => $m['media_type'] === 'pdf'));
                    $image_srcs_json = htmlspecialchars(json_encode(array_map(fn($m) => $m['media_path'], $images)), ENT_QUOTES);
                    $media_total = count($post_media);
                ?>
                    <article class="communaute-post-card" id="post-<?php echo $pid; ?>" data-post-id="<?php echo $pid; ?>">
                        <div class="communaute-post-header">
                            <div class="communaute-post-avatar">
                                <?php if (!empty($post['auteur_avatar'])): ?>
                                    <img src="<?php echo SITE_URL . '/' . htmlspecialchars($post['auteur_avatar']); ?>" alt="">
                                <?php else: ?>
                                    <i class="fas fa-circle-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="communaute-post-meta">
                                <strong><?php echo htmlspecialchars($post['auteur_nom']); ?></strong>
                                <span><i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></span>
                            </div>
                            <span class="communaute-post-type"><i class="fas <?php echo $type_icons[$post['type']] ?? 'fa-thumbtack'; ?>"></i> <?php echo t($type_label_keys[$post['type']] ?? 'communaute_type_autre'); ?></span>
                        </div>

                        <?php if (!empty($post['contenu'])): ?>
                            <p class="communaute-post-content"><?php echo nl2br(htmlspecialchars($post['contenu'])); ?></p>
                        <?php endif; ?>

                        <?php if ($media_total > 2):
                            $stack_preview = array_slice($post_media, 0, 3);
                            $media_json = htmlspecialchars(json_encode(array_map(fn($m) => [
                                'type' => $m['media_type'],
                                'path' => $m['media_path'],
                            ], $post_media)), ENT_QUOTES);
                        ?>
                            <button type="button" class="communaute-media-stack" data-media='<?php echo $media_json; ?>'>
                                <?php foreach ($stack_preview as $m): ?>
                                    <div class="communaute-media-stack-layer">
                                        <?php if ($m['media_type'] === 'image'): ?>
                                            <img src="<?php echo htmlspecialchars($m['media_path']); ?>" alt="" loading="lazy">
                                        <?php elseif ($m['media_type'] === 'video'): ?>
                                            <div class="communaute-media-stack-icon"><i class="fas fa-circle-play"></i></div>
                                        <?php else: ?>
                                            <div class="communaute-media-stack-icon"><i class="fas fa-file-pdf"></i></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                <span class="communaute-media-stack-badge"><i class="fas fa-images"></i> <?php echo sprintf(t('communaute_voir_tout_fichiers'), $media_total); ?></span>
                            </button>
                        <?php else: ?>
                            <?php if (!empty($images)): ?>
                                <div class="communaute-media-gallery" data-count="<?php echo min(count($images), 4); ?>">
                                    <?php foreach ($images as $img): ?>
                                        <div class="communaute-media-item">
                                            <img src="<?php echo htmlspecialchars($img['media_path']); ?>" alt="" loading="lazy" class="communaute-gallery-img" data-full="<?php echo htmlspecialchars($img['media_path']); ?>" data-gallery='<?php echo $image_srcs_json; ?>'>
                                            <a href="<?php echo htmlspecialchars($img['media_path']); ?>" download class="communaute-media-download" title="<?php echo t('communaute_telecharger'); ?>" onclick="event.stopPropagation();"><i class="fas fa-download"></i></a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php foreach ($videos as $vid): ?>
                                <div class="communaute-post-media">
                                    <video src="<?php echo htmlspecialchars($vid['media_path']); ?>" controls></video>
                                </div>
                            <?php endforeach; ?>

                            <?php foreach ($docs as $doc):
                                $doc_ext = strtolower(pathinfo($doc['media_path'], PATHINFO_EXTENSION));
                            ?>
                                <a href="<?php echo htmlspecialchars($doc['media_path']); ?>" download target="_blank" rel="noopener" class="communaute-post-file">
                                    <i class="fas <?php echo messagerie_file_icon($doc_ext); ?>"></i>
                                    <span><?php echo t('communaute_voir_document'); ?></span>
                                    <span class="communaute-post-file-download"><i class="fas fa-download"></i> <?php echo t('communaute_telecharger'); ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <div class="communaute-reactions-summary">
                            <span data-summary="like" style="<?php echo $likes > 0 ? '' : 'display:none;'; ?>"><i class="fas fa-thumbs-up"></i> <?php echo $likes; ?></span>
                            <span data-summary="love" style="<?php echo $loves > 0 ? '' : 'display:none;'; ?>"><i class="fas fa-heart"></i> <?php echo $loves; ?></span>
                            <span class="communaute-comment-count-label" data-suffix="<?php echo htmlspecialchars(t('communaute_commentaires')); ?>"><?php echo count($comments_by_post[$pid] ?? []); ?> <?php echo t('communaute_commentaires'); ?></span>
                        </div>

                        <div class="communaute-reactions-bar">
                            <div class="communaute-reaction-group">
                                <button type="button" class="communaute-reaction-btn communaute-reaction-main <?php echo $my_reaction ? 'is-active' : ''; ?> <?php echo $my_reaction === 'love' ? 'communaute-reaction-love' : ''; ?>" data-post-id="<?php echo $pid; ?>" data-reaction="<?php echo $my_reaction === 'love' ? 'love' : 'like'; ?>">
                                    <i class="fas <?php echo $my_reaction === 'love' ? 'fa-heart' : 'fa-thumbs-up'; ?>"></i> <?php echo $my_reaction === 'love' ? t('communaute_jadore') : t('communaute_jaime'); ?>
                                </button>
                                <div class="communaute-reaction-picker">
                                    <button type="button" class="communaute-reaction-btn communaute-reaction-pick" data-post-id="<?php echo $pid; ?>" data-reaction="like" title="<?php echo htmlspecialchars(t('communaute_jaime')); ?>"><i class="fas fa-thumbs-up"></i></button>
                                    <button type="button" class="communaute-reaction-btn communaute-reaction-love communaute-reaction-pick" data-post-id="<?php echo $pid; ?>" data-reaction="love" title="<?php echo htmlspecialchars(t('communaute_jadore')); ?>"><i class="fas fa-heart"></i></button>
                                </div>
                            </div>
                            <button type="button" class="communaute-reaction-btn communaute-comment-focus-btn" data-post-id="<?php echo $pid; ?>">
                                <i class="fas fa-comment"></i> <?php echo t('communaute_bouton_commentaire'); ?>
                            </button>
                            <div class="communaute-share-dropdown">
                                <button type="button" class="communaute-reaction-btn communaute-share-toggle"><i class="fas fa-share-nodes"></i> <?php echo t('communaute_partager'); ?></button>
                                <div class="communaute-share-menu">
                                    <a class="share-btn share-facebook" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($post_url); ?>" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                                    <a class="share-btn share-whatsapp" target="_blank" rel="noopener" href="https://wa.me/?text=<?php echo urlencode($post_url); ?>" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                    <a class="share-btn share-messenger" target="_blank" rel="noopener" href="https://www.facebook.com/dialog/send?link=<?php echo urlencode($post_url); ?>&app_id=0&redirect_uri=<?php echo urlencode($post_url); ?>" title="Messenger"><i class="fab fa-facebook-messenger"></i></a>
                                    <a class="share-btn share-x" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=<?php echo urlencode($post_url); ?>" title="X"><i class="fab fa-x-twitter"></i></a>
                                    <button type="button" class="share-btn share-copy communaute-copy-link-btn" data-url="<?php echo htmlspecialchars($post_url); ?>" title="Copier le lien"><i class="fas fa-link"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="communaute-comments" id="communaute-comments-<?php echo $pid; ?>" data-label-voir="<?php echo htmlspecialchars(t('communaute_voir_tous_commentaires')); ?>" data-label-reduire="<?php echo htmlspecialchars(t('communaute_reduire_commentaires')); ?>">
                            <?php foreach ($top_comments as $c): ?>
                                <?php echo communaute_render_comment($c, $self_id, $is_admin, false); ?>
                                <?php foreach (($replies_by_parent[$c['id']] ?? []) as $reply): ?>
                                    <?php echo communaute_render_comment($reply, $self_id, $is_admin, true); ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>

                        <form class="communaute-comment-form" data-post-id="<?php echo $pid; ?>">
                            <input type="text" name="contenu" placeholder="<?php echo t('communaute_ecrire_commentaire'); ?>" required maxlength="1000">
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        </div>

        <aside class="communaute-sidebar">
            <div class="communaute-sidebar-card communaute-sidebar-profile">
                <div class="communaute-sidebar-avatar">
                    <?php if (!empty($_SESSION['user_avatar'])): ?>
                        <img src="<?php echo SITE_URL . '/' . htmlspecialchars($_SESSION['user_avatar']); ?>" alt="">
                    <?php else: ?>
                        <i class="fas fa-circle-user"></i>
                    <?php endif; ?>
                </div>
                <div class="communaute-sidebar-profile-info">
                    <strong><?php echo htmlspecialchars($_SESSION['user_nom']); ?></strong>
                    <span><?php echo t($_SESSION['user_role'] === 'admin' ? 'admin_utilisateurs_role_admin' : ($_SESSION['user_role'] === 'enseignant' ? 'admin_utilisateurs_role_enseignant' : 'admin_utilisateurs_role_etudiant')); ?></span>
                </div>
                <a href="profil.php" class="btn-outline"><?php echo t('communaute_sidebar_mon_profil'); ?></a>
            </div>

            <?php if ($sidebar_has_groupe): ?>
                <div class="communaute-sidebar-card">
                    <h3><i class="fas fa-people-group"></i> <?php echo t('communaute_sidebar_mon_groupe'); ?></h3>
                    <?php if ($sidebar_single_group): ?>
                        <p><?php echo htmlspecialchars($sidebar_single_group['nom']); ?></p>
                        <a href="groupe_chat.php?id=<?php echo (int) $sidebar_single_group['groupe_id']; ?>" class="btn-add-item"><i class="fas fa-arrow-right"></i> <?php echo t('communaute_sidebar_acceder_groupe'); ?></a>
                    <?php elseif ($sidebar_group_count > 1): ?>
                        <p><?php echo t('communaute_sidebar_plusieurs_groupes'); ?></p>
                        <a href="mes_groupes.php" class="btn-add-item"><i class="fas fa-arrow-right"></i> <?php echo t('groupe_nav_titre'); ?></a>
                    <?php else: ?>
                        <p class="communaute-sidebar-muted"><?php echo t('communaute_sidebar_aucun_groupe'); ?></p>
                        <a href="mes_groupes.php" class="btn-outline"><?php echo t('groupe_nav_titre'); ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="communaute-sidebar-card">
                <h3><i class="fas fa-file-circle-check"></i> <?php echo t('communaute_sidebar_resultats_titre'); ?></h3>
                <p class="communaute-sidebar-muted"><?php echo t('communaute_sidebar_resultats_desc'); ?></p>
                <a href="resultats_examen.php" class="btn-add-item"><i class="fas fa-arrow-right"></i> <?php echo t('resultats_lien_voir'); ?></a>
            </div>

            <div class="communaute-sidebar-card">
                <h3><i class="fas fa-paper-plane"></i> <?php echo t('communaute_sidebar_messages_titre'); ?><?php if ($sidebar_dm_unread > 0): ?> <span class="groupe-unread-badge communaute-sidebar-badge"><?php echo $sidebar_dm_unread > 99 ? '99+' : $sidebar_dm_unread; ?></span><?php endif; ?></h3>
                <?php if (empty($sidebar_dm_entries)): ?>
                    <p class="communaute-sidebar-muted"><?php echo t('communaute_sidebar_aucun_message'); ?></p>
                <?php else: ?>
                    <div class="communaute-sidebar-list">
                        <?php foreach ($sidebar_dm_entries as $entry): ?>
                            <a href="<?php echo $entry['id'] ? 'messages_prives.php?id=' . (int) $entry['id'] : 'messages_prives.php?with=' . (int) $entry['other']['id']; ?>" class="communaute-sidebar-list-item">
                                <img src="<?php echo $entry['other']['avatar_path'] ? htmlspecialchars($entry['other']['avatar_path']) : 'images/teachers/default-avatar.svg'; ?>" alt="" class="communaute-sidebar-list-avatar" onerror="this.src='images/teachers/default-avatar.svg'">
                                <div class="communaute-sidebar-list-info">
                                    <strong><?php echo htmlspecialchars($entry['other']['nom']); ?></strong>
                                    <span><?php echo $entry['last_message'] ? htmlspecialchars(mb_strimwidth((string) $entry['last_message']['content'], 0, 28, '...')) : t('dm_nouvelle_conversation'); ?></span>
                                </div>
                                <?php if ($entry['unread'] > 0): ?><span class="groupe-unread-badge"><?php echo $entry['unread'] > 99 ? '99+' : $entry['unread']; ?></span><?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <a href="messages_prives.php" class="btn-outline communaute-sidebar-voir-tout"><?php echo t('communaute_sidebar_voir_tout'); ?></a>
            </div>

            <div class="communaute-sidebar-card">
                <h3><i class="fas fa-people-group"></i> <?php echo t('communaute_sidebar_groupes_perso_titre'); ?></h3>
                <?php if (empty($sidebar_groupes_perso)): ?>
                    <p class="communaute-sidebar-muted"><?php echo t('communaute_sidebar_aucun_groupe_perso'); ?></p>
                <?php else: ?>
                    <div class="communaute-sidebar-list">
                        <?php foreach ($sidebar_groupes_perso as $sg): ?>
                            <a href="groupe_perso_chat.php?id=<?php echo (int) $sg['id']; ?>" class="communaute-sidebar-list-item">
                                <div class="communaute-sidebar-list-avatar communaute-sidebar-list-avatar-icon"><i class="fas fa-people-group"></i></div>
                                <div class="communaute-sidebar-list-info">
                                    <strong><?php echo htmlspecialchars($sg['nom']); ?></strong>
                                    <span><?php echo (int) $sg['membres_count']; ?> <?php echo t('groupe_membres'); ?></span>
                                </div>
                                <?php if ($sg['unread'] > 0): ?><span class="groupe-unread-badge"><?php echo $sg['unread'] > 99 ? '99+' : $sg['unread']; ?></span><?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <a href="mes_amis.php?tab=groupes" class="btn-outline communaute-sidebar-voir-tout"><?php echo t('communaute_sidebar_voir_tout'); ?></a>
            </div>
        </aside>

        </div>
    </div>
</div>

<!-- Visionneuse dédiée aux publications à plusieurs fichiers (images/vidéos/PDF mêlés), remplie en JS -->
<div class="communaute-media-viewer" id="communaute-media-viewer" aria-hidden="true">
    <button type="button" class="communaute-media-viewer-close" aria-label="<?php echo t('communaute_fermer'); ?>"><i class="fas fa-xmark"></i></button>
    <button type="button" class="communaute-media-viewer-nav communaute-media-viewer-prev" aria-label="<?php echo t('communaute_precedent'); ?>"><i class="fas fa-chevron-left"></i></button>
    <div class="communaute-media-viewer-content"></div>
    <button type="button" class="communaute-media-viewer-nav communaute-media-viewer-next" aria-label="<?php echo t('communaute_suivant'); ?>"><i class="fas fa-chevron-right"></i></button>
    <div class="communaute-media-viewer-counter"></div>
</div>

<script src="<?php echo SITE_URL; ?>/communaute.js?v=<?php echo filemtime(__DIR__ . '/communaute.js'); ?>"></script>

<?php include 'footer.php'; ?>
