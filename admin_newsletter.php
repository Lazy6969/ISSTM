<?php
include_once 'language.php';
require_once 'db_connect.php';

$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';
if (!$is_admin) {
    header('Location: index.php');
    exit;
}

$flash = null;

// Lien de désabonnement signé, unique par email : évite qu'un lien soit deviné/forgé pour
// désabonner quelqu'un d'autre.
function nl_unsubscribe_link($email) {
    $token = hash_hmac('sha256', $email, NEWSLETTER_SECRET);
    return SITE_URL . '/newsletter_unsubscribe.php?email=' . urlencode($email) . '&token=' . $token;
}

function nl_build_email_body($subject, $message, $email) {
    $unsub = nl_unsubscribe_link($email);
    return '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">'
          . '<h2 style="color:#003366;">' . htmlspecialchars($subject) . '</h2>'
          . '<div>' . nl2br(htmlspecialchars($message)) . '</div>'
          . '<hr><p style="font-size:12px;color:#888;">ISSTM Mahajanga — isstm.umg@gmail.com<br>'
          . '<a href="' . htmlspecialchars($unsub) . '" style="color:#888;">Se désabonner</a></p></div>';
}

// --- Envoi effectif d'une campagne (immédiate ou programmée arrivée à échéance) ---
function nl_send_campaign($mysqli, $campaign_id, $subject, $message) {
    $recipients = $mysqli->query("SELECT email FROM newsletter_subscribers")->fetch_all(MYSQLI_ASSOC);
    $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: ISSTM Mahajanga <isstm.umg@gmail.com>\r\n";
    $sent = 0;
    foreach ($recipients as $r) {
        $body = nl_build_email_body($subject, $message, $r['email']);
        if (@mail($r['email'], $subject, $body, $headers)) {
            $sent++;
        }
    }
    $total = count($recipients);
    $stmt = $mysqli->prepare("UPDATE newsletter_campaigns SET status='envoye', sent_at=NOW(), recipients_count=?, sent_count=? WHERE id=?");
    $stmt->bind_param("iii", $total, $sent, $campaign_id);
    $stmt->execute();
    $stmt->close();
    return [$sent, $total];
}

// --- Dispatch opportuniste des campagnes programmées arrivées à échéance ---
// (Sans tâche planifiée système, on vérifie à chaque visite de cette page. Pour un envoi
// garanti à l'heure même sans visite admin, appeler newsletter_cron.php via le Planificateur
// de tâches Windows.)
$due = $mysqli->query("SELECT * FROM newsletter_campaigns WHERE status='programme' AND scheduled_at <= NOW()")->fetch_all(MYSQLI_ASSOC);
foreach ($due as $campaign) {
    nl_send_campaign($mysqli, $campaign['id'], $campaign['subject'], $campaign['message']);
}

