<?php
// Plan de site XML généré dynamiquement : reprend les pages statiques ainsi que
// les fiches filières / articles / albums publiés en base, afin de rester à
// jour automatiquement sans édition manuelle à chaque ajout de contenu.
require_once 'db_connect.php';

header('Content-Type: application/xml; charset=utf-8');

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

$urls = [];

// --- Pages statiques ---
$static_pages = [
    ['loc' => 'index.php', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => 'historique.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => 'parcours.php', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => 'filieres.php', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => 'enseignants.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => 'vie_etudiante.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => 'associations.php', 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['loc' => 'campus.php', 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['loc' => 'bourse.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => 'inscription.php', 'priority' => '0.9', 'changefreq' => 'monthly'],
    ['loc' => 'galerie.php', 'priority' => '0.6', 'changefreq' => 'weekly'],
    ['loc' => 'actualite.php', 'priority' => '0.8', 'changefreq' => 'daily'],
    ['loc' => 'confidentialite.php', 'priority' => '0.2', 'changefreq' => 'yearly'],
    ['loc' => 'mentions_legales.php', 'priority' => '0.2', 'changefreq' => 'yearly'],
];
foreach ($static_pages as $p) {
    $urls[] = ['loc' => $base_url . '/' . $p['loc'], 'priority' => $p['priority'], 'changefreq' => $p['changefreq'], 'lastmod' => null];
}

// --- Filières ---
if ($mysqli->query("SHOW TABLES LIKE 'filieres'")->num_rows > 0) {
    $res = $mysqli->query("SELECT slug, created_at FROM filieres");
    while ($row = $res->fetch_assoc()) {
        $urls[] = ['loc' => $base_url . '/filiere_detail.php?slug=' . urlencode($row['slug']), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $row['created_at']];
    }
}

// --- Articles publiés ---
if ($mysqli->query("SHOW TABLES LIKE 'news_articles'")->num_rows > 0) {
    $res = $mysqli->query("SELECT slug, published_at FROM news_articles WHERE status = 'publie' AND (published_at IS NULL OR published_at <= NOW())");
    while ($row = $res->fetch_assoc()) {
        $urls[] = ['loc' => $base_url . '/actualite_article.php?slug=' . urlencode($row['slug']), 'priority' => '0.6', 'changefreq' => 'weekly', 'lastmod' => $row['published_at']];
    }
}

// --- Albums publiés ---
if ($mysqli->query("SHOW TABLES LIKE 'gallery_albums'")->num_rows > 0) {
    $res = $mysqli->query("SELECT id, created_at FROM gallery_albums WHERE status = 'publie'");
    while ($row = $res->fetch_assoc()) {
        $urls[] = ['loc' => $base_url . '/galerie_album.php?id=' . (int) $row['id'], 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $row['created_at']];
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($u['loc']) . "</loc>\n";
    if (!empty($u['lastmod'])) {
        echo "    <lastmod>" . date('Y-m-d', strtotime($u['lastmod'])) . "</lastmod>\n";
    }
    echo "    <changefreq>" . $u['changefreq'] . "</changefreq>\n";
    echo "    <priority>" . $u['priority'] . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>';
