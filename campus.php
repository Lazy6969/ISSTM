<?php
include_once 'language.php';
require_once 'db_connect.php';
$page_title = t('campus_titre');
include 'header.php';

// Données des blocs du campus, désormais gérées depuis l'admin (voir admin_campus.php)
// et stockées dans la table campus_blocs.
$campus_blocs = [];
$result = $mysqli->query("SELECT * FROM campus_blocs ORDER BY nom ASC");
while ($row = $result->fetch_assoc()) {
    $campus_blocs[] = [
        'id' => $row['bloc_key'],
        'nom' => $row['nom'],
        'signification' => $row['signification'],
        'fondation' => $row['fondation'],
        'fondateurs' => $row['fondateurs'],
        'slogan' => $row['slogan'],
        'objectifs' => array_values(array_filter(explode("\n", (string) $row['objectifs']), fn($v) => trim($v) !== '')),
        'activites' => array_values(array_filter(explode("\n", (string) $row['activites']), fn($v) => trim($v) !== '')),
        'danse' => $row['danse'],
        'mampiavaka' => $row['mampiavaka'],
        'images' => array_values(array_filter(explode(',', (string) $row['images']), fn($v) => trim($v) !== '')),
    ];
}

// Extrait l'acronyme d'un nom de bloc. En base, 'nom' ne contient que l'acronyme
// (ex: "MAFAMI"), mais on gère aussi l'ancien format "Bloc N : ACRONYME" par sécurité
// (sinon strpos() renverrait false et substr(..., false + 1) tronquerait le 1er caractère).
function campus_extraire_acronyme($nom) {
    $pos = strpos($nom, ':');
    return $pos !== false ? trim(substr($nom, $pos + 1)) : trim($nom);
}

// Tri des blocs par ordre alphabétique de leur nom (acronyme)
usort($campus_blocs, function($a, $b) {
    $name_a = campus_extraire_acronyme($a['nom']);
    $name_b = campus_extraire_acronyme($b['nom']);
    return strcasecmp($name_a, $name_b);
});

// Re-numérotation des blocs après le tri
foreach ($campus_blocs as $index => &$bloc) {
    $name_part = campus_extraire_acronyme($bloc['nom']);
    $bloc['nom'] = 'Bloc ' . ($index + 1) . ' : ' . $name_part;
}
unset($bloc); // Détruire la référence à la dernière itération

?>

