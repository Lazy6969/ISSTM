document.addEventListener('DOMContentLoaded', () => {
    const dashboard = document.getElementById('admin-stats-dashboard');
    if (!dashboard) return;

    const REFRESH_MS = 8000;
    const PERF_HISTORY_MAX = 24;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const palette = ['#003366', '#8e44ad', '#2ecc71', '#d4a017', '#2980b9', '#7f8c8d', '#e74c3c', '#16a085'];

    const moisLabels = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    const joursLabels = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    // --- Animation "compteur" façon trading, désactivée si prefers-reduced-motion ---
    function animateValue(el, from, to, duration = 900) {
        if (reduceMotion || from === to) { el.textContent = to.toLocaleString('fr-FR'); return; }
        const start = performance.now();
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(from + (to - from) * eased);
            el.textContent = current.toLocaleString('fr-FR');
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    function updateTiles(stats) {
        dashboard.querySelectorAll('.admin-stat-tile[data-stat]').forEach((tile) => {
            const key = tile.dataset.stat;
            if (!(key in stats)) return;
            const valueEl = tile.querySelector('.admin-stat-value');
            const previous = parseInt((valueEl.textContent || '0').replace(/\s/g, ''), 10) || 0;
            const next = Number(stats[key]) || 0;
            animateValue(valueEl, previous, next);
            if (previous !== next) {
                tile.classList.add('is-updating');
                setTimeout(() => tile.classList.remove('is-updating'), 700);
            }
        });
    }

    // --- Vitesse du serveur : courbe qui monte/descend au fil des mesures (temps de réponse réel
    // mesuré à chaque sondage, pas de charge système disponible sous Windows/XAMPP). ---
    const perfHistory = [];
    function updatePerfChart(ms) {
        const line = document.getElementById('admin-perf-chart-line');
        const fill = document.getElementById('admin-perf-chart-fill');
        const valueEl = document.getElementById('admin-perf-value');
        const labelEl = document.getElementById('admin-perf-label');
        if (!line) return;

        perfHistory.push(ms);
        if (perfHistory.length > PERF_HISTORY_MAX) perfHistory.shift();

        let color = '#2ecc71', label = 'Excellent';
        if (ms >= 300) { color = '#e74c3c'; label = 'Lent'; }
        else if (ms >= 100) { color = '#d4a017'; label = 'Bon'; }

        const w = 300, h = 100, pad = 8;
        const maxVal = Math.max(60, ...perfHistory) * 1.15;
        const n = perfHistory.length;
        const stepX = n > 1 ? (w / (n - 1)) : 0;
        const points = perfHistory.map((v, i) => {
            const x = n > 1 ? i * stepX : w;
            const y = pad + (h - pad * 2) * (1 - Math.min(1, v / maxVal));
            return `${x.toFixed(1)},${y.toFixed(1)}`;
        });
        line.setAttribute('points', points.join(' '));
        line.style.stroke = color;
        fill.setAttribute('points', points.length ? `0,${h} ${points.join(' ')} ${w},${h}` : '');
        fill.style.fill = color;

        valueEl.textContent = ms.toLocaleString('fr-FR') + ' ms';
        labelEl.textContent = label;
        labelEl.style.color = color;
    }

    // --- Donut + diagramme en bâton "Étudiants par filière" (section du bas) : le donut reste
    // la synthèse globale (réutilise les classes .etu-donut* déjà utilisées côté PHP), et le
    // diagramme réutilise tel quel .etu-barchart* (même composant que admin_etudiants.php),
    // une barre par filière dont la hauteur est relative à la filière la plus peuplée. ---
    function updateFiliereDonut(parFiliere) {
        const donut = document.getElementById('admin-filiere-donut');
        const totalEl = document.getElementById('admin-filiere-total');
        const chart = document.getElementById('admin-filiere-legend');
        if (!donut || !chart) return;
        const total = parFiliere.reduce((s, f) => s + Number(f.total), 0);
        totalEl.textContent = total.toLocaleString('fr-FR');
        if (total === 0) {
            donut.style.background = 'conic-gradient(var(--border-color) 0deg 360deg)';
            chart.innerHTML = `<p class="gallery-empty"><i class="fas fa-chart-column"></i> ${escapeHtml(chart.dataset.emptyLabel || 'Aucune donnée disponible.')}</p>`;
            return;
        }
        const maxTotal = Math.max(...parFiliere.map((f) => Number(f.total)));
        let cumulative = 0;
        const stops = [];
        chart.innerHTML = '';
        parFiliere.forEach((f, i) => {
            const color = palette[i % palette.length];
            const count = Number(f.total);
            const start = (cumulative / total) * 360;
            cumulative += count;
            const end = (cumulative / total) * 360;
            stops.push(`${color} ${start}deg ${end}deg`);
            const groupHeight = Math.max(Math.round((count / maxTotal) * 100), 4);
            const group = document.createElement('div');
            group.className = 'etu-barchart-group';
            group.innerHTML = `
                <div class="etu-barchart-stack" style="height:${groupHeight}%;">
                    <div class="etu-barchart-segment" style="height:100%; background:${color};" title="${escapeHtml(f.filiere)} : ${count}"></div>
                </div>
                <span class="etu-barchart-label">${escapeHtml(f.filiere)} (${count})</span>
            `;
            chart.appendChild(group);
        });
        donut.style.background = `conic-gradient(${stops.join(', ')})`;
    }

    // --- Mini-calendrier interactif : navigation mois précédent/suivant + clic sur un jour pour
    // voir le détail des événements de ce jour. ---
    const now = new Date();
    const calState = { year: now.getFullYear(), month: now.getMonth(), events: [], selectedDay: null };

    function renderMiniCalendar() {
        const cal = document.getElementById('admin-mini-calendar');
        if (!cal) return;
        const { year, month, events } = calState;
        const firstDay = new Date(year, month, 1);
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const startOffset = (firstDay.getDay() + 6) % 7; // lundi = 0
        const isCurrentMonth = now.getFullYear() === year && now.getMonth() === month;

        const eventsByDay = {};
        events.forEach(e => { (eventsByDay[e.jour] = eventsByDay[e.jour] || []).push(e); });

        let html = '<div class="admin-mini-calendar-nav">'
            + '<button type="button" class="admin-mini-calendar-nav-btn" id="admin-cal-prev" aria-label="Mois précédent"><i class="fas fa-chevron-left"></i></button>'
            + '<span class="admin-mini-calendar-title">' + moisLabels[month] + ' ' + year + '</span>'
            + '<button type="button" class="admin-mini-calendar-nav-btn" id="admin-cal-next" aria-label="Mois suivant"><i class="fas fa-chevron-right"></i></button>'
            + '</div>';
        html += '<div class="admin-mini-calendar-grid">';
        joursLabels.forEach(j => { html += `<span class="admin-mini-calendar-weekday">${j}</span>`; });
        for (let i = 0; i < startOffset; i++) html += '<span></span>';
        for (let d = 1; d <= daysInMonth; d++) {
            const isToday = isCurrentMonth && d === now.getDate();
            const hasEvent = !!eventsByDay[d];
            const isSelected = calState.selectedDay === d;
            html += `<button type="button" class="admin-mini-calendar-day${isToday ? ' is-today' : ''}${hasEvent ? ' has-event' : ''}${isSelected ? ' is-selected' : ''}" data-day="${d}">${d}</button>`;
        }
        html += '</div>';
        html += '<div class="admin-mini-calendar-events" id="admin-cal-events"></div>';
        cal.innerHTML = html;

        document.getElementById('admin-cal-prev').addEventListener('click', () => shiftMonth(-1));
        document.getElementById('admin-cal-next').addEventListener('click', () => shiftMonth(1));
        cal.querySelectorAll('.admin-mini-calendar-day').forEach(btn => {
            btn.addEventListener('click', () => {
                const day = parseInt(btn.dataset.day, 10);
                calState.selectedDay = calState.selectedDay === day ? null : day;
                renderMiniCalendar();
            });
        });
        renderDayEvents();
    }

    function renderDayEvents() {
        const evEl = document.getElementById('admin-cal-events');
        if (!evEl) return;
        if (!calState.selectedDay) { evEl.innerHTML = ''; return; }
        const dayEvents = calState.events.filter(e => e.jour === calState.selectedDay);
        if (!dayEvents.length) {
            evEl.innerHTML = `<p class="admin-mini-calendar-events-empty">${calState.selectedDay} ${moisLabels[calState.month]} — aucun événement.</p>`;
            return;
        }
        evEl.innerHTML = `<h5>${calState.selectedDay} ${moisLabels[calState.month]}</h5><ul>`
            + dayEvents.map(e => `<li>${escapeHtml(e.titre)}</li>`).join('')
            + '</ul>';
    }

    async function shiftMonth(delta) {
        let m = calState.month + delta;
        let y = calState.year;
        if (m < 0) { m = 11; y -= 1; } else if (m > 11) { m = 0; y += 1; }
        calState.year = y;
        calState.month = m;
        calState.selectedDay = null;
        try {
            const res = await fetch(`admin_calendar_events.php?year=${y}&month=${m + 1}`);
            const data = await res.json();
            calState.events = data.events || [];
        } catch (err) {
            calState.events = [];
        }
        renderMiniCalendar();
    }

    // Ne réinjecte les événements reçus par le sondage temps réel que si l'admin regarde toujours
    // le mois courant (sinon ça annulerait sa navigation vers un autre mois).
    function updateCalendarFromPoll(prochains) {
        if (calState.year !== now.getFullYear() || calState.month !== now.getMonth()) return;
        calState.events = (prochains || []).filter(e => e.mois === calState.month + 1 && e.annee === calState.year);
        renderMiniCalendar();
    }

    // --- Bloc-notes admin : sauvegarde automatique après une courte pause de saisie ---
    (function initNotes() {
        const textarea = document.getElementById('admin-notes-textarea');
        const statusEl = document.getElementById('admin-notes-status');
        if (!textarea || !statusEl) return;
        let saveTimeout = null;

        async function saveNote() {
            statusEl.textContent = 'Enregistrement...';
            statusEl.className = 'admin-notes-status is-pending';
            try {
                const fd = new FormData();
                fd.append('contenu', textarea.value);
                const res = await fetch('admin_notes_save.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    statusEl.textContent = 'Enregistré ✓';
                    statusEl.className = 'admin-notes-status is-saved';
                } else {
                    throw new Error('save failed');
                }
            } catch (err) {
                statusEl.textContent = "Erreur d'enregistrement";
                statusEl.className = 'admin-notes-status is-error';
            }
        }

        textarea.addEventListener('input', () => {
            statusEl.textContent = '';
            statusEl.className = 'admin-notes-status';
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(saveNote, 900);
        });
    })();

    // --- Réinitialisation des compteurs de vues (boutons sur les tuiles "Vues totales" /
    // "Vues aujourd'hui") : confirmation stylée (voir script.js), puis appel AJAX et
    // rafraîchissement immédiat des tuiles au lieu d'attendre le prochain sondage. ---
    (function initVuesReset() {
        dashboard.addEventListener('click', async (e) => {
            const btn = e.target.closest('.admin-stat-reset-btn');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();

            const type = btn.dataset.resetType;
            const msg = btn.dataset.confirmMsg || 'Confirmer cette action ?';
            const ok = typeof window.iSSTMConfirm === 'function' ? await window.iSSTMConfirm(msg) : window.confirm(msg);
            if (!ok) return;

            btn.disabled = true;
            try {
                const res = await fetch('admin_reset_vues.php', {
                    method: 'POST',
                    body: new URLSearchParams({ type }),
                });
                const data = await res.json();
                if (!data.success) throw new Error('reset failed');
                refresh();
            } catch (err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', text: 'Échec de la réinitialisation. Réessayez.' });
                }
            } finally {
                btn.disabled = false;
            }
        });
    })();

    async function refresh() {
        try {
            const res = await fetch('admin_dashboard_stats.php');
            if (!res.ok) return;
            const stats = await res.json();
            if (stats.error) return;
            updateTiles(stats);
            updatePerfChart(stats.temps_reponse_ms || 0);
            updateFiliereDonut(stats.par_filiere || []);
            updateCalendarFromPoll(stats.prochains_evenements || []);
        } catch (err) { /* silencieux : un rafraîchissement raté n'affiche pas d'erreur intrusive */ }
    }

    renderMiniCalendar();
    refresh();
    setInterval(refresh, REFRESH_MS);
});
