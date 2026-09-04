<?php
// La recherche de la filière se fait AVANT l'inclusion de header.php : header.php
// commence déjà à produire de la sortie HTML (avec un output_buffering trop petit
// pour la contenir en entier), ce qui empêche tout header('Location: ...') ultérieur.
include_once 'language.php';
require_once 'db_connect.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

$filiere = null;
if ($slug !== '') {
    $stmt = $mysqli->prepare("SELECT * FROM filieres WHERE slug = ?");
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $filiere = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$filiere) {
    header('Location: parcours.php');
    exit;
}

function filiere_field($row, $base, $lang) {
    $key = $base . '_' . $lang;
    return !empty($row[$key]) ? $row[$key] : $row[$base . '_fr'];
}

// Traduction automatique à la volée : si le champ n'a pas encore de traduction pour la langue
// demandée, on la génère via l'API MyMemory et on la sauvegarde en base pour que les visites
// suivantes n'aient plus jamais besoin de re-traduire ce même champ.
// $translate_budget limite le nombre d'appels API par requête HTTP pour éviter qu'une page
// jamais traduite ne mette 30+ secondes à charger : la traduction se complète alors
// progressivement sur les premières visites plutôt qu'en un seul coup.
$translate_budget = 6;

function filiere_auto_field($mysqli, $table, $id, $row, $base, $lang, &$budget) {
    $key = $base . '_' . $lang;
    if (!empty($row[$key])) return $row[$key];
    $fr = trim((string) ($row[$base . '_fr'] ?? ''));
    if ($fr === '' || $lang === 'fr' || $budget <= 0) return $fr;
    $budget--;
    $translated = mymemory_translate($fr, $lang);
    if ($translated === '') return $fr;
    $stmt = $mysqli->prepare("UPDATE `$table` SET `$key` = ? WHERE id = ?");
    $stmt->bind_param('si', $translated, $id);
    $stmt->execute();
    $stmt->close();
    return $translated;
}

// Variante pour les champs multi-lignes (avantages, débouchés) : chaque ligne est traduite
// séparément puis rejointe, pour ne pas perdre le découpage en cartes après traduction.
function filiere_auto_field_lines($mysqli, $table, $id, $row, $base, $lang, &$budget) {
    $key = $base . '_' . $lang;
    if (!empty($row[$key])) return $row[$key];
    $fr = trim((string) ($row[$base . '_fr'] ?? ''));
    if ($fr === '' || $lang === 'fr') return $fr;
    $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $fr)), fn($l) => $l !== ''));
    $translated_lines = [];
    foreach ($lines as $line) {
        if ($budget <= 0) return $fr; // budget épuisé en cours de route : on retombe sur le français pour ne pas mélanger les langues
        $budget--;
        $t = mymemory_translate($line, $lang);
        if ($t === '') return $fr;
        $translated_lines[] = $t;
    }
    $translated = implode("\n", $translated_lines);
    $stmt = $mysqli->prepare("UPDATE `$table` SET `$key` = ? WHERE id = ?");
    $stmt->bind_param('si', $translated, $id);
    $stmt->execute();
    $stmt->close();
    return $translated;
}

// Découpe un champ texte multi-lignes en une liste de phrases (une par ligne non vide),
// pour l'afficher sous forme de cartes plutôt qu'un simple paragraphe.
function filiere_lines($text) {
    if (!$text) return [];
    $lines = preg_split('/\r\n|\r|\n/', $text);
    $lines = array_map('trim', $lines);
    return array_values(array_filter($lines, fn($l) => $l !== ''));
}

$filiere_nom = filiere_auto_field($mysqli, 'filieres', $filiere['id'], $filiere, 'nom', $lang, $translate_budget);
$filiere_desc = filiere_auto_field($mysqli, 'filieres', $filiere['id'], $filiere, 'description', $lang, $translate_budget);
$filiere_debouches = filiere_lines(filiere_auto_field_lines($mysqli, 'filieres', $filiere['id'], $filiere, 'debouches', $lang, $translate_budget));
$filiere_historique = filiere_auto_field($mysqli, 'filieres', $filiere['id'], $filiere, 'historique', $lang, $translate_budget);
$filiere_avantages = filiere_lines(filiere_auto_field_lines($mysqli, 'filieres', $filiere['id'], $filiere, 'avantages', $lang, $translate_budget));

