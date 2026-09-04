<?php
include_once 'language.php';
$page_title_q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page_title = $page_title_q !== '' ? t('recherche_titre') . ' : ' . $page_title_q : t('recherche_titre');

include 'header.php';
require_once 'db_connect.php';

// =========================================================================
// "Recherche intelligente" : sans clé IA payante, on reformule la requête
// en normalisant les accents/majuscules, en ignorant les mots vides, et en
// élargissant chaque mot à ses synonymes usuels + une tolérance aux fautes
// de frappe (distance de Levenshtein) pour retrouver du contenu pertinent
// même si l'utilisateur ne tape pas exactement les bons mots.
// =========================================================================

function search_normalize($text) {
    $text = mb_strtolower(trim((string) $text), 'UTF-8');
    $translit = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    if ($translit !== false) { $text = $translit; }
    return $text;
}

function search_synonym_map() {
    return [
        'ecole' => ['institut', 'etablissement', 'universite'],
        'inscription' => ['admission', 'inscrire', 'candidature', 'immatriculation'],
        'bourse' => ['aide financiere', 'financement', 'subvention'],
        'filiere' => ['parcours', 'formation', 'specialite', 'mention', 'diplome'],
        'professeur' => ['enseignant', 'prof', 'formateur', 'intervenant'],
        'photo' => ['image', 'galerie', 'album', 'media'],
        'video' => ['film', 'clip'],
        'actu' => ['actualite', 'news', 'nouvelle', 'evenement'],
        'contact' => ['telephone', 'email', 'mail', 'adresse', 'joindre'],
        'stage' => ['emploi', 'debouche', 'carriere', 'travail', 'job'],
        'campus' => ['batiment', 'infrastructure', 'site', 'locaux'],
        'etudiant' => ['eleve', 'apprenant'],
        'association' => ['club', 'activite', 'vie etudiante'],
        'directeur' => ['direction', 'responsable'],
        'connexion' => ['login', 'se connecter', 'compte'],
        'histoire' => ['historique', 'origine', 'creation'],
    ];
}

function search_expand_terms($query) {
    $synonyms = search_synonym_map();
    $norm = search_normalize($query);
    $stopwords = ['le','la','les','de','des','du','un','une','et','a','au','aux','pour','dans','sur','ou','est','avec','ce','ces','mon','ma','mes'];
    $words = array_filter(preg_split('/[\s,;\'"]+/', $norm), fn($w) => $w !== '' && !in_array($w, $stopwords, true));

    $terms = [$norm];
    foreach ($words as $w) {
        if (mb_strlen($w) < 2) { continue; }
        $terms[] = $w;
        foreach ($synonyms as $key => $syns) {
            if ($w === $key || in_array($w, $syns, true)) {
                $terms[] = $key;
                $terms = array_merge($terms, $syns);
            }
        }
    }
    return array_values(array_unique(array_filter($terms)));
}

// Une correspondance "floue" : substring direct, ou tolérance de 1-2 fautes
// de frappe (distance de Levenshtein) pour les mots de 4 lettres ou plus.
function search_fuzzy_contains($haystack, $term) {
    if ($term === '') { return false; }
    if (mb_strpos($haystack, $term) !== false) { return true; }
    if (mb_strlen($term) < 4) { return false; }
    foreach (preg_split('/[\s,;]+/', $haystack) as $hword) {
        if ($hword === '') { continue; }
        $maxDist = mb_strlen($term) >= 7 ? 2 : 1;
        if (levenshtein($term, $hword) <= $maxDist) { return true; }
    }
    return false;
}

