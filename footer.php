</main>

<?php
$footer_redirect_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?? '', $footer_redirect_query);
unset($footer_redirect_query['newsletter']);
$footer_redirect_target = $footer_redirect_path . (!empty($footer_redirect_query) ? '?' . http_build_query($footer_redirect_query) : '');
if ($footer_redirect_target === '' || $footer_redirect_target === '/') { $footer_redirect_target = 'index.php'; }

// dc_footer() est désormais définie dans db_connect.php (voir commentaire là-bas) afin d'être
// disponible aussi avant l'inclusion du footer, ex : la section Contact d'index.php.
$footer_hide_visible = in_array(basename($_SERVER['PHP_SELF']), ['messagerie.php', 'groupe_chat.php'], true);
?>

<?php if (!$footer_hide_visible): ?>
<footer class="main-footer-bottom">
    <div class="container">
        <div class="footer-layout">
            <div class="footer-column footer-links">
                <h4><?php echo t('liens_rapides'); ?></h4>
                <a href="<?php echo SITE_URL; ?>/index.php"><?php echo t('accueil'); ?></a>
                <a href="<?php echo SITE_URL; ?>/historique.php"><?php echo t('historiques'); ?></a>
                <a href="<?php echo SITE_URL; ?>/parcours.php"><?php echo t('menu_organigramme'); ?></a>
                <a href="<?php echo SITE_URL; ?>/filieres.php"><?php echo t('filieres_section_titre'); ?></a>
                <a href="<?php echo SITE_URL; ?>/enseignants.php"><?php echo t('enseignants'); ?></a>
                <a href="<?php echo SITE_URL; ?>/vie_etudiante.php"><?php echo t('vie_etudiante'); ?></a>
                <a href="<?php echo SITE_URL; ?>/campus.php"><?php echo t('campus_titre'); ?></a>
                <a href="<?php echo SITE_URL; ?>/associations.php"><?php echo t('associations_titre'); ?></a>
                <a href="<?php echo SITE_URL; ?>/inscription.php"><?php echo t('form_inscription'); ?></a>
                <a href="<?php echo SITE_URL; ?>/bourse.php"><?php echo t('bourse'); ?></a>
                <a href="<?php echo SITE_URL; ?>/galerie.php"><?php echo t('galeries'); ?></a>
                <a href="<?php echo SITE_URL; ?>/bibliotheque/index.php"><?php echo t('bib_nav_titre'); ?></a>
                <a href="<?php echo SITE_URL; ?>/documents.php"><?php echo t('menu_documents'); ?></a>
                <a href="<?php echo SITE_URL; ?>/actualite.php"><?php echo t('actualites'); ?></a>
                <a href="<?php echo SITE_URL; ?>/index.php#partenaires"><?php echo t('partenaires_section_titre'); ?></a>
                <a href="<?php echo SITE_URL; ?>/index.php#contact"><?php echo t('contact_section_titre'); ?></a>
                <a href="#" id="faq-link">FAQ</a>
            </div>
            <div class="footer-column footer-logo-container">
                <img src="<?php echo SITE_URL; ?>/images/logo-isstm.jpg" alt="Logo ISSTM Footer" class="footer-logo">
                <p class="footer-tagline"><?php echo t('devise'); ?></p>
                <p class="footer-description"><?php echo t('footer_description'); ?></p>

                <div class="footer-newsletter-inline" id="footer-newsletter">
                    <h4><?php echo t('footer_newsletter_titre'); ?></h4>
                    <p class="footer-newsletter-text"><?php echo t('footer_newsletter_texte'); ?></p>
                    <form method="POST" action="<?php echo SITE_URL; ?>/newsletter_subscribe.php" class="footer-newsletter-form">
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($footer_redirect_target); ?>">
                        <input type="email" name="newsletter_email" placeholder="<?php echo t('footer_newsletter_placeholder'); ?>" required>
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                    <?php if (isset($_GET['newsletter'])): ?>
                        <p class="footer-newsletter-feedback footer-newsletter-<?php echo $_GET['newsletter'] === 'success' ? 'success' : 'error'; ?>">
                            <?php echo $_GET['newsletter'] === 'success' ? t('footer_newsletter_succes') : t('footer_newsletter_erreur'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php
                $footer_contact_email = dc_footer('contact_email');
                $footer_contact_tels = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', dc_footer('contact_telephone')))));
                $footer_contact_fb = dc_footer('contact_facebook');
            ?>
            <div class="footer-column footer-contact">
                <h4><?php echo t('contactez_nous'); ?></h4>
                <a href="mailto:<?php echo $footer_contact_email; ?>"><i class="fas fa-envelope"></i> <?php echo $footer_contact_email; ?></a>
                <a href="<?php echo $footer_contact_fb; ?>" target="_blank"><i class="fab fa-facebook-f"></i> Facebook</a>
                <?php foreach ($footer_contact_tels as $footer_tel): ?>
                    <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $footer_tel); ?>"><i class="fas fa-phone"></i> <?php echo $footer_tel; ?></a>
                <?php endforeach; ?>
                <span class="footer-address">
                    <i class="fas fa-location-dot"></i>
                    <span>
                        <?php echo dc_footer('contact_adresse'); ?><br>
                        <small><?php echo dc_footer('contact_adresse_detail'); ?></small>
                    </span>
                </span>
                <span class="footer-hours"><i class="fas fa-clock"></i> <?php echo dc_footer('footer_horaires'); ?></span>
            </div>
        </div>
        <p class="copyright-text">&copy; <?php echo date("Y"); ?> <?php echo t('droits_reserves'); ?></p>
        <p class="footer-legal-links">
            <a href="<?php echo SITE_URL; ?>/confidentialite.php"><?php echo t('footer_confidentialite'); ?></a>
            <span aria-hidden="true">|</span>
            <a href="<?php echo SITE_URL; ?>/mentions_legales.php"><?php echo t('footer_mentions_legales'); ?></a>
        </p>
    </div>
