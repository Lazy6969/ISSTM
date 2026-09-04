document.addEventListener('DOMContentLoaded', () => {
    const shell = document.getElementById('groupe-perso-shell');
    if (!shell) return;

    const groupeId = parseInt(shell.dataset.groupeId, 10);
    const selfId = parseInt(shell.dataset.selfId, 10);
    let lastId = parseInt(shell.dataset.lastId, 10) || 0;

    const messagesEl = document.getElementById('messagerie-messages');
    const form = document.getElementById('messagerie-compose-form');
    const textInput = document.getElementById('messagerie-text-input');
    const fileInput = document.getElementById('messagerie-file-input');
    const filePreview = document.getElementById('messagerie-file-preview');
    let pendingFiles = [];

    const LABELS = window.GROUPE_PERSO_LABELS || {};

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

    async function deleteMessage(msgEl, scope) {
        const messageId = msgEl.dataset.id;
        try {
            const fd = new FormData();
            fd.append('message_id', messageId);
            fd.append('scope', scope);
            const res = await fetch('groupe_perso_delete_message.php', { method: 'POST', body: fd });
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

    // --- Fenêtre centrée de confirmation ---
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

    function bindMessageOptions(msgEl) {
        const btn = msgEl.querySelector('.messagerie-msg-options-btn');
        if (!btn) return;
        btn.addEventListener('click', () => {
            const canDeleteEveryone = btn.dataset.canDeleteEveryone === '1';
            openConfirmModal({
                title: LABELS.titre_suppr_message || 'Supprimer ce message ?',
                desc: LABELS.desc_suppr_message || 'Choisissez comment vous voulez le supprimer.',
                meLabel: LABELS.supprimer_pour_moi || 'Supprimer pour moi',
                everyoneLabel: canDeleteEveryone ? (LABELS.supprimer_pour_tous || 'Supprimer pour tout le monde') : null,
                onMe: () => deleteMessage(msgEl, 'me'),
                onEveryone: () => deleteMessage(msgEl, 'everyone'),
            });
        });
    }

    function renderMessage(msg) {
        const isOwn = parseInt(msg.sender_id, 10) === selfId;
        const canDeleteEveryone = isOwn || window.GROUPE_PERSO_IS_OWNER === true;
        const avatar = msg.sender_avatar ? escapeHtml(msg.sender_avatar) : 'images/teachers/default-avatar.svg';
        const attachmentsHtml = (msg.attachments || []).map(renderAttachment).join('');
        const textHtml = msg.content ? `<p class="messagerie-msg-text">${linkify(escapeHtml(msg.content)).replace(/\n/g, '<br>')}</p>` : '';

        const wrap = document.createElement('div');
        wrap.className = 'messagerie-msg ' + (isOwn ? 'is-own' : 'is-other');
        wrap.dataset.id = msg.id;
        wrap.innerHTML = `
            <img src="${avatar}" alt="" class="messagerie-msg-avatar" onerror="this.src='images/teachers/default-avatar.svg'">
            <div class="messagerie-msg-body">
                <span class="messagerie-msg-author">${escapeHtml(msg.sender_nom)}</span>
                <div class="messagerie-msg-bubble-row">
                    <div class="messagerie-msg-bubble">${textHtml}${attachmentsHtml}</div>
                    <button type="button" class="messagerie-msg-options-btn" data-is-own="${isOwn ? '1' : '0'}" data-can-delete-everyone="${canDeleteEveryone ? '1' : '0'}" title="${LABELS.options || 'Options'}"><i class="fas fa-ellipsis-vertical"></i></button>
                </div>
                <div class="messagerie-msg-meta">
                    <span class="messagerie-msg-time">${formatDateTime(msg.created_at)}</span>
                </div>
            </div>`;
        bindMessageOptions(wrap);
        return wrap;
    }

    messagesEl.querySelectorAll('.messagerie-msg').forEach(bindMessageOptions);

    function isScrolledNearBottom() {
        return messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight < 150;
    }
    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }
    scrollToBottom();

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
        fd.append('groupe_id', groupeId);
        fd.append('content', text);
        pendingFiles.forEach(f => fd.append('files[]', f));

        const sendBtn = form.querySelector('.messagerie-send-btn');
        sendBtn.disabled = true;

        try {
            const res = await fetch('groupe_perso_send.php', { method: 'POST', body: fd });
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

    // --- Sondage périodique : nouveaux messages + suppressions ---
    async function poll() {
        try {
            const res = await fetch(`groupe_perso_poll.php?groupe_id=${groupeId}&since_id=${lastId}`);
            const data = await res.json();

            if (data.messages && data.messages.length > 0) {
                const wasNearBottom = isScrolledNearBottom();
                data.messages.forEach(msg => {
                    messagesEl.appendChild(renderMessage(msg));
                    lastId = Math.max(lastId, parseInt(msg.id, 10));
                });
                if (wasNearBottom) scrollToBottom();
            }

            if (Array.isArray(data.deleted_ids)) {
                data.deleted_ids.forEach(id => {
                    const el = messagesEl.querySelector(`.messagerie-msg[data-id="${id}"]`);
                    if (el) el.remove();
                });
            }
        } catch (err) { /* sondage silencieux */ }
    }

    poll();
    setInterval(poll, 6000);

    // --- Panneau Membres : liste + bannir/rétablir (créateur uniquement) ---
    const membresPanel = document.getElementById('groupe-membres-panel');
    const overlay = document.getElementById('messagerie-panel-overlay');
    const membresList = document.getElementById('groupe-membres-list');

    function openPanel(panel) {
        overlay.classList.add('is-open');
        panel.classList.add('is-open');
    }
    function closeAllPanels() {
        overlay.classList.remove('is-open');
        membresPanel.classList.remove('is-open');
    }
    overlay.addEventListener('click', closeAllPanels);

    async function loadMembers() {
        membresList.innerHTML = `<p class="messagerie-panel-loading">${LABELS.chargement || 'Chargement...'}</p>`;
        try {
            const res = await fetch(`groupe_perso_members.php?groupe_id=${groupeId}`);
            const data = await res.json();
            if (!data.members || data.members.length === 0) {
                membresList.innerHTML = `<p class="messagerie-panel-empty">${LABELS.aucun_membre || 'Aucun membre.'}</p>`;
                return;
            }
            membresList.innerHTML = '';
            data.members.forEach(m => {
                const row = document.createElement('div');
                row.className = 'groupe-membre-row' + (m.is_banned ? ' is-banned' : '');
                const avatar = m.avatar_path ? escapeHtml(m.avatar_path) : 'images/teachers/default-avatar.svg';
                const ownerBadge = m.is_createur ? '<span class="groupe-badge-enseignant">Créateur</span>' : '';
                const banBadge = m.is_banned ? `<span class="groupe-banned-badge">${LABELS.banni_badge || 'Banni'}</span>` : '';
                let actionBtn = '';
                if (data.can_moderate && !m.is_createur && !m.is_self) {
                    actionBtn = m.is_banned
                        ? `<button type="button" class="btn-outline groupe-unban-btn" data-user-id="${m.id}">${LABELS.retablir || 'Rétablir'}</button>`
                        : `<button type="button" class="btn-delete groupe-ban-btn" data-user-id="${m.id}">${LABELS.bannir || 'Bannir'}</button>`;
                }
                row.innerHTML = `
                    <img src="${avatar}" alt="" class="groupe-membre-avatar" onerror="this.src='images/teachers/default-avatar.svg'">
                    <div class="groupe-membre-info">
                        <span class="groupe-membre-nom">${escapeHtml(m.nom)} ${ownerBadge} ${banBadge}</span>
                    </div>
                    <div class="groupe-membre-actions">${actionBtn}</div>`;
                membresList.appendChild(row);
            });

            membresList.querySelectorAll('.groupe-ban-btn').forEach(btn => {
                btn.addEventListener('click', () => banMember(btn.dataset.userId, 'ban'));
            });
            membresList.querySelectorAll('.groupe-unban-btn').forEach(btn => {
                btn.addEventListener('click', () => banMember(btn.dataset.userId, 'unban'));
            });
        } catch (err) {
            membresList.innerHTML = `<p class="messagerie-panel-empty">${LABELS.erreur || 'Erreur de chargement.'}</p>`;
        }
    }

    async function banMember(userId, action) {
        const confirmMsg = action === 'ban' ? LABELS.confirm_bannir : LABELS.confirm_retablir;
        if (confirmMsg && !(await window.iSSTMConfirm(confirmMsg))) return;
        try {
            const fd = new FormData();
            fd.append('groupe_id', groupeId);
            fd.append('user_id', userId);
            fd.append('action', action);
            const res = await fetch('groupe_perso_ban_member.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                loadMembers();
            } else {
                alert(LABELS.erreur || 'Erreur.');
            }
        } catch (err) {
            alert(LABELS.erreur || 'Erreur.');
        }
    }

    const btnOpenMembres = document.getElementById('btn-open-membres');
    if (btnOpenMembres) {
        btnOpenMembres.addEventListener('click', () => {
            openPanel(membresPanel);
            loadMembers();
        });
    }
    const btnCloseMembres = document.getElementById('btn-close-membres');
    if (btnCloseMembres) btnCloseMembres.addEventListener('click', closeAllPanels);
});