// --- Export CSV (avant tout affichage, pour pouvoir envoyer les en-têtes de téléchargement) ---
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $result = $mysqli->query("SELECT email, subscribed_at FROM newsletter_subscribers ORDER BY subscribed_at DESC");
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="newsletter_abonnes_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, [t('email'), t('admin_date_inscription')]);
    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [$row['email'], $row['subscribed_at']]);
    }
    fclose($out);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_subscriber_id'])) {
        $id = (int) $_POST['delete_subscriber_id'];
        $stmt = $mysqli->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_newsletter.php?flash=" . urlencode('success|Abonné supprimé.'));
        exit;
    }

    // --- Supprimer une campagne de l'historique (envoyée ou programmée) ---
    if (isset($_POST['delete_campaign_id'])) {
        $id = (int) $_POST['delete_campaign_id'];
        $stmt = $mysqli->prepare("DELETE FROM newsletter_campaigns WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_newsletter.php?flash=" . urlencode('success|Campagne supprimée de l\'historique.'));
        exit;
    }

    // --- Envoyer un email à tous les abonnés de la newsletter (immédiat ou programmé) ---
    if (isset($_POST['send_newsletter']) || isset($_POST['schedule_newsletter'])) {
        $subject = trim($_POST['newsletter_subject'] ?? '');
        $message = trim($_POST['newsletter_message'] ?? '');
        if ($subject === '' || $message === '') {
            header("Location: admin_newsletter.php?flash=" . urlencode('error|Le sujet et le message sont obligatoires.'));
            exit;
        }

        if (isset($_POST['schedule_newsletter'])) {
            $scheduled_at = str_replace('T', ' ', trim($_POST['scheduled_at'] ?? ''));
            if ($scheduled_at === '' || strtotime($scheduled_at) === false) {
                header("Location: admin_newsletter.php?flash=" . urlencode('error|Veuillez indiquer une date et une heure d\'envoi valides.'));
                exit;
            }
            $stmt = $mysqli->prepare("INSERT INTO newsletter_campaigns (subject, message, status, scheduled_at) VALUES (?, ?, 'programme', ?)");
            $stmt->bind_param("sss", $subject, $message, $scheduled_at);
            $stmt->execute();
            $stmt->close();
            header("Location: admin_newsletter.php?flash=" . urlencode('success|Envoi programmé pour le ' . date('d/m/Y à H:i', strtotime($scheduled_at)) . '.'));
            exit;
        }

        $total_recipients = (int) $mysqli->query("SELECT COUNT(*) c FROM newsletter_subscribers")->fetch_assoc()['c'];
        if ($total_recipients === 0) {
            header("Location: admin_newsletter.php?flash=" . urlencode('error|Aucun abonné à qui envoyer un email.'));
            exit;
        }

        $stmt = $mysqli->prepare("INSERT INTO newsletter_campaigns (subject, message, status) VALUES (?, ?, 'programme')");
        $stmt->bind_param("ss", $subject, $message);
        $stmt->execute();
        $campaign_id = $mysqli->insert_id;
        $stmt->close();

        [$sent, $total] = nl_send_campaign($mysqli, $campaign_id, $subject, $message);
        header("Location: admin_newsletter.php?flash=" . urlencode('success|Email envoyé à ' . $sent . ' abonné(s) sur ' . $total . '.'));
        exit;
    }

    // --- Ajouter un abonné manuellement ---
    if (isset($_POST['add_subscriber'])) {
        $email = trim($_POST['new_subscriber_email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: admin_newsletter.php?flash=" . urlencode('error|Adresse email invalide.'));
            exit;
        }
        $stmt = $mysqli->prepare("INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $added = $stmt->affected_rows > 0;
        $stmt->close();
        header("Location: admin_newsletter.php?flash=" . urlencode($added ? 'success|Abonné ajouté.' : 'error|Cette adresse est déjà abonnée.'));
        exit;
    }
}

if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

$f_q = trim($_GET['fq'] ?? '');
$where = '1=1';
$params = [];
$types = '';
if ($f_q !== '') {
    $where = 'email LIKE ?';
    $params[] = '%' . $f_q . '%';
    $types = 's';
}
$stmt = $mysqli->prepare("SELECT * FROM newsletter_subscribers WHERE $where ORDER BY subscribed_at DESC");
if ($types !== '') { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$subscribers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = $mysqli->query("SELECT COUNT(*) c FROM newsletter_subscribers")->fetch_assoc()['c'];
$this_month = $mysqli->query("SELECT COUNT(*) c FROM newsletter_subscribers WHERE MONTH(subscribed_at) = MONTH(NOW()) AND YEAR(subscribed_at) = YEAR(NOW())")->fetch_assoc()['c'];

$campaigns = $mysqli->query("SELECT * FROM newsletter_campaigns ORDER BY COALESCE(sent_at, scheduled_at, created_at) DESC LIMIT 30")->fetch_all(MYSQLI_ASSOC);

$page_title = t('admin_newsletter_titre');

include 'header.php';
?>

<a href="administrateur.php" class="btn-outline btn-back-sticky"><i class="fas fa-arrow-left"></i> <?php echo t('admin_retour_tableau_bord'); ?></a>

<div class="page-banner news-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="administrateur.php"><?php echo t('administration'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('admin_newsletter_breadcrumb'); ?></span>
        </nav>
        <h1><?php echo t('admin_newsletter_titre'); ?></h1>
        <p><?php echo t('admin_newsletter_soustitre'); ?></p>
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

        <div class="admin-galerie-stats">
            <div class="admin-stat-card"><i class="fas fa-envelope-open-text"></i><div><strong><?php echo (int) $total; ?></strong><span><?php echo t('admin_newsletter_abonnes_total'); ?></span></div></div>
            <div class="admin-stat-card"><i class="fas fa-calendar-plus"></i><div><strong><?php echo (int) $this_month; ?></strong><span><?php echo t('admin_newsletter_nouveaux_mois'); ?></span></div></div>
        </div>

        <div class="admin-newsletter-compose">
            <h2 class="admin-section-title"><i class="fas fa-paper-plane"></i> <?php echo t('admin_newsletter_composer_titre'); ?></h2>
            <?php $confirm_envoi_msg = t('admin_newsletter_confirm_envoi') . ' (' . (int) $total . ')'; ?>
            <form action="admin_newsletter.php" method="POST" id="newsletter-compose-form">
                <div class="form-group">
                    <label><?php echo t('admin_newsletter_sujet'); ?></label>
                    <input type="text" name="newsletter_subject" placeholder="<?php echo t('admin_newsletter_sujet_placeholder'); ?>" required>
                </div>
                <div class="form-group">
                    <label><?php echo t('admin_newsletter_message'); ?></label>
                    <textarea name="newsletter_message" rows="6" placeholder="<?php echo t('admin_newsletter_message_placeholder'); ?>" required></textarea>
                </div>
                <div class="form-group">
                    <label><i class="far fa-clock"></i> <?php echo t('admin_newsletter_programmer_label'); ?></label>
                    <input type="datetime-local" name="scheduled_at" min="<?php echo date('Y-m-d\TH:i'); ?>">
                    <p class="admin-field-hint"><?php echo t('admin_newsletter_programmer_hint'); ?></p>
                </div>
                <div class="admin-newsletter-compose-actions">
                    <button type="submit" name="send_newsletter" id="newsletter-send-now-btn" class="btn-add-item js-confirm-btn" data-confirm-msg="<?php echo htmlspecialchars($confirm_envoi_msg); ?>"><i class="fas fa-paper-plane"></i> <?php echo t('admin_newsletter_envoyer'); ?></button>
                    <button type="submit" name="schedule_newsletter" class="btn-outline"><i class="far fa-clock"></i> <?php echo t('admin_newsletter_programmer_btn'); ?></button>
                </div>
            </form>
        </div>

        <div class="admin-newsletter-compose">
            <h2 class="admin-section-title"><i class="fas fa-user-plus"></i> <?php echo t('admin_newsletter_ajouter_abonne_titre'); ?></h2>
            <form action="admin_newsletter.php" method="POST" class="admin-galerie-filters">
                <input type="email" name="new_subscriber_email" placeholder="<?php echo t('admin_newsletter_nouvel_email_placeholder'); ?>" required>
                <button type="submit" name="add_subscriber" class="btn-add-item"><i class="fas fa-plus"></i> <?php echo t('admin_newsletter_ajouter_abonne_btn'); ?></button>
            </form>
        </div>

        <h2 class="admin-section-title"><i class="fas fa-users"></i> <?php echo t('admin_newsletter_liste_abonnes_titre'); ?></h2>
        <div class="admin-galerie-toolbar">
            <a href="admin_newsletter.php?export=csv" class="btn-add-item admin-new-album-btn"><i class="fas fa-file-csv"></i> <?php echo t('admin_newsletter_export_csv'); ?></a>
            <form method="GET" action="admin_newsletter.php" class="admin-galerie-filters">
                <input type="text" name="fq" value="<?php echo htmlspecialchars($f_q); ?>" placeholder="<?php echo t('admin_newsletter_rechercher_email'); ?>">
                <button type="submit" class="btn-filter-gallery"><i class="fas fa-filter"></i></button>
            </form>
        </div>

        <?php if (empty($subscribers)): ?>
            <p class="gallery-empty"><i class="fas fa-envelope-open-text"></i> <?php echo t('admin_newsletter_aucun_abonne'); ?></p>
        <?php else: ?>
            <div class="admin-subscriber-list">
                <div class="admin-subscriber-row admin-subscriber-head">
                    <span><?php echo t('email'); ?></span>
                    <span><?php echo t('admin_date_inscription'); ?></span>
                    <span></span>
                </div>
                <?php foreach ($subscribers as $sub): ?>
                    <div class="admin-subscriber-row">
                        <span class="admin-subscriber-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($sub['email']); ?></span>
                        <span class="admin-subscriber-date"><i class="far fa-calendar"></i> <?php echo date('d/m/Y à H:i', strtotime($sub['subscribed_at'])); ?></span>
                        <form action="admin_newsletter.php<?php echo $f_q !== '' ? '?fq=' . urlencode($f_q) : ''; ?>" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_newsletter_confirm_delete')); ?>">
                            <button type="submit" name="delete_subscriber_id" value="<?php echo $sub['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2 class="admin-section-title"><i class="fas fa-clock-rotate-left"></i> <?php echo t('admin_newsletter_historique_titre'); ?></h2>
        <?php if (empty($campaigns)): ?>
            <p class="gallery-empty"><i class="fas fa-paper-plane"></i> <?php echo t('admin_newsletter_aucun_historique'); ?></p>
        <?php else: ?>
            <div class="admin-subscriber-list">
                <div class="admin-subscriber-row admin-subscriber-head">
                    <span><?php echo t('admin_newsletter_sujet'); ?></span>
                    <span><?php echo t('admin_statut_label'); ?></span>
                    <span></span>
                </div>
                <?php foreach ($campaigns as $c): ?>
                    <div class="admin-subscriber-row">
                        <span class="admin-subscriber-email"><i class="fas fa-envelope-open-text"></i> <?php echo htmlspecialchars($c['subject']); ?></span>
                        <span>
                            <?php if ($c['status'] === 'envoye'): ?>
                                <span class="admin-status-badge admin-status-publie"><i class="fas fa-circle-check"></i> <?php echo t('admin_newsletter_statut_envoye'); ?> (<?php echo (int) $c['sent_count']; ?>/<?php echo (int) $c['recipients_count']; ?>)</span>
                            <?php else: ?>
                                <span class="admin-status-badge admin-status-brouillon"><i class="far fa-clock"></i> <?php echo t('admin_newsletter_statut_programme'); ?></span>
                            <?php endif; ?>
                            <span class="admin-subscriber-date"><i class="far fa-calendar"></i>
                                <?php echo $c['status'] === 'envoye'
                                    ? date('d/m/Y à H:i', strtotime($c['sent_at']))
                                    : date('d/m/Y à H:i', strtotime($c['scheduled_at'])); ?>
                            </span>
                        </span>
                        <form action="admin_newsletter.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_newsletter_confirm_delete_campagne')); ?>">
                            <button type="submit" name="delete_campaign_id" value="<?php echo (int) $c['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include 'footer.php'; ?>
