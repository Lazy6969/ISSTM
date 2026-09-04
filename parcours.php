<?php
include_once 'language.php';
require_once 'db_connect.php';

// Téléchargements organigramme/cursus réservés aux comptes actifs (connectés) : un visiteur non
// connecté voit un CTA vers la connexion à la place des liens de téléchargement.
$org_is_logged_in = (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) || isset($_SESSION['admin_id']);

$page_title = t('parcours_titre');

// Noms/photos éditables depuis l'administration (admin_organigramme.php).
// La structure de l'organigramme (qui dépend de qui) reste codée ici ; seuls le nom
// et la photo affichés pour chaque poste sont surchargeables depuis la base de données.
$org_people = [];
$og_result = $mysqli->query("SELECT title_key, name, photo FROM org_people");
while ($og_row = $og_result->fetch_assoc()) {
    $org_people[$og_row['title_key']] = $og_row;
}

include 'header.php';

/**
 * Génère une carte de profil pour l'organigramme.
 * Si $targetId est fourni, la carte devient un bouton dépliant/repliant un panneau enfant.
 */
function og_card($titleKey, $name, $photo, $color, $descKey = null, $roleKey = null, $targetId = null) {
    global $org_people;
    if (isset($org_people[$titleKey])) {
        if ($org_people[$titleKey]['name'] !== '' && $org_people[$titleKey]['name'] !== null) {
            $name = $org_people[$titleKey]['name'];
        }
        if ($org_people[$titleKey]['photo'] !== '' && $org_people[$titleKey]['photo'] !== null) {
            $photo = $org_people[$titleKey]['photo'];
        }
    }
    $title = t($titleKey);
    $nameOut = $name . ($roleKey ? ' · ' . t($roleKey) : '');
    $titleAttr = $descKey ? ' title="' . htmlspecialchars(t($descKey)) . '"' : '';
    $photoTag = $photo
        ? '<img class="og-card-photo" src="images/teachers/' . $photo . '" alt="' . htmlspecialchars($name) . '" loading="lazy">'
        : '<div class="og-card-photo og-card-photo-placeholder"><i class="fas fa-landmark"></i></div>';
    $inner = $photoTag . '<span class="og-card-title">' . $title . '</span><span class="og-card-name">' . $nameOut . '</span>';

    if ($targetId) {
        return '<button type="button" class="og-card og-card-toggle ' . $color . ' animate-on-scroll" '
            . 'data-target="' . $targetId . '" aria-expanded="false" aria-controls="' . $targetId . '"' . $titleAttr . '>'
            . $inner . '<i class="fas fa-chevron-down og-card-chevron"></i></button>';
    }
    return '<div class="og-card ' . $color . ' animate-on-scroll"' . $titleAttr . '>' . $inner . '</div>';
}
?>

