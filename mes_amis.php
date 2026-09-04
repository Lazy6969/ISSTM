<?php
// Hub réseau : fusionne recherche d'utilisateurs (+ suggestions), demandes reçues/envoyées, amis,
// et groupes personnels en une seule page à onglets (?tab=recherche|recues|amis|envoyees|groupes).
include_once 'language.php';
require_once 'db_connect.php';
require_once 'amis_functions.php';
require_once 'groupe_perso_functions.php';

amis_require_login();
$self_id = (int) $_SESSION['user_id'];

$amis = amis_get_liste($mysqli, $self_id);

$demandes_recues = $mysqli->query("
    SELECT u.id, u.nom, u.avatar_path, u.role, d.created_at
    FROM amis_demandes d JOIN utilisateurs u ON u.id = d.demandeur_id
    WHERE d.destinataire_id = $self_id AND d.statut = 'en_attente'
    ORDER BY d.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$demandes_envoyees = $mysqli->query("
    SELECT u.id, u.nom, u.avatar_path, u.role, d.created_at
    FROM amis_demandes d JOIN utilisateurs u ON u.id = d.destinataire_id
    WHERE d.demandeur_id = $self_id AND d.statut = 'en_attente'
    ORDER BY d.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$groupes_count = $mysqli->query("SELECT COUNT(*) c FROM groupe_utilisateurs_membres WHERE user_id = $self_id AND is_banned = 0")->fetch_assoc()['c'];

$valid_tabs = ['recherche', 'recues', 'amis', 'envoyees', 'groupes'];
$tab = in_array($_GET['tab'] ?? '', $valid_tabs, true) ? $_GET['tab'] : 'amis';

$flash = null;
if ($tab === 'groupes' && isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

$page_title = t('header_mon_reseau');
include 'header.php';
?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('header_mon_reseau'); ?></span>
        </nav>
        <h1><?php echo t('header_mon_reseau'); ?></h1>
        <p><?php echo t('mes_amis_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <div class="admin-galerie-toolbar etu-tabs">
            <a href="?tab=recherche" class="btn-outline <?php echo $tab === 'recherche' ? 'is-active' : ''; ?>"><i class="fas fa-magnifying-glass"></i> <?php echo t('mes_amis_onglet_recherche'); ?></a>
            <a href="?tab=amis" class="btn-outline <?php echo $tab === 'amis' ? 'is-active' : ''; ?>"><i class="fas fa-user-group"></i> <?php echo t('mes_amis_onglet_amis'); ?> (<?php echo count($amis); ?>)</a>
            <a href="?tab=recues" class="btn-outline <?php echo $tab === 'recues' ? 'is-active' : ''; ?>"><i class="fas fa-inbox"></i> <?php echo t('mes_amis_onglet_recues'); ?> (<?php echo count($demandes_recues); ?>)</a>
            <a href="?tab=envoyees" class="btn-outline <?php echo $tab === 'envoyees' ? 'is-active' : ''; ?>"><i class="fas fa-paper-plane"></i> <?php echo t('mes_amis_onglet_envoyees'); ?> (<?php echo count($demandes_envoyees); ?>)</a>
            <a href="?tab=groupes" class="btn-outline <?php echo $tab === 'groupes' ? 'is-active' : ''; ?>"><i class="fas fa-people-group"></i> <?php echo t('mes_amis_onglet_groupes'); ?> (<?php echo (int) $groupes_count; ?>)</a>
        </div>

        <?php if ($tab === 'recherche'): ?>
            <?php $search_action_url = 'mes_amis.php'; $search_extra_params = ['tab' => 'recherche']; ?>
            <?php include 'annuaire_partiel.php'; ?>
        <?php elseif ($tab === 'amis'): ?>
            <?php if (empty($amis)): ?>
                <p class="gallery-empty"><i class="fas fa-user-group"></i> <?php echo t('mes_amis_aucun_ami'); ?></p>
            <?php else: ?>
                <div class="annuaire-results">
                    <?php foreach ($amis as $a): ?>
                        <div class="annuaire-result-card">
                            <a href="profil_public.php?id=<?php echo (int) $a['id']; ?>" class="annuaire-result-avatar">
                                <?php if (!empty($a['avatar_path'])): ?><img src="<?php echo htmlspecialchars($a['avatar_path']); ?>" alt=""><?php else: ?><i class="fas fa-circle-user"></i><?php endif; ?>
                            </a>
                            <a href="profil_public.php?id=<?php echo (int) $a['id']; ?>" class="annuaire-result-info">
                                <strong><?php echo htmlspecialchars($a['nom']); ?></strong>
                            </a>
                            <a href="messages_prives.php?with=<?php echo (int) $a['id']; ?>" class="btn-outline"><i class="fas fa-paper-plane"></i> <?php echo t('amis_envoyer_message'); ?></a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php elseif ($tab === 'recues'): ?>
            <?php if (empty($demandes_recues)): ?>
                <p class="gallery-empty"><i class="fas fa-inbox"></i> <?php echo t('mes_amis_aucune_demande'); ?></p>
            <?php else: ?>
                <div class="annuaire-results" id="demandes-recues-list">
                    <?php foreach ($demandes_recues as $d): ?>
                        <div class="annuaire-result-card" data-user-id="<?php echo (int) $d['id']; ?>">
                            <a href="profil_public.php?id=<?php echo (int) $d['id']; ?>" class="annuaire-result-avatar">
                                <?php if (!empty($d['avatar_path'])): ?><img src="<?php echo htmlspecialchars($d['avatar_path']); ?>" alt=""><?php else: ?><i class="fas fa-circle-user"></i><?php endif; ?>
                            </a>
                            <a href="profil_public.php?id=<?php echo (int) $d['id']; ?>" class="annuaire-result-info">
                                <strong><?php echo htmlspecialchars($d['nom']); ?></strong>
                            </a>
                            <button type="button" class="btn-add-item btn-accepter-demande" data-user-id="<?php echo (int) $d['id']; ?>"><i class="fas fa-check"></i> <?php echo t('amis_accepter'); ?></button>
                            <button type="button" class="btn-outline btn-refuser-demande" data-user-id="<?php echo (int) $d['id']; ?>"><i class="fas fa-xmark"></i> <?php echo t('amis_refuser'); ?></button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php elseif ($tab === 'envoyees'): ?>
            <?php if (empty($demandes_envoyees)): ?>
                <p class="gallery-empty"><i class="fas fa-paper-plane"></i> <?php echo t('mes_amis_aucune_demande_envoyee'); ?></p>
            <?php else: ?>
                <div class="annuaire-results" id="demandes-envoyees-list">
                    <?php foreach ($demandes_envoyees as $d): ?>
                        <div class="annuaire-result-card" data-user-id="<?php echo (int) $d['id']; ?>">
                            <a href="profil_public.php?id=<?php echo (int) $d['id']; ?>" class="annuaire-result-avatar">
                                <?php if (!empty($d['avatar_path'])): ?><img src="<?php echo htmlspecialchars($d['avatar_path']); ?>" alt=""><?php else: ?><i class="fas fa-circle-user"></i><?php endif; ?>
                            </a>
                            <a href="profil_public.php?id=<?php echo (int) $d['id']; ?>" class="annuaire-result-info">
                                <strong><?php echo htmlspecialchars($d['nom']); ?></strong>
                            </a>
                            <span class="annuaire-result-badge"><i class="fas fa-clock"></i> <?php echo t('amis_demande_envoyee'); ?></span>
                            <button type="button" class="btn-outline btn-annuler-demande" data-user-id="<?php echo (int) $d['id']; ?>"><i class="fas fa-xmark"></i> <?php echo t('amis_annuler_demande'); ?></button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <?php include 'groupe_perso_partiel.php'; ?>
        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    async function postAction(url, userId, extra = {}) {
        const params = new URLSearchParams({ user_id: userId, ...extra });
        const res = await fetch(url, { method: 'POST', body: params });
        return res.json();
    }
    document.querySelectorAll('.btn-accepter-demande').forEach(btn => {
        btn.addEventListener('click', async () => {
            const data = await postAction('ami_repondre.php', btn.dataset.userId, { action: 'accepter' });
            if (data.success) btn.closest('.annuaire-result-card').remove();
        });
    });
    document.querySelectorAll('.btn-refuser-demande').forEach(btn => {
        btn.addEventListener('click', async () => {
            const data = await postAction('ami_repondre.php', btn.dataset.userId, { action: 'refuser' });
            if (data.success) btn.closest('.annuaire-result-card').remove();
        });
    });
    document.querySelectorAll('.btn-annuler-demande').forEach(btn => {
        btn.addEventListener('click', async () => {
            const data = await postAction('ami_supprimer.php', btn.dataset.userId);
            if (data.success) btn.closest('.annuaire-result-card').remove();
        });
    });
});
</script>

<?php include 'footer.php'; ?>
