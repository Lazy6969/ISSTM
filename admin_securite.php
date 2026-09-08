<?php
include_once 'language.php';
require_once 'db_connect.php';
require_once 'mail_functions.php';
require_once 'security_functions.php';

$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';
if (!$is_admin) {
    header('Location: index.php');
    exit;
}

// Tous les événements journalisés par le circuit "mot de passe oublié" : le circuit actuel par
// lien email (reset_link_sent/reset_link_unknown_email dans mot_de_passe_oublie.php, puis
// pwd_criteria_fail/pwd_reset_success dans reinitialiser_mdp.php), le ré-envoi manuel depuis
// cette page (pwd_admin_resend), et les types otp_* conservés pour l'historique de l'ancien
// circuit par code OTP (nom + téléphone + code), remplacé par le lien email. Défini avant les
// actions POST ci-dessous, qui s'appuient dessus pour ne jamais toucher à des lignes de
// security_log hors de ce périmètre.
$pwreset_events = ['reset_link_sent', 'reset_link_unknown_email', 'pwd_criteria_fail', 'pwd_reset_success', 'pwd_admin_resend', 'otp_request', 'otp_success', 'otp_fail', 'otp_mismatch', 'otp_locked', 'otp_no_phone', 'otp_rate_limited'];
$placeholders = implode(',', array_fill(0, count($pwreset_events), '?'));
$types = str_repeat('s', count($pwreset_events));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Purger l'historique (au-delà de 90 jours) ---
    if (isset($_POST['purge_log'])) {
        $stmt = $mysqli->prepare("DELETE FROM security_log WHERE event_type IN ($placeholders) AND created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
        $stmt->bind_param($types, ...$pwreset_events);
        $stmt->execute();
        $stmt->close();
        header('Location: admin_securite.php?flash=' . urlencode('success|' . t('admin_securite_purge_ok')));
        exit;
    }

    // --- Supprimer un événement précis ---
    if (isset($_POST['delete_log_id'])) {
        $id = (int) $_POST['delete_log_id'];
        $stmt = $mysqli->prepare("DELETE FROM security_log WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        header('Location: admin_securite.php?flash=' . urlencode('success|' . t('admin_securite_event_supprime')));
        exit;
    }

    // --- Tout supprimer (sans condition d'âge, contrairement à la purge) ---
    if (isset($_POST['delete_all_log'])) {
        $stmt = $mysqli->prepare("DELETE FROM security_log WHERE event_type IN ($placeholders)");
        $stmt->bind_param($types, ...$pwreset_events);
        $stmt->execute();
        $stmt->close();
        header('Location: admin_securite.php?flash=' . urlencode('success|' . t('admin_securite_tout_supprime')));
        exit;
    }

    // --- Renvoyer un nouveau mot de passe par email, pour le compte identifié par un événement ---
    if (isset($_POST['resend_password_id'])) {
        $uid = (int) $_POST['resend_password_id'];
        $stmt = $mysqli->prepare("SELECT id, nom, email FROM utilisateurs WHERE id = ?");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $target = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$target) {
            header('Location: admin_securite.php?flash=' . urlencode('error|' . t('admin_securite_utilisateur_introuvable')));
            exit;
        }

        $new_password = security_generate_password(10);
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("UPDATE utilisateurs SET mot_de_passe = ?, dernier_mdp_genere = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $stmt->bind_param('ssi', $hashed, $new_password, $target['id']);
        $stmt->execute();
        $stmt->close();

        $subject = t('admin_securite_email_sujet');
        $body = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">'
            . '<h2 style="color:#003366;">' . htmlspecialchars(t('admin_securite_email_titre')) . '</h2>'
            . '<p>' . htmlspecialchars(sprintf(t('mdp_oublie_email_bonjour'), $target['nom'])) . '</p>'
            . '<p>' . htmlspecialchars(t('admin_securite_email_texte')) . '</p>'
            . '<p style="margin:24px 0;font-size:22px;font-weight:bold;letter-spacing:2px;color:#d4a017;text-align:center;">' . htmlspecialchars($new_password) . '</p>'
            . '<p style="font-size:13px;color:#888;">' . htmlspecialchars(t('admin_securite_email_recommandation')) . '</p>'
            . '<hr><p style="font-size:12px;color:#888;">ISSTM Mahajanga — isstm.univ.umg@gmail.com</p></div>';
        $sent = isstm_send_mail($target['email'], $subject, $body);
        pwreset_log($mysqli, 'pwd_admin_resend', $target['email']);

        $flash_msg = sprintf($sent ? t('admin_securite_resend_ok') : t('admin_securite_resend_echec'), $target['nom']);
        header('Location: admin_securite.php?flash=' . urlencode(($sent ? 'success|' : 'error|') . $flash_msg));
        exit;
    }
}

if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; } else { $flash = null; }
} else {
    $flash = null;
}

