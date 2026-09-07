<?php
include_once 'language.php';
require_once 'db_connect.php';
$page_title = t('enseignants');
include 'header.php';
?>

<?php
// Traduction automatique à la volée de la spécialité/description de chaque enseignant (voir le
// même mécanisme dans filiere_detail.php) : traduit et sauvegarde en base au premier affichage
// dans une langue donnée, avec un budget d'appels API par requête pour ne pas ralentir la page.
$teacher_translate_budget = 6;
function teacher_auto_field($mysqli, $id, $row, $base, $lang, &$budget) {
    $key = $base . '_' . $lang;
    if (!empty($row[$key])) return $row[$key];
    $fr = trim((string) ($row[$base . '_fr'] ?? ''));
    if ($fr === '' || $lang === 'fr' || $budget <= 0) return $fr;
    $budget--;
    $translated = mymemory_translate($fr, $lang);
    if ($translated === '') return $fr;
    $stmt = $mysqli->prepare("UPDATE teachers SET `$key` = ? WHERE id = ?");
    $stmt->bind_param('si', $translated, $id);
    $stmt->execute();
    $stmt->close();
    return $translated;
}

$enseignants = [];
$result = $mysqli->query("SELECT * FROM teachers ORDER BY display_order ASC, id ASC");
while ($row = $result->fetch_assoc()) {
    $enseignants[] = [
        'id' => $row['id'],
        'image' => $row['photo'] ? 'images/teachers/' . $row['photo'] : 'images/teachers/default-avatar.svg',
        'nom' => $row['nom'],
        'role' => $row['categorie'] === 'permanent' ? 'enseignant_chercheur' : 'enseignant_vacataire',
        'categorie' => $row['categorie'],
        'specialite' => teacher_auto_field($mysqli, $row['id'], $row, 'specialite', $lang, $teacher_translate_budget),
        'description' => teacher_auto_field($mysqli, $row['id'], $row, 'description', $lang, $teacher_translate_budget),
        'email' => $row['email'],
    ];
}

$enseignants_permanents = array_values(array_filter($enseignants, fn($e) => $e['categorie'] === 'permanent'));
$enseignants_vacataires = array_values(array_filter($enseignants, fn($e) => $e['categorie'] === 'vacataire'));
?>

