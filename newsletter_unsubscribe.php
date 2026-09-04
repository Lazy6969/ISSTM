<?php
include_once 'language.php';
require_once 'db_connect.php';

$email = trim($_GET['email'] ?? '');
$token = $_GET['token'] ?? '';
$state = 'invalid'; // invalid | success | already

if ($email !== '' && $token !== '' && hash_equals(hash_hmac('sha256', $email, NEWSLETTER_SECRET), $token)) {
    $stmt = $mysqli->prepare("DELETE FROM newsletter_subscribers WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $state = $stmt->affected_rows > 0 ? 'success' : 'already';
    $stmt->close();
}

$page_title = t('newsletter_desabo_titre');
include 'header.php';
?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('newsletter_desabo_titre'); ?></span>
        </nav>
        <h1><?php echo t('newsletter_desabo_titre'); ?></h1>
    </div>
</div>

<div class="page-content">
    <div class="container" style="max-width:600px; text-align:center;">
        <?php if ($state === 'success'): ?>
            <div class="admin-flash admin-flash-success"><i class="fas fa-circle-check"></i> <?php echo t('newsletter_desabo_succes'); ?></div>
        <?php elseif ($state === 'already'): ?>
            <div class="admin-flash admin-flash-success"><i class="fas fa-circle-check"></i> <?php echo t('newsletter_desabo_deja'); ?></div>
        <?php else: ?>
            <div class="admin-flash admin-flash-error"><i class="fas fa-circle-exclamation"></i> <?php echo t('newsletter_desabo_erreur'); ?></div>
        <?php endif; ?>
        <p style="margin-top:20px;"><a href="index.php" class="btn btn-primary"><?php echo t('accueil'); ?></a></p>
    </div>
</div>

<?php include 'footer.php'; ?>