$counts = array_fill_keys($pwreset_events, 0);
$stmt = $mysqli->prepare("SELECT event_type, COUNT(*) c FROM security_log WHERE event_type IN ($placeholders) GROUP BY event_type");
$stmt->bind_param($types, ...$pwreset_events);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) { $counts[$row['event_type']] = (int) $row['c']; }
$stmt->close();

// Compteurs du circuit ACTUEL (lien email). Les types otp_* restent comptés dans le total et
// listés dans l'historique (voir plus bas) pour ne pas perdre les anciennes traces, mais ne sont
// plus mis en avant en haut de page : ce circuit-là (nom + téléphone + code OTP) a été remplacé.
$total_liens_envoyes = $counts['reset_link_sent'];
$total_emails_inconnus = $counts['reset_link_unknown_email'];
$total_changes = $counts['pwd_reset_success'];
$total_mdp_refuse = $counts['pwd_criteria_fail'];

$per_page = 20;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

$stmt = $mysqli->prepare("SELECT COUNT(*) c FROM security_log WHERE event_type IN ($placeholders)");
$stmt->bind_param($types, ...$pwreset_events);
$stmt->execute();
$total_rows = (int) $stmt->get_result()->fetch_assoc()['c'];
$stmt->close();
$total_pages = max(1, (int) ceil($total_rows / $per_page));

$stmt = $mysqli->prepare("SELECT * FROM security_log WHERE event_type IN ($placeholders) ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param($types . 'ii', ...[...$pwreset_events, $per_page, $offset]);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Résolution en une seule requête (pas de N+1) du "profil exact" (nom + photo) derrière chaque
// identifiant de cette page de résultats, pour l'affichage et pour savoir sur quels comptes
// l'action "renvoyer un nouveau mot de passe" est possible.
$identifiants = array_values(array_unique(array_filter(array_column($logs, 'identifiant'))));
$user_lookup = [];
if (!empty($identifiants)) {
    $ph2 = implode(',', array_fill(0, count($identifiants), '?'));
    $types2 = str_repeat('s', count($identifiants));
    $stmt = $mysqli->prepare("SELECT id, nom, email, avatar_path FROM utilisateurs WHERE email IN ($ph2)");
    $stmt->bind_param($types2, ...$identifiants);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $user_lookup[$row['email']] = $row; }
    $stmt->close();
}

// Libellé / icône / couleur de badge par type d'événement, pour un affichage lisible. Les
// otp_* (ancien circuit par code, remplacé) restent affichables pour l'historique existant.
$event_info = [
    'reset_link_sent'          => ['label' => t('admin_securite_event_link_sent'),     'icon' => 'fa-paper-plane',          'badge' => 'admin-status-info'],
    'reset_link_unknown_email' => ['label' => t('admin_securite_event_link_unknown'),  'icon' => 'fa-circle-question',      'badge' => 'admin-status-brouillon'],
    'pwd_criteria_fail'        => ['label' => t('admin_securite_event_pwd_faible'),    'icon' => 'fa-key',                  'badge' => 'admin-status-brouillon'],
    'pwd_reset_success'        => ['label' => t('admin_securite_event_pwd_change'),    'icon' => 'fa-circle-check',         'badge' => 'admin-status-publie'],
    'pwd_admin_resend'         => ['label' => t('admin_securite_event_admin_resend'),  'icon' => 'fa-paper-plane',          'badge' => 'admin-status-publie'],
    'otp_request'       => ['label' => t('admin_securite_event_request'),       'icon' => 'fa-paper-plane',          'badge' => 'admin-status-info'],
    'otp_success'       => ['label' => t('admin_securite_event_success'),       'icon' => 'fa-shield-halved',        'badge' => 'admin-status-info'],
    'otp_fail'          => ['label' => t('admin_securite_event_fail'),          'icon' => 'fa-triangle-exclamation', 'badge' => 'admin-status-brouillon'],
    'otp_mismatch'      => ['label' => t('admin_securite_event_mismatch'),      'icon' => 'fa-circle-xmark',         'badge' => 'admin-status-erreur'],
    'otp_locked'        => ['label' => t('admin_securite_event_locked'),        'icon' => 'fa-lock',                 'badge' => 'admin-status-erreur'],
    'otp_rate_limited'  => ['label' => t('admin_securite_event_rate_limited'),  'icon' => 'fa-hourglass-half',       'badge' => 'admin-status-brouillon'],
    'otp_no_phone'      => ['label' => t('admin_securite_event_no_phone'),      'icon' => 'fa-phone-slash',          'badge' => 'admin-status-info'],
];

