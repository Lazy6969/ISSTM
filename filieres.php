<?php
include_once 'language.php';
$page_title = t('filieres_section_titre');
require_once 'db_connect.php';

$filieres_db = [];
if ($check_filieres = $mysqli->query("SHOW TABLES LIKE 'filieres'")) {
    if ($check_filieres->num_rows > 0) {
        $result = $mysqli->query("SELECT * FROM filieres ORDER BY display_order ASC, id ASC");
        while ($row = $result->fetch_assoc()) { $filieres_db[] = $row; }
    }
}

$filieres_mentions = [];
foreach ($filieres_db as $f) {
    if (!empty($f['mention']) && !in_array($f['mention'], $filieres_mentions, true)) {
        $filieres_mentions[] = $f['mention'];
    }
}
sort($filieres_mentions);

include 'header.php';
?>

<div class="page-banner filieres-page-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('filieres_section_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-graduation-cap"></i> <?php echo t('filieres_section_titre'); ?></h1>
        <p><?php echo t('filieres_section_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <?php if (empty($filieres_db)): ?>
            <p class="gallery-empty"><i class="fas fa-graduation-cap"></i> <?php echo t('admin_filieres_aucune_filiere'); ?></p>
        <?php else: ?>

            <div class="filieres-filters">
                <div class="filieres-filter-group">
                    <span class="filieres-filter-label"><?php echo t('filieres_filtre_niveau'); ?></span>
                    <div class="filieres-filter-buttons" data-filter-axis="niveau">
                        <button type="button" class="filiere-filter-btn is-active" data-value="tous"><?php echo t('filieres_filtre_tous'); ?></button>
                        <button type="button" class="filiere-filter-btn" data-value="licence"><?php echo t('filieres_filtre_licence'); ?></button>
                        <button type="button" class="filiere-filter-btn" data-value="master"><?php echo t('filieres_filtre_master'); ?></button>
                    </div>
                </div>
                <div class="filieres-filter-group">
                    <span class="filieres-filter-label"><?php echo t('filieres_filtre_mention'); ?></span>
                    <div class="filieres-filter-buttons" data-filter-axis="mention">
                        <button type="button" class="filiere-filter-btn is-active" data-value="tous"><?php echo t('filieres_filtre_tous'); ?></button>
                        <?php foreach ($filieres_mentions as $m): ?>
                            <button type="button" class="filiere-filter-btn" data-value="<?php echo htmlspecialchars($m); ?>"><?php echo htmlspecialchars($m); ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="filieres-grid" id="filieres-grid">
                <?php foreach ($filieres_db as $i => $f):
                    $nom = !empty($f['nom_' . $lang]) ? $f['nom_' . $lang] : $f['nom_fr'];
                    $desc = !empty($f['description_' . $lang]) ? $f['description_' . $lang] : $f['description_fr'];
                ?>
                    <a href="filiere_detail.php?slug=<?php echo urlencode($f['slug']); ?>" class="filiere-card animate-on-scroll" style="--delay: <?php echo ($i % 6) * 0.08; ?>s;" data-niveaux="<?php echo htmlspecialchars($f['niveaux'] ?? ''); ?>" data-mention="<?php echo htmlspecialchars($f['mention'] ?? ''); ?>">
                        <div class="filiere-card-media" <?php if (!empty($f['image_path'])): ?>style="background-image:url('<?php echo htmlspecialchars($f['image_path']); ?>');"<?php endif; ?>>
                            <?php if (empty($f['image_path'])): ?><i class="fas fa-graduation-cap"></i><?php endif; ?>
                            <?php if (!empty($f['mention'])): ?><span class="filiere-card-mention"><?php echo htmlspecialchars($f['mention']); ?></span><?php endif; ?>
                        </div>
                        <div class="filiere-card-body">
                            <h3><?php echo htmlspecialchars($nom); ?></h3>
                            <p><?php echo htmlspecialchars(mb_strimwidth($desc ?? '', 0, 100, '…')); ?></p>
                            <span class="filiere-card-link"><?php echo t('en_savoir_plus'); ?> <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <p class="filieres-filter-empty" id="filieres-filter-empty" hidden><?php echo t('filieres_filtre_vide'); ?></p>

        <?php endif; ?>

    </div>
</div>

<?php include 'footer.php'; ?>
