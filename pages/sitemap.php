<?php
header('Content-Type: application/xml; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';

$db = Database::getConnection();
$villes = $db->query('SELECT ville, updated_at FROM villes_prix ORDER BY updated_at DESC')->fetchAll(PDO::FETCH_ASSOC);

$staticUrls = [
    ['loc' => SITE_URL . '/', 'lastmod' => date('Y-m-d')],
    ['loc' => SITE_URL . '/prix-m2', 'lastmod' => date('Y-m-d')],
];

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php foreach ($staticUrls as $url): ?>
    <url>
        <loc><?= htmlspecialchars($url['loc'], ENT_QUOTES, 'UTF-8') ?></loc>
        <lastmod><?= htmlspecialchars($url['lastmod'], ENT_QUOTES, 'UTF-8') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <?php endforeach; ?>

    <?php foreach ($villes as $ville): ?>
    <?php
    $slug = strtolower(str_replace(' ', '-', $ville['ville']));
    $updatedAt = !empty($ville['updated_at']) ? date('Y-m-d', strtotime($ville['updated_at'])) : date('Y-m-d');
    ?>
    <url>
        <loc><?= htmlspecialchars(SITE_URL . '/prix-m2/' . $slug, ENT_QUOTES, 'UTF-8') ?></loc>
        <lastmod><?= htmlspecialchars($updatedAt, ENT_QUOTES, 'UTF-8') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
</urlset>
