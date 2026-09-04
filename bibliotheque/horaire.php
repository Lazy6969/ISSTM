<?php
include_once '../language.php';
require_once '../db_connect.php';
require 'config.php';
require 'functions.php';

if (!estUtilisateurConnecte()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$page_title = t('bib_horaire_titre');

$jours_ordre = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
$jours_traduction = [
    'Lundi' => 'bib_jour_lundi', 'Mardi' => 'bib_jour_mardi', 'Mercredi' => 'bib_jour_mercredi',
    'Jeudi' => 'bib_jour_jeudi', 'Vendredi' => 'bib_jour_vendredi', 'Samedi' => 'bib_jour_samedi',
    'Dimanche' => 'bib_jour_dimanche',
];
$stmt = $pdo->query("SELECT * FROM horaires");
$horaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Réordonner selon les jours de la semaine
usort($horaires, fn($a, $b) => array_search($a['jour'], $jours_ordre) <=> array_search($b['jour'], $jours_ordre));

$stmt = $pdo->query("SELECT * FROM actualites_horaire
                      WHERE date_fin IS NULL OR date_fin >= CURDATE()
                      ORDER BY date_publication DESC");
$actualites = $stmt->fetchAll();

$photosParActualite = [];
if ($actualites) {
    $ids = implode(',', array_map('intval', array_column($actualites, 'id')));
    foreach ($pdo->query("SELECT * FROM actualite_photos WHERE actualite_id IN ($ids) ORDER BY id")->fetchAll() as $p) {
        $photosParActualite[$p['actualite_id']][] = $p;
    }
}

require '../header.php';
?>

<div class="page-banner bib-page-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php"><?php echo t('bib_nav_titre'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('bib_horaire_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-clock"></i> <?php echo t('bib_horaire_titre'); ?></h1>
        <p><?php echo t('bib_horaire_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <?php if ($actualites): ?>
            <?php foreach ($actualites as $act): ?>
                <div class="alerte-bib">
                    <strong><?php echo e($act['message']); ?></strong>
                    <span class="bib-date-actu">
                        (<?php echo t('bib_du'); ?> <?php echo date('d/m/Y', strtotime($act['date_debut'])); ?>
                        <?php echo $act['date_fin'] ? ' ' . t('bib_au') . ' ' . date('d/m/Y', strtotime($act['date_fin'])) : ''; ?>)
                    </span>
                    <?php if (!empty($photosParActualite[$act['id']])): ?>
                        <div class="bib-actu-photos">
                            <?php foreach ($photosParActualite[$act['id']] as $p): ?>
                                <img src="uploads/actualites/<?php echo e($p['chemin_fichier']); ?>" alt="" loading="lazy">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <table class="bib-table-horaire">
            <thead>
                <tr><th><?php echo t('bib_jour'); ?></th><th><?php echo t('bib_ouverture'); ?></th><th><?php echo t('bib_fermeture'); ?></th><th><?php echo t('bib_statut'); ?></th></tr>
            </thead>
            <tbody>
                <?php foreach ($horaires as $h): ?>
                    <tr class="<?php echo $h['actif'] ? '' : 'bib-jour-ferme'; ?>">
                        <td><?php echo t($jours_traduction[$h['jour']]); ?></td>
                        <td><?php echo $h['actif'] ? date('H:i', strtotime($h['heure_ouverture'])) : '-'; ?></td>
                        <td><?php echo $h['actif'] ? date('H:i', strtotime($h['heure_fermeture'])) : '-'; ?></td>
                        <td>
                            <?php if ($h['actif']): ?>
                                <i class="fas fa-circle-check bib-statut-ouvert"></i> <?php echo t('bib_ouvert'); ?>
                            <?php else: ?>
                                <i class="fas fa-circle-xmark bib-statut-ferme"></i> <?php echo t('bib_ferme'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>

<?php require '../footer.php'; ?>