<div class="page-banner campus-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="vie_etudiante.php"><?php echo t('vie_etudiante'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('campus_titre'); ?></span>
        </nav>
        <h1><?php echo t('campus_titre'); ?></h1>
        <p><?php echo t('campus_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <?php include 'background_animation.php'; ?>

    <!-- Bouton de retour (positionné hors du conteneur, complètement à gauche) -->
    <a href="vie_etudiante.php" class="btn-outline btn-back-sticky">
        <i class="fas fa-arrow-left"></i> <?php echo t('retour_vie_etudiante'); ?>
    </a>

    <div class="container">

        <!-- Texte d'introduction à la recherche -->
        <p class="search-intro-text"><?php echo t('campus_recherche_intro'); ?></p>

        <!-- Barre de recherche -->
        <div class="search-bar-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="bloc-search-input" placeholder="<?php echo t('rechercher_bloc_par_nom'); ?>" autocomplete="off">
            <button id="search-reset-btn" class="search-reset-btn" title="<?php echo t('reinitialiser_recherche'); ?>">&times;</button>
        </div>

        <!-- Section Vie au Campus -->
        <section id="campus-grid" class="campus-blocks-grid"> <!-- Ajout de l'ID pour le JS -->
            <?php foreach ($campus_blocs as $bloc): ?>
                <?php
                $final_image_src = !empty($bloc['images'][0]) ? $bloc['images'][0] : "images/campus/default.jpg";
                ?>
                <div class="block-card" data-id="<?php echo htmlspecialchars($bloc['id']); ?>">
                    <img src="<?php echo htmlspecialchars($final_image_src); ?>" alt="Image pour <?php echo htmlspecialchars($bloc['nom']); ?>" class="block-card-image">
                    <div class="block-card-title">
                        <h3><?php echo htmlspecialchars($bloc['nom']); ?></h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- Message "Aucun résultat" -->
        <div id="no-results-message" class="no-results-message" style="display: none;">
            <i class="fas fa-search"></i>
            <p><?php echo t('aucun_resultat_trouve'); ?></p>
        </div>

    </div>
</div>

<!-- Block Details Modal -->
<div id="blockDetailsModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2 id="modalBlocNom" class="modal-title"></h2>
        <div class="modal-body">
            <div class="modal-bloc-details">
                <div class="modal-bloc-images" id="modalBlocImages"></div>
                <div class="modal-bloc-info" id="modalBlocInfo"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Constantes et Variables ---
    const campusBlocsData = <?php echo json_encode($campus_blocs); ?>;
    const campusTranslations = {
        signification: "<?php echo addslashes(t('signification')); ?>", // Assurez-vous que t() est disponible
        slogan: "<?php echo addslashes(t('slogan')); ?>", // Assurez-vous que t() est disponible
        date_fondation: "<?php echo addslashes(t('date_fondation')); ?>", // Assurez-vous que t() est disponible
        fondateurs: "<?php echo addslashes(t('fondateurs')); ?>", // Assurez-vous que t() est disponible
        objectifs: "<?php echo addslashes(t('objectifs')); ?>", // Assurez-vous que t() est disponible
        activites_annuelles: "<?php echo addslashes(t('activites_annuelles')); ?>", // Assurez-vous que t() est disponible
        danse_traditionnelle: "<?php echo addslashes(t('danse_traditionnelle')); ?>", // Assurez-vous que t() est disponible
        mampiavaka: "<?php echo addslashes(t('mampiavaka')); ?>" // Assurez-vous que t() est disponible
    };

    const modal = document.getElementById('blockDetailsModal');
    const closeModalButton = modal.querySelector('.close-button');
    const campusGrid = document.getElementById('campus-grid');

    // --- Modal Functions ---
    function createInfoItem(label, value, iconClass) {
        if (!value || (Array.isArray(value) && value.length === 0)) return '';
        let content = Array.isArray(value) ? `<ul class="styled-list-small">${value.map(item => `<li>${item}</li>`).join('')}</ul>` : `<p>${value}</p>`;
        return `<div class="info-item"><strong><i class="${iconClass}"></i> ${label}</strong>${content}</div>`;
    }

    function showBlocDetails(blocId) {
        const bloc = campusBlocsData.find(b => b.id === blocId);
        if (!bloc) return;

        document.getElementById('modalBlocNom').textContent = bloc.nom;
        document.getElementById('modalBlocInfo').innerHTML = `
            ${createInfoItem(campusTranslations.signification, bloc.signification, 'fas fa-info-circle')}
            ${createInfoItem(campusTranslations.slogan, bloc.slogan, 'fas fa-bullhorn')}
            ${createInfoItem(campusTranslations.date_fondation, bloc.fondation, 'fas fa-calendar-alt')}
            ${createInfoItem(campusTranslations.fondateurs, bloc.fondateurs, 'fas fa-users')}
            ${createInfoItem(campusTranslations.objectifs, bloc.objectifs, 'fas fa-bullseye')}
            ${createInfoItem(campusTranslations.activites_annuelles, bloc.activites, 'fas fa-tasks')}
            ${createInfoItem(campusTranslations.danse_traditionnelle, bloc.danse, 'fas fa-music')}
            ${createInfoItem(campusTranslations.mampiavaka, bloc.mampiavaka, 'fas fa-star')}
        `;

        const imagesContainer = document.getElementById('modalBlocImages');
        imagesContainer.innerHTML = '';
        const validImagePaths = (bloc.images && bloc.images.length) ? bloc.images : ['images/campus/default.jpg'];
        validImagePaths.forEach((imgPath) => {
            const img = new Image();
            img.src = imgPath;
            img.onclick = () => globalOpenLightbox(imgPath, validImagePaths);
            imagesContainer.appendChild(img);
        });

        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('show'), 10);
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
    }

    // --- Event Listeners ---
    campusGrid.addEventListener('click', function(e) {
        const card = e.target.closest('.block-card');
        if (card && card.dataset.id) {
            showBlocDetails(card.dataset.id);
        }
    });

    closeModalButton.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    // --- Search Logic ---
    const searchInput = document.getElementById('bloc-search-input');
    const searchResetBtn = document.getElementById('search-reset-btn');
    const noResultsMessage = document.getElementById('no-results-message');
    const blockCards = campusGrid.querySelectorAll('.block-card');

    let debounceTimeout;
    searchInput.addEventListener('input', function() {
        // Affiche ou cache le bouton de réinitialisation
        if (this.value.length > 0) {
            searchResetBtn.classList.add('show');
        } else {
            searchResetBtn.classList.remove('show');
        }

        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            filterBlocks(this.value);
        }, 200); // Délai pour ne pas surcharger à chaque frappe
    });

    searchResetBtn.addEventListener('click', () => {
        searchInput.value = '';
        filterBlocks('');
        searchResetBtn.classList.remove('show');
        searchInput.focus(); // Remet le focus sur le champ de recherche
    });

    function filterBlocks(inputValue) {
        const searchTerm = inputValue.toLowerCase().trim();
        if (searchTerm === '') {
            // Si la recherche est vide, on réaffiche toutes les cartes sans animation
            blockCards.forEach(card => {
                card.style.display = 'block';
                card.classList.remove('is-hidden');
            });
            noResultsMessage.style.display = 'none';
            return;
        }

        let matchCount = 0;

        blockCards.forEach(card => {
            const blockId = card.dataset.id;
            const blocData = campusBlocsData.find(b => b.id === blockId);
            let isMatch = false;

            if (blocData) {
                // Concatène toutes les informations textuelles du bloc en une seule chaîne de caractères
                const searchableContent = [
                    blocData.nom, blocData.signification, blocData.fondateurs, blocData.slogan,
                    blocData.danse, blocData.mampiavaka,
                    ...(Array.isArray(blocData.objectifs) ? blocData.objectifs : []),
                    ...(Array.isArray(blocData.activites) ? blocData.activites : [])
                ].join(' ').toLowerCase();
                isMatch = searchableContent.includes(searchTerm);
            }

            if (isMatch) {
                // Pour afficher : on remet le display, PUIS on anime l'opacité
                card.style.display = 'block';
                card.classList.remove('is-hidden');
                matchCount++;
            } else {
                // Pour cacher : on anime l'opacité, PUIS on change le display
                card.classList.add('is-hidden');
                // Après la fin de l'animation (300ms), on cache complètement l'élément
                // pour que la grille se réorganise. Nous utilisons 'transitionend' pour plus de fiabilité.
                card.addEventListener('transitionend', function handler() {
                    if (card.classList.contains('is-hidden')) {
                        card.style.display = 'none';
                    }
                    card.removeEventListener('transitionend', handler);
                });
            }
        });
        noResultsMessage.style.display = matchCount === 0 ? 'block' : 'none';
    }

    // Expose globalOpenLightbox to be callable from here, if needed (though it's already global in script.js)
    // This is a safeguard if the script.js is loaded after this inline script, but it shouldn't be the case.
    const globalOpenLightbox = window.openLightbox; // Reference to the global function
});
</script>

<?php include 'footer.php'; ?>