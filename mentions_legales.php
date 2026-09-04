<?php
include_once 'language.php';
$page_title = t('mentions_legales_titre');
include 'header.php';
?>

<div class="page-banner historique-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('mentions_legales_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-scale-balanced"></i> <?php echo t('mentions_legales_titre'); ?></h1>
        <p><?php echo t('mentions_legales_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">
        <article class="historique-article legal-page-article">

            <section class="content-block animate-on-scroll">
                <h2><?php echo t('mentions_legales_s1_titre'); ?></h2>
                <p><?php echo t('mentions_legales_s1_texte'); ?></p>
            </section>

            <section class="content-block animate-on-scroll">
                <h2><?php echo t('mentions_legales_s2_titre'); ?></h2>
                <p><?php echo t('mentions_legales_s2_texte'); ?></p>
            </section>

            <section class="content-block animate-on-scroll">
                <h2><?php echo t('mentions_legales_s3_titre'); ?></h2>
                <p><?php echo t('mentions_legales_s3_texte'); ?></p>
            </section>

            <section class="content-block animate-on-scroll">
                <h2><?php echo t('mentions_legales_s4_titre'); ?></h2>
                <p><?php echo t('mentions_legales_s4_texte'); ?></p>
            </section>

            <section class="content-block animate-on-scroll">
                <h2><?php echo t('mentions_legales_s5_titre'); ?></h2>
                <p><?php echo t('mentions_legales_s5_texte'); ?></p>
            </section>

        </article>
    </div>
</div>

<?php include 'footer.php'; ?>
