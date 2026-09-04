<?php
include_once 'language.php';
require_once 'db_connect.php';
require_once 'groupe_functions.php';

groupe_require_login();
$self_id = (int) $_SESSION['user_id'];
$self_role = $_SESSION['user_role'];
$can_create = in_array($self_role, ['enseignant', 'admin'], true);

$flash = null;

function groupe_unlink_photo($path) {
    if ($path && strpos($path, 'uploads/') === 0 && file_exists($path)) {
        unlink($path);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Créer un groupe (enseignant) ---
    if (isset($_POST['create_group']) && $can_create) {
        $nom = trim($_POST['nom'] ?? '');
        $type = ($_POST['type'] ?? 'classe') === 'enseignants' ? 'enseignants' : 'classe';
        $annee = trim($_POST['annee'] ?? '');
        $filiere_id = ctype_digit($_POST['filiere_id'] ?? '') ? (int) $_POST['filiere_id'] : null;
        $niveau = trim($_POST['niveau'] ?? '');

        if ($nom === '') {
            header('Location: mes_groupes.php?flash=' . urlencode('error|Le nom du groupe est obligatoire.'));
            exit;
        }

        $code = groupe_generate_code($mysqli);
        $stmt = $mysqli->prepare("INSERT INTO groupes_classe (nom, type, annee, filiere_id, niveau, code_unique, enseignant_id) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("sssissi", $nom, $type, $annee, $filiere_id, $niveau, $code, $self_id);
        $stmt->execute();
        $new_group_id = $mysqli->insert_id;
        $stmt->close();

        $stmt = $mysqli->prepare("INSERT INTO groupe_membres (groupe_id, user_id, role_in_group, last_read_at) VALUES (?, ?, 'enseignant', NOW())");
        $stmt->bind_param("ii", $new_group_id, $self_id);
        $stmt->execute();
        $stmt->close();

        header('Location: mes_groupes.php?flash=' . urlencode('success|Groupe créé. Code à communiquer aux étudiants : ' . $code));
        exit;
    }

    // --- Rejoindre un groupe via son code ---
    if (isset($_POST['join_group'])) {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        if ($code === '') {
            header('Location: mes_groupes.php?flash=' . urlencode('error|Merci de saisir un code.'));
            exit;
        }
        $group = $mysqli->query("SELECT id, nom FROM groupes_classe WHERE code_unique = '" . $mysqli->real_escape_string($code) . "'")->fetch_assoc();
        if (!$group) {
            header('Location: mes_groupes.php?flash=' . urlencode('error|Aucun groupe ne correspond à ce code.'));
            exit;
        }
        $already = $mysqli->query("SELECT 1 FROM groupe_membres WHERE groupe_id = " . (int) $group['id'] . " AND user_id = $self_id")->num_rows > 0;
        if ($already) {
            header('Location: mes_groupes.php?flash=' . urlencode('error|Vous êtes déjà membre de ce groupe.'));
            exit;
        }
        $role_in_group = $self_role === 'enseignant' ? 'enseignant' : 'etudiant';
        $stmt = $mysqli->prepare("INSERT INTO groupe_membres (groupe_id, user_id, role_in_group, last_read_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $group['id'], $self_id, $role_in_group);
        $stmt->execute();
        $stmt->close();
        header('Location: mes_groupes.php?flash=' . urlencode('success|Vous avez rejoint le groupe "' . $group['nom'] . '".'));
        exit;
    }

    // --- Quitter un groupe (membre non créateur) ---
    if (isset($_POST['leave_group_id'])) {
        $gid = (int) $_POST['leave_group_id'];
        $g = $mysqli->query("SELECT enseignant_id FROM groupes_classe WHERE id = $gid")->fetch_assoc();
        if ($g && (int) $g['enseignant_id'] === $self_id) {
            header('Location: mes_groupes.php?flash=' . urlencode('error|Le créateur du groupe ne peut pas le quitter, seulement le supprimer.'));
            exit;
        }
        $mysqli->query("DELETE FROM groupe_membres WHERE groupe_id = $gid AND user_id = $self_id");
        header('Location: mes_groupes.php?flash=' . urlencode('success|Vous avez quitté le groupe.'));
        exit;
    }

    // --- Supprimer entièrement un groupe (créateur uniquement) ---
    if (isset($_POST['delete_group_id'])) {
        $gid = (int) $_POST['delete_group_id'];
        $g = $mysqli->query("SELECT enseignant_id FROM groupes_classe WHERE id = $gid")->fetch_assoc();
        if (!$g || (int) $g['enseignant_id'] !== $self_id) {
            header('Location: mes_groupes.php?flash=' . urlencode('error|Action non autorisée.'));
            exit;
        }
        $att = $mysqli->query("SELECT file_path FROM groupe_message_attachments a JOIN groupe_messages m ON m.id = a.message_id WHERE m.groupe_id = $gid");
        while ($row = $att->fetch_assoc()) { groupe_unlink_photo($row['file_path']); }
        $mysqli->query("DELETE FROM groupes_classe WHERE id = $gid"); // cascade sur membres/messages/pièces jointes
        header('Location: mes_groupes.php?flash=' . urlencode('success|Groupe supprimé.'));
        exit;
    }
}

if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

// --- Recherche / tri (par nom de groupe, nom d'enseignant, ou année) parmi les groupes de
// l'utilisateur courant uniquement. ---
$q = trim($_GET['q'] ?? '');
$annee_filter = trim($_GET['annee'] ?? '');
$sort = $_GET['sort'] ?? 'recent';

$searchWhere = '';
$searchParams = [];
$searchTypes = '';
if ($q !== '') {
    $searchWhere .= " AND (g.nom LIKE ? OR ens.nom LIKE ?)";
    $like = "%$q%";
    $searchTypes .= 'ss';
    $searchParams[] = $like;
    $searchParams[] = $like;
}
if ($annee_filter !== '') {
    $searchWhere .= " AND g.annee = ?";
    $searchTypes .= 's';
    $searchParams[] = $annee_filter;
}
$orderBy = match ($sort) {
    'nom' => 'g.nom ASC',
    'annee' => 'g.annee DESC',
    'enseignant' => 'ens.nom ASC',
    default => 'g.created_at DESC',
};

$sql = "SELECT g.*, m.role_in_group, m.last_read_at, f.nom_fr AS filiere_nom, ens.nom AS enseignant_nom,
        (SELECT COUNT(*) FROM groupe_membres gm WHERE gm.groupe_id = g.id AND gm.is_banned = 0) AS membres_count
        FROM groupes_classe g
        JOIN groupe_membres m ON m.groupe_id = g.id AND m.user_id = $self_id AND m.is_banned = 0
        JOIN utilisateurs ens ON ens.id = g.enseignant_id
        LEFT JOIN filieres f ON f.id = g.filiere_id
        WHERE 1=1 $searchWhere
        ORDER BY $orderBy";
if ($searchParams) {
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($searchTypes, ...$searchParams);
    $stmt->execute();
    $my_groups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $my_groups = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
}
foreach ($my_groups as &$g) {
    $g['unread'] = groupe_unread_count($mysqli, $g['id'], $self_id, $g['last_read_at']);
}
unset($g);

$annees_disponibles = $mysqli->query("SELECT DISTINCT g.annee FROM groupes_classe g
                                       JOIN groupe_membres m ON m.groupe_id = g.id AND m.user_id = $self_id AND m.is_banned = 0
                                       WHERE g.annee IS NOT NULL AND g.annee != ''
                                       ORDER BY g.annee DESC")->fetch_all(MYSQLI_ASSOC);

$filieres_list = $mysqli->query("SELECT id, nom_fr FROM filieres ORDER BY display_order ASC, nom_fr ASC")->fetch_all(MYSQLI_ASSOC);

$page_title = t('groupe_mes_groupes_titre');
include 'header.php';
?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('groupe_mes_groupes_titre'); ?></span>
        </nav>
        <h1><?php echo t('groupe_mes_groupes_titre'); ?></h1>
        <p><?php echo t('groupe_mes_groupes_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <?php if ($flash): ?>
            <div class="admin-flash admin-flash-<?php echo htmlspecialchars($flash['type']); ?>">
                <i class="fas <?php echo $flash['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'; ?>"></i>
                <?php echo htmlspecialchars($flash['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="groupe-actions-row">
            <?php if ($can_create): ?>
                <div class="groupe-action-card">
                    <h3><i class="fas fa-plus"></i> <?php echo t('groupe_creer_titre'); ?></h3>
                    <form action="mes_groupes.php" method="POST" class="groupe-inline-form" id="groupe-create-form">
                        <div class="groupe-type-toggle">
                            <label class="preinscription-radio"><input type="radio" name="type" value="classe" checked> <?php echo t('groupe_type_classe'); ?></label>
                            <label class="preinscription-radio"><input type="radio" name="type" value="enseignants"> <?php echo t('groupe_type_enseignants'); ?></label>
                        </div>
                        <input type="text" name="nom" placeholder="<?php echo t('groupe_nom_placeholder'); ?>" required>
                        <input type="text" name="annee" placeholder="<?php echo t('groupe_annee_placeholder'); ?>">
                        <select name="filiere_id" id="groupe-create-filiere">
                            <option value=""><?php echo t('groupe_filiere_toutes'); ?></option>
                            <?php foreach ($filieres_list as $f): ?>
                                <option value="<?php echo (int) $f['id']; ?>"><?php echo htmlspecialchars($f['nom_fr']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="niveau" id="groupe-create-niveau" placeholder="<?php echo t('groupe_niveau_placeholder'); ?>">
                        <button type="submit" name="create_group" class="btn-add-item"><i class="fas fa-users"></i> <?php echo t('groupe_creer_bouton'); ?></button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="groupe-action-card">
                <h3><i class="fas fa-key"></i> <?php echo t('groupe_rejoindre_titre'); ?></h3>
                <form action="mes_groupes.php" method="POST" class="groupe-inline-form">
                    <input type="text" name="code" placeholder="<?php echo t('groupe_code_placeholder'); ?>" maxlength="10" style="text-transform:uppercase;" required>
                    <button type="submit" name="join_group" class="btn-add-item"><i class="fas fa-right-to-bracket"></i> <?php echo t('groupe_rejoindre_bouton'); ?></button>
                </form>
            </div>
        </div>

        <form action="mes_groupes.php" method="GET" class="etu-search-form groupe-search-form">
            <div class="etu-search-input-wrap">
                <i class="fas fa-magnifying-glass"></i>
                <input type="search" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="<?php echo t('groupe_recherche_placeholder'); ?>">
            </div>
            <select name="annee">
                <option value=""><?php echo t('admin_etudiants_toutes_annees'); ?></option>
                <?php foreach ($annees_disponibles as $a): ?>
                    <option value="<?php echo htmlspecialchars($a['annee']); ?>" <?php echo $annee_filter === $a['annee'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($a['annee']); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="sort">
                <option value="recent" <?php echo $sort === 'recent' ? 'selected' : ''; ?>><?php echo t('groupe_tri_recent'); ?></option>
                <option value="nom" <?php echo $sort === 'nom' ? 'selected' : ''; ?>><?php echo t('groupe_tri_nom'); ?></option>
                <option value="annee" <?php echo $sort === 'annee' ? 'selected' : ''; ?>><?php echo t('groupe_tri_annee'); ?></option>
                <option value="enseignant" <?php echo $sort === 'enseignant' ? 'selected' : ''; ?>><?php echo t('groupe_tri_enseignant'); ?></option>
            </select>
            <button type="submit" class="btn-outline"><i class="fas fa-filter"></i> <?php echo t('admin_etudiants_filtrer'); ?></button>
            <?php if ($q !== '' || $annee_filter !== '' || $sort !== 'recent'): ?>
                <a href="mes_groupes.php" class="btn-outline"><i class="fas fa-xmark"></i> <?php echo t('admin_etudiants_reinitialiser_filtre'); ?></a>
            <?php endif; ?>
        </form>

        <?php if (empty($my_groups)): ?>
            <p class="gallery-empty"><i class="fas fa-people-group"></i> <?php echo t('groupe_aucun'); ?></p>
        <?php else: ?>
            <div class="groupe-list">
                <?php foreach ($my_groups as $g): ?>
                    <div class="groupe-card">
                        <?php if ($g['unread'] > 0): ?><span class="groupe-unread-badge"><?php echo $g['unread'] > 99 ? '99+' : $g['unread']; ?></span><?php endif; ?>
                        <a href="groupe_chat.php?id=<?php echo (int) $g['id']; ?>" class="groupe-card-link">
                            <div class="groupe-card-icon"><i class="fas fa-people-group"></i></div>
                            <?php if ($g['type'] === 'enseignants'): ?><span class="groupe-type-badge"><i class="fas fa-chalkboard-user"></i> <?php echo t('groupe_type_enseignants'); ?></span><?php endif; ?>
                            <h4><?php echo htmlspecialchars($g['nom']); ?></h4>
                            <div class="groupe-card-meta">
                                <span><i class="fas fa-chalkboard-user"></i> <?php echo htmlspecialchars($g['enseignant_nom']); ?></span>
                            </div>
                            <div class="groupe-card-meta">
                                <?php if (!empty($g['filiere_nom'])): ?><span><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($g['filiere_nom']); ?></span><?php endif; ?>
                                <?php if (!empty($g['niveau'])): ?><span><?php echo htmlspecialchars($g['niveau']); ?></span><?php endif; ?>
                                <?php if (!empty($g['annee'])): ?><span><?php echo htmlspecialchars($g['annee']); ?></span><?php endif; ?>
                            </div>
                            <div class="groupe-card-meta">
                                <span><i class="fas fa-user-group"></i> <?php echo (int) $g['membres_count']; ?></span>
                                <?php if ($g['role_in_group'] === 'enseignant' && (int) $g['enseignant_id'] === $self_id): ?>
                                    <span class="groupe-card-code"><i class="fas fa-key"></i> <?php echo htmlspecialchars($g['code_unique']); ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                        <div class="groupe-card-actions">
                            <?php if ((int) $g['enseignant_id'] === $self_id): ?>
                                <form action="mes_groupes.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('groupe_confirm_suppr')); ?>">
                                    <button type="submit" name="delete_group_id" value="<?php echo (int) $g['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            <?php else: ?>
                                <form action="mes_groupes.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('groupe_confirm_quitter')); ?>">
                                    <button type="submit" name="leave_group_id" value="<?php echo (int) $g['id']; ?>" class="btn-outline" title="<?php echo t('groupe_quitter'); ?>"><i class="fas fa-right-from-bracket"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('groupe-create-form');
    if (!form) return;
    const filiereField = document.getElementById('groupe-create-filiere');
    const niveauField = document.getElementById('groupe-create-niveau');
    function syncType() {
        const isEnseignants = form.querySelector('input[name="type"]:checked').value === 'enseignants';
        filiereField.hidden = isEnseignants;
        niveauField.hidden = isEnseignants;
    }
    form.querySelectorAll('input[name="type"]').forEach(function (radio) {
        radio.addEventListener('change', syncType);
    });
    syncType();
});
</script>

<?php include 'footer.php'; ?>
