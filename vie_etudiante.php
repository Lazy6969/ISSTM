<?php
include_once 'language.php';
$page_title = t('vie_etudiante_titre');
include 'header.php';
?>

<div class="page-banner vie-etudiante-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('vie_etudiante_titre'); ?></span>
        </nav>
        <h1><?php echo t('vie_etudiante_titre'); ?></h1>
        <p><?php echo t('vie_etudiante_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="background-animation">
        <span class="icon"><i class="fas fa-users"></i></span>
        <span class="icon"><i class="fas fa-campground"></i></span>
        <span class="icon"><i class="fas fa-music"></i></span>
        <span class="icon"><i class="fas fa-futbol"></i></span>
        <span class="icon"><i class="fas fa-handshake"></i></span>
    </div>
    <div class="container">

        <!-- Paragraphe d'introduction stylé -->
        <div class="intro-with-image">
            <div class="intro-image">
                <img src="images/campus/etudiant1.png" alt="<?php echo t('vie_etudiante_titre'); ?>">
            </div>
            <div class="page-intro-text">
                <p><?php echo t('vie_etudiante_intro'); ?></p>
            </div>
        </div>
        
        <!-- Section "Vie au Campus" -->
        <section class="content-block animate-on-scroll">
            <div class="portal-card with-slideshow">
                <div class="portal-slideshow">
                    <div class="portal-slide" style="background-image: url('images/portal_campus_1.jpg');"></div>
                    <div class="portal-slide" style="background-image: url('images/portal_campus_2.jpg');"></div>
                    <div class="portal-slide" style="background-image: url('images/portal_campus_3.jpg');"></div>
                </div>
                <div class="portal-overlay"></div>
                <div class="portal-content">
                    <div class="portal-icon"><img src="images/umg.jpg" alt="Logo Université de Mahajanga"></div>
                    <h3><?php echo t('campus_titre'); ?></h3>
                    <p><?php echo t('campus_portail_desc'); ?></p>
                    <a href="campus.php" class="btn btn-primary btn-large"><?php echo t('decouvrir'); ?></a>
                </div>
            </div>
        </section>

        <!-- Paragraphe d'introduction pour les clubs -->
        <div class="intro-with-image image-right">
            <div class="intro-image">
                <img src="images/campus/etudiant2.png" alt="<?php echo t('associations_titre'); ?>">
            </div>
            <div class="page-intro-text">
                <p><?php echo t('associations_intro'); ?></p>
            </div>
        </div>

        <!-- Section "Clubs et Associations" -->
        <section class="content-block animate-on-scroll">
            <div class="portal-card with-slideshow">
                <div class="portal-slideshow">
                    <div class="portal-slide" style="background-image: url('images/portal_assoc_4.jpg');"></div>
                    <div class="portal-slide" style="background-image: url('images/portal_assoc_5.jpg');"></div>
                    <div class="portal-slide" style="background-image: url('images/portal_assoc_6.jpg');"></div>
                </div>
                <div class="portal-overlay"></div>
                <div class="portal-content">
                    <div class="portal-icon"><img src="images/aei.jpeg" alt="Logo Associations"></div>
                    <h3><?php echo t('associations_titre'); ?></h3>
                    <p><?php echo t('associations_portail_desc'); ?></p>
                    <!-- Bouton pour ouvrir la modale des statuts -->
                    <a href="associations.php" class="btn btn-primary btn-large"><?php echo t('decouvrir'); ?></a>
                </div>
            </div>
        </section>

    </div>
</div>

<?php include 'footer.php'; ?>
