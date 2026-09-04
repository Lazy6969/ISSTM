document.addEventListener('DOMContentLoaded', () => {
    const shell = document.getElementById('messagerie-shell');
    if (!shell) return;

    const selfId = parseInt(shell.dataset.selfId, 10);
    let otherIds = [];
    try { otherIds = JSON.parse(shell.dataset.otherIds || '[]'); } catch (e) { otherIds = []; }
    let lastId = parseInt(shell.dataset.lastId, 10) || 0;
    if (otherIds.length === 0) return; // pas d'autre participant configuré (voir messagerie_pas_interlocuteur)

    const messagesEl = document.getElementById('messagerie-messages');
    const form = document.getElementById('messagerie-compose-form');
    const textInput = document.getElementById('messagerie-text-input');
    const fileInput = document.getElementById('messagerie-file-input');
    const filePreview = document.getElementById('messagerie-file-preview');
    const statusText = document.getElementById('messagerie-status-text');
    let pendingFiles = [];

    const LABELS = window.MESSAGERIE_LABELS || {};

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function linkify(escapedText) {
        return escapedText.replace(/(https?:\/\/[^\s<]+)/gi, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>');
    }

    function formatSize(bytes) {
        bytes = parseInt(bytes, 10) || 0;
        if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' Go';
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' Mo';
        if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' Ko';
        return bytes + ' o';
    }

    function fileIconClass(name) {
        const ext = (name.split('.').pop() || '').toLowerCase();
        if (ext === 'pdf') return 'fa-file-pdf';
        if (['doc', 'docx'].includes(ext)) return 'fa-file-word';
        if (['xls', 'xlsx', 'csv'].includes(ext)) return 'fa-file-excel';
        if (['ppt', 'pptx'].includes(ext)) return 'fa-file-powerpoint';
        if (['zip', 'rar', '7z'].includes(ext)) return 'fa-file-zipper';
        if (['mp3', 'wav', 'ogg', 'm4a'].includes(ext)) return 'fa-file-audio';
        return 'fa-file';
    }

    function formatDateTime(iso) {
        const d = new Date(iso.replace(' ', 'T'));
        if (isNaN(d.getTime())) return iso;
        const pad = n => String(n).padStart(2, '0');
        return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    function renderAttachment(att) {
        if (att.file_type === 'image') {
            return `<div class="messagerie-att-media-wrap">
                        <a href="${escapeHtml(att.file_path)}" target="_blank" rel="noopener" class="messagerie-att-image-link">
                            <img src="${escapeHtml(att.file_path)}" alt="${escapeHtml(att.original_name)}" class="messagerie-att-image"></a>
                        <a href="${escapeHtml(att.file_path)}" download="${escapeHtml(att.original_name)}" class="messagerie-att-download-btn" title="${LABELS.telecharger || 'Télécharger'}"><i class="fas fa-download"></i></a>
                    </div>`;
        }
        if (att.file_type === 'video') {
            return `<div class="messagerie-att-media-wrap">
                        <video src="${escapeHtml(att.file_path)}" controls class="messagerie-att-video"></video>
                        <a href="${escapeHtml(att.file_path)}" download="${escapeHtml(att.original_name)}" class="messagerie-att-download-btn" title="${LABELS.telecharger || 'Télécharger'}"><i class="fas fa-download"></i></a>
                    </div>`;
        }
        return `<a href="${escapeHtml(att.file_path)}" download="${escapeHtml(att.original_name)}" class="messagerie-att-file">
                    <i class="fas ${fileIconClass(att.original_name)}"></i>
                    <span class="messagerie-att-file-info"><strong>${escapeHtml(att.original_name)}</strong><small>${formatSize(att.file_size)}</small></span>
                    <i class="fas fa-download messagerie-att-download-icon"></i>
                </a>`;
    }

    function renderMessage(msg) {
        const isOwn = parseInt(msg.sender_id, 10) === selfId;
        const avatar = msg.sender_avatar ? escapeHtml(msg.sender_avatar) : 'images/teachers/default-avatar.svg';
        const attachmentsHtml = (msg.attachments || []).map(renderAttachment).join('');
        const textHtml = msg.content ? `<p class="messagerie-msg-text">${linkify(escapeHtml(msg.content)).replace(/\n/g, '<br>')}</p>` : '';
        const statusHtml = isOwn
            ? `<span class="messagerie-msg-status" data-status="${msg.read_at ? 'read' : 'sent'}">
                   ${msg.read_at ? `<i class="fas fa-check-double"></i> ${LABELS.lu || 'Lu'}` : `<i class="fas fa-check"></i> ${LABELS.envoye || 'Envoyé'}`}
               </span>`
            : '';

        const wrap = document.createElement('div');
        wrap.className = 'messagerie-msg ' + (isOwn ? 'is-own' : 'is-other');
        wrap.dataset.id = msg.id;
        wrap.innerHTML = `
            <img src="${avatar}" alt="" class="messagerie-msg-avatar" onerror="this.src='images/teachers/default-avatar.svg'">
            <div class="messagerie-msg-body">
                <span class="messagerie-msg-author">${escapeHtml(msg.sender_nom)}</span>
                <div class="messagerie-msg-bubble-row">
                    <div class="messagerie-msg-bubble">${textHtml}${attachmentsHtml}</div>
                    <button type="button" class="messagerie-msg-options-btn" data-is-own="${isOwn ? '1' : '0'}" title="${LABELS.options || 'Options'}"><i class="fas fa-ellipsis-vertical"></i></button>
                </div>
                <div class="messagerie-msg-meta">
                    <span class="messagerie-msg-time">${formatDateTime(msg.created_at)}</span>
                    ${statusHtml}
                </div>
            </div>`;
        bindMessageOptions(wrap);
        return wrap;
    }

    // --- Fenêtre centrée de confirmation, réutilisée pour un message ou toute la discussion ---
    const confirmOverlay = document.getElementById('messagerie-confirm-overlay');
    const confirmTitle = document.getElementById('messagerie-confirm-title');
    const confirmDesc = document.getElementById('messagerie-confirm-desc');
    const confirmBtnMe = document.getElementById('messagerie-confirm-btn-me');
    const confirmBtnEveryone = document.getElementById('messagerie-confirm-btn-everyone');
    const confirmBtnCancel = document.getElementById('messagerie-confirm-btn-cancel');
    const confirmBtnClose = document.getElementById('messagerie-confirm-close');

    function closeConfirmModal() {
        confirmOverlay.classList.remove('is-open');
        confirmBtnMe.onclick = null;
        confirmBtnEveryone.onclick = null;
    }

    // options: { title, desc, meLabel, everyoneLabel (null pour la masquer), onMe, onEveryone }
    function openConfirmModal(options) {
        confirmTitle.textContent = options.title;
        confirmDesc.textContent = options.desc;
        confirmBtnMe.textContent = options.meLabel;
        confirmBtnMe.onclick = () => { closeConfirmModal(); options.onMe(); };
        if (options.everyoneLabel) {
            confirmBtnEveryone.textContent = options.everyoneLabel;
            confirmBtnEveryone.style.display = '';
            confirmBtnEveryone.onclick = () => { closeConfirmModal(); options.onEveryone(); };
        } else {
            confirmBtnEveryone.style.display = 'none';
        }
        confirmOverlay.classList.add('is-open');
    }

    confirmBtnCancel.addEventListener('click', closeConfirmModal);
    confirmBtnClose.addEventListener('click', closeConfirmModal);
    confirmOverlay.addEventListener('click', (e) => { if (e.target === confirmOverlay) closeConfirmModal(); });

    // --- Options par message : supprimer pour moi / pour tout le monde ---
    function bindMessageOptions(msgEl) {
        const btn = msgEl.querySelector('.messagerie-msg-options-btn');
        if (!btn) return;
        btn.addEventListener('click', () => {
            const isOwn = btn.dataset.isOwn === '1';
            openConfirmModal({
                title: LABELS.titre_suppr_message || 'Supprimer ce message ?',
                desc: LABELS.desc_suppr_message || 'Choisissez comment vous voulez le supprimer.',
                meLabel: LABELS.supprimer_pour_moi || 'Supprimer pour moi',
                everyoneLabel: isOwn ? (LABELS.supprimer_pour_tous || 'Supprimer pour tout le monde') : null,
                onMe: () => deleteMessage(msgEl, 'me'),
                onEveryone: () => deleteMessage(msgEl, 'everyone'),
            });
        });
    }

    async function deleteMessage(msgEl, scope) {
        const messageId = msgEl.dataset.id;
        try {
            const fd = new FormData();
            fd.append('message_id', messageId);
            fd.append('scope', scope);
            const res = await fetch('messagerie_delete_message.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                msgEl.remove();
            } else {
                alert(LABELS.erreur || 'Erreur.');
            }
        } catch (err) {
            alert(LABELS.erreur || 'Erreur.');
        }
    }

    // Les messages déjà rendus côté serveur au premier chargement n'ont pas encore leurs
    // gestionnaires d'options (uniquement posés par renderMessage() pour les nouveaux messages).
    messagesEl.querySelectorAll('.messagerie-msg').forEach(bindMessageOptions);

    function isScrolledNearBottom() {
        return messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight < 150;
    }

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    scrollToBottom();

    // --- Auto-redimensionnement de la zone de texte ---
    textInput.addEventListener('input', () => {
        textInput.style.height = 'auto';
        textInput.style.height = Math.min(textInput.scrollHeight, 140) + 'px';
    });
    textInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    // --- Pièces jointes : sélection + aperçu avant envoi ---
    document.getElementById('btn-attach-file').addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => {
        pendingFiles = Array.from(fileInput.files);
        renderFilePreview();
    });

    function renderFilePreview() {
        filePreview.innerHTML = '';
        if (pendingFiles.length === 0) { filePreview.classList.remove('has-files'); return; }
        filePreview.classList.add('has-files');
        pendingFiles.forEach((file, idx) => {
            const chip = document.createElement('div');
            chip.className = 'messagerie-file-chip';
            const isImg = file.type.startsWith('image/');
            chip.innerHTML = isImg
                ? `<img src="${URL.createObjectURL(file)}" alt="">`
                : `<i class="fas ${fileIconClass(file.name)}"></i>`;
            const name = document.createElement('span');
            name.textContent = file.name;
            chip.appendChild(name);
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.innerHTML = '<i class="fas fa-xmark"></i>';
            remove.addEventListener('click', () => {
                pendingFiles.splice(idx, 1);
                renderFilePreview();
            });
            chip.appendChild(remove);
            filePreview.appendChild(chip);
        });
    }

    // --- Emoji ---
    const EMOJIS = ['😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😋','😛','😜','🤪','🤨','🧐','🤓','😎','🥳','😏','😒','😞','😔','🙁','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡','🤯','😳','🥵','🥶','😱','😨','😰','🤗','🤔','🤭','🤫','🙄','😴','🤤','🤒','🤕','🤑','🤠','👍','👎','👏','🙌','🤝','🙏','💪','✌️','🤞','👌','👋','❤️','🧡','💛','💚','💙','💜','🖤','🤍','💕','💖','💯','🔥','⭐','✅','❌','⚠️','🎉','📎','📁','📄','🎥','📷','🔗','💬','⏰'];
    const emojiPanel = document.getElementById('messagerie-emoji-panel');
    EMOJIS.forEach(emoji => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = emoji;
        btn.addEventListener('click', () => {
            const start = textInput.selectionStart || textInput.value.length;
            const end = textInput.selectionEnd || textInput.value.length;
            textInput.value = textInput.value.slice(0, start) + emoji + textInput.value.slice(end);
            textInput.focus();
            textInput.selectionStart = textInput.selectionEnd = start + emoji.length;
        });
        emojiPanel.appendChild(btn);
    });
    document.getElementById('btn-toggle-emoji').addEventListener('click', (e) => {
        e.stopPropagation();
        emojiPanel.classList.toggle('is-open');
    });
    document.addEventListener('click', (e) => {
        if (!emojiPanel.contains(e.target) && e.target.id !== 'btn-toggle-emoji') {
            emojiPanel.classList.remove('is-open');
        }
    });

    // --- Envoi du message ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = textInput.value.trim();
        if (text === '' && pendingFiles.length === 0) return;

        const fd = new FormData();
        fd.append('content', text);
        pendingFiles.forEach(f => fd.append('files[]', f));

        const sendBtn = form.querySelector('.messagerie-send-btn');
        sendBtn.disabled = true;

        try {
            const res = await fetch('messagerie_send.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.message) {
                messagesEl.appendChild(renderMessage(data.message));
                lastId = data.message.id;
                scrollToBottom();
                textInput.value = '';
                textInput.style.height = 'auto';
                pendingFiles = [];
                fileInput.value = '';
                renderFilePreview();
            } else if (data.error) {
                alert(LABELS.erreur_envoi || "Erreur lors de l'envoi du message.");
            }
        } catch (err) {
            alert(LABELS.erreur_envoi || "Erreur lors de l'envoi du message.");
        } finally {
            sendBtn.disabled = false;
        }
    });

    // --- Sondage périodique : nouveaux messages + présence + accusés de lecture ---
    async function poll() {
        try {
            const res = await fetch(`messagerie_poll.php?since_id=${lastId}`);
            const data = await res.json();

            if (data.messages && data.messages.length > 0) {
                const wasNearBottom = isScrolledNearBottom();
                data.messages.forEach(msg => {
                    messagesEl.appendChild(renderMessage(msg));
                    lastId = Math.max(lastId, parseInt(msg.id, 10));
                });
                if (wasNearBottom) scrollToBottom();
            }

            // Messages supprimés "pour tout le monde" par un autre participant depuis le dernier
            // sondage : on les retire de la conversation déjà affichée, s'ils y étaient.
            if (Array.isArray(data.deleted_ids)) {
                data.deleted_ids.forEach(id => {
                    const el = messagesEl.querySelector(`.messagerie-msg[data-id="${id}"]`);
                    if (el) el.remove();
                });
            }

            if (data.sent_status) {
                Object.entries(data.sent_status).forEach(([id, readAt]) => {
                    const el = messagesEl.querySelector(`.messagerie-msg[data-id="${id}"] .messagerie-msg-status`);
                    if (el && readAt && el.dataset.status !== 'read') {
                        el.dataset.status = 'read';
                        el.innerHTML = `<i class="fas fa-check-double"></i> ${LABELS.lu || 'Lu'}`;
                    }
                });
            }

            if (Array.isArray(data.others_status)) {
                data.others_status.forEach(person => {
                    const dot = document.querySelector(`.messagerie-status-dot[data-user-id="${person.id}"]`);
                    if (dot) dot.classList.toggle('is-online', !!person.online);
                });
            }
            if (statusText) {
                statusText.textContent = data.other_online
                    ? (LABELS.actif_maintenant || 'Actif maintenant')
                    : (LABELS.hors_ligne || 'Hors ligne');
            }
        } catch (err) {
            // Sondage silencieux : une erreur réseau ponctuelle ne doit pas interrompre la conversation.
        }
    }

    poll();
    setInterval(poll, 6000);

    // --- "Options de la discussion" : effacer pour moi / supprimer pour tout le monde ---
    const conversationMenuBtn = document.getElementById('btn-open-conversation-menu');
    if (conversationMenuBtn) {
        conversationMenuBtn.addEventListener('click', () => {
            openConfirmModal({
                title: LABELS.titre_suppr_discussion || 'Supprimer la discussion ?',
                desc: LABELS.desc_suppr_discussion || 'Choisissez comment vous voulez supprimer tout l\'historique.',
                meLabel: LABELS.effacer_pour_moi || 'Effacer pour moi',
                everyoneLabel: LABELS.supprimer_pour_tous || 'Supprimer pour tout le monde',
                onMe: () => deleteConversation('me'),
                onEveryone: () => deleteConversation('everyone'),
            });
        });

        async function deleteConversation(scope) {
            // Second garde-fou natif uniquement pour l'action la plus destructrice de toutes
            // (efface tout, pour tout le monde, définitivement) : la fenêtre stylée sert de
            // premier choix explicite, celui-ci évite un double-clic accidentel qui l'exécuterait.
            if (scope === 'everyone' && !(await window.iSSTMConfirm(LABELS.confirm_suppr_discussion_tous || 'Supprimer définitivement toute la discussion pour tout le monde ? Cette action est irréversible.'))) {
                return;
            }
            try {
                const fd = new FormData();
                fd.append('scope', scope);
                const res = await fetch('messagerie_delete_conversation.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    messagesEl.innerHTML = '';
                } else {
                    alert(LABELS.erreur || 'Erreur.');
                }
            } catch (err) {
                alert(LABELS.erreur || 'Erreur.');
            }
        }
    }

    // --- Panneau Médias ---
    const mediaPanel = document.getElementById('messagerie-media-panel');
    const searchPanel = document.getElementById('messagerie-search-panel');
    const overlay = document.getElementById('messagerie-panel-overlay');
    const mediaGrid = document.getElementById('messagerie-media-grid');

    function openPanel(panel) {
        overlay.classList.add('is-open');
        panel.classList.add('is-open');
    }
    function closeAllPanels() {
        overlay.classList.remove('is-open');
        mediaPanel.classList.remove('is-open');
        searchPanel.classList.remove('is-open');
    }
    overlay.addEventListener('click', closeAllPanels);

    async function loadMedia(type) {
        mediaGrid.innerHTML = `<p class="messagerie-panel-loading">${LABELS.chargement || 'Chargement...'}</p>`;
        try {
            const res = await fetch(`messagerie_media.php?type=${type}`);
            const data = await res.json();
            if (data.items.length === 0) {
                mediaGrid.innerHTML = `<p class="messagerie-panel-empty">${LABELS.aucun_media || 'Aucun élément.'}</p>`;
                return;
            }
            mediaGrid.innerHTML = '';
            data.items.forEach(item => {
                const card = document.createElement('a');
                card.href = item.file_path;
                card.target = '_blank';
                card.rel = 'noopener';
                card.className = 'messagerie-media-item';
                if (item.file_type === 'image') {
                    card.innerHTML = `<img src="${escapeHtml(item.file_path)}" alt="">`;
                } else if (item.file_type === 'video') {
                    card.innerHTML = `<video src="${escapeHtml(item.file_path)}" muted></video><i class="fas fa-play messagerie-media-play"></i>`;
                } else {
                    card.innerHTML = `<i class="fas ${item.icon} messagerie-media-file-icon"></i><span>${escapeHtml(item.original_name)}</span>`;
                }
                const meta = document.createElement('div');
                meta.className = 'messagerie-media-item-meta';
                meta.innerHTML = `<small>${formatDateTime(item.created_at)} · ${escapeHtml(item.sender_nom)}</small>`;
                const cell = document.createElement('div');
                cell.className = 'messagerie-media-cell';
                cell.appendChild(card);
                cell.appendChild(meta);
                mediaGrid.appendChild(cell);
            });
        } catch (err) {
            mediaGrid.innerHTML = `<p class="messagerie-panel-empty">${LABELS.erreur || 'Erreur de chargement.'}</p>`;
        }
    }

    document.getElementById('btn-open-media').addEventListener('click', () => {
        openPanel(mediaPanel);
        loadMedia('all');
    });
    document.getElementById('btn-close-media').addEventListener('click', closeAllPanels);
    document.querySelectorAll('.messagerie-media-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.messagerie-media-tab').forEach(t => t.classList.remove('is-active'));
            tab.classList.add('is-active');
            loadMedia(tab.dataset.type);
        });
    });

    // --- Panneau Recherche ---
    const searchInput = document.getElementById('messagerie-search-input');
    const searchResults = document.getElementById('messagerie-search-results');
    let searchDebounce;

    document.getElementById('btn-open-search').addEventListener('click', () => {
        openPanel(searchPanel);
        searchInput.focus();
    });
    document.getElementById('btn-close-search').addEventListener('click', closeAllPanels);

    searchInput.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        const q = searchInput.value.trim();
        if (q === '') { searchResults.innerHTML = ''; return; }
        searchDebounce = setTimeout(async () => {
            searchResults.innerHTML = `<p class="messagerie-panel-loading">${LABELS.chargement || 'Chargement...'}</p>`;
            try {
                const res = await fetch(`messagerie_search.php?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                if (data.results.length === 0) {
                    searchResults.innerHTML = `<p class="messagerie-panel-empty">${LABELS.aucun_resultat || 'Aucun message trouvé.'}</p>`;
                    return;
                }
                searchResults.innerHTML = '';
                data.results.forEach(r => {
                    const item = document.createElement('div');
                    item.className = 'messagerie-search-result';
                    item.innerHTML = `<strong>${escapeHtml(r.sender_nom)}</strong><span>${formatDateTime(r.created_at)}</span><p>${escapeHtml(r.content || '')}</p>`;
                    item.addEventListener('click', () => {
                        closeAllPanels();
                        const target = messagesEl.querySelector(`.messagerie-msg[data-id="${r.id}"]`);
                        if (target) {
                            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            target.classList.add('is-highlighted');
                            setTimeout(() => target.classList.remove('is-highlighted'), 1800);
                        }
                    });
                    searchResults.appendChild(item);
                });
            } catch (err) {
                searchResults.innerHTML = `<p class="messagerie-panel-empty">${LABELS.erreur || 'Erreur de chargement.'}</p>`;
            }
        }, 300);
    });
});
