// --- Motif de fond décoratif global (baobab / cocotier en alternance dorée) ---
// Génère une grille de <img> en alternance stricte coco/baobab (jamais deux images identiques
// côte à côte, jamais fusionnées : chaque arbre garde son propre espace grâce à TILE+GAP), fixée
// derrière tout le contenu de la page. Recalculée au redimensionnement (avec une petite marge de
// débordement pour que le léger fondu d'animation ne laisse jamais de bord vide apparaître).
(function initSiteBgMotif() {
    const layer = document.createElement('div');
    layer.className = 'site-bg-motif';
    layer.setAttribute('aria-hidden', 'true');

    const TILE = 280; // largeur visuelle de chaque arbre (doit correspondre à .site-bg-motif-tile en CSS)
    const GAP = 70;   // espace vide entre deux arbres, pour qu'ils ne se touchent/fusionnent jamais
    const STEP = TILE + GAP;
    const IMAGES = ['images/coco.png', 'images/baobab.png'];

    function build() {
        layer.innerHTML = '';
        const cols = Math.ceil(window.innerWidth / STEP) + 2;
        const rows = Math.ceil(window.innerHeight / STEP) + 2;
        let i = 0;
        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < cols; c++) {
                const img = document.createElement('img');
                img.src = IMAGES[i % 2];
                img.alt = '';
                img.loading = 'eager';
                img.className = 'site-bg-motif-tile';
                img.style.left = (c * STEP - STEP) + 'px';
                img.style.top = (r * STEP - STEP) + 'px';
                // Durée/délai randomisés par arbre : un balancement naturel, jamais parfaitement
                // synchronisé entre les copies (voir @keyframes site-bg-motif-sway en CSS).
                img.style.animationDuration = (5 + Math.random() * 4).toFixed(2) + 's';
                img.style.animationDelay = (-(Math.random() * 8)).toFixed(2) + 's';
                layer.appendChild(img);
                i++;
            }
        }
    }

    function start() {
        document.body.appendChild(layer);
        build();
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(build, 300);
        });
    }

    if (document.body) {
        start();
    } else {
        document.addEventListener('DOMContentLoaded', start);
    }
})();

// --- Transition en fondu entre les pages (au lieu d'un rechargement brut) ---
// Sur clic d'un lien interne "normal" (même onglet, pas de touche modificatrice, pas de lien
// externe/ancre/mailto/tel/téléchargement), on fait un court fondu de sortie avant de naviguer.
// La page de destination affiche déjà son propre écran de chargement (site-preloader), donc ce
// fondu ne sert qu'à adoucir la sortie de la page courante. Exposée en global (isstmNavigate) pour
// être réutilisée ailleurs (ex : palette de commande Ctrl+K) sans dupliquer la logique.
function isstmNavigate(href) {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion) { window.location.href = href; return; }
    document.documentElement.classList.add('is-navigating-away');
    setTimeout(() => { window.location.href = href; }, 200);
}

(function initPageFadeTransition() {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.addEventListener('pageshow', (e) => {
        if (e.persisted) document.documentElement.classList.remove('is-navigating-away');
    });

    if (reduceMotion) return;

    document.addEventListener('click', (e) => {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        const link = e.target.closest('a[href]');
        if (!link) return;
        if (link.target && link.target !== '' && link.target !== '_self') return;
        if (link.hasAttribute('download')) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) return;

        let url;
        try { url = new URL(href, window.location.href); } catch (err) { return; }
        if (url.origin !== window.location.origin) return;
        if (url.href.split('#')[0] === window.location.href.split('#')[0]) return; // même page (simple ancre)

        e.preventDefault();
        isstmNavigate(href);
    });
})();

// --- Gestion du Diaporama ---
// Les diapositives image tournent toutes les 3s comme avant. Une diapositive vidéo, elle,
// reste affichée jusqu'à ce que la vidéo se termine complètement (lue une seule fois, sans
// boucle), puis la rotation reprend normalement.
let slideIndex = 0;
let slideTimer = null;

function goToNextSlide() {
    clearTimeout(slideTimer);
    showSlides();
}

function showSlides() {
    const slides = document.getElementsByClassName("slide");
    if (slides.length === 0) return;

    // Met en pause la vidéo de la diapositive qu'on quitte, pour ne pas la laisser jouer cachée.
    for (let i = 0; i < slides.length; i++) {
        const previousVideo = slides[i].querySelector('video');
        if (previousVideo) previousVideo.pause();
        slides[i].classList.remove('active');
    }

    slideIndex++;
    if (slideIndex > slides.length) {
        slideIndex = 1;
    }

    const currentSlide = slides[slideIndex - 1];
    currentSlide.classList.add('active');

    const video = currentSlide.querySelector('video');
    if (video) {
        video.muted = true; // Garantit l'autoplay même si l'attribut HTML n'a pas encore été pris en compte
        video.currentTime = 0;
        video.play().catch(() => {});

        // Filet de sécurité seulement si la vidéo échoue à se terminer normalement (fichier
        // corrompu, erreur réseau...). Sa durée est calée sur la vraie durée de la vidéo (+5s de
        // marge) une fois connue, pour ne JAMAIS couper une vidéo qui joue normalement, même
        // longue — un délai fixe trop court (l'ancien bug) coupait toute vidéo de plus de 20s.
        let fallback;
        const scheduleFallback = () => {
            clearTimeout(fallback);
            const knownDuration = isFinite(video.duration) && video.duration > 0;
            const delay = knownDuration ? (video.duration * 1000 + 5000) : 15000;
            fallback = setTimeout(goToNextSlide, delay);
        };
        scheduleFallback();
        video.addEventListener('loadedmetadata', scheduleFallback);
        video.addEventListener('ended', () => {
            clearTimeout(fallback);
            goToNextSlide();
        }, { once: true });
    } else {
        slideTimer = setTimeout(showSlides, 3000);
    }
}

showSlides();

