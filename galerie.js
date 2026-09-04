document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('album-photo-grid');
    if (!grid) return;

    const items = Array.from(grid.querySelectorAll('.photo-grid-item'));
    const lightbox = document.getElementById('gal-lightbox');
    const lbImg = document.getElementById('gal-lb-img');
    const lbTitle = document.getElementById('gal-lb-title');
    const lbDesc = document.getElementById('gal-lb-desc');
    const lbDate = document.getElementById('gal-lb-date');
    const lbLocation = document.getElementById('gal-lb-location');
    const lbPhotographer = document.getElementById('gal-lb-photographer');
    const lbCurrent = document.getElementById('gal-lb-current');
    const lbTotal = document.getElementById('gal-lb-total');
    const lbDownload = document.getElementById('gal-lb-download');
    const lbStage = document.querySelector('.gal-lightbox-stage');
    const btnClose = document.getElementById('gal-lb-close');
    const btnPrev = document.getElementById('gal-lb-prev');
    const btnNext = document.getElementById('gal-lb-next');
    const btnZoom = document.getElementById('gal-lb-zoom');
    const btnFullscreen = document.getElementById('gal-lb-fullscreen');
    const btnShare = document.getElementById('gal-lb-share');

    let currentIndex = 0;
    let isZoomed = false;

    lbTotal.textContent = items.length;

    function clearVideoEmbed() {
        const existing = lbStage.querySelector('.gal-lb-video');
        if (existing) existing.remove();
        lbImg.style.display = '';
    }

    function renderSlide(index) {
        const item = items[index];
        if (!item) return;
        currentIndex = index;
        isZoomed = false;
        lbImg.classList.remove('is-zoomed');
        clearVideoEmbed();

        if (item.dataset.type === 'video') {
            lbImg.style.display = 'none';
            if (item.dataset.videoType === 'iframe') {
                const iframe = document.createElement('iframe');
                iframe.className = 'gal-lb-video';
                iframe.src = item.dataset.videoEmbed;
                iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
                iframe.setAttribute('allowfullscreen', '');
                lbStage.appendChild(iframe);
            } else {
                const video = document.createElement('video');
                video.className = 'gal-lb-video';
                video.src = item.dataset.videoEmbed;
                video.controls = true;
                video.autoplay = true;
                lbStage.appendChild(video);
            }
            lbDownload.style.display = 'none';
        } else {
            lbImg.src = item.dataset.src;
            lbImg.alt = item.dataset.title || '';
            lbDownload.style.display = '';
            lbDownload.href = item.dataset.src;
            const filename = item.dataset.src.split('/').pop();
            lbDownload.setAttribute('download', filename);
        }

        lbTitle.style.display = 'none';
        lbDesc.textContent = item.dataset.desc || '';
        lbDesc.style.display = item.dataset.desc ? 'block' : 'none';
        lbDate.innerHTML = item.dataset.date ? '<i class="far fa-calendar"></i> ' + item.dataset.date : '';
        lbLocation.innerHTML = item.dataset.location ? '<i class="fas fa-location-dot"></i> ' + item.dataset.location : '';
        lbPhotographer.innerHTML = item.dataset.photographer ? '<i class="fas fa-camera"></i> ' + item.dataset.photographer : '';
        lbCurrent.textContent = index + 1;
    }

    function openLightbox(index) {
        renderSlide(index);
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.remove('is-open');
        document.body.style.overflow = '';
        clearVideoEmbed();
        if (document.fullscreenElement) {
            document.exitFullscreen().catch(() => {});
        }
    }

    function showNext() { renderSlide((currentIndex + 1) % items.length); }
    function showPrev() { renderSlide((currentIndex - 1 + items.length) % items.length); }

    items.forEach((item, index) => {
        item.addEventListener('click', () => openLightbox(index));
    });

    btnClose.addEventListener('click', closeLightbox);
    btnNext.addEventListener('click', showNext);
    btnPrev.addEventListener('click', showPrev);

    btnZoom.addEventListener('click', () => {
        isZoomed = !isZoomed;
        lbImg.classList.toggle('is-zoomed', isZoomed);
    });
    lbImg.addEventListener('click', () => {
        isZoomed = !isZoomed;
        lbImg.classList.toggle('is-zoomed', isZoomed);
    });

    btnFullscreen.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            lightbox.requestFullscreen().catch(() => {});
        } else {
            document.exitFullscreen().catch(() => {});
        }
    });

    btnShare.addEventListener('click', async () => {
        const item = items[currentIndex];
        const shareUrl = window.location.href;
        const shareTitle = item.dataset.title || document.title;
        if (navigator.share) {
            try {
                await navigator.share({ title: shareTitle, url: shareUrl });
            } catch (e) { /* utilisateur a annulé */ }
        } else {
            try {
                await navigator.clipboard.writeText(shareUrl);
                btnShare.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => { btnShare.innerHTML = '<i class="fas fa-share-nodes"></i>'; }, 1500);
            } catch (e) { /* clipboard indisponible */ }
        }
    });

    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox();
    });

    document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('is-open')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') showNext();
        if (e.key === 'ArrowLeft') showPrev();
    });

    // --- Copier le lien de l'album ---
    const copyBtn = document.getElementById('btn-copy-link');
    if (copyBtn) {
        copyBtn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(window.location.href);
                copyBtn.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => { copyBtn.innerHTML = '<i class="fas fa-link"></i>'; }, 1500);
            } catch (e) { /* clipboard indisponible */ }
        });
    }
});
