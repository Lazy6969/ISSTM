<?php
include_once 'language.php';
require_once 'db_connect.php';

$preinscription_cta_media = $mysqli->query("SELECT media_type, media_path FROM preinscription_cta_media ORDER BY display_order ASC, id ASC")->fetch_all(MYSQLI_ASSOC);

// --- Informations et dates administrables (admin_contenu.php, panneau "Inscription : Frais & Dates") ---
// Chaque clé a une valeur de repli (le contenu historiquement codé en dur) pour que la page
// reste correcte même si le panneau n'a jamais été enregistré côté admin.
$inscription_config_defaults = [
    'inscription_annee_universitaire' => '2026',
    'inscription_date_limite' => '2026-10-09',
    'inscription_adresse_bloc' => "Mme le Chef de Service de la Scolarité Centrale\nUniversité de Mahajanga, BP 652, Mahajanga (401)\nTél : 034 44 889 86",
    'inscription_compte_bancaire' => '00650 05004012981-07',
    'frais_nat_lic_droit' => '750 000 Ar', 'frais_nat_lic_v1' => '250 000 Ar', 'frais_nat_lic_v2' => '250 000 Ar', 'frais_nat_lic_v3' => '250 000 Ar',
    'frais_nat_mas_droit' => '1 050 000 Ar', 'frais_nat_mas_v1' => '550 000 Ar', 'frais_nat_mas_v2' => '250 000 Ar', 'frais_nat_mas_v3' => '250 000 Ar',
    'frais_nat_tenue' => '20 000 Ar',
    'frais_etr_lic_droit' => '1 050 000 Ar', 'frais_etr_lic_v1' => '350 000 Ar', 'frais_etr_lic_v2' => '350 000 Ar', 'frais_etr_lic_v3' => '350 000 Ar',
    'frais_etr_mas_droit' => '1 500 000 Ar', 'frais_etr_mas_v1' => '750 000 Ar', 'frais_etr_mas_v2' => '375 000 Ar', 'frais_etr_mas_v3' => '375 000 Ar',
    'frais_etr_tenue' => '20 000 Ar',
];
$inscription_config = [];
$ic_keys = array_keys($inscription_config_defaults);
$ic_placeholders = implode(',', array_fill(0, count($ic_keys), '?'));
$ic_stmt = $mysqli->prepare("SELECT content_key, content_value_fr, content_value_en, content_value_mg FROM site_content WHERE content_key IN ($ic_placeholders)");
$ic_stmt->bind_param(str_repeat('s', count($ic_keys)), ...$ic_keys);
$ic_stmt->execute();
$ic_result = $ic_stmt->get_result();
while ($row = $ic_result->fetch_assoc()) {
    $inscription_config[$row['content_key']] = $row;
}
$ic_stmt->close();

function ic($key) {
    global $inscription_config, $inscription_config_defaults, $lang;
    if (isset($inscription_config[$key])) {
        $val = $inscription_config[$key]['content_value_' . $lang] ?: $inscription_config[$key]['content_value_fr'];
        if ($val !== '') return $val;
    }
    return $inscription_config_defaults[$key] ?? '';
}

$page_title = t('inscription_titre');
include 'header.php';
?>

