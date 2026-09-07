<?php
// language.php démarre déjà la session, pas besoin de le refaire.
include_once 'language.php';

// Deux types de session possibles, mutuellement exclusifs pour l'affichage du header :
// - $header_is_site_user : compte ISSTM classique (site principal), toutes pages.
// - $header_is_bib_admin_only : compte autonome de la bibliothèque (admin_id, posé par
//   login_handler.php), qui n'a accès qu'à la gestion de la bibliothèque, mais dont l'état
//   connecté/déconnexion doit rester visible sur tout le site comme pour un admin ISSTM.
$header_is_site_user = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
$header_is_bib_admin_only = !$header_is_site_user && isset($_SESSION['admin_id']);

// On récupère le chemin du logo une seule fois pour l'utiliser dans le header
require_once 'db_connect.php';

// --- Suivi léger pour le tableau de bord admin (administrateur.php) : présence en ligne
// (utilisateurs.last_activity, déjà utilisé par la messagerie mais généralisé ici à toute page) et
// compteur de vues (page_views), un enregistrement par page vue. Pages d'admin exclues du comptage
// de vues pour ne pas fausser les statistiques de fréquentation "publique" du site.
if ($header_is_site_user) {
    $mysqli->query("UPDATE utilisateurs SET last_activity = NOW() WHERE id = " . (int) $_SESSION['user_id']);
}
$header_current_page = basename($_SERVER['PHP_SELF']);
if (strpos($header_current_page, 'admin') !== 0) {
    $pv_user_id = $header_is_site_user ? (int) $_SESSION['user_id'] : null;
    $pv_stmt = $mysqli->prepare("INSERT INTO page_views (page, user_id) VALUES (?, ?)");
    $pv_stmt->bind_param('si', $header_current_page, $pv_user_id);
    $pv_stmt->execute();
    $pv_stmt->close();
}

// Accès à la messagerie interne (comptes Kakal / Scolarité uniquement, voir messagerie.php) :
// affiche un lien avec un badge de messages non lus dans la navigation pour ces deux comptes.
$header_has_messagerie = false;
$header_messagerie_unread = 0;
// Compte scolarité : accès direct depuis "Mon Compte" à sa page de gestion des étudiants
// (admin_etudiants.php), sans avoir à se reconnecter pour y retourner.
$header_is_scolarite = false;
if ($header_is_site_user) {
    $mstmt = $mysqli->prepare("SELECT is_messagerie, is_scolarite FROM utilisateurs WHERE id = ?");
    $mstmt->bind_param('i', $_SESSION['user_id']);
    $mstmt->execute();
    $mrow = $mstmt->get_result()->fetch_assoc();
    $mstmt->close();
    if ($mrow && (int) $mrow['is_messagerie'] === 1) {
        $header_has_messagerie = true;
        $header_messagerie_unread = (int) $mysqli->query(
            "SELECT COUNT(*) c FROM messagerie_messages WHERE sender_id != " . (int) $_SESSION['user_id'] . " AND read_at IS NULL"
        )->fetch_assoc()['c'];
    }
    $header_is_scolarite = $mrow && (int) $mrow['is_scolarite'] === 1;
}

// Groupes de classe (enseignants / étudiants) : lien "Mes groupes" avec jauge de notification
// (somme des messages ET annonces non lus, tous groupes confondus, voir groupe_functions.php).
$header_has_groupes = $header_is_site_user && in_array($_SESSION['user_role'] ?? '', ['enseignant', 'etudiant'], true);
// Communauté : fil d'actualité partagé enseignants/étudiants (voir communaute.php), l'admin y a
// aussi accès pour prévisualiser et modérer directement depuis le lien du header.
$header_has_communaute = $header_is_site_user && in_array($_SESSION['user_role'] ?? '', ['enseignant', 'etudiant', 'admin'], true);
$header_groupes_unread = 0;
if ($header_has_groupes) {
    require_once 'groupe_functions.php';
    $guRes = $mysqli->query("SELECT groupe_id, last_read_at FROM groupe_membres WHERE user_id = " . (int) $_SESSION['user_id'] . " AND is_banned = 0");
    while ($guRow = $guRes->fetch_assoc()) {
        $header_groupes_unread += groupe_unread_count($mysqli, (int) $guRow['groupe_id'], (int) $_SESSION['user_id'], $guRow['last_read_at']);
    }
}

