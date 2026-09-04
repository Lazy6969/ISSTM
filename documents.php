<?php
include_once 'language.php';
require_once 'db_connect.php';

$is_logged_student = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && ($_SESSION['user_role'] ?? '') === 'etudiant';
// Un visiteur non connecté est invité à se connecter ; un compte déjà connecté mais qui n'est
// pas étudiant (admin, enseignant, scolarité, compte bibliothèque...) voit un message d'accès
// refusé plutôt qu'un lien de connexion, puisqu'il est déjà authentifié.
$is_logged_in_any = (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) || isset($_SESSION['admin_id']);

$all_documents = $mysqli->query("SELECT * FROM documents ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

$page_title = t('documents_page_titre');
include 'header.php';
?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('documents_page_titre'); ?></span>
        </nav>
        <h1><?php echo t('documents_page_titre'); ?></h1>
        <p><?php echo t('documents_page_intro'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">
        <?php if (empty($all_documents)): ?>
            <p class="gallery-empty"><i class="fas fa-file-lines"></i> <?php echo t('documents_aucun'); ?></p>
        <?php else: ?>
            <div class="admin-album-list">
                <?php foreach ($all_documents as $d): ?>
                    <?php $etu_locked = $d['category'] === 'etudiant' && !$is_logged_student; ?>
                    <div class="admin-album-row">
                        <div class="admin-album-row-thumb" style="display:flex;align-items:center;justify-content:center;background:var(--box-bg-color-alt,#f1f1f1);"><i class="fas fa-file-lines" style="font-size:1.6rem;color:var(--secondary-color);"></i></div>
                        <div class="admin-album-row-info">
                            <h4><?php echo htmlspecialchars($d['title']); ?></h4>
                            <div class="admin-album-row-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($d['created_at'])); ?></span>
                                <?php if ($d['category'] === 'etudiant'): ?>
                                    <span class="documents-badge-etudiant"><i class="fas fa-lock"></i> <?php echo t('documents_badge_etudiant'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="admin-album-row-actions">
                            <?php if ($etu_locked && $is_logged_in_any): ?>
                                <button type="button" class="btn btn-primary bib-cta-locked js-doc-etudiant-only" data-popup-title="<?php echo htmlspecialchars(t('documents_etudiant_popup_titre')); ?>" data-popup-text="<?php echo htmlspecialchars(t('documents_etudiant_popup_texte')); ?>" title="<?php echo t('documents_badge_etudiant'); ?>"><i class="fas fa-lock"></i> <?php echo t('documents_badge_etudiant'); ?></button>
                            <?php elseif ($etu_locked): ?>
                                <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-primary bib-cta-locked" title="<?php echo t('documents_etudiant_connexion_requise'); ?>"><i class="fas fa-lock"></i> <?php echo t('documents_etudiant_connexion_requise'); ?></a>
                            <?php else: ?>
                                <a href="<?php echo htmlspecialchars($d['file_path']); ?>" download class="btn btn-primary"><i class="fas fa-download"></i> <?php echo t('documents_telecharger'); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
