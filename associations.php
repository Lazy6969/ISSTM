<?php
include_once 'language.php';
$page_title = t('associations_titre');
include 'header.php';

// --- Composition du Bureau Exécutif (Art. 13 des statuts) ---
$bureau_roles = [
    ['role' => 'Président', 'count' => 1, 'critere' => "Seuls les niveaux L2 et M1 peuvent être élus Président de l'A.E.I.", 'icon' => 'fa-user-tie'],
    ['role' => 'Vice-Président', 'count' => 1, 'critere' => "Désigné par le Président ; aucune restriction de niveau, sauf L1.", 'icon' => 'fa-user-shield'],
    ['role' => 'Secrétaire Général', 'count' => 1, 'critere' => "Doit être un des candidats non-élus lors de l'élection du Président.", 'icon' => 'fa-pen-nib'],
    ['role' => 'Trésorier', 'count' => 1, 'critere' => "Désigné par les autres membres du bureau et les chefs de classe.", 'icon' => 'fa-wallet'],
    ['role' => 'Commissaire aux Comptes', 'count' => 5, 'critere' => "Chaque niveau (L1, L2, L3, M1, M2) envoie un représentant.", 'icon' => 'fa-magnifying-glass-chart'],
    ['role' => 'Conseillers', 'count' => 4, 'critere' => "Chaque mention désigne un représentant ; l'ex-Président en fait partie.", 'icon' => 'fa-people-group'],
];

// --- Bureau fondateur élu lors du procès-verbal du 10 mai 2022 ---
$bureau_fondateur = [
    ['role' => 'Président', 'nom' => 'NOMENJANAHARY Narcisse Isidore'],
    ['role' => 'Vice-président', 'nom' => 'RAKOTOARIVELO Vannyaud Bruno'],
    ['role' => 'Secrétaire Générale', 'nom' => 'HARENANTENAINA Florentinoh Jobela Adelin'],
    ['role' => 'Trésorier', 'nom' => 'RATSIMALAIMANANA Mamy Nirina'],
    ['role' => 'Commissaire au compte', 'nom' => 'RANDRIANARIMALALA Jean Leonard'],
    ['role' => 'Commissaire au compte', 'nom' => 'RABARIVELOMANANA Maxwell Ny Aina'],
    ['role' => 'Commissaire au compte', 'nom' => 'RAZAFINDRAFITA Zagarino'],
    ['role' => 'Commissaire au compte', 'nom' => 'RANDRIANAIVOSOLO Aina Daniel'],
    ['role' => 'Commissaire au compte', 'nom' => 'ADIALHAM Tonganjara'],
    ['role' => 'Conseiller', 'nom' => 'RABOTOVAO Harimboahangitiana Kanto'],
    ['role' => 'Conseiller', 'nom' => 'RANDRIAMANTENA Judicaël'],
    ['role' => 'Conseiller', 'nom' => 'FIDERANA Nardah Mamelphina'],
    ['role' => 'Conseiller', 'nom' => 'FREDERIC Moise'],
];

// --- Galerie photo, gérée depuis Administration > Galerie si la table existe, sinon valeurs par défaut ---
$galerie_items = [
    ['img' => 'images/portal_assoc_4.jpg', 'alt' => 'Match de football étudiant', 'cat' => 'vie'],
    ['img' => 'images/portal_assoc_5.jpg', 'alt' => 'Cérémonie étudiante', 'cat' => 'vie'],
    ['img' => 'images/portal_assoc_6.jpg', 'alt' => 'Ambiance sur le campus', 'cat' => 'vie'],
    ['img' => 'images/slide1.jpg', 'alt' => "Entrée de l'ISSTM", 'cat' => 'campus'],
    ['img' => 'images/slide2.jpg', 'alt' => "Bâtiments de l'ISSTM", 'cat' => 'campus'],
    ['img' => 'images/slide3.jpg', 'alt' => "Logo ISSTM sur le bâtiment", 'cat' => 'campus'],
];

