<?php
include_once 'language.php';
$page_title = t('bourse_titre');
include 'header.php';
?>

<div class="page-banner bourse-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('bourse_titre'); ?></span>
        </nav>
        <h1><?php echo t('bourse_titre'); ?></h1>
        <p><?php echo t('bourse_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <!-- Arrière-plan animé pour la cohérence visuelle -->
    <div class="background-animation">
        <span class="icon"><i class="fas fa-graduation-cap"></i></span>
        <span class="icon"><i class="fas fa-award"></i></span>
        <span class="icon"><i class="fas fa-hand-holding-usd"></i></span>
        <span class="icon"><i class="fas fa-book-open"></i></span>
    </div>
    <div class="container">
        <article class="form-article">
            <!-- La page se concentre désormais sur la redirection vers les plateformes officielles -->
            <div class="external-link-cards">
                <div class="external-link-card">
                    <img src="images/partenariat/mesupres.png" alt="Logo MESupReS" class="external-link-logo">
                    <h3><?php echo t('bourse_externe_titre'); ?></h3>
                    <p><?php echo t('bourse_externe_desc'); ?></p>
                    <a href="https://boursesext.mesupres.edu.mg/" target="_blank" class="btn btn-primary external-link-btn">
                        <i class="fas fa-external-link-alt"></i> <?php echo t('bourse_externe_bouton'); ?>
                    </a>
                </div>
                <div class="external-link-card">
                    <img src="images/partenariat/tresor.png" alt="Logo Trésor Public" class="external-link-logo">
                    <h3><?php echo t('bourse_tresor_titre'); ?></h3>
                    <p><?php echo t('bourse_tresor_desc'); ?></p>
                    <a href="https://app.tresorpublic.mg:12000/wallet/login" target="_blank" class="btn btn-primary external-link-btn">
                        <i class="fas fa-external-link-alt"></i> <?php echo t('bourse_tresor_bouton'); ?>
                    </a>
                </div>
            </div>
        </article>
    </div>
</div>

<?php include 'footer.php'; ?>