<div class="page-banner parcours-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('parcours_titre'); ?></span>
        </nav>
        <h1><?php echo t('parcours_titre'); ?></h1>
        <p><?php echo t('parcours_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container og-wrap">

        <p class="page-intro-text"><?php echo t('org_intro_texte'); ?></p>

        <!-- Gouvernance -->
        <div class="og-grid og-grid-1">
            <?php echo og_card('conseil_etablissement', 'Organe collégial', null, 'n-top', 'desc_conseil_etablissement'); ?>
        </div>
        <div class="og-arrow"><i class="fas fa-arrow-down"></i></div>
        <div class="og-grid og-grid-1">
            <?php echo og_card('directeur', 'Dr. Hary Tiana R.', 'directeur.jpg', 'n-directeur', 'desc_directeur'); ?>
        </div>

        <div class="og-arrow"><i class="fas fa-arrow-down"></i></div>
        <h3 class="og-section-title animate-on-scroll"><?php echo t('titre_direction'); ?></h3>
        <div class="og-grid og-grid-services">
            <?php echo og_card('prmp', 'Mme. Gestion F.', 'gestion.jpg', 'n-purple', 'desc_prmp'); ?>
            <?php echo og_card('conseil_scientifique', 'M. Lovas R.', 'lovas.jpg', 'n-orange', 'desc_conseil_scientifique'); ?>
            <?php echo og_card('college_enseignants', 'Mme. Nathalie V.', 'nathalie.jpg', 'n-orange', 'desc_college_enseignants'); ?>
            <?php echo og_card('secretariat_direction', 'Mme. Secrétaire P.', 'secretaire.jpg', 'n-green-dark', 'desc_secretariat_direction'); ?>
            <?php echo og_card('resp_qualite', 'M. Maxwell A.', 'maxwell.jpg', 'n-purple', 'desc_resp_qualite'); ?>
            <?php echo og_card('resp_comm', 'M. Judickael M.', 'judickael.jpg', 'n-purple', 'desc_resp_comm'); ?>
        </div>

        <div class="og-arrow"><i class="fas fa-arrow-down"></i></div>
        <h3 class="og-section-title animate-on-scroll"><?php echo t('titre_pole_pedagogique'); ?> &amp; <?php echo t('titre_pole_administratif'); ?></h3>
        <p class="og-hint animate-on-scroll"><i class="fas fa-hand-pointer"></i> Cliquez sur un pôle pour découvrir son équipe</p>

        <div class="og-grid og-grid-2 og-poles">

            <!-- Pôle Pédagogique -->
            <div class="og-pole">
                <?php echo og_card('coordo_pedagogique', 'Mme. Nathalie V.', 'nathalie.jpg', 'n-green', 'desc_coordo_pedagogique', null, 'panel-coordo'); ?>
                <div class="og-panel" id="panel-coordo">
                    <div class="og-arrow og-arrow-sm"><i class="fas fa-arrow-down"></i></div>
                    <div class="og-grid og-grid-3">

                        <div class="og-item">
                            <?php echo og_card('mention_stnpa', 'M. Hary L.', 'hary.jpg', 'n-teal', 'desc_mention_stnpa', null, 'panel-stnpa'); ?>
                            <div class="og-panel" id="panel-stnpa">
                                <div class="og-arrow og-arrow-sm"><i class="fas fa-arrow-down"></i></div>
                                <div class="og-grid og-grid-1col">
                                    <?php echo og_card('parcours_gi', 'M. Hary L.', 'hary.jpg', 'n-gray', 'desc_parcours_gi', 'chef_de_parcours'); ?>
                                    <?php echo og_card('parcours_gb', 'M. Chrysostome', 'chrysostome.jpg', 'n-gray', 'desc_parcours_gb', 'chef_de_parcours'); ?>
                                    <?php echo og_card('parcours_gei', 'M. Telesphore', 'telesphore.jpg', 'n-gray', 'desc_parcours_gei', 'chef_de_parcours'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="og-item">
                            <?php echo og_card('mention_sti', 'M. Telesphore', 'telesphore.jpg', 'n-teal', 'desc_mention_sti', null, 'panel-sti'); ?>
                            <div class="og-panel" id="panel-sti">
                                <div class="og-arrow og-arrow-sm"><i class="fas fa-arrow-down"></i></div>
                                <div class="og-grid og-grid-1col">
                                    <?php echo og_card('parcours_ge', 'M. Maxwell A.', 'maxwell.jpg', 'n-gray', 'desc_parcours_ge', 'chef_de_parcours'); ?>
                                    <?php echo og_card('parcours_gind', 'Mme. Gestion F.', 'gestion.jpg', 'n-gray', 'desc_parcours_gind', 'chef_de_parcours'); ?>
                                    <?php echo og_card('parcours_gt', 'M. Lovas R.', 'lovas.jpg', 'n-gray', 'desc_parcours_gt', 'chef_de_parcours'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="og-item">
                            <?php echo og_card('mention_gc', 'Dr. Charles R.', 'charles.jpg', 'n-teal', 'desc_mention_gc', null, 'panel-gc'); ?>
                            <div class="og-panel" id="panel-gc">
                                <div class="og-arrow og-arrow-sm"><i class="fas fa-arrow-down"></i></div>
                                <div class="og-grid og-grid-1col">
                                    <?php echo og_card('parcours_gcivil', 'Dr. Charles R.', 'charles.jpg', 'n-gray', 'desc_parcours_gcivil', 'chef_de_parcours'); ?>
                                    <?php echo og_card('parcours_ghyd', 'M. Moïse D.', 'moise.jpg', 'n-gray', 'desc_parcours_ghyd', 'chef_de_parcours'); ?>
                                    <?php echo og_card('parcours_garchi', 'M. Moïse D.', 'moise.jpg', 'n-gray', 'desc_parcours_garchi', 'chef_de_parcours'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="og-item">
                            <?php echo og_card('service_cooperation', 'Mme. Gestion F.', 'gestion.jpg', 'n-teal', 'desc_service_cooperation', null, 'panel-coop'); ?>
                            <div class="og-panel" id="panel-coop">
                                <div class="og-arrow og-arrow-sm"><i class="fas fa-arrow-down"></i></div>
                                <div class="og-grid og-grid-1col">
                                    <?php echo og_card('division_coop', 'Mme. Gestion F.', 'gestion.jpg', 'n-maroon'); ?>
                                    <?php echo og_card('division_labo', 'M. Lovas R.', 'lovas.jpg', 'n-maroon'); ?>
                                    <?php echo og_card('division_relations', 'M. Maxwell A.', 'maxwell.jpg', 'n-maroon'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="og-item">
                            <?php echo og_card('service_stats', 'M. Maxwell A.', 'maxwell.jpg', 'n-blue', 'desc_service_stats', null, 'panel-stats'); ?>
                            <div class="og-panel" id="panel-stats">
                                <div class="og-arrow og-arrow-sm"><i class="fas fa-arrow-down"></i></div>
                                <div class="og-grid og-grid-1col">
                                    <?php echo og_card('agent_affaires', 'Mme. Secrétaire P.', 'secretaire.jpg', 'n-olive'); ?>
                                    <?php echo og_card('resp_stats_diplomes', 'M. Maxwell A.', 'maxwell.jpg', 'n-olive'); ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Pôle Administratif -->
            <div class="og-pole">
                <?php echo og_card('secretaire_principal', 'Mme. Secrétaire P.', 'secretaire.jpg', 'n-green', 'desc_secretaire_principal', null, 'panel-secretaire'); ?>
                <div class="og-panel" id="panel-secretaire">
                    <div class="og-arrow og-arrow-sm"><i class="fas fa-arrow-down"></i></div>
                    <div class="og-grid og-grid-2">

                        <?php echo og_card('service_compta', 'M. Chrysostome', 'chrysostome.jpg', 'n-blue', 'desc_service_compta'); ?>
                        <?php echo og_card('service_numerique', 'M. Judickael M.', 'judickael.jpg', 'n-blue', 'desc_service_numerique'); ?>

                        <div class="og-item">
                            <?php echo og_card('service_scolarite', 'Mme. Secrétaire P.', 'secretaire.jpg', 'n-blue', 'desc_service_scolarite', null, 'panel-scolarite'); ?>
                            <div class="og-panel" id="panel-scolarite">
                                <div class="og-arrow og-arrow-sm"><i class="fas fa-arrow-down"></i></div>
                                <div class="og-grid og-grid-1col">
                                    <?php echo og_card('secretariat_master', 'Mme. Nathalie V.', 'nathalie.jpg', 'n-olive'); ?>
                                    <?php echo og_card('secretariat_licence', 'Mme. Secrétaire P.', 'secretaire.jpg', 'n-olive'); ?>
                                    <?php echo og_card('resp_diplomes', 'M. Judickael M.', 'judickael.jpg', 'n-olive'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="og-item">
                            <?php echo og_card('service_logistique', 'M. Chrysostome', 'chrysostome.jpg', 'n-blue', 'desc_service_logistique', null, 'panel-logistique'); ?>
                            <div class="og-panel" id="panel-logistique">
                                <div class="og-arrow og-arrow-sm"><i class="fas fa-arrow-down"></i></div>
                                <div class="og-grid og-grid-1col">
                                    <?php echo og_card('division_logistique', 'M. Chrysostome', 'chrysostome.jpg', 'n-olive'); ?>
                                    <?php echo og_card('division_technique', 'M. Telesphore', 'telesphore.jpg', 'n-olive'); ?>
                                    <?php echo og_card('resp_biblio', 'Mme. Nathalie V.', 'nathalie.jpg', 'n-olive'); ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <!-- Légende des couleurs -->
        <div class="orgchart-legend">
            <span class="legend-item"><i class="legend-dot n-top"></i><?php echo t('titre_gouvernance'); ?></span>
            <span class="legend-item"><i class="legend-dot n-purple"></i><?php echo t('resp_qualite'); ?></span>
            <span class="legend-item"><i class="legend-dot n-orange"></i><?php echo t('conseil_scientifique'); ?></span>
            <span class="legend-item"><i class="legend-dot n-green-dark"></i><?php echo t('secretariat_direction'); ?></span>
            <span class="legend-item"><i class="legend-dot n-green"></i><?php echo t('titre_pole_pedagogique'); ?> / <?php echo t('titre_pole_administratif'); ?></span>
            <span class="legend-item"><i class="legend-dot n-teal"></i><?php echo t('mention_gc'); ?></span>
            <span class="legend-item"><i class="legend-dot n-gray"></i><?php echo t('titre_details_parcours'); ?></span>
            <span class="legend-item"><i class="legend-dot n-maroon"></i><?php echo t('division_coop'); ?></span>
            <span class="legend-item"><i class="legend-dot n-blue"></i><?php echo t('service_compta'); ?></span>
            <span class="legend-item"><i class="legend-dot n-olive"></i><?php echo t('resp_biblio'); ?></span>
        </div>

        <p class="page-intro-text"><?php echo t('cursus_intro_texte'); ?></p>

        <!-- Schéma du Cursus -->
        <div class="cursus-schema">
            <h3 class="og-section-title animate-on-scroll"><?php echo t('cursus_schema_titre'); ?></h3>
            <div class="cursus-ladder">
                <div class="cursus-level cursus-m2 animate-on-scroll">
                    <span class="cursus-level-name"><?php echo t('cursus_m2_titre'); ?></span>
                    <ul class="cursus-level-desc">
                        <li><?php echo t('cursus_m2_desc1'); ?></li>
                    </ul>
                </div>
                <div class="cursus-ladder-arrow cursus-arrow-4"><i class="fas fa-arrow-up"></i></div>

                <div class="cursus-level cursus-m1 animate-on-scroll">
                    <span class="cursus-level-name"><?php echo t('cursus_m1_titre'); ?></span>
                    <ul class="cursus-level-desc">
                        <li><?php echo t('cursus_m1_desc1'); ?></li>
                        <li><?php echo t('cursus_m1_desc2'); ?></li>
                    </ul>
                </div>
                <div class="cursus-ladder-arrow cursus-arrow-3"><i class="fas fa-arrow-up"></i></div>

                <div class="cursus-level cursus-l3 animate-on-scroll">
                    <span class="cursus-level-name"><?php echo t('cursus_l3_titre'); ?></span>
                    <ul class="cursus-level-desc">
                        <li><?php echo t('cursus_l3_desc1'); ?></li>
                        <li><?php echo t('cursus_l3_desc2'); ?></li>
                    </ul>
                </div>
                <div class="cursus-ladder-arrow cursus-arrow-2"><i class="fas fa-arrow-up"></i></div>

                <div class="cursus-level cursus-l1l2 animate-on-scroll">
                    <span class="cursus-level-name"><?php echo t('cursus_l1l2_titre'); ?></span>
                    <ul class="cursus-level-desc">
                        <li><?php echo t('cursus_l1l2_desc1'); ?></li>
                        <li><?php echo t('cursus_l1l2_desc2'); ?></li>
                    </ul>
                </div>
                <div class="cursus-ladder-arrow cursus-arrow-1"><i class="fas fa-arrow-up"></i></div>

                <div class="cursus-level cursus-bacc animate-on-scroll">
                    <span class="cursus-level-name"><?php echo t('cursus_bacc_titre'); ?></span>
                    <ul class="cursus-level-desc">
                        <li><?php echo t('cursus_bacc_desc1'); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Téléchargements : Organigramme & Cursus -->
        <div class="og-downloads animate-on-scroll">
            <h3 class="og-section-title"><?php echo t('org_dl_titre'); ?></h3>
            <p class="og-downloads-intro"><?php echo t('org_dl_intro'); ?></p>
            <div class="og-downloads-grid">
                <div class="og-download-card">
                    <div class="og-download-icon"><i class="fas fa-sitemap"></i></div>
                    <h4><?php echo t('org_dl_organigramme_titre'); ?></h4>
                    <p><?php echo t('org_dl_organigramme_desc'); ?></p>
                    <?php if ($org_is_logged_in): ?>
                        <div class="og-download-links">
                            <a href="images/organigramme/organigramme.jpeg" download class="og-download-link og-dl-image"><i class="fas fa-file-image"></i> JPEG</a>
                            <a href="images/organigramme/organigramme.docx" download class="og-download-link og-dl-word"><i class="fas fa-file-word"></i> Word</a>
                            <a href="images/organigramme/organigramme.pdf" download class="og-download-link og-dl-pdf"><i class="fas fa-file-pdf"></i> PDF</a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/login.php" class="og-download-link og-dl-locked"><i class="fas fa-lock"></i> <?php echo t('org_dl_connexion_requise'); ?></a>
                    <?php endif; ?>
                </div>
                <div class="og-download-card">
                    <div class="og-download-icon"><i class="fas fa-graduation-cap"></i></div>
                    <h4><?php echo t('org_dl_cursus_titre'); ?></h4>
                    <p><?php echo t('org_dl_cursus_desc'); ?></p>
                    <?php if ($org_is_logged_in): ?>
                        <div class="og-download-links">
                            <a href="images/organigramme/cursus.jpg" download class="og-download-link og-dl-image"><i class="fas fa-file-image"></i> JPG</a>
                            <a href="images/organigramme/cursus.docx" download class="og-download-link og-dl-word"><i class="fas fa-file-word"></i> Word</a>
                            <a href="images/organigramme/cursus.pdf" download class="og-download-link og-dl-pdf"><i class="fas fa-file-pdf"></i> PDF</a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/login.php" class="og-download-link og-dl-locked"><i class="fas fa-lock"></i> <?php echo t('org_dl_connexion_requise'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
