<?php
include_once 'language.php';
require_once 'db_connect.php';

// Accès : admin général OU compte dédié "materiel" (rôle autonome, voir admin_utilisateurs.php).
$is_admin = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'admin';
$is_materiel = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && $_SESSION['user_role'] === 'materiel';
if (!$is_admin && !$is_materiel) {
    header('Location: index.php');
    exit;
}

$etats_labels = [
    'bon_etat'    => t('materiel_etat_bon'),
    'en_marche'   => t('materiel_etat_marche'),
    'mauvais'     => t('materiel_etat_mauvais'),
    'mauvais_etat'=> t('materiel_etat_mauvais_etat'),
    'en_panne'    => t('materiel_etat_panne'),
];
$etats_icons = [
    'bon_etat'     => 'fa-circle-check',
    'en_marche'    => 'fa-bolt',
    'mauvais'      => 'fa-triangle-exclamation',
    'mauvais_etat' => 'fa-triangle-exclamation',
    'en_panne'     => 'fa-circle-xmark',
];
$etats_badges = [
    'bon_etat'     => 'admin-status-publie',
    'en_marche'    => 'admin-status-info',
    'mauvais'      => 'admin-status-brouillon',
    'mauvais_etat' => 'admin-status-brouillon',
    'en_panne'     => 'admin-status-erreur',
];
$valid_etats = array_keys($etats_labels);

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Ajouter un matériel ---
    if (isset($_POST['add_materiel'])) {
        $lieu = trim($_POST['lieu'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $quantite = max(1, (int) ($_POST['quantite'] ?? 1));
        $etat = in_array($_POST['etat'] ?? '', $valid_etats, true) ? $_POST['etat'] : 'bon_etat';
        $observation = trim($_POST['observation'] ?? '');

        if ($lieu === '' || $nom === '') {
            header('Location: admin_materiel.php?flash=' . urlencode('error|' . t('materiel_erreur_champs')));
            exit;
        }

        $stmt = $mysqli->prepare("INSERT INTO materiels (lieu, nom, quantite, etat, observation, ajoute_par) VALUES (?,?,?,?,?,?)");
        $uid = (int) ($_SESSION['user_id'] ?? 0);
        $stmt->bind_param('ssissi', $lieu, $nom, $quantite, $etat, $observation, $uid);
        $stmt->execute();
        $stmt->close();

        header('Location: admin_materiel.php?lieu=' . urlencode($lieu) . '&flash=' . urlencode('success|' . t('materiel_ajoute_ok')));
        exit;
    }

    // --- Modifier un matériel ---
    if (isset($_POST['update_materiel'])) {
        $id = (int) ($_POST['id'] ?? 0);
        $lieu = trim($_POST['lieu'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $quantite = max(1, (int) ($_POST['quantite'] ?? 1));
        $etat = in_array($_POST['etat'] ?? '', $valid_etats, true) ? $_POST['etat'] : 'bon_etat';
        $observation = trim($_POST['observation'] ?? '');

        if ($lieu === '' || $nom === '' || $id <= 0) {
            header('Location: admin_materiel.php?flash=' . urlencode('error|' . t('materiel_erreur_champs')));
            exit;
        }

        $stmt = $mysqli->prepare("UPDATE materiels SET lieu=?, nom=?, quantite=?, etat=?, observation=? WHERE id=?");
        $stmt->bind_param('ssissi', $lieu, $nom, $quantite, $etat, $observation, $id);
        $stmt->execute();
        $stmt->close();

        header('Location: admin_materiel.php?lieu=' . urlencode($lieu) . '&flash=' . urlencode('success|' . t('materiel_modifie_ok')));
        exit;
    }

    // --- Supprimer un matériel ---
    if (isset($_POST['delete_materiel_id'])) {
        $id = (int) $_POST['delete_materiel_id'];
        $lieu = trim($_POST['lieu'] ?? '');
        $stmt = $mysqli->prepare("DELETE FROM materiels WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        header('Location: admin_materiel.php?lieu=' . urlencode($lieu) . '&flash=' . urlencode('success|' . t('materiel_supprime_ok')));
        exit;
    }

    // --- Ajouter une intervention de maintenance (peut aussi mettre à jour l'état) ---
    if (isset($_POST['add_maintenance'])) {
        $materiel_id = (int) ($_POST['materiel_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $etat_apres = in_array($_POST['etat_apres'] ?? '', $valid_etats, true) ? $_POST['etat_apres'] : null;
        $lieu = trim($_POST['lieu'] ?? '');

        if ($materiel_id <= 0 || $description === '') {
            header('Location: admin_materiel.php?lieu=' . urlencode($lieu) . '&flash=' . urlencode('error|' . t('materiel_erreur_champs')));
            exit;
        }

        $uid = (int) ($_SESSION['user_id'] ?? 0);
        $stmt = $mysqli->prepare("INSERT INTO materiel_maintenance (materiel_id, description, etat_apres, cree_par) VALUES (?,?,?,?)");
        $stmt->bind_param('issi', $materiel_id, $description, $etat_apres, $uid);
        $stmt->execute();
        $stmt->close();

        if ($etat_apres !== null) {
            $stmt = $mysqli->prepare("UPDATE materiels SET etat = ? WHERE id = ?");
            $stmt->bind_param('si', $etat_apres, $materiel_id);
            $stmt->execute();
            $stmt->close();
        }

        header('Location: admin_materiel.php?lieu=' . urlencode($lieu) . '&flash=' . urlencode('success|' . t('materiel_maintenance_ok')));
        exit;
    }

    // --- Emprunter un matériel (tout ou partie de la quantité) vers un autre lieu ---
    // La fiche reste affichée dans son lieu d'origine (elle ne "déménage" plus) : seule sa
    // quantité disponible diminue, et l'emprunt actif est journalisé + affiché sur la carte.
    if (isset($_POST['emprunter_materiel'])) {
        $materiel_id = (int) ($_POST['materiel_id'] ?? 0);
        $lieu_destination = trim($_POST['lieu_destination'] ?? '');
        $emprunteur = trim($_POST['emprunteur'] ?? '');
        $motif = trim($_POST['motif'] ?? '');
        $lieu = trim($_POST['lieu'] ?? '');

        $target = $materiel_id ? $mysqli->query("SELECT lieu, quantite FROM materiels WHERE id = $materiel_id")->fetch_assoc() : null;
        $quantite_emprunt = $target ? min((int) ($_POST['quantite_emprunt'] ?? $target['quantite']), (int) $target['quantite']) : 0;

        if (!$target || $lieu_destination === '' || $lieu_destination === $target['lieu'] || $emprunteur === '' || $quantite_emprunt < 1) {
            header('Location: admin_materiel.php?lieu=' . urlencode($lieu) . '&flash=' . urlencode('error|' . t('materiel_deplacement_erreur')));
            exit;
        }

        $lieu_origine = $target['lieu'];
        $uid = (int) ($_SESSION['user_id'] ?? 0);
        $stmt = $mysqli->prepare("INSERT INTO materiel_deplacements (materiel_id, lieu_origine, lieu_destination, motif, emprunteur, quantite, cree_par) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('isssssi', $materiel_id, $lieu_origine, $lieu_destination, $motif, $emprunteur, $quantite_emprunt, $uid);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("UPDATE materiels SET quantite = quantite - ? WHERE id = ?");
        $stmt->bind_param('ii', $quantite_emprunt, $materiel_id);
        $stmt->execute();
        $stmt->close();

        header('Location: admin_materiel.php?lieu=' . urlencode($lieu) . '&flash=' . urlencode('success|' . t('materiel_deplacement_ok')));
        exit;
    }

    // --- Marquer un emprunt précis comme retourné (restitue sa quantité au lieu d'origine) ---
    if (isset($_POST['retourner_deplacement_id'])) {
        $deplacement_id = (int) $_POST['retourner_deplacement_id'];
        $lieu = trim($_POST['lieu'] ?? '');
        // Retour déclenché depuis la fenêtre "Voir l'historique" (globale) : on y renvoie
        // l'utilisateur après coup, plutôt que vers la liste d'un lieu précis.
        $retour_url = ($_POST['return_to'] ?? '') === 'historique'
            ? 'admin_materiel.php?view=historique'
            : 'admin_materiel.php?lieu=' . urlencode($lieu);

        $actif = $mysqli->query("SELECT id, materiel_id, quantite FROM materiel_deplacements WHERE id = $deplacement_id AND statut = 'en_cours'")->fetch_assoc();

        if ($actif) {
            $stmt = $mysqli->prepare("UPDATE materiel_deplacements SET statut = 'retourne', date_retour = NOW() WHERE id = ?");
            $stmt->bind_param('i', $actif['id']);
            $stmt->execute();
            $stmt->close();

            $stmt = $mysqli->prepare("UPDATE materiels SET quantite = quantite + ? WHERE id = ?");
            $stmt->bind_param('ii', $actif['quantite'], $actif['materiel_id']);
            $stmt->execute();
            $stmt->close();

            header('Location: ' . $retour_url . '&flash=' . urlencode('success|' . t('materiel_retour_ok')));
            exit;
        }

        header('Location: ' . $retour_url . '&flash=' . urlencode('error|' . t('materiel_deplacement_erreur')));
        exit;
    }

    // --- Supprimer un événement précis de l'historique des emprunts/déplacements ---
    if (isset($_POST['delete_deplacement_id'])) {
        $deplacement_id = (int) $_POST['delete_deplacement_id'];
        // Un emprunt encore en cours qu'on supprime doit d'abord restituer sa quantité au
        // matériel, sinon elle resterait perdue (comptée "empruntée" nulle part).
        $row = $mysqli->query("SELECT materiel_id, quantite, statut FROM materiel_deplacements WHERE id = $deplacement_id")->fetch_assoc();
        if ($row) {
            if ($row['statut'] === 'en_cours') {
                $stmt = $mysqli->prepare("UPDATE materiels SET quantite = quantite + ? WHERE id = ?");
                $stmt->bind_param('ii', $row['quantite'], $row['materiel_id']);
                $stmt->execute();
                $stmt->close();
            }
            $stmt = $mysqli->prepare("DELETE FROM materiel_deplacements WHERE id = ?");
            $stmt->bind_param('i', $deplacement_id);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: admin_materiel.php?view=historique&flash=' . urlencode('success|' . t('materiel_historique_supprime_ok')));
        exit;
    }

    // --- Vider entièrement l'historique des emprunts/déplacements ---
    if (isset($_POST['delete_all_historique'])) {
        // Même précaution que ci-dessus, mais pour chaque emprunt encore en cours.
        $en_cours = $mysqli->query("SELECT materiel_id, quantite FROM materiel_deplacements WHERE statut = 'en_cours'")->fetch_all(MYSQLI_ASSOC);
        $stmt = $mysqli->prepare("UPDATE materiels SET quantite = quantite + ? WHERE id = ?");
        foreach ($en_cours as $row) {
            $stmt->bind_param('ii', $row['quantite'], $row['materiel_id']);
            $stmt->execute();
        }
        $stmt->close();
        $mysqli->query("TRUNCATE TABLE materiel_deplacements");
        header('Location: admin_materiel.php?view=historique&flash=' . urlencode('success|' . t('materiel_historique_tout_supprime_ok')));
        exit;
    }
}

if (isset($_GET['flash'])) {
    [$ftype, $fmsg] = array_pad(explode('|', $_GET['flash'], 2), 2, '');
    if ($fmsg !== '') { $flash = ['type' => $ftype, 'msg' => $fmsg]; }
}

// --- Statistiques globales (toujours visibles) ---
$total_items = (int) $mysqli->query("SELECT COUNT(*) c FROM materiels")->fetch_assoc()['c'];
$total_quantite = (int) $mysqli->query("SELECT COALESCE(SUM(quantite),0) c FROM materiels")->fetch_assoc()['c'];
$total_lieux = (int) $mysqli->query("SELECT COUNT(DISTINCT lieu) c FROM materiels")->fetch_assoc()['c'];
$total_en_panne = (int) $mysqli->query("SELECT COUNT(*) c FROM materiels WHERE etat='en_panne'")->fetch_assoc()['c'];

$etat_counts = array_fill_keys($valid_etats, 0);
$res = $mysqli->query("SELECT etat, COUNT(*) c FROM materiels GROUP BY etat");
while ($row = $res->fetch_assoc()) { $etat_counts[$row['etat']] = (int) $row['c']; }

$lieux_list = [];
$res = $mysqli->query("
    SELECT lieu, COUNT(*) nb, SUM(quantite) qte, SUM(etat='en_panne') pannes,
           SUM(etat='bon_etat') c_bon_etat, SUM(etat='en_marche') c_en_marche,
           SUM(etat='mauvais') c_mauvais, SUM(etat='mauvais_etat') c_mauvais_etat,
           SUM(etat='en_panne') c_en_panne
    FROM materiels GROUP BY lieu ORDER BY lieu ASC
");
while ($row = $res->fetch_assoc()) { $lieux_list[] = $row; }

$view = $_GET['view'] ?? 'liste';
$q = trim($_GET['q'] ?? '');
$current_lieu = trim($_GET['lieu'] ?? '');
$incoming_loans = [];
// Rempli pendant le rendu des cartes ci-dessous (un emprunt actif = une entrée) ; les petites
// fenêtres correspondantes sont redessinées plus bas, hors de .container.
$all_active_loans_for_popup = [];

if ($view === 'liste' || $view === 'historique') {
    if ($q !== '') {
        $stmt = $mysqli->prepare("SELECT * FROM materiels WHERE nom LIKE ? OR lieu LIKE ? OR observation LIKE ? ORDER BY lieu ASC, nom ASC");
        $like = "%$q%";
        $stmt->bind_param('sss', $like, $like, $like);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        // Résultats de recherche multi-lieux : pas de "lieu courant" unique auquel rattacher
        // les emprunts entrants, donc pas de cartes visiteuses dans ce mode.
        $incoming_loans = [];
    } else {
        if ($current_lieu === '' && !empty($lieux_list)) {
            $current_lieu = $lieux_list[0]['lieu'];
        }
        if ($current_lieu !== '') {
            $stmt = $mysqli->prepare("SELECT * FROM materiels WHERE lieu = ? ORDER BY nom ASC");
            $stmt->bind_param('s', $current_lieu);
            $stmt->execute();
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Matériel emprunté à CE lieu depuis un autre (le lieu de destination d'un emprunt en
            // cours) : affiché en plus des fiches "chez elles" ici, pour qu'on voie aussi ce qui est
            // physiquement présent dans ce lieu sans y appartenir à l'origine.
            $stmt = $mysqli->prepare("
                SELECT d.*, m.nom AS materiel_nom, m.etat AS materiel_etat, m.observation AS materiel_observation
                FROM materiel_deplacements d
                JOIN materiels m ON m.id = d.materiel_id
                WHERE d.statut = 'en_cours' AND d.lieu_destination = ?
                ORDER BY d.date_deplacement DESC
            ");
            $stmt->bind_param('s', $current_lieu);
            $stmt->execute();
            $incoming_loans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $items = [];
            $incoming_loans = [];
        }
    }

    // Historique de maintenance des matériels affichés (une seule requête, pas de N+1)
    $maintenance_by_item = [];
    if (!empty($items)) {
        $ids = array_column($items, 'id');
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = $mysqli->prepare("SELECT * FROM materiel_maintenance WHERE materiel_id IN ($ph) ORDER BY created_at DESC");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $res2 = $stmt->get_result();
        while ($row = $res2->fetch_assoc()) { $maintenance_by_item[$row['materiel_id']][] = $row; }
        $stmt->close();

        // Suivi des déplacements/emprunts (historique complet + emprunts actifs, une seule requête).
        // Plusieurs emprunts partiels simultanés sont possibles sur un même matériel (ex : 2 chaises
        // prêtées à la Salle R2 et 1 au Labo GT en même temps) : $emprunts_actifs_by_item est donc
        // un tableau de tableaux, pas une seule ligne.
        $deplacements_by_item = [];
        $emprunts_actifs_by_item = [];
        $stmt = $mysqli->prepare("SELECT * FROM materiel_deplacements WHERE materiel_id IN ($ph) ORDER BY date_deplacement DESC");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $res3 = $stmt->get_result();
        while ($row = $res3->fetch_assoc()) {
            $deplacements_by_item[$row['materiel_id']][] = $row;
            if ($row['statut'] === 'en_cours') {
                $emprunts_actifs_by_item[$row['materiel_id']][] = $row;
            }
        }
        $stmt->close();
    }
}

// Historique global (fenêtre "Voir l'historique") : tous les emprunts/déplacements de TOUS les
// matériels, tous lieux confondus, les plus récents en premier — pas seulement ceux du lieu
// actuellement affiché en arrière-plan.
$historique_globale = [];
if ($view === 'historique') {
    $res = $mysqli->query("
        SELECT d.*, m.nom AS materiel_nom, m.lieu AS materiel_lieu_actuel, m.quantite AS materiel_quantite_actuelle
        FROM materiel_deplacements d
        JOIN materiels m ON m.id = d.materiel_id
        ORDER BY d.date_deplacement DESC
        LIMIT 100
    ");
    $historique_globale = $res->fetch_all(MYSQLI_ASSOC);
}

$page_title = t('materiel_titre');
include 'header.php';
?>

<?php if ($is_admin): ?>
<a href="administrateur.php" class="btn-outline btn-back-sticky"><i class="fas fa-arrow-left"></i> <?php echo t('admin_retour_tableau_bord'); ?></a>
<?php endif; ?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('materiel_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-boxes-stacked"></i> <?php echo t('materiel_titre'); ?></h1>
        <p><?php echo t('materiel_soustitre'); ?></p>
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

        <div class="admin-galerie-stats">
            <div class="admin-stat-card animate-on-scroll"><i class="fas fa-boxes-stacked"></i><div><strong><?php echo $total_items; ?></strong><span><?php echo t('materiel_stat_references'); ?></span></div></div>
            <div class="admin-stat-card animate-on-scroll" style="--delay:0.05s"><i class="fas fa-layer-group"></i><div><strong><?php echo $total_quantite; ?></strong><span><?php echo t('materiel_stat_quantite'); ?></span></div></div>
            <div class="admin-stat-card animate-on-scroll" style="--delay:0.1s"><i class="fas fa-location-dot"></i><div><strong><?php echo $total_lieux; ?></strong><span><?php echo t('materiel_stat_lieux'); ?></span></div></div>
            <div class="admin-stat-card admin-stat-card-alert animate-on-scroll" style="--delay:0.15s"><i class="fas fa-circle-xmark"></i><div><strong><?php echo $total_en_panne; ?></strong><span><?php echo t('materiel_stat_pannes'); ?></span></div></div>
        </div>

        <div class="admin-galerie-toolbar etu-tabs">
            <a href="admin_materiel.php" class="btn-outline <?php echo $view !== 'stats' ? 'is-active' : ''; ?>"><i class="fas fa-list"></i> <?php echo t('materiel_onglet_liste'); ?></a>
            <a href="admin_materiel.php?view=stats" class="btn-outline <?php echo $view === 'stats' ? 'is-active' : ''; ?>"><i class="fas fa-chart-pie"></i> <?php echo t('materiel_onglet_stats'); ?></a>
            <a href="admin_materiel.php?view=historique" class="btn-outline"><i class="fas fa-clock-rotate-left"></i> <?php echo t('materiel_voir_historique_btn'); ?></a>
        </div>

        <?php if ($view === 'stats'):
            // Palette alignée sur les badges d'état utilisés partout ailleurs sur la page
            // (cartes, historique), pour que la même couleur désigne toujours le même état.
            $etat_colors = [
                'bon_etat'     => '#2ecc71',
                'en_marche'    => '#3498db',
                'mauvais'      => '#f39c12',
                'mauvais_etat' => '#e67e22',
                'en_panne'     => '#e74c3c',
            ];

            $cumulative = 0;
            $conic_stops = [];
            foreach ($etat_counts as $key => $cnt) {
                if ($cnt === 0) { continue; }
                $start = $total_items > 0 ? round($cumulative / $total_items * 360, 2) : 0;
                $cumulative += $cnt;
                $end = $total_items > 0 ? round($cumulative / $total_items * 360, 2) : 0;
                $conic_stops[] = $etat_colors[$key] . " {$start}deg {$end}deg";
            }
            $conic_gradient = !empty($conic_stops) ? 'conic-gradient(' . implode(', ', $conic_stops) . ')' : 'conic-gradient(var(--border-color) 0deg 360deg)';

            // Un classement (top 12, lieux les plus concernés en premier) par état, calculé une
            // fois pour les 5 boutons de la section "État par lieu" ci-dessous — chaque bouton
            // n'affiche que SON état (jamais les 5 empilés ensemble). "reste" complète avec les
            // lieux qui ne rentrent pas dans le top 12, pour que TOUS les lieux restent visibles
            // quelque part (rien n'est vraiment caché, juste réparti en deux rangées).
            $etat_chart_data = [];
            foreach ($etat_colors as $key => $color) {
                $sorted = $lieux_list;
                usort($sorted, fn($a, $b) => (int) $b['c_' . $key] <=> (int) $a['c_' . $key]);
                $max = 1;
                foreach ($lieux_list as $l) { $max = max($max, (int) $l['c_' . $key]); }
                $etat_chart_data[$key] = [
                    'top'   => array_slice($sorted, 0, 12),
                    'reste' => array_slice($sorted, 12),
                    'max'   => $max,
                ];
            }
        ?>
            <div class="etu-stats-grid animate-on-scroll">
                <div class="etu-stats-card">
                    <h3><i class="fas fa-chart-pie"></i> <?php echo t('materiel_stats_repartition'); ?></h3>
                    <div class="etu-donut-wrap">
                        <div class="etu-donut" style="background: <?php echo $conic_gradient; ?>;">
                            <div class="etu-donut-hole"><strong><?php echo $total_items; ?></strong><span><?php echo t('materiel_stat_references'); ?></span></div>
                        </div>
                        <ul class="etu-legend">
                            <?php foreach ($etat_counts as $key => $cnt):
                                if ($cnt === 0) { continue; }
                                $pct = $total_items > 0 ? round($cnt / $total_items * 100) : 0;
                            ?>
                                <li><span class="etu-legend-dot" style="background: <?php echo $etat_colors[$key]; ?>;"></span> <?php echo htmlspecialchars($etats_labels[$key]); ?> — <strong><?php echo $cnt; ?></strong> (<?php echo $pct; ?>%)</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="etu-stats-card etu-stats-card-wide">
                    <div class="materiel-stats-chart-header">
                        <h3><i class="fas fa-chart-column"></i> <span id="materiel-chart-title"><?php echo htmlspecialchars(sprintf(t('materiel_stats_etat_par_lieu'), $etats_labels['en_panne'])); ?></span></h3>
                        <div class="materiel-stats-chart-toggle">
                            <?php foreach ($etat_colors as $key => $color): ?>
                                <button type="button" class="btn-outline<?php echo $key === 'en_panne' ? ' is-active' : ''; ?>" data-chart="<?php echo $key; ?>" style="--etat-color: <?php echo $color; ?>"><span class="materiel-toggle-dot" style="background: <?php echo $color; ?>"></span> <?php echo htmlspecialchars($etats_labels[$key]); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php
                    // Un seul rendu de rangée de barres, réutilisé pour "top 12" et pour "autres
                    // lieux" ci-dessous — évite de dupliquer la logique de hauteur/plancher.
                    $render_etat_bars = function ($lieux_group, $key, $color, $max) {
                        foreach ($lieux_group as $l):
                            $cnt = (int) $l['c_' . $key];
                            // Pas de "plancher" visuel quand le compteur est à 0 : une barre colorée
                            // pour 0 serait trompeuse (un lieu sans matériel dans cet état doit
                            // apparaître visuellement vide, pas comme s'il y avait un souci).
                            $group_height = $cnt > 0 ? max(round($cnt / $max * 100), 4) : 0;
                            ?>
                            <div class="etu-barchart-group">
                                <div class="etu-barchart-track">
                                    <div class="etu-barchart-stack" style="height: <?php echo $group_height; ?>%;">
                                        <div class="etu-barchart-segment" style="height: 100%; background: <?php echo $color; ?>;" title="<?php echo htmlspecialchars($l['lieu']) . ' : ' . $cnt; ?>"></div>
                                    </div>
                                </div>
                                <span class="etu-barchart-label"><?php echo htmlspecialchars($l['lieu']); ?> (<?php echo $cnt; ?>)</span>
                            </div>
                        <?php endforeach;
                    };
                    ?>
                    <?php foreach ($etat_colors as $key => $color):
                        $data = $etat_chart_data[$key];
                    ?>
                        <div id="materiel-chart-<?php echo $key; ?>" <?php echo $key !== 'en_panne' ? 'hidden' : ''; ?>>
                            <?php if ($data['max'] <= 1 && $etat_counts[$key] === 0): ?>
                                <p class="gallery-empty"><i class="fas fa-circle-info"></i> <?php echo t('materiel_aucun_dans_etat'); ?></p>
                            <?php else: ?>
                                <div class="etu-barchart">
                                    <?php $render_etat_bars($data['top'], $key, $color, $data['max']); ?>
                                </div>
                                <?php if (!empty($data['reste'])): ?>
                                    <p class="materiel-stats-subtitle"><i class="fas fa-ellipsis"></i> <?php echo sprintf(t('materiel_stats_autres_lieux'), count($data['reste'])); ?></p>
                                    <div class="etu-barchart etu-barchart-secondary">
                                        <?php $render_etat_bars($data['reste'], $key, $color, $data['max']); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php else: ?>

            <form action="admin_materiel.php" method="GET" class="etu-search-form">
                <div class="etu-search-input-wrap">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="search" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="<?php echo t('materiel_recherche_placeholder'); ?>">
                </div>
                <button type="submit" class="btn-outline"><i class="fas fa-filter"></i> <?php echo t('admin_etudiants_filtrer'); ?></button>
                <?php if ($q !== ''): ?><a href="admin_materiel.php" class="btn-outline"><i class="fas fa-xmark"></i> <?php echo t('materiel_reset_recherche'); ?></a><?php endif; ?>
            </form>

            <?php if ($q === ''): ?>
                <div class="banner-page-picker">
                    <?php foreach ($lieux_list as $l): ?>
                        <a href="admin_materiel.php?lieu=<?php echo urlencode($l['lieu']); ?>" class="banner-page-picker-item<?php echo $l['lieu'] === $current_lieu ? ' is-active' : ''; ?>">
                            <?php echo htmlspecialchars($l['lieu']); ?> <span class="materiel-picker-count"><?php echo (int) $l['nb']; ?></span>
                            <?php if ((int) $l['pannes'] > 0): ?><i class="fas fa-circle-xmark materiel-picker-panne-dot" title="<?php echo (int) $l['pannes']; ?> en panne"></i><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="materiel-add-form-wrap animate-on-scroll">
                <h2 class="admin-section-title"><i class="fas fa-plus"></i> <?php echo t('materiel_ajouter_titre'); ?></h2>
                <form action="admin_materiel.php" method="POST" class="admin-form materiel-add-form">
                    <div class="form-group">
                        <label><?php echo t('materiel_champ_lieu'); ?></label>
                        <input type="text" name="lieu" list="materiel-lieux-list" value="<?php echo htmlspecialchars($current_lieu); ?>" required>
                        <datalist id="materiel-lieux-list">
                            <?php foreach ($lieux_list as $l): ?><option value="<?php echo htmlspecialchars($l['lieu']); ?>"><?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('materiel_champ_nom'); ?></label>
                        <input type="text" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('materiel_champ_quantite'); ?></label>
                        <input type="number" name="quantite" value="1" min="1" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('materiel_champ_etat'); ?></label>
                        <select name="etat">
                            <?php foreach ($etats_labels as $key => $label): ?>
                                <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('materiel_champ_observation'); ?></label>
                        <input type="text" name="observation" placeholder="<?php echo t('materiel_champ_observation_placeholder'); ?>">
                    </div>
                    <button type="submit" name="add_materiel" class="btn-add-item"><i class="fas fa-plus"></i> <?php echo t('materiel_ajouter_btn'); ?></button>
                </form>
            </div>

            <?php if (empty($items) && empty($incoming_loans)): ?>
                <p class="gallery-empty"><i class="fas fa-boxes-stacked"></i> <?php echo t('materiel_aucun'); ?></p>
            <?php else: ?>
                <div class="materiel-grid">
                    <?php if (!empty($incoming_loans)): ?>
                        <?php foreach ($incoming_loans as $i => $loan): ?>
                            <div class="materiel-card materiel-card-emprunte animate-on-scroll" style="--delay: <?php echo min($i * 0.04, 0.6); ?>s">
                                <div class="materiel-card-head">
                                    <h3><?php echo htmlspecialchars($loan['materiel_nom']); ?></h3>
                                    <span class="admin-status-badge <?php echo $etats_badges[$loan['materiel_etat']]; ?>"><i class="fas <?php echo $etats_icons[$loan['materiel_etat']]; ?>"></i> <?php echo htmlspecialchars($etats_labels[$loan['materiel_etat']]); ?></span>
                                </div>
                                <span class="materiel-emprunt-stamp materiel-emprunt-stamp-inline"><?php echo t('materiel_stamp_emprunte'); ?></span>
                                <div class="materiel-card-body">
                                    <span><i class="fas fa-route"></i> <?php echo sprintf(t('materiel_carte_provenance'), htmlspecialchars($loan['lieu_origine'])); ?></span>
                                    <span><i class="fas fa-layer-group"></i> <?php echo t('materiel_quantite_label'); ?> : <strong><?php echo (int) $loan['quantite']; ?></strong></span>
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($loan['emprunteur']); ?></span>
                                    <?php if (!empty($loan['motif'])): ?>
                                        <span class="materiel-card-obs"><i class="fas fa-note-sticky"></i> <?php echo htmlspecialchars($loan['motif']); ?></span>
                                    <?php endif; ?>
                                    <span class="materiel-emprunt-banner-date"><i class="far fa-clock"></i> <?php echo date('d/m/Y à H:i', strtotime($loan['date_deplacement'])); ?></span>
                                </div>
                                <div class="materiel-card-actions">
                                    <a href="admin_materiel.php?lieu=<?php echo urlencode($loan['lieu_origine']); ?>" class="btn-outline" title="<?php echo t('materiel_voir_lieu_origine'); ?>"><i class="fas fa-arrow-left"></i></a>
                                    <form action="admin_materiel.php" method="POST">
                                        <input type="hidden" name="lieu" value="<?php echo htmlspecialchars($current_lieu); ?>">
                                        <button type="submit" name="retourner_deplacement_id" value="<?php echo $loan['id']; ?>" class="btn-outline materiel-retour-btn"><i class="fas fa-rotate-left"></i> <?php echo t('materiel_retourner_btn'); ?></button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php foreach ($items as $i => $item):
                        $etat = $item['etat'];
                        $maint = $maintenance_by_item[$item['id']] ?? [];
                        $depl = $deplacements_by_item[$item['id']] ?? [];
                        $emprunts_actifs = $emprunts_actifs_by_item[$item['id']] ?? [];
                        $row_delay = min($i * 0.04, 0.6);
                    ?>
                        <div class="materiel-card materiel-etat-<?php echo $etat; ?> animate-on-scroll" style="--delay: <?php echo $row_delay; ?>s">
                            <div class="materiel-card-head">
                                <h3><?php echo htmlspecialchars($item['nom']); ?></h3>
                                <span class="admin-status-badge <?php echo $etats_badges[$etat]; ?>"><i class="fas <?php echo $etats_icons[$etat]; ?>"></i> <?php echo htmlspecialchars($etats_labels[$etat]); ?></span>
                            </div>
                            <div class="materiel-card-body">
                                <span><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($item['lieu']); ?></span>
                                <span><i class="fas fa-layer-group"></i> <?php echo t('materiel_quantite_label'); ?> : <strong><?php echo (int) $item['quantite']; ?></strong></span>
                                <?php if (!empty($item['observation'])): ?>
                                    <span class="materiel-card-obs"><i class="fas fa-note-sticky"></i> <?php echo htmlspecialchars($item['observation']); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($emprunts_actifs)): ?>
                                <div class="materiel-emprunt-stamps">
                                    <?php foreach ($emprunts_actifs as $e):
                                        // Collectées ici et redessinées plus bas, HORS de .container (voir le
                                        // commentaire sur les fenêtres modales) : une petite fenêtre par emprunt,
                                        // ouverte au clic sur son tampon, avec le détail + le bouton "Retourner".
                                        $all_active_loans_for_popup[] = ['loan' => $e, 'item' => $item];
                                    ?>
                                        <button type="button" class="materiel-emprunt-stamp materiel-emprunt-stamp-btn" data-target="materiel-emprunt-popup-<?php echo $e['id']; ?>"><?php echo t('materiel_stamp_emprunte'); ?></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="materiel-card-actions">
                                <a href="admin_materiel.php?view=edit&id=<?php echo $item['id']; ?>&lieu=<?php echo urlencode($current_lieu); ?>" class="btn-outline" title="<?php echo t('admin_modifier'); ?>"><i class="fas fa-pen"></i></a>
                                <button type="button" class="btn-outline materiel-toggle-maintenance" data-target="materiel-maint-<?php echo $item['id']; ?>" title="<?php echo t('materiel_historique_btn'); ?>"><i class="fas fa-clock-rotate-left"></i> <?php echo count($maint); ?></button>
                                <button type="button" class="btn-outline materiel-toggle-maintenance" data-target="materiel-depl-<?php echo $item['id']; ?>" title="<?php echo t('materiel_deplacement_btn'); ?>"><i class="fas fa-truck-fast"></i> <?php echo count($depl); ?></button>
                                <form action="admin_materiel.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('materiel_confirm_suppr')); ?>">
                                    <input type="hidden" name="lieu" value="<?php echo htmlspecialchars($current_lieu); ?>">
                                    <button type="submit" name="delete_materiel_id" value="<?php echo $item['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </div>

                            <div class="materiel-maintenance-panel" id="materiel-maint-<?php echo $item['id']; ?>" hidden>
                                <h4 class="materiel-panel-title"><i class="fas fa-wrench"></i> <?php echo t('materiel_historique_btn'); ?></h4>
                                <?php if (!empty($maint)): ?>
                                    <ul class="materiel-maintenance-list">
                                        <?php foreach ($maint as $m): ?>
                                            <li>
                                                <span class="materiel-maintenance-date"><?php echo date('d/m/Y à H:i', strtotime($m['created_at'])); ?></span>
                                                <span><?php echo htmlspecialchars($m['description']); ?></span>
                                                <?php if (!empty($m['etat_apres'])): ?>
                                                    <span class="admin-status-badge <?php echo $etats_badges[$m['etat_apres']]; ?>"><?php echo htmlspecialchars($etats_labels[$m['etat_apres']]); ?></span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="materiel-maintenance-empty"><?php echo t('materiel_historique_vide'); ?></p>
                                <?php endif; ?>
                                <form action="admin_materiel.php" method="POST" class="materiel-maintenance-form">
                                    <input type="hidden" name="materiel_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="lieu" value="<?php echo htmlspecialchars($current_lieu); ?>">
                                    <input type="text" name="description" placeholder="<?php echo t('materiel_maintenance_placeholder'); ?>" required>
                                    <select name="etat_apres">
                                        <option value=""><?php echo t('materiel_maintenance_etat_inchange'); ?></option>
                                        <?php foreach ($etats_labels as $key => $label): ?>
                                            <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="add_maintenance" class="btn-outline"><i class="fas fa-wrench"></i> <?php echo t('materiel_maintenance_ajouter_btn'); ?></button>
                                </form>
                            </div>

                            <div class="materiel-maintenance-panel" id="materiel-depl-<?php echo $item['id']; ?>" hidden>
                                <h4 class="materiel-panel-title"><i class="fas fa-route"></i> <?php echo t('materiel_deplacement_historique_titre'); ?></h4>
                                <?php if (!empty($depl)): ?>
                                    <ul class="materiel-maintenance-list">
                                        <?php foreach ($depl as $d): ?>
                                            <li>
                                                <span class="materiel-maintenance-date"><?php echo date('d/m/Y à H:i', strtotime($d['date_deplacement'])); ?></span>
                                                <span>
                                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($d['emprunteur'] ?: '—'); ?> —
                                                    <?php echo (int) $d['quantite']; ?>x <?php echo htmlspecialchars($item['nom']); ?> :
                                                    <?php echo htmlspecialchars($d['lieu_origine']); ?> <i class="fas fa-arrow-right"></i> <?php echo htmlspecialchars($d['lieu_destination']); ?>
                                                    <?php if (!empty($d['motif'])): ?> — <?php echo htmlspecialchars($d['motif']); ?><?php endif; ?>
                                                </span>
                                                <?php if ($d['statut'] === 'en_cours'): ?>
                                                    <span class="admin-status-badge admin-status-info"><?php echo t('materiel_statut_en_cours'); ?></span>
                                                <?php else: ?>
                                                    <span class="admin-status-badge admin-status-publie"><?php echo t('materiel_statut_retourne'); ?> (<?php echo date('d/m/Y à H:i', strtotime($d['date_retour'])); ?>)</span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="materiel-maintenance-empty"><?php echo t('materiel_deplacement_vide'); ?></p>
                                <?php endif; ?>
                                <form action="admin_materiel.php" method="POST" class="materiel-maintenance-form">
                                    <input type="hidden" name="materiel_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="lieu" value="<?php echo htmlspecialchars($current_lieu); ?>">
                                    <input type="text" name="emprunteur" placeholder="<?php echo t('materiel_deplacement_emprunteur_placeholder'); ?>" required>
                                    <input type="text" name="lieu_destination" list="materiel-lieux-list" placeholder="<?php echo t('materiel_deplacement_destination_placeholder'); ?>" required>
                                    <input type="number" name="quantite_emprunt" value="<?php echo (int) $item['quantite']; ?>" min="1" max="<?php echo (int) $item['quantite']; ?>" title="<?php echo t('materiel_champ_quantite'); ?>">
                                    <input type="text" name="motif" placeholder="<?php echo t('materiel_deplacement_motif_placeholder'); ?>">
                                    <button type="submit" name="emprunter_materiel" class="btn-outline" <?php echo (int) $item['quantite'] < 1 ? 'disabled' : ''; ?>><i class="fas fa-truck-fast"></i> <?php echo t('materiel_deplacement_btn'); ?></button>
                                </form>
                                <?php if ((int) $item['quantite'] < 1): ?>
                                    <p class="materiel-maintenance-empty"><?php echo t('materiel_quantite_epuisee'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>

<?php
// Les deux fenêtres modales ci-dessous (édition / historique global) sont délibérément rendues
// EN DEHORS de .page-content > .container (position:fixed + z-index:2000 sur .materiel-edit-overlay
// ne suffit pas à les faire passer au-dessus de la bannière : .page-content > .container a son
// propre z-index:2, ce qui piège tout ce qu'il contient — y compris un enfant à z-index:2000 —
// dans ce même niveau 2 vu de l'extérieur, alors que .page-banner .container est à z-index:3 et
// passait donc PAR-DESSUS la fenêtre modale, quel que soit son propre z-index interne. En les
// sortant de ce conteneur, elles redeviennent de simples enfants de <body> et leur z-index élevé
// s'applique enfin normalement.
?>


<?php foreach ($all_active_loans_for_popup as $entry):
    $e = $entry['loan'];
    $loan_item = $entry['item'];
?>
    <div class="materiel-edit-overlay materiel-emprunt-popup-overlay" id="materiel-emprunt-popup-<?php echo $e['id']; ?>" hidden>
        <div class="materiel-edit-modal materiel-emprunt-popup-modal animate-on-scroll">
            <div class="materiel-historique-modal-head">
                <h2 class="admin-section-title"><i class="fas fa-right-left"></i> <?php echo t('materiel_emprunt_popup_titre'); ?></h2>
                <button type="button" class="materiel-historique-close materiel-popup-close" data-target="materiel-emprunt-popup-<?php echo $e['id']; ?>"><i class="fas fa-xmark"></i></button>
            </div>
            <p class="materiel-emprunt-popup-text">
                <?php echo sprintf(
                    t('materiel_emprunt_phrase'),
                    '<strong>' . htmlspecialchars($loan_item['nom']) . '</strong>',
                    '<strong>' . htmlspecialchars($e['emprunteur']) . '</strong>',
                    '<strong>' . htmlspecialchars($e['lieu_destination']) . '</strong>',
                    '<strong>' . (int) $e['quantite'] . '</strong>',
                    (int) $loan_item['quantite'],
                    '<strong>' . htmlspecialchars($loan_item['lieu']) . '</strong>'
                ); ?>
            </p>
            <?php if (!empty($e['motif'])): ?>
                <p class="materiel-card-obs"><i class="fas fa-note-sticky"></i> <?php echo htmlspecialchars($e['motif']); ?></p>
            <?php endif; ?>
            <span class="materiel-emprunt-banner-date"><i class="far fa-clock"></i> <?php echo date('d/m/Y à H:i', strtotime($e['date_deplacement'])); ?></span>
            <div class="materiel-edit-actions">
                <form action="admin_materiel.php" method="POST">
                    <input type="hidden" name="lieu" value="<?php echo htmlspecialchars($current_lieu); ?>">
                    <button type="submit" name="retourner_deplacement_id" value="<?php echo $e['id']; ?>" class="btn-add-item materiel-retour-btn"><i class="fas fa-rotate-left"></i> <?php echo t('materiel_retourner_btn'); ?></button>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php if ($view === 'edit'):
    $edit_id = (int) ($_GET['id'] ?? 0);
    $edit_item = $edit_id ? $mysqli->query("SELECT * FROM materiels WHERE id = $edit_id")->fetch_assoc() : null;
    if ($edit_item):
?>
    <div class="materiel-edit-overlay" id="materiel-edit-overlay">
        <div class="materiel-edit-modal animate-on-scroll">
            <h2 class="admin-section-title"><i class="fas fa-pen"></i> <?php echo t('materiel_modifier_titre'); ?></h2>
            <form action="admin_materiel.php" method="POST" class="admin-form">
                <input type="hidden" name="id" value="<?php echo (int) $edit_item['id']; ?>">
                <div class="form-group">
                    <label><?php echo t('materiel_champ_lieu'); ?></label>
                    <input type="text" name="lieu" list="materiel-lieux-list" value="<?php echo htmlspecialchars($edit_item['lieu']); ?>" required>
                </div>
                <div class="form-group">
                    <label><?php echo t('materiel_champ_nom'); ?></label>
                    <input type="text" name="nom" value="<?php echo htmlspecialchars($edit_item['nom']); ?>" required>
                </div>
                <div class="form-group">
                    <label><?php echo t('materiel_champ_quantite'); ?></label>
                    <input type="number" name="quantite" value="<?php echo (int) $edit_item['quantite']; ?>" min="1" required>
                </div>
                <div class="form-group">
                    <label><?php echo t('materiel_champ_etat'); ?></label>
                    <select name="etat">
                        <?php foreach ($etats_labels as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo $edit_item['etat'] === $key ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo t('materiel_champ_observation'); ?></label>
                    <input type="text" name="observation" value="<?php echo htmlspecialchars($edit_item['observation'] ?? ''); ?>">
                </div>
                <div class="materiel-edit-actions">
                    <a href="admin_materiel.php?lieu=<?php echo urlencode($_GET['lieu'] ?? $edit_item['lieu']); ?>" class="btn-outline"><?php echo t('materiel_annuler'); ?></a>
                    <button type="submit" name="update_materiel" class="btn-add-item"><i class="fas fa-save"></i> <?php echo t('admin_enregistrer_modifications'); ?></button>
                </div>
            </form>
        </div>
    </div>
<?php endif; endif; ?>

<?php if ($view === 'historique'): ?>
    <div class="materiel-edit-overlay" id="materiel-historique-overlay">
        <div class="materiel-edit-modal materiel-historique-modal animate-on-scroll">
            <div class="materiel-historique-modal-head">
                <h2 class="admin-section-title"><i class="fas fa-clock-rotate-left"></i> <?php echo t('materiel_historique_globale_titre'); ?></h2>
                <a href="admin_materiel.php<?php echo $current_lieu !== '' ? '?lieu=' . urlencode($current_lieu) : ''; ?>" class="materiel-historique-close" title="<?php echo t('materiel_annuler'); ?>"><i class="fas fa-xmark"></i></a>
            </div>

            <?php if (empty($historique_globale)): ?>
                <p class="gallery-empty"><i class="fas fa-boxes-stacked"></i> <?php echo t('materiel_deplacement_vide'); ?></p>
            <?php else: ?>
                <div class="materiel-historique-filtres">
                    <button type="button" class="btn-outline is-active" data-statut-filtre="tous"><?php echo t('materiel_historique_filtre_tous'); ?></button>
                    <button type="button" class="btn-outline" data-statut-filtre="en_cours"><?php echo t('materiel_historique_filtre_en_cours'); ?></button>
                    <button type="button" class="btn-outline" data-statut-filtre="retourne"><?php echo t('materiel_historique_filtre_retourne'); ?></button>
                    <form action="admin_materiel.php" method="POST" class="js-confirm-submit materiel-historique-tout-supprimer" data-confirm-msg="<?php echo htmlspecialchars(t('materiel_historique_tout_supprimer_confirm')); ?>">
                        <button type="submit" name="delete_all_historique" class="btn-delete"><i class="fas fa-trash-alt"></i> <?php echo t('materiel_historique_tout_supprimer'); ?></button>
                    </form>
                </div>
                <div class="materiel-historique-toolbar">
                    <div class="etu-search-input-wrap">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="search" id="materiel-historique-search" placeholder="<?php echo t('materiel_historique_recherche_placeholder'); ?>">
                    </div>
                    <select id="materiel-historique-sort">
                        <option value="date_desc"><?php echo t('materiel_historique_tri_date_desc'); ?></option>
                        <option value="date_asc"><?php echo t('materiel_historique_tri_date_asc'); ?></option>
                        <option value="nom"><?php echo t('materiel_historique_tri_nom'); ?></option>
                        <option value="lieu"><?php echo t('materiel_historique_tri_lieu'); ?></option>
                        <option value="emprunteur"><?php echo t('materiel_historique_tri_emprunteur'); ?></option>
                        <option value="statut"><?php echo t('materiel_historique_tri_statut'); ?></option>
                    </select>
                </div>
                <p class="materiel-historique-empty-search" id="materiel-historique-empty-search" hidden><i class="fas fa-magnifying-glass"></i> <?php echo t('materiel_historique_recherche_vide'); ?></p>
                <ul class="materiel-historique-globale-list" id="materiel-historique-list">
                    <?php foreach ($historique_globale as $d):
                        $search_blob = mb_strtolower($d['materiel_nom'] . ' ' . $d['emprunteur'] . ' ' . $d['lieu_destination'] . ' ' . $d['materiel_lieu_actuel'] . ' ' . ($d['motif'] ?? '') . ' ' . $etats_labels[$d['statut'] === 'en_cours' ? 'en_marche' : 'bon_etat']);
                    ?>
                        <li
                            data-search="<?php echo htmlspecialchars($search_blob); ?>"
                            data-nom="<?php echo htmlspecialchars(mb_strtolower($d['materiel_nom'])); ?>"
                            data-lieu="<?php echo htmlspecialchars(mb_strtolower($d['materiel_lieu_actuel'])); ?>"
                            data-emprunteur="<?php echo htmlspecialchars(mb_strtolower($d['emprunteur'])); ?>"
                            data-statut="<?php echo htmlspecialchars($d['statut']); ?>"
                            data-date="<?php echo strtotime($d['date_deplacement']); ?>"
                        >
                            <?php if ($d['statut'] === 'en_cours'): ?>
                                <span class="admin-status-badge admin-status-info"><?php echo t('materiel_statut_en_cours'); ?></span>
                                <p>
                                    <?php echo sprintf(
                                        t('materiel_emprunt_phrase'),
                                        '<strong>' . htmlspecialchars($d['materiel_nom']) . '</strong>',
                                        '<strong>' . htmlspecialchars($d['emprunteur']) . '</strong>',
                                        '<strong>' . htmlspecialchars($d['lieu_destination']) . '</strong>',
                                        '<strong>' . (int) $d['quantite'] . '</strong>',
                                        (int) $d['materiel_quantite_actuelle'],
                                        '<strong>' . htmlspecialchars($d['materiel_lieu_actuel']) . '</strong>'
                                    ); ?>
                                </p>
                            <?php else: ?>
                                <span class="admin-status-badge admin-status-publie"><?php echo t('materiel_statut_retourne'); ?></span>
                                <p>
                                    <?php echo sprintf(
                                        t('materiel_retour_phrase'),
                                        '<strong>' . htmlspecialchars($d['materiel_nom']) . '</strong>',
                                        '<strong>' . htmlspecialchars($d['emprunteur']) . '</strong>',
                                        '<strong>' . htmlspecialchars($d['lieu_destination']) . '</strong>',
                                        date('d/m/Y à H:i', strtotime($d['date_retour']))
                                    ); ?>
                                </p>
                            <?php endif; ?>
                            <div class="materiel-historique-globale-footer">
                                <span class="materiel-emprunt-banner-date"><i class="far fa-clock"></i> <?php echo date('d/m/Y à H:i', strtotime($d['date_deplacement'])); ?></span>
                                <div class="materiel-historique-globale-actions">
                                    <?php if ($d['statut'] === 'en_cours'): ?>
                                        <form action="admin_materiel.php" method="POST">
                                            <input type="hidden" name="return_to" value="historique">
                                            <button type="submit" name="retourner_deplacement_id" value="<?php echo $d['id']; ?>" class="btn-outline materiel-retour-btn"><i class="fas fa-rotate-left"></i> <?php echo t('materiel_retourner_btn'); ?></button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="admin_materiel.php" method="POST" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('materiel_historique_supprimer_confirm')); ?>">
                                        <button type="submit" name="delete_deplacement_id" value="<?php echo $d['id']; ?>" class="btn-delete" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (count($historique_globale) >= 100): ?>
                    <p class="materiel-maintenance-empty"><?php echo t('materiel_historique_globale_limite'); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.materiel-toggle-maintenance').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(btn.dataset.target);
            if (target) target.hidden = !target.hidden;
        });
    });

    // Tampon "Emprunté" cliquable sur une carte : ouvre sa petite fenêtre de détail (fermeture
    // par la croix, par un clic sur le fond sombre, ou par la touche Échap).
    document.querySelectorAll('.materiel-emprunt-stamp-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(btn.dataset.target);
            if (target) target.hidden = false;
        });
    });
    document.querySelectorAll('.materiel-emprunt-popup-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.hidden = true;
        });
    });
    document.querySelectorAll('.materiel-popup-close').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(btn.dataset.target);
            if (target) target.hidden = true;
        });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.materiel-emprunt-popup-overlay:not([hidden])').forEach(function (overlay) {
            overlay.hidden = true;
        });
    });

    // Bascule entre les 5 diagrammes "[État] par lieu" (vue statistiques) : un seul état
    // affiché à la fois, jamais les 5 empilés ensemble.
    var chartTitleTpl = <?php echo json_encode(t('materiel_stats_etat_par_lieu')); ?>;
    var chartLabels = <?php echo json_encode($etats_labels); ?>;
    var chartTitleEl = document.getElementById('materiel-chart-title');
    document.querySelectorAll('.materiel-stats-chart-toggle .btn-outline').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var chart = btn.dataset.chart;
            document.querySelectorAll('.materiel-stats-chart-toggle .btn-outline').forEach(function (b) {
                b.classList.toggle('is-active', b === btn);
            });
            document.querySelectorAll('[id^="materiel-chart-"]').forEach(function (el) {
                if (el.id === 'materiel-chart-title') return;
                el.hidden = el.id !== 'materiel-chart-' + chart;
            });
            if (chartTitleEl && chartLabels[chart]) {
                chartTitleEl.textContent = chartTitleTpl.replace('%s', chartLabels[chart]);
            }
        });
    });

    // Recherche + tri + filtre par statut (côté client, sans rechargement) dans la fenêtre
    // "Voir l'historique". Recherche et filtre de statut se combinent (une ligne n'est visible
    // que si elle passe les deux à la fois).
    var histList = document.getElementById('materiel-historique-list');
    if (histList) {
        var histSearch = document.getElementById('materiel-historique-search');
        var histSort = document.getElementById('materiel-historique-sort');
        var histEmpty = document.getElementById('materiel-historique-empty-search');
        var histStatutBtns = document.querySelectorAll('[data-statut-filtre]');
        var currentStatutFiltre = 'tous';

        function applyHistFilter() {
            var term = (histSearch.value || '').trim().toLowerCase();
            var visibleCount = 0;
            histList.querySelectorAll('li').forEach(function (li) {
                var matchSearch = term === '' || li.dataset.search.indexOf(term) !== -1;
                var matchStatut = currentStatutFiltre === 'tous' || li.dataset.statut === currentStatutFiltre;
                var match = matchSearch && matchStatut;
                li.hidden = !match;
                if (match) visibleCount++;
            });
            if (histEmpty) histEmpty.hidden = visibleCount > 0;
        }

        function applyHistSort() {
            var items = Array.prototype.slice.call(histList.querySelectorAll('li'));
            var key = histSort.value;
            items.sort(function (a, b) {
                switch (key) {
                    case 'date_asc': return a.dataset.date - b.dataset.date;
                    case 'nom': return a.dataset.nom.localeCompare(b.dataset.nom);
                    case 'lieu': return a.dataset.lieu.localeCompare(b.dataset.lieu);
                    case 'emprunteur': return a.dataset.emprunteur.localeCompare(b.dataset.emprunteur);
                    case 'statut': return a.dataset.statut.localeCompare(b.dataset.statut);
                    default: return b.dataset.date - a.dataset.date; // date_desc
                }
            });
            items.forEach(function (li) { histList.appendChild(li); });
        }

        if (histSearch) histSearch.addEventListener('input', applyHistFilter);
        if (histSort) histSort.addEventListener('change', applyHistSort);
        histStatutBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                currentStatutFiltre = btn.dataset.statutFiltre;
                histStatutBtns.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
                applyHistFilter();
            });
        });
    }
});
</script>

<?php include 'footer.php'; ?>
