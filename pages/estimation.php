<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../../site-specific/config/site.php';
require_once __DIR__ . '/../classes/SEO/KeywordManager.php';
require_once __DIR__ . '/../classes/SEO/PageGenerator.php';
require_once __DIR__ . '/../classes/SEO/SchemaMarkup.php';

$typeBien = (string) ($_GET['type'] ?? 'appartement');
$ville = (string) ($_GET['ville'] ?? CITY_NAME);
$quartier = isset($_GET['quartier']) ? (string) $_GET['quartier'] : null;

$siteConfig = require __DIR__ . '/../../site-specific/config/site.php';

$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$keywordManager = new KeywordManager($pdo, $siteConfig);
$pageGenerator = new PageGenerator($keywordManager, __DIR__ . '/../templates');
$schemaMarkup = new SchemaMarkup();

try {
    $content = $pageGenerator->generateEstimationPage($typeBien, $ville, $quartier);

    $keywords = $keywordManager->getKeywordsForPage($typeBien, $ville, $quartier);
    $primaryKeyword = isset($keywords[0]['keyword']) ? (string) $keywords[0]['keyword'] : ('estimation ' . $typeBien . ' ' . $ville);

    $pageData = [
        'title' => $keywordManager->generateTitle($typeBien, $ville, $quartier),
        'meta_description' => 'Obtenez une estimation gratuite de votre bien en 30 secondes.',
        'canonical' => rtrim(BASE_URL, '/') . '/estimation/' . rawurlencode($typeBien) . '/' . rawurlencode($ville) . '/' . ($quartier ? rawurlencode($quartier) . '/' : ''),
        'keywords' => array_column($keywords, 'keyword'),
    ];

    $schemaBlocks = [
        $schemaMarkup->buildLocalBusiness([
            'zone_label' => $quartier ? ($quartier . ', ' . $ville) : $ville,
            'meta_description' => $pageData['meta_description'],
        ]),
        $schemaMarkup->buildFaqPage([
            ['question' => 'Comment est calculée mon estimation ?', 'answer' => 'À partir des données DVF, de comparables et de signaux de marché local.'],
            ['question' => 'Dois-je m\'engager pour obtenir le prix ?', 'answer' => 'Non, l\'estimation initiale est gratuite et sans engagement.'],
        ]),
    ];

    ob_start();
    require __DIR__ . '/../templates/seo/meta.php';
    $metaTags = (string) ob_get_clean();

    ob_start();
    require __DIR__ . '/../templates/seo/schema.php';
    $schemaTags = (string) ob_get_clean();

    $content = preg_replace('/<\/head>/', $metaTags . PHP_EOL . $schemaTags . PHP_EOL . '</head>', $content, 1);
    $content = str_replace('{{PRIMARY_KEYWORD}}', $primaryKeyword, (string) $content);

    echo $content;
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Erreur de génération SEO</h1>';
    echo '<p>La page n\'a pas pu être générée automatiquement.</p>';
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '</pre>';
    }
}
