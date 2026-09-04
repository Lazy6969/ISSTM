<?php
// Traite les actions POST sur les groupes personnels puis redirige toujours vers le hub
// mes_amis.php (onglet "Groupes") — voir groupe_perso_partiel.php pour le rendu partagé.
include_once 'language.php';
require_once 'db_connect.php';
require_once 'groupe_perso_functions.php';

groupe_perso_require_login();
$self_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Créer un groupe (n'importe quel rôle) ---
    if (isset($_POST['create_group'])) {
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($nom === '') {
            header('Location: mes_amis.php?tab=groupes&flash=' . urlencode('error|' . t('groupe_perso_nom_requis')));
            exit;
        }
        $code = groupe_perso_generate_code($mysqli);
        $stmt = $mysqli->prepare("INSERT INTO groupes_utilisateurs (nom, description, createur_id, code_unique) VALUES (?,?,?,?)");
        $stmt->bind_param("ssis", $nom, $description, $self_id, $code);
        $stmt->execute();
        $new_group_id = $mysqli->insert_id;
        $stmt->close();

        $stmt = $mysqli->prepare("INSERT INTO groupe_utilisateurs_membres (groupe_id, user_id, last_read_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $new_group_id, $self_id);
        $stmt->execute();
        $stmt->close();

        header('Location: mes_amis.php?tab=groupes&flash=' . urlencode('success|' . sprintf(t('groupe_perso_cree_flash'), $code)));
        exit;
    }

    // --- Rejoindre un groupe via son code ---
    if (isset($_POST['join_group'])) {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        if ($code === '') {
            header('Location: mes_amis.php?tab=groupes&flash=' . urlencode('error|' . t('groupe_code_requis')));
            exit;
        }
        $group = $mysqli->query("SELECT id, nom FROM groupes_utilisateurs WHERE code_unique = '" . $mysqli->real_escape_string($code) . "'")->fetch_assoc();
        if (!$group) {
            header('Location: mes_amis.php?tab=groupes&flash=' . urlencode('error|' . t('groupe_code_introuvable')));
            exit;
        }
        $already = $mysqli->query("SELECT 1 FROM groupe_utilisateurs_membres WHERE groupe_id = " . (int) $group['id'] . " AND user_id = $self_id")->num_rows > 0;
        if ($already) {
            header('Location: mes_amis.php?tab=groupes&flash=' . urlencode('error|' . t('groupe_deja_membre')));
            exit;
        }
        $stmt = $mysqli->prepare("INSERT INTO groupe_utilisateurs_membres (groupe_id, user_id, last_read_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $group['id'], $self_id);
        $stmt->execute();
        $stmt->close();
        header('Location: mes_amis.php?tab=groupes&flash=' . urlencode('success|' . sprintf(t('groupe_perso_rejoint_flash'), $group['nom'])));
        exit;
    }

    // --- Quitter un groupe (membre non créateur) ---
    if (isset($_POST['leave_group_id'])) {
        $gid = (int) $_POST['leave_group_id'];
        $g = $mysqli->query("SELECT createur_id FROM groupes_utilisateurs WHERE id = $gid")->fetch_assoc();
        if ($g && (int) $g['createur_id'] === $self_id) {
            header('Location: mes_amis.php?tab=groupes&flash=' . urlencode('error|' . t('groupe_perso_createur_ne_peut_quitter')));
            exit;
        }
        $mysqli->query("DELETE FROM groupe_utilisateurs_membres WHERE groupe_id = $gid AND user_id = $self_id");
        header('Location: mes_amis.php?tab=groupes&flash=' . urlencode('success|' . t('groupe_perso_quitte_flash')));
        exit;
    }

    // --- Supprimer entièrement un groupe (créateur uniquement) ---
    if (isset($_POST['delete_group_id'])) {
        $gid = (int) $_POST['delete_group_id'];
        $g = $mysqli->query("SELECT createur_id FROM groupes_utilisateurs WHERE id = $gid")->fetch_assoc();
        if (!$g || (int) $g['createur_id'] !== $self_id) {
            header('Location: mes_amis.php?tab=groupes&flash=' . urlencode('error|' . t('groupe_action_non_autorisee')));
            exit;
        }
        $att = $mysqli->query("SELECT file_path FROM groupe_utilisateurs_attachments a JOIN groupe_utilisateurs_messages m ON m.id = a.message_id WHERE m.groupe_id = $gid");
        while ($row = $att->fetch_assoc()) { groupe_perso_unlink_file($row['file_path']); }
        $mysqli->query("DELETE FROM groupes_utilisateurs WHERE id = $gid"); // cascade sur membres/messages/pièces jointes
        header('Location: mes_amis.php?tab=groupes&flash=' . urlencode('success|' . t('groupe_perso_supprime_flash')));
        exit;
    }
}

// Accès direct en GET (ancien lien/marque-page) : redirige vers le hub.
header('Location: mes_amis.php?tab=groupes');
exit;