// Notifications Communauté (nouvelles publications, réponses à mes commentaires) : cloche avec
// badge dans la navigation, voir communaute_notifications.php pour le détail + marquage lu.
$header_notifications_unread = 0;
if ($header_has_communaute) {
    $header_notifications_unread = (int) $mysqli->query(
        "SELECT COUNT(*) c FROM communaute_notifications WHERE user_id = " . (int) $_SESSION['user_id'] . " AND is_read = 0"
    )->fetch_assoc()['c'];
}

// Amis / messages privés / groupes personnels : disponibles à tous les comptes du site principal.
$header_has_amis = $header_is_site_user;
$header_amis_demandes_recues = 0;
if ($header_has_amis) {
    $header_amis_demandes_recues = (int) $mysqli->query(
        "SELECT COUNT(*) c FROM amis_demandes WHERE destinataire_id = " . (int) $_SESSION['user_id'] . " AND statut = 'en_attente'"
    )->fetch_assoc()['c'];
}

$logo_path = SITE_URL . '/images/logo-isstm.jpg'; // Valeur par défaut
$result = $mysqli->query("SELECT content_value_fr FROM site_content WHERE content_key = 'logo_image_path'");
if ($row = $result->fetch_assoc()) {
    $logo_path = SITE_URL . '/' . htmlspecialchars($row['content_value_fr']);
}

// Image de fond semi-transparente du header, optionnelle (gérée dans admin_contenu.php).
$header_bg_path = '';
$result = $mysqli->query("SELECT content_value_fr FROM site_content WHERE content_key = 'header_bg_image_path'");
if (($row = $result->fetch_assoc()) && !empty($row['content_value_fr'])) {
    $header_bg_path = SITE_URL . '/' . htmlspecialchars($row['content_value_fr']);
}

