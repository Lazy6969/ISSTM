<?php
include_once 'language.php';
require_once 'db_connect.php';

$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
if (!$is_logged_in) {
    header('Location: login.php');
    exit;
}

$self_id = (int) $_SESSION['user_id'];

// Filière de l'utilisateur connecté (si inscription approuvée), pour présélectionner son onglet.
$own_filiere_id = 0;
$prow = $mysqli->query("SELECT filiere_id FROM preinscriptions WHERE user_id = $self_id AND status = 'approuve' AND filiere_id IS NOT NULL ORDER BY id DESC LIMIT 1")->fetch_assoc();
if ($prow && $prow['filiere_id']) { $own_filiere_id = (int) $prow['filiere_id']; }

// Filières ayant au moins une publication publiée, seules celles-ci apparaissent dans le sélecteur.
$filieres_with_results = $mysqli->query("
    SELECT f.id, f.nom_fr, COUNT(p.id) AS nb
    FROM filieres f
    INNER JOIN resultat_publications p ON p.filiere_id = f.id AND p.statut = 'publie'
    GROUP BY f.id, f.nom_fr
    ORDER BY f.display_order ASC, f.nom_fr ASC
")->fetch_all(MYSQLI_ASSOC);

$current_filiere_id = 0;
if (isset($_GET['filiere']) && ctype_digit($_GET['filiere'])) {
    $requested = (int) $_GET['filiere'];
    foreach ($filieres_with_results as $f) {
        if ((int) $f['id'] === $requested) { $current_filiere_id = $requested; break; }
    }
}
if ($current_filiere_id === 0 && $own_filiere_id > 0) {
    foreach ($filieres_with_results as $f) {
        if ((int) $f['id'] === $own_filiere_id) { $current_filiere_id = $own_filiere_id; break; }
    }
}
if ($current_filiere_id === 0 && !empty($filieres_with_results)) {
    $current_filiere_id = (int) $filieres_with_results[0]['id'];
}

$publications = [];
if ($current_filiere_id > 0) {
    $stmt = $mysqli->prepare("SELECT p.* FROM resultat_publications p WHERE p.filiere_id = ? AND p.statut = 'publie' ORDER BY p.published_at DESC, p.created_at DESC");
    $stmt->bind_param("i", $current_filiere_id);
    $stmt->execute();
    $publications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (!empty($publications)) {
        $ids = array_column($publications, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = $mysqli->prepare("SELECT * FROM resultat_images WHERE publication_id IN ($placeholders) ORDER BY display_order ASC, id ASC");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $all_images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $images_by_pub = [];
        foreach ($all_images as $img) {
            $images_by_pub[(int) $img['publication_id']][] = $img;
        }
        foreach ($publications as &$pub) {
            $pub['images'] = $images_by_pub[(int) $pub['id']] ?? [];
        }
        unset($pub);
    }
}

$page_title = t('resultats_page_titre');
include 'header.php';
?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('resultats_page_titre'); ?></span>
        </nav>
        <h1><?php echo t('resultats_page_titre'); ?></h1>
        <p><?php echo t('resultats_page_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <?php if (empty($filieres_with_results)): ?>
            <p class="gallery-empty"><i class="fas fa-file-circle-check"></i> <?php echo t('resultats_aucune_filiere'); ?></p>
        <?php else: ?>

            <?php if (count($filieres_with_results) > 1): ?>
                <div class="banner-page-picker">
                    <?php foreach ($filieres_with_results as $f): ?>
                        <a href="resultats_examen.php?filiere=<?php echo (int) $f['id']; ?>" class="banner-page-picker-item<?php echo (int) $f['id'] === $current_filiere_id ? ' is-active' : ''; ?>">
                            <?php echo htmlspecialchars($f['nom_fr']); ?> <span class="resultat-picker-count"><?php echo (int) $f['nb']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($publications)): ?>
                <p class="gallery-empty"><i class="fas fa-file-circle-check"></i> <?php echo t('resultats_aucune_publication'); ?></p>
            <?php else: ?>
                <?php foreach ($publications as $pub): ?>
                    <div class="resultat-publication-card animate-on-scroll">
                        <div class="resultat-publication-head">
                            <h2><?php echo htmlspecialchars($pub['titre']); ?></h2>
                            <div class="resultat-publication-meta">
                                <span class="admin-status-badge admin-status-publie"><?php echo $pub['niveau'] ? htmlspecialchars($pub['niveau']) : t('resultats_niveau_toutes'); ?></span>
                                <?php if ($pub['published_at']): ?>
                                    <span class="resultat-publication-date"><i class="far fa-calendar"></i> <?php echo date('d/m/Y à H:i', strtotime($pub['published_at'])); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($pub['description'])): ?>
                            <p class="resultat-publication-desc"><?php echo nl2br(htmlspecialchars($pub['description'])); ?></p>
                        <?php endif; ?>

                        <?php if (empty($pub['images'])): ?>
                            <p class="gallery-empty"><i class="fas fa-image"></i> <?php echo t('resultats_aucune_image'); ?></p>
                        <?php else: ?>
                            <div class="resultat-image-grid">
                                <?php foreach ($pub['images'] as $img): ?>
                                    <div class="resultat-image-item gallery-item">
                                        <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="<?php echo htmlspecialchars($pub['titre']); ?>" loading="lazy">
                                        <div class="gallery-item-overlay">
                                            <i class="fas fa-magnifying-glass-plus"></i>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Chaque publication a son propre groupe d'images pour la lightbox (navigation prev/next
    // limitée aux images de CETTE publication, pas mélangée avec les autres publications de la page).
    document.querySelectorAll('.resultat-image-grid').forEach(grid => {
        const items = Array.from(grid.querySelectorAll('.resultat-image-item'));
        const urls = items.map(item => item.querySelector('img').src);
        items.forEach((item, idx) => {
            item.addEventListener('click', () => window.openLightbox(urls[idx], urls));
        });
    });
});
</script>

<?php include 'footer.php'; ?>