<div class="page-banner inscription-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('inscription_titre'); ?></span>
        </nav>
        <h1><?php echo t('inscription_titre'); ?></h1>
        <p><?php echo t('inscription_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <!-- Arrière-plan animé pour la cohérence visuelle -->
    <div class="background-animation">
        <span class="icon"><i class="fas fa-edit"></i></span>
        <span class="icon"><i class="fas fa-file-signature"></i></span>
        <span class="icon"><i class="fas fa-keyboard"></i></span>
        <span class="icon"><i class="fas fa-user-plus"></i></span>
    </div>
    <div class="container">
        <article class="form-article">
            <section class="content-block inscription-info">
                <p><?php echo t('inscription_intro'); ?></p>
                <p><strong><?php echo t('inscription_note'); ?></strong> <?php echo t('inscription_note_contenu'); ?></p>
            </section>

            <div class="inscription-layout-grid">
                <div class="unified-content-panel">
                    <div class="main-content-area">
                        <section class="content-block fees-section">
                            <h2><?php echo str_replace('{annee}', htmlspecialchars(ic('inscription_annee_universitaire')), t('frais_titre')); ?></h2>
                            <div class="fees-container">
                                <!-- Étudiants Nationaux -->
                                <div class="fees-column">
                                    <h3><?php echo t('frais_section_nationaux'); ?></h3>
                                    <div class="fees-category">
                                        <h4><?php echo t('frais_licence'); ?></h4>
                                        <div class="fee-item"><span><?php echo t('frais_droit_annuel'); ?></span> <span><?php echo htmlspecialchars(ic('frais_nat_lic_droit')); ?></span></div>
                                        <div class="fee-item"><span><?php echo t('frais_versement1'); ?></span> <span><?php echo htmlspecialchars(ic('frais_nat_lic_v1')); ?></span></div>
                                        <div class="fee-item"><span><?php echo t('frais_versement2'); ?></span> <span><?php echo htmlspecialchars(ic('frais_nat_lic_v2')); ?></span></div>
                                        <div class="fee-item"><span><?php echo t('frais_versement3'); ?></span> <span><?php echo htmlspecialchars(ic('frais_nat_lic_v3')); ?></span></div>
                                    </div>
                                    <div class="fees-category">
                                        <h4><?php echo t('frais_master'); ?></h4>
                                        <div class="fee-item"><span><?php echo t('frais_droit_annuel'); ?></span> <span><?php echo htmlspecialchars(ic('frais_nat_mas_droit')); ?></span></div>
                                        <div class="fee-item"><span><?php echo t('frais_versement1'); ?></span> <span><?php echo htmlspecialchars(ic('frais_nat_mas_v1')); ?></span></div>
                                        <div class="fee-item"><span><?php echo t('frais_versement2'); ?></span> <span><?php echo htmlspecialchars(ic('frais_nat_mas_v2')); ?></span></div>
                                        <div class="fee-item"><span><?php echo t('frais_versement3'); ?></span> <span><?php echo htmlspecialchars(ic('frais_nat_mas_v3')); ?></span></div>
                                    </div>
                                    <div class="fees-category additional-fees">
                                        <h4><?php echo t('frais_supplementaires'); ?></h4>
                                        <div class="fee-item"><span><?php echo t('frais_tenue'); ?></span> <span><?php echo htmlspecialchars(ic('frais_nat_tenue')); ?></span></div>
                                    </div>
                                </div>

                                <!-- Étudiants Étrangers -->
                                <div class="fees-column">
                                    <h3><?php echo t('frais_section_etrangers'); ?></h3>
                                    <div class="fees-category">
                                        <h4><?php echo t('frais_licence'); ?></h4>
                                        <div class="fee-item"><span><?php echo t('frais_droit_annuel'); ?></span> <span><?php echo htmlspecialchars(ic('frais_etr_lic_droit')); ?></span></div>
                                        <div class="fee-item"><span><?php echo t('frais_versement1'); ?></span> <span><?php echo htmlspecialchars(ic('frais_etr_lic_v1')); ?></span></div>
                                        <div class="fee-item"><span><?php echo t('frais_versement2'); ?></span> <span><?php echo htmlspecialchars(ic('frais_etr_lic_v2')); ?></span></div>
                                        <div class="fee-item"><span><?php echo t('frais_versement3'); ?></span> <span><?php echo htmlspecialchars(ic('frais_etr_lic_v3')); ?></span></div>
                                    </div>
                                    <div class="fees-category">
                                        <h4><?php echo t('frais_master'); ?></h4>
                                        <div class="fee-item"><span><?php echo t('frais_droit_annuel'); ?></span> <span><?php echo htmlspecialchars(ic('frais_etr_mas_droit')); ?></span></div>
                                        <div class="fee-item"><span><?php echo t('frais_versement1'); ?></span> <span><?php echo htmlspecialchars(ic('frais_etr_mas_v1')); ?></span></div>
                                        <div class="fee-item"><span><?php echo t('frais_versement2'); ?></span> <span><?php echo htmlspecialchars(ic('frais_etr_mas_v2')); ?></span></div>
                                        <div class="fee-item"><span><?php echo t('frais_versement3'); ?></span> <span><?php echo htmlspecialchars(ic('frais_etr_mas_v3')); ?></span></div>
                                    </div>
                                    <div class="fees-category additional-fees">
                                        <h4><?php echo t('frais_supplementaires'); ?></h4>
                                        <div class="fee-item"><span><?php echo t('frais_tenue'); ?></span> <span><?php echo htmlspecialchars(ic('frais_etr_tenue')); ?></span></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="content-block dossier-section">
                            <h2><?php echo t('dossiers_titre'); ?></h2>

                            <div class="dossier-subsection">
                                <h3><?php echo t('preinscription_isstm_titre'); ?></h3>
                                <ul class="styled-list">
                                    <li><?php echo t('dossiers_fiche_preinscription'); ?></li>
                                    <li><?php echo t('dossiers_copie_naissance'); ?></li>
                                    <li><?php echo t('dossiers_photocopie_bac'); ?></li>
                                    <li><?php echo t('dossiers_cert_residence_parents'); ?></li>
                                    <li><?php echo t('dossiers_enveloppes'); ?></li>
                                    <li><?php echo t('dossiers_recu_preinscription'); ?> <?php echo t('dossiers_recu_pre_mg'); ?> / <?php echo t('dossiers_recu_pre_etr'); ?></li>
                                </ul>

                                <div class="download-form-subsection">
                                    <h4><?php echo t('telecharger_form_titre'); ?></h4>
                                    <p><?php echo t('telecharger_form_instruction'); ?></p>
                                    <a href="documents/fiche.pdf" download class="btn btn-primary download-btn">
                                        <i class="fas fa-download"></i> <?php echo t('telecharger_form_bouton'); ?>
                                    </a>
                                </div>
                            </div>

                            <div class="dossier-subsection">
                                <h3><?php echo t('dossiers_titre_initiale'); ?></h3>
                                <ul class="styled-list">
                                    <li><?php echo t('dossiers_chemises'); ?>
                                        <ul class="folder-colors">
                                            <li class="color-jaune"><?php echo t('filiere_gc_couleur'); ?></li>
                                            <li class="color-vert-clair"><?php echo t('filiere_gh_couleur'); ?></li>
                                            <li class="color-marron"><?php echo t('filiere_garch_couleur'); ?></li>
                                            <li class="color-orange"><?php echo t('filiere_ge_couleur'); ?></li>
                                            <li class="color-bleu"><?php echo t('filiere_gi_couleur'); ?></li>
                                            <li class="color-vert-fonce"><?php echo t('filiere_gt_couleur'); ?></li>
                                            <li class="color-rose"><?php echo t('filiere_ginfo_couleur'); ?></li>
                                            <li class="color-violet"><?php echo t('filiere_gei_couleur'); ?></li>
                                            <li class="color-rouge"><?php echo t('filiere_gbm_couleur'); ?></li>
                                        </ul>
                                    </li>
                                    <li><?php echo t('dossiers_photos'); ?></li>
                                    <li><?php echo t('dossiers_lettre_engagement'); ?></li>
                                    <li><?php echo t('dossiers_cert_residence'); ?></li>
                                    <li><?php echo t('dossiers_releve_bac'); ?></li>
                                    <li><?php echo t('dossiers_preuve_versement'); ?> <?php echo t('dossiers_versement_mg'); ?> / <?php echo t('dossiers_versement_etr'); ?>. <?php echo str_replace('{compte}', htmlspecialchars(ic('inscription_compte_bancaire')), t('dossiers_compte_bred')); ?></li>
                                    <li><?php echo t('dossiers_fiche_inscription'); ?></li>
                                    <li><?php echo t('dossiers_achat_tenue'); ?></li>
                                </ul>
                            </div>

                            <div class="dossier-subsection">
                                <h3><?php echo t('master_preinscription_titre'); ?></h3>
                                <h4><?php echo t('master_docs_requis'); ?></h4>
                                <ul class="styled-list">
                                    <li><?php echo t('dossiers_fiche_preinscription'); ?></li>
                                    <li><?php echo t('dossiers_photos'); ?></li>
                                    <li><?php echo t('dossiers_cert_residence_parents'); ?></li>
                                    <li><?php echo t('master_photocopie_licence'); ?></li>
                                    <li><?php echo t('master_acte_naissance'); ?></li>
                                    <li><?php echo t('master_photocopie_cin'); ?></li>
                                    <li><?php echo t('master_enveloppe_pm'); ?></li>
                                    <li><?php echo t('dossiers_recu_preinscription'); ?> <?php echo t('dossiers_recu_pre_mg'); ?> / <?php echo t('dossiers_recu_pre_etr'); ?></li>
                                </ul>
                            </div>

                            <div class="dossier-subsection">
                                <h3><?php echo t('reinscription_titre'); ?></h3>
                                <ul class="styled-list">
                                    <li><?php echo t('reinscription_photos'); ?></li>
                                    <li><?php echo t('reinscription_carte_etudiant'); ?></li>
                                    <li><?php echo t('dossiers_lettre_engagement'); ?></li>
                                    <li><?php echo t('dossiers_cert_residence'); ?></li>
                                    <li><?php echo t('dossiers_preuve_versement'); ?> <?php echo t('dossiers_versement_mg'); ?> / <?php echo t('dossiers_versement_etr'); ?></li>
                                    <li><?php echo t('reinscription_fiche'); ?></li>
                                    <li><?php echo t('dossiers_achat_tenue'); ?></li>
                                </ul>
                            </div>

                            <div class="dossier-subsection">
                                <h3><?php echo t('reinscription_m2_titre'); ?></h3>
                                <ul class="styled-list">
                                    <li><?php echo t('reinscription_photos'); ?></li>
                                    <li><?php echo t('reinscription_carte_etudiant'); ?></li>
                                    <li><?php echo t('dossiers_lettre_engagement'); ?></li>
                                    <li><?php echo t('dossiers_cert_residence'); ?></li>
                                    <li><?php echo t('reinscription_m2_releve'); ?></li>
                                    <li><?php echo t('reinscription_fiche'); ?></li>
                                    <li><?php echo t('reinscription_m2_versement_intro'); ?> <?php echo t('reinscription_m2_versement_mg'); ?> / <?php echo t('reinscription_m2_versement_etr'); ?></li>
                                </ul>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Les informations épinglées sont maintenant ici -->
                <aside class="inscription-sidebar">
                    <div class="sticky-content">
                        <button type="button" class="sidebar-scroll-btn sidebar-scroll-up is-hidden" aria-label="Faire défiler vers le haut">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                        <div class="sidebar-panel">
                            <div class="dossier-subsection">
                                <h3><?php echo t('depot_dossiers_titre'); ?></h3>
                                <p><strong><?php echo t('depot_dossiers_adresse'); ?></strong><br><?php echo nl2br(htmlspecialchars(ic('inscription_adresse_bloc'))); ?></p>
                                <p><strong><?php echo t('depot_dossiers_modalites'); ?></strong> <?php echo t('depot_dossiers_direct'); ?> <?php echo t('depot_dossiers_postal'); ?></p>
                                <p class="important-notice"><?php echo str_replace('{date}', htmlspecialchars(format_date_localized(ic('inscription_date_limite'), $lang)), t('depot_dossiers_limite')); ?></p>
                            </div>

                            <div class="dossier-subsection important-notes-final">
                                <h3><?php echo t('mentions_importantes_titre'); ?></h3>
                                <p class="warning-text"><?php echo t('mentions_importantes_incomplets'); ?></p>
                                <div class="bank-info-container">
                                    <img src="images/partenariat/bre.jpg" alt="Logo BRED" class="bank-logo">
                                    <p><?php echo str_replace('{compte}', htmlspecialchars(ic('inscription_compte_bancaire')), t('dossiers_compte_bred')); ?></p>
                                </div>
                                <p><em><?php echo t('mentions_importantes_devise'); ?></em></p>
                            </div>
                        </div>
                        <button type="button" class="sidebar-scroll-btn sidebar-scroll-down" aria-label="Faire défiler vers le bas">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                </aside>
            </div>

            <!-- Bloc d'appel à l'action : carrousel image/vidéo + bouton vers le formulaire
                 de pré-inscription, volontairement placé tout en bas (après toutes les
                 informations sur les frais et les dossiers requis). -->
            <section class="content-block preinscription-cta-section">
                <div class="preinscription-cta-carousel" id="preinscription-cta-carousel">
                    <?php if (!empty($preinscription_cta_media)): ?>
                        <?php foreach ($preinscription_cta_media as $i => $m): ?>
                            <?php if ($m['media_type'] === 'video'): ?>
                                <video class="preinscription-cta-slide<?php echo $i === 0 ? ' is-active' : ''; ?>" src="<?php echo htmlspecialchars($m['media_path']); ?>" muted playsinline></video>
                            <?php else: ?>
                                <div class="preinscription-cta-slide<?php echo $i === 0 ? ' is-active' : ''; ?>" style="background-image:url('<?php echo htmlspecialchars($m['media_path']); ?>');"></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="preinscription-cta-slide is-active preinscription-cta-slide-default"><i class="fas fa-graduation-cap"></i></div>
                    <?php endif; ?>
                    <div class="preinscription-cta-overlay"></div>
                    <div class="preinscription-cta-content">
                        <h2><?php echo t('preinscription_cta_titre'); ?></h2>
                        <p><?php echo t('preinscription_cta_texte'); ?></p>
                        <a href="preinscription.php" class="btn btn-primary preinscription-cta-btn"><i class="fas fa-user-plus"></i> <?php echo t('preinscription_cta_bouton'); ?></a>
                        <p class="preinscription-cta-note"><i class="fas fa-circle-info"></i> <?php echo t('preinscription_cta_note_mdp'); ?></p>
                    </div>
                </div>
            </section>

        </article>
    </div>
</div>

<script>
(function () {
    const track = document.getElementById('preinscription-cta-carousel');
    if (!track) return;
    const slides = Array.from(track.querySelectorAll('.preinscription-cta-slide'));
    if (slides.length <= 1) return;
    let idx = 0;
    let timer = null;

    function scheduleNext() {
        clearTimeout(timer);
        const current = slides[idx];
        if (current.tagName === 'VIDEO') {
            current.currentTime = 0;
            current.play().catch(function () {});
            current.onended = function () { showSlide((idx + 1) % slides.length); };
        } else {
            timer = setTimeout(function () { showSlide((idx + 1) % slides.length); }, 5000);
        }
    }

    function showSlide(i) {
        const prev = slides[idx];
        prev.classList.remove('is-active');
        if (prev.tagName === 'VIDEO') { prev.pause(); prev.onended = null; }
        idx = i;
        slides[idx].classList.add('is-active');
        scheduleNext();
    }

    scheduleNext();
})();
</script>

<?php include 'footer.php'; ?>