// Bannière personnalisée par page (gérée dans admin_banners.php) : chaque page du site peut
// avoir sa propre image ou vidéo de bannière, indépendamment des autres. On identifie la page
// en cours via le nom réel du fichier PHP appelant (ex: 'historique.php'), et on ne remplace le
// fond que pour CETTE page précise (l'injection ci-dessous ne s'applique qu'à la réponse HTTP
// courante, donc ça ne "fuit" jamais vers les autres pages même si elles partagent une classe
// CSS de bannière commune, ex: login.php et inscription.php utilisent tous deux .inscription-banner).
$page_banner = null;
if ($mysqli->query("SHOW TABLES LIKE 'site_banners'")->num_rows > 0) {
    // Chemin relatif au site (ex: 'bibliotheque/recherche.php') plutôt que le simple nom de
    // fichier : plusieurs pages de la bibliothèque partagent le même basename qu'une page
    // racine (recherche.php, profil.php...) et se retrouveraient sinon à tort sur la même clé.
    // Pour toute page racine, ce chemin reste identique à l'ancien basename (aucune migration
    // nécessaire pour les bannières déjà enregistrées).
    $current_page_key = ltrim(substr($_SERVER['PHP_SELF'], strlen(SITE_URL)), '/');
    $stmt = $mysqli->prepare("SELECT media_type, media_path FROM site_banners WHERE page_key = ? AND is_active = 1 LIMIT 1");
    $stmt->bind_param('s', $current_page_key);
    $stmt->execute();
    $page_banner = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
}
// La connexion sera fermée par les autres scripts qui incluent ce fichier
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) && $page_title !== '' ? htmlspecialchars($page_title) . ' - ISSTM Mahajanga' : 'ISSTM Mahajanga'; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <!-- PWA : installation sur écran d'accueil + mise en cache hors-ligne des ressources statiques -->
    <link rel="manifest" href="<?php echo SITE_URL; ?>/manifest.webmanifest">
    <meta name="theme-color" content="#003366">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/images/icon-192.png">
    <!-- Favicon (logo ISSTM), affiché dans l'onglet du navigateur -->
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo SITE_URL; ?>/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?php echo SITE_URL; ?>/images/icon-512.png">
    <!-- Intégration de Font Awesome pour les icônes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Dancing+Script:wght@700&family=Lora:ital,wght@1,400;1,500&family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- AJOUT : Bibliothèque pour les fenêtres de dialogue stylées -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if ($page_banner): ?>
        <!-- Une bannière personnalisée (image ou vidéo) est active sur cette page : on désactive
             l'effet "polygone lumineux" par défaut de .search-banner (::after, révélé au passage
             de la souris), qui n'a plus de raison d'être une fois un vrai média affiché. -->
        <style>
            .search-banner::after { display: none !important; }
        </style>
    <?php endif; ?>
    <?php if ($page_banner && $page_banner['media_type'] === 'image'): ?>
        <!-- Bannière personnalisée (image) de cette page, définie dans admin_banners.php -->
        <style>
            .page-banner {
                background-image: linear-gradient(rgba(0, 25, 51, 0.4), rgba(0, 25, 51, 0.4)), url('<?php echo htmlspecialchars($page_banner['media_path']); ?>') !important;
            }
        </style>
    <?php elseif ($page_banner && $page_banner['media_type'] === 'video'): ?>
        <!-- Bannière personnalisée (vidéo) de cette page : le fond CSS est neutralisé, une vraie vidéo est insérée en JS -->
        <style>
            .page-banner { background-image: none !important; }
            .page-banner-video-layer {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                z-index: -2;
            }
            .page-banner-video-overlay {
                position: absolute;
                inset: 0;
                background: linear-gradient(rgba(0, 25, 51, 0.4), rgba(0, 25, 51, 0.4));
                z-index: -1;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const banner = document.querySelector('.page-banner');
                if (!banner) return;
                const video = document.createElement('video');
                video.className = 'page-banner-video-layer';
                video.src = <?php echo json_encode(htmlspecialchars_decode($page_banner['media_path'])); ?>;
                video.autoplay = true;
                video.muted = true;
                video.loop = true;
                video.playsInline = true;
                const overlay = document.createElement('div');
                overlay.className = 'page-banner-video-overlay';
                banner.prepend(overlay);
                banner.prepend(video);
            });
        </script>
    <?php endif; ?>
    <script>
        // Empêche tout flash du préchargeur si l'utilisateur l'a déjà vu durant cette session
        // (navigation entre pages) : l'attribut est posé avant même que le <body> ne soit analysé.
        (function () {
            try {
                if (sessionStorage.getItem('isstm_preloader_shown')) {
                    document.documentElement.setAttribute('data-preloader', 'skip');
                }
            } catch (e) {}
        })();
    </script>
</head>
<body>

<div id="site-preloader" class="site-preloader" aria-hidden="true">
    <div class="site-preloader-visual">
        <div class="site-preloader-ring"></div>
        <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="ISSTM" class="site-preloader-logo">
    </div>
    <p class="site-preloader-text"><?php echo t('chargement_texte'); ?><span class="site-preloader-dots"><span>.</span><span>.</span><span>.</span></span></p>
    <div class="site-preloader-bar"><div class="site-preloader-bar-fill"></div></div>
