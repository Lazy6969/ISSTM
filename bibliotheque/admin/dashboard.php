<?php
include_once '../../language.php';
require_once '../../db_connect.php';
require '../config.php';
require '../functions.php';
requireAdmin();

$page_title = t('bib_admin_dashboard_titre');

$nb_canevas = $pdo->query("SELECT COUNT(*) FROM canevas")->fetchColumn();
$nb_memoires = $pdo->query("SELECT COUNT(*) FROM memoires WHERE categorie = 'Mémoire'")->fetchColumn();
$nb_projets = $pdo->query("SELECT COUNT(*) FROM memoires WHERE categorie = 'Projet'")->fetchColumn();
$nb_filieres = $pdo->query("SELECT COUNT(*) FROM filieres")->fetchColumn();

// Mémoires/projets ajoutés : vue par mois (d'une année choisie) ou agrégée par année
$vueGraphique = ($_GET['vue'] ?? 'mois') === 'annee' ? 'annee' : 'mois';

$anneesDisponibles = $pdo->query("SELECT DISTINCT YEAR(date_ajout) AS annee FROM memoires ORDER BY annee")->fetchAll(PDO::FETCH_COLUMN);
if (!$anneesDisponibles) {
    $anneesDisponibles = [(int) date('Y')];
}
$anneeSelectionnee = (int) ($_GET['annee'] ?? end($anneesDisponibles));
if (!in_array($anneeSelectionnee, $anneesDisponibles)) {
    $anneeSelectionnee = (int) end($anneesDisponibles);
}

$nomsMois = ['01'=>'Jan','02'=>'Fév','03'=>'Mar','04'=>'Avr','05'=>'Mai','06'=>'Juin',
             '07'=>'Juil','08'=>'Août','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Déc'];

if ($vueGraphique === 'annee') {
    $stmt = $pdo->query("SELECT YEAR(date_ajout) AS cle, COUNT(*) AS total FROM memoires GROUP BY cle ORDER BY cle");
    $donneesGraphique = [];
    foreach ($stmt->fetchAll() as $ligne) {
        $donneesGraphique[(string) $ligne['cle']] = (int) $ligne['total'];
    }
    if (!$donneesGraphique) {
        $donneesGraphique = [(string) date('Y') => 0];
    }
} else {
    $stmt = $pdo->prepare("SELECT MONTH(date_ajout) AS mois, COUNT(*) AS total FROM memoires WHERE YEAR(date_ajout) = ? GROUP BY mois");
    $stmt->execute([$anneeSelectionnee]);
    $parMois = [];
    foreach ($stmt->fetchAll() as $ligne) {
        $parMois[(int) $ligne['mois']] = (int) $ligne['total'];
    }
    $donneesGraphique = [];
    for ($m = 1; $m <= 12; $m++) {
        $donneesGraphique[str_pad((string) $m, 2, '0', STR_PAD_LEFT)] = $parMois[$m] ?? 0;
    }
}
$maxDonneesGraphique = max(1, max($donneesGraphique));

require '../../header.php';
?>

<div class="page-banner bib-page-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="../index.php"><?php echo t('bib_nav_titre'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('bib_admin_dashboard_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-toolbox"></i> <?php echo t('bib_admin_dashboard_titre'); ?></h1>
        <p><?php echo t('bib_admin_dashboard_connecte_en_tant_que'); ?> <strong><?php echo e(nomAdminConnecte()); ?></strong></p>
    </div>
</div>

