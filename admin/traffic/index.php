<?php
$pageTitle = 'Trafic & Publicité';
$currentPage = 'traffic';
$topNavCurrent = 'traffic';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$db = Database::getConnection();
$leadsDaily = [];
$budgetByAwareness = [];
$campaigns = [];
$totals = ['leads' => 0, 'budget' => 0.0, 'converted' => 0];

try {
    $stmt = $db->query("SELECT DATE(created_at) AS day, COUNT(*) AS count FROM leads WHERE source = 'google_ads' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day");
    $leadsDaily = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $stmt = $db->query("SELECT c.awareness_level, COALESCE(SUM(l.conversion_value), 0) AS budget_spent FROM google_ads_campaigns c LEFT JOIN leads l ON l.campaign_id = c.id AND l.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) WHERE c.is_active = 1 GROUP BY c.awareness_level");
    $budgetByAwareness = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $stmt = $db->query("SELECT s.name AS strategy_name, v.ville AS city_name, c.awareness_level, COUNT(l.id) AS leads_count, COALESCE(SUM(l.conversion_value),0) AS budget_spent, COALESCE(SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END), 0) AS converted_count FROM google_ads_campaigns c INNER JOIN google_ads_strategies s ON s.id = c.strategy_id INNER JOIN villes_prix v ON v.id = c.city_id LEFT JOIN leads l ON l.campaign_id = c.id AND l.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) WHERE c.is_active = 1 GROUP BY c.id ORDER BY leads_count DESC, budget_spent DESC");
    $campaigns = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    foreach ($campaigns as $campaign) {
        $totals['leads'] += (int) $campaign['leads_count'];
        $totals['budget'] += (float) $campaign['budget_spent'];
        $totals['converted'] += (int) $campaign['converted_count'];
    }
} catch (Throwable $e) {
    error_log('Erreur trafic/publicité: ' . $e->getMessage());
}

$cpl = $totals['leads'] > 0 ? $totals['budget'] / $totals['leads'] : 0.0;
$conversionRate = $totals['leads'] > 0 ? ($totals['converted'] * 100) / $totals['leads'] : 0.0;
?>

<div class="mx-auto max-w-7xl space-y-6">
    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold">📊 Trafic & Publicité</h1>
            <a href="/admin/traffic/export.php" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-300">📥 Export CSV</a>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 p-4"><div class="text-2xl font-bold"><?= (int) $totals['leads'] ?></div><div class="text-sm text-slate-600">Leads (30j)</div></div>
            <div class="rounded-xl border border-slate-200 p-4"><div class="text-2xl font-bold"><?= number_format($cpl, 2, ',', ' ') ?> €</div><div class="text-sm text-slate-600">CPL moyen</div></div>
            <div class="rounded-xl border border-slate-200 p-4"><div class="text-2xl font-bold"><?= number_format($conversionRate, 1, ',', ' ') ?>%</div><div class="text-sm text-slate-600">Taux conversion</div></div>
            <div class="rounded-xl border border-slate-200 p-4"><div class="text-2xl font-bold"><?= number_format($totals['budget'], 2, ',', ' ') ?> €</div><div class="text-sm text-slate-600">Budget dépensé</div></div>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <h2 class="mb-2 text-lg font-bold">Évolution des leads (30j)</h2>
            <canvas id="leadsChart" height="180"></canvas>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <h2 class="mb-2 text-lg font-bold">Répartition du budget</h2>
            <canvas id="budgetChart" height="180"></canvas>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-xl font-bold">Campagnes actives</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="border-b border-slate-200 text-left"><th class="p-2">Stratégie</th><th class="p-2">Ville</th><th class="p-2">Niveau</th><th class="p-2">Leads</th><th class="p-2">Budget</th><th class="p-2">CPL</th><th class="p-2">Taux conv.</th></tr></thead>
                <tbody>
                <?php foreach ($campaigns as $campaign): ?>
                    <?php $leadCount = (int) $campaign['leads_count']; $spent = (float) $campaign['budget_spent']; $rate = $leadCount > 0 ? ((int) $campaign['converted_count'] * 100) / $leadCount : 0; ?>
                    <tr class="border-b border-slate-100">
                        <td class="p-2"><?= admin_h($campaign['strategy_name']) ?></td>
                        <td class="p-2"><?= admin_h($campaign['city_name']) ?></td>
                        <td class="p-2"><?= admin_h(ucfirst((string) $campaign['awareness_level'])) ?></td>
                        <td class="p-2"><?= $leadCount ?></td>
                        <td class="p-2"><?= number_format($spent, 2, ',', ' ') ?> €</td>
                        <td class="p-2"><?= $leadCount > 0 ? number_format($spent / $leadCount, 2, ',', ' ') . ' €' : 'N/A' ?></td>
                        <td class="p-2"><?= number_format($rate, 1, ',', ' ') ?>%</td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$campaigns): ?><tr><td colspan="7" class="p-3 text-slate-500">Aucune donnée de campagne.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const leadsCtx = document.getElementById('leadsChart');
if (leadsCtx) {
    new Chart(leadsCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($leadsDaily, 'day')) ?>,
            datasets: [{label: 'Leads', data: <?= json_encode(array_map('intval', array_column($leadsDaily, 'count'))) ?>, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.15)', fill: true, tension: 0.2}]
        },
        options: {responsive: true}
    });
}

const budgetCtx = document.getElementById('budgetChart');
if (budgetCtx) {
    new Chart(budgetCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($budgetByAwareness, 'awareness_level')) ?>,
            datasets: [{data: <?= json_encode(array_map('floatval', array_column($budgetByAwareness, 'budget_spent'))) ?>, backgroundColor: ['#ef4444','#f59e0b','#3b82f6']}]
        },
        options: {responsive: true}
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