</footer>
<?php endif; ?>

<!-- Lightbox Structure (Global) -->
<div id="lightbox" class="lightbox">
    <span class="lightbox-close">&times;</span>
    <div class="lightbox-content-wrapper">
        <img class="lightbox-content" id="lightbox-img" alt="Lightbox Image">
        <a class="lightbox-prev">&#10094;</a>
        <a class="lightbox-next">&#10095;</a>
    </div>
</div>

<!-- Modale FAQ (Global) -->
<div id="faqModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2 class="modal-title"><?php echo t('faq_titre'); ?></h2>
        <div class="modal-body faq-modal-body">
            <details class="faq-item">
                <summary><?php echo t('faq_q1'); ?></summary>
                <p><?php echo t('faq_a1'); ?></p>
            </details>
            <details class="faq-item">
                <summary><?php echo t('faq_q2'); ?></summary>
                <p><?php echo t('faq_a2'); ?></p>
            </details>
            <details class="faq-item">
                <summary><?php echo t('faq_q3'); ?></summary>
                <p><?php echo t('faq_a3'); ?></p>
            </details>
            <details class="faq-item">
                <summary><?php echo t('faq_q4'); ?></summary>
                <p><?php echo t('faq_a4'); ?></p>
            </details>
            <details class="faq-item">
                <summary><?php echo t('faq_q5'); ?></summary>
                <p><?php echo t('faq_a5'); ?></p>
            </details>
            <details class="faq-item">
                <summary><?php echo t('faq_q6'); ?></summary>
                <p><?php echo t('faq_a6'); ?></p>
            </details>
            <details class="faq-item">
                <summary><?php echo t('faq_q7'); ?></summary>
                <p><?php echo t('faq_a7'); ?></p>
            </details>
            <details class="faq-item">
                <summary><?php echo t('faq_q8'); ?></summary>
                <p><?php echo t('faq_a8'); ?></p>
            </details>
            <details class="faq-item">
                <summary><?php echo t('faq_q9'); ?></summary>
                <p><?php echo t('faq_a9'); ?></p>
            </details>
            <details class="faq-item">
                <summary><?php echo t('faq_q10'); ?></summary>
                <p><?php echo t('faq_a10'); ?></p>
            </details>
        </div>
    </div>
</div>

