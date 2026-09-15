<?php
include_once 'language.php'; // Pour la fonction t() si besoin

// --- Vérification si l'utilisateur est connecté ET s'il est admin ---
$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';

// Si l'utilisateur n'est pas un admin, on le redirige vers la page d'accueil.
if (!$is_admin) {
    header('Location: index.php');
    exit;
}

$page_title = t('administration');

include 'header.php';

// Bloc-notes personnel de l'admin connecté (voir admin_notes_save.php pour la sauvegarde AJAX).
$admin_note_row = $mysqli->query("SELECT contenu FROM admin_notes WHERE user_id = " . (int) $_SESSION['user_id'])->fetch_assoc();
$admin_note_content = $admin_note_row['contenu'] ?? '';
?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('administration'); ?></span>
        </nav>
        <h1><?php echo t('administration'); ?></h1>
        <p><?php echo t('admin_dashboard_subtitle'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">
        <!-- === Tableau de Bord Administrateur === -->
        <div class="admin-dashboard">
            <h2><?php echo t('profil_bienvenue'); ?> <?php echo htmlspecialchars($_SESSION['user_nom']); ?> !</h2>
            <p><?php echo t('admin_dashboard_choisir_action'); ?></p>

            <!-- === Statistiques temps réel === -->
            <div class="admin-stats-dashboard" id="admin-stats-dashboard">
                <div class="admin-stats-header">
                    <h3><i class="fas fa-chart-line"></i> <?php echo t('admin_dashboard_stats_titre'); ?></h3>
                    <span class="admin-stats-live"><span class="admin-stats-live-dot"></span> <?php echo t('admin_dashboard_stats_live'); ?></span>
                </div>

                <div class="admin-stats-grid">
                    <div class="admin-stat-tile" data-stat="utilisateurs_total"><i class="fas fa-users"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_utilisateurs'); ?></span></div>
                    <div class="admin-stat-tile" data-stat="etudiants"><i class="fas fa-user-graduate"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_etudiants'); ?></span></div>
                    <div class="admin-stat-tile" data-stat="enseignants"><i class="fas fa-chalkboard-user"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_enseignants'); ?></span></div>
                    <div class="admin-stat-tile" data-stat="bibliotheque"><i class="fas fa-book"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_bibliotheque'); ?></span></div>
                    <div class="admin-stat-tile" data-stat="documents"><i class="fas fa-file-lines"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_documents'); ?></span></div>
                    <div class="admin-stat-tile" data-stat="publications_communaute"><i class="fas fa-users-rectangle"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_publications'); ?></span></div>
                    <div class="admin-stat-tile" data-stat="actualites_publiees"><i class="fas fa-newspaper"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_actualites'); ?></span></div>
                    <div class="admin-stat-tile" data-stat="evenements_total"><i class="fas fa-calendar-days"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_evenements'); ?></span></div>
                    <div class="admin-stat-tile" data-stat="evenements_a_venir"><i class="fas fa-calendar-check"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_evenements_a_venir'); ?></span></div>
                    <div class="admin-stat-tile" data-stat="comptes_en_attente"><i class="fas fa-user-clock"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_comptes_attente'); ?></span></div>
                    <div class="admin-stat-tile" data-stat="utilisateurs_en_ligne"><i class="fas fa-signal"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_en_ligne'); ?></span></div>
                    <div class="admin-stat-tile admin-stat-tile-resettable" data-stat="vues_total">
                        <button type="button" class="admin-stat-reset-btn" data-reset-type="total" data-confirm-msg="<?php echo htmlspecialchars(t('admin_dashboard_vues_total_confirm')); ?>" title="<?php echo htmlspecialchars(t('admin_dashboard_vues_reset_label')); ?>"><i class="fas fa-rotate-left"></i></button>
                        <i class="fas fa-eye"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_vues_total'); ?></span>
                    </div>
                    <div class="admin-stat-tile admin-stat-tile-resettable" data-stat="vues_aujourdhui">
                        <button type="button" class="admin-stat-reset-btn" data-reset-type="jour" data-confirm-msg="<?php echo htmlspecialchars(t('admin_dashboard_vues_jour_confirm')); ?>" title="<?php echo htmlspecialchars(t('admin_dashboard_vues_reset_label')); ?>"><i class="fas fa-rotate-left"></i></button>
                        <i class="fas fa-eye"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_vues_jour'); ?></span>
                    </div>
                    <div class="admin-stat-tile" data-stat="filieres_total"><i class="fas fa-graduation-cap"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_filieres'); ?></span></div>
                    <div class="admin-stat-tile" data-stat="enseignants_fiches"><i class="fas fa-id-card"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_enseignants_fiches'); ?></span></div>
                    <div class="admin-stat-tile" data-stat="galerie_albums"><i class="fas fa-images"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_galerie'); ?></span></div>
                    <div class="admin-stat-tile" data-stat="newsletter_abonnes"><i class="fas fa-envelope-open-text"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_newsletter'); ?></span></div>
                    <div class="admin-stat-tile" data-stat="partenaires_total"><i class="fas fa-handshake"></i><span class="admin-stat-value">--</span><span class="admin-stat-label"><?php echo t('admin_dashboard_stat_partenaires'); ?></span></div>
                </div>

                <div class="admin-stats-widgets">
                    <div class="admin-widget admin-perf-widget">
                        <h4><i class="fas fa-gauge-high"></i> <?php echo t('admin_dashboard_perf_titre'); ?></h4>
                        <div class="admin-perf-chart-wrap">
                            <svg class="admin-perf-chart" id="admin-perf-chart" viewBox="0 0 300 100" preserveAspectRatio="none">
                                <polygon class="admin-perf-chart-fill" id="admin-perf-chart-fill" points=""></polygon>
                                <polyline class="admin-perf-chart-line" id="admin-perf-chart-line" points=""></polyline>
                            </svg>
                            <div class="admin-perf-chart-current">
                                <span id="admin-perf-value">-- ms</span>
                                <span class="admin-perf-label" id="admin-perf-label"></span>
                            </div>
                        </div>
                    </div>

                    <div class="admin-widget admin-calendar-widget">
                        <h4><i class="fas fa-calendar"></i> <?php echo t('admin_dashboard_calendrier_titre'); ?></h4>
                        <div class="admin-mini-calendar" id="admin-mini-calendar"></div>
                    </div>

                    <div class="admin-widget admin-notes-widget">
                        <h4><i class="fas fa-note-sticky"></i> <?php echo t('admin_dashboard_notes_titre'); ?></h4>
                        <textarea id="admin-notes-textarea" class="admin-notes-textarea" placeholder="<?php echo t('admin_dashboard_notes_placeholder'); ?>"><?php echo htmlspecialchars($admin_note_content); ?></textarea>
                        <div class="admin-notes-status" id="admin-notes-status"></div>
                    </div>
                </div>
            </div>

            <div class="dashboard-actions">
                <a href="admin_contenu.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-pen-to-square"></i></div>
                    <h3><?php echo t('admin_dashboard_contenu_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_contenu_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_galerie.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-images"></i></div>
                    <h3><?php echo t('admin_dashboard_galerie_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_galerie_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_utilisateurs.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-users-gear"></i></div>
                    <h3><?php echo t('admin_dashboard_utilisateurs_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_utilisateurs_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_securite.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-shield-halved"></i></div>
                    <h3><?php echo t('admin_dashboard_securite_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_securite_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_materiel.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-boxes-stacked"></i></div>
                    <h3><?php echo t('admin_dashboard_materiel_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_materiel_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_etudiants.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-user-graduate"></i></div>
                    <h3><?php echo t('admin_dashboard_etudiants_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_etudiants_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_resultats.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-file-circle-check"></i></div>
                    <h3><?php echo t('admin_dashboard_resultats_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_resultats_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_actualites.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-newspaper"></i></div>
                    <h3><?php echo t('admin_dashboard_actualites_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_actualites_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_filieres.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-graduation-cap"></i></div>
                    <h3><?php echo t('admin_dashboard_filieres_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_filieres_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_partenaires.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-handshake"></i></div>
                    <h3><?php echo t('admin_dashboard_partenaires_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_partenaires_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_campus.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-school"></i></div>
                    <h3><?php echo t('admin_dashboard_campus_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_campus_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_documents.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-file-lines"></i></div>
                    <h3><?php echo t('admin_dashboard_documents_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_documents_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_evenements.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-calendar-days"></i></div>
                    <h3><?php echo t('admin_dashboard_evenements_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_evenements_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_communaute.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-users-rectangle"></i></div>
                    <h3><?php echo t('admin_dashboard_communaute_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_communaute_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_banners.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-panorama"></i></div>
                    <h3><?php echo t('admin_dashboard_banners_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_banners_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_organigramme.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-sitemap"></i></div>
                    <h3><?php echo t('admin_dashboard_organigramme_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_organigramme_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_enseignants.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-chalkboard-user"></i></div>
                    <h3><?php echo t('admin_dashboard_enseignants_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_enseignants_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="<?php echo SITE_URL; ?>/bibliotheque/admin/dashboard.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-book"></i></div>
                    <h3><?php echo t('admin_dashboard_bibliotheque_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_bibliotheque_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="admin_newsletter.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-envelope-open-text"></i></div>
                    <h3><?php echo t('admin_dashboard_newsletter_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_newsletter_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="profil.php" class="action-card">
                    <div class="action-card-icon"><i class="fas fa-user-gear"></i></div>
                    <h3><?php echo t('admin_dashboard_profil_titre'); ?></h3>
                    <p><?php echo t('admin_dashboard_profil_desc'); ?></p>
                    <span class="action-card-cta"><?php echo t('admin_dashboard_ouvrir'); ?> <i class="fas fa-arrow-right"></i></span>
                </a>
            </div>

            <!-- === Étudiants par filière (déplacé en bas, avec diagramme en bâton par filière) === -->
            <div class="admin-stats-dashboard admin-filiere-section" id="admin-filiere-section">
                <div class="admin-stats-header">
                    <h3><i class="fas fa-graduation-cap"></i> <?php echo t('admin_dashboard_filiere_titre'); ?></h3>
                </div>
                <div class="admin-filiere-layout">
                    <div class="etu-donut-wrap">
                        <div class="etu-donut" id="admin-filiere-donut">
                            <div class="etu-donut-hole"><strong id="admin-filiere-total">0</strong><span><?php echo t('admin_utilisateurs_role_etudiant'); ?></span></div>
                        </div>
                    </div>
                    <div class="etu-barchart" id="admin-filiere-legend" data-empty-label="<?php echo htmlspecialchars(t('admin_etudiants_aucune_donnee')); ?>"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>/admin_dashboard.js?v=<?php echo filemtime(__DIR__ . '/admin_dashboard.js'); ?>"></script>

<?php include 'footer.php'; ?>