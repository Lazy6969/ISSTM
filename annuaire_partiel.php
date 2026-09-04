<?php
// Partiel partagé par annuaire.php (page autonome, conservée pour compatibilité) et
// mes_amis.php (onglet "Recherche" du hub réseau). Suppose que $mysqli, $self_id sont déjà
// définis par l'includeur, ainsi que $search_action_url (URL du formulaire de recherche).

$q = trim($_GET['q'] ?? '');
$results = [];
if ($q !== '') {
    $stmt = $mysqli->prepare("SELECT id, nom, avatar_path, role FROM utilisateurs WHERE nom LIKE ? AND id != ? ORDER BY nom ASC LIMIT 40");
    $like = "%$q%";
    $stmt->bind_param('si', $like, $self_id);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($results as &$r) {
        $r['filiere'] = $r['role'] === 'etudiant' ? amis_get_filiere($mysqli, $r['id']) : null;
        $r['statut'] = amis_get_statut($mysqli, $self_id, $r['id']);
    }
    unset($r);
}

$role_labels = [
    'admin' => t('admin_utilisateurs_role_admin'),
    'enseignant' => t('admin_utilisateurs_role_enseignant'),
    'etudiant' => t('admin_utilisateurs_role_etudiant'),
    'bibliotheque' => t('admin_utilisateurs_role_bibliotheque'),
];
?>

<form action="<?php echo htmlspecialchars($search_action_url ?? 'annuaire.php'); ?>" method="GET" class="etu-search-form">
    <?php if (!empty($search_extra_params)): foreach ($search_extra_params as $pk => $pv): ?>
        <input type="hidden" name="<?php echo htmlspecialchars($pk); ?>" value="<?php echo htmlspecialchars($pv); ?>">
    <?php endforeach; endif; ?>
    <div class="etu-search-input-wrap">
        <i class="fas fa-magnifying-glass"></i>
        <input type="search" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="<?php echo t('annuaire_recherche_placeholder'); ?>">
    </div>
    <button type="submit" class="btn-outline"><i class="fas fa-filter"></i> <?php echo t('admin_etudiants_filtrer'); ?></button>
</form>

<?php if ($q === ''): ?>
    <p class="gallery-empty"><i class="fas fa-users"></i> <?php echo t('annuaire_saisir_recherche'); ?></p>
<?php elseif (empty($results)): ?>
    <p class="gallery-empty"><i class="fas fa-user-slash"></i> <?php echo t('annuaire_aucun_resultat'); ?></p>
<?php else: ?>
    <div class="annuaire-results">
        <?php foreach ($results as $r): ?>
            <a href="profil_public.php?id=<?php echo (int) $r['id']; ?>" class="annuaire-result-card">
                <div class="annuaire-result-avatar">
                    <?php if (!empty($r['avatar_path'])): ?>
                        <img src="<?php echo htmlspecialchars($r['avatar_path']); ?>" alt="">
                    <?php else: ?>
                        <i class="fas fa-circle-user"></i>
                    <?php endif; ?>
                </div>
                <div class="annuaire-result-info">
                    <strong><?php echo htmlspecialchars($r['nom']); ?></strong>
                    <span><?php echo htmlspecialchars($role_labels[$r['role']] ?? $r['role']); ?><?php if (!empty($r['filiere'])): ?> · <?php echo htmlspecialchars($r['filiere']['filiere']); ?><?php endif; ?></span>
                </div>
                <?php if ($r['statut'] === 'amis'): ?>
                    <span class="annuaire-result-badge is-amis"><i class="fas fa-check"></i> <?php echo t('amis_statut_amis'); ?></span>
                <?php elseif ($r['statut'] === 'en_attente_envoyee'): ?>
                    <span class="annuaire-result-badge"><i class="fas fa-clock"></i> <?php echo t('amis_demande_envoyee'); ?></span>
                <?php elseif ($r['statut'] === 'en_attente_recue'): ?>
                    <span class="annuaire-result-badge is-recue"><i class="fas fa-user-clock"></i> <?php echo t('amis_demande_recue'); ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$suggestions = amis_get_suggestions($mysqli, $self_id, 8);
if (!empty($suggestions)):
?>
    <div class="annuaire-suggestions">
        <h3><i class="fas fa-user-plus"></i> <?php echo t('amis_suggestions_titre'); ?></h3>
        <div class="annuaire-results">
            <?php foreach ($suggestions as $s): ?>
                <a href="profil_public.php?id=<?php echo (int) $s['id']; ?>" class="annuaire-result-card">
                    <div class="annuaire-result-avatar">
                        <?php if (!empty($s['avatar_path'])): ?>
                            <img src="<?php echo htmlspecialchars($s['avatar_path']); ?>" alt="">
                        <?php else: ?>
                            <i class="fas fa-circle-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="annuaire-result-info">
                        <strong><?php echo htmlspecialchars($s['nom']); ?></strong>
                        <span><?php echo htmlspecialchars($role_labels[$s['role']] ?? $s['role']); ?><?php if (!empty($s['filiere'])): ?> · <?php echo htmlspecialchars($s['filiere']['filiere']); ?><?php endif; ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php elseif ($q === ''): ?>
    <p class="gallery-empty"><i class="fas fa-user-plus"></i> <?php echo t('amis_suggestions_vide'); ?></p>
<?php endif; ?>
