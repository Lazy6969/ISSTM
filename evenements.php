<?php
include_once 'language.php';
require_once 'db_connect.php';

$mois_noms = [
    'fr' => ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
    'en' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    'mg' => ['Janoary', 'Febroary', 'Martsa', 'Aprily', 'Mey', 'Jona', 'Jolay', 'Aogositra', 'Septambra', 'Oktobra', 'Novambra', 'Desambra'],
];
$jours_noms = [
    'fr' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
    'en' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    'mg' => ['Alts', 'Tal', 'Alr', 'Alk', 'Zom', 'Sab', 'Alah'],
];
$cur_mois_noms = $mois_noms[$lang] ?? $mois_noms['fr'];
$cur_jours_noms = $jours_noms[$lang] ?? $jours_noms['fr'];

$category_icons = [
    'general' => 'fa-calendar-day', 'examen' => 'fa-file-pen', 'ceremonie' => 'fa-graduation-cap',
    'atelier' => 'fa-chalkboard-user', 'vacances' => 'fa-umbrella-beach', 'inscription' => 'fa-user-plus',
];
$category_colors = [
    'general' => '#003366', 'examen' => '#c0392b', 'ceremonie' => '#8e44ad',
    'atelier' => '#2980b9', 'vacances' => '#2ecc71', 'inscription' => '#d4a017',
];
$category_label_keys = [
    'general' => 'admin_evenements_categorie_general', 'examen' => 'admin_evenements_categorie_examen',
    'ceremonie' => 'admin_evenements_categorie_ceremonie', 'atelier' => 'admin_evenements_categorie_atelier',
    'vacances' => 'admin_evenements_categorie_vacances', 'inscription' => 'admin_evenements_categorie_inscription',
];

// Fenêtre de rendu du calendrier : du mois précédent à 11 mois après le mois courant, tout
// rendu côté serveur d'un coup (cohérent avec le reste du site, majoritairement PHP) ; la
// navigation mois précédent/suivant ne fait ensuite que basculer l'affichage en JS, sans rechargement.
$today = new DateTime('today');
$window_start = (new DateTime($today->format('Y-m-01')))->modify('-1 month');
$window_end = (new DateTime($today->format('Y-m-t')))->modify('+11 months');

$events_by_day = [];
$evt_table_exists = $mysqli->query("SHOW TABLES LIKE 'evenements'")->num_rows > 0;
if ($evt_table_exists) {
    $stmt = $mysqli->prepare("SELECT * FROM evenements WHERE date_debut BETWEEN ? AND ? ORDER BY date_debut ASC");
    $ws = $window_start->format('Y-m-d 00:00:00');
    $we = $window_end->format('Y-m-d 23:59:59');
    $stmt->bind_param('ss', $ws, $we);
    $stmt->execute();
    $window_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($window_events as $e) {
        $day_key = substr($e['date_debut'], 0, 10);
        $events_by_day[$day_key][] = $e;
    }

    $upcoming_events = $mysqli->query("SELECT * FROM evenements WHERE date_debut >= NOW() ORDER BY date_debut ASC LIMIT 30")->fetch_all(MYSQLI_ASSOC);
} else {
    $upcoming_events = [];
}

function evt_field($row, $base, $lang) {
    $key = $base . '_' . $lang;
    return !empty($row[$key]) ? $row[$key] : ($row[$base . '_fr'] ?? '');
}