// --- Gestion du Diaporama des Témoignages ---
(function initTestimonialsSlider() {
    const showcase = document.getElementById('testimonial-showcase');
    const photoCards = document.querySelectorAll('.testimonial-photo-card');
    const textSlides = document.querySelectorAll('.testimonial-text-slide');
    if (!showcase || textSlides.length === 0) return;
    const dots = document.querySelectorAll('.testimonial-dot');
    const total = textSlides.length;
    const BG_COLORS_COUNT = 4;
    let current = 0;
    let timer = null;

    function render() {
        textSlides.forEach((slide, i) => slide.classList.toggle('active', i === current));
        dots.forEach((dot, i) => dot.classList.toggle('active', i === current));
        // Chaque photo reçoit sa "distance" au témoignage actif (0 = devant, 1/2 = en éventail
        // derrière, >2 = masquée) : c'est ce qui fait "tourner" la pile à chaque changement.
        photoCards.forEach((card, i) => {
            const dist = (i - current + total) % total;
            card.dataset.dist = dist <= 2 ? String(dist) : 'hidden';
        });
        showcase.dataset.bgIndex = String(current % BG_COLORS_COUNT);
    }

    function goTo(index) {
        current = (index + total) % total;
        render();
    }

    function startAuto() {
        stopAuto();
        timer = setInterval(() => goTo(current + 1), 5000);
    }
    function stopAuto() {
        if (timer) { clearInterval(timer); timer = null; }
    }

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            goTo(parseInt(dot.dataset.index, 10));
            startAuto();
        });
    });

    render();
    startAuto();
})();

// --- Barre de recherche du header : popover ouvert/fermé au clic (plus accessible qu'un
// simple survol), avec fermeture au clic extérieur et à la touche Échap. ---
(function initHeaderSearch() {
    const container = document.getElementById('nav-search-container');
    const btn = document.getElementById('nav-search-btn');
    const form = document.getElementById('header-search-form');
    const input = document.getElementById('header-search-input');
    if (!container || !btn || !form || !input) return;

    function openSearch() {
        container.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
        input.focus();
    }
    function closeSearch() {
        container.classList.remove('is-open');
        btn.setAttribute('aria-expanded', 'false');
    }
    function toggleSearch() {
        if (container.classList.contains('is-open')) { closeSearch(); } else { openSearch(); }
    }

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleSearch();
    });
    document.addEventListener('click', (e) => {
        if (container.classList.contains('is-open') && !container.contains(e.target)) {
            closeSearch();
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && container.classList.contains('is-open')) {
            closeSearch();
            btn.focus();
        }
    });
})();

// --- Lightbox Logic (Consolidated and Global) ---
// Global variables for lightbox elements, initialized in DOMContentLoaded
let globalLightbox = null;
let globalLightboxImg = null;
let globalLightboxClose = null;
let globalLightboxPrev = null;
let globalLightboxNext = null;
let globalCurrentLightboxImages = [];
let globalCurrentLightboxIndex = 0;

window.updateLightboxNav = function() {
    if (!globalLightboxPrev || !globalLightboxNext) return;
    const display = globalCurrentLightboxImages.length > 1 ? 'block' : 'none';
    globalLightboxPrev.style.display = display;
    globalLightboxNext.style.display = display;
}

window.openLightbox = function(src, allImages = []) {
    if (!globalLightbox || !globalLightboxImg) return;
    globalCurrentLightboxImages = allImages.filter(imgSrc => imgSrc);
    globalCurrentLightboxIndex = globalCurrentLightboxImages.indexOf(src);
    globalLightboxImg.src = src;
    globalLightbox.style.display = 'block';
    updateLightboxNav();
}

window.closeLightbox = function() {
    if (globalLightbox) globalLightbox.style.display = 'none';
}

// --- Generic Modal Functions (Globally accessible) ---
// These functions are defined globally because they are called from inline scripts
// in other PHP files (e.g., campus.php)
window.openModal = function(modal) {
    modal.style.display = 'block';
    const closeButton = modal.querySelector('.close-button');
    if (closeButton) {
        closeButton.addEventListener('click', () => window.closeModal(modal));
    }
}
window.closeModal = function(modal) {
    modal.style.display = 'none';
}

// --- Gestion du mode Nuit/Clair ---
// Plusieurs boutons (header desktop + menu mobile) partagent la classe .theme-toggle-btn
// et restent synchronisés puisqu'ils reflètent tous le même état localStorage/body.dark-mode.
const themeToggleButtons = document.querySelectorAll('.theme-toggle-btn');

function applyThemeIcons(isDark) {
    themeToggleButtons.forEach((btn) => {
        const sun = btn.querySelector('.fa-sun');
        const moon = btn.querySelector('.fa-moon');
        if (sun) sun.style.display = isDark ? 'block' : 'none';
        if (moon) moon.style.display = isDark ? 'none' : 'block';
    });
}

// Appliquer le thème sauvegardé au chargement de la page
if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-mode');
}
applyThemeIcons(document.body.classList.contains('dark-mode'));

themeToggleButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        applyThemeIcons(isDark);
    });
});

window.changeLightboxImage = function(direction) {
    if (!globalCurrentLightboxImages || globalCurrentLightboxImages.length === 0) return;
    globalCurrentLightboxIndex = (globalCurrentLightboxIndex + direction + globalCurrentLightboxImages.length) % globalCurrentLightboxImages.length;
    globalLightboxImg.src = globalCurrentLightboxImages[globalCurrentLightboxIndex];
}