// --- Construction de l'index de contenu "recherchable" (statique + dynamique) ---
$contenu_site = [
    ['titre' => t('accueil'), 'description' => 'Page principale de l\'ISSTM. Statistiques, mot du directeur, mission et vision.', 'url' => 'index.php', 'image' => 'images/logo-isstm.jpg', 'mots_cles' => 'accueil isstm bienvenue statistiques directeur mission vision'],
    ['titre' => t('historiques'), 'description' => 'Découvrez la genèse, le contexte de création et l\'évolution de notre établissement.', 'url' => 'historique.php', 'image' => 'images/historique.jpg', 'mots_cles' => 'histoire historique creation contexte objectifs lmd'],
    ['titre' => t('menu_organigramme'), 'description' => 'Structure organisationnelle et académique de l\'ISSTM, du conseil d\'établissement aux parcours de formation.', 'url' => 'parcours.php', 'image' => 'images/parcours.jpg', 'mots_cles' => 'organigramme parcours structure mentions sti stgc stnpa'],
    ['titre' => t('enseignants'), 'description' => 'Le corps enseignant et l\'équipe pédagogique de l\'ISSTM.', 'url' => 'enseignants.php', 'image' => 'images/prof.jpg', 'mots_cles' => 'enseignants professeurs corps professoral equipe pedagogique'],
    ['titre' => t('form_inscription'), 'description' => 'Accédez au formulaire pour vous inscrire à l\'une de nos formations.', 'url' => 'inscription.php', 'image' => 'images/ins.jpg', 'mots_cles' => 'inscription formulaire admission candidature'],
    ['titre' => t('bourse'), 'description' => 'Informations sur les bourses et aides financières disponibles pour les étudiants.', 'url' => 'bourse.php', 'image' => 'images/bourse.jpg', 'mots_cles' => 'bourse aide financiere financement subvention'],
    ['titre' => t('vie_etudiante'), 'description' => 'La vie associative, les clubs et les activités des étudiants de l\'ISSTM.', 'url' => 'vie_etudiante.php', 'image' => 'images/salle.jpg', 'mots_cles' => 'vie etudiante associations clubs activites'],
    ['titre' => 'Campus', 'description' => 'Découvrez les infrastructures et les locaux du campus de l\'ISSTM.', 'url' => 'campus.php', 'image' => 'images/portal_campus_1.jpg', 'mots_cles' => 'campus batiment infrastructure locaux site'],
    ['titre' => 'Associations', 'description' => 'Les associations et la vie associative au sein de l\'ISSTM.', 'url' => 'associations.php', 'image' => 'images/portal_assoc_4.jpg', 'mots_cles' => 'associations vie etudiante clubs'],
    ['titre' => t('galeries'), 'description' => 'Toutes les photos et vidéos des événements de l\'ISSTM.', 'url' => 'galerie.php', 'image' => 'images/logo-isstm.jpg', 'mots_cles' => 'galerie photos videos album images media'],
    ['titre' => t('actualites'), 'description' => 'Toutes les actualités et derniers événements de l\'ISSTM.', 'url' => 'actualite.php', 'image' => 'images/logo-isstm.jpg', 'mots_cles' => 'actualites news evenements'],
];

// --- Albums de galerie publiés ---
if ($mysqli->query("SHOW TABLES LIKE 'gallery_albums'")->num_rows > 0) {
    $r = $mysqli->query("SELECT id, title_fr, description_fr, cover_image FROM gallery_albums WHERE status='publie' ORDER BY created_at DESC LIMIT 40");
    while ($row = $r->fetch_assoc()) {
        $contenu_site[] = [
            'titre' => $row['title_fr'],
            'description' => $row['description_fr'] ?: 'Album photo de la galerie ISSTM.',
            'url' => 'galerie_album.php?id=' . $row['id'],
            'image' => $row['cover_image'] ?: 'images/logo-isstm.jpg',
            'mots_cles' => 'galerie album photo ' . $row['title_fr'],
        ];
    }
}

// --- Articles d'actualités publiés ---
if ($mysqli->query("SHOW TABLES LIKE 'news_articles'")->num_rows > 0) {
    $r = $mysqli->query("SELECT title_fr, excerpt_fr, slug, image_path, tags FROM news_articles WHERE status='publie' AND (published_at IS NULL OR published_at <= NOW()) ORDER BY published_at DESC LIMIT 40");
    while ($row = $r->fetch_assoc()) {
        $contenu_site[] = [
            'titre' => $row['title_fr'],
            'description' => $row['excerpt_fr'] ?: 'Article d\'actualité de l\'ISSTM.',
            'url' => 'actualite_article.php?slug=' . urlencode($row['slug']),
            'image' => $row['image_path'] ?: 'images/logo-isstm.jpg',
            'mots_cles' => 'actualite article news ' . $row['title_fr'] . ' ' . $row['tags'],
        ];
    }
}

