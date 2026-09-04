<?php
include_once 'language.php';
$page_title = t('histoire_titre');
include 'header.php';

// --- Liens directs des parcours vers leur fiche filière (quand elle existe) ---
$filiere_slug_by_code = [];
if ($mysqli->query("SHOW TABLES LIKE 'filieres'")->num_rows > 0) {
    $res = $mysqli->query("SELECT code, slug FROM filieres");
    while ($row = $res->fetch_assoc()) {
        $filiere_slug_by_code[$row['code']] = $row['slug'];
    }
}

function parcours_item($label, $code, $slug_by_code, $delay = 0) {
    $style = ' style="--delay: ' . $delay . 's"';
    if ($code !== null && isset($slug_by_code[$code])) {
        $url = 'filiere_detail.php?slug=' . urlencode($slug_by_code[$code]);
        return '<li class="parcours-item animate-on-scroll"' . $style . '><a href="' . $url . '" class="parcours-link"><span>' . htmlspecialchars($label) . '</span><i class="fas fa-arrow-right"></i></a></li>';
    }
    return '<li class="parcours-item animate-on-scroll"' . $style . '><span class="parcours-static">' . htmlspecialchars($label) . '</span></li>';
}
?>

<div class="page-banner historique-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('histoire_titre'); ?></span>
        </nav>
        <h1><?php echo t('histoire_titre'); ?></h1>
        <p><?php echo t('histoire_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <!-- Arrière-plan animé -->
    <div class="background-animation">
        <span class="icon"><i class="fas fa-vial"></i></span>
        <span class="icon"><i class="fas fa-satellite"></i></span>
        <span class="icon"><i class="fas fa-robot"></i></span>
        <span class="icon"><i class="fas fa-brain"></i></span>
        <span class="icon"><i class="fas fa-pills"></i></span>
        <span class="icon"><i class="fas fa-laptop-code"></i></span>
        <span class="icon"><i class="fas fa-atom"></i></span>
        <span class="icon"><i class="fas fa-flask"></i></span>
        <span class="icon"><i class="fas fa-dna"></i></span>
        <span class="icon"><i class="fas fa-microscope"></i></span>
        <span class="icon"><i class="fas fa-cogs"></i></span>
    </div>
    <div class="container">
        <article class="historique-article">

            <section class="content-block animate-on-scroll">
                <div class="background-animation">
                    <span class="icon"><i class="fas fa-atom"></i></span><span class="icon"><i class="fas fa-flask"></i></span><span class="icon"><i class="fas fa-dna"></i></span><span class="icon"><i class="fas fa-microscope"></i></span>
                </div>
                <h2><?php echo t('creation_contexte'); ?></h2>
                <p><?php echo t('creation_p1'); ?></p>
                <p><?php echo t('creation_p2'); ?></p>
            </section>

            <section class="content-block animate-on-scroll">
                <div class="background-animation">
                    <span class="icon"><i class="fas fa-satellite"></i></span><span class="icon"><i class="fas fa-robot"></i></span><span class="icon"><i class="fas fa-brain"></i></span><span class="icon"><i class="fas fa-cogs"></i></span>
                </div>
                <h2><?php echo t('objectifs_majeurs'); ?></h2>
                <p><?php echo t('objectifs_p1'); ?></p>
                <ul class="styled-list">
                    <li><?php echo t('objectif_1'); ?></li>
                    <li><?php echo t('objectif_2'); ?></li>
                    <li><?php echo t('objectif_3'); ?></li>
                </ul>
            </section>

            <section class="content-block animate-on-scroll">
                <div class="background-animation">
                    <span class="icon"><i class="fas fa-vial"></i></span><span class="icon"><i class="fas fa-pills"></i></span><span class="icon"><i class="fas fa-laptop-code"></i></span><span class="icon"><i class="fas fa-atom"></i></span>
                </div>
                <h2><?php echo t('statut_pedagogie'); ?></h2>
                <p><?php echo t('statut_p1'); ?></p>
                <p><?php echo t('statut_p2'); ?></p>
            </section>

            <section class="content-block animate-on-scroll">
                <div class="background-animation">
                    <span class="icon"><i class="fas fa-flask"></i></span><span class="icon"><i class="fas fa-dna"></i></span><span class="icon"><i class="fas fa-microscope"></i></span><span class="icon"><i class="fas fa-robot"></i></span>
                </div>
                <h2><?php echo t('offre_formation'); ?></h2>
                <p><?php echo t('offre_p1'); ?></p>
                <div class="mentions-grid">
                    <div class="mention-card"><?php echo t('mention_sti'); ?></div>
                    <div class="mention-card"><?php echo t('mention_stgc'); ?></div>
                    <div class="mention-card"><?php echo t('mention_stnpa'); ?></div>
                </div>

                <div class="formations-container">
                    <div class="formation-column">
                        <h3><?php echo t('licence_pro'); ?></h3>
                        <h4><?php echo t('mention_sti'); ?></h4>
                        <ul class="parcours-list">
                            <?php
                            echo parcours_item(t('histoire_parcours_ge'), 'GE', $filiere_slug_by_code, 0);
                            echo parcours_item(t('histoire_parcours_gi'), 'GIND', $filiere_slug_by_code, 0.08);
                            echo parcours_item(t('parcours_fe'), 'FE', $filiere_slug_by_code, 0.16);
                            ?>
                        </ul>
                        <h4><?php echo t('mention_stgc'); ?></h4>
                        <ul class="parcours-list">
                            <?php
                            echo parcours_item(t('parcours_gc_btp'), 'GCIVIL', $filiere_slug_by_code, 0);
                            echo parcours_item(t('parcours_garch'), 'GARCHI', $filiere_slug_by_code, 0.08);
                            echo parcours_item(t('parcours_gh'), 'GHYD', $filiere_slug_by_code, 0.16);
                            ?>
                        </ul>
                        <h4><?php echo t('mention_stnpa'); ?></h4>
                        <ul class="parcours-list">
                            <?php
                            echo parcours_item(t('histoire_parcours_gei'), 'GEI', $filiere_slug_by_code, 0);
                            echo parcours_item(t('parcours_ginfo'), 'GI', $filiere_slug_by_code, 0.08);
                            echo parcours_item(t('parcours_gbm'), 'GB', $filiere_slug_by_code, 0.16);
                            ?>
                        </ul>
                    </div>
                    <div class="formation-column">
                        <h3><?php echo t('master_recherche'); ?></h3>
                        <h4><?php echo t('mention_stnpa'); ?></h4>
                        <ul class="parcours-list">
                            <?php
                            echo parcours_item(t('master_eii'), 'EII', $filiere_slug_by_code, 0);
                            echo parcours_item(t('master_tr'), 'TR', $filiere_slug_by_code, 0.08);
                            echo parcours_item(t('master_gl'), 'GL', $filiere_slug_by_code, 0.16);
                            echo parcours_item(t('master_gbm'), 'GB', $filiere_slug_by_code, 0.24);
                            ?>
                        </ul>
                        <h4><?php echo t('mention_sti'); ?></h4>
                        <ul class="parcours-list">
                            <?php
                            echo parcours_item(t('master_isea'), 'ISEA', $filiere_slug_by_code, 0);
                            echo parcours_item(t('master_gi'), 'GIND', $filiere_slug_by_code, 0.08);
                            echo parcours_item(t('master_gt'), 'GT', $filiere_slug_by_code, 0.16);
                            ?>
                        </ul>
                        <h4><?php echo t('mention_stgc'); ?></h4>
                        <ul class="parcours-list">
                            <?php
                            echo parcours_item(t('master_gc_bat'), 'GCIVIL', $filiere_slug_by_code, 0);
                            echo parcours_item(t('master_gc_atp'), 'GCIVIL', $filiere_slug_by_code, 0.08);
                            ?>
                        </ul>
                    </div>
                </div>
            </section>

        </article>
    </div>
</div>

<?php include 'footer.php'; ?>