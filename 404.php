<?php
http_response_code(404);
include_once 'language.php';
$page_title = t('erreur_404_titre');
include 'header.php';
?>

<div class="page-banner historique-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('erreur_404_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-triangle-exclamation"></i> <?php echo t('erreur_404_titre'); ?></h1>
    </div>
</div>

<div class="page-content">
    <div class="container">
        <div class="error-404-block">
            <p class="error-404-code"><?php echo t('erreur_404_code'); ?></p>
            <p class="error-404-text"><?php echo t('erreur_404_texte'); ?></p>

            <a href="index.php" class="btn-primary"><i class="fas fa-house"></i> <?php echo t('erreur_404_retour_accueil'); ?></a>

            <p class="error-404-search-label"><?php echo t('erreur_404_recherche'); ?></p>
            <form method="GET" action="recherche.php" class="search-page-form">
                <input type="search" name="q" placeholder="<?php echo t('rechercher_placeholder'); ?>" autocomplete="off">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
