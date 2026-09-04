<?php
include_once 'language.php';
require_once 'db_connect.php';

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: index.php?deja_connecte=1');
    exit;
}

$preinscription_filieres = $mysqli->query("SELECT id, nom_fr, niveaux FROM filieres ORDER BY display_order ASC, nom_fr ASC")->fetch_all(MYSQLI_ASSOC);
$pays_nationalites = include 'pays_nationalites.php';

$page_title = t('preinscription_form_titre');
include 'header.php';
?>

<div class="page-banner inscription-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="inscription.php"><?php echo t('inscription_titre'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('preinscription_form_titre'); ?></span>
        </nav>
        <h1><?php echo t('preinscription_form_titre'); ?></h1>
        <p><?php echo t('preinscription_form_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="background-animation">
        <span class="icon"><i class="fas fa-edit"></i></span>
        <span class="icon"><i class="fas fa-file-signature"></i></span>
        <span class="icon"><i class="fas fa-keyboard"></i></span>
        <span class="icon"><i class="fas fa-user-plus"></i></span>
    </div>
    <div class="container">
        <article class="form-article">
            <section class="content-block preinscription-section">
                <div class="preinscription-alert-banner">
                    <span class="preinscription-alert-icon"><i class="fas fa-triangle-exclamation"></i></span>
                    <p><?php echo t('preinscription_alerte'); ?></p>
                </div>

                <form id="preinscription-form" class="preinscription-form" novalidate>
                    <div class="preinscription-form-grid">
                        <fieldset class="preinscription-fieldset">
                            <legend><i class="fas fa-id-card"></i> <?php echo t('preinscription_section_identite'); ?></legend>
                            <div class="preinscription-row">
                                <div class="preinscription-field">
                                    <label for="pi-nom"><?php echo t('preinscription_nom'); ?> *</label>
                                    <input type="text" id="pi-nom" name="nom" required>
                                </div>
                                <div class="preinscription-field">
                                    <label for="pi-prenoms"><?php echo t('preinscription_prenoms'); ?> *</label>
                                    <input type="text" id="pi-prenoms" name="prenoms" required>
                                </div>
                                <div class="preinscription-field">
                                    <label><?php echo t('preinscription_sexe'); ?> *</label>
                                    <div class="preinscription-radio-group">
                                        <label class="preinscription-radio"><input type="radio" name="sexe" value="M" required> <?php echo t('preinscription_sexe_m'); ?></label>
                                        <label class="preinscription-radio"><input type="radio" name="sexe" value="F" required> <?php echo t('preinscription_sexe_f'); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="preinscription-row">
                                <div class="preinscription-field">
                                    <label for="pi-date-naissance"><?php echo t('preinscription_date_naissance'); ?> *</label>
                                    <input type="date" id="pi-date-naissance" name="date_naissance" required>
                                </div>
                                <div class="preinscription-field">
                                    <label for="pi-lieu-naissance"><?php echo t('preinscription_lieu_naissance'); ?> *</label>
                                    <input type="text" id="pi-lieu-naissance" name="lieu_naissance" required>
                                </div>
                                <div class="preinscription-field">
                                    <label for="pi-cin"><?php echo t('preinscription_cin'); ?></label>
                                    <input type="text" id="pi-cin" name="cin">
                                    <small class="preinscription-hint"><?php echo t('preinscription_cin_aide'); ?></small>
                                </div>
                            </div>
                            <div class="preinscription-row">
                                <div class="preinscription-field">
                                    <label for="pi-nationalite"><?php echo t('preinscription_nationalite'); ?> *</label>
                                    <select id="pi-nationalite" name="nationalite" required>
                                        <option value="" disabled selected><?php echo t('preinscription_choisir'); ?></option>
                                        <?php foreach ($pays_nationalites as $pn): ?>
                                            <option value="<?php echo htmlspecialchars($pn['nationalite']); ?>"><?php echo htmlspecialchars($pn['nationalite']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="preinscription-field">
                                    <label for="pi-pays"><?php echo t('preinscription_pays'); ?> *</label>
                                    <select id="pi-pays" name="pays" required>
                                        <option value="" disabled selected><?php echo t('preinscription_choisir'); ?></option>
                                        <?php foreach ($pays_nationalites as $pn): ?>
                                            <option value="<?php echo htmlspecialchars($pn['pays']); ?>"><?php echo htmlspecialchars($pn['pays']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="preinscription-fieldset">
                            <legend><i class="fas fa-graduation-cap"></i> <?php echo t('preinscription_section_parcours'); ?></legend>
                            <div class="preinscription-row">
                                <div class="preinscription-field">
                                    <label for="pi-filiere"><?php echo t('preinscription_filiere'); ?> *</label>
                                    <select id="pi-filiere" name="filiere_id" required>
                                        <option value="" disabled selected data-niveaux=""><?php echo t('preinscription_choisir'); ?></option>
                                        <?php foreach ($preinscription_filieres as $f): ?>
                                            <option value="<?php echo (int) $f['id']; ?>" data-niveaux="<?php echo htmlspecialchars($f['niveaux'] ?? ''); ?>"><?php echo htmlspecialchars($f['nom_fr']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="preinscription-field">
                                    <label for="pi-niveau"><?php echo t('preinscription_niveau'); ?> *</label>
                                    <select id="pi-niveau" name="niveau" required disabled>
                                        <option value="" disabled selected><?php echo t('preinscription_choisir_filiere_dabord'); ?></option>
                                    </select>
                                </div>
                                <div class="preinscription-field">
                                    <label for="pi-annee-bacc"><?php echo t('preinscription_annee_bacc'); ?> *</label>
                                    <input type="number" id="pi-annee-bacc" name="annee_bacc" min="1980" max="2100" required>
                                </div>
                            </div>
                            <div class="preinscription-row">
                                <div class="preinscription-field">
                                    <label for="pi-serie-bacc"><?php echo t('preinscription_serie_bacc'); ?> *</label>
                                    <select id="pi-serie-bacc" name="serie_bacc" required>
                                        <option value="" disabled selected><?php echo t('preinscription_choisir'); ?></option>
                                        <?php foreach (['C', 'D', 'L', 'S', 'A1', 'A2', 'OSE', 'Tech. Industriel', 'Tech. Génie civil', 'Tech. Tertiaire', 'Technologique', 'AUTRE'] as $serie): ?>
                                            <option value="<?php echo htmlspecialchars($serie); ?>"><?php echo htmlspecialchars($serie); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="preinscription-field" id="pi-serie-autre-wrap" hidden>
                                    <label for="pi-serie-bacc-autre"><?php echo t('preinscription_serie_bacc_autre_placeholder'); ?> *</label>
                                    <input type="text" id="pi-serie-bacc-autre" name="serie_bacc_autre">
                                </div>
                                <div class="preinscription-field">
                                    <label for="pi-mention-bacc"><?php echo t('preinscription_mention_bacc'); ?> *</label>
                                    <select id="pi-mention-bacc" name="mention_bacc" required>
                                        <option value="" disabled selected><?php echo t('preinscription_choisir'); ?></option>
                                        <option value="Passable"><?php echo t('preinscription_mention_passable'); ?></option>
                                        <option value="Assez Bien"><?php echo t('preinscription_mention_assez_bien'); ?></option>
                                        <option value="Bien"><?php echo t('preinscription_mention_bien'); ?></option>
                                        <option value="Très Bien"><?php echo t('preinscription_mention_tres_bien'); ?></option>
                                    </select>
                                </div>
                                <div class="preinscription-field">
                                    <label><?php echo t('preinscription_code_redoublement'); ?> *</label>
                                    <div class="preinscription-radio-group">
                                        <label class="preinscription-radio"><input type="radio" name="code_redoublement" value="N" required> <?php echo t('preinscription_redoublement_n'); ?></label>
                                        <label class="preinscription-radio"><input type="radio" name="code_redoublement" value="R" required> <?php echo t('preinscription_redoublement_r'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="preinscription-fieldset">
                            <legend><i class="fas fa-location-dot"></i> <?php echo t('preinscription_section_contact'); ?></legend>
                            <div class="preinscription-row">
                                <div class="preinscription-field preinscription-field-full">
                                    <label for="pi-adresse"><?php echo t('preinscription_adresse'); ?> *</label>
                                    <input type="text" id="pi-adresse" name="adresse" required>
                                </div>
                            </div>
                            <div class="preinscription-row">
                                <div class="preinscription-field">
                                    <label for="pi-telephone"><?php echo t('preinscription_telephone'); ?> *</label>
                                    <input type="tel" id="pi-telephone" name="telephone" required>
                                </div>
                                <div class="preinscription-field">
                                    <label for="pi-email"><?php echo t('preinscription_email'); ?> *</label>
                                    <input type="email" id="pi-email" name="email" required>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="preinscription-fieldset">
                            <legend><i class="fas fa-people-roof"></i> <?php echo t('preinscription_section_parents'); ?></legend>
                            <div class="preinscription-row">
                                <div class="preinscription-field">
                                    <label for="pi-nom-pere"><?php echo t('preinscription_nom_pere'); ?></label>
                                    <input type="text" id="pi-nom-pere" name="nom_pere">
                                </div>
                                <div class="preinscription-field">
                                    <label for="pi-profession-pere"><?php echo t('preinscription_profession_pere'); ?></label>
                                    <input type="text" id="pi-profession-pere" name="profession_pere">
                                </div>
                                <div class="preinscription-field">
                                    <label for="pi-nom-mere"><?php echo t('preinscription_nom_mere'); ?></label>
                                    <input type="text" id="pi-nom-mere" name="nom_mere">
                                </div>
                                <div class="preinscription-field">
                                    <label for="pi-profession-mere"><?php echo t('preinscription_profession_mere'); ?></label>
                                    <input type="text" id="pi-profession-mere" name="profession_mere">
                                </div>
                            </div>
                            <div class="preinscription-row">
                                <div class="preinscription-field preinscription-field-full">
                                    <label for="pi-adresse-parents"><?php echo t('preinscription_adresse_parents'); ?></label>
                                    <input type="text" id="pi-adresse-parents" name="adresse_parents">
                                </div>
                            </div>
                            <div class="preinscription-row">
                                <div class="preinscription-field">
                                    <label for="pi-contact-parents"><?php echo t('preinscription_contact_parents'); ?> 1</label>
                                    <input type="text" id="pi-contact-parents" name="contact_parents">
                                </div>
                                <div class="preinscription-field">
                                    <label for="pi-contact-parents-2"><?php echo t('preinscription_contact_parents'); ?> 2 (<?php echo t('preinscription_facultatif'); ?>)</label>
                                    <input type="text" id="pi-contact-parents-2" name="contact_parents_2">
                                </div>
                                <div class="preinscription-field preinscription-field-full">
                                    <p class="preinscription-hint"><?php echo t('preinscription_contact_parents_aide'); ?></p>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="preinscription-fieldset">
                            <legend><i class="fas fa-camera-retro"></i> <?php echo t('preinscription_section_photo'); ?></legend>
                            <div class="preinscription-photo-upload">
                                <div class="preinscription-photo-preview" id="pi-photo-preview">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="preinscription-photo-controls">
                                    <label for="pi-photo" class="preinscription-photo-btn">
                                        <i class="fas fa-upload"></i> <?php echo t('preinscription_photo_choisir'); ?>
                                    </label>
                                    <input type="file" id="pi-photo" name="photo" accept="image/*" class="preinscription-file-hidden" required>
                                    <p class="preinscription-hint"><?php echo t('preinscription_photo_aide'); ?></p>
                                    <p class="preinscription-photo-filename" id="pi-photo-filename"></p>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <div class="preinscription-submit-row">
                        <button type="submit" class="btn btn-primary preinscription-submit-btn" id="pi-submit-btn">
                            <i class="fas fa-paper-plane"></i> <span><?php echo t('preinscription_bouton_envoyer'); ?></span>
                        </button>
                    </div>
                </form>
            </section>
        </article>
    </div>

    <!-- Fenêtre centrée : confirmation avant envoi de la pré-inscription -->
    <div class="preinscription-confirm-overlay" id="pi-confirm-overlay">
        <div class="preinscription-confirm-modal">
            <div class="preinscription-confirm-icon"><i class="fas fa-circle-question"></i></div>
            <h3><?php echo t('preinscription_confirm_titre'); ?></h3>
            <p><?php echo t('preinscription_confirm_desc'); ?></p>
            <div class="preinscription-confirm-recap" id="pi-confirm-recap"></div>
            <div class="preinscription-confirm-actions">
                <button type="button" class="preinscription-confirm-btn preinscription-confirm-btn-cancel" id="pi-confirm-cancel"><?php echo t('preinscription_confirm_bouton_modifier'); ?></button>
                <button type="button" class="preinscription-confirm-btn preinscription-confirm-btn-ok" id="pi-confirm-ok"><?php echo t('preinscription_confirm_bouton_confirmer'); ?></button>
            </div>
        </div>
    </div>

    <!-- Fenêtre centrée : confirmation de succès -->
    <div class="preinscription-confirm-overlay" id="pi-success-overlay">
        <div class="preinscription-confirm-modal preinscription-success-modal">
            <div class="preinscription-confirm-icon preinscription-success-icon"><i class="fas fa-circle-check"></i></div>
            <h3><?php echo t('preinscription_succes_titre'); ?></h3>
            <p><?php echo t('preinscription_succes_desc'); ?></p>
            <div class="preinscription-confirm-actions">
                <button type="button" class="preinscription-confirm-btn preinscription-confirm-btn-ok" id="pi-success-close"><?php echo t('preinscription_succes_bouton'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
window.PREINSCRIPTION_LABELS = {
    champObligatoire: <?php echo json_encode(t('preinscription_champ_obligatoire')); ?>,
    erreurPhoto: <?php echo json_encode(t('preinscription_erreur_photo')); ?>,
    erreurGenerique: <?php echo json_encode(t('preinscription_erreur_generique')); ?>,
    envoiEnCours: <?php echo json_encode(t('preinscription_bouton_envoi_cours')); ?>,
    envoyer: <?php echo json_encode(t('preinscription_bouton_envoyer')); ?>,
};

(function () {
    const form = document.getElementById('preinscription-form');
    if (!form) return;

    const filiereSelect = document.getElementById('pi-filiere');
    const niveauSelect = document.getElementById('pi-niveau');
    const niveauChoisirDabord = <?php echo json_encode(t('preinscription_choisir_filiere_dabord')); ?>;
    const niveauChoisir = <?php echo json_encode(t('preinscription_choisir')); ?>;
    filiereSelect.addEventListener('change', function () {
        const opt = filiereSelect.options[filiereSelect.selectedIndex];
        const niveaux = (opt.dataset.niveaux || '').split(',').map(function (s) { return s.trim(); }).filter(Boolean);
        niveauSelect.innerHTML = '';
        if (niveaux.length === 0) {
            niveauSelect.disabled = true;
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.disabled = true;
            placeholder.selected = true;
            placeholder.textContent = niveauChoisirDabord;
            niveauSelect.appendChild(placeholder);
            return;
        }
        niveauSelect.disabled = false;
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.disabled = true;
        placeholder.selected = true;
        placeholder.textContent = niveauChoisir;
        niveauSelect.appendChild(placeholder);
        niveaux.forEach(function (n) {
            const o = document.createElement('option');
            o.value = n;
            o.textContent = n;
            niveauSelect.appendChild(o);
        });
    });

    const serieSelect = document.getElementById('pi-serie-bacc');
    const serieAutreWrap = document.getElementById('pi-serie-autre-wrap');
    const serieAutreInput = document.getElementById('pi-serie-bacc-autre');
    serieSelect.addEventListener('change', function () {
        const isAutre = serieSelect.value === 'AUTRE';
        serieAutreWrap.hidden = !isAutre;
        serieAutreInput.required = isAutre;
        if (!isAutre) serieAutreInput.value = '';
    });

    const photoInput = document.getElementById('pi-photo');
    const photoPreview = document.getElementById('pi-photo-preview');
    const photoFilename = document.getElementById('pi-photo-filename');
    photoInput.addEventListener('change', function () {
        const file = photoInput.files && photoInput.files[0];
        if (!file) {
            photoPreview.innerHTML = '<i class="fas fa-user"></i>';
            photoFilename.textContent = '';
            return;
        }
        photoFilename.textContent = file.name;
        const reader = new FileReader();
        reader.onload = function (e) {
            photoPreview.innerHTML = '<img src="' + e.target.result + '" alt="">';
        };
        reader.readAsDataURL(file);
    });

    // Construit le récapitulatif affiché dans la fenêtre de confirmation en parcourant
    // dynamiquement les champs remplis, plutôt que de dupliquer chaque libellé en JS.
    function buildRecap() {
        const seen = new Set();
        const rows = [];
        form.querySelectorAll('[name]').forEach(function (el) {
            const name = el.name;
            if (seen.has(name)) return;
            const wrap = el.closest('.preinscription-field');
            if (!wrap || wrap.hidden) return;

            if (el.type === 'radio') {
                const checked = form.querySelector('[name="' + name + '"]:checked');
                seen.add(name);
                if (!checked) return;
                const groupLabel = wrap.querySelector('label') ? wrap.querySelector('label').textContent.replace('*', '').trim() : name;
                const valueLabel = checked.closest('.preinscription-radio').textContent.trim();
                rows.push([groupLabel, valueLabel]);
                return;
            }
            seen.add(name);
            const value = el.value.trim();
            if (value === '') return;
            const labelEl = wrap.querySelector('label');
            const label = labelEl ? labelEl.textContent.replace('*', '').trim() : name;
            const display = el.tagName === 'SELECT' && el.selectedIndex >= 0 ? el.options[el.selectedIndex].textContent : value;
            rows.push([label, display]);
        });
        return rows;
    }

    function renderRecap() {
        const recapEl = document.getElementById('pi-confirm-recap');
        const rows = buildRecap();
        if (photoInput.files && photoInput.files[0]) {
            rows.push([document.querySelector('label[for="pi-photo"]').textContent.trim(), photoInput.files[0].name]);
        }
        recapEl.innerHTML = rows.map(function (r) {
            return '<div class="preinscription-recap-row"><span>' + r[0] + '</span><strong>' + r[1].replace(/</g, '&lt;') + '</strong></div>';
        }).join('');
    }

    const confirmOverlay = document.getElementById('pi-confirm-overlay');
    const confirmCancel = document.getElementById('pi-confirm-cancel');
    const confirmOk = document.getElementById('pi-confirm-ok');
    const successOverlay = document.getElementById('pi-success-overlay');
    const successClose = document.getElementById('pi-success-close');
    const submitBtn = document.getElementById('pi-submit-btn');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        renderRecap();
        confirmOverlay.classList.add('is-open');
    });

    confirmCancel.addEventListener('click', function () {
        confirmOverlay.classList.remove('is-open');
    });
    confirmOverlay.addEventListener('click', function (e) {
        if (e.target === confirmOverlay) confirmOverlay.classList.remove('is-open');
    });

    confirmOk.addEventListener('click', function () {
        confirmOverlay.classList.remove('is-open');
        submitBtn.disabled = true;
        submitBtn.querySelector('span').textContent = window.PREINSCRIPTION_LABELS.envoiEnCours;

        const formData = new FormData(form);
        fetch('inscription_submit.php', { method: 'POST', body: formData })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    successOverlay.classList.add('is-open');
                    form.reset();
                    serieAutreWrap.hidden = true;
                    photoPreview.innerHTML = '<i class="fas fa-user"></i>';
                    photoFilename.textContent = '';
                    niveauSelect.innerHTML = '<option value="" disabled selected>' + niveauChoisirDabord + '</option>';
                    niveauSelect.disabled = true;
                } else {
                    alert(window.PREINSCRIPTION_LABELS.erreurGenerique);
                }
            })
            .catch(function () {
                alert(window.PREINSCRIPTION_LABELS.erreurGenerique);
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.querySelector('span').textContent = window.PREINSCRIPTION_LABELS.envoyer;
            });
    });

    successClose.addEventListener('click', function () {
        successOverlay.classList.remove('is-open');
    });
    successOverlay.addEventListener('click', function (e) {
        if (e.target === successOverlay) successOverlay.classList.remove('is-open');
    });
})();
</script>

<?php include 'footer.php'; ?>
