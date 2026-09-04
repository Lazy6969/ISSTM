<?php
include_once 'language.php';
require_once 'db_connect.php';

// --- Accès réservé à l'administrateur (rôle admin) et au compte scolarité ---
// On revérifie is_scolarite en base plutôt que de se fier uniquement à la session,
// pour couvrir aussi les sessions ouvertes avant l'ajout de cette page.
$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';
$is_scolarite = false;
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && !$is_admin) {
    $row = $mysqli->query("SELECT is_scolarite FROM utilisateurs WHERE id = " . (int) $_SESSION['user_id'])->fetch_assoc();
    $is_scolarite = $row && (bool) $row['is_scolarite'];
}
if (!$is_admin && !$is_scolarite) {
    header('Location: index.php');
    exit;
}

function etu_generate_password($length = 10) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    $pwd = '';
    for ($i = 0; $i < $length; $i++) {
        $pwd .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pwd;
}

function etu_unlink_photo($path) {
    if ($path && strpos($path, 'uploads/') === 0 && file_exists($path)) {
        unlink($path);
    }
}

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Approuver une demande : crée le compte utilisateur de l'étudiant ---
    if (isset($_POST['approve_id'])) {
        $pid = (int) $_POST['approve_id'];
        $p = $mysqli->query("SELECT * FROM preinscriptions WHERE id = $pid AND status = 'en_attente'")->fetch_assoc();
        if ($p) {
            $identifiant = 'USER_ISSTM_' . $pid;
            $password = etu_generate_password(10);
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $nom_complet = trim($p['nom'] . ' ' . $p['prenoms']);

            $stmt = $mysqli->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, avatar_path, role) VALUES (?, ?, ?, ?, 'etudiant')");
            $stmt->bind_param("ssss", $nom_complet, $identifiant, $hashed, $p['photo_path']);
            $stmt->execute();
            $new_user_id = $mysqli->insert_id;
            $stmt->close();

            $stmt = $mysqli->prepare("UPDATE preinscriptions SET status = 'approuve', user_id = ?, dernier_mdp_genere = ? WHERE id = ?");
            $stmt->bind_param("isi", $new_user_id, $password, $pid);
            $stmt->execute();
            $stmt->close();

            // Le mot de passe en clair est archivé dans dernier_mdp_genere (consultable à tout
            // moment via "Voir les identifiants") ; ce message de session sert seulement à le
            // mettre en avant juste après l'action, dans un encart bien visible.
            $_SESSION['pi_new_account'] = ['identifiant' => $identifiant, 'password' => $password, 'nom' => $nom_complet];
        }
        header('Location: admin_etudiants.php?status=approuve');
        exit;
    }

    // --- Supprimer une demande en attente ---
    if (isset($_POST['reject_id'])) {
        $pid = (int) $_POST['reject_id'];
        $p = $mysqli->query("SELECT photo_path FROM preinscriptions WHERE id = $pid AND status = 'en_attente'")->fetch_assoc();
        if ($p) {
            etu_unlink_photo($p['photo_path']);
            $mysqli->query("DELETE FROM preinscriptions WHERE id = $pid");
        }
        header('Location: admin_etudiants.php?status=en_attente&flash=' . urlencode('success|Demande supprimée.'));
        exit;
    }

    // --- Supprimer un étudiant approuvé (compte + dossier) ---
    if (isset($_POST['delete_student_id'])) {
        $pid = (int) $_POST['delete_student_id'];
        $p = $mysqli->query("SELECT photo_path, user_id FROM preinscriptions WHERE id = $pid")->fetch_assoc();
        if ($p) {
            if ($p['user_id']) {
                $mysqli->query("DELETE FROM utilisateurs WHERE id = " . (int) $p['user_id']);
            }
            etu_unlink_photo($p['photo_path']);
            $mysqli->query("DELETE FROM preinscriptions WHERE id = $pid");
        }
        header('Location: admin_etudiants.php?status=approuve&flash=' . urlencode('success|Étudiant supprimé.'));
        exit;
    }

    // --- Réinitialiser le mot de passe d'un étudiant ---
    if (isset($_POST['reset_password_id'])) {
        $pid = (int) $_POST['reset_password_id'];
        $p = $mysqli->query("SELECT user_id, nom, prenoms FROM preinscriptions WHERE id = $pid")->fetch_assoc();
        if ($p && $p['user_id']) {
            $password = etu_generate_password(10);
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $p['user_id']);
            $stmt->execute();
            $stmt->close();
            $stmt = $mysqli->prepare("UPDATE preinscriptions SET dernier_mdp_genere = ? WHERE id = ?");
            $stmt->bind_param("si", $password, $pid);
            $stmt->execute();
            $stmt->close();
            $idrow = $mysqli->query("SELECT email FROM utilisateurs WHERE id = " . (int) $p['user_id'])->fetch_assoc();
            $_SESSION['pi_new_account'] = ['identifiant' => $idrow['email'], 'password' => $password, 'nom' => trim($p['nom'] . ' ' . $p['prenoms']), 'reset' => true];
        }
        header('Location: admin_etudiants.php?status=approuve');
        exit;
    }

    // --- Enregistrer les modifications d'une fiche (en attente ou approuvée) ---
    if (isset($_POST['save_student'])) {
        $pid = (int) $_POST['pid'];
        $p = $mysqli->query("SELECT * FROM preinscriptions WHERE id = $pid")->fetch_assoc();
        if ($p) {
            $fields = [
                'nom', 'prenoms', 'sexe', 'date_naissance', 'lieu_naissance', 'cin', 'nationalite',
                'annee_bacc', 'serie_bacc', 'serie_bacc_autre', 'mention_bacc', 'code_redoublement',
                'adresse', 'telephone', 'email', 'nom_pere', 'profession_pere', 'nom_mere', 'profession_mere',
                'adresse_parents', 'contact_parents', 'contact_parents_2', 'pays', 'niveau',
            ];
            $values = [];
            foreach ($fields as $f) {
                $values[$f] = trim($_POST[$f] ?? '');
            }
            $filiere_id = ctype_digit($_POST['filiere_id'] ?? '') ? (int) $_POST['filiere_id'] : 0;

            $photo_path = $p['photo_path'];
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
                finfo_close($finfo);
                if (strpos($mime, 'image/') === 0) {
                    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    $new_photo = 'uploads/preinscriptions/photo_' . uniqid() . ($ext !== '' ? '.' . $ext : '');
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $new_photo)) {
                        etu_unlink_photo($p['photo_path']);
                        $photo_path = $new_photo;
                    }
                }
            }

            $stmt = $mysqli->prepare("UPDATE preinscriptions SET
                nom=?, prenoms=?, sexe=?, date_naissance=?, lieu_naissance=?, cin=?, nationalite=?, annee_bacc=?,
                serie_bacc=?, serie_bacc_autre=?, mention_bacc=?, code_redoublement=?, adresse=?, telephone=?, email=?,
                nom_pere=?, profession_pere=?, nom_mere=?, profession_mere=?, adresse_parents=?, contact_parents=?,
                contact_parents_2=?, pays=?, niveau=?, filiere_id=?, photo_path=?
                WHERE id=?");
            $stmt->bind_param(
                "ssssssssssssssssssssssssisi",
                $values['nom'], $values['prenoms'], $values['sexe'], $values['date_naissance'], $values['lieu_naissance'],
                $values['cin'], $values['nationalite'], $values['annee_bacc'], $values['serie_bacc'], $values['serie_bacc_autre'],
                $values['mention_bacc'], $values['code_redoublement'], $values['adresse'], $values['telephone'], $values['email'],
                $values['nom_pere'], $values['profession_pere'], $values['nom_mere'], $values['profession_mere'],
                $values['adresse_parents'], $values['contact_parents'], $values['contact_parents_2'], $values['pays'],
                $values['niveau'], $filiere_id, $photo_path, $pid
            );
            $stmt->execute();
            $stmt->close();

            // Si le dossier est déjà lié à un compte, on garde le nom et la photo affichés
            // dans le compte utilisateur synchronisés avec la fiche.
            if ($p['user_id']) {
                $nom_complet = trim($values['nom'] . ' ' . $values['prenoms']);
                if (!empty($_POST['new_password'])) {
                    $new_password = $_POST['new_password'];
                    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt2 = $mysqli->prepare("UPDATE utilisateurs SET nom = ?, avatar_path = ?, mot_de_passe = ? WHERE id = ?");
                    $stmt2->bind_param("sssi", $nom_complet, $photo_path, $hashed, $p['user_id']);
                    $mysqli->query("UPDATE preinscriptions SET dernier_mdp_genere = '" . $mysqli->real_escape_string($new_password) . "' WHERE id = " . $pid);
                } else {
                    $stmt2 = $mysqli->prepare("UPDATE utilisateurs SET nom = ?, avatar_path = ? WHERE id = ?");
                    $stmt2->bind_param("ssi", $nom_complet, $photo_path, $p['user_id']);
                }
                $stmt2->execute();
                $stmt2->close();
            }

            header('Location: admin_etudiants.php?status=' . urlencode($p['status']) . '&flash=' . urlencode('success|Fiche mise à jour.'));
            exit;
        }
    }
}