// --- Filières / parcours ---
if ($mysqli->query("SHOW TABLES LIKE 'filieres'")->num_rows > 0) {
    $r = $mysqli->query("SELECT nom_fr, description_fr, slug, image_path, mention FROM filieres ORDER BY display_order ASC");
    while ($row = $r->fetch_assoc()) {
        $contenu_site[] = [
            'titre' => $row['nom_fr'],
            'description' => $row['description_fr'] ?: 'Filière proposée par l\'ISSTM.',
            'url' => 'filiere_detail.php?slug=' . urlencode($row['slug']),
            'image' => $row['image_path'] ?: 'images/logo-isstm.jpg',
            'mots_cles' => 'filiere parcours formation ' . $row['mention'] . ' ' . $row['nom_fr'],
        ];
    }
}

$resultats = [];
$recherche = '';
$termes_recherche = [];

if (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $recherche = trim($_GET['q']);
    $termes_recherche = search_expand_terms($recherche);

    $scored = [];
    foreach ($contenu_site as $page) {
        $haystack = search_normalize($page['titre'] . ' ' . $page['description'] . ' ' . $page['mots_cles']);
        $score = 0;
        foreach ($termes_recherche as $term) {
            if (search_fuzzy_contains($haystack, $term)) { $score++; }
        }
        if ($score > 0) {
            $page['score'] = $score;
            $scored[] = $page;
        }
    }
    usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
    $resultats = $scored;
}

// Termes reformulés à afficher à l'utilisateur (hors requête originale et mots vides très courts)
$termes_affiches = array_values(array_filter($termes_recherche, function ($t) use ($recherche) {
    return $t !== search_normalize($recherche) && mb_strlen($t) > 2;
}));
?>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('recherche_titre'); ?></span>
        </nav>
        <h1><?php echo t('recherche_titre'); ?></h1>
        <p><?php echo t('recherche_pour'); ?> "<?php echo htmlspecialchars($recherche); ?>"</p>
    </div>
</div>

<div class="page-content">
    <?php include 'background_animation.php'; ?>
    <div class="container">

        <form method="GET" action="recherche.php" class="search-page-form">
            <input type="search" name="q" value="<?php echo htmlspecialchars($recherche); ?>" placeholder="<?php echo t('rechercher_placeholder'); ?>" autocomplete="off">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>

        <?php if (!empty($resultats)): ?>

            <?php if (!empty($termes_affiches)): ?>
                <p class="search-reformulated">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <?php echo t('recherche_elargie_a'); ?>
                    <?php foreach (array_slice($termes_affiches, 0, 6) as $i => $t): ?>
                        <span class="search-term-chip"><?php echo htmlspecialchars($t); ?></span>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>

            <p class="search-results-count"><?php echo count($resultats); ?> <?php echo t('recherche_resultats_trouves'); ?></p>

            <div class="search-results-grid">
                <?php foreach ($resultats as $resultat): ?>
                    <a href="<?php echo htmlspecialchars($resultat['url']); ?>" class="search-result-card">
                        <div class="search-result-img" style="background-image:url('<?php echo htmlspecialchars($resultat['image']); ?>');"></div>
                        <div class="search-result-body">
                            <h3><?php echo htmlspecialchars($resultat['titre']); ?></h3>
                            <p><?php echo htmlspecialchars(mb_strimwidth($resultat['description'], 0, 110, '…')); ?></p>
                            <span class="search-result-link"><?php echo t('en_savoir_plus'); ?> <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

        <?php elseif (!empty($recherche)): ?>
            <p class="no-results"><?php echo t('recherche_aucun_resultat'); ?> "<?php echo htmlspecialchars($recherche); ?>". <?php echo t('recherche_essayez_autre'); ?></p>
        <?php else: ?>
            <p class="no-results"><?php echo t('recherche_veuillez_entrer'); ?></p>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