<div class="page-banner enseignants-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('enseignants'); ?></span>
        </nav>
        <h1><?php echo t('enseignants'); ?></h1>
        <p><?php echo t('enseignants_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <?php include 'background_animation.php'; ?>
    <div class="container">

        <p class="intro-text"><?php echo t('enseignants_intro'); ?></p>
        <p class="enseignants-dissertation"><?php echo t('enseignants_dissertation'); ?></p>

        <!-- Filtres et recherche -->
        <div class="teacher-toolbar">
            <div class="teacher-filter-buttons">
                <button type="button" class="teacher-filter-btn" data-filter="permanent">
                    <i class="fas fa-user-tie"></i>
                    <span><?php echo t('enseignants_section_permanents'); ?></span>
                </button>
                <button type="button" class="teacher-filter-btn" data-filter="vacataire">
                    <i class="fas fa-user-clock"></i>
                    <span><?php echo t('enseignants_section_vacataires'); ?></span>
                </button>
            </div>

            <div class="search-bar-container teacher-search-bar">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="teacher-search-input" placeholder="<?php echo t('rechercher_enseignant'); ?>" autocomplete="off">
                <button id="teacher-search-reset-btn" class="search-reset-btn" title="<?php echo t('reinitialiser_recherche'); ?>">&times;</button>
            </div>
        </div>

        <div id="teacher-category-sections">
            <div class="teacher-category-section" data-category="permanent">
                <h2 class="teachers-section-title"><?php echo t('enseignants_section_permanents'); ?></h2>
                <div class="teacher-grid">
                    <?php foreach ($enseignants_permanents as $enseignant): ?>
                        <div class="teacher-card" data-search="<?php echo htmlspecialchars(mb_strtolower($enseignant['nom'] . ' ' . $enseignant['specialite'], 'UTF-8')); ?>">
                            <div class="teacher-card-inner">
                                <div class="teacher-card-front">
                                    <img src="<?php echo $enseignant['image']; ?>" alt="<?php echo htmlspecialchars(sprintf(t('photo_de_alt'), $enseignant['nom'])); ?>" loading="lazy">
                                    <h3><?php echo htmlspecialchars($enseignant['nom']); ?></h3>
                                    <p class="teacher-role"><?php echo t($enseignant['role']); ?></p>
                                </div>
                                <div class="teacher-card-back">
                                    <div class="back-content">
                                        <p class="teacher-desc">"<?php echo htmlspecialchars($enseignant['description']); ?>"</p>
                                        <div class="teacher-details">
                                            <h4><?php echo t('specialite'); ?></h4>
                                            <p><?php echo htmlspecialchars($enseignant['specialite']); ?></p>
                                            <?php if (!empty($enseignant['parcours'])): ?>
                                                <h4><?php echo t('parcours'); ?></h4>
                                                <p><?php echo t($enseignant['parcours']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="teacher-contact">
                                        <a href="mailto:<?php echo $enseignant['email']; ?>" title="<?php echo t('email'); ?>"><i class="fas fa-envelope"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="teacher-category-section" data-category="vacataire">
                <h2 class="teachers-section-title"><?php echo t('enseignants_section_vacataires'); ?></h2>
                <div class="teacher-grid">
                    <?php foreach ($enseignants_vacataires as $enseignant): ?>
                        <div class="teacher-card" data-search="<?php echo htmlspecialchars(mb_strtolower($enseignant['nom'] . ' ' . $enseignant['specialite'], 'UTF-8')); ?>">
                            <div class="teacher-card-inner">
                                <div class="teacher-card-front">
                                    <img src="<?php echo $enseignant['image']; ?>" alt="<?php echo htmlspecialchars(sprintf(t('photo_de_alt'), $enseignant['nom'])); ?>" loading="lazy">
                                    <h3><?php echo htmlspecialchars($enseignant['nom']); ?></h3>
                                    <p class="teacher-role"><?php echo t($enseignant['role']); ?></p>
                                </div>
                                <div class="teacher-card-back">
                                    <div class="back-content">
                                        <p class="teacher-desc">"<?php echo htmlspecialchars($enseignant['description']); ?>"</p>
                                        <div class="teacher-details">
                                            <h4><?php echo t('specialite'); ?></h4>
                                            <p><?php echo htmlspecialchars($enseignant['specialite']); ?></p>
                                            <?php if (!empty($enseignant['parcours'])): ?>
                                                <h4><?php echo t('parcours'); ?></h4>
                                                <p><?php echo t($enseignant['parcours']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="teacher-contact">
                                        <a href="mailto:<?php echo $enseignant['email']; ?>" title="<?php echo t('email'); ?>"><i class="fas fa-envelope"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Message "Aucun résultat" -->
        <div id="teacher-no-results" class="no-results-message" style="display: none;">
            <i class="fas fa-search"></i>
            <p><?php echo t('aucun_resultat_trouve'); ?></p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterButtons = document.querySelectorAll('.teacher-filter-btn');
    const categorySections = document.querySelectorAll('.teacher-category-section');
    const searchInput = document.getElementById('teacher-search-input');
    const searchResetBtn = document.getElementById('teacher-search-reset-btn');
    const noResultsMessage = document.getElementById('teacher-no-results');

    if (!searchInput || categorySections.length === 0) return;

    let activeFilter = null;

    function renderVisibility() {
        const term = searchInput.value.toLowerCase().trim();
        let totalVisible = 0;

        categorySections.forEach(section => {
            const categoryAllowed = !activeFilter || activeFilter === section.dataset.category;
            let visibleInSection = 0;

            section.querySelectorAll('.teacher-card').forEach(card => {
                const show = categoryAllowed && (term === '' || card.dataset.search.includes(term));
                card.style.display = show ? '' : 'none';
                if (show) visibleInSection++;
            });

            section.style.display = visibleInSection > 0 ? '' : 'none';
            totalVisible += visibleInSection;
        });

        noResultsMessage.style.display = totalVisible === 0 ? 'block' : 'none';
    }

    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const wasActive = btn.classList.contains('is-active');
            filterButtons.forEach(b => b.classList.remove('is-active'));
            activeFilter = wasActive ? null : btn.dataset.filter;
            if (activeFilter) btn.classList.add('is-active');
            renderVisibility();
        });
    });

    let debounceTimeout;
    searchInput.addEventListener('input', function () {
        searchResetBtn.classList.toggle('show', this.value.length > 0);
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(renderVisibility, 200);
    });

    searchResetBtn.addEventListener('click', () => {
        searchInput.value = '';
        searchResetBtn.classList.remove('show');
        renderVisibility();
        searchInput.focus();
    });
});
</script>

<?php include 'footer.php'; ?>