if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

// Message ponctuel (identifiant + mot de passe en clair) affiché une seule fois après
// une création de compte ou une réinitialisation, puis effacé de la session.
$new_account = $_SESSION['pi_new_account'] ?? null;
unset($_SESSION['pi_new_account']);

$view = $_GET['view'] ?? 'list';
$status = $_GET['status'] ?? 'en_attente';
if (!in_array($status, ['en_attente', 'approuve', 'stats'], true)) $status = 'en_attente';

$filieres_list = $mysqli->query("SELECT id, nom_fr, niveaux FROM filieres ORDER BY display_order ASC, nom_fr ASC")->fetch_all(MYSQLI_ASSOC);
$pays_nationalites = include 'pays_nationalites.php';

$page_title = t('admin_etudiants_titre');
include 'header.php';
?>

<a href="administrateur.php" class="btn-outline btn-back-sticky"><i class="fas fa-arrow-left"></i> <?php echo t('admin_retour_tableau_bord'); ?></a>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('admin_etudiants_titre'); ?></span>
        </nav>
        <h1><?php echo t('admin_etudiants_titre'); ?></h1>
        <p><?php echo t('admin_etudiants_soustitre'); ?></p>
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
                    <h4><?php echo $new_account['reset'] ?? false ? t('admin_etudiants_mdp_reinitialise') : t('admin_etudiants_compte_cree'); ?></h4>
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
            $pid = (int) ($_GET['id'] ?? 0);
            $p = $mysqli->query("SELECT * FROM preinscriptions WHERE id = $pid")->fetch_assoc();
            if (!$p) { echo '<p>' . t('admin_etudiants_introuvable') . '</p>'; include 'footer.php'; exit; }
        ?>
            <a href="admin_etudiants.php?status=<?php echo htmlspecialchars($p['status']); ?>" class="admin-galerie-back"><i class="fas fa-chevron-left"></i> <?php echo t('admin_etudiants_retour_liste'); ?></a>
            <h2 class="admin-section-title"><?php echo htmlspecialchars($p['nom'] . ' ' . $p['prenoms']); ?></h2>

            <form action="admin_etudiants.php" method="POST" enctype="multipart/form-data" class="admin-form preinscription-form">
                <input type="hidden" name="pid" value="<?php echo (int) $p['id']; ?>">
                <div class="preinscription-form-grid">
                    <fieldset class="preinscription-fieldset">
                        <legend><i class="fas fa-id-card"></i> <?php echo t('preinscription_section_identite'); ?></legend>
                        <div class="preinscription-row">
                            <div class="preinscription-field"><label><?php echo t('preinscription_nom'); ?></label><input type="text" name="nom" value="<?php echo htmlspecialchars($p['nom']); ?>" required></div>
                            <div class="preinscription-field"><label><?php echo t('preinscription_prenoms'); ?></label><input type="text" name="prenoms" value="<?php echo htmlspecialchars($p['prenoms']); ?>" required></div>
                        </div>
                        <div class="preinscription-row">
                            <div class="preinscription-field">
                                <label><?php echo t('preinscription_sexe'); ?></label>
                                <div class="preinscription-radio-group">
                                    <label class="preinscription-radio"><input type="radio" name="sexe" value="M" <?php echo $p['sexe'] === 'M' ? 'checked' : ''; ?>> <?php echo t('preinscription_sexe_m'); ?></label>
                                    <label class="preinscription-radio"><input type="radio" name="sexe" value="F" <?php echo $p['sexe'] === 'F' ? 'checked' : ''; ?>> <?php echo t('preinscription_sexe_f'); ?></label>
                                </div>
                            </div>
                            <div class="preinscription-field"><label><?php echo t('preinscription_date_naissance'); ?></label><input type="date" name="date_naissance" value="<?php echo htmlspecialchars($p['date_naissance']); ?>" required></div>
                        </div>
                        <div class="preinscription-row">
                            <div class="preinscription-field"><label><?php echo t('preinscription_lieu_naissance'); ?></label><input type="text" name="lieu_naissance" value="<?php echo htmlspecialchars($p['lieu_naissance']); ?>" required></div>
                            <div class="preinscription-field"><label><?php echo t('preinscription_cin'); ?></label><input type="text" name="cin" value="<?php echo htmlspecialchars($p['cin'] ?? ''); ?>"></div>
                        </div>
                        <div class="preinscription-row">
                            <div class="preinscription-field">
                                <label><?php echo t('preinscription_nationalite'); ?></label>
                                <select name="nationalite" required>
                                    <?php foreach ($pays_nationalites as $pn): ?>
                                        <option value="<?php echo htmlspecialchars($pn['nationalite']); ?>" <?php echo $pn['nationalite'] === $p['nationalite'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($pn['nationalite']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="preinscription-field">
                                <label><?php echo t('preinscription_pays'); ?></label>
                                <select name="pays" required>
                                    <?php foreach ($pays_nationalites as $pn): ?>
                                        <option value="<?php echo htmlspecialchars($pn['pays']); ?>" <?php echo $pn['pays'] === $p['pays'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($pn['pays']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="preinscription-fieldset">
                        <legend><i class="fas fa-graduation-cap"></i> <?php echo t('preinscription_section_filiere'); ?></legend>
                        <div class="preinscription-row">
                            <div class="preinscription-field">
                                <label><?php echo t('preinscription_filiere'); ?></label>
                                <select name="filiere_id" required>
                                    <?php foreach ($filieres_list as $f): ?>
                                        <option value="<?php echo (int) $f['id']; ?>" <?php echo (int) $f['id'] === (int) $p['filiere_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['nom_fr']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="preinscription-field">
                                <label><?php echo t('preinscription_niveau'); ?></label>
                                <input type="text" name="niveau" value="<?php echo htmlspecialchars($p['niveau'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="preinscription-fieldset">
                        <legend><i class="fas fa-graduation-cap"></i> <?php echo t('preinscription_section_bac'); ?></legend>
                        <div class="preinscription-row">
                            <div class="preinscription-field"><label><?php echo t('preinscription_annee_bacc'); ?></label><input type="number" name="annee_bacc" value="<?php echo htmlspecialchars($p['annee_bacc']); ?>" required></div>
                            <div class="preinscription-field">
                                <label><?php echo t('preinscription_code_redoublement'); ?></label>
                                <div class="preinscription-radio-group">
                                    <label class="preinscription-radio"><input type="radio" name="code_redoublement" value="N" <?php echo $p['code_redoublement'] === 'N' ? 'checked' : ''; ?>> <?php echo t('preinscription_redoublement_n'); ?></label>
                                    <label class="preinscription-radio"><input type="radio" name="code_redoublement" value="R" <?php echo $p['code_redoublement'] === 'R' ? 'checked' : ''; ?>> <?php echo t('preinscription_redoublement_r'); ?></label>
                                </div>
                            </div>
                        </div>
                        <div class="preinscription-row">
                            <div class="preinscription-field"><label><?php echo t('preinscription_serie_bacc'); ?></label><input type="text" name="serie_bacc" value="<?php echo htmlspecialchars($p['serie_bacc']); ?>" required></div>
                            <div class="preinscription-field"><label><?php echo t('preinscription_serie_bacc_autre_placeholder'); ?></label><input type="text" name="serie_bacc_autre" value="<?php echo htmlspecialchars($p['serie_bacc_autre'] ?? ''); ?>"></div>
                            <div class="preinscription-field">
                                <label><?php echo t('preinscription_mention_bacc'); ?></label>
                                <select name="mention_bacc" required>
                                    <?php foreach (['Passable', 'Assez Bien', 'Bien', 'Très Bien'] as $m): ?>
                                        <option value="<?php echo $m; ?>" <?php echo $m === $p['mention_bacc'] ? 'selected' : ''; ?>><?php echo $m; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="preinscription-fieldset">
                        <legend><i class="fas fa-location-dot"></i> <?php echo t('preinscription_section_contact'); ?></legend>
                        <div class="preinscription-row">
                            <div class="preinscription-field preinscription-field-full"><label><?php echo t('preinscription_adresse'); ?></label><input type="text" name="adresse" value="<?php echo htmlspecialchars($p['adresse']); ?>" required></div>
                        </div>
                        <div class="preinscription-row">
                            <div class="preinscription-field"><label><?php echo t('preinscription_telephone'); ?></label><input type="tel" name="telephone" value="<?php echo htmlspecialchars($p['telephone']); ?>" required></div>
                            <div class="preinscription-field"><label><?php echo t('preinscription_email'); ?></label><input type="email" name="email" value="<?php echo htmlspecialchars($p['email']); ?>" required></div>
                        </div>
                    </fieldset>

                    <fieldset class="preinscription-fieldset">
                        <legend><i class="fas fa-people-roof"></i> <?php echo t('preinscription_section_parents'); ?></legend>
                        <div class="preinscription-row">
                            <div class="preinscription-field"><label><?php echo t('preinscription_nom_pere'); ?></label><input type="text" name="nom_pere" value="<?php echo htmlspecialchars($p['nom_pere'] ?? ''); ?>"></div>
                            <div class="preinscription-field"><label><?php echo t('preinscription_profession_pere'); ?></label><input type="text" name="profession_pere" value="<?php echo htmlspecialchars($p['profession_pere'] ?? ''); ?>"></div>
                        </div>
                        <div class="preinscription-row">
                            <div class="preinscription-field"><label><?php echo t('preinscription_nom_mere'); ?></label><input type="text" name="nom_mere" value="<?php echo htmlspecialchars($p['nom_mere'] ?? ''); ?>"></div>
                            <div class="preinscription-field"><label><?php echo t('preinscription_profession_mere'); ?></label><input type="text" name="profession_mere" value="<?php echo htmlspecialchars($p['profession_mere'] ?? ''); ?>"></div>
                        </div>
                        <div class="preinscription-row">
                            <div class="preinscription-field preinscription-field-full"><label><?php echo t('preinscription_adresse_parents'); ?></label><input type="text" name="adresse_parents" value="<?php echo htmlspecialchars($p['adresse_parents'] ?? ''); ?>"></div>
                        </div>
                        <div class="preinscription-row">
                            <div class="preinscription-field"><label><?php echo t('preinscription_contact_parents'); ?> 1</label><input type="text" name="contact_parents" value="<?php echo htmlspecialchars($p['contact_parents'] ?? ''); ?>"></div>
                            <div class="preinscription-field"><label><?php echo t('preinscription_contact_parents'); ?> 2</label><input type="text" name="contact_parents_2" value="<?php echo htmlspecialchars($p['contact_parents_2'] ?? ''); ?>"></div>
                        </div>
                    </fieldset>

                    <fieldset class="preinscription-fieldset">
                        <legend><i class="fas fa-camera-retro"></i> <?php echo t('preinscription_section_photo'); ?></legend>
                        <div class="preinscription-photo-upload">
                            <div class="preinscription-photo-preview">
                                <?php if (!empty($p['photo_path'])): ?><img src="<?php echo htmlspecialchars($p['photo_path']); ?>" alt=""><?php else: ?><i class="fas fa-user"></i><?php endif; ?>
                            </div>
                            <div class="preinscription-photo-controls">
                                <label for="etu-photo" class="preinscription-photo-btn"><i class="fas fa-upload"></i> <?php echo t('preinscription_photo_choisir'); ?></label>
                                <input type="file" id="etu-photo" name="photo" accept="image/*" class="preinscription-file-hidden">
                            </div>
                        </div>
                    </fieldset>

                    <?php if ($p['status'] === 'approuve'):
                        $identifiant_row = $mysqli->query("SELECT email FROM utilisateurs WHERE id = " . (int) $p['user_id'])->fetch_assoc();
                    ?>
                        <fieldset class="preinscription-fieldset">
                            <legend><i class="fas fa-key"></i> <?php echo t('admin_etudiants_mot_de_passe'); ?></legend>
                            <?php if ($identifiant_row): ?>
                                <div class="etu-credentials-values etu-credentials-inline">
                                    <span><?php echo t('admin_etudiants_identifiant'); ?>: <strong><?php echo htmlspecialchars($identifiant_row['email']); ?></strong></span>
                                    <span><?php echo t('admin_etudiants_mot_de_passe'); ?>: <strong><?php echo htmlspecialchars($p['dernier_mdp_genere'] ?? '—'); ?></strong></span>
                                </div>
                            <?php endif; ?>
                            <div class="preinscription-row">
                                <div class="preinscription-field preinscription-field-full">
                                    <label><?php echo t('admin_etudiants_nouveau_mdp_label'); ?></label>
                                    <input type="text" name="new_password" placeholder="<?php echo t('admin_etudiants_nouveau_mdp_placeholder'); ?>">
                                    <small class="preinscription-hint"><?php echo t('admin_etudiants_nouveau_mdp_aide'); ?></small>
                                </div>
                            </div>
                        </fieldset>
                    <?php endif; ?>
                </div>

                <button type="submit" name="save_student" class="btn-submit"><i class="fas fa-save"></i> <?php echo t('admin_enregistrer_modifications'); ?></button>
            </form>

        <?php else:
            $en_attente_count = (int) $mysqli->query("SELECT COUNT(*) c FROM preinscriptions WHERE status='en_attente'")->fetch_assoc()['c'];
            $approuve_count = (int) $mysqli->query("SELECT COUNT(*) c FROM preinscriptions WHERE status='approuve'")->fetch_assoc()['c'];
            $q = trim($_GET['q'] ?? '');
            $annee = ctype_digit($_GET['annee'] ?? '') ? (int) $_GET['annee'] : 0;
            $annees_disponibles = $mysqli->query("SELECT DISTINCT YEAR(created_at) AS y FROM preinscriptions ORDER BY y DESC")->fetch_all(MYSQLI_ASSOC);
        ?>
            <div class="admin-galerie-stats">
                <div class="admin-stat-card"><i class="fas fa-hourglass-half"></i><div><strong><?php echo $en_attente_count; ?></strong><span><?php echo t('admin_etudiants_en_attente'); ?></span></div></div>
                <div class="admin-stat-card"><i class="fas fa-user-graduate"></i><div><strong><?php echo $approuve_count; ?></strong><span><?php echo t('admin_etudiants_approuves'); ?></span></div></div>
            </div>

            <div class="admin-galerie-toolbar etu-tabs">
                <a href="admin_etudiants.php?status=en_attente" class="btn-outline <?php echo $status === 'en_attente' ? 'is-active' : ''; ?>"><i class="fas fa-hourglass-half"></i> <?php echo t('admin_etudiants_en_attente'); ?></a>
                <a href="admin_etudiants.php?status=approuve" class="btn-outline <?php echo $status === 'approuve' ? 'is-active' : ''; ?>"><i class="fas fa-user-graduate"></i> <?php echo t('admin_etudiants_approuves'); ?></a>
                <a href="admin_etudiants.php?status=stats" class="btn-outline <?php echo $status === 'stats' ? 'is-active' : ''; ?>"><i class="fas fa-chart-pie"></i> <?php echo t('admin_etudiants_statistiques'); ?></a>
            </div>

            <?php if ($status !== 'stats'): ?>
                <form action="admin_etudiants.php" method="GET" class="etu-search-form">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                    <div class="etu-search-input-wrap">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="search" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="<?php echo t('admin_etudiants_recherche_placeholder'); ?>">
                    </div>
                    <select name="annee">
                        <option value=""><?php echo t('admin_etudiants_toutes_annees'); ?></option>
                        <?php foreach ($annees_disponibles as $a): ?>
                            <option value="<?php echo (int) $a['y']; ?>" <?php echo $annee === (int) $a['y'] ? 'selected' : ''; ?>><?php echo (int) $a['y']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-outline"><i class="fas fa-filter"></i> <?php echo t('admin_etudiants_filtrer'); ?></button>
                    <?php if ($q !== '' || $annee > 0): ?>
                        <a href="admin_etudiants.php?status=<?php echo htmlspecialchars($status); ?>" class="btn-outline"><i class="fas fa-xmark"></i> <?php echo t('admin_etudiants_reinitialiser_filtre'); ?></a>
                    <?php endif; ?>
                </form>
            <?php endif; ?>

            <?php
            // Construit la clause de filtre (recherche texte multi-champs + année d'inscription)
            // partagée par les deux onglets liste, sur une requête préparée.
            $etu_where = "p.status = ?";
            $etu_types = "s";
            $etu_params = [$status === 'stats' ? 'approuve' : $status];
            if ($q !== '') {
                $etu_where .= " AND (p.nom LIKE ? OR p.prenoms LIKE ? OR p.email LIKE ? OR p.telephone LIKE ?
                    OR p.cin LIKE ? OR p.nationalite LIKE ? OR p.pays LIKE ? OR p.adresse LIKE ?
                    OR p.nom_pere LIKE ? OR p.nom_mere LIKE ? OR p.lieu_naissance LIKE ? OR p.niveau LIKE ?
                    OR f.nom_fr LIKE ? OR u.email LIKE ?)";
                $like = "%$q%";
                for ($i = 0; $i < 14; $i++) { $etu_types .= "s"; $etu_params[] = $like; }
            }
            if ($annee > 0) {
                $etu_where .= " AND YEAR(p.created_at) = ?";
                $etu_types .= "i";
                $etu_params[] = $annee;
            }
            ?>

            <?php if ($status === 'en_attente'):
                $stmt = $mysqli->prepare("SELECT p.*, f.nom_fr AS filiere_nom FROM preinscriptions p
                    LEFT JOIN filieres f ON f.id = p.filiere_id
                    LEFT JOIN utilisateurs u ON u.id = p.user_id
                    WHERE $etu_where ORDER BY p.created_at DESC");
                $stmt->bind_param($etu_types, ...$etu_params);
                $stmt->execute();
                $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            ?>
                <?php if (empty($rows)): ?>
                    <p class="gallery-empty"><i class="fas fa-hourglass-half"></i> <?php echo t('admin_etudiants_aucune_demande'); ?></p>
                <?php else: ?>
                    <div class="admin-album-list">
                        <?php foreach ($rows as $r): ?>
                            <div class="admin-album-row">
                                <img src="<?php echo htmlspecialchars($r['photo_path'] ?: 'images/teachers/default-avatar.svg'); ?>" alt="" class="admin-album-row-thumb">
                                <div class="admin-album-row-info">
                                    <h4><?php echo htmlspecialchars($r['nom'] . ' ' . $r['prenoms']); ?></h4>
                                    <div class="admin-album-row-meta">
                                        <span><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($r['filiere_nom'] ?? '—'); ?> (<?php echo htmlspecialchars($r['niveau'] ?? '—'); ?>)</span>
                                        <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($r['email']); ?></span>
                                        <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($r['telephone']); ?></span>
                                        <span><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($r['created_at']); ?></span>
                                    </div>
                                </div>
                                <div class="admin-album-row-actions">
                                    <a href="admin_etudiants.php?view=edit&id=<?php echo $r['id']; ?>" class="btn-outline" title="<?php echo t('admin_modifier'); ?>"><i class="fas fa-pen"></i></a>
                                    <form action="admin_etudiants.php" method="POST" style="display:inline;">
                                        <button type="submit" name="approve_id" value="<?php echo $r['id']; ?>" class="btn-outline etu-btn-approve" title="<?php echo t('admin_etudiants_approuver'); ?>"><i class="fas fa-check"></i></button>
                                    </form>
                                    <form action="admin_etudiants.php" method="POST" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_etudiants_confirm_suppr_demande')); ?>">
                                        <button type="submit" name="reject_id" value="<?php echo $r['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php elseif ($status === 'approuve'):
                $stmt = $mysqli->prepare("SELECT p.*, f.nom_fr AS filiere_nom, u.email AS identifiant FROM preinscriptions p
                    LEFT JOIN filieres f ON f.id = p.filiere_id
                    LEFT JOIN utilisateurs u ON u.id = p.user_id
                    WHERE $etu_where ORDER BY p.created_at DESC");
                $stmt->bind_param($etu_types, ...$etu_params);
                $stmt->execute();
                $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            ?>
                <?php if (empty($rows)): ?>
                    <p class="gallery-empty"><i class="fas fa-user-graduate"></i> <?php echo t('admin_etudiants_aucun_etudiant'); ?></p>
                <?php else: ?>
                    <div class="admin-album-list">
                        <?php foreach ($rows as $r): ?>
                            <div class="admin-album-row">
                                <img src="<?php echo htmlspecialchars($r['photo_path'] ?: 'images/teachers/default-avatar.svg'); ?>" alt="" class="admin-album-row-thumb">
                                <div class="admin-album-row-info">
                                    <h4><?php echo htmlspecialchars($r['nom'] . ' ' . $r['prenoms']); ?></h4>
                                    <div class="admin-album-row-meta">
                                        <span><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($r['filiere_nom'] ?? '—'); ?> (<?php echo htmlspecialchars($r['niveau'] ?? '—'); ?>)</span>
                                        <span><i class="fas fa-id-badge"></i> <?php echo htmlspecialchars($r['identifiant'] ?? '—'); ?></span>
                                        <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($r['email']); ?></span>
                                        <span><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($r['created_at']); ?></span>
                                    </div>
                                    <button type="button" class="etu-toggle-creds-btn" data-target="creds-<?php echo $r['id']; ?>" data-show-label="<?php echo htmlspecialchars(t('admin_etudiants_voir_identifiants')); ?>" data-hide-label="<?php echo htmlspecialchars(t('admin_etudiants_masquer_identifiants')); ?>"><i class="fas fa-eye"></i> <?php echo t('admin_etudiants_voir_identifiants'); ?></button>
                                    <div class="etu-credentials-values etu-credentials-inline" id="creds-<?php echo $r['id']; ?>" hidden>
                                        <span><?php echo t('admin_etudiants_identifiant'); ?>: <strong><?php echo htmlspecialchars($r['identifiant'] ?? '—'); ?></strong></span>
                                        <span><?php echo t('admin_etudiants_mot_de_passe'); ?>: <strong><?php echo htmlspecialchars($r['dernier_mdp_genere'] ?? '—'); ?></strong></span>
                                    </div>
                                </div>
                                <div class="admin-album-row-actions">
                                    <a href="admin_etudiants.php?view=edit&id=<?php echo $r['id']; ?>" class="btn-outline" title="<?php echo t('admin_modifier'); ?>"><i class="fas fa-pen"></i></a>
                                    <form action="admin_etudiants.php" method="POST" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_etudiants_confirm_reset')); ?>">
                                        <button type="submit" name="reset_password_id" value="<?php echo $r['id']; ?>" class="btn-outline" title="<?php echo t('admin_etudiants_reinitialiser_mdp'); ?>"><i class="fas fa-key"></i></button>
                                    </form>
                                    <form action="admin_etudiants.php" method="POST" style="display:inline;" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('admin_etudiants_confirm_suppr_etudiant')); ?>">
                                        <button type="submit" name="delete_student_id" value="<?php echo $r['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: // --- Statistiques ---
                $par_annee = $mysqli->query("SELECT YEAR(created_at) AS annee, COUNT(*) AS total FROM preinscriptions WHERE status='approuve' GROUP BY YEAR(created_at) ORDER BY annee ASC")->fetch_all(MYSQLI_ASSOC);
                $par_filiere_annee = $mysqli->query("SELECT f.nom_fr AS filiere, YEAR(p.created_at) AS annee, COUNT(*) AS total
                    FROM preinscriptions p JOIN filieres f ON f.id = p.filiere_id
                    WHERE p.status = 'approuve'
                    GROUP BY p.filiere_id, YEAR(p.created_at)
                    ORDER BY f.display_order ASC, f.nom_fr ASC, annee ASC")->fetch_all(MYSQLI_ASSOC);

                $etu_palette = ['#003366', '#d4a017', '#2ecc71', '#e74c3c', '#8e44ad', '#16a085', '#e67e22', '#2980b9', '#c0392b', '#7f8c8d'];
                $annees_liste = [];
                foreach ($par_annee as $row) { $annees_liste[] = (int) $row['annee']; }
                foreach ($par_filiere_annee as $row) { if (!in_array((int) $row['annee'], $annees_liste, true)) $annees_liste[] = (int) $row['annee']; }
                sort($annees_liste);
                $annee_couleur = [];
                foreach ($annees_liste as $i => $an) { $annee_couleur[$an] = $etu_palette[$i % count($etu_palette)]; }

                $total_approuves = array_sum(array_column($par_annee, 'total'));
                $cumulative = 0;
                $conic_stops = [];
                foreach ($par_annee as $row) {
                    $an = (int) $row['annee'];
                    $start = $total_approuves > 0 ? round($cumulative / $total_approuves * 360, 2) : 0;
                    $cumulative += (int) $row['total'];
                    $end = $total_approuves > 0 ? round($cumulative / $total_approuves * 360, 2) : 0;
                    $conic_stops[] = $annee_couleur[$an] . " {$start}deg {$end}deg";
                }
                $conic_gradient = !empty($conic_stops) ? 'conic-gradient(' . implode(', ', $conic_stops) . ')' : 'conic-gradient(var(--border-color) 0deg 360deg)';

                // Regroupe par filière -> [année => total], pour le diagramme empilé.
                $par_filiere = [];
                foreach ($par_filiere_annee as $row) {
                    $f = $row['filiere'];
                    if (!isset($par_filiere[$f])) $par_filiere[$f] = [];
                    $par_filiere[$f][(int) $row['annee']] = (int) $row['total'];
                }
                $max_filiere_total = 1;
                foreach ($par_filiere as $annees_counts) {
                    $max_filiere_total = max($max_filiere_total, array_sum($annees_counts));
                }

                // --- Répartition par niveau (L1, L2, L3, M1, M2...), même principe empilé par année ---
                $par_niveau_annee = $mysqli->query("SELECT p.niveau AS niveau, YEAR(p.created_at) AS annee, COUNT(*) AS total
                    FROM preinscriptions p
                    WHERE p.status = 'approuve' AND p.niveau IS NOT NULL AND p.niveau != ''
                    GROUP BY p.niveau, YEAR(p.created_at)
                    ORDER BY p.niveau ASC, annee ASC")->fetch_all(MYSQLI_ASSOC);
                // Mêmes lignes "approuvées" que $par_annee ci-dessus, donc les mêmes années : pas
                // besoin de recompléter $annees_liste / $annee_couleur pour ce regroupement.
                $par_niveau = [];
                foreach ($par_niveau_annee as $row) {
                    $niv = $row['niveau'];
                    if (!isset($par_niveau[$niv])) $par_niveau[$niv] = [];
                    $par_niveau[$niv][(int) $row['annee']] = (int) $row['total'];
                }
                $max_niveau_total = 1;
                foreach ($par_niveau as $annees_counts) {
                    $max_niveau_total = max($max_niveau_total, array_sum($annees_counts));
                }

                // --- Répartition par parcours (cycle Licence L1-L3 / Master M1-M2), déduite du niveau :
                // un niveau commençant par "L" compte en Licence, par "M" en Master, le reste en "Autre".
                $par_parcours = ['Licence' => 0, 'Master' => 0, 'Autre' => 0];
                foreach ($par_niveau_annee as $row) {
                    $niv = strtoupper(trim($row['niveau']));
                    if ($niv !== '' && $niv[0] === 'L') { $par_parcours['Licence'] += (int) $row['total']; }
                    elseif ($niv !== '' && $niv[0] === 'M') { $par_parcours['Master'] += (int) $row['total']; }
                    else { $par_parcours['Autre'] += (int) $row['total']; }
                }
                $par_parcours = array_filter($par_parcours);
                $total_parcours = array_sum($par_parcours);
                $parcours_couleurs = ['Licence' => '#2ecc71', 'Master' => '#8e44ad', 'Autre' => '#7f8c8d'];
                $cumulative_pc = 0;
                $conic_stops_pc = [];
                foreach ($par_parcours as $cyc => $cnt) {
                    $start = $total_parcours > 0 ? round($cumulative_pc / $total_parcours * 360, 2) : 0;
                    $cumulative_pc += $cnt;
                    $end = $total_parcours > 0 ? round($cumulative_pc / $total_parcours * 360, 2) : 0;
                    $conic_stops_pc[] = $parcours_couleurs[$cyc] . " {$start}deg {$end}deg";
                }
                $conic_gradient_pc = !empty($conic_stops_pc) ? 'conic-gradient(' . implode(', ', $conic_stops_pc) . ')' : 'conic-gradient(var(--border-color) 0deg 360deg)';
            ?>
                <div class="etu-stats-grid">
                    <div class="etu-stats-card">
                        <h3><i class="fas fa-chart-pie"></i> <?php echo t('admin_etudiants_stats_par_annee'); ?></h3>
                        <?php if ($total_approuves === 0): ?>
                            <p class="gallery-empty"><i class="fas fa-chart-pie"></i> <?php echo t('admin_etudiants_aucune_donnee'); ?></p>
                        <?php else: ?>
                            <div class="etu-donut-wrap">
                                <div class="etu-donut" style="background: <?php echo $conic_gradient; ?>;">
                                    <div class="etu-donut-hole"><strong><?php echo $total_approuves; ?></strong><span><?php echo t('admin_etudiants_approuves'); ?></span></div>
                                </div>
                                <ul class="etu-legend">
                                    <?php foreach ($par_annee as $row):
                                        $an = (int) $row['annee'];
                                        $pct = $total_approuves > 0 ? round($row['total'] / $total_approuves * 100) : 0;
                                    ?>
                                        <li><span class="etu-legend-dot" style="background: <?php echo $annee_couleur[$an]; ?>;"></span> <?php echo $an; ?> — <strong><?php echo (int) $row['total']; ?></strong> (<?php echo $pct; ?>%)</li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="etu-stats-card etu-stats-card-wide">
                        <h3><i class="fas fa-chart-column"></i> <?php echo t('admin_etudiants_stats_par_filiere'); ?></h3>
                        <?php if (empty($par_filiere)): ?>
                            <p class="gallery-empty"><i class="fas fa-chart-column"></i> <?php echo t('admin_etudiants_aucune_donnee'); ?></p>
                        <?php else: ?>
                            <div class="etu-barchart">
                                <?php foreach ($par_filiere as $filiere_nom => $annees_counts):
                                    $filiere_total = array_sum($annees_counts);
                                    $group_height = round($filiere_total / $max_filiere_total * 100);
                                ?>
                                    <div class="etu-barchart-group">
                                        <div class="etu-barchart-stack" style="height: <?php echo max($group_height, 4); ?>%;">
                                            <?php foreach ($annees_counts as $an => $cnt):
                                                $seg_height = round($cnt / $filiere_total * 100);
                                            ?>
                                                <div class="etu-barchart-segment" style="height: <?php echo $seg_height; ?>%; background: <?php echo $annee_couleur[$an]; ?>;" title="<?php echo htmlspecialchars($filiere_nom) . ' — ' . $an . ' : ' . $cnt; ?>"></div>
                                            <?php endforeach; ?>
                                        </div>
                                        <span class="etu-barchart-label"><?php echo htmlspecialchars($filiere_nom); ?> (<?php echo $filiere_total; ?>)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <ul class="etu-legend etu-legend-horizontal">
                                <?php foreach ($annees_liste as $an): ?>
                                    <li><span class="etu-legend-dot" style="background: <?php echo $annee_couleur[$an]; ?>;"></span> <?php echo $an; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="etu-stats-card etu-stats-card-wide">
                        <h3><i class="fas fa-chart-column"></i> <?php echo t('admin_etudiants_stats_par_niveau'); ?></h3>
                        <?php if (empty($par_niveau)): ?>
                            <p class="gallery-empty"><i class="fas fa-chart-column"></i> <?php echo t('admin_etudiants_aucune_donnee'); ?></p>
                        <?php else: ?>
                            <div class="etu-barchart">
                                <?php foreach ($par_niveau as $niveau_nom => $annees_counts):
                                    $niveau_total = array_sum($annees_counts);
                                    $group_height = round($niveau_total / $max_niveau_total * 100);
                                ?>
                                    <div class="etu-barchart-group">
                                        <div class="etu-barchart-stack" style="height: <?php echo max($group_height, 4); ?>%;">
                                            <?php foreach ($annees_counts as $an => $cnt):
                                                $seg_height = round($cnt / $niveau_total * 100);
                                            ?>
                                                <div class="etu-barchart-segment" style="height: <?php echo $seg_height; ?>%; background: <?php echo $annee_couleur[$an]; ?>;" title="<?php echo htmlspecialchars($niveau_nom) . ' — ' . $an . ' : ' . $cnt; ?>"></div>
                                            <?php endforeach; ?>
                                        </div>
                                        <span class="etu-barchart-label"><?php echo htmlspecialchars($niveau_nom); ?> (<?php echo $niveau_total; ?>)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <ul class="etu-legend etu-legend-horizontal">
                                <?php foreach ($annees_liste as $an): ?>
                                    <li><span class="etu-legend-dot" style="background: <?php echo $annee_couleur[$an]; ?>;"></span> <?php echo $an; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="etu-stats-card">
                        <h3><i class="fas fa-chart-pie"></i> <?php echo t('admin_etudiants_stats_par_parcours'); ?></h3>
                        <?php if ($total_parcours === 0): ?>
                            <p class="gallery-empty"><i class="fas fa-chart-pie"></i> <?php echo t('admin_etudiants_aucune_donnee'); ?></p>
                        <?php else: ?>
                            <div class="etu-donut-wrap">
                                <div class="etu-donut" style="background: <?php echo $conic_gradient_pc; ?>;">
                                    <div class="etu-donut-hole"><strong><?php echo $total_parcours; ?></strong><span><?php echo t('admin_etudiants_approuves'); ?></span></div>
                                </div>
                                <ul class="etu-legend">
                                    <?php foreach ($par_parcours as $cyc => $cnt):
                                        $pct = $total_parcours > 0 ? round($cnt / $total_parcours * 100) : 0;
                                        $cyc_label = $cyc === 'Licence' ? t('admin_etudiants_parcours_licence') : ($cyc === 'Master' ? t('admin_etudiants_parcours_master') : t('admin_etudiants_parcours_autre'));
                                    ?>
                                        <li><span class="etu-legend-dot" style="background: <?php echo $parcours_couleurs[$cyc]; ?>;"></span> <?php echo htmlspecialchars($cyc_label); ?> — <strong><?php echo $cnt; ?></strong> (<?php echo $pct; ?>%)</li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
