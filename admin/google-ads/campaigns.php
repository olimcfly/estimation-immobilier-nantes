<?php
$pageTitle = 'Google Ads - Campagnes';
$currentPage = 'google-ads';
$topNavCurrent = 'google-ads';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/_bootstrap.php';

$db = Database::getConnection();
$strategies = [];
$villes = [];
$campaigns = [];

try {
    $strategies = gaFetchStrategies($db, true);
    $villes = gaFetchCities($db);

    $sql = "
        SELECT c.id, c.strategy_id, c.city_id, c.awareness_level, c.budget_percent, c.is_active,
               s.name AS strategy_name, s.color AS strategy_color,
               v.ville AS city_name,
               COUNT(l.id) AS leads_count,
               COALESCE(SUM(l.conversion_value), 0) AS budget_spent
        FROM google_ads_campaigns c
        INNER JOIN google_ads_strategies s ON s.id = c.strategy_id
        INNER JOIN villes_prix v ON v.id = c.city_id
        LEFT JOIN leads l ON l.campaign_id = c.id
        GROUP BY c.id
        ORDER BY s.name, v.ville, FIELD(c.awareness_level, 'hot', 'warm', 'cold')
    ";
    $stmt = $db->query($sql);
    $campaigns = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Throwable $e) {
    error_log('Erreur campagnes Google Ads: ' . $e->getMessage());
}
?>

<div class="mx-auto max-w-7xl space-y-6">
    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h1 class="mb-4 text-2xl font-bold">📊 Campagnes multi-villes</h1>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left">
                        <th class="p-2">Stratégie</th>
                        <th class="p-2">Ville</th>
                        <th class="p-2">Conscience</th>
                        <th class="p-2">Budget</th>
                        <th class="p-2">Leads</th>
                        <th class="p-2">Dépense</th>
                        <th class="p-2">CPL</th>
                        <th class="p-2">État</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campaigns as $campaign): ?>
                        <?php $cpl = (int) $campaign['leads_count'] > 0 ? (float) $campaign['budget_spent'] / (int) $campaign['leads_count'] : null; ?>
                        <tr class="border-b border-slate-100">
                            <td class="p-2 font-medium" style="color: <?= admin_h($campaign['strategy_color'] ?: '#2563eb') ?>;"><?= admin_h($campaign['strategy_name']) ?></td>
                            <td class="p-2"><?= admin_h($campaign['city_name']) ?></td>
                            <td class="p-2"><span class="rounded px-2 py-1 text-xs text-white" style="background: <?= gaAwarenessColor((string) $campaign['awareness_level']) ?>;"><?= gaAwarenessLabel((string) $campaign['awareness_level']) ?></span></td>
                            <td class="p-2"><?= (int) $campaign['budget_percent'] ?>%</td>
                            <td class="p-2"><?= (int) $campaign['leads_count'] ?></td>
                            <td class="p-2"><?= number_format((float) $campaign['budget_spent'], 2, ',', ' ') ?> €</td>
                            <td class="p-2"><?= $cpl !== null ? number_format($cpl, 2, ',', ' ') . ' €' : 'N/A' ?></td>
                            <td class="p-2"><?= (int) $campaign['is_active'] === 1 ? '✅ Active' : '⏸️ Inactive' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$campaigns): ?>
                        <tr><td class="p-3 text-slate-500" colspan="8">Aucune campagne pour le moment.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-xl font-bold">➕ Créer une campagne</h2>
        <form method="POST" action="/admin/google-ads/campaign-save.php" class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium">Stratégie</label>
                <select name="strategy_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    <option value="">Choisir...</option>
                    <?php foreach ($strategies as $strategy): ?>
                        <option value="<?= (int) $strategy['id'] ?>"><?= admin_h($strategy['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Ville</label>
                <select name="city_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    <option value="">Choisir...</option>
                    <?php foreach ($villes as $ville): ?>
                        <option value="<?= (int) $ville['id'] ?>"><?= admin_h($ville['ville']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Niveau de conscience</label>
                <select name="awareness_level" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    <option value="hot">Chaud</option>
                    <option value="warm">Tiède</option>
                    <option value="cold">Froid</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Budget (%)</label>
                <input type="number" name="budget_percent" min="1" max="100" value="60" class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">Créer la campagne</button>
            </div>
        </form>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