$filiere_blocks = [];
if ($mysqli->query("SHOW TABLES LIKE 'filiere_blocks'")->num_rows > 0) {
    $stmt = $mysqli->prepare("SELECT * FROM filiere_blocks WHERE filiere_id = ? ORDER BY display_order ASC, id ASC");
    $stmt->bind_param('i', $filiere['id']);
    $stmt->execute();
    $filiere_blocks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$filiere_niveaux = array_values(array_filter(array_map('trim', explode(',', $filiere['niveaux'] ?? ''))));
$a_licence = count(array_intersect($filiere_niveaux, ['L1', 'L2', 'L3'])) > 0;
$a_master = count(array_intersect($filiere_niveaux, ['M1', 'M2'])) > 0;

$page_title = $filiere_nom;

include 'header.php';
?>

<div class="page-banner filiere-detail-banner"<?php if (!empty($filiere['image_path'])): ?> style="background-image: linear-gradient(rgba(0, 25, 51, 0.4), rgba(0, 25, 51, 0.4)), url('<?php echo htmlspecialchars($filiere['image_path']); ?>');"<?php endif; ?>>
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="filieres.php"><?php echo t('filieres'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($filiere_nom); ?></span>
        </nav>
        <h1><?php echo htmlspecialchars($filiere_nom); ?></h1>
        <div class="album-banner-meta">
            <?php if ($filiere['mention']): ?><span class="mention-badge"><?php echo htmlspecialchars($filiere['mention']); ?></span><?php endif; ?>
            <?php if ($filiere['code']): ?><span><i class="fas fa-hashtag"></i> <?php echo t('filiere_detail_code_label'); ?> : <?php echo htmlspecialchars($filiere['code']); ?></span><?php endif; ?>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <a href="filieres.php" class="btn-outline btn-back-sticky"><i class="fas fa-arrow-left"></i> <?php echo t('filiere_detail_retour'); ?></a>

        <?php if ($lang !== 'fr'): ?>
            <button type="button" id="btn-translate-all" class="btn-outline btn-translate-all" data-filiere-id="<?php echo (int) $filiere['id']; ?>" data-lang="<?php echo htmlspecialchars($lang); ?>">
                <i class="fas fa-language"></i> <span class="btn-translate-all-label"><?php echo t('filiere_traduire_page_btn'); ?></span>
            </button>
        <?php endif; ?>

        <!-- En un coup d'œil : faits réels de la filière (niveaux, mention, code) -->
        <div class="filiere-facts-row animate-on-scroll">
            <?php if ($a_licence): ?>
                <div class="filiere-fact-box"><i class="fas fa-graduation-cap"></i><div><strong><?php echo t('filieres_filtre_licence'); ?></strong><span>L1 — L3</span></div></div>
            <?php endif; ?>
            <?php if ($a_master): ?>
                <div class="filiere-fact-box"><i class="fas fa-user-graduate"></i><div><strong><?php echo t('filieres_filtre_master'); ?></strong><span>M1 — M2</span></div></div>
            <?php endif; ?>
            <?php if ($filiere['mention']): ?>
                <div class="filiere-fact-box"><i class="fas fa-layer-group"></i><div><strong><?php echo t('admin_categorie_label'); ?></strong><span><?php echo htmlspecialchars($filiere['mention']); ?></span></div></div>
            <?php endif; ?>
            <?php if ($filiere['code']): ?>
                <div class="filiere-fact-box"><i class="fas fa-hashtag"></i><div><strong><?php echo t('filiere_detail_code_label'); ?></strong><span><?php echo htmlspecialchars($filiere['code']); ?></span></div></div>
            <?php endif; ?>
        </div>

        <?php if ($filiere_desc): ?>
            <div class="intro-with-image animate-on-scroll">
                <?php if (!empty($filiere['image_path'])): ?>
                    <div class="intro-image">
                        <img src="<?php echo htmlspecialchars($filiere['image_path']); ?>" alt="<?php echo htmlspecialchars($filiere_nom); ?>" loading="lazy">
                    </div>
                <?php endif; ?>
                <div class="page-intro-text" style="font-family: inherit; font-size: 1.05rem; text-align: left; color: var(--text-color); opacity: 0.9;">
                    <p class="filiere-intro-paragraph"><?php echo nl2br(htmlspecialchars($filiere_desc)); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($filiere_historique): ?>
            <section class="filiere-histoire-section animate-on-scroll">
                <h2 class="og-section-title filiere-section-title animate-on-scroll"><i class="fas fa-landmark"></i> <?php echo t('filiere_detail_historique_titre'); ?></h2>
                <div class="filiere-debouches-box">
                    <p><?php echo nl2br(htmlspecialchars($filiere_historique)); ?></p>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($filiere_avantages)): ?>
            <section class="filiere-avantages-section">
                <h2 class="og-section-title filiere-section-title animate-on-scroll"><i class="fas fa-star"></i> <?php echo t('filiere_detail_avantages_titre'); ?></h2>
                <div class="filiere-cards-grid">
                    <?php foreach ($filiere_avantages as $i => $ligne): ?>
                        <div class="filiere-point-card animate-on-scroll" style="transition-delay: <?php echo $i * 0.08; ?>s;">
                            <i class="fas fa-circle-check"></i>
                            <p><?php echo htmlspecialchars($ligne); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($filiere_debouches)): ?>
            <section class="filiere-debouches-section">
                <h2 class="og-section-title filiere-section-title animate-on-scroll"><i class="fas fa-briefcase"></i> <?php echo t('filiere_detail_debouches_titre'); ?></h2>
                <div class="filiere-cards-grid">
                    <?php foreach ($filiere_debouches as $i => $ligne): ?>
                        <div class="filiere-point-card filiere-point-card-job animate-on-scroll" style="transition-delay: <?php echo $i * 0.08; ?>s;">
                            <i class="fas fa-arrow-trend-up"></i>
                            <p><?php echo htmlspecialchars($ligne); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($filiere_blocks)): ?>
            <?php
                // Mise en page façon article de magazine : les images alternent à gauche/droite
                // et le texte s'enroule autour (float CSS), au lieu d'un simple empilement vertical.
                $filiere_img_i = 0;
            ?>
            <div class="filiere-content-blocks">
                <?php foreach ($filiere_blocks as $i => $block): ?>
                    <?php if ($block['block_type'] === 'image'): ?>
                        <?php
                            $block_caption = filiere_auto_field($mysqli, 'filiere_blocks', $block['id'], $block, 'caption', $lang, $translate_budget);
                            $img_side = $filiere_img_i % 2 === 0 ? 'filiere-block-image-left' : 'filiere-block-image-right';
                            $filiere_img_i++;
                        ?>
                        <figure class="filiere-block-image <?php echo $img_side; ?> animate-on-scroll" style="transition-delay: <?php echo ($i % 6) * 0.06; ?>s;">
                            <img src="<?php echo htmlspecialchars($block['image_path']); ?>" alt="<?php echo htmlspecialchars($block_caption ?: $filiere_nom); ?>" loading="lazy">
                            <?php if ($block_caption): ?><figcaption><?php echo htmlspecialchars($block_caption); ?></figcaption><?php endif; ?>
                        </figure>
                    <?php else: ?>
                        <div class="filiere-block-text animate-on-scroll" style="transition-delay: <?php echo ($i % 6) * 0.06; ?>s;">
                            <p><?php echo nl2br(htmlspecialchars(filiere_auto_field($mysqli, 'filiere_blocks', $block['id'], $block, 'content', $lang, $translate_budget))); ?></p>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!$filiere_desc && !$filiere_historique && empty($filiere_avantages) && empty($filiere_debouches) && empty($filiere_blocks)): ?>
            <p class="gallery-empty"><i class="fas fa-circle-info"></i> <?php echo t('filiere_detail_aucun_contenu'); ?></p>
        <?php endif; ?>

    </div>
