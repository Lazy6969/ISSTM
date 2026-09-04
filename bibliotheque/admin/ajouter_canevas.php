<?php
include_once '../../language.php';
require_once '../../db_connect.php';
require '../config.php';
require '../functions.php';
requireAdmin();

$page_title = t('bib_ajouter_canevas_titre');
$erreur = '';
$succes = '';

$annees = $pdo->query("SELECT * FROM annees_universitaires ORDER BY libelle DESC")->fetchAll();

$extensionsAutorisees = [
    'doc' => 'word', 'docx' => 'word',
    'pdf' => 'pdf',
    'ppt' => 'pptx', 'pptx' => 'pptx',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $niveau = $_POST['niveau'] ?? '';
    $annee_id = $_POST['annee_id'] ?? '';

    if ($titre === '' || !in_array($niveau, ['Licence', 'Master']) || !$annee_id) {
        $erreur = t('bib_erreur_champs_obligatoires');
    } elseif (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
        $erreur = t('bib_erreur_fichier_requis');
    } else {
        $nomOriginal = $_FILES['fichier']['name'];
        $extension = strtolower(pathinfo($nomOriginal, PATHINFO_EXTENSION));

        if (!isset($extensionsAutorisees[$extension])) {
            $erreur = t('bib_erreur_format_canevas');
        } elseif ($_FILES['fichier']['size'] > 20 * 1024 * 1024) {
            $erreur = t('bib_erreur_taille_20mo');
        } else {
            $type_fichier = $extensionsAutorisees[$extension];
            $nomUnique = uniqid('canevas_', true) . '.' . $extension;
            $cheminDestination = __DIR__ . '/../uploads/' . $nomUnique;

            if (move_uploaded_file($_FILES['fichier']['tmp_name'], $cheminDestination)) {
                $contenu_texte = bib_extract_text($cheminDestination, $extension);
                $stmt = $pdo->prepare("INSERT INTO canevas (titre, niveau, annee_id, type_fichier, chemin_fichier, contenu_texte)
                                        VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$titre, $niveau, $annee_id, $type_fichier, $nomUnique, $contenu_texte ?: null]);
                $succes = t('bib_succes_canevas_ajoute');
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
            <span><?php echo t('bib_ajouter_canevas_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-file-circle-plus"></i> <?php echo t('bib_ajouter_canevas_titre'); ?></h1>
        <p><?php echo t('bib_ajouter_canevas_soustitre'); ?></p>
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
                        <label><?php echo t('bib_titre_canevas_label'); ?></label>
                        <input type="text" name="titre" placeholder="<?php echo t('bib_titre_canevas_placeholder'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo t('bib_niveau_label'); ?></label>
                        <select name="niveau" required>
                            <option value=""><?php echo t('bib_choisir'); ?></option>
                            <option value="Licence"><?php echo t('bib_licence'); ?></option>
                            <option value="Master"><?php echo t('bib_master'); ?></option>
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
                </div>
                <div class="album-form-side">
                    <div class="form-group upload-zone-group">
                        <label><?php echo t('bib_fichier_canevas_label'); ?></label>
                        <label class="upload-zone">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <span class="upload-zone-text"><?php echo t('bib_deposer_fichier'); ?></span>
                            <span class="upload-zone-filename"></span>
                            <input type="file" name="fichier" accept=".doc,.docx,.pdf,.ppt,.pptx" class="upload-zone-input" required>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit"><i class="fas fa-plus"></i> <?php echo t('bib_ajouter_le_canevas'); ?></button>
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
