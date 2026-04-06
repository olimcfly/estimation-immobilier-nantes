<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';

if (!function_exists('clean')) {
    function clean(string $value): string
    {
        return trim(strip_tags($value));
    }
}

$villeSlug = $_GET['ville'] ?? '';
$villeSlug = clean($villeSlug);

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM villes_prix WHERE LOWER(REPLACE(ville, ' ', '-')) = ?");
$stmt->execute([strtolower($villeSlug)]);
$villeData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$villeData) {
    $showIndex = true;
    $villes = $db->query(
        'SELECT * FROM villes_prix WHERE distance_centre <= ' . (int) CITY_RADIUS_KM . ' ORDER BY population DESC'
    )->fetchAll(PDO::FETCH_ASSOC);

    $pageTitle = 'Prix au m² autour de ' . CITY_NAME . ' - ' . SITE_NAME;
    $metaDescription = 'Consultez les prix immobiliers au m² dans ' . count($villes)
        . ' villes autour de ' . CITY_NAME . '. Appartements et maisons. Données actualisées.';
    $canonicalUrl = SITE_URL . '/prix-m2';
} else {
    $showIndex = false;

    $pageTitle = 'Prix au m² à ' . $villeData['ville'] . ' (' . $villeData['code_postal'] . ') - ' . SITE_NAME;
    $metaDescription = 'Prix immobilier à ' . $villeData['ville'] . ' : appartement '
        . number_format((float) $villeData['prix_m2_appartement'], 0, ',', ' ') . ' €/m², maison '
        . number_format((float) $villeData['prix_m2_maison'], 0, ',', ' ') . ' €/m². Estimez votre bien gratuitement.';
    $canonicalUrl = SITE_URL . '/prix-m2/' . strtolower(str_replace(' ', '-', $villeData['ville']));
}

