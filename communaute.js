document.addEventListener('DOMContentLoaded', () => {
    const feed = document.querySelector('.communaute-feed');
    if (!feed) return;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    // --- Compression des commentaires : au-delà de 3 fils de premier niveau, seuls les 3 plus
    // récents restent visibles, avec un bouton "Voir tous les commentaires (N)" / "Réduire". ---
    const COMMENTS_VISIBLE_DEFAULT = 3;

    function collapseComments(commentsContainer) {
        const existingToggle = commentsContainer.querySelector(':scope > .communaute-comments-toggle');
        if (existingToggle) existingToggle.remove();
        commentsContainer.querySelectorAll('.communaute-comment.is-collapsed').forEach(c => c.classList.remove('is-collapsed'));

        const topLevel = Array.from(commentsContainer.querySelectorAll(':scope > .communaute-comment:not(.communaute-comment-reply)'));
        if (topLevel.length <= COMMENTS_VISIBLE_DEFAULT) return;

        const hiddenTop = topLevel.slice(0, topLevel.length - COMMENTS_VISIBLE_DEFAULT);
        const hiddenEls = [];
        hiddenTop.forEach((c) => {
            hiddenEls.push(c);
            commentsContainer.querySelectorAll(`[data-parent-id="${c.dataset.commentId}"]`).forEach((r) => hiddenEls.push(r));
        });
        hiddenEls.forEach((el) => el.classList.add('is-collapsed'));

        const labelShow = (commentsContainer.dataset.labelVoir || 'Voir tous les commentaires') + ` (${topLevel.length})`;
        const labelHide = commentsContainer.dataset.labelReduire || 'Réduire les commentaires';
        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'communaute-comments-toggle';
        toggle.textContent = labelShow;
        commentsContainer.insertBefore(toggle, commentsContainer.firstElementChild);

        toggle.addEventListener('click', () => {
            const isCurrentlyCollapsed = hiddenEls[0].classList.contains('is-collapsed');
            hiddenEls.forEach((el) => el.classList.toggle('is-collapsed', !isCurrentlyCollapsed));
            toggle.textContent = isCurrentlyCollapsed ? labelHide : labelShow;
        });
    }

    feed.querySelectorAll('.communaute-comments').forEach(collapseComments);

    // --- Ouvrir une image de publication dans la lightbox globale du site (footer.php) ---
    feed.addEventListener('click', (e) => {
        const img = e.target.closest('.communaute-gallery-img');
        if (!img || typeof window.openLightbox !== 'function') return;
        let gallery = [];
        try { gallery = JSON.parse(img.dataset.gallery || '[]'); } catch (err) {}
        window.openLightbox(img.dataset.full, gallery);
    });

    // --- Visionneuse dédiée aux publications à plusieurs fichiers (>2), mêlant images/vidéos/PDF.
    // Volontairement distincte de la lightbox globale du site (footer.php/script.js), qui ne gère
    // que des <img> et est partagée avec galerie.php/actualite_article.php. ---
    const mediaViewer = document.getElementById('communaute-media-viewer');
    if (mediaViewer) {
        const viewerContent = mediaViewer.querySelector('.communaute-media-viewer-content');
        const viewerCounter = mediaViewer.querySelector('.communaute-media-viewer-counter');
        let viewerItems = [];
        let viewerIndex = 0;

        function renderViewerItem() {
            const item = viewerItems[viewerIndex];
            if (!item) return;
            if (item.type === 'image') {
                viewerContent.innerHTML = `<img src="${escapeHtml(item.path)}" alt="">`;
            } else if (item.type === 'video') {
                viewerContent.innerHTML = `<video src="${escapeHtml(item.path)}" controls autoplay></video>`;
            } else {
                viewerContent.innerHTML = `<div class="communaute-media-viewer-doc"><i class="fas fa-file-pdf"></i><a href="${escapeHtml(item.path)}" download target="_blank" rel="noopener">${escapeHtml(item.path.split('/').pop())}</a></div>`;
            }
            viewerCounter.textContent = (viewerIndex + 1) + ' / ' + viewerItems.length;
        }

        function openViewer(items, startIndex) {
            viewerItems = items;
            viewerIndex = startIndex || 0;
            renderViewerItem();
            mediaViewer.classList.add('is-open');
            mediaViewer.setAttribute('aria-hidden', 'false');
        }

        function closeViewer() {
            mediaViewer.classList.remove('is-open');
            mediaViewer.setAttribute('aria-hidden', 'true');
            viewerContent.innerHTML = '';
        }

        feed.addEventListener('click', (e) => {
            const stack = e.target.closest('.communaute-media-stack');
            if (!stack) return;
            let items = [];
            try { items = JSON.parse(stack.dataset.media || '[]'); } catch (err) {}
            if (items.length) openViewer(items, 0);
        });

        mediaViewer.querySelector('.communaute-media-viewer-close').addEventListener('click', closeViewer);
        mediaViewer.querySelector('.communaute-media-viewer-prev').addEventListener('click', () => {
            viewerIndex = (viewerIndex - 1 + viewerItems.length) % viewerItems.length;
            renderViewerItem();
        });
        mediaViewer.querySelector('.communaute-media-viewer-next').addEventListener('click', () => {
            viewerIndex = (viewerIndex + 1) % viewerItems.length;
            renderViewerItem();
        });
        mediaViewer.addEventListener('click', (e) => { if (e.target === mediaViewer) closeViewer(); });
        document.addEventListener('keydown', (e) => {
            if (!mediaViewer.classList.contains('is-open')) return;
            if (e.key === 'Escape') closeViewer();
            if (e.key === 'ArrowLeft') mediaViewer.querySelector('.communaute-media-viewer-prev').click();
            if (e.key === 'ArrowRight') mediaViewer.querySelector('.communaute-media-viewer-next').click();
        });
    }

    // --- Réactions (j'aime / j'adore) ---
    feed.addEventListener('click', async (e) => {
        const btn = e.target.closest('.communaute-reaction-btn:not(.communaute-share-toggle)');
        if (!btn) return;
        const postId = btn.dataset.postId;
        const type = btn.dataset.reaction;
        if (!postId || !type) return;

        try {
            const res = await fetch('communaute_react.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${encodeURIComponent(postId)}&type=${encodeURIComponent(type)}`,
            });
            const data = await res.json();
            if (data.error) return;
            const card = btn.closest('.communaute-post-card');

            // Le bouton principal (façon Facebook) reflète toujours la réaction active : icône,
            // libellé et data-reaction changent pour qu'un futur clic dessus la retire (toggle),
            // que le choix vienne du bouton principal lui-même ou du petit sélecteur au survol.
            const mainBtn = card.querySelector('.communaute-reaction-main');
            if (mainBtn) {
                const isLove = data.my_reaction === 'love';
                mainBtn.classList.toggle('is-active', !!data.my_reaction);
                mainBtn.classList.toggle('communaute-reaction-love', isLove);
                mainBtn.dataset.reaction = isLove ? 'love' : 'like';
                mainBtn.innerHTML = isLove
                    ? '<i class="fas fa-heart"></i> ' + feed.dataset.i18nJadore
                    : '<i class="fas fa-thumbs-up"></i> ' + feed.dataset.i18nJaime;
            }
            card.querySelectorAll('.communaute-reaction-pick').forEach(b => {
                b.classList.toggle('is-active', b.dataset.reaction === data.my_reaction);
            });

            const summary = card.querySelector('.communaute-reactions-summary');
            const likeSpan = summary.querySelector('[data-summary="like"]');
            const loveSpan = summary.querySelector('[data-summary="love"]');
            likeSpan.innerHTML = `<i class="fas fa-thumbs-up"></i> ${data.likes}`;
            likeSpan.style.display = data.likes > 0 ? '' : 'none';
            loveSpan.innerHTML = `<i class="fas fa-heart"></i> ${data.loves}`;
            loveSpan.style.display = data.loves > 0 ? '' : 'none';
        } catch (err) { console.error(err); }
    });

    // --- Bouton "Commentaire" (remplace l'ancien bouton "J'adore" dans la barre) : amène le
    // focus directement sur le champ de saisie du commentaire, comme un raccourci. ---
    feed.addEventListener('click', (e) => {
        const cbtn = e.target.closest('.communaute-comment-focus-btn');
        if (!cbtn) return;
        const card = cbtn.closest('.communaute-post-card');
        const input = card?.querySelector('.communaute-comment-form input');
        if (!input) return;
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        input.focus();
    });

    // --- Menu de partage (ouverture/fermeture) ---
    feed.addEventListener('click', (e) => {
        const toggle = e.target.closest('.communaute-share-toggle');
        document.querySelectorAll('.communaute-share-dropdown.is-open').forEach(d => {
            if (!toggle || d !== toggle.closest('.communaute-share-dropdown')) d.classList.remove('is-open');
        });
        if (toggle) {
            toggle.closest('.communaute-share-dropdown').classList.toggle('is-open');
        }
    });
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.communaute-share-dropdown')) {
            document.querySelectorAll('.communaute-share-dropdown.is-open').forEach(d => d.classList.remove('is-open'));
        }
    });

    // --- Copier le lien ---
    feed.addEventListener('click', async (e) => {
        const btn = e.target.closest('.communaute-copy-link-btn');
        if (!btn) return;
        try {
            await navigator.clipboard.writeText(btn.dataset.url);
            const icon = btn.querySelector('i');
            icon.className = 'fas fa-check';
            setTimeout(() => { icon.className = 'fas fa-link'; }, 1500);
        } catch (err) {}
    });

    // --- Ajout de commentaire (premier niveau) ---
    feed.addEventListener('submit', async (e) => {
        const form = e.target.closest('.communaute-comment-form');
        if (!form) return;
        e.preventDefault();
        const postId = form.dataset.postId;
        const input = form.querySelector('input[name="contenu"]');
        const contenu = input.value.trim();
        if (!contenu) return;
        await submitComment(postId, contenu, null, form);
        input.value = '';
    });

    // --- Réponse à un commentaire (formulaire injecté dynamiquement) ---
    feed.addEventListener('click', (e) => {
        const btn = e.target.closest('.communaute-comment-reply-btn');
        if (!btn) return;
        const comment = btn.closest('.communaute-comment');
        let replyForm = comment.querySelector(':scope > .communaute-inline-reply-form');
        if (replyForm) { replyForm.remove(); return; }
        replyForm = document.createElement('form');
        replyForm.className = 'communaute-inline-reply-form';
        replyForm.innerHTML = '<input type="text" placeholder="Écrire une réponse..." required maxlength="1000"><button type="submit"><i class="fas fa-paper-plane"></i></button>';
        comment.querySelector('.communaute-comment-body').appendChild(replyForm);
        replyForm.querySelector('input').focus();
        replyForm.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            const input = replyForm.querySelector('input');
            const contenu = input.value.trim();
            if (!contenu) return;
            const postId = comment.closest('.communaute-post-card').dataset.postId;
            await submitComment(postId, contenu, btn.dataset.commentId, comment);
            replyForm.remove();
        });
    });

    async function submitComment(postId, contenu, parentId, anchorEl) {
        try {
            const body = new URLSearchParams({ post_id: postId, contenu });
            if (parentId) body.set('parent_id', parentId);
            const res = await fetch('communaute_comment_add.php', { method: 'POST', body });
            const data = await res.json();
            if (data.error || !data.html) return;
            const wrapper = document.createElement('div');
            wrapper.innerHTML = data.html.trim();
            const newNode = wrapper.firstElementChild;
            if (data.is_reply) {
                // Insère la réponse juste après le dernier élément (commentaire ou réponse déjà
                // présente) appartenant à ce même commentaire de premier niveau.
                const parentComment = document.getElementById('communaute-comment-' + data.parent_id);
                const siblings = document.querySelectorAll(`[data-parent-id="${data.parent_id}"]`);
                const last = siblings.length ? siblings[siblings.length - 1] : parentComment;
                last.insertAdjacentElement('afterend', newNode);
            } else {
                const commentsContainer = document.getElementById('communaute-comments-' + postId);
                commentsContainer.appendChild(newNode);
            }
            const card = document.querySelector(`.communaute-post-card[data-post-id="${postId}"]`);
            const countLabel = card.querySelector('.communaute-comment-count-label');
            countLabel.textContent = (parseInt(countLabel.textContent, 10) + 1) + ' ' + countLabel.dataset.suffix;

            // On voit toujours le résultat de sa propre action : on annule la compression pour ce
            // fil de commentaires plutôt que de la recalculer (le nouveau commentaire pourrait
            // sinon se retrouver masqué si le fil était déjà réduit).
            const commentsContainer = document.getElementById('communaute-comments-' + postId);
            const toggle = commentsContainer.querySelector(':scope > .communaute-comments-toggle');
            if (toggle) toggle.remove();
            commentsContainer.querySelectorAll('.communaute-comment.is-collapsed').forEach(c => c.classList.remove('is-collapsed'));
        } catch (err) { console.error(err); }
    }

    // --- Édition d'un commentaire (bascule vers un textarea inline) ---
    feed.addEventListener('click', (e) => {
        const btn = e.target.closest('.communaute-comment-edit-btn');
        if (!btn) return;
        const comment = btn.closest('.communaute-comment');
        const bubble = comment.querySelector('.communaute-comment-bubble');
        const textEl = bubble.querySelector('.communaute-comment-text');
        if (bubble.querySelector('.communaute-comment-edit-form')) return;

        const currentText = textEl.textContent;
        const editForm = document.createElement('div');
        editForm.className = 'communaute-comment-edit-form';
        editForm.innerHTML = `
            <textarea maxlength="1000">${escapeHtml(currentText)}</textarea>
            <div class="communaute-comment-edit-actions">
                <button type="button" class="btn-outline communaute-edit-cancel">Annuler</button>
                <button type="button" class="btn-submit communaute-edit-save">Enregistrer</button>
            </div>
        `;
        textEl.style.display = 'none';
        bubble.appendChild(editForm);
        editForm.querySelector('textarea').focus();

        editForm.querySelector('.communaute-edit-cancel').addEventListener('click', () => {
            editForm.remove();
            textEl.style.display = '';
        });
        editForm.querySelector('.communaute-edit-save').addEventListener('click', async () => {
            const newText = editForm.querySelector('textarea').value.trim();
            if (!newText) return;
            const commentId = comment.dataset.commentId;
            try {
                const res = await fetch('communaute_comment_edit.php', {
                    method: 'POST',
                    body: new URLSearchParams({ comment_id: commentId, contenu: newText }),
                });
                const data = await res.json();
                if (data.error) return;
                textEl.innerHTML = data.contenu_html;
                textEl.style.display = '';
                editForm.remove();
                let dateEl = comment.querySelector('.communaute-comment-date');
                if (!dateEl.querySelector('.communaute-comment-edited')) {
                    dateEl.insertAdjacentHTML('beforeend', ' <span class="communaute-comment-edited">(modifié)</span>');
                }
            } catch (err) { console.error(err); }
        });
    });

    // --- Suppression d'un commentaire (avec confirmation SweetAlert2) ---
    feed.addEventListener('click', async (e) => {
        const btn = e.target.closest('.communaute-comment-delete-btn');
        if (!btn) return;
        const commentId = btn.dataset.commentId;
        const comment = btn.closest('.communaute-comment');
        const msg = btn.dataset.confirmMsg || 'Supprimer ce commentaire ?';
        const confirmed = window.iSSTMConfirm ? await window.iSSTMConfirm(msg) : confirm(msg);
        if (!confirmed) return;
        try {
            const res = await fetch('communaute_comment_delete.php', {
                method: 'POST',
                body: new URLSearchParams({ comment_id: commentId }),
            });
            const data = await res.json();
            if (data.error) return;
            const card = comment.closest('.communaute-post-card');
            // Retire aussi les réponses éventuelles de ce commentaire (supprimées en cascade côté serveur).
            let removed = 1;
            card.querySelectorAll(`[data-parent-id="${commentId}"]`).forEach(r => { r.remove(); removed++; });
            comment.remove();
            const countLabel = card.querySelector('.communaute-comment-count-label');
            const newCount = Math.max(0, parseInt(countLabel.textContent, 10) - removed);
            countLabel.textContent = newCount + ' ' + countLabel.dataset.suffix;
        } catch (err) { console.error(err); }
    });
});
