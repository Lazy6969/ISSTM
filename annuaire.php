<?php
// Page autonome conservée pour compatibilité (liens/marque-pages existants) : le rendu réel vit
// désormais dans annuaire_partiel.php, partagé avec l'onglet "Recherche" du hub mes_amis.php.
include_once 'language.php';
require_once 'db_connect.php';
require_once 'amis_functions.php';

amis_require_login();
$self_id = (int) $_SESSION['user_id'];
$search_action_url = 'annuaire.php';

$page_title = t('annuaire_titre');
include 'header.php';
?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('annuaire_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-magnifying-glass"></i> <?php echo t('annuaire_titre'); ?></h1>
        <p><?php echo t('annuaire_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">
        <?php include 'annuaire_partiel.php'; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