$page_title = t('admin_securite_titre');
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
            <span><?php echo t('admin_securite_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-shield-halved"></i> <?php echo t('admin_securite_titre'); ?></h1>
        <p><?php echo t('admin_securite_soustitre'); ?></p>
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

        <div class="admin-banner-active-notice">
            <i class="fas fa-circle-info"></i>
            <span><?php echo t('admin_securite_intro'); ?></span>
        </div>

        <div class="admin-galerie-stats">
            <div class="admin-stat-card"><i class="fas fa-paper-plane"></i><div><strong><?php echo $total_liens_envoyes; ?></strong><span><?php echo t('admin_securite_stat_liens'); ?></span></div></div>
            <div class="admin-stat-card"><i class="fas fa-circle-question"></i><div><strong><?php echo $total_emails_inconnus; ?></strong><span><?php echo t('admin_securite_stat_emails_inconnus'); ?></span></div></div>
            <div class="admin-stat-card"><i class="fas fa-circle-check"></i><div><strong><?php echo $total_changes; ?></strong><span><?php echo t('admin_securite_stat_reussies'); ?></span></div></div>
            <div class="admin-stat-card"><i class="fas fa-key"></i><div><strong><?php echo $total_mdp_refuse; ?></strong><span><?php echo t('admin_securite_stat_mdp_refuse'); ?></span></div></div>
        </div>

        <div class="admin-galerie-toolbar">
            <h2 class="admin-section-title"><i class="fas fa-clock-rotate-left"></i> <?php echo t('admin_securite_historique_titre'); ?></h2>
            <div class="admin-securite-toolbar-actions">
                <form action="admin_securite.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_securite_purge_confirm')); ?>">
                    <button type="submit" name="purge_log" class="btn-outline"><i class="fas fa-broom"></i> <?php echo t('admin_securite_purger'); ?></button>
                </form>
                <?php if (!empty($logs)): ?>
                    <form action="admin_securite.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_securite_tout_supprimer_confirm')); ?>">
                        <button type="submit" name="delete_all_log" class="btn-delete"><i class="fas fa-trash-alt"></i> <?php echo t('admin_securite_tout_supprimer'); ?></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($logs)): ?>
            <p class="gallery-empty"><i class="fas fa-shield-halved"></i> <?php echo t('admin_securite_aucun'); ?></p>
        <?php else: ?>
            <div class="admin-subscriber-list">
                <div class="admin-subscriber-row admin-subscriber-head">
                    <span><?php echo t('admin_securite_col_evenement'); ?></span>
                    <span><?php echo t('admin_securite_col_date'); ?></span>
                    <span></span>
                </div>
                <?php foreach ($logs as $log):
                    $info = $event_info[$log['event_type']] ?? ['label' => $log['event_type'], 'icon' => 'fa-circle', 'badge' => 'admin-status-info'];
                    $resolved = $log['identifiant'] ? ($user_lookup[$log['identifiant']] ?? null) : null;
                    $avatar = ($resolved && !empty($resolved['avatar_path'])) ? $resolved['avatar_path'] : 'images/teachers/default-avatar.svg';
                ?>
                    <div class="admin-subscriber-row">
                        <span class="admin-subscriber-email admin-securite-row-main">
                            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="" class="admin-securite-avatar">
                            <span>
                                <i class="fas <?php echo $info['icon']; ?>"></i> <?php echo htmlspecialchars($info['label']); ?>
                                <br><small>
                                    <?php if ($resolved): ?><strong><?php echo htmlspecialchars($resolved['nom']); ?></strong> — <?php endif; ?>
                                    <?php echo htmlspecialchars($log['identifiant'] ?: '—'); ?>
                                </small>
                            </span>
                        </span>
                        <span>
                            <span class="admin-status-badge <?php echo $info['badge']; ?>"><?php echo htmlspecialchars($info['label']); ?></span>
                            <span class="admin-subscriber-date"><i class="far fa-calendar"></i> <?php echo date('d/m/Y à H:i', strtotime($log['created_at'])); ?> <span class="admin-securite-ip">· <?php echo htmlspecialchars($log['ip_address'] ?: '—'); ?></span></span>
                        </span>
                        <span class="admin-album-row-actions">
                            <?php if ($resolved): ?>
                                <form action="admin_securite.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(sprintf(t('admin_securite_resend_confirm'), $resolved['nom'])); ?>">
                                    <button type="submit" name="resend_password_id" value="<?php echo (int) $resolved['id']; ?>" class="btn-outline" title="<?php echo t('admin_securite_resend_btn'); ?>"><i class="fas fa-paper-plane"></i></button>
                                </form>
                            <?php endif; ?>
                            <form action="admin_securite.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_securite_event_supprimer_confirm')); ?>">
                                <button type="submit" name="delete_log_id" value="<?php echo (int) $log['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav class="gallery-pagination" aria-label="Pagination de l'historique">
                    <?php if ($page > 1): ?><a href="admin_securite.php?page=<?php echo $page - 1; ?>" class="page-nav-btn"><i class="fas fa-chevron-left"></i> <?php echo t('precedent'); ?></a><?php endif; ?>
                    <div class="page-numbers">
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <a href="admin_securite.php?page=<?php echo $p; ?>" class="page-number <?php echo $p === $page ? 'is-active' : ''; ?>"><?php echo $p; ?></a>
                        <?php endfor; ?>
                    </div>
                    <?php if ($page < $total_pages): ?><a href="admin_securite.php?page=<?php echo $page + 1; ?>" class="page-nav-btn"><?php echo t('suivant'); ?> <i class="fas fa-chevron-right"></i></a><?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<?php include 'footer.php'; ?>
