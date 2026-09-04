<?php
include_once '../../language.php';
require_once '../../db_connect.php';
require '../config.php';
require '../functions.php';
requireAdmin();

$page_title = t('bib_ajouter_memoire_titre');
$erreur = '';
$succes = '';

$filieres = $pdo->query("SELECT f.*, m.nom AS mention_nom, m.abreviation AS mention_abrev
                          FROM filieres f
                          JOIN mentions m ON m.id = f.mention_id
                          ORDER BY m.nom, f.niveau, f.nom")->fetchAll();
$annees = $pdo->query("SELECT * FROM annees_universitaires ORDER BY libelle DESC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $auteur = trim($_POST['auteur'] ?? '');
    $encadreur = trim($_POST['encadreur'] ?? '');
    $categorie = $_POST['categorie'] ?? '';
    $resume = trim($_POST['resume'] ?? '');
    $filiere_id = $_POST['filiere_id'] ?? '';
    $annee_id = $_POST['annee_id'] ?? '';

    if ($titre === '' || $auteur === '' || !in_array($categorie, ['Mémoire', 'Projet']) || !$filiere_id || !$annee_id) {
        $erreur = t('bib_erreur_champs_obligatoires_memoire');
    } elseif (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
        $erreur = t('bib_erreur_fichier_pdf_requis');
    } else {
        $extension = strtolower(pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION));

        if ($extension !== 'pdf') {
            $erreur = t('bib_erreur_format_memoire');
        } elseif ($_FILES['fichier']['size'] > 30 * 1024 * 1024) {
            $erreur = t('bib_erreur_taille_30mo');
        } else {
            $nomUnique = uniqid('memoire_', true) . '.pdf';
            $cheminDestination = __DIR__ . '/../uploads/' . $nomUnique;

            if (move_uploaded_file($_FILES['fichier']['tmp_name'], $cheminDestination)) {
                $contenu_texte = bib_extract_text($cheminDestination, $extension);
                $stmt = $pdo->prepare("INSERT INTO memoires (titre, auteur, encadreur, categorie, filiere_id, annee_id, resume, chemin_fichier, contenu_texte)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$titre, $auteur, $encadreur ?: null, $categorie, $filiere_id, $annee_id, $resume ?: null, $nomUnique, $contenu_texte ?: null]);
                $succes = t('bib_succes_memoire_ajoute');
            } else {
                $erreur = t('bib_erreur_enregistrement_fichier');
            }
        }
    }
}

require '../../header.php';
?>

<a href="dashboard.php" class="btn-outline btn-back-sticky"><i class="fas fa-arrow-left"></i> <?php echo t('admin_retour_tableau_bord'); ?></a>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="dashboard.php"><?php echo t('bib_admin_dashboard_titre'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('bib_ajouter_memoire_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-file-circle-plus"></i> <?php echo t('bib_ajouter_memoire_titre'); ?></h1>
        <p><?php echo t('bib_ajouter_memoire_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <?php if ($erreur): ?><div class="admin-flash admin-flash-error"><i class="fas fa-circle-exclamation"></i> <?php echo e($erreur); ?></div><?php endif; ?>
        <?php if ($succes): ?><div class="admin-flash admin-flash-success"><i class="fas fa-circle-check"></i> <?php echo e($succes); ?></div><?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="admin-form">
            <div class="album-form-grid">
                <div class="album-form-main">
                    <div class="form-group">
                        <label><?php echo t('bib_titre_label'); ?></label>
                        <input type="text" name="titre" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('bib_auteur_label'); ?></label>
                        <input type="text" name="auteur" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('bib_encadreur_optionnel_label'); ?></label>
                        <input type="text" name="encadreur">
                    </div>
                    <div class="form-group">
                        <label><?php echo t('bib_categorie_label'); ?></label>
                        <select name="categorie" required>
                            <option value=""><?php echo t('bib_choisir'); ?></option>
                            <option value="Mémoire"><?php echo t('bib_memoire'); ?></option>
                            <option value="Projet"><?php echo t('bib_projet'); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('bib_annee_universitaire_label'); ?></label>
                        <select name="annee_id" required>
                            <option value=""><?php echo t('bib_choisir'); ?></option>
                            <?php foreach ($annees as $a): ?>
                                <option value="<?php echo $a['id']; ?>"><?php echo e($a['libelle']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('bib_filiere_label'); ?></label>
                        <select name="filiere_id" required>
                            <option value=""><?php echo t('bib_choisir'); ?></option>
                            <?php $mentionCourante = null; foreach ($filieres as $f): ?>
                                <?php if ($mentionCourante !== $f['mention_nom']): ?>
                                    <?php if ($mentionCourante !== null) echo '</optgroup>'; ?>
                                    <optgroup label="<?php echo e($f['mention_nom']); ?> (<?php echo e($f['mention_abrev']); ?>)">
                                    <?php $mentionCourante = $f['mention_nom']; ?>
                                <?php endif; ?>
                                <option value="<?php echo $f['id']; ?>"><?php echo e($f['niveau']); ?> - <?php echo e($f['nom']); ?> (<?php echo e($f['abreviation']); ?>)</option>
                            <?php endforeach; if ($mentionCourante !== null) echo '</optgroup>'; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('bib_resume_optionnel_label'); ?></label>
                        <textarea name="resume" rows="4"></textarea>
                    </div>
                </div>
                <div class="album-form-side">
                    <div class="form-group upload-zone-group">
                        <label><?php echo t('bib_fichier_pdf_label'); ?></label>
                        <label class="upload-zone">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <span class="upload-zone-text"><?php echo t('bib_deposer_pdf'); ?></span>
                            <span class="upload-zone-filename"></span>
                            <input type="file" name="fichier" accept=".pdf" class="upload-zone-input" required>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit"><i class="fas fa-plus"></i> <?php echo t('bib_ajouter_le_document'); ?></button>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.upload-zone-input').forEach(input => {
        input.addEventListener('change', () => {
            const zone = input.closest('.upload-zone');
            const filenameSpan = zone.querySelector('.upload-zone-filename');
            if (input.files.length === 1) {
                filenameSpan.textContent = input.files[0].name;
                zone.classList.add('has-file');
            }
        });
    });
});
</script>

<?php require '../../footer.php'; ?>
