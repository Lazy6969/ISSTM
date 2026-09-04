<?php
include_once 'language.php';
require_once 'db_connect.php';
require_once 'amis_functions.php';
require_once 'groupe_perso_functions.php';

amis_require_login();
$self_id = (int) $_SESSION['user_id'];
$profile_id = (int) ($_GET['id'] ?? 0);

if ($profile_id === $self_id) {
    header('Location: profil.php');
    exit;
}

$user = $mysqli->query("SELECT id, nom, avatar_path, role, bio, date_naissance, ville, centres_interet, lien_facebook, lien_linkedin, site_web FROM utilisateurs WHERE id = $profile_id")->fetch_assoc();
if (!$user) {
    header('Location: annuaire.php');
    exit;
}

$filiere = $user['role'] === 'etudiant' ? amis_get_filiere($mysqli, $profile_id) : null;
$statut = amis_get_statut($mysqli, $self_id, $profile_id);
$nb_amis = count(amis_get_liste($mysqli, $profile_id));
$amis_communs = amis_get_mutuels($mysqli, $self_id, $profile_id);

$role_labels = [
    'admin' => t('admin_utilisateurs_role_admin'),
    'enseignant' => t('admin_utilisateurs_role_enseignant'),
    'etudiant' => t('admin_utilisateurs_role_etudiant'),
    'bibliotheque' => t('admin_utilisateurs_role_bibliotheque'),
];

$page_title = htmlspecialchars($user['nom']);
include 'header.php';
?>

<div class="page-content profil-public-page-content">
    <div class="container">
        <div class="profile-header-card" id="profil-public-card" data-user-id="<?php echo (int) $user['id']; ?>" data-statut="<?php echo htmlspecialchars($statut); ?>">
            <?php if (!empty($user['avatar_path'])): ?>
                <img src="<?php echo htmlspecialchars($user['avatar_path']); ?>" alt="" class="profile-header-avatar">
            <?php else: ?>
                <div class="profile-header-avatar profile-header-avatar-placeholder"><i class="fas fa-user"></i></div>
            <?php endif; ?>
            <div>
                <h2><?php echo htmlspecialchars($user['nom']); ?></h2>
                <span class="profile-role-badge"><i class="fas fa-shield-halved"></i> <?php echo htmlspecialchars($role_labels[$user['role']] ?? $user['role']); ?></span>
                <?php if (!empty($filiere)): ?>
                    <span class="profile-role-badge"><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($filiere['filiere']); ?><?php if (!empty($filiere['niveau'])): ?> · <?php echo htmlspecialchars($filiere['niveau']); ?><?php endif; ?></span>
                <?php endif; ?>
                <div class="profil-public-stats">
                    <span class="profil-public-stat"><i class="fas fa-user-group"></i> <?php echo sprintf(t('profil_public_nb_amis'), $nb_amis); ?></span>
                    <?php if (!empty($amis_communs)): ?>
                        <span class="profil-public-stat"><i class="fas fa-people-arrows"></i> <?php echo sprintf(t('profil_public_nb_amis_communs'), count($amis_communs)); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="profile-settings-card">
            <h3><i class="fas fa-id-card"></i> <?php echo t('profil_public_a_propos'); ?></h3>
            <p class="profil-public-bio"><?php echo !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : '<em>' . t('profil_public_pas_de_bio') . '</em>'; ?></p>

            <div id="profil-public-actions" class="profil-public-actions"></div>
        </div>

        <?php if (!empty($amis_communs)): ?>
        <div class="profile-settings-card">
            <h3><i class="fas fa-people-arrows"></i> <?php echo sprintf(t('profil_public_amis_communs_titre'), count($amis_communs)); ?></h3>
            <div class="annuaire-results">
                <?php foreach (array_slice($amis_communs, 0, 8) as $m): ?>
                    <a href="profil_public.php?id=<?php echo (int) $m['id']; ?>" class="annuaire-result-card">
                        <div class="annuaire-result-avatar">
                            <?php if (!empty($m['avatar_path'])): ?>
                                <img src="<?php echo htmlspecialchars($m['avatar_path']); ?>" alt="">
                            <?php else: ?>
                                <i class="fas fa-circle-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="annuaire-result-info">
                            <strong><?php echo htmlspecialchars($m['nom']); ?></strong>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php
        $has_more_info = !empty($user['ville']) || !empty($user['date_naissance']) || !empty($user['centres_interet'])
            || !empty($user['lien_facebook']) || !empty($user['lien_linkedin']) || !empty($user['site_web']);
        ?>
        <?php if ($has_more_info): ?>
        <div class="profile-settings-card">
            <h3><i class="fas fa-circle-info"></i> <?php echo t('profil_public_en_savoir_plus'); ?></h3>
            <ul class="profil-public-infos-list">
                <?php if (!empty($user['ville'])): ?>
                    <li><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($user['ville']); ?></li>
                <?php endif; ?>
                <?php if (!empty($user['date_naissance'])): ?>
                    <li><i class="fas fa-cake-candles"></i> <?php echo date('d/m/Y', strtotime($user['date_naissance'])); ?></li>
                <?php endif; ?>
                <?php if (!empty($user['centres_interet'])): ?>
                    <li><i class="fas fa-heart"></i> <?php echo htmlspecialchars($user['centres_interet']); ?></li>
                <?php endif; ?>
            </ul>
            <?php if (!empty($user['lien_facebook']) || !empty($user['lien_linkedin']) || !empty($user['site_web'])): ?>
                <div class="profil-public-social-links">
                    <?php if (!empty($user['lien_facebook'])): ?>
                        <a href="<?php echo htmlspecialchars($user['lien_facebook']); ?>" target="_blank" rel="noopener noreferrer" title="Facebook"><i class="fab fa-facebook"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($user['lien_linkedin'])): ?>
                        <a href="<?php echo htmlspecialchars($user['lien_linkedin']); ?>" target="_blank" rel="noopener noreferrer" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($user['site_web'])): ?>
                        <a href="<?php echo htmlspecialchars($user['site_web']); ?>" target="_blank" rel="noopener noreferrer" title="Site web"><i class="fas fa-globe"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
window.PROFIL_PUBLIC_LABELS = {
    ajouter: <?php echo json_encode(t('amis_ajouter')); ?>,
    demande_envoyee: <?php echo json_encode(t('amis_demande_envoyee')); ?>,
    annuler: <?php echo json_encode(t('amis_annuler_demande')); ?>,
    accepter: <?php echo json_encode(t('amis_accepter')); ?>,
    refuser: <?php echo json_encode(t('amis_refuser')); ?>,
    amis: <?php echo json_encode(t('amis_statut_amis')); ?>,
    envoyer_message: <?php echo json_encode(t('amis_envoyer_message')); ?>,
    retirer_ami: <?php echo json_encode(t('amis_retirer')); ?>,
    erreur: <?php echo json_encode(t('messagerie_erreur')); ?>,
    confirm_retirer: <?php echo json_encode(t('amis_confirm_retirer')); ?>
};
</script>
<script src="<?php echo SITE_URL; ?>/profil_public.js?v=<?php echo filemtime(__DIR__ . '/profil_public.js'); ?>"></script>

<?php include 'footer.php'; ?>
