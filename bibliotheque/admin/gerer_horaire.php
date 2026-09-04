<?php
include_once '../../language.php';
require_once '../../db_connect.php';
require '../config.php';
require '../functions.php';
requireAdmin();

$page_title = t('bib_gerer_horaire_titre');
$succes = '';

$jours_ordre = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
$jours_traduction = [
    'Lundi' => 'bib_jour_lundi', 'Mardi' => 'bib_jour_mardi', 'Mercredi' => 'bib_jour_mercredi',
    'Jeudi' => 'bib_jour_jeudi', 'Vendredi' => 'bib_jour_vendredi', 'Samedi' => 'bib_jour_samedi',
    'Dimanche' => 'bib_jour_dimanche',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE horaires SET heure_ouverture = ?, heure_fermeture = ?, actif = ? WHERE jour = ?");
    foreach ($jours_ordre as $jour) {
        $ouverture = $_POST['ouverture_' . $jour] ?? '08:00';
        $fermeture = $_POST['fermeture_' . $jour] ?? '17:00';
        $actif = isset($_POST['actif_' . $jour]) ? 1 : 0;
        $stmt->execute([$ouverture, $fermeture, $actif, $jour]);
    }
    $succes = t('bib_succes_horaire_maj');
}

$parJour = [];
foreach ($pdo->query("SELECT * FROM horaires")->fetchAll() as $h) {
    $parJour[$h['jour']] = $h;
}

require '../../header.php';
?>

<a href="dashboard.php" class="btn-outline btn-back-sticky"><i class="fas fa-arrow-left"></i> <?php echo t('admin_retour_tableau_bord'); ?></a>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="dashboard.php"><?php echo t('bib_admin_dashboard_titre'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('bib_gerer_horaire_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-clock"></i> <?php echo t('bib_gerer_horaire_titre'); ?></h1>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <?php if ($succes): ?><div class="admin-flash admin-flash-success"><i class="fas fa-circle-check"></i> <?php echo e($succes); ?></div><?php endif; ?>

        <form method="post" class="bib-horaire-form">
            <table class="bib-table-horaire">
                <thead>
                    <tr><th><?php echo t('bib_jour'); ?></th><th><?php echo t('bib_ouverture'); ?></th><th><?php echo t('bib_fermeture'); ?></th><th><?php echo t('bib_actif'); ?></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($jours_ordre as $jour): $h = $parJour[$jour]; ?>
                        <tr>
                            <td><?php echo t($jours_traduction[$jour]); ?></td>
                            <td><input type="time" name="ouverture_<?php echo $jour; ?>" value="<?php echo substr($h['heure_ouverture'], 0, 5); ?>"></td>
                            <td><input type="time" name="fermeture_<?php echo $jour; ?>" value="<?php echo substr($h['heure_fermeture'], 0, 5); ?>"></td>
                            <td><input type="checkbox" name="actif_<?php echo $jour; ?>" <?php echo $h['actif'] ? 'checked' : ''; ?>></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> <?php echo t('bib_enregistrer'); ?></button>
        </form>

    </div>
</div>

<?php require '../../footer.php'; ?>