<?php
// --- Palette de commande (Ctrl+K) : liste des raccourcis de navigation, adaptée à la session en
// cours (mêmes conditions que les liens du header). Consommée par script.js::initCommandPalette().
$palette_items = [
    ['label' => t('accueil'), 'href' => SITE_URL . '/index.php', 'icon' => 'fa-house', 'group' => 'nav'],
    ['label' => t('historiques'), 'href' => SITE_URL . '/historique.php', 'icon' => 'fa-landmark', 'group' => 'nav'],
    ['label' => t('menu_organigramme'), 'href' => SITE_URL . '/parcours.php', 'icon' => 'fa-sitemap', 'group' => 'nav'],
    ['label' => t('filieres_section_titre'), 'href' => SITE_URL . '/filieres.php', 'icon' => 'fa-graduation-cap', 'group' => 'nav'],
    ['label' => t('form_inscription'), 'href' => SITE_URL . '/inscription.php', 'icon' => 'fa-file-signature', 'group' => 'nav'],
    ['label' => t('enseignants'), 'href' => SITE_URL . '/enseignants.php', 'icon' => 'fa-chalkboard-user', 'group' => 'nav'],
    ['label' => t('vie_etudiante'), 'href' => SITE_URL . '/vie_etudiante.php', 'icon' => 'fa-people-group', 'group' => 'nav'],
    ['label' => t('bourse'), 'href' => SITE_URL . '/bourse.php', 'icon' => 'fa-hand-holding-dollar', 'group' => 'nav'],
    ['label' => t('galeries'), 'href' => SITE_URL . '/galerie.php', 'icon' => 'fa-images', 'group' => 'nav'],
    ['label' => t('bib_nav_titre'), 'href' => SITE_URL . '/bibliotheque/index.php', 'icon' => 'fa-book', 'group' => 'nav'],
    ['label' => t('menu_documents'), 'href' => SITE_URL . '/documents.php', 'icon' => 'fa-file-lines', 'group' => 'nav'],
    ['label' => t('actualites'), 'href' => SITE_URL . '/actualite.php', 'icon' => 'fa-newspaper', 'group' => 'nav'],
    ['label' => t('associations_titre'), 'href' => SITE_URL . '/associations.php', 'icon' => 'fa-people-roof', 'group' => 'nav'],
    ['label' => t('campus_titre'), 'href' => SITE_URL . '/campus.php', 'icon' => 'fa-city', 'group' => 'nav'],
];
if (!empty($header_is_site_user)) {
    $palette_items[] = ['label' => t('profil_titre'), 'href' => SITE_URL . '/profil.php', 'icon' => 'fa-user', 'group' => 'compte'];
    if (($_SESSION['user_role'] ?? '') === 'admin') {
        $palette_items[] = ['label' => t('administration'), 'href' => SITE_URL . '/administrateur.php', 'icon' => 'fa-user-shield', 'group' => 'compte'];
    }
    if (!empty($header_is_scolarite)) {
        $palette_items[] = ['label' => t('admin_etudiants_titre'), 'href' => SITE_URL . '/admin_etudiants.php', 'icon' => 'fa-user-graduate', 'group' => 'compte'];
    }
    if (!empty($header_has_groupes)) {
        $palette_items[] = ['label' => t('groupe_nav_titre'), 'href' => SITE_URL . '/mes_groupes.php', 'icon' => 'fa-comments', 'group' => 'compte'];
    }
    if (!empty($header_has_messagerie)) {
        $palette_items[] = ['label' => t('messagerie_titre'), 'href' => SITE_URL . '/messagerie.php', 'icon' => 'fa-comment-dots', 'group' => 'compte'];
    }
} elseif (!empty($header_is_bib_admin_only)) {
    $palette_items[] = ['label' => t('bib_admin_dashboard_titre'), 'href' => SITE_URL . '/bibliotheque/admin/dashboard.php', 'icon' => 'fa-user-shield', 'group' => 'compte'];
} else {
    $palette_items[] = ['label' => t('se_connecter'), 'href' => SITE_URL . '/login.php', 'icon' => 'fa-right-to-bracket', 'group' => 'compte'];
}
?>
<script>
    window.ISSTM_PALETTE_ITEMS = <?php echo json_encode($palette_items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    window.ISSTM_PALETTE_SEARCH_URL = <?php echo json_encode(SITE_URL . '/recherche.php', JSON_UNESCAPED_SLASHES); ?>;
    window.ISSTM_PALETTE_LABELS = {
        placeholder: <?php echo json_encode(t('palette_placeholder'), JSON_UNESCAPED_UNICODE); ?>,
        searchFor: <?php echo json_encode(t('palette_search_for'), JSON_UNESCAPED_UNICODE); ?>,
        noResults: <?php echo json_encode(t('palette_no_results'), JSON_UNESCAPED_UNICODE); ?>
    };
</script>
<script src="<?php echo SITE_URL; ?>/script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
<script>
    // PWA : service worker pour la mise en cache hors-ligne des ressources statiques du site.
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?php echo SITE_URL; ?>/sw.js', { scope: '<?php echo SITE_URL; ?>/' }).catch(() => {});
        });
    }
</script>
</body>
</html>