</div>
<script>
    (function () {
        var preloader = document.getElementById('site-preloader');
        if (!preloader || document.documentElement.getAttribute('data-preloader') === 'skip') return;
        try { sessionStorage.setItem('isstm_preloader_shown', '1'); } catch (e) {}
        document.documentElement.style.overflow = 'hidden';
        setTimeout(function () {
            preloader.classList.add('is-hidden');
            document.documentElement.style.overflow = '';
            setTimeout(function () { preloader.remove(); }, 700);
        }, 5000);
    })();
</script>

<header class="main-header">
    <?php if ($header_bg_path !== ''): ?>
        <div class="main-header-bg-photo" style="background-image:url('<?php echo $header_bg_path; ?>');"></div>
    <?php endif; ?>
    <div class="container">
        <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
            <img src="<?php echo $logo_path; ?>" alt="Logo ISSTM">
            <div class="logo-text">
                <span class="institute-name-line1">Institut Supérieur des Sciences</span>
                <span class="institute-name-line2">et Technologies de Mahajanga</span>
                <span class="motto">Honnêteté - Discipline - Excellence</span>
            </div>
        </a>

        <nav class="main-nav">
            <ul class="nav-links">
                <?php if (rtrim($_SERVER['PHP_SELF'], '/') !== SITE_URL . '/index.php'): ?>
                    <li><a href="<?php echo SITE_URL; ?>/index.php"><?php echo t('accueil'); ?></a></li>
                <?php endif; ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle"><?php echo t('linstitut'); ?> <i class="fas fa-chevron-down"></i></a>
                    <ul class="dropdown-menu dropdown-menu-mega">
                        <li><a href="<?php echo SITE_URL; ?>/historique.php"><?php echo t('historiques'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/parcours.php"><?php echo t('menu_organigramme'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/filieres.php"><?php echo t('filieres_section_titre'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/inscription.php"><?php echo t('header_admissions'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/enseignants.php"><?php echo t('enseignants'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/vie_etudiante.php"><?php echo t('vie_etudiante'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/bourse.php"><?php echo t('bourse'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/galerie.php"><?php echo t('galeries'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/evenements.php"><?php echo t('menu_evenements'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/bibliotheque/index.php"><?php echo t('bib_nav_titre'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/documents.php"><?php echo t('menu_documents'); ?></a></li>
                    </ul>
                </li>
                <li><a href="<?php echo SITE_URL; ?>/actualite.php"><?php echo t('actualites'); ?></a></li>

                <?php if ($header_is_site_user): ?>
                    <!-- Si l'utilisateur est connecté -->
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle nav-account-toggle">
                            <?php if (!empty($_SESSION['user_avatar'])): ?>
                                <img src="<?php echo SITE_URL . '/' . htmlspecialchars($_SESSION['user_avatar']); ?>" alt="Avatar" class="nav-account-avatar">
                            <?php else: ?>
                                <i class="fas fa-circle-user nav-account-avatar-placeholder"></i>
                            <?php endif; ?>
                            <?php echo t('mon_compte'); ?> <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo SITE_URL; ?>/profil.php"><?php echo t('profil_titre'); ?></a></li>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <li><a href="<?php echo SITE_URL; ?>/administrateur.php"><?php echo t('administration'); ?></a></li>
                            <?php endif; ?>
                            <?php if ($header_is_scolarite): ?>
                                <li><a href="<?php echo SITE_URL; ?>/admin_etudiants.php"><?php echo t('admin_etudiants_titre'); ?></a></li>
                                <li><a href="<?php echo SITE_URL; ?>/admin_documents.php"><?php echo t('admin_documents_titre'); ?></a></li>
                            <?php endif; ?>
                            <?php if ($header_has_messagerie): ?>
                                <li><a href="<?php echo SITE_URL; ?>/messagerie.php"><?php echo t('messagerie_titre'); ?><?php if ($header_messagerie_unread > 0): ?> <span class="messagerie-nav-badge"><?php echo $header_messagerie_unread > 9 ? '9+' : $header_messagerie_unread; ?></span><?php endif; ?></a></li>
                            <?php endif; ?>
                            <?php if ($header_has_groupes): ?>
                                <li><a href="<?php echo SITE_URL; ?>/mes_groupes.php"><?php echo t('groupe_nav_titre'); ?><?php if ($header_groupes_unread > 0): ?> <span class="messagerie-nav-badge"><?php echo $header_groupes_unread > 9 ? '9+' : $header_groupes_unread; ?></span><?php endif; ?></a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php elseif ($header_is_bib_admin_only): ?>
                    <!-- Compte autonome de la bibliothèque : accès limité à sa gestion -->
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle nav-account-toggle">
                            <?php if (!empty($_SESSION['admin_avatar'])): ?>
                                <img src="<?php echo SITE_URL . '/bibliotheque/' . htmlspecialchars($_SESSION['admin_avatar']); ?>" alt="Avatar" class="nav-account-avatar">
                            <?php else: ?>
                                <i class="fas fa-circle-user nav-account-avatar-placeholder"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($_SESSION['admin_username']); ?> <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo SITE_URL; ?>/bibliotheque/admin/profil.php"><?php echo t('bib_mon_profil'); ?></a></li>
                            <li><a href="<?php echo SITE_URL; ?>/bibliotheque/admin/dashboard.php"><?php echo t('bib_admin_dashboard_titre'); ?></a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($header_has_communaute): ?>
                    <li class="nav-icon messagerie-nav-icon">
                        <a href="<?php echo SITE_URL; ?>/communaute.php" title="<?php echo t('communaute_titre'); ?>">
                            <i class="fas fa-users-rectangle"></i>
                        </a>
                    </li>
                    <li class="dropdown nav-icon communaute-notif-dropdown">
                        <a href="#" class="dropdown-toggle communaute-notif-toggle" title="<?php echo t('notifications_titre'); ?>">
                            <i class="fas fa-bell"></i>
                            <?php if ($header_notifications_unread > 0): ?>
                                <span class="messagerie-nav-badge"><?php echo $header_notifications_unread > 9 ? '9+' : $header_notifications_unread; ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="communaute-notif-menu">
                            <h4><?php echo t('notifications_titre'); ?></h4>
                            <div class="communaute-notif-list" data-empty-label="<?php echo htmlspecialchars(t('notifications_vide')); ?>"><p class="communaute-notif-loading"><?php echo t('notifications_chargement'); ?></p></div>
                            <a href="<?php echo SITE_URL; ?>/notifications.php" class="communaute-notif-voir-tout"><?php echo t('notifications_voir_tout'); ?> <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </li>
                <?php endif; ?>

                <li class="nav-icon theme-toggle-btn" id="theme-toggle" title="<?php echo t('changer_theme'); ?>">
                    <i class="fas fa-moon"></i>
                    <i class="fas fa-sun"></i>
                </li>
                <li class="dropdown nav-icon">
                    <a href="#" class="dropdown-toggle lang-toggle-current" title="<?php echo t('changer_langue'); ?>"><img src="<?php echo SITE_URL; ?>/images/flags/<?php echo htmlspecialchars($lang); ?>.svg" class="lang-flag lang-flag-current" alt=""></a>
                    <ul class="dropdown-menu lang-menu">
                        <li><a href="<?php echo lang_switch_url('fr'); ?>"><img src="<?php echo SITE_URL; ?>/images/flags/fr.svg" class="lang-flag" alt=""> Français</a></li>
                        <li><a href="<?php echo lang_switch_url('en'); ?>"><img src="<?php echo SITE_URL; ?>/images/flags/en.svg" class="lang-flag" alt=""> English</a></li>
                        <li><a href="<?php echo lang_switch_url('mg'); ?>"><img src="<?php echo SITE_URL; ?>/images/flags/mg.svg" class="lang-flag" alt=""> Malagasy</a></li>
                    </ul>
                </li>
                <li class="nav-search-container" id="nav-search-container">
                    <button type="button" class="search-icon-btn" id="nav-search-btn" title="<?php echo t('rechercher'); ?>" aria-label="<?php echo t('rechercher'); ?>" aria-expanded="false">
                        <i class="fas fa-search"></i>
                        <i class="fas fa-xmark"></i>
                    </button>
                    <form action="<?php echo SITE_URL; ?>/recherche.php" method="get" class="header-search-form" id="header-search-form">
                        <i class="fas fa-search header-search-form-icon"></i>
                        <input type="search" name="q" placeholder="<?php echo t('rechercher_placeholder'); ?>" required autocomplete="off" id="header-search-input">
                        <button type="submit" class="header-search-submit" aria-label="<?php echo t('rechercher'); ?>"><i class="fas fa-arrow-right"></i></button>
                    </form>
                </li>
                <!-- Bouton Connexion / Déconnexion -->
                <?php if ($header_is_site_user): ?>
                    <li><a href="<?php echo SITE_URL; ?>/logout.php" class="btn-logout-icon" data-username="<?php echo htmlspecialchars($_SESSION['user_nom']); ?>" title="<?php echo t('deconnexion'); ?>"><i class="fas fa-sign-out-alt"></i></a></li>
                <?php elseif ($header_is_bib_admin_only): ?>
                    <li><a href="<?php echo SITE_URL; ?>/bibliotheque/admin/logout.php" class="btn-logout-icon" data-username="<?php echo htmlspecialchars($_SESSION['admin_username']); ?>" title="<?php echo t('deconnexion'); ?>"><i class="fas fa-sign-out-alt"></i></a></li>
                <?php endif; ?>

            </ul>
            <div class="menu-toggle" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </div>
</header>

<div class="mobile-menu" id="mobile-menu">
    <form action="<?php echo SITE_URL; ?>/recherche.php" method="get" class="mobile-menu-search">
        <input type="search" name="q" placeholder="<?php echo t('rechercher_placeholder'); ?>" required autocomplete="off">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>

    <a href="<?php echo SITE_URL; ?>/index.php"><?php echo t('accueil'); ?></a>
    <a href="<?php echo SITE_URL; ?>/historique.php"><?php echo t('historiques'); ?></a>
    <a href="<?php echo SITE_URL; ?>/parcours.php"><?php echo t('menu_organigramme'); ?></a>
    <a href="<?php echo SITE_URL; ?>/filieres.php"><?php echo t('filieres_section_titre'); ?></a>
    <a href="<?php echo SITE_URL; ?>/enseignants.php"><?php echo t('enseignants'); ?></a>
    <a href="<?php echo SITE_URL; ?>/vie_etudiante.php"><?php echo t('vie_etudiante'); ?></a>
    <a href="<?php echo SITE_URL; ?>/bourse.php"><?php echo t('bourse'); ?></a>
    <a href="<?php echo SITE_URL; ?>/inscription.php"><?php echo t('header_admissions'); ?></a>
    <a href="<?php echo SITE_URL; ?>/galerie.php"><?php echo t('galeries_photos_media'); ?></a>
    <a href="<?php echo SITE_URL; ?>/evenements.php"><?php echo t('menu_evenements'); ?></a>
    <a href="<?php echo SITE_URL; ?>/bibliotheque/index.php"><?php echo t('bib_nav_titre'); ?></a>
    <a href="<?php echo SITE_URL; ?>/documents.php"><?php echo t('menu_documents'); ?></a>
    <a href="<?php echo SITE_URL; ?>/actualite.php"><?php echo t('actualites'); ?></a>
    <a href="<?php echo SITE_URL; ?>/index.php#partenaires"><?php echo t('partenaires_section_titre'); ?></a>
    <a href="<?php echo SITE_URL; ?>/index.php#contact"><?php echo t('contact_section_titre'); ?></a>

    <div class="mobile-menu-divider"></div>

    <?php if ($header_is_site_user): ?>
        <a href="<?php echo SITE_URL; ?>/profil.php"><i class="fas fa-circle-user"></i> <?php echo t('profil_titre'); ?></a>
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <a href="<?php echo SITE_URL; ?>/administrateur.php"><i class="fas fa-toolbox"></i> <?php echo t('administration'); ?></a>
        <?php endif; ?>
        <?php if ($header_is_scolarite): ?>
            <a href="<?php echo SITE_URL; ?>/admin_etudiants.php"><i class="fas fa-user-graduate"></i> <?php echo t('admin_etudiants_titre'); ?></a>
            <a href="<?php echo SITE_URL; ?>/admin_documents.php"><i class="fas fa-file-lines"></i> <?php echo t('admin_documents_titre'); ?></a>
        <?php endif; ?>
        <?php if ($header_has_messagerie): ?>
            <a href="<?php echo SITE_URL; ?>/messagerie.php"><i class="fas fa-comments"></i> <?php echo t('messagerie_titre'); ?><?php if ($header_messagerie_unread > 0): ?> <span class="messagerie-nav-badge"><?php echo $header_messagerie_unread > 9 ? '9+' : $header_messagerie_unread; ?></span><?php endif; ?></a>
        <?php endif; ?>
        <?php if ($header_has_groupes): ?>
            <a href="<?php echo SITE_URL; ?>/mes_groupes.php"><i class="fas fa-people-group"></i> <?php echo t('groupe_nav_titre'); ?><?php if ($header_groupes_unread > 0): ?> <span class="messagerie-nav-badge"><?php echo $header_groupes_unread > 9 ? '9+' : $header_groupes_unread; ?></span><?php endif; ?></a>
        <?php endif; ?>
        <?php if ($header_has_communaute): ?>
            <a href="<?php echo SITE_URL; ?>/communaute.php"><i class="fas fa-users-rectangle"></i> <?php echo t('communaute_titre'); ?></a>
            <a href="<?php echo SITE_URL; ?>/notifications.php"><i class="fas fa-bell"></i> <?php echo t('notifications_titre'); ?><?php if ($header_notifications_unread > 0): ?> <span class="messagerie-nav-badge"><?php echo $header_notifications_unread > 9 ? '9+' : $header_notifications_unread; ?></span><?php endif; ?></a>
        <?php endif; ?>
        <a href="<?php echo SITE_URL; ?>/logout.php"><i class="fas fa-right-from-bracket"></i> <?php echo t('deconnexion'); ?></a>
    <?php elseif ($header_is_bib_admin_only): ?>
        <a href="<?php echo SITE_URL; ?>/bibliotheque/admin/profil.php"><i class="fas fa-user-gear"></i> <?php echo t('bib_mon_profil'); ?></a>
        <a href="<?php echo SITE_URL; ?>/bibliotheque/admin/dashboard.php"><i class="fas fa-toolbox"></i> <?php echo t('bib_admin_dashboard_titre'); ?></a>
        <a href="<?php echo SITE_URL; ?>/bibliotheque/admin/logout.php"><i class="fas fa-right-from-bracket"></i> <?php echo t('deconnexion'); ?></a>
    <?php endif; ?>

    <div class="mobile-menu-divider"></div>

    <div class="mobile-menu-footer-row">
        <button type="button" class="theme-toggle-btn mobile-theme-toggle" title="<?php echo t('changer_theme'); ?>">
            <i class="fas fa-moon"></i><i class="fas fa-sun"></i>
        </button>
        <div class="mobile-menu-lang">
            <a href="<?php echo lang_switch_url('fr'); ?>"><img src="<?php echo SITE_URL; ?>/images/flags/fr.svg" class="lang-flag" alt=""> FR</a>
            <a href="<?php echo lang_switch_url('en'); ?>"><img src="<?php echo SITE_URL; ?>/images/flags/en.svg" class="lang-flag" alt=""> EN</a>
            <a href="<?php echo lang_switch_url('mg'); ?>"><img src="<?php echo SITE_URL; ?>/images/flags/mg.svg" class="lang-flag" alt=""> MG</a>
        </div>
    </div>
</div>

<?php $header_is_messagerie_page = in_array(basename($_SERVER['PHP_SELF']), ['messagerie.php', 'groupe_chat.php'], true); ?>

<?php if (!$header_is_messagerie_page): ?>
<!-- === Assistant d'aide virtuel IA (site entier) === -->
<button type="button" class="ai-help-bubble" id="ai-help-toggle" title="<?php echo t('aide_ia_titre'); ?>">
    <i class="fas fa-robot"></i>
</button>

<div class="ai-help-panel" id="ai-help-panel">
    <div class="ai-help-header">
        <span><i class="fas fa-robot"></i> <?php echo t('aide_ia_titre'); ?></span>
        <button type="button" id="ai-help-close" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="ai-help-messages" id="ai-help-messages">
        <div class="ai-help-msg ai-help-msg-bot"><?php echo t('aide_ia_message_bienvenue'); ?></div>
    </div>
    <form class="ai-help-form" id="ai-help-form">
        <input type="text" id="ai-help-input" placeholder="<?php echo t('aide_ia_placeholder'); ?>" autocomplete="off" required>
        <button type="submit"><i class="fas fa-paper-plane"></i></button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('ai-help-toggle');
    const panel = document.getElementById('ai-help-panel');
    const closeBtn = document.getElementById('ai-help-close');
    const form = document.getElementById('ai-help-form');
    const input = document.getElementById('ai-help-input');
    const messages = document.getElementById('ai-help-messages');

    function addMessage(text, sender, link, linkLabel) {
        const div = document.createElement('div');
        div.className = 'ai-help-msg ai-help-msg-' + sender;
        div.textContent = text;
        if (link && linkLabel) {
            const a = document.createElement('a');
            a.href = link;
            a.className = 'ai-help-msg-link';
            a.innerHTML = linkLabel + ' <i class="fas fa-arrow-right"></i>';
            div.appendChild(document.createElement('br'));
            div.appendChild(a);
        }
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    // Efface la conversation (ne garde que le message d'accueil), utilisé à chaque fermeture
    // du panneau pour ne pas laisser traîner l'historique de discussion.
    function resetConversation() {
        messages.innerHTML = '';
        const welcome = document.createElement('div');
        welcome.className = 'ai-help-msg ai-help-msg-bot';
        welcome.textContent = <?php echo json_encode(t('aide_ia_message_bienvenue')); ?>;
        messages.appendChild(welcome);
    }

    toggle.addEventListener('click', () => {
        const wasOpen = panel.classList.contains('is-open');
        panel.classList.toggle('is-open');
        if (!wasOpen) {
            input.focus();
        } else {
            resetConversation();
        }
    });
    closeBtn.addEventListener('click', () => {
        panel.classList.remove('is-open');
        resetConversation();
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const question = input.value.trim();
        if (!question) return;
        addMessage(question, 'user');
        input.value = '';
        input.disabled = true;

        const typing = document.createElement('div');
        typing.className = 'ai-help-msg ai-help-msg-bot ai-help-typing';
        typing.innerHTML = '<i class="fas fa-ellipsis"></i>';
        messages.appendChild(typing);
        messages.scrollTop = messages.scrollHeight;

        try {
            const res = await fetch('<?php echo SITE_URL; ?>/ai_help.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: question })
            });
            const data = await res.json();
            typing.remove();
            addMessage(data.reply, 'bot', data.link, data.link_label);
        } catch (err) {
            typing.remove();
            addMessage('Erreur de connexion. Réessayez plus tard.', 'bot');
        }
        input.disabled = false;
        input.focus();
    });
});
</script>
<?php endif; ?>

<main>