<div class="page-content">
    <div class="container">
        <div class="admin-dashboard">

            <div class="admin-galerie-stats">
                <div class="admin-stat-card"><i class="fas fa-file-lines"></i><div><strong><?php echo $nb_canevas; ?></strong><span><?php echo t('bib_stat_canevas'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-graduation-cap"></i><div><strong><?php echo $nb_memoires; ?></strong><span><?php echo t('bib_stat_memoires'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-diagram-project"></i><div><strong><?php echo $nb_projets; ?></strong><span><?php echo t('bib_stat_projets'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-school"></i><div><strong><?php echo $nb_filieres; ?></strong><span><?php echo t('bib_stat_filieres'); ?></span></div></div>
            </div>

            <div class="bib-graphique-toolbar">
                <p class="bib-graphique-titre"><?php echo t('bib_graphique_titre'); ?></p>
                <div class="bib-graphique-controles">
                    <div class="bib-graphique-tabs">
                        <a href="?vue=mois&annee=<?php echo $anneeSelectionnee; ?>" class="bib-graphique-tab <?php echo $vueGraphique === 'mois' ? 'is-active' : ''; ?>"><?php echo t('bib_graphique_par_mois'); ?></a>
                        <a href="?vue=annee" class="bib-graphique-tab <?php echo $vueGraphique === 'annee' ? 'is-active' : ''; ?>"><?php echo t('bib_graphique_par_annee'); ?></a>
                    </div>
                    <?php if ($vueGraphique === 'mois'): ?>
                        <form method="get" class="bib-graphique-annee-form">
                            <input type="hidden" name="vue" value="mois">
                            <select name="annee" onchange="this.form.submit()">
                                <?php foreach ($anneesDisponibles as $an): ?>
                                    <option value="<?php echo $an; ?>" <?php echo $an == $anneeSelectionnee ? 'selected' : ''; ?>><?php echo $an; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="bib-mini-graphique">
                <?php foreach ($donneesGraphique as $cle => $total): ?>
                    <div class="bib-barre-groupe">
                        <div class="bib-barre" style="height: <?php echo max(4, round($total / $maxDonneesGraphique * 100)); ?>%;" title="<?php echo $total; ?>"></div>
                        <span class="bib-barre-label"><?php echo $vueGraphique === 'annee' ? $cle : $nomsMois[$cle]; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2 class="admin-section-title"><?php echo t('bib_dashboard_contenu_titre'); ?></h2>
            <div class="dashboard-actions">
                <a href="ajouter_canevas.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-file-circle-plus"></i></div>
                    <h3><?php echo t('bib_dashboard_ajouter_canevas'); ?></h3>
                    <p><?php echo t('bib_dashboard_ajouter_canevas_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="gerer_canevas.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-folder-open"></i></div>
                    <h3><?php echo t('bib_dashboard_gerer_canevas'); ?></h3>
                    <p><?php echo t('bib_dashboard_gerer_canevas_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="ajouter_memoire.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-file-circle-plus"></i></div>
                    <h3><?php echo t('bib_dashboard_ajouter_memoire'); ?></h3>
                    <p><?php echo t('bib_dashboard_ajouter_memoire_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="gerer_memoires.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-book"></i></div>
                    <h3><?php echo t('bib_dashboard_gerer_memoires'); ?></h3>
                    <p><?php echo t('bib_dashboard_gerer_memoires_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
            </div>

            <h2 class="admin-section-title"><?php echo t('bib_dashboard_vie_titre'); ?></h2>
            <div class="dashboard-actions">
                <a href="gerer_horaire.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-clock"></i></div>
                    <h3><?php echo t('bib_dashboard_horaire'); ?></h3>
                    <p><?php echo t('bib_dashboard_horaire_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="gerer_actualite.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-bullhorn"></i></div>
                    <h3><?php echo t('bib_dashboard_actualites'); ?></h3>
                    <p><?php echo t('bib_dashboard_actualites_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="gerer_carousel.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-images"></i></div>
                    <h3><?php echo t('bib_dashboard_carousel'); ?></h3>
                    <p><?php echo t('bib_dashboard_carousel_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
            </div>

            <h2 class="admin-section-title"><?php echo t('bib_dashboard_structure_titre'); ?></h2>
            <div class="dashboard-actions">
                <a href="gerer_filieres.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-school"></i></div>
                    <h3><?php echo t('bib_dashboard_filieres'); ?></h3>
                    <p><?php echo t('bib_dashboard_filieres_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
            </div>

            <h2 class="admin-section-title"><?php echo t('bib_dashboard_compte_titre'); ?></h2>
            <div class="dashboard-actions">
                <a href="profil.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-user-gear"></i></div>
                    <h3><?php echo t('bib_mon_profil'); ?></h3>
                    <p><?php echo t('bib_dashboard_compte_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
            </div>

        </div>
    </div>
</div>

<?php require '../../footer.php'; ?>