</div>

<?php if ($lang !== 'fr'): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-translate-all');
    if (!btn) return;
    const label = btn.querySelector('.btn-translate-all-label');
    const defaultLabel = label.textContent;
    btn.addEventListener('click', async () => {
        btn.disabled = true;
        label.textContent = <?php echo json_encode(t('filiere_traduire_page_encours')); ?>;
        btn.querySelector('i').className = 'fas fa-spinner fa-spin';
        try {
            const res = await fetch('translate_filiere.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ filiere_id: btn.dataset.filiereId, lang: btn.dataset.lang }),
            });
            const data = await res.json();
            if (!res.ok || data.error) throw new Error(data.error === 'quota' ? 'quota' : (data.error || 'HTTP ' + res.status));
            if (data.translated_count > 0) {
                label.textContent = <?php echo json_encode(t('filiere_traduire_page_termine')); ?>;
                window.location.reload();
                return;
            }
            label.textContent = <?php echo json_encode(t('filiere_traduire_page_dejafait')); ?>;
            btn.querySelector('i').className = 'fas fa-check';
        } catch (e) {
            label.textContent = e.message === 'quota'
                ? <?php echo json_encode(t('filiere_traduire_page_quota')); ?>
                : <?php echo json_encode(t('filiere_traduire_page_erreur')); ?>;
            btn.querySelector('i').className = 'fas fa-triangle-exclamation';
            btn.disabled = false;
            setTimeout(() => { label.textContent = defaultLabel; btn.querySelector('i').className = 'fas fa-language'; }, 5000);
        }
    });
});
</script>
<?php endif; ?>

<?php include 'footer.php'; ?>
