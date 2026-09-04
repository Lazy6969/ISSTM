<?php
include_once '../language.php';
require_once '../db_connect.php';
require 'config.php';
require 'functions.php';

if (!estUtilisateurConnecte()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die('Document introuvable.');
}

$stmt = $pdo->prepare("SELECT m.*, f.nom AS filiere_nom, f.abreviation AS filiere_abrev, f.niveau,
                               men.nom AS mention_nom, men.abreviation AS mention_abrev, a.libelle AS annee
                        FROM memoires m
                        JOIN filieres f ON f.id = m.filiere_id
                        JOIN mentions men ON men.id = f.mention_id
                        JOIN annees_universitaires a ON a.id = m.annee_id
                        WHERE m.id = ?");
$stmt->execute([$id]);
$memoire = $stmt->fetch();

if (!$memoire) {
    die('Document introuvable.');
}

// Jeton temporaire à usage limité : sans ce jeton (régénéré à chaque
// chargement de cette page), le fichier PDF brut n'est pas accessible.
$token = bin2hex(random_bytes(32));
$_SESSION['memoire_token_' . $id] = [
    'token'  => $token,
    'expire' => time() + 300, // valable 5 minutes
];

$page_title = $memoire['titre'];
require '../header.php';
?>

<div class="page-banner bib-page-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php"><?php echo t('bib_nav_titre'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo e($memoire['titre']); ?></span>
        </nav>
        <h1><i class="fas fa-book-open"></i> <?php echo e($memoire['titre']); ?></h1>
        <p>
            <?php echo t('bib_auteur'); ?> : <?php echo e($memoire['auteur']); ?>
            <?php if ($memoire['encadreur']): ?> — <?php echo t('bib_encadreur'); ?> : <?php echo e($memoire['encadreur']); ?><?php endif; ?>
            — <?php echo e($memoire['categorie']); ?> —
            <?php echo e($memoire['niveau']); ?> - <?php echo e($memoire['filiere_nom']); ?> (<?php echo e($memoire['filiere_abrev']); ?>) —
            <?php echo t('bib_mention'); ?> : <?php echo e($memoire['mention_abrev']); ?> —
            <?php echo t('bib_annee'); ?> : <?php echo e($memoire['annee']); ?>
        </p>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <div class="alerte-bib">
            <i class="fas fa-lock"></i> <?php echo t('bib_consultation_uniquement'); ?>
        </div>

        <div id="visionneuse-wrapper" class="bib-visionneuse-wrapper">
            <div id="pages">
                <p id="chargement"><?php echo t('bib_chargement_document'); ?></p>
            </div>
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";

const memoireId = <?php echo (int) $id; ?>;
const token = "<?php echo $token; ?>";
const watermarkText = "<?php echo e(t('bib_filigrane_texte')); ?> — " + new Date().toLocaleDateString('<?php echo $lang === 'en' ? 'en-US' : ($lang === 'mg' ? 'mg-MG' : 'fr-FR'); ?>');

async function chargerDocument() {
    const url = "memoire_flux.php?id=" + memoireId + "&token=" + token;

    // En-tête personnalisé : bloque les tentatives d'accès en collant
    // directement l'URL dans la barre d'adresse du navigateur.
    const pdf = await pdfjsLib.getDocument({
        url: url,
        httpHeaders: { 'X-Requete-Visionneuse': '1' }
    }).promise;

    const container = document.getElementById('pages');
    document.getElementById('chargement').remove();

    for (let i = 1; i <= pdf.numPages; i++) {
        const page = await pdf.getPage(i);
        const viewport = page.getViewport({ scale: 1.4 });
        const canvas = document.createElement('canvas');
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        const ctx = canvas.getContext('2d');

        await page.render({ canvasContext: ctx, viewport: viewport }).promise;

        // Filigrane répété directement "cuit" dans l'image affichée
        ctx.save();
        ctx.globalAlpha = 0.12;
        ctx.font = '20px Arial';
        ctx.fillStyle = '#000000';
        ctx.translate(canvas.width / 2, canvas.height / 2);
        ctx.rotate(-Math.PI / 7);
        for (let y = -canvas.height; y < canvas.height; y += 110) {
            ctx.fillText(watermarkText, -canvas.width / 2, y);
        }
        ctx.restore();

        container.appendChild(canvas);
    }
}

chargerDocument().catch(function () {
    document.getElementById('pages').innerHTML =
        '<p><?php echo e(t('bib_erreur_chargement_document')); ?></p>';
});

// Restrictions dans la visionneuse
document.getElementById('visionneuse-wrapper').addEventListener('contextmenu', function (e) {
    e.preventDefault();
});
document.addEventListener('keydown', function (e) {
    const touche = e.key.toLowerCase();
    if ((e.ctrlKey || e.metaKey) && ['s', 'p', 'u', 'c'].includes(touche)) {
        e.preventDefault();
    }
});
</script>

<?php require '../footer.php'; ?>