$page_title = t('evenements_page_titre');
include 'header.php';
?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('evenements_page_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-calendar-days"></i> <?php echo t('evenements_page_titre'); ?></h1>
        <p><?php echo t('evenements_page_intro'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <div class="evt-calendar-card animate-on-scroll">
            <div class="evt-calendar-header">
                <button type="button" class="evt-nav-btn" id="evt-prev-month" aria-label="<?php echo t('evenements_mois_precedent'); ?>"><i class="fas fa-chevron-left"></i></button>
                <button type="button" class="evt-current-label" id="evt-current-label" aria-expanded="false" aria-haspopup="true"></button>
                <button type="button" class="evt-nav-btn" id="evt-next-month" aria-label="<?php echo t('evenements_mois_suivant'); ?>"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="evt-jump-popover" id="evt-jump-popover">
                <label class="evt-jump-date-label" for="evt-jump-date"><?php echo t('evenements_aller_a_une_date'); ?></label>
                <input type="date" id="evt-jump-date" class="evt-jump-date-input" min="<?php echo $window_start->format('Y-m-d'); ?>" max="<?php echo $window_end->format('Y-m-d'); ?>">
                <div id="evt-jump-months-wrap"></div>
            </div>
            <div class="evt-weekdays">
                <?php foreach ($cur_jours_noms as $jn): ?><span><?php echo htmlspecialchars($jn); ?></span><?php endforeach; ?>
            </div>
            <div class="evt-calendar-months" id="evt-calendar-months">
                <?php
                $month_cursor = clone $window_start;
                $month_index = 0;
                while ($month_cursor <= $window_end):
                    $year = (int) $month_cursor->format('Y');
                    $month = (int) $month_cursor->format('n');
                    $first_of_month = new DateTime("$year-$month-01");
                    $days_in_month = (int) $first_of_month->format('t');
                    // Lundi = 1 ... Dimanche = 7 : nombre de cases vides avant le jour 1.
                    $leading_blanks = ((int) $first_of_month->format('N')) - 1;
                    $month_label = $cur_mois_noms[$month - 1] . ' ' . $year;
                ?>
                    <div class="evt-calendar-month" data-index="<?php echo $month_index; ?>" data-label="<?php echo htmlspecialchars($month_label); ?>" data-year="<?php echo $year; ?>" data-month="<?php echo $month; ?>" <?php echo $month_index !== 1 ? 'style="display:none;"' : ''; ?>>
                        <div class="evt-days-grid">
                            <?php for ($b = 0; $b < $leading_blanks; $b++): ?><span class="evt-day evt-day-empty"></span><?php endfor; ?>
                            <?php for ($d = 1; $d <= $days_in_month; $d++):
                                $day_key = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                $day_events = $events_by_day[$day_key] ?? [];
                                $is_today = $day_key === $today->format('Y-m-d');
                                $day_payload = [];
                                foreach ($day_events as $e) {
                                    $day_payload[] = [
                                        'titre' => evt_field($e, 'titre', $lang),
                                        'description' => evt_field($e, 'description', $lang),
                                        'date_debut' => $e['date_debut'],
                                        'date_fin' => $e['date_fin'],
                                        'lieu' => $e['lieu'],
                                        'categorie' => $e['categorie'],
                                        'image' => $e['image_path'],
                                    ];
                                }
                            ?>
                                <span class="evt-day <?php echo $day_events ? 'has-events' : ''; ?> <?php echo $is_today ? 'is-today' : ''; ?>" data-date="<?php echo $day_key; ?>" <?php if ($day_events): ?>data-events='<?php echo htmlspecialchars(json_encode($day_payload), ENT_QUOTES); ?>' tabindex="0" role="button"<?php endif; ?>>
                                    <?php echo $d; ?>
                                    <?php if ($day_events): ?>
                                        <span class="evt-day-dots">
                                            <?php foreach (array_slice($day_events, 0, 3) as $e): ?>
                                                <span class="evt-day-dot" style="background: <?php echo $category_colors[$e['categorie']] ?? '#003366'; ?>;"></span>
                                            <?php endforeach; ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php
                    $month_cursor->modify('+1 month');
                    $month_index++;
                endwhile;
                ?>
            </div>

            <div class="evt-day-detail" id="evt-day-detail" hidden></div>
        </div>

        <section class="evt-upcoming-section">
            <h2 class="section-title"><i class="fas fa-hourglass-half"></i> <?php echo t('evenements_a_venir_titre'); ?></h2>

            <div class="evt-filter-pills" id="evt-filter-pills">
                <button type="button" class="evt-filter-pill is-active" data-cat="tous"><?php echo t('evenements_filtre_tous'); ?></button>
                <?php foreach ($category_label_keys as $cat => $key): ?>
                    <button type="button" class="evt-filter-pill" data-cat="<?php echo $cat; ?>"><i class="fas <?php echo $category_icons[$cat]; ?>"></i> <?php echo t($key); ?></button>
                <?php endforeach; ?>
            </div>

            <?php if (empty($upcoming_events)): ?>
                <p class="gallery-empty"><i class="fas fa-calendar-days"></i> <?php echo t('evenements_aucun_a_venir'); ?></p>
            <?php else: ?>
                <div class="evt-ticket-list">
                    <?php foreach ($upcoming_events as $i => $e):
                        $dt = new DateTime($e['date_debut']);
                        $cat = $e['categorie'];
                    ?>
                        <div class="evt-ticket-card animate-on-scroll" data-cat="<?php echo htmlspecialchars($cat); ?>" style="--evt-color: <?php echo $category_colors[$cat] ?? '#003366'; ?>; transition-delay: <?php echo ($i % 6) * 0.07; ?>s;">
                            <div class="evt-ticket-date">
                                <span class="evt-ticket-day"><?php echo $dt->format('d'); ?></span>
                                <span class="evt-ticket-month"><?php echo mb_substr($cur_mois_noms[(int) $dt->format('n') - 1], 0, 3); ?></span>
                            </div>
                            <div class="evt-ticket-body">
                                <span class="evt-ticket-cat"><i class="fas <?php echo $category_icons[$cat] ?? 'fa-calendar-day'; ?>"></i> <?php echo t($category_label_keys[$cat] ?? 'admin_evenements_categorie_general'); ?></span>
                                <h3><?php echo htmlspecialchars(evt_field($e, 'titre', $lang)); ?></h3>
                                <?php if (!empty($e['lieu'])): ?><p class="evt-ticket-lieu"><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($e['lieu']); ?></p><?php endif; ?>
                                <p class="evt-ticket-time"><i class="far fa-clock"></i> <?php echo $dt->format('d/m/Y H:i'); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const months = Array.from(document.querySelectorAll('.evt-calendar-month'));
    const label = document.getElementById('evt-current-label');
    const prevBtn = document.getElementById('evt-prev-month');
    const nextBtn = document.getElementById('evt-next-month');
    const detail = document.getElementById('evt-day-detail');
    if (!months.length) return;

    let current = 1; // fenêtre serveur : index 0 = mois précédent, 1 = mois courant
    function render() {
        months.forEach((m, i) => { m.style.display = i === current ? '' : 'none'; });
        label.textContent = months[current].dataset.label;
        prevBtn.disabled = current === 0;
        nextBtn.disabled = current === months.length - 1;
        detail.hidden = true;
        detail.innerHTML = '';
        popover.querySelectorAll('.evt-jump-month-btn').forEach(b => {
            b.classList.toggle('is-current', Number(b.dataset.index) === current);
        });
    }
    prevBtn.addEventListener('click', () => { if (current > 0) { current--; render(); } });
    nextBtn.addEventListener('click', () => { if (current < months.length - 1) { current++; render(); } });

    // --- Sélecteur rapide "cliquer sur le libellé pour aller directement à une année/mois/jour" ---
    const popover = document.getElementById('evt-jump-popover');
    const monthsWrap = document.getElementById('evt-jump-months-wrap');
    const dateInput = document.getElementById('evt-jump-date');
    const monthIndexByKey = new Map(); // "YYYY-M" -> index dans `months`
    const yearGroups = new Map(); // année -> [{index, monthName}]
    months.forEach((m, i) => {
        monthIndexByKey.set(m.dataset.year + '-' + m.dataset.month, i);
        const parts = m.dataset.label.split(' ');
        const year = parts.pop();
        const monthName = parts.join(' ');
        if (!yearGroups.has(year)) yearGroups.set(year, []);
        yearGroups.get(year).push({ index: i, monthName });
    });
    monthsWrap.innerHTML = Array.from(yearGroups.entries()).map(([year, monthList]) => `
        <div class="evt-jump-year-group">
            <h5>${escapeHtml(year)}</h5>
            <div class="evt-jump-months">
                ${monthList.map(mo => `<button type="button" class="evt-jump-month-btn" data-index="${mo.index}">${escapeHtml(mo.monthName)}</button>`).join('')}
            </div>
        </div>
    `).join('');

    function closePopover() {
        popover.classList.remove('is-open');
        label.setAttribute('aria-expanded', 'false');
    }
    label.addEventListener('click', (e) => {
        e.stopPropagation();
        const willOpen = !popover.classList.contains('is-open');
        popover.classList.toggle('is-open', willOpen);
        label.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });
    monthsWrap.addEventListener('click', (e) => {
        const btn = e.target.closest('.evt-jump-month-btn');
        if (!btn) return;
        current = Number(btn.dataset.index);
        render();
        closePopover();
    });
    popover.addEventListener('click', (e) => { e.stopPropagation(); });
    document.addEventListener('click', (e) => {
        if (!popover.contains(e.target) && e.target !== label) closePopover();
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePopover(); });

    // --- Aller directement à une date précise (jour compris), pas seulement au mois ---
    dateInput.addEventListener('change', () => {
        const value = dateInput.value; // "YYYY-MM-DD"
        if (!value) return;
        const [y, m] = value.split('-');
        const key = String(Number(y)) + '-' + String(Number(m));
        const targetIndex = monthIndexByKey.get(key);
        if (targetIndex === undefined) return; // hors de la fenêtre rendue (borné par min/max de l'input)
        current = targetIndex;
        render();
        closePopover();
        requestAnimationFrame(() => {
            const dayEl = months[current].querySelector(`.evt-day[data-date="${value}"]`);
            if (!dayEl) return;
            dayEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            dayEl.classList.add('is-jumped');
            setTimeout(() => dayEl.classList.remove('is-jumped'), 1600);
            if (dayEl.classList.contains('has-events')) openDay(dayEl);
        });
    });

    render();

    const catIcons = <?php echo json_encode($category_icons); ?>;
    const catColors = <?php echo json_encode($category_colors); ?>;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function openDay(dayEl) {
        let events;
        try { events = JSON.parse(dayEl.dataset.events); } catch (e) { return; }
        detail.innerHTML = events.map(ev => `
            <article class="evt-detail-item" style="--evt-color: ${catColors[ev.categorie] || '#003366'};">
                ${ev.image ? `<img src="${escapeHtml(ev.image)}" alt="" class="evt-detail-img">` : ''}
                <div class="evt-detail-body">
                    <span class="evt-detail-cat"><i class="fas ${catIcons[ev.categorie] || 'fa-calendar-day'}"></i> ${escapeHtml(ev.categorie)}</span>
                    <h4>${escapeHtml(ev.titre)}</h4>
                    <p class="evt-detail-meta"><i class="far fa-clock"></i> ${new Date(ev.date_debut.replace(' ', 'T')).toLocaleString()}</p>
                    ${ev.lieu ? `<p class="evt-detail-meta"><i class="fas fa-location-dot"></i> ${escapeHtml(ev.lieu)}</p>` : ''}
                    ${ev.description ? `<p class="evt-detail-desc">${escapeHtml(ev.description)}</p>` : ''}
                </div>
            </article>
        `).join('');
        detail.hidden = false;
        requestAnimationFrame(() => detail.classList.add('is-visible'));
        detail.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    document.getElementById('evt-calendar-months').addEventListener('click', (e) => {
        const dayEl = e.target.closest('.evt-day.has-events');
        if (dayEl) openDay(dayEl);
    });
    document.getElementById('evt-calendar-months').addEventListener('keydown', (e) => {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        const dayEl = e.target.closest('.evt-day.has-events');
        if (dayEl) { e.preventDefault(); openDay(dayEl); }
    });

    // Filtre par catégorie sur la liste "à venir"
    const pills = document.querySelectorAll('.evt-filter-pill');
    const cards = document.querySelectorAll('.evt-ticket-card');
    pills.forEach(pill => {
        pill.addEventListener('click', () => {
            pills.forEach(p => p.classList.remove('is-active'));
            pill.classList.add('is-active');
            const cat = pill.dataset.cat;
            cards.forEach(card => {
                card.style.display = (cat === 'tous' || card.dataset.cat === cat) ? '' : 'none';
            });
        });
    });
});
</script>

<?php include 'footer.php'; ?>