require_once __DIR__ . '/../header.php';
?>
<main>
    <?php if ($showIndex): ?>
    <section class="max-w-5xl mx-auto py-12 px-4">
        <h1 class="text-3xl font-bold text-gray-900">Prix au m² autour de <?= htmlspecialchars(CITY_NAME, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-gray-600 mt-4 max-w-2xl">
            Découvrez les prix immobiliers dans un rayon de <?= (int) CITY_RADIUS_KM ?> km autour de <?= htmlspecialchars(CITY_NAME, ENT_QUOTES, 'UTF-8') ?>.
            Données mises à jour régulièrement.
        </p>

        <div class="mt-6 relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔎</span>
            <input id="ville-search" type="text" placeholder="Rechercher une ville..."
                   class="w-full bg-white border border-gray-200 rounded-xl py-3 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary">
        </div>

        <div id="villes-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
            <?php foreach ($villes as $ville): ?>
            <?php $slug = strtolower(str_replace(' ', '-', $ville['ville'])); ?>
            <a href="/prix-m2/<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>"
               data-ville="<?= htmlspecialchars(strtolower($ville['ville']), ENT_QUOTES, 'UTF-8') ?>"
               class="ville-card block bg-white rounded-xl border border-gray-200 p-6 hover:shadow-lg hover:border-primary transition group">
                <div class="flex justify-between items-start gap-4">
                    <div>
                        <h2 class="font-bold text-lg text-gray-900 group-hover:text-primary"><?= htmlspecialchars($ville['ville'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($ville['code_postal'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <span class="bg-blue-50 text-primary px-3 py-1 rounded-full text-sm font-semibold whitespace-nowrap">
                        <?= number_format((float) $ville['prix_m2_appartement'], 0, ',', ' ') ?> €/m²
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-gray-500">Appartement</span>
                        <p class="font-semibold"><?= number_format((float) $ville['prix_m2_appartement'], 0, ',', ' ') ?> €/m²</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Maison</span>
                        <p class="font-semibold"><?= number_format((float) $ville['prix_m2_maison'], 0, ',', ' ') ?> €/m²</p>
                    </div>
                </div>

                <div class="mt-3 flex items-center gap-1 text-sm">
                    <?php if ((float) $ville['tendance_annuelle'] > 0): ?>
                        <span class="text-green-600">📈 +<?= (float) $ville['tendance_annuelle'] ?>%</span>
                        <span class="text-gray-400">sur 1 an</span>
                    <?php elseif ((float) $ville['tendance_annuelle'] < 0): ?>
                        <span class="text-red-600">📉 <?= (float) $ville['tendance_annuelle'] ?>%</span>
                        <span class="text-gray-400">sur 1 an</span>
                    <?php else: ?>
                        <span class="text-gray-500">➡️ Stable</span>
                    <?php endif; ?>
                </div>

                <p class="mt-4 text-primary text-sm font-medium group-hover:underline">Voir le détail →</p>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <script>
        const input = document.getElementById('ville-search');
        const cards = [...document.querySelectorAll('.ville-card')];

        input?.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();
            cards.forEach(card => {
                const name = card.dataset.ville || '';
                card.classList.toggle('hidden', term && !name.includes(term));
            });
        });
    </script>

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ItemList",
      "name": "Prix immobilier autour de <?= addslashes(CITY_NAME) ?>",
      "numberOfItems": <?= count($villes) ?>,
      "itemListElement": [
        <?php foreach ($villes as $i => $v): ?>
        {
          "@type": "ListItem",
          "position": <?= $i + 1 ?>,
          "name": "Prix m² <?= addslashes($v['ville']) ?>",
          "url": "<?= SITE_URL ?>/prix-m2/<?= strtolower(str_replace(' ', '-', $v['ville'])) ?>"
        }<?= $i < count($villes) - 1 ? ',' : '' ?>
        <?php endforeach; ?>
      ]
    }
    </script>
    <?php else: ?>
    <?php
    $villesProchesStmt = $db->prepare(
        'SELECT * FROM villes_prix
         WHERE ville != ? AND distance_centre <= ?
         ORDER BY ABS(latitude - ?) + ABS(longitude - ?) ASC
         LIMIT 6'
    );
    $villesProchesStmt->execute([
        $villeData['ville'],
        CITY_RADIUS_KM,
        $villeData['latitude'],
        $villeData['longitude'],
    ]);
    $villesProches = $villesProchesStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <section class="max-w-4xl mx-auto py-12 px-4">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="/" class="hover:text-primary">Accueil</a> /
            <a href="/pages/prix-m2.php" class="hover:text-primary">Prix au m²</a> /
            <span class="text-gray-900"><?= htmlspecialchars($villeData['ville'], ENT_QUOTES, 'UTF-8') ?></span>
        </nav>

        <h1 class="text-3xl font-bold text-gray-900">Prix immobilier à <?= htmlspecialchars($villeData['ville'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($villeData['code_postal'], ENT_QUOTES, 'UTF-8') ?>)</h1>

        <p class="text-gray-700 mt-4">
            Le prix moyen au m² à <?= htmlspecialchars($villeData['ville'], ENT_QUOTES, 'UTF-8') ?> est de
            <strong><?= number_format((float) $villeData['prix_m2_appartement'], 0, ',', ' ') ?> €/m²</strong>
            pour un appartement et
            <strong><?= number_format((float) $villeData['prix_m2_maison'], 0, ',', ' ') ?> €/m²</strong>
            pour une maison.
            <?php if ((float) $villeData['tendance_annuelle'] > 0): ?>
                Les prix sont en hausse de <?= (float) $villeData['tendance_annuelle'] ?>% sur un an.
            <?php elseif ((float) $villeData['tendance_annuelle'] < 0): ?>
                Les prix sont en baisse de <?= abs((float) $villeData['tendance_annuelle']) ?>% sur un an.
            <?php else: ?>
                Les prix sont stables par rapport à l'année dernière.
            <?php endif; ?>
        </p>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border p-6">
                <h2 class="font-semibold text-gray-700">🏢 Appartement</h2>
                <p class="mt-2 text-2xl font-bold"><?= number_format((float) $villeData['prix_m2_appartement'], 0, ',', ' ') ?> €/m²</p>
                <p class="text-sm mt-1 <?= (float) $villeData['tendance_annuelle'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                    Tendance : <?= (float) $villeData['tendance_annuelle'] >= 0 ? '+' : '' ?><?= (float) $villeData['tendance_annuelle'] ?>% <?= (float) $villeData['tendance_annuelle'] >= 0 ? '📈' : '📉' ?>
                </p>
            </div>
            <div class="bg-white rounded-xl border p-6">
                <h2 class="font-semibold text-gray-700">🏡 Maison</h2>
                <p class="mt-2 text-2xl font-bold"><?= number_format((float) $villeData['prix_m2_maison'], 0, ',', ' ') ?> €/m²</p>
                <p class="text-sm mt-1 <?= (float) $villeData['tendance_annuelle'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                    Tendance : <?= (float) $villeData['tendance_annuelle'] >= 0 ? '+' : '' ?><?= (float) $villeData['tendance_annuelle'] ?>% <?= (float) $villeData['tendance_annuelle'] >= 0 ? '📈' : '📉' ?>
                </p>
            </div>
            <div class="bg-white rounded-xl border p-6">
                <h2 class="font-semibold text-gray-700">🏗️ Terrain</h2>
                <p class="mt-2 text-2xl font-bold">
                    <?= isset($villeData['prix_m2_terrain']) && $villeData['prix_m2_terrain'] !== null
                        ? number_format((float) $villeData['prix_m2_terrain'], 0, ',', ' ') . ' €/m²'
                        : 'Non disponible' ?>
                </p>
            </div>
        </div>

        <div class="mt-8 bg-white rounded-xl border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-3">Type de bien</th>
                        <th class="text-left p-3">Prix moyen m²</th>
                        <th class="text-left p-3">Fourchette basse</th>
                        <th class="text-left p-3">Fourchette haute</th>
                        <th class="text-left p-3">Tendance 1 an</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $base = (float) $villeData['prix_m2_appartement'];
                    $maison = (float) $villeData['prix_m2_maison'];
                    $trend = (float) $villeData['tendance_annuelle'];
                    ?>
                    <tr class="border-t">
                        <td class="p-3">Studio / T1</td>
                        <td class="p-3"><?= number_format($base * 1.1, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= number_format($base * 0.95, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= number_format($base * 1.2, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= $trend >= 0 ? '+' : '' ?><?= $trend ?>%</td>
                    </tr>
                    <tr class="border-t">
                        <td class="p-3">T2 / T3</td>
                        <td class="p-3"><?= number_format($base, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= number_format($base * 0.9, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= number_format($base * 1.1, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= $trend >= 0 ? '+' : '' ?><?= $trend ?>%</td>
                    </tr>
                    <tr class="border-t">
                        <td class="p-3">T4 et +</td>
                        <td class="p-3"><?= number_format($base * 0.9, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= number_format($base * 0.8, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= number_format($base, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= $trend >= 0 ? '+' : '' ?><?= $trend ?>%</td>
                    </tr>
                    <tr class="border-t">
                        <td class="p-3">Maison 3 pièces</td>
                        <td class="p-3"><?= number_format($maison * 0.95, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= number_format($maison * 0.85, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= number_format($maison * 1.05, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= $trend >= 0 ? '+' : '' ?><?= $trend ?>%</td>
                    </tr>
                    <tr class="border-t">
                        <td class="p-3">Maison 5 pièces +</td>
                        <td class="p-3"><?= number_format($maison * 1.05, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= number_format($maison * 0.95, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= number_format($maison * 1.2, 0, ',', ' ') ?> €</td>
                        <td class="p-3"><?= $trend >= 0 ? '+' : '' ?><?= $trend ?>%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <section class="mt-10 bg-gradient-to-r from-primary to-blue-700 text-white rounded-2xl p-8">
            <h2 class="text-2xl font-bold">Estimez votre bien à <?= htmlspecialchars($villeData['ville'], ENT_QUOTES, 'UTF-8') ?> gratuitement</h2>
            <p class="mt-2">Obtenez une estimation précise en 2 minutes, basée sur les données du marché local.</p>
            <a href="/?ville=<?= urlencode($villeData['ville']) ?>"
               class="mt-4 inline-block bg-white text-primary px-8 py-3 rounded-xl font-bold hover:bg-gray-100 transition">
                Estimer mon bien →
            </a>
        </section>

        <section class="mt-10">
            <h2 class="text-2xl font-bold text-gray-900">Villes proches de <?= htmlspecialchars($villeData['ville'], ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                <?php foreach ($villesProches as $ville): ?>
                <?php $slug = strtolower(str_replace(' ', '-', $ville['ville'])); ?>
                <a href="/prix-m2/<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>"
                   class="block bg-white rounded-xl border border-gray-200 p-6 hover:shadow-lg hover:border-primary transition group">
                    <h3 class="font-bold text-lg text-gray-900 group-hover:text-primary"><?= htmlspecialchars($ville['ville'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($ville['code_postal'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="mt-3 font-semibold"><?= number_format((float) $ville['prix_m2_appartement'], 0, ',', ' ') ?> €/m²</p>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
    </section>

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Place",
      "name": "<?= addslashes($villeData['ville']) ?>",
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": <?= (float) $villeData['latitude'] ?>,
        "longitude": <?= (float) $villeData['longitude'] ?>
      },
      "containedInPlace": {
        "@type": "AdministrativeArea",
        "name": "<?= addslashes(CITY_NAME) ?>"
      }
    }
    </script>

    <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../footer.php'; ?>
