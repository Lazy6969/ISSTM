<?php
include_once 'language.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['reply' => 'Méthode non autorisée.']);
    exit;
}

// =========================================================================
// Assistant d'aide virtuel "IA" sans clé payante : met en correspondance la
// question de l'utilisateur avec la FAQ du site et une liste d'intentions
// courantes (inscription, bourse, contact...), via un score de mots communs
// tolérant aux accents/majuscules et aux petites fautes de frappe.
// =========================================================================

function ai_normalize($text) {
    $text = mb_strtolower(trim((string) $text), 'UTF-8');
    $translit = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    if ($translit !== false) { $text = $translit; }
    return $text;
}

function ai_words($text) {
    $stopwords = ['le','la','les','de','des','du','un','une','et','a','au','aux','pour','dans','sur','ou','est','avec','ce','ces','je','tu','il','elle','vous','comment','quel','quelle','quels','quelles','que','qui','mon','ma','mes','y','en'];
    $norm = ai_normalize($text);
    $words = preg_split('/[\s,;?!\'"]+/', $norm);
    return array_values(array_filter($words, fn($w) => $w !== '' && !in_array($w, $stopwords, true)));
}

function ai_overlap_score($questionWords, $targetWords) {
    $score = 0;
    foreach ($questionWords as $qw) {
        foreach ($targetWords as $tw) {
            if ($qw === $tw) { $score += 2; continue 2; }
            if (mb_strlen($qw) >= 4 && mb_strlen($tw) >= 4 && levenshtein($qw, $tw) <= 1) { $score += 1; continue 2; }
        }
    }
    return $score;
}

$input = json_decode(file_get_contents('php://input'), true);
$question = trim($input['message'] ?? '');

if ($question === '') {
    echo json_encode(['reply' => t('aide_ia_message_vide'), 'link' => null, 'link_label' => null]);
    exit;
}

$question_words = ai_words($question);

// --- Intentions rapides avec lien direct suggéré ---
$intents = [
    'inscription' => ['mots' => ['inscription','inscrire','admission','candidature','immatriculation'], 'reponse' => t('aide_ia_reponse_inscription'), 'lien' => 'inscription.php', 'lien_label' => t('form_inscription')],
    'bourse' => ['mots' => ['bourse','financement','aide','financiere','subvention'], 'reponse' => t('aide_ia_reponse_bourse'), 'lien' => 'bourse.php', 'lien_label' => t('bourse')],
    'contact' => ['mots' => ['contact','telephone','email','mail','joindre','adresse'], 'reponse' => t('aide_ia_reponse_contact'), 'lien' => 'index.php#contact', 'lien_label' => t('contact_section_titre')],
    'horaire' => ['mots' => ['horaire','heure','ouverture','ferme','ouvert'], 'reponse' => t('aide_ia_reponse_horaire'), 'lien' => null, 'lien_label' => null],
    'galerie' => ['mots' => ['galerie','photo','photos','image','video','album'], 'reponse' => t('aide_ia_reponse_galerie'), 'lien' => 'galerie.php', 'lien_label' => t('galeries')],
    'actualite' => ['mots' => ['actualite','actualites','news','evenement','nouvelle'], 'reponse' => t('aide_ia_reponse_actualite'), 'lien' => 'actualite.php', 'lien_label' => t('actualites')],
    'filiere' => ['mots' => ['filiere','filieres','parcours','formation','specialite','mention'], 'reponse' => t('aide_ia_reponse_filiere'), 'lien' => 'filieres.php', 'lien_label' => t('filieres_section_titre')],
];

$best_score = 0;
$best_reply = null;
$best_link = null;
$best_link_label = null;

foreach ($intents as $intent) {
    $score = ai_overlap_score($question_words, $intent['mots']);
    if ($score > $best_score) {
        $best_score = $score;
        $best_reply = $intent['reponse'];
        $best_link = $intent['lien'];
        $best_link_label = $intent['lien_label'];
    }
}

// --- FAQ du site (même contenu que la modale FAQ) ---
for ($i = 1; $i <= 10; $i++) {
    $faq_q = t("faq_q$i");
    $faq_a = t("faq_a$i");
    if ($faq_q === "faq_q$i" || $faq_a === "faq_a$i") { continue; }
    $score = ai_overlap_score($question_words, ai_words($faq_q));
    if ($score > $best_score) {
        $best_score = $score;
        $best_reply = $faq_a;
        $best_link = null;
        $best_link_label = null;
    }
}

if ($best_score >= 2 && $best_reply) {
    echo json_encode(['reply' => $best_reply, 'link' => $best_link, 'link_label' => $best_link_label]);
} else {
    echo json_encode(['reply' => t('aide_ia_reponse_inconnue'), 'link' => 'recherche.php?q=' . urlencode($question), 'link_label' => t('recherche_titre')]);
}
