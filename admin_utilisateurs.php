<?php
include_once 'language.php';
require_once 'db_connect.php';

$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';
if (!$is_admin) {
    header('Location: index.php');
    exit;
}

function etu_unlink_photo_generic($path) {
    if ($path && strpos($path, 'uploads/') === 0 && file_exists($path)) {
        unlink($path);
    }
}

function admu_generate_password($length = 10) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    $pwd = '';
    for ($i = 0; $i < $length; $i++) {
        $pwd .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pwd;
}

$flash = null;
$valid_roles = ['admin', 'user', 'etudiant', 'enseignant', 'bibliotheque'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Créer rapidement un compte enseignant : identifiant et mot de passe générés
    // automatiquement, à communiquer à l'enseignant pour qu'il se connecte. ---
    if (isset($_POST['create_teacher'])) {
        $nom = trim($_POST['teacher_nom'] ?? '');
        if ($nom === '') {
            header('Location: admin_utilisateurs.php?flash=' . urlencode('error|Le nom de l\'enseignant est obligatoire.'));
            exit;
        }
        $password = admu_generate_password(10);
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $placeholder_email = 'TEACHER_ISSTM_TMP_' . uniqid();
        $stmt = $mysqli->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, dernier_mdp_genere, role) VALUES (?, ?, ?, ?, 'enseignant')");
        $stmt->bind_param("ssss", $nom, $placeholder_email, $hashed, $password);
        $stmt->execute();
        $new_id = $mysqli->insert_id;
        $stmt->close();

        $identifiant = 'TEACHER_ISSTM_' . $new_id;
        $stmt = $mysqli->prepare("UPDATE utilisateurs SET email = ? WHERE id = ?");
        $stmt->bind_param("si", $identifiant, $new_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['admu_new_account'] = ['identifiant' => $identifiant, 'password' => $password, 'nom' => $nom];
        header('Location: admin_utilisateurs.php?flash=' . urlencode('success|Compte enseignant créé.'));
        exit;
    }

    // --- Créer / mettre à jour un compte ---
    if (isset($_POST['save_user'])) {
        $uid = (int) ($_POST['uid'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = in_array($_POST['role'] ?? '', $valid_roles, true) ? $_POST['role'] : 'user';
        $is_messagerie = isset($_POST['is_messagerie']) ? 1 : 0;
        $is_scolarite = isset($_POST['is_scolarite']) ? 1 : 0;
        $is_bibliotheque = isset($_POST['is_bibliotheque']) ? 1 : 0;
        $telephone = trim($_POST['telephone'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');

        if ($nom === '' || $email === '') {
            header('Location: admin_utilisateurs.php?view=edit' . ($uid ? "&id=$uid" : '') . '&flash=' . urlencode('error|Le nom et l\'identifiant sont obligatoires.'));
            exit;
        }

        $check = $mysqli->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $uid);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            header('Location: admin_utilisateurs.php?view=edit' . ($uid ? "&id=$uid" : '') . '&flash=' . urlencode('error|Cet identifiant est déjà utilisé par un autre compte.'));
            exit;
        }
        $check->close();

        if ($uid > 0) {
            if ($new_password !== '') {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare("UPDATE utilisateurs SET nom=?, email=?, role=?, is_messagerie=?, is_scolarite=?, is_bibliotheque=?, telephone=?, mot_de_passe=? WHERE id=?");
                $stmt->bind_param("sssiiissi", $nom, $email, $role, $is_messagerie, $is_scolarite, $is_bibliotheque, $telephone, $hashed, $uid);
            } else {
                $stmt = $mysqli->prepare("UPDATE utilisateurs SET nom=?, email=?, role=?, is_messagerie=?, is_scolarite=?, is_bibliotheque=?, telephone=? WHERE id=?");
                $stmt->bind_param("sssiiisi", $nom, $email, $role, $is_messagerie, $is_scolarite, $is_bibliotheque, $telephone, $uid);
            }
            $stmt->execute();
            $stmt->close();
            header('Location: admin_utilisateurs.php?flash=' . urlencode('success|Compte mis à jour.'));
            exit;
        } else {
            if ($new_password === '') {
                header('Location: admin_utilisateurs.php?view=edit&flash=' . urlencode('error|Un mot de passe est obligatoire pour un nouveau compte.'));
                exit;
            }
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role, is_messagerie, is_scolarite, is_bibliotheque, telephone) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssiiis", $nom, $email, $hashed, $role, $is_messagerie, $is_scolarite, $is_bibliotheque, $telephone);
            $stmt->execute();
            $stmt->close();
            header('Location: admin_utilisateurs.php?flash=' . urlencode('success|Compte créé.'));
            exit;
        }
    }

    // --- Supprimer un compte ---
    if (isset($_POST['delete_user_id'])) {
        $uid = (int) $_POST['delete_user_id'];
        if ($uid === (int) $_SESSION['user_id']) {
            header('Location: admin_utilisateurs.php?flash=' . urlencode('error|Vous ne pouvez pas supprimer votre propre compte.'));
            exit;
        }
        $target = $mysqli->query("SELECT role FROM utilisateurs WHERE id = $uid")->fetch_assoc();
        if ($target && $target['role'] === 'admin') {
            $admin_count = (int) $mysqli->query("SELECT COUNT(*) c FROM utilisateurs WHERE role='admin'")->fetch_assoc()['c'];
            if ($admin_count <= 1) {
                header('Location: admin_utilisateurs.php?flash=' . urlencode('error|Impossible de supprimer le dernier compte administrateur.'));
                exit;
            }
        }
        // Un compte lié à une fiche de pré-inscription approuvée est nettoyé des deux côtés,
        // comme depuis admin_etudiants.php, pour ne pas laisser de fiche orpheline.
        $pre = $mysqli->query("SELECT id, photo_path FROM preinscriptions WHERE user_id = $uid")->fetch_assoc();
        if ($pre) {
            etu_unlink_photo_generic($pre['photo_path']);
            $mysqli->query("DELETE FROM preinscriptions WHERE id = " . (int) $pre['id']);
        }
        $old = $mysqli->query("SELECT avatar_path FROM utilisateurs WHERE id = $uid")->fetch_assoc();
        if ($old) etu_unlink_photo_generic($old['avatar_path']);
        $mysqli->query("DELETE FROM utilisateurs WHERE id = $uid");
        header('Location: admin_utilisateurs.php?flash=' . urlencode('success|Compte supprimé.'));
        exit;
    }

    // --- Générer un nouveau mot de passe (circuit "contact/présentiel" du mot de passe oublié) ---
    if (isset($_POST['regenerate_password_id'])) {
        $uid = (int) $_POST['regenerate_password_id'];
        $new_password = admu_generate_password(10);
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("UPDATE utilisateurs SET mot_de_passe = ?, dernier_mdp_genere = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $stmt->bind_param('ssi', $hashed, $new_password, $uid);
        $stmt->execute();
        $stmt->close();
        header('Location: admin_utilisateurs.php?flash=' . urlencode('success|' . t('admin_utilisateurs_mdp_regenere')));
        exit;
    }
}

if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

$new_account = $_SESSION['admu_new_account'] ?? null;
unset($_SESSION['admu_new_account']);

$view = $_GET['view'] ?? 'list';
$q = trim($_GET['q'] ?? '');

$page_title = t('admin_utilisateurs_titre');
include 'header.php';
?>

<a href="administrateur.php" class="btn-outline btn-back-sticky"><i class="fas fa-arrow-left"></i> <?php echo t('admin_retour_tableau_bord'); ?></a>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('admin_utilisateurs_titre'); ?></span>
        </nav>
        <h1><?php echo t('admin_utilisateurs_titre'); ?></h1>
        <p><?php echo t('admin_utilisateurs_soustitre'); ?></p>
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

        <?php if ($new_account): ?>
            <div class="etu-credentials-banner">
                <i class="fas fa-key"></i>
                <div>
                    <h4><?php echo t('admin_utilisateurs_compte_enseignant_cree'); ?></h4>
                    <p><?php echo t('admin_etudiants_communiquer'); ?> <strong><?php echo htmlspecialchars($new_account['nom']); ?></strong></p>
                    <div class="etu-credentials-values">
                        <span><?php echo t('admin_etudiants_identifiant'); ?>: <strong><?php echo htmlspecialchars($new_account['identifiant']); ?></strong></span>
                        <span><?php echo t('admin_etudiants_mot_de_passe'); ?>: <strong><?php echo htmlspecialchars($new_account['password']); ?></strong></span>
                    </div>
                    <p class="etu-credentials-warning"><i class="fas fa-circle-info"></i> <?php echo t('admin_etudiants_archive_info'); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($view === 'edit'):
            $uid = (int) ($_GET['id'] ?? 0);
            $u = $uid ? $mysqli->query("SELECT * FROM utilisateurs WHERE id = $uid")->fetch_assoc() : null;
        ?>
            <a href="admin_utilisateurs.php" class="admin-galerie-back"><i class="fas fa-chevron-left"></i> <?php echo t('admin_utilisateurs_retour_liste'); ?></a>
            <h2 class="admin-section-title"><?php echo $u ? htmlspecialchars($u['nom']) : t('admin_utilisateurs_nouveau'); ?></h2>

            <?php if ($u && !empty($u['dernier_mdp_genere'])): ?>
                <div class="etu-credentials-values etu-credentials-inline">
                    <span><?php echo t('admin_etudiants_identifiant'); ?>: <strong><?php echo htmlspecialchars($u['email']); ?></strong></span>
                    <span><?php echo t('admin_etudiants_mot_de_passe'); ?>: <strong><?php echo htmlspecialchars($u['dernier_mdp_genere']); ?></strong></span>
                </div>
            <?php endif; ?>

            <form action="admin_utilisateurs.php" method="POST" class="admin-form">
                <?php if ($u): ?><input type="hidden" name="uid" value="<?php echo (int) $u['id']; ?>"><?php endif; ?>
                <div class="form-group">
                    <label><?php echo t('nom'); ?></label>
                    <input type="text" name="nom" value="<?php echo htmlspecialchars($u['nom'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label><?php echo t('admin_utilisateurs_identifiant_label'); ?></label>
                    <input type="text" name="email" value="<?php echo htmlspecialchars($u['email'] ?? ''); ?>" required>
                    <small class="preinscription-hint"><?php echo t('admin_utilisateurs_identifiant_aide'); ?></small>
                </div>
                <div class="form-group">
                    <label><?php echo t('admin_utilisateurs_role_label'); ?></label>
                    <select name="role">
                        <?php // "Utilisateur" n'est plus proposé pour un nouveau choix ; conservé uniquement en
                        // secours pour ne pas changer silencieusement le rôle d'un compte existant qui l'a encore. ?>
                        <?php if (($u['role'] ?? '') === 'user'): ?>
                            <option value="user" selected><?php echo t('admin_utilisateurs_role_user'); ?></option>
                        <?php endif; ?>
                        <option value="etudiant" <?php echo (($u['role'] ?? 'etudiant') === 'etudiant') ? 'selected' : ''; ?>><?php echo t('admin_utilisateurs_role_etudiant'); ?></option>
                        <option value="enseignant" <?php echo (($u['role'] ?? '') === 'enseignant') ? 'selected' : ''; ?>><?php echo t('admin_utilisateurs_role_enseignant'); ?></option>
                        <option value="bibliotheque" <?php echo (($u['role'] ?? '') === 'bibliotheque') ? 'selected' : ''; ?>><?php echo t('admin_utilisateurs_role_bibliotheque'); ?></option>
                        <option value="admin" <?php echo (($u['role'] ?? '') === 'admin') ? 'selected' : ''; ?>><?php echo t('admin_utilisateurs_role_admin'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo t('admin_utilisateurs_telephone_label'); ?></label>
                    <input type="text" name="telephone" value="<?php echo htmlspecialchars($u['telephone'] ?? ''); ?>">
                </div>
                <div class="form-group etu-checkbox-group">
                    <label class="etu-checkbox"><input type="checkbox" name="is_bibliotheque" <?php echo !empty($u['is_bibliotheque']) ? 'checked' : ''; ?>> <?php echo t('admin_utilisateurs_is_bibliotheque'); ?></label>
                    <label class="etu-checkbox"><input type="checkbox" name="is_messagerie" <?php echo !empty($u['is_messagerie']) ? 'checked' : ''; ?>> <?php echo t('admin_utilisateurs_is_messagerie'); ?></label>
                    <label class="etu-checkbox"><input type="checkbox" name="is_scolarite" <?php echo !empty($u['is_scolarite']) ? 'checked' : ''; ?>> <?php echo t('admin_utilisateurs_is_scolarite'); ?></label>
                </div>
                <div class="form-group">
                    <label><?php echo $u ? t('admin_etudiants_nouveau_mdp_label') : t('admin_utilisateurs_mdp_label'); ?></label>
                    <input type="text" name="new_password" placeholder="<?php echo $u ? t('admin_etudiants_nouveau_mdp_placeholder') : ''; ?>">
                </div>
                <button type="submit" name="save_user" class="btn-submit"><i class="fas fa-save"></i> <?php echo $u ? t('admin_enregistrer_modifications') : t('admin_utilisateurs_creer'); ?></button>
            </form>

        <?php else:
            $where = '';
            $params = [];
            $types = '';
            if ($q !== '') {
                $where = 'WHERE u.nom LIKE ? OR u.email LIKE ?';
                $like = "%$q%";
                $types = 'ss';
                $params = [$like, $like];
            }
            $sql = "SELECT u.*, p.filiere_id, p.niveau, f.nom_fr AS filiere_nom
                    FROM utilisateurs u
                    LEFT JOIN preinscriptions p ON p.user_id = u.id AND p.status = 'approuve'
                    LEFT JOIN filieres f ON f.id = p.filiere_id
                    $where ORDER BY u.date_creation DESC";
            if ($params) {
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $all_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            } else {
                $all_users = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
            }
        ?>
            <div class="admin-galerie-stats">
                <div class="admin-stat-card"><i class="fas fa-users"></i><div><strong><?php echo count($all_users); ?></strong><span><?php echo t('admin_utilisateurs_total'); ?></span></div></div>
            </div>

            <div class="admin-galerie-toolbar etu-tabs">
                <a href="admin_utilisateurs.php" class="btn-outline <?php echo $view !== 'stats' ? 'is-active' : ''; ?>"><i class="fas fa-list"></i> <?php echo t('admin_utilisateurs_liste'); ?></a>
                <a href="admin_utilisateurs.php?view=stats" class="btn-outline <?php echo $view === 'stats' ? 'is-active' : ''; ?>"><i class="fas fa-chart-pie"></i> <?php echo t('admin_etudiants_statistiques'); ?></a>
            </div>

            <?php if ($view === 'stats'):
                $role_counts = ['admin' => 0, 'enseignant' => 0, 'etudiant' => 0, 'scolarite' => 0, 'messagerie' => 0, 'bibliotheque' => 0, 'autre' => 0];
                foreach ($all_users as $u) {
                    if ($u['role'] === 'admin') { $role_counts['admin']++; }
                    elseif ($u['role'] === 'enseignant') { $role_counts['enseignant']++; }
                    elseif ($u['role'] === 'etudiant') { $role_counts['etudiant']++; }
                    elseif ($u['role'] === 'bibliotheque' || !empty($u['is_bibliotheque'])) { $role_counts['bibliotheque']++; }
                    elseif (!empty($u['is_scolarite'])) { $role_counts['scolarite']++; }
                    elseif (!empty($u['is_messagerie'])) { $role_counts['messagerie']++; }
                    else { $role_counts['autre']++; }
                }
                $role_labels = [
                    'admin' => t('admin_utilisateurs_role_admin'),
                    'enseignant' => t('admin_utilisateurs_role_enseignant'),
                    'etudiant' => t('admin_utilisateurs_role_etudiant'),
                    'scolarite' => t('admin_utilisateurs_is_scolarite'),
                    'messagerie' => t('admin_utilisateurs_is_messagerie'),
                    'bibliotheque' => t('admin_utilisateurs_role_bibliotheque'),
                    'autre' => t('admin_utilisateurs_role_user'),
                ];
                $etu_palette = ['#003366', '#8e44ad', '#2ecc71', '#d4a017', '#2980b9', '#7f8c8d'];
                $total_users = count($all_users);
                $cumulative = 0;
                $conic_stops = [];
                $i = 0;
                foreach ($role_counts as $key => $cnt) {
                    if ($cnt === 0) { $i++; continue; }
                    $start = $total_users > 0 ? round($cumulative / $total_users * 360, 2) : 0;
                    $cumulative += $cnt;
                    $end = $total_users > 0 ? round($cumulative / $total_users * 360, 2) : 0;
                    $conic_stops[] = $etu_palette[$i % count($etu_palette)] . " {$start}deg {$end}deg";
                    $i++;
                }
                $conic_gradient = !empty($conic_stops) ? 'conic-gradient(' . implode(', ', $conic_stops) . ')' : 'conic-gradient(var(--border-color) 0deg 360deg)';

                // Comptes créés par année, répartis en 3 catégories : étudiants, enseignants,
                // administration (regroupe admin, scolarité, messagerie et comptes classiques).
                $par_annee_role = $mysqli->query("SELECT YEAR(date_creation) AS annee, role, COUNT(*) AS total FROM utilisateurs GROUP BY YEAR(date_creation), role ORDER BY annee ASC")->fetch_all(MYSQLI_ASSOC);
                $categorie_labels = [
                    'etudiant' => t('admin_utilisateurs_role_etudiant'),
                    'enseignant' => t('admin_utilisateurs_role_enseignant'),
                    'autre' => t('admin_utilisateurs_stats_autre'),
                ];
                $categorie_couleurs = ['etudiant' => '#2ecc71', 'enseignant' => '#8e44ad', 'autre' => '#003366'];
                $par_annee = [];
                foreach ($par_annee_role as $row) {
                    $an = (int) $row['annee'];
                    $cat = in_array($row['role'], ['etudiant', 'enseignant'], true) ? $row['role'] : 'autre';
                    if (!isset($par_annee[$an])) $par_annee[$an] = ['etudiant' => 0, 'enseignant' => 0, 'autre' => 0];
                    $par_annee[$an][$cat] += (int) $row['total'];
                }
                ksort($par_annee);
                $max_annee_total = 1;
                foreach ($par_annee as $cats) { $max_annee_total = max($max_annee_total, array_sum($cats)); }

                // Étudiants par filière / niveau / parcours (Licence L1-L3 / Master M1-M2), à partir
                // des comptes déjà chargés dans $all_users (jointure preinscriptions approuvées).
                $admu_par_filiere = [];
                $admu_par_niveau = [];
                $admu_par_parcours = ['Licence' => 0, 'Master' => 0, 'Autre' => 0];
                foreach ($all_users as $u) {
                    if ($u['role'] !== 'etudiant' || empty($u['filiere_nom'])) continue;
                    $admu_par_filiere[$u['filiere_nom']] = ($admu_par_filiere[$u['filiere_nom']] ?? 0) + 1;
                    $niv = trim((string) ($u['niveau'] ?? ''));
                    if ($niv === '') continue;
                    $admu_par_niveau[$niv] = ($admu_par_niveau[$niv] ?? 0) + 1;
                    $niv_up = strtoupper($niv);
                    if ($niv_up[0] === 'L') { $admu_par_parcours['Licence']++; }
                    elseif ($niv_up[0] === 'M') { $admu_par_parcours['Master']++; }
                    else { $admu_par_parcours['Autre']++; }
                }
                arsort($admu_par_filiere);
                ksort($admu_par_niveau);
                $admu_max_filiere = max(1, ...array_values($admu_par_filiere ?: [0]));
                $admu_max_niveau = max(1, ...array_values($admu_par_niveau ?: [0]));
                $admu_par_parcours = array_filter($admu_par_parcours);
                $admu_total_parcours = array_sum($admu_par_parcours);
                $admu_parcours_couleurs = ['Licence' => '#2ecc71', 'Master' => '#8e44ad', 'Autre' => '#7f8c8d'];
                $admu_cumulative_pc = 0;
                $admu_conic_stops = [];
                foreach ($admu_par_parcours as $cyc => $cnt) {
                    $start = $admu_total_parcours > 0 ? round($admu_cumulative_pc / $admu_total_parcours * 360, 2) : 0;
                    $admu_cumulative_pc += $cnt;
                    $end = $admu_total_parcours > 0 ? round($admu_cumulative_pc / $admu_total_parcours * 360, 2) : 0;
                    $admu_conic_stops[] = $admu_parcours_couleurs[$cyc] . " {$start}deg {$end}deg";
                }
                $admu_conic_gradient = !empty($admu_conic_stops) ? 'conic-gradient(' . implode(', ', $admu_conic_stops) . ')' : 'conic-gradient(var(--border-color) 0deg 360deg)';
            ?>
                <div class="etu-stats-grid">
                    <div class="etu-stats-card">
                        <h3><i class="fas fa-chart-pie"></i> <?php echo t('admin_utilisateurs_stats_repartition'); ?></h3>
                        <?php if ($total_users === 0): ?>
                            <p class="gallery-empty"><i class="fas fa-chart-pie"></i> <?php echo t('admin_etudiants_aucune_donnee'); ?></p>
                        <?php else: ?>
                            <div class="etu-donut-wrap">
                                <div class="etu-donut" style="background: <?php echo $conic_gradient; ?>;">
                                    <div class="etu-donut-hole"><strong><?php echo $total_users; ?></strong><span><?php echo t('admin_utilisateurs_total'); ?></span></div>
                                </div>
                                <ul class="etu-legend">
                                    <?php $i = 0; foreach ($role_counts as $key => $cnt):
                                        if ($cnt === 0) { $i++; continue; }
                                        $pct = $total_users > 0 ? round($cnt / $total_users * 100) : 0;
                                    ?>
                                        <li><span class="etu-legend-dot" style="background: <?php echo $etu_palette[$i % count($etu_palette)]; ?>;"></span> <?php echo htmlspecialchars($role_labels[$key]); ?> — <strong><?php echo $cnt; ?></strong> (<?php echo $pct; ?>%)</li>
                                    <?php $i++; endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="etu-stats-card etu-stats-card-wide">
                        <h3><i class="fas fa-chart-column"></i> <?php echo t('admin_utilisateurs_stats_par_annee'); ?></h3>
                        <?php if (empty($par_annee)): ?>
                            <p class="gallery-empty"><i class="fas fa-chart-column"></i> <?php echo t('admin_etudiants_aucune_donnee'); ?></p>
                        <?php else: ?>
                            <div class="etu-barchart">
                                <?php foreach ($par_annee as $annee => $cats):
                                    $annee_total = array_sum($cats);
                                    $group_height = round($annee_total / $max_annee_total * 100);
                                ?>
                                    <div class="etu-barchart-group">
                                        <div class="etu-barchart-stack" style="height: <?php echo max($group_height, 4); ?>%;">
                                            <?php foreach ($cats as $cat => $cnt):
                                                if ($cnt === 0) continue;
                                                $seg_height = round($cnt / $annee_total * 100);
                                            ?>
                                                <div class="etu-barchart-segment" style="height: <?php echo $seg_height; ?>%; background: <?php echo $categorie_couleurs[$cat]; ?>;" title="<?php echo htmlspecialchars($categorie_labels[$cat]) . ' ' . $annee . ' : ' . $cnt; ?>"></div>
                                            <?php endforeach; ?>
                                        </div>
                                        <span class="etu-barchart-label"><?php echo $annee; ?> (<?php echo $annee_total; ?>)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <ul class="etu-legend etu-legend-horizontal">
                                <?php foreach ($categorie_labels as $cat => $label): ?>
                                    <li><span class="etu-legend-dot" style="background: <?php echo $categorie_couleurs[$cat]; ?>;"></span> <?php echo htmlspecialchars($label); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="etu-stats-card etu-stats-card-wide">
                        <h3><i class="fas fa-chart-column"></i> <?php echo t('admin_utilisateurs_stats_par_filiere'); ?></h3>
                        <?php if (empty($admu_par_filiere)): ?>
                            <p class="gallery-empty"><i class="fas fa-chart-column"></i> <?php echo t('admin_etudiants_aucune_donnee'); ?></p>
                        <?php else: ?>
                            <div class="etu-barchart">
                                <?php $i = 0; foreach ($admu_par_filiere as $filiere_nom => $cnt):
                                    $group_height = round($cnt / $admu_max_filiere * 100);
                                ?>
                                    <div class="etu-barchart-group">
                                        <div class="etu-barchart-stack" style="height: <?php echo max($group_height, 4); ?>%;">
                                            <div class="etu-barchart-segment" style="height: 100%; background: <?php echo $etu_palette[$i % count($etu_palette)]; ?>;" title="<?php echo htmlspecialchars($filiere_nom) . ' : ' . $cnt; ?>"></div>
                                        </div>
                                        <span class="etu-barchart-label"><?php echo htmlspecialchars($filiere_nom); ?> (<?php echo $cnt; ?>)</span>
                                    </div>
                                <?php $i++; endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="etu-stats-card etu-stats-card-wide">
                        <h3><i class="fas fa-chart-column"></i> <?php echo t('admin_utilisateurs_stats_par_niveau'); ?></h3>
                        <?php if (empty($admu_par_niveau)): ?>
                            <p class="gallery-empty"><i class="fas fa-chart-column"></i> <?php echo t('admin_etudiants_aucune_donnee'); ?></p>
                        <?php else: ?>
                            <div class="etu-barchart">
                                <?php $i = 0; foreach ($admu_par_niveau as $niveau_nom => $cnt):
                                    $group_height = round($cnt / $admu_max_niveau * 100);
                                ?>
                                    <div class="etu-barchart-group">
                                        <div class="etu-barchart-stack" style="height: <?php echo max($group_height, 4); ?>%;">
                                            <div class="etu-barchart-segment" style="height: 100%; background: <?php echo $etu_palette[$i % count($etu_palette)]; ?>;" title="<?php echo htmlspecialchars($niveau_nom) . ' : ' . $cnt; ?>"></div>
                                        </div>
                                        <span class="etu-barchart-label"><?php echo htmlspecialchars($niveau_nom); ?> (<?php echo $cnt; ?>)</span>
                                    </div>
                                <?php $i++; endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="etu-stats-card">
                        <h3><i class="fas fa-chart-pie"></i> <?php echo t('admin_etudiants_stats_par_parcours'); ?></h3>
                        <?php if ($admu_total_parcours === 0): ?>
                            <p class="gallery-empty"><i class="fas fa-chart-pie"></i> <?php echo t('admin_etudiants_aucune_donnee'); ?></p>
                        <?php else: ?>
                            <div class="etu-donut-wrap">
                                <div class="etu-donut" style="background: <?php echo $admu_conic_gradient; ?>;">
                                    <div class="etu-donut-hole"><strong><?php echo $admu_total_parcours; ?></strong><span><?php echo t('admin_utilisateurs_role_etudiant'); ?></span></div>
                                </div>
                                <ul class="etu-legend">
                                    <?php foreach ($admu_par_parcours as $cyc => $cnt):
                                        $pct = $admu_total_parcours > 0 ? round($cnt / $admu_total_parcours * 100) : 0;
                                        $cyc_label = $cyc === 'Licence' ? t('admin_etudiants_parcours_licence') : ($cyc === 'Master' ? t('admin_etudiants_parcours_master') : t('admin_etudiants_parcours_autre'));
                                    ?>
                                        <li><span class="etu-legend-dot" style="background: <?php echo $admu_parcours_couleurs[$cyc]; ?>;"></span> <?php echo htmlspecialchars($cyc_label); ?> — <strong><?php echo $cnt; ?></strong> (<?php echo $pct; ?>%)</li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
            <div class="admin-galerie-toolbar">
                <form action="admin_utilisateurs.php" method="GET" class="etu-search-form">
                    <div class="etu-search-input-wrap">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="search" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="<?php echo t('admin_utilisateurs_recherche_placeholder'); ?>">
                    </div>
                    <button type="submit" class="btn-outline"><i class="fas fa-filter"></i> <?php echo t('admin_etudiants_filtrer'); ?></button>
                </form>
                <a href="admin_utilisateurs.php?view=edit" class="btn-add-item admin-new-album-btn"><i class="fas fa-plus"></i> <?php echo t('admin_utilisateurs_nouveau'); ?></a>
            </div>

            <form action="admin_utilisateurs.php" method="POST" class="admu-add-teacher-form">
                <div class="etu-search-input-wrap">
                    <i class="fas fa-chalkboard-user"></i>
                    <input type="text" name="teacher_nom" placeholder="<?php echo t('admin_utilisateurs_nom_enseignant_placeholder'); ?>" required>
                </div>
                <button type="submit" name="create_teacher" class="btn-add-item"><i class="fas fa-user-plus"></i> <?php echo t('admin_utilisateurs_ajouter_enseignant'); ?></button>
            </form>

            <?php if (empty($all_users)): ?>
                <p class="gallery-empty"><i class="fas fa-users"></i> <?php echo t('admin_utilisateurs_aucun'); ?></p>
            <?php else: ?>
                <div class="admin-album-list">
                    <?php foreach ($all_users as $u): ?>
                        <div class="admin-album-row">
                            <img src="<?php echo htmlspecialchars($u['avatar_path'] ?: 'images/teachers/default-avatar.svg'); ?>" alt="" class="admin-album-row-thumb">
                            <div class="admin-album-row-info">
                                <h4><?php echo htmlspecialchars($u['nom']); ?></h4>
                                <div class="admin-album-row-meta">
                                    <span><i class="fas fa-id-badge"></i> <?php echo htmlspecialchars($u['email']); ?></span>
                                    <?php
                                    $admu_role_labels = [
                                        'admin' => t('admin_utilisateurs_role_admin'),
                                        'enseignant' => t('admin_utilisateurs_role_enseignant'),
                                        'etudiant' => t('admin_utilisateurs_role_etudiant'),
                                        'bibliotheque' => t('admin_utilisateurs_role_bibliotheque'),
                                        'user' => t('admin_utilisateurs_role_user'),
                                    ];
                                    ?>
                                    <span><i class="fas fa-shield-halved"></i> <?php echo $admu_role_labels[$u['role']] ?? $u['role']; ?></span>
                                    <?php if (!empty($u['filiere_nom'])): ?>
                                        <span><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($u['filiere_nom']); ?> (<?php echo htmlspecialchars($u['niveau'] ?? ''); ?>)</span>
                                    <?php endif; ?>
                                    <?php if (!empty($u['is_scolarite'])): ?><span class="etu-badge"><i class="fas fa-briefcase"></i> Scolarité</span><?php endif; ?>
                                    <?php if (!empty($u['is_messagerie'])): ?><span class="etu-badge"><i class="fas fa-comments"></i> Messagerie</span><?php endif; ?>
                                    <?php if (!empty($u['is_bibliotheque'])): ?><span class="etu-badge"><i class="fas fa-book"></i> Bibliothèque</span><?php endif; ?>
                                </div>
                                <?php if (!empty($u['dernier_mdp_genere'])): ?>
                                    <button type="button" class="etu-toggle-creds-btn" data-target="admu-creds-<?php echo $u['id']; ?>" data-show-label="<?php echo htmlspecialchars(t('admin_etudiants_voir_identifiants')); ?>" data-hide-label="<?php echo htmlspecialchars(t('admin_etudiants_masquer_identifiants')); ?>"><i class="fas fa-eye"></i> <?php echo t('admin_etudiants_voir_identifiants'); ?></button>
                                    <div class="etu-credentials-values etu-credentials-inline" id="admu-creds-<?php echo $u['id']; ?>" hidden>
                                        <span><?php echo t('admin_etudiants_identifiant'); ?>: <strong><?php echo htmlspecialchars($u['email']); ?></strong></span>
                                        <span><?php echo t('admin_etudiants_mot_de_passe'); ?>: <strong><?php echo htmlspecialchars($u['dernier_mdp_genere']); ?></strong></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="admin-album-row-actions">
                                <a href="admin_utilisateurs.php?view=edit&id=<?php echo $u['id']; ?>" class="btn-outline" title="<?php echo t('admin_modifier'); ?>"><i class="fas fa-pen"></i></a>
                                <form action="admin_utilisateurs.php" method="POST" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_utilisateurs_regenerer_confirm')); ?>">
                                    <button type="submit" name="regenerate_password_id" value="<?php echo $u['id']; ?>" class="btn-outline" title="<?php echo t('admin_utilisateurs_regenerer_mdp'); ?>"><i class="fas fa-key"></i></button>
                                </form>
                                <?php if ((int) $u['id'] !== (int) $_SESSION['user_id']): ?>
                                    <form action="admin_utilisateurs.php" method="POST" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_utilisateurs_confirm_suppr')); ?>">
                                        <button type="submit" name="delete_user_id" value="<?php echo $u['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.etu-toggle-creds-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = document.getElementById(btn.dataset.target);
            if (!target) return;
            target.hidden = !target.hidden;
            btn.innerHTML = target.hidden
                ? '<i class="fas fa-eye"></i> ' + btn.dataset.showLabel
                : '<i class="fas fa-eye-slash"></i> ' + btn.dataset.hideLabel;
        });
    });
});
</script>

<?php include 'footer.php'; ?>
