document.addEventListener('DOMContentLoaded', () => {
    const card = document.getElementById('profil-public-card');
    const actionsEl = document.getElementById('profil-public-actions');
    if (!card || !actionsEl) return;

    const userId = card.dataset.userId;
    const L = window.PROFIL_PUBLIC_LABELS || {};
    let statut = card.dataset.statut;

    function render() {
        if (statut === 'amis') {
            actionsEl.innerHTML = `
                <span class="profil-public-amis-badge"><i class="fas fa-check"></i> ${L.amis || 'Amis'}</span>
                <a href="messages_prives.php?with=${userId}" class="btn-add-item"><i class="fas fa-paper-plane"></i> ${L.envoyer_message || 'Envoyer un message'}</a>
                <button type="button" class="btn-outline" id="btn-retirer-ami">${L.retirer_ami || 'Retirer'}</button>`;
            document.getElementById('btn-retirer-ami').addEventListener('click', async () => {
                if (L.confirm_retirer && window.iSSTMConfirm && !(await window.iSSTMConfirm(L.confirm_retirer))) return;
                const data = await postAction('ami_supprimer.php');
                if (data && data.success) { statut = 'aucune'; render(); }
            });
        } else if (statut === 'en_attente_envoyee') {
            actionsEl.innerHTML = `<button type="button" class="btn-outline" id="btn-annuler-demande"><i class="fas fa-clock"></i> ${L.demande_envoyee || 'Demande envoyée'} — ${L.annuler || 'Annuler'}</button>`;
            document.getElementById('btn-annuler-demande').addEventListener('click', async () => {
                const data = await postAction('ami_supprimer.php');
                if (data && data.success) { statut = 'aucune'; render(); }
            });
        } else if (statut === 'en_attente_recue') {
            actionsEl.innerHTML = `
                <button type="button" class="btn-add-item" id="btn-accepter"><i class="fas fa-check"></i> ${L.accepter || 'Accepter'}</button>
                <button type="button" class="btn-outline" id="btn-refuser"><i class="fas fa-xmark"></i> ${L.refuser || 'Refuser'}</button>`;
            document.getElementById('btn-accepter').addEventListener('click', async () => {
                const data = await postAction('ami_repondre.php', { action: 'accepter' });
                if (data && data.success) { statut = data.statut; render(); }
            });
            document.getElementById('btn-refuser').addEventListener('click', async () => {
                const data = await postAction('ami_repondre.php', { action: 'refuser' });
                if (data && data.success) { statut = data.statut; render(); }
            });
        } else {
            actionsEl.innerHTML = `<button type="button" class="btn-add-item" id="btn-ajouter-ami"><i class="fas fa-user-plus"></i> ${L.ajouter || 'Ajouter en ami'}</button>`;
            document.getElementById('btn-ajouter-ami').addEventListener('click', async () => {
                const data = await postAction('ami_demande.php');
                if (data && data.success) { statut = data.statut; render(); }
            });
        }
    }

    async function postAction(url, extra = {}) {
        try {
            const params = new URLSearchParams({ user_id: userId, ...extra });
            const res = await fetch(url, { method: 'POST', body: params });
            const data = await res.json();
            if (data.error) { alert(L.erreur || 'Erreur.'); return null; }
            return data;
        } catch (err) {
            alert(L.erreur || 'Erreur.');
            return null;
        }
    }

    render();
});