document.addEventListener('DOMContentLoaded', () => {
    // --- Titre de bannière "lettre par lettre" ---
    // Découpe le texte du <h1> de .page-banner en un <span class="banner-letter"> par
    // caractère (les espaces restent de simples noeuds texte, pour ne pas casser le passage
    // à la ligne) ; chaque span reçoit son rang via --i, utilisé par style.css pour échelonner
    // son délai d'apparition. Se rejoue à chaque chargement/actualisation de la page.
    const bannerTitle = document.querySelector('.page-banner h1');
    if (bannerTitle) {
        const titleText = bannerTitle.textContent;
        bannerTitle.textContent = '';
        let letterIndex = 0;
        for (const char of titleText) {
            if (char === ' ') {
                bannerTitle.appendChild(document.createTextNode(' '));
            } else {
                const span = document.createElement('span');
                span.className = 'banner-letter';
                span.style.setProperty('--i', letterIndex);
                span.textContent = char;
                bannerTitle.appendChild(span);
            }
            letterIndex++;
        }
    }

    // --- Étincelles dorées animées sur les bords gauche/droit des bannières (effet vignette) ---
    // Générées en JS (positions/tailles/délais aléatoires) plutôt qu'en CSS pur, pour obtenir un
    // semis irrégulier et "vivant" plutôt qu'un motif répétitif. Ignoré si l'utilisateur préfère
    // moins d'animations (cohérent avec .banner-sparkle-layer { display: none } dans ce cas).
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.querySelectorAll('.page-banner').forEach(banner => {
        if (prefersReducedMotion) return;
        const layer = document.createElement('div');
        layer.className = 'banner-sparkle-layer';
        const sparkleCount = 22; // "beaucoup", réparties sur les deux bords
        for (let i = 0; i < sparkleCount; i++) {
            const sparkle = document.createElement('span');
            const isStar = Math.random() < 0.4;
            sparkle.className = 'banner-sparkle' + (isStar ? ' is-star' : '');

            // Bande gauche (0-17%) pour la moitié des étincelles, bande droite (83-100%) pour l'autre.
            const onLeft = i % 2 === 0;
            const horizontal = onLeft ? (Math.random() * 17) : (83 + Math.random() * 17);
            const vertical = 4 + Math.random() * 92;
            const size = isStar ? (8 + Math.random() * 10) : (3 + Math.random() * 4);
            const duration = (1.6 + Math.random() * 2).toFixed(2);
            const delay = (-(Math.random() * 3)).toFixed(2); // délai négatif : démarre déjà "en vol"

            sparkle.style.left = horizontal + '%';
            sparkle.style.top = vertical + '%';
            sparkle.style.width = size + 'px';
            sparkle.style.height = size + 'px';
            sparkle.style.animationDuration = duration + 's';
            sparkle.style.animationDelay = delay + 's';

            layer.appendChild(sparkle);
        }
        banner.appendChild(layer);
    });

    // --- Reflet "diamant" qui suit la souris sur les bannières par défaut (search-banner) ---
    // Ces bannières n'ont plus de photo de fond mais un dégradé sombre façon diamant (voir
    // .search-banner dans style.css) ; on met à jour les variables CSS --mx/--my (position du
    // curseur en % dans la bannière) pour que son reflet radial se déplace avec la souris.
    // Ignoré si l'utilisateur préfère moins d'animations : le reflet reste alors fixe (valeurs
    // par défaut définies dans le CSS), cohérent avec le reste des effets de bannière.
    if (!prefersReducedMotion) {
        document.querySelectorAll('.search-banner').forEach(banner => {
            banner.addEventListener('mousemove', (e) => {
                const rect = banner.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;
                banner.style.setProperty('--mx', x + '%');
                banner.style.setProperty('--my', y + '%');
            });
            banner.addEventListener('mouseleave', () => {
                banner.style.setProperty('--mx', '50%');
                banner.style.setProperty('--my', '35%');
            });
        });
    }

    // --- Écrans "squelette" (effet de scintillement) pour les images en chargement différé ---
    // Donne une sensation de rapidité perçue pendant que les images loading="lazy" se chargent,
    // à la place d'un espace vide brut. Retiré dès que chaque image a fini de charger (ou échoué).
    document.querySelectorAll('img[loading="lazy"]').forEach((img) => {
        if (img.complete && img.naturalWidth > 0) return;
        img.classList.add('img-skeleton');
        const clearSkeleton = () => img.classList.remove('img-skeleton');
        img.addEventListener('load', clearSkeleton, { once: true });
        img.addEventListener('error', clearSkeleton, { once: true });
    });

    // --- Palette de commande (Ctrl+K / Cmd+K) ---
    // Raccourci de navigation façon "quick launcher", avec la mascotte robot du site, alimentée
    // par window.ISSTM_PALETTE_ITEMS (injecté par footer.php selon la session en cours) et
    // débouchant sur la recherche intelligente du site (recherche.php) pour toute requête libre.
    (function initCommandPalette() {
        const items = Array.isArray(window.ISSTM_PALETTE_ITEMS) ? window.ISSTM_PALETTE_ITEMS : [];
        const labels = window.ISSTM_PALETTE_LABELS || {};
        const searchUrl = window.ISSTM_PALETTE_SEARCH_URL || 'recherche.php';

        const overlay = document.createElement('div');
        overlay.className = 'cmdk-overlay';
        overlay.setAttribute('aria-hidden', 'true');
        overlay.innerHTML = `
            <div class="cmdk-panel" role="dialog" aria-modal="true" aria-label="${labels.placeholder ? labels.placeholder.replace(/"/g, '&quot;') : 'Recherche rapide'}">
                <div class="cmdk-input-row">
                    <i class="fas fa-robot cmdk-robot-icon"></i>
                    <input type="text" class="cmdk-input" autocomplete="off" spellcheck="false" placeholder="${labels.placeholder ? labels.placeholder.replace(/"/g, '&quot;') : 'Rechercher...'}">
                    <kbd class="cmdk-esc-hint">Esc</kbd>
                </div>
                <div class="cmdk-results"></div>
                <div class="cmdk-footer">
                    <span><kbd>&uarr;</kbd><kbd>&darr;</kbd> naviguer</span>
                    <span><kbd>Entrée</kbd> ouvrir</span>
                    <span><kbd>Esc</kbd> fermer</span>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        const input = overlay.querySelector('.cmdk-input');
        const resultsEl = overlay.querySelector('.cmdk-results');
        let activeIndex = 0;
        let currentResults = [];

        function buildResults(query) {
            const q = query.trim().toLowerCase();
            const filtered = q === '' ? items : items.filter((it) => it.label.toLowerCase().includes(q));
            const results = filtered.map((it) => ({ type: 'nav', label: it.label, href: it.href, icon: it.icon }));
            if (q !== '') {
                results.push({
                    type: 'search',
                    label: (labels.searchFor || 'Rechercher « %s »').replace('%s', query.trim()),
                    href: searchUrl + '?q=' + encodeURIComponent(query.trim()),
                    icon: 'fa-magnifying-glass',
                });
            }
            return results;
        }

        function render(query) {
            currentResults = buildResults(query);
            activeIndex = 0;
            if (currentResults.length === 0) {
                resultsEl.innerHTML = `<div class="cmdk-empty">${labels.noResults || 'Aucun résultat.'}</div>`;
                return;
            }
            resultsEl.innerHTML = currentResults.map((r, i) => `
                <a href="${r.href}" class="cmdk-item${i === 0 ? ' is-active' : ''}${r.type === 'search' ? ' cmdk-item-search' : ''}" data-index="${i}">
                    <i class="fas ${r.icon}"></i>
                    <span>${r.label}</span>
                </a>
            `).join('');
        }

        function setActive(index) {
            const els = resultsEl.querySelectorAll('.cmdk-item');
            if (els.length === 0) return;
            activeIndex = (index + els.length) % els.length;
            els.forEach((el, i) => el.classList.toggle('is-active', i === activeIndex));
            els[activeIndex].scrollIntoView({ block: 'nearest' });
        }

        function openPalette() {
            overlay.classList.add('is-open');
            document.documentElement.style.overflow = 'hidden';
            input.value = '';
            render('');
            setTimeout(() => input.focus(), 30);
        }

        function closePalette() {
            overlay.classList.remove('is-open');
            document.documentElement.style.overflow = '';
        }

        function activateResult(index) {
            const target = currentResults[index];
            if (!target) return;
            closePalette();
            isstmNavigate(target.href);
        }

        input.addEventListener('input', () => render(input.value));

        input.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') { e.preventDefault(); setActive(activeIndex + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(activeIndex - 1); }
            else if (e.key === 'Enter') { e.preventDefault(); activateResult(activeIndex); }
            else if (e.key === 'Escape') { e.preventDefault(); closePalette(); }
        });

        resultsEl.addEventListener('mousemove', (e) => {
            const item = e.target.closest('.cmdk-item');
            if (!item) return;
            setActive(Number(item.dataset.index));
        });

        resultsEl.addEventListener('click', (e) => {
            const item = e.target.closest('.cmdk-item');
            if (!item) return;
            e.preventDefault();
            activateResult(Number(item.dataset.index));
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closePalette();
        });

        document.addEventListener('keydown', (e) => {
            const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
            const modifier = isMac ? e.metaKey : e.ctrlKey;
            if (modifier && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                if (overlay.classList.contains('is-open')) closePalette();
                else openPalette();
            } else if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
                closePalette();
            }
        });
    })();

    // --- Jauge de défilement de la page (pourcentage), robot animé au centre ---
    // Widget flottant en bas à droite : 0% au chargement, 100% une fois arrivé en bas de page.
    // Masqué si la page tient entière à l'écran (rien à défiler).
    (function initScrollGauge() {
        const gauge = document.createElement('div');
        gauge.className = 'scroll-gauge';
        gauge.setAttribute('role', 'button');
        gauge.setAttribute('tabindex', '0');
        gauge.setAttribute('aria-label', 'Retour en haut de page');

        const RADIUS = 28;
        const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

        gauge.innerHTML = `
            <svg class="scroll-gauge-ring" viewBox="0 0 64 64">
                <circle class="scroll-gauge-track" cx="32" cy="32" r="${RADIUS}"></circle>
                <circle class="scroll-gauge-fill" cx="32" cy="32" r="${RADIUS}"
                    stroke-dasharray="${CIRCUMFERENCE}" stroke-dashoffset="${CIRCUMFERENCE}"></circle>
            </svg>
            <div class="scroll-gauge-robot">
                <span class="sgr-antenna"></span>
                <span class="sgr-head">
                    <span class="sgr-eye sgr-eye-l"></span>
                    <span class="sgr-eye sgr-eye-r"></span>
                </span>
                <span class="sgr-body">
                    <span class="sgr-arm sgr-arm-l"></span>
                    <span class="sgr-arm sgr-arm-r"></span>
                </span>
            </div>
            <span class="scroll-gauge-percent">0%</span>
        `;
        document.body.appendChild(gauge);

        const fillCircle = gauge.querySelector('.scroll-gauge-fill');
        const percentLabel = gauge.querySelector('.scroll-gauge-percent');

        let ticking = false;
        function updateScrollGauge() {
            ticking = false;
            const scrollTop = window.scrollY || document.documentElement.scrollTop;
            const scrollable = document.documentElement.scrollHeight - window.innerHeight;

            if (scrollable <= 20) {
                // Rien (ou presque rien) à défiler sur cette page : la jauge n'a pas de sens.
                gauge.classList.remove('is-visible');
                return;
            }
            gauge.classList.add('is-visible');

            const percent = Math.min(100, Math.max(0, Math.round((scrollTop / scrollable) * 100)));
            fillCircle.setAttribute('stroke-dashoffset', CIRCUMFERENCE * (1 - percent / 100));
            percentLabel.textContent = percent + '%';
        }

        function onScrollOrResize() {
            if (!ticking) {
                ticking = true;
                window.requestAnimationFrame(updateScrollGauge);
            }
        }

        window.addEventListener('scroll', onScrollOrResize, { passive: true });
        window.addEventListener('resize', onScrollOrResize);
        updateScrollGauge(); // état initial (0% au chargement, ou masqué si non défilable)

        function scrollToTop() {
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
        }
        gauge.addEventListener('click', scrollToTop);
        gauge.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                scrollToTop();
            }
        });
    })();

    // --- Gestion du Menu Mobile ---
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');

    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
        });
    }

    // --- En-tête intelligent au défilement ---
    const mainHeader = document.querySelector('.main-header');
    if (mainHeader) {
        let lastScrollY = window.scrollY;
        window.addEventListener('scroll', () => {
            if (lastScrollY < window.scrollY && window.scrollY > 150) {
                mainHeader.classList.add('header-hidden');
            } else {
                mainHeader.classList.remove('header-hidden');
            }
            lastScrollY = window.scrollY;
        });
    }

    // --- Formatage compact des grands nombres : 1500 -> "1.5K", 100000 -> "100K", 5000000 -> "5M" ---
    function formatCompactNumber(n) {
        n = Math.floor(n);
        if (n < 1000) return String(n);
        const units = [{ value: 1e9, suffix: 'B' }, { value: 1e6, suffix: 'M' }, { value: 1e3, suffix: 'K' }];
        for (let i = 0; i < units.length; i++) {
            let u = units[i];
            if (n >= u.value) {
                let v = Math.round((n / u.value) * 10) / 10;
                if (v >= 1000 && i > 0) {
                    u = units[i - 1];
                    v = Math.round((n / u.value) * 10) / 10;
                }
                return (Number.isInteger(v) ? v.toString() : v.toFixed(1)) + u.suffix;
            }
        }
        return String(n);
    }

    // --- Animation du compteur de statistiques ---
    function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-target'), 10);
        const duration = 2000;
        let startTimestamp = null;

        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const currentValue = Math.floor(progress * target);
            element.innerText = `+${formatCompactNumber(currentValue)}`;

            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                element.innerText = `+${formatCompactNumber(target)}`;
            }
        };
        window.requestAnimationFrame(step);
    }

    // --- Animation d'apparition au défilement (Intersection Observer) ---
    const animationObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                if (entry.target.classList.contains('stat-number')) {
                    animateCounter(entry.target);
                }
                // observer.unobserve(entry.target); // Optionnel: pour que l'animation ne se joue qu'une fois
            }
        });
    }, {
        threshold: 0.01
    });

    const elementsToAnimate = document.querySelectorAll('.stat-box, .director-section, .contact-section, .map-section, .content-block, .mention-card, .teacher-card, .intro-text, .enseignants-dissertation, .stat-number, .slide-in-right, .slide-in-left, .animate-on-scroll');
    elementsToAnimate.forEach(el => animationObserver.observe(el));

    // --- Animation de la sidebar "collante" sur la page d'inscription ---
    const stickySidebar = document.querySelector('.inscription-sidebar .sticky-content');
    const sentinel = document.querySelector('.page-banner');
    if (stickySidebar && sentinel) {
        const handler = (entries) => {
            if (!entries[0].isIntersecting) {
                stickySidebar.classList.add('is-sticky');
            } else {
                stickySidebar.classList.remove('is-sticky');
            }
        };
        const observer = new IntersectionObserver(handler, { threshold: [0] });
        observer.observe(sentinel);
    }

    // --- Filtres Filières (niveau L1-M2 / mention STI-STGC-STNPA) sur l'accueil ---
    const filieresGrid = document.getElementById('filieres-grid');
    if (filieresGrid) {
        const filterGroups = document.querySelectorAll('.filieres-filter-buttons');
        const emptyMsg = document.getElementById('filieres-filter-empty');
        const activeFilieresFilters = { niveau: 'tous', mention: 'tous' };

        const setFiliereCardVisibility = (card, show) => {
            if (show) {
                card.style.display = '';
                // Force le navigateur à prendre en compte le display avant de retirer la classe,
                // sinon la transition d'entrée ne rejoue pas (élément déjà "final" au 1er frame).
                requestAnimationFrame(() => card.classList.remove('filiere-filtered-out'));
            } else {
                card.classList.add('filiere-filtered-out');
                const onEnd = (e) => {
                    if (e.propertyName === 'opacity' && card.classList.contains('filiere-filtered-out')) {
                        card.style.display = 'none';
                        card.removeEventListener('transitionend', onEnd);
                    }
                };
                card.addEventListener('transitionend', onEnd);
            }
        };

        // Un parcours suit tout son cycle Licence (L1->L2->L3) ou Master (M1->M2) sans en
        // sortir : on regroupe donc le filtre par cycle plutôt que par année isolée, ce qui
        // évite d'avoir des boutons L1/L2/L3 quasi identiques (et donc trompeurs).
        const NIVEAU_GROUPS = { licence: ['L1', 'L2', 'L3'], master: ['M1', 'M2'] };

        const applyFilieresFilters = () => {
            let visibleCount = 0;
            filieresGrid.querySelectorAll('.filiere-card').forEach((card) => {
                const niveaux = (card.dataset.niveaux || '').split(',');
                const mention = card.dataset.mention || '';
                const wantedNiveaux = NIVEAU_GROUPS[activeFilieresFilters.niveau];
                const matchNiveau = activeFilieresFilters.niveau === 'tous' || (wantedNiveaux && wantedNiveaux.some((n) => niveaux.includes(n)));
                const matchMention = activeFilieresFilters.mention === 'tous' || mention === activeFilieresFilters.mention;
                const show = matchNiveau && matchMention;
                setFiliereCardVisibility(card, show);
                if (show) visibleCount++;
            });
            if (emptyMsg) emptyMsg.hidden = visibleCount !== 0;
        };

        filterGroups.forEach((group) => {
            const axis = group.dataset.filterAxis;
            group.querySelectorAll('.filiere-filter-btn').forEach((btn) => {
                btn.addEventListener('click', () => {
                    group.querySelectorAll('.filiere-filter-btn').forEach((b) => b.classList.remove('is-active'));
                    btn.classList.add('is-active');
                    activeFilieresFilters[axis] = btn.dataset.value;
                    applyFilieresFilters();
                });
            });
        });
    }

    // --- Boutons de défilement (haut/bas) pour la sidebar d'inscription ---
    const sidebarPanel = document.querySelector('.inscription-sidebar .sidebar-panel');
    const sidebarScrollUp = document.querySelector('.sidebar-scroll-up');
    const sidebarScrollDown = document.querySelector('.sidebar-scroll-down');
    if (sidebarPanel && sidebarScrollUp && sidebarScrollDown) {
        const SCROLL_STEP = 180;
        sidebarScrollUp.addEventListener('click', () => {
            sidebarPanel.scrollBy({ top: -SCROLL_STEP, behavior: 'smooth' });
        });
        sidebarScrollDown.addEventListener('click', () => {
            sidebarPanel.scrollBy({ top: SCROLL_STEP, behavior: 'smooth' });
        });
        const updateSidebarScrollButtons = () => {
            const atTop = sidebarPanel.scrollTop <= 2;
            const atBottom = sidebarPanel.scrollTop + sidebarPanel.clientHeight >= sidebarPanel.scrollHeight - 2;
            sidebarScrollUp.classList.toggle('is-hidden', atTop);
            sidebarScrollDown.classList.toggle('is-hidden', atBottom);
        };
        sidebarPanel.addEventListener('scroll', updateSidebarScrollButtons);
        window.addEventListener('resize', updateSidebarScrollButtons);
        updateSidebarScrollButtons();
    }

    // --- Animations Avancées de la Section Héros ---
    const heroTitles = document.querySelectorAll('.animated-hero-title');
    const particleContainer = document.getElementById('hero-particles');
    const heroContent = document.querySelector('.hero-content');

    // 1. Animation du titre mot par mot, ligne par ligne
    if (heroTitles.length > 0) {
        let totalDelay = 0;
        heroTitles.forEach((heroTitle, lineIndex) => {
            const text = heroTitle.dataset.text;
            const words = text.split(' ');
            words.forEach((word, index) => {
                const wordSpan = document.createElement('span');
                wordSpan.className = 'word';
                const innerSpan = document.createElement('span');
                innerSpan.className = 'inner-word';
                innerSpan.innerText = word;
                wordSpan.appendChild(innerSpan);
                heroTitle.appendChild(wordSpan);
                heroTitle.appendChild(document.createTextNode(' '));
                innerSpan.style.setProperty('--delay', `${totalDelay}s`);
                totalDelay += 0.1;
            });
            if (lineIndex < heroTitles.length - 1) {
                totalDelay += 0.2;
            }
        });
    }

    // 2. Création des particules animées
    if (particleContainer) {
        const numParticles = 120;
        for (let i = 0; i < numParticles; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            const size = Math.random() * 5 + 1;
            const startX = Math.random() * 100;
            const duration = Math.random() * 15 + 10;
            const delay = Math.random() * 10;

            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            particle.style.left = `${startX}%`;
            particle.style.animationDuration = `${duration}s`;
            particle.style.animationDelay = `${delay}s`;
            particleContainer.appendChild(particle);
        }
    }

    // 3. Effet de parallaxe à la souris
    if (heroContent) {
        document.body.addEventListener('mousemove', (e) => {
            const { clientX, clientY } = e;
            const { innerWidth, innerHeight } = window;
            const moveX = (clientX / innerWidth - 0.5) * 2;
            const moveY = (clientY / innerHeight - 0.5) * 2;
            heroContent.style.transform = `translate(${moveX * -10}px, ${moveY * -10}px)`;
        });
    }

    // --- Confirmation de Déconnexion ---
    // Sélectionne TOUS les boutons de déconnexion
    const logoutButtons = document.querySelectorAll('.btn-logout-text, .btn-logout-icon');
    logoutButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const logoutUrl = this.href;
            const userName = this.getAttribute('data-username');

            Swal.fire({
                title: `Au revoir, ${userName} !`,
                text: "Êtes-vous sûr de vouloir vous déconnecter ?",
                // Remplacement de l'icône par défaut par une icône Font Awesome personnalisée
                iconHtml: '<i class="fas fa-sign-out-alt"></i>',
                showCancelButton: true,
                confirmButtonText: 'Déconnecter',
                cancelButtonText: 'Annuler',
                reverseButtons: true,
                // Ajout des classes pour un style personnalisé
                customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    icon: 'custom-swal-icon', // Classe pour notre nouvelle icône
                    confirmButton: 'btn-swal-confirm',
                    cancelButton: 'btn-swal-cancel'
                },
                // Active l'arrière-plan pour pouvoir le styler
                backdrop: true,
                // Ajout d'animations
                showClass: {
                    popup: 'swal2-show',
                    backdrop: 'swal2-backdrop-show'
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = logoutUrl;
                }
            });
        });
    });

    // --- Traduction Automatique pour le formulaire d'administration ---
    // On s'assure d'être sur la bonne page
    const adminForm = document.querySelector('.admin-form');
    if (adminForm) {
        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), delay);
            };
        }

        let activeTranslation = null;

        async function translateText(text, sourceLang, targetLang, targetElement) {
            if (!text.trim()) {
                targetElement.value = '';
                return;
            }
            const originalPlaceholder = targetElement.placeholder;
            targetElement.placeholder = 'Traduction en cours...';
            activeTranslation = targetElement;
            try {
                const response = await fetch('translate_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ text: text, source_lang: sourceLang, target_lang: targetLang })
                });
                const data = await response.json();
                if (data.error) {
                    targetElement.placeholder = 'Traduction indisponible';
                } else {
                    targetElement.value = data.translatedText || '';
                }
            } catch (error) {
                console.error('Erreur de traduction:', error);
                targetElement.placeholder = 'Erreur de traduction';
            } finally {
                if (activeTranslation === targetElement) {
                    activeTranslation = null;
                }
                setTimeout(() => { targetElement.placeholder = originalPlaceholder; }, 2000);
            }
        }

        function triggerTranslation(sourceField) {
            const allLangs = ['fr', 'en', 'mg'];
            const sourceText = sourceField.value;
            const sourceName = sourceField.name;
            const sourceLang = allLangs.find(lang => sourceName.includes(`[${lang}]`));
            if (!sourceLang) return;
            const baseName = sourceName.replace(`[${sourceLang}]`, '');
            const group = sourceField.closest('.translatable-field-group');
            allLangs.forEach(targetLang => {
                if (targetLang !== sourceLang) {
                    const targetField = group.querySelector(`[name="${baseName}[${targetLang}]"]`);
                    if (targetField) {
                        translateText(sourceText, sourceLang, targetLang, targetField);
                    }
                }
            });
        }

        const debouncedTranslate = debounce(triggerTranslation, 800);
        adminForm.querySelectorAll('input[name*="[content]"], textarea[name*="[content]"]').forEach(sourceField => {
            sourceField.addEventListener('input', () => debouncedTranslate(sourceField));
        });

        adminForm.querySelectorAll('.btn-translate').forEach(button => {
            button.addEventListener('click', () => {
                const sourceField = button.parentElement.querySelector('input, textarea');
                triggerTranslation(sourceField);
            });
        });
    }

    // --- Gestion du clic sur les overlays d'upload d'image ---
    document.querySelectorAll('.image-preview-container').forEach(container => {
        const uploadOverlay = container.querySelector('.upload-overlay');
        const imagePreview = container.querySelector('img');
        const fileInput = container.querySelector('.hidden-file-input');

        if (uploadOverlay && fileInput && imagePreview) {
            uploadOverlay.addEventListener('click', () => {
                fileInput.click();
            });

            fileInput.addEventListener('change', (event) => {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        imagePreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }

            });
        }
    });

    // --- Gestion des diaporamas pour les cartes portail ---
    const portalCards = document.querySelectorAll('.portal-card.with-slideshow');
    portalCards.forEach(card => {
        const slides = card.querySelectorAll('.portal-slide');
        if (slides.length > 0) {
            let currentSlideIndex = 0;
            slides[0].style.opacity = 1;
            setInterval(() => {
                slides[currentSlideIndex].style.opacity = 0;
                currentSlideIndex = (currentSlideIndex + 1) % slides.length;
                slides[currentSlideIndex].style.opacity = 1;
            }, 4000);
        }
    });

    // --- Animation pour les étiquettes de formulaire flottantes ---
    document.querySelectorAll('.form-group input, .form-group textarea').forEach(input => {
        // Vérifier au chargement si le champ est déjà rempli (ex: autocomplete)
        if (input.value.trim() !== '') {
            input.parentElement.classList.add('has-content');
        }
        input.addEventListener('focus', () => {
            input.parentElement.classList.add('has-content');
        });
        input.addEventListener('blur', () => {
            if (input.value.trim() === '') {
                input.parentElement.classList.remove('has-content');
            }
        });
    });
    document.querySelectorAll('.form-group select').forEach(select => {
        // Pour les listes déroulantes, on considère qu'elles ont toujours du contenu pour que le label reste en haut
        select.parentElement.classList.add('has-content');
    });

    // --- Bouton "voir le mot de passe" (œil) sur tous les champs mot de passe du site : ajouté
    // comme simple frère de l'input (jamais un wrapper autour) pour ne pas casser les sélecteurs
    // CSS de l'étiquette flottante ci-dessus, qui dépendent de input.parentElement === .form-group. ---
    document.querySelectorAll('input[type="password"]').forEach(input => {
        if (input.dataset.pwToggleInit) return;
        input.dataset.pwToggleInit = '1';
        input.classList.add('has-password-toggle');
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'password-toggle-btn';
        btn.setAttribute('aria-label', 'Afficher le mot de passe');
        btn.innerHTML = '<i class="fas fa-eye"></i>';
        input.insertAdjacentElement('afterend', btn);
        btn.addEventListener('click', () => {
            const willShow = input.type === 'password';
            input.type = willShow ? 'text' : 'password';
            btn.innerHTML = willShow ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
            btn.setAttribute('aria-label', willShow ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
            input.focus();
        });
    });

    // --- Lightbox Initialization ---
    globalLightbox = document.getElementById('lightbox');
    if (globalLightbox) {
        globalLightboxImg = document.getElementById('lightbox-img');
        globalLightboxClose = globalLightbox.querySelector('.lightbox-close');
        globalLightboxPrev = globalLightbox.querySelector('.lightbox-prev');
        globalLightboxNext = globalLightbox.querySelector('.lightbox-next');

        if (globalLightboxClose) globalLightboxClose.addEventListener('click', window.closeLightbox);
        if (globalLightboxPrev) globalLightboxPrev.addEventListener('click', (e) => { e.stopPropagation(); window.changeLightboxImage(-1); });
        if (globalLightboxNext) globalLightboxNext.addEventListener('click', (e) => { e.stopPropagation(); window.changeLightboxImage(1); });
        globalLightbox.addEventListener('click', (e) => { if (e.target === globalLightbox) window.closeLightbox(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && globalLightbox.style.display === 'block') window.closeLightbox(); });
    }

    // --- Logique pour le carousel "Découvrir nos pages" ---
    const pagesTrack = document.getElementById('pagesCarouselTrack');
    const pagesPrevBtn = document.getElementById('pagesCarouselPrev');
    const pagesNextBtn = document.getElementById('pagesCarouselNext');
    if (pagesTrack && pagesPrevBtn && pagesNextBtn) {
        const pagesWrapper = pagesTrack.closest('.pages-carousel-wrapper');
        const AUTO_SCROLL_DELAY = 1500;
        let autoScrollTimer = null;

        const getScrollStep = () => {
            const card = pagesTrack.querySelector('.page-card');
            if (!card) return pagesTrack.clientWidth;
            const gap = parseFloat(getComputedStyle(pagesTrack).gap) || 0;
            return card.offsetWidth + gap;
        };

        const getMaxScroll = () => pagesTrack.scrollWidth - pagesTrack.clientWidth - 2;

        const updateNavState = () => {
            const maxScroll = getMaxScroll();
            pagesPrevBtn.classList.toggle('is-disabled', pagesTrack.scrollLeft <= 0);
            pagesNextBtn.classList.toggle('is-disabled', pagesTrack.scrollLeft >= maxScroll);
        };

        const advanceCarousel = () => {
            if (pagesTrack.scrollLeft >= getMaxScroll()) {
                pagesTrack.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                pagesTrack.scrollBy({ left: getScrollStep(), behavior: 'smooth' });
            }
        };

        const startAutoScroll = () => {
            stopAutoScroll();
            autoScrollTimer = setInterval(advanceCarousel, AUTO_SCROLL_DELAY);
        };

        function stopAutoScroll() {
            if (autoScrollTimer) {
                clearInterval(autoScrollTimer);
                autoScrollTimer = null;
            }
        }

        pagesPrevBtn.addEventListener('click', () => {
            pagesTrack.scrollBy({ left: -getScrollStep(), behavior: 'smooth' });
            startAutoScroll(); // Redémarre le minuteur après une action manuelle
        });
        pagesNextBtn.addEventListener('click', () => {
            pagesTrack.scrollBy({ left: getScrollStep(), behavior: 'smooth' });
            startAutoScroll();
        });
        pagesTrack.addEventListener('scroll', updateNavState);
        window.addEventListener('resize', updateNavState);

        if (pagesWrapper) {
            pagesWrapper.addEventListener('mouseenter', stopAutoScroll);
            pagesWrapper.addEventListener('mouseleave', startAutoScroll);
            pagesWrapper.addEventListener('touchstart', stopAutoScroll, { passive: true });
            pagesWrapper.addEventListener('touchend', startAutoScroll);
        }

        updateNavState();
        startAutoScroll();
    }

    // --- Logique pour la modale FAQ (footer) ---
    const faqLink = document.getElementById('faq-link');
    const faqModal = document.getElementById('faqModal');
    if (faqLink && faqModal) {
        const closeFaqModal = () => {
            faqModal.classList.remove('show');
            setTimeout(() => { faqModal.style.display = 'none'; }, 300);
        };
        faqLink.addEventListener('click', (e) => {
            e.preventDefault();
            faqModal.style.display = 'block';
            setTimeout(() => faqModal.classList.add('show'), 10);
        });
        const faqCloseButton = faqModal.querySelector('.close-button');
        if (faqCloseButton) faqCloseButton.addEventListener('click', closeFaqModal);
        faqModal.addEventListener('click', (e) => { if (e.target === faqModal) closeFaqModal(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && faqModal.classList.contains('show')) closeFaqModal(); });
    }

    // --- Logique pour les panneaux dépliants de l'organigramme (parcours.php) ---
    const ogToggleButtons = document.querySelectorAll('.og-card-toggle');
    if (ogToggleButtons.length > 0) {
        const getOpenAncestorPanels = (panel) => {
            const list = [];
            let ancestor = panel.parentElement ? panel.parentElement.closest('.og-panel') : null;
            while (ancestor) {
                if (ancestor.classList.contains('is-open')) list.push(ancestor);
                ancestor = ancestor.parentElement ? ancestor.parentElement.closest('.og-panel') : null;
            }
            return list;
        };

        ogToggleButtons.forEach(button => {
            const panel = document.getElementById(button.dataset.target);
            if (!panel) return;

            button.addEventListener('click', () => {
                const isOpen = panel.classList.contains('is-open');

                // Pendant une transition CSS, scrollHeight des ancêtres ne reflète pas
                // encore la nouvelle taille du panneau enfant. On calcule donc la
                // différence de hauteur AVANT de modifier le panneau, et on la
                // répercute directement sur les panneaux ancêtres déjà ouverts,
                // sinon leur contenu (ex: Génie Civil, Coopération, Statistiques)
                // reste coupé par une hauteur maximale figée trop petite.
                const oldContribution = isOpen ? panel.scrollHeight : 0;
                const ancestors = getOpenAncestorPanels(panel);
                const ancestorBaseHeights = ancestors.map(a => a.scrollHeight);

                if (isOpen) {
                    panel.classList.remove('is-open');
                    button.classList.remove('is-active');
                    button.setAttribute('aria-expanded', 'false');
                    panel.style.maxHeight = '0px';
                } else {
                    panel.classList.add('is-open');
                    button.classList.add('is-active');
                    button.setAttribute('aria-expanded', 'true');
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                }

                const newContribution = isOpen ? 0 : panel.scrollHeight;
                const delta = newContribution - oldContribution;
                ancestors.forEach((ancestor, i) => {
                    ancestor.style.maxHeight = Math.max(0, ancestorBaseHeights[i] + delta) + 'px';
                });
            });
        });

        // Recalcule la hauteur des panneaux ouverts si la fenêtre est redimensionnée
        // (le texte peut changer de nombre de lignes et donc la hauteur du contenu).
        window.addEventListener('resize', () => {
            document.querySelectorAll('.og-panel.is-open').forEach(panel => {
                panel.style.maxHeight = panel.scrollHeight + 'px';
            });
        });
    }

    // --- Generic Modal Logic (e.g., for status modals) ---
    const openModalButtons = document.querySelectorAll('[data-modal-target]');
    openModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = document.querySelector(button.dataset.modalTarget);
            if (modal) {
                window.openModal(modal);
            }
        });
    });

    // --- Logique pour la galerie photo sur associations.php ---
    const galleryItems = document.querySelectorAll('.gallery-grid .gallery-item');
    if (galleryItems.length > 0) {
        const galleryImageUrls = Array.from(galleryItems).map(item => item.querySelector('img').src);
        galleryItems.forEach((item, index) => {
            item.addEventListener('click', () => {
                window.openLightbox(galleryImageUrls[index], galleryImageUrls);
            });
        });
    }
});

// --- Recherche rapide dans les listes admin de la bibliothèque (bibliotheque/admin/*.php) ---
// Classe dédiée (.bib-quick-filter), sans impact sur le reste du site.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.bib-quick-filter').forEach((barre) => {
        const input = barre.querySelector('input');
        const liste = document.querySelector(barre.dataset.target);
        if (!input || !liste) return;
        input.addEventListener('input', () => {
            const terme = input.value.trim().toLowerCase();
            liste.querySelectorAll('.admin-album-row').forEach((ligne) => {
                ligne.style.display = ligne.textContent.toLowerCase().includes(terme) ? '' : 'none';
            });
        });
    });
});

// --- Confirmations stylées (SweetAlert2) à la place des confirm() natifs du navigateur ---
// Contrat pour les formulaires PHP :
//   - Cas simple (un seul bouton dans le formulaire) : <form class="js-confirm-submit" data-confirm-msg="...">
//   - Cas bouton multiple (formaction/name/value partagés dans un même <form>) : <button class="js-confirm-btn" data-confirm-msg="...">
// Dans les deux cas, le message est injecté via htmlspecialchars() côté PHP (attribut HTML, pas une chaîne JS).
(function () {
    if (typeof Swal === 'undefined') return;

    function askConfirm(msg) {
        return Swal.fire({
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirmer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#c0392b',
            cancelButtonColor: '#7f8c8d',
            reverseButtons: true,
        }).then((result) => result.isConfirmed);
    }
    window.iSSTMConfirm = askConfirm;

    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || !form.classList.contains('js-confirm-submit')) return;
        if (form.dataset.confirmed === '1') { delete form.dataset.confirmed; return; }
        e.preventDefault();
        const submitter = e.submitter || undefined;
        askConfirm(form.dataset.confirmMsg || 'Confirmer cette action ?').then((yes) => {
            if (yes) {
                form.dataset.confirmed = '1';
                form.requestSubmit(submitter);
            }
        });
    });

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.js-confirm-btn');
        if (!btn) return;
        const form = btn.form || btn.closest('form');
        if (!form) return;
        e.preventDefault();
        askConfirm(btn.dataset.confirmMsg || 'Confirmer cette action ?').then((yes) => {
            if (yes) form.requestSubmit(btn);
        });
    });

    // Bouton "document réservé aux étudiants" cliqué par un compte déjà connecté mais qui n'est
    // pas étudiant (voir documents.php) : simple message d'accès refusé, pas de navigation.
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.js-doc-etudiant-only');
        if (!btn) return;
        Swal.fire({
            icon: 'error',
            title: btn.dataset.popupTitle || 'Accès réservé',
            text: btn.dataset.popupText || 'Ce document est réservé aux comptes étudiants.',
            confirmButtonColor: '#d4a017',
        });
    });

    // --- Cloche de notifications Communauté (header.php) : chargement au survol/clic, une seule
    // fois par ouverture. Le marquage "lu" n'est plus un effet de bord du simple chargement : il
    // se fait individuellement au clic (notification_ouvrir.php) ou explicitement depuis la page
    // complète notifications.php — le badge reflète donc le vrai nombre non lu tant qu'on n'a pas
    // cliqué une notification ou rechargé la page.
    (function initCommunauteNotifications() {
        const dropdown = document.querySelector('.communaute-notif-dropdown');
        if (!dropdown) return;
        const list = dropdown.querySelector('.communaute-notif-list');
        let loaded = false;

        function loadNotifications() {
            if (loaded) return;
            loaded = true;
            fetch('communaute_notifications.php')
                .then((r) => r.json())
                .then((data) => {
                    const items = data.items || [];
                    if (items.length === 0) {
                        list.innerHTML = '<p class="communaute-notif-empty">' + (list.dataset.emptyLabel || 'Aucune notification pour le moment.') + '</p>';
                    } else {
                        list.innerHTML = items.map((n) => (
                            '<a class="communaute-notif-item' + (n.is_read ? '' : ' is-unread') + '" href="notification_ouvrir.php?id=' + n.id + '&redirect=' + encodeURIComponent(n.link) + '">' +
                                '<p>' + n.message + '</p>' +
                                '<span>' + n.created_at + '</span>' +
                            '</a>'
                        )).join('');
                    }
                })
                .catch(() => {
                    loaded = false;
                });
        }

        dropdown.addEventListener('mouseenter', loadNotifications);
        dropdown.querySelector('.communaute-notif-toggle')?.addEventListener('click', (e) => {
            e.preventDefault();
            loadNotifications();
        });
    })();
})();