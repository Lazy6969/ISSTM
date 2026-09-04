<?php
include_once 'language.php';
$page_title = t('confidentialite_titre');
include 'header.php';
?>

<div class="page-banner historique-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('confidentialite_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-shield-halved"></i> <?php echo t('confidentialite_titre'); ?></h1>
        <p><?php echo t('confidentialite_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">
        <article class="historique-article legal-page-article">

            <p class="legal-updated"><strong><?php echo t('confidentialite_maj'); ?> :</strong> <?php echo date('d/m/Y'); ?></p>

            <section class="content-block animate-on-scroll">
                <h2><?php echo t('confidentialite_s1_titre'); ?></h2>
                <p><?php echo t('confidentialite_s1_texte'); ?></p>
            </section>

            <section class="content-block animate-on-scroll">
                <h2><?php echo t('confidentialite_s2_titre'); ?></h2>
                <p><?php echo t('confidentialite_s2_texte'); ?></p>
            </section>

            <section class="content-block animate-on-scroll">
                <h2><?php echo t('confidentialite_s3_titre'); ?></h2>
                <p><?php echo t('confidentialite_s3_texte'); ?></p>
            </section>

            <section class="content-block animate-on-scroll">
                <h2><?php echo t('confidentialite_s4_titre'); ?></h2>
                <p><?php echo t('confidentialite_s4_texte'); ?></p>
            </section>

            <section class="content-block animate-on-scroll">
                <h2><?php echo t('confidentialite_s5_titre'); ?></h2>
                <p><?php echo t('confidentialite_s5_texte'); ?></p>
            </section>

        </article>
    </div>
</div>

<?php include 'footer.php'; ?>