// Puise dans le même système de galerie que admin_galerie.php (albums "Vie étudiante" et
// "Campus & infrastructures"), pour n'avoir qu'une seule gestion de galerie sur tout le site.
$db_gallery = [];
$result = $mysqli->query("SELECT p.image_path, p.title, p.alt_text, a.category_id
                           FROM gallery_photos p
                           JOIN gallery_albums a ON a.id = p.album_id
                           WHERE a.category_id IN (3, 4) AND (p.media_type = 'photo' OR p.media_type IS NULL) AND a.status = 'publie'
                           ORDER BY p.created_at DESC LIMIT 24");
while ($row = $result->fetch_assoc()) {
    $db_gallery[] = [
        'img' => $row['image_path'],
        'alt' => $row['alt_text'] ?: ($row['title'] ?: 'Photo de la galerie'),
        'cat' => (int) $row['category_id'] === 4 ? 'campus' : 'vie',
    ];
}
if (!empty($db_gallery)) {
    $galerie_items = $db_gallery;
}
?>

<div class="page-banner associations-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="vie_etudiante.php"><?php echo t('vie_etudiante'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('associations_titre'); ?></span>
        </nav>
        <h1><?php echo t('associations_titre'); ?></h1>
        <p><?php echo t('associations_page_soustitre'); ?></p>
    </div>
</div>

<div class="page-content">
    <div class="background-animation">
        <span class="icon"><i class="fas fa-users"></i></span>
        <span class="icon"><i class="fas fa-handshake"></i></span>
        <span class="icon"><i class="fas fa-trophy"></i></span>
        <span class="icon"><i class="fas fa-scale-balanced"></i></span>
    </div>

    <!-- Bouton de retour (positionné hors du conteneur, complètement à gauche) -->
    <a href="vie_etudiante.php" class="btn-outline btn-back-sticky">
        <i class="fas fa-arrow-left"></i> <?php echo t('retour_vie_etudiante'); ?>
    </a>

    <div class="container">

        <div class="page-logo-header animate-on-scroll">
            <img src="images/aei.jpeg" alt="Logo AEI">
        </div>

        <!-- Carte d'identité de l'association -->
        <div class="aei-id-card animate-on-scroll">
            <h2><?php echo t('aei_carte_identite'); ?></h2>
            <div class="aei-id-grid">
                <div class="aei-id-item">
                    <i class="fas fa-file-signature"></i>
                    <div>
                        <h4><?php echo t('aei_regime_juridique'); ?></h4>
                        <p>Association à but non lucratif — Ordonnance n°60-133 du 03/10/1960</p>
                    </div>
                </div>
                <div class="aei-id-item">
                    <i class="fas fa-location-dot"></i>
                    <div>
                        <h4><?php echo t('aei_siege_social'); ?></h4>
                        <p>ISSTM, Majunga Be, Commune Urbaine Mahajanga-I</p>
                    </div>
                </div>
                <div class="aei-id-item">
                    <i class="fas fa-infinity"></i>
                    <div>
                        <h4><?php echo t('aei_duree'); ?></h4>
                        <p><?php echo t('aei_duree_valeur'); ?></p>
                    </div>
                </div>
                <div class="aei-id-item">
                    <i class="fas fa-bullseye"></i>
                    <div>
                        <h4><?php echo t('aei_but'); ?></h4>
                        <p><?php echo t('aei_but_valeur'); ?></p>
                    </div>
                </div>
            </div>
            <div class="aei-id-footer">
                <i class="fas fa-award"></i>
                <div>
                    <h4><?php echo t('aei_membres_honneur'); ?></h4>
                    <p><?php echo t('aei_membres_honneur_valeur'); ?></p>
                </div>
            </div>
        </div>

        <h2 class="statuts-section-title animate-on-scroll"><?php echo t('statuts_aei_titre'); ?></h2>
        <button type="button" id="statuts-toggle-all" class="statuts-section-hint animate-on-scroll">
            <i class="fas fa-hand-pointer"></i> <span><?php echo t('aei_lire_statuts_complets'); ?></span>
        </button>

        <div class="statuts-accordion">

            <!-- Titre I -->
            <div class="accordion-item">
                <button type="button" class="accordion-header">
                    <span>Titre I — Dénomination, Siège social, Durée</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="accordion-content">
                    <div class="bloc-details">
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 1</span>
                            <div class="statut-article-body">
                                <p>Il est créé à Mahajanga une association à but non lucratif régie par l'ordonnance n°60-133 du 03 Octobre 1960 dénommée : A.E.I « Association des Étudiants de l'ISSTM ».</p>
                            </div>
                        </div>
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 2</span>
                            <div class="statut-article-body">
                                <h4>Siège social</h4>
                                <p>Son siège social est fixé à l'ISSTM, Majunga Be. Ce siège pourra être transféré à tout autre endroit de la ville ou de la région sur décision de l'Assemblée Générale (AG). L'association est ouverte à tout étudiant de l'ISSTM. Tout membre est tenu de promouvoir les actions pour l'intérêt de l'association et en conformité à son objet principal.</p>
                            </div>
                        </div>
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 3</span>
                            <div class="statut-article-body">
                                <h4>Durée</h4>
                                <p>La durée de l'association est illimitée. Sa dissolution peut être décidée par l'Assemblée Générale extraordinaire comme prévu à l'article 19 du présent statut.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Titre II -->
            <div class="accordion-item">
                <button type="button" class="accordion-header">
                    <span>Titre II — But et Objectifs</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="accordion-content">
                    <div class="bloc-details">
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 4</span>
                            <div class="statut-article-body">
                                <h4>But</h4>
                                <p>L'association a pour but de rassembler et d'unir tous les étudiants de l'ISSTM.</p>
                            </div>
                        </div>
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 5</span>
                            <div class="statut-article-body">
                                <h4>Objectifs</h4>
                                <p>L'association a pour objectif :</p>
                                <ul class="styled-list">
                                    <li>La fraternité, l'unité et l'entraide dans les situations difficiles</li>
                                    <li>Le social des étudiants</li>
                                    <li>Les cercles d'étude</li>
                                    <li>Les activités et divertissements (sport, jeu, …)</li>
                                    <li>La protection de l'environnement</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Titre III -->
            <div class="accordion-item">
                <button type="button" class="accordion-header">
                    <span>Titre III — Adhésion, Démission, Radiation des Membres</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="accordion-content">
                    <div class="bloc-details">
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 6</span>
                            <div class="statut-article-body">
                                <h4>Adhésion et Admission</h4>
                                <p>Tous les étudiants de l'ISSTM sont membres de l'Association. L'adhésion se fait sans distinction d'origine, de genre et de conviction politique ou religieuse. Le seul critère pour adhérer à l'association est d'être étudiant inscrit à l'ISSTM.</p>
                            </div>
                        </div>
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 7</span>
                            <div class="statut-article-body">
                                <h4>Démission</h4>
                                <p>La qualité de membre se perd soit par :</p>
                                <ul class="styled-list">
                                    <li>Démission volontaire</li>
                                    <li>Décès</li>
                                </ul>
                            </div>
                        </div>
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 8</span>
                            <div class="statut-article-body">
                                <h4>Composition des membres</h4>
                                <ul class="styled-list">
                                    <li><strong>Membres fondateurs :</strong> ceux qui ont participé à la création de l'association.</li>
                                    <li><strong>Membres actifs :</strong> membre ayant payé le droit d'adhésion et les cotisations annuelles.</li>
                                    <li><strong>Membres d'honneur :</strong> parrains de l'association (le Directeur de l'ISSTM et le Responsable Pédagogique ou Directeur Adjoint de l'ISSTM).</li>
                                    <li><strong>Membres bienfaiteurs :</strong> ceux qui ont aidé l'association par des moyens matériels, moraux ou financiers.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 9</span>
                            <div class="statut-article-body">
                                <h4>Radiation</h4>
                                <p>L'exclusion est prononcée et décidée par l'Assemblée Générale. Tout membre peut être exclu ou suspendu pour une violation grave aux principes, aux règlements et à la discipline. Toutefois, aucune sanction ne peut être prononcée sans que l'intéressé n'use de son droit à la défense.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Titre IV -->
            <div class="accordion-item">
                <button type="button" class="accordion-header">
                    <span>Titre IV — Administration et Fonctionnement</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="accordion-content">
                    <div class="bloc-details">
                        <p>L'association a deux (2) organes : l'<strong>Assemblée Générale</strong> et le <strong>Bureau Exécutif</strong>.</p>

                        <div class="statut-article">
                            <span class="statut-article-num">Art. 10</span>
                            <div class="statut-article-body">
                                <h4>Assemblée Générale</h4>
                                <p>L'Assemblée Générale (AG) est l'instance suprême de l'association. Elle est formée par la réunion de tous les membres actifs. Sur convocation du bureau, l'Assemblée Générale ordinaire se réunit une (01) fois par semestre. Toutes les décisions prises par le bureau administratif sont portées devant l'Assemblée Générale.</p>
                                <p>Elle a pour attribution d'élaborer le programme d'activités de l'Association et de faire le rapport du budget soumis par le bureau exécutif.</p>
                            </div>
                        </div>
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 11</span>
                            <div class="statut-article-body">
                                <h4>Assemblée Ordinaire des membres de bureau</h4>
                                <p>L'assemblée ordinaire ne peut valablement délibérer qu'à la majorité des deux tiers de ses membres de bureau. En cas de partage égal des voix, celle du président est prépondérante. Si le quorum n'est pas atteint lors de la première réunion, les membres sont convoqués à une deuxième réunion et la décision prise sera valable quel que soit le nombre de présents. Les convocations se font 15 jours avant la réunion, et un rappel téléphonique une semaine avant.</p>
                            </div>
                        </div>
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 12</span>
                            <div class="statut-article-body">
                                <h4>Assemblée Générale Extraordinaire</h4>
                                <p>Une Assemblée Générale Extraordinaire se tient sur la demande des 2/3 des membres. Toute décision de l'Assemblée Générale n'est révocable que par une nouvelle Assemblée Générale. Le Président peut convoquer les membres pour une réunion.</p>
                            </div>
                        </div>
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 13</span>
                            <div class="statut-article-body">
                                <h4><?php echo t('aei_bureau_execution_titre'); ?></h4>
                                <div class="bureau-grid">
                                    <?php foreach ($bureau_roles as $poste): ?>
                                        <div class="bureau-card">
                                            <div class="bureau-role">
                                                <span><i class="fas <?php echo $poste['icon']; ?>"></i> <?php echo htmlspecialchars($poste['role']); ?></span>
                                                <span class="bureau-count"><?php echo $poste['count']; ?></span>
                                            </div>
                                            <p class="bureau-critere"><strong>Critère :</strong> <?php echo htmlspecialchars($poste['critere']); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <p>L'association est administrée par un bureau dont les membres sont élus au vote public pour un an par l'Assemblée Générale. Ils sont rééligibles et exercent leurs pouvoirs les plus étendus par délégation de l'Assemblée Générale dans tous les domaines d'activités de l'association, après passation.</p>
                                <p><strong>Ses attributions sont :</strong></p>
                                <ul class="styled-list">
                                    <li>Élaborer les stratégies, le plan d'action et trouver des moyens pour atteindre les objectifs assignés.</li>
                                    <li>Administrer et gérer le fonds et le patrimoine de l'association.</li>
                                    <li>Approuver et cautionner la gestion des ressources humaines, matérielles et financières.</li>
                                    <li>Défendre l'intérêt de l'association et la représenter au niveau régional, national ou international.</li>
                                    <li>Prendre en cas d'urgence toutes les décisions et mesures qu'exigent les circonstances sur des problèmes exceptionnels, à charge d'en rendre compte à l'Assemblée Générale.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 14</span>
                            <div class="statut-article-body">
                                <h4>Fonctionnement</h4>
                                <p>Les fonctions au sein de l'association « A.E.I » sont gratuites. Néanmoins, les membres peuvent être remboursés des frais qu'ils ont engagés à l'occasion de missions et services effectués pour le compte de l'association.</p>
                            </div>
                        </div>
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 15</span>
                            <div class="statut-article-body">
                                <h4>Attribution des tâches des membres de bureau</h4>
                                <ul class="styled-list">
                                    <li><strong>Président :</strong> porte-parole de l'Association ; préside et convoque les réunions de l'Assemblée Générale et du bureau exécutif ; représente de plein droit l'association devant la justice.</li>
                                    <li><strong>Vice-Président :</strong> assiste le président ; le remplace en cas d'empêchement ou d'absence.</li>
                                    <li><strong>Secrétaire Général :</strong> prépare l'ordre du jour des réunions avec le Président ; rédige les procès-verbaux ; assure les affaires administratives.</li>
                                    <li><strong>Trésorier :</strong> tient le livre de compte des recettes et dépenses ; rédige les rapports financiers annuels ; gère les finances.</li>
                                    <li><strong>Commissaire au Compte :</strong> contrôle planifié et inopiné de la gestion financière ; vérifie la sincérité et la concordance des comptes annuels.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Titre V -->
            <div class="accordion-item">
                <button type="button" class="accordion-header">
                    <span>Titre V — Ressources et Gestion Financière</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="accordion-content">
                    <div class="bloc-details">
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 16</span>
                            <div class="statut-article-body">
                                <p>Les ressources de l'association proviennent :</p>
                                <ul class="styled-list">
                                    <li>Du droit d'adhésion des membres</li>
                                    <li>De la cotisation annuelle</li>
                                    <li>Des donations octroyées par des personnes physiques ou morales</li>
                                    <li>Des produits de manifestations diverses</li>
                                    <li>Des subventions publiques conformes aux lois en vigueur</li>
                                    <li>Des intérêts ou produits des biens et capitaux appartenant à l'association</li>
                                    <li>Des recettes diverses et bénéfices réalisés à l'occasion des activités de l'association</li>
                                </ul>
                            </div>
                        </div>
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 17</span>
                            <div class="statut-article-body">
                                <h4>Dépenses</h4>
                                <p>Toutes les pièces justificatives des dépenses ou des sorties de matériel doivent être signées par le trésorier et visées par le Secrétaire Général et le Président. Il en est de même pour l'émission des chèques bancaires, que ce soit pour un paiement ou un retrait.</p>
                            </div>
                        </div>
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 18</span>
                            <div class="statut-article-body">
                                <p>Le compte sera ouvert au nom de l'association A.E.I et les signataires sont le Président, le Trésorier et le Secrétaire Général de l'association.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Titre VI -->
            <div class="accordion-item">
                <button type="button" class="accordion-header">
                    <span>Titre VI — Modification de Statuts et Dissolution</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="accordion-content">
                    <div class="bloc-details">
                        <div class="statut-article">
                            <span class="statut-article-num">Art. 19</span>
                            <div class="statut-article-body">
                                <h4>Modification de statuts</h4>
                                <p>Les présents statuts ne peuvent être modifiés, et la dissolution ne peut être prononcée, que par l'Assemblée Générale de l'association, sur proposition des membres de bureau expressément convoqués à cet effet, ou à la demande de trois quarts (3/4) des membres de l'Assemblée Générale.</p>
                            </div>
                        </div>
                        <p class="statut-signature">Fait à Mahajanga, le <span class="statut-signature-line"></span></p>
                        <div class="statut-signatures">
                            <span>Le Secrétaire Général</span>
                            <span>Le Président</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Déclaration de Constitution -->
        <div class="aei-declaration animate-on-scroll">
            <i class="fas fa-stamp aei-declaration-seal"></i>
            <h2><?php echo t('aei_declaration_titre'); ?></h2>
            <p><?php echo t('aei_declaration_texte'); ?></p>
        </div>

        <!-- Bureau Fondateur -->
        <h2 class="statuts-section-title animate-on-scroll"><?php echo t('aei_bureau_fondateur_titre'); ?></h2>
        <div class="pv-grid">
            <?php foreach ($bureau_fondateur as $membre): ?>
                <div class="pv-card animate-on-scroll">
                    <div class="pv-avatar"><i class="fas fa-user"></i></div>
                    <h4><?php echo htmlspecialchars($membre['nom']); ?></h4>
                    <p><?php echo htmlspecialchars($membre['role']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Galerie Photos & Média -->
        <section class="photo-gallery-section">
            <h2 class="section-title-centered animate-on-scroll"><?php echo t('galeries_photos_media'); ?></h2>

            <div class="gallery-filter-buttons animate-on-scroll">
                <button type="button" class="gallery-filter-btn is-active" data-filter="all"><?php echo t('galerie_filtre_tous'); ?></button>
                <button type="button" class="gallery-filter-btn" data-filter="vie"><?php echo t('galerie_filtre_vie_associative'); ?></button>
                <button type="button" class="gallery-filter-btn" data-filter="campus"><?php echo t('galerie_filtre_campus'); ?></button>
            </div>

            <div class="gallery-grid">
                <?php foreach ($galerie_items as $i => $item): ?>
                    <div class="gallery-item" data-category="<?php echo $item['cat']; ?>" style="transition-delay: <?php echo $i * 0.08; ?>s;">
                        <img src="<?php echo $item['img']; ?>" alt="<?php echo htmlspecialchars($item['alt']); ?>" loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
                        <div class="gallery-item-overlay">
                            <i class="fas fa-magnifying-glass-plus"></i>
                            <span><?php echo htmlspecialchars($item['alt']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </div>
</div>

<script>
const AEI_LABEL_EXPAND = <?php echo json_encode(t('aei_lire_statuts_complets')); ?>;
const AEI_LABEL_COLLAPSE = <?php echo json_encode(t('aei_reduire_statuts')); ?>;

document.addEventListener('DOMContentLoaded', () => {
    // --- Accordéon des statuts ---
    document.querySelectorAll('.statuts-accordion .accordion-header').forEach(header => {
        const item = header.closest('.accordion-item');
        const content = header.nextElementSibling;

        header.addEventListener('click', () => {
            const isActive = item.classList.contains('active');

            document.querySelectorAll('.statuts-accordion .accordion-item.active').forEach(openItem => {
                if (openItem !== item) {
                    openItem.classList.remove('active');
                    openItem.querySelector('.accordion-content').style.maxHeight = null;
                }
            });

            if (isActive) {
                item.classList.remove('active');
                content.style.maxHeight = null;
            } else {
                item.classList.add('active');
                content.style.maxHeight = content.scrollHeight + 'px';
            }
        });
    });

    // --- Bouton "Lire les statuts complets" : ouvre/ferme tous les Titres d'un coup ---
    const toggleAllBtn = document.getElementById('statuts-toggle-all');
    const accordionItems = document.querySelectorAll('.statuts-accordion .accordion-item');
    if (toggleAllBtn && accordionItems.length > 0) {
        const labelSpan = toggleAllBtn.querySelector('span');
        toggleAllBtn.addEventListener('click', () => {
            const shouldOpen = toggleAllBtn.dataset.state !== 'open';
            accordionItems.forEach(item => {
                const content = item.querySelector('.accordion-content');
                if (shouldOpen) {
                    item.classList.add('active');
                    content.style.maxHeight = content.scrollHeight + 'px';
                } else {
                    item.classList.remove('active');
                    content.style.maxHeight = null;
                }
            });
            toggleAllBtn.dataset.state = shouldOpen ? 'open' : 'closed';
            labelSpan.textContent = shouldOpen ? AEI_LABEL_COLLAPSE : AEI_LABEL_EXPAND;
        });
    }

    // --- Filtre de la galerie ---
    const filterButtons = document.querySelectorAll('.gallery-filter-btn');
    const galleryItems = document.querySelectorAll('.gallery-grid .gallery-item');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            filterButtons.forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            const filter = btn.dataset.filter;
            galleryItems.forEach(item => {
                const show = filter === 'all' || item.dataset.category === filter;
                item.classList.toggle('is-hidden', !show);
            });
        });
    });
});
</script>

<?php include 'footer.php'; ?>
