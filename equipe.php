<?php
include_once 'language.php';
$page_title = t('equipe_titre');
include 'header.php';

// Informations fixes de l'équipe projet : volontairement codées en dur (pas de table dédiée,
// pas de panneau admin) — voir demande explicite : ces informations ne sont pas paramétrables
// depuis l'administrateur.
$team_members = [
    [
        'nom' => 'RAMANANA Mirindra Michel',
        'photo' => 'images/etudiant/mirindra.jpeg',
        'icon' => 'fa-crown',
        'role_key' => 'equipe_role_mirindra',
        'bio_key' => 'equipe_bio_mirindra',
        'highlight_key' => 'equipe_highlight_mirindra',
        'mention_key' => 'equipe_mention_m',
        'featured' => true,
        'tel' => '0380746987',
        'facebook' => 'https://web.facebook.com/lauthner.ramanana',
    ],
    [
        'nom' => 'RANDRIAMAHAFALY Safidy Thierry',
        'photo' => 'images/etudiant/safidy.jpg',
        'icon' => 'fa-server',
        'role_key' => 'equipe_role_safidy',
        'bio_key' => 'equipe_bio_safidy',
        'mention_key' => 'equipe_mention_m',
        'tel' => '0380545618',
        'facebook' => 'https://web.facebook.com/safilaureat.randriamahafaly',
    ],
    [
        'nom' => 'RAZAFINDRABARY Heather Doleen Jameelah',
        'photo' => 'images/etudiant/jameelah.jpg',
        'icon' => 'fa-wand-magic-sparkles',
        'role_key' => 'equipe_role_jameelah',
        'bio_key' => 'equipe_bio_jameelah',
        'mention_key' => 'equipe_mention_f',
        'tel' => '0385229010',
        'facebook' => 'https://web.facebook.com/profile.php?id=100073469688031',
    ],
    [
        'nom' => 'JAOSOA Tanael Faustin',
        'photo' => 'images/etudiant/tanael.jpg',
        'icon' => 'fa-screwdriver-wrench',
        'role_key' => 'equipe_role_tanael',
        'bio_key' => 'equipe_bio_tanael',
        'mention_key' => 'equipe_mention_m',
        'tel' => '0344306616',
        'facebook' => 'https://web.facebook.com/tanael.rolland.90',
    ],
];
?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('equipe_titre'); ?></span>
        </nav>
        <h1><?php echo t('equipe_titre'); ?></h1>
        <p><?php echo t('equipe_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <?php include 'background_animation.php'; ?>
    <div class="container">

        <p class="intro-text team-intro animate-on-scroll"><?php echo t('equipe_intro'); ?></p>

        <div class="team-list">
            <?php foreach ($team_members as $i => $m):
                // Décalage de base par développeur (ligne entière), puis un décalage
                // supplémentaire par élément à l'intérieur de la ligne pour que photo, nom,
                // rôle, mention, bio et citation apparaissent l'un après l'autre plutôt que
                // d'un seul bloc.
                $row_delay = $i * 0.2;
            ?>
                <div class="team-row<?php echo !empty($m['featured']) ? ' is-featured' : ''; ?> animate-on-scroll" style="--delay: <?php echo $row_delay; ?>s">
                    <div class="team-row-media animate-on-scroll" style="--delay: <?php echo $row_delay; ?>s">
                        <span class="team-card-ring"></span>
                        <img class="team-card-photo" src="<?php echo htmlspecialchars($m['photo']); ?>" alt="<?php echo htmlspecialchars(sprintf(t('photo_de_alt'), $m['nom'])); ?>" loading="lazy">
                        <?php if (!empty($m['featured'])): ?>
                            <span class="team-featured-badge"><i class="fas fa-star"></i> <?php echo t('equipe_badge_leader'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="team-row-info">
                        <h3 class="team-row-name animate-on-scroll" style="--delay: <?php echo $row_delay + 0.1; ?>s"><?php echo htmlspecialchars($m['nom']); ?></h3>
                        <span class="team-row-role animate-on-scroll" style="--delay: <?php echo $row_delay + 0.18; ?>s"><i class="fas <?php echo $m['icon']; ?>"></i> <?php echo t($m['role_key']); ?></span>
                        <p class="team-row-mention animate-on-scroll" style="--delay: <?php echo $row_delay + 0.26; ?>s"><?php echo t($m['mention_key']); ?></p>
                        <p class="team-row-bio animate-on-scroll" style="--delay: <?php echo $row_delay + 0.34; ?>s"><?php echo t($m['bio_key']); ?></p>
                        <?php if (!empty($m['highlight_key'])): ?>
                            <p class="team-row-highlight animate-on-scroll" style="--delay: <?php echo $row_delay + 0.42; ?>s">
                                <i class="fas fa-quote-left" aria-hidden="true"></i> <?php echo t($m['highlight_key']); ?>
                            </p>
                        <?php endif; ?>
                        <div class="team-row-contact animate-on-scroll" style="--delay: <?php echo $row_delay + 0.5; ?>s">
                            <?php if (!empty($m['tel'])): ?>
                                <a href="tel:<?php echo htmlspecialchars($m['tel']); ?>" class="team-contact-btn team-contact-tel" title="<?php echo t('telephone'); ?>">
                                    <i class="fas fa-phone" aria-hidden="true"></i> <?php echo htmlspecialchars($m['tel']); ?>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($m['facebook'])): ?>
                                <a href="<?php echo htmlspecialchars($m['facebook']); ?>" class="team-contact-btn team-contact-fb" title="<?php echo t('facebook'); ?>" target="_blank" rel="noopener">
                                    <i class="fab fa-facebook-f" aria-hidden="true"></i> Facebook
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="team-merci animate-on-scroll">
            <i class="fas fa-heart" aria-hidden="true"></i>
            <p><?php echo t('equipe_merci'); ?></p>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>
