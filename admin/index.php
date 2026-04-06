<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';

session_start();

if (empty($_SESSION['admin_logged']) || empty($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$db = Database::getConnection();
$totalEstimations = 0;
$dashboardError = null;

try {
    $totalEstimations = (int) $db->query('SELECT COUNT(*) FROM estimations')->fetchColumn();
} catch (Throwable $exception) {
    $dashboardError = 'Le tableau de bord est temporairement indisponible. Vérifiez la connexion à la base de données et la table "estimations".';
}

$pageTitle = 'Dashboard SaaS';
$currentPage = 'dashboard';
$topNavCurrent = 'dashboard';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<section class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 md:text-3xl dark:text-slate-100">Admin immobilier premium</h1>
            <p class="mt-1 text-sm text-slate-500">Pilotage des leads, acquisition et conversion en temps réel.</p>
        </div>
        <a href="/admin/leads/index.php" class="inline-flex items-center justify-center rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-400">+ Nouveau lead manuel</a>
    </div>

    <?php if ($dashboardError !== null): ?>
        <div class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-amber-800">
            <?php echo htmlspecialchars($dashboardError, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="admin-card rounded-2xl border p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Leads totaux</p>
            <p class="mt-2 text-3xl font-extrabold text-blue-600"><?php echo $totalEstimations; ?></p>
            <p class="mt-1 text-xs text-emerald-500">+11.2% sur 30 jours</p>
        </article>
        <article class="admin-card rounded-2xl border p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Taux conversion</p>
            <p class="mt-2 text-3xl font-extrabold">18.7%</p>
            <p class="mt-1 text-xs text-emerald-500">+2.4 pts cette semaine</p>
        </article>
        <article class="admin-card rounded-2xl border p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Coût / lead</p>
            <p class="mt-2 text-3xl font-extrabold">42€</p>
            <p class="mt-1 text-xs text-emerald-500">-8% vs mois dernier</p>
        </article>
        <article class="admin-card rounded-2xl border p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Satisfaction clients</p>
            <p class="mt-2 text-3xl font-extrabold">4.8/5</p>
            <p class="mt-1 text-xs text-slate-500">127 avis vérifiés</p>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <article class="admin-card rounded-2xl border p-5 shadow-sm xl:col-span-2">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-semibold">Performance leads (7 jours)</h2>
                <span class="text-xs text-slate-500">Mockup JSON</span>
            </div>
            <div id="kpi-chart" class="h-56"></div>
        </article>
        <article class="admin-card rounded-2xl border p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-semibold">Sources d'acquisition</h2>
                <span class="text-xs text-slate-500">Top canaux</span>
            </div>
            <div id="source-chart" class="h-56"></div>
        </article>
    </section>

    <section class="admin-card rounded-2xl border p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-semibold">Leads prioritaires</h2>
            <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700">Focus conversion</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">Lead</th>
                    <th class="px-3 py-2">Bien</th>
                    <th class="px-3 py-2">Source</th>
                    <th class="px-3 py-2">Score</th>
                    <th class="px-3 py-2">Statut</th>
                    <th class="px-3 py-2">Valeur</th>
                </tr>
                </thead>
                <tbody id="leads-table" class="divide-y" style="border-color: var(--admin-border)"></tbody>
            </table>
        </div>
    </section>
</section>

<script>
    (function () {
        const dashboardMockup = {
            leadsTrend: [12, 18, 15, 22, 27, 24, 31],
            leadSources: [
                { label: 'SEO', value: 45, color: '#0ea5e9' },
                { label: 'Google Ads', value: 30, color: '#f97316' },
                { label: 'Referral', value: 15, color: '#22c55e' },
                { label: 'Social', value: 10, color: '#a855f7' }
            ],
            leads: [
                { name: 'Claire Dupont', property: 'Maison · Caudéran', source: 'SEO', score: 92, status: 'RDV planifié', value: '1 250 000€' },
                { name: 'Paul Martin', property: 'Appartement · Chartrons', source: 'Google Ads', score: 87, status: 'Relance J+1', value: '640 000€' },
                { name: 'Maya Leroy', property: 'Immeuble · Bastide', source: 'Referral', score: 81, status: 'Qualification', value: '980 000€' },
                { name: 'Sami Benali', property: 'Loft · Saint-Michel', source: 'Social', score: 76, status: 'Nouveau', value: '710 000€' }
            ]
        };

        const chartContainer = document.getElementById('kpi-chart');
        const sourceContainer = document.getElementById('source-chart');
        const leadsTable = document.getElementById('leads-table');

        if (chartContainer) {
            const max = Math.max.apply(null, dashboardMockup.leadsTrend);
            chartContainer.innerHTML = '<div class="flex h-full items-end gap-2">' +
                dashboardMockup.leadsTrend.map(function (value, index) {
                    const height = Math.max(12, Math.round((value / max) * 100));
                    return '<div class="flex-1 text-center">' +
                        '<div class="mx-auto w-full max-w-[42px] rounded-t-lg bg-sky-500/80" style="height:' + height + '%"></div>' +
                        '<p class="mt-2 text-[11px] text-slate-500">J' + (index + 1) + '</p>' +
                    '</div>';
                }).join('') +
            '</div>';
        }

        if (sourceContainer) {
            const total = dashboardMockup.leadSources.reduce(function (sum, source) { return sum + source.value; }, 0);
            sourceContainer.innerHTML = '<div class="space-y-3">' + dashboardMockup.leadSources.map(function (source) {
                const width = Math.round((source.value / total) * 100);
                return '<div>' +
                    '<div class="mb-1 flex items-center justify-between text-xs"><span>' + source.label + '</span><span>' + width + '%</span></div>' +
                    '<div class="h-2 w-full rounded-full bg-slate-200/70"><div class="h-2 rounded-full" style="width:' + width + '%;background:' + source.color + '"></div></div>' +
                '</div>';
            }).join('') + '</div>';
        }

        if (leadsTable) {
            leadsTable.innerHTML = dashboardMockup.leads.map(function (lead) {
                const statusClass = lead.status === 'Nouveau' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700';
                return '<tr class="hover:bg-slate-50/80">' +
                    '<td class="px-3 py-3 font-medium">' + lead.name + '</td>' +
                    '<td class="px-3 py-3">' + lead.property + '</td>' +
                    '<td class="px-3 py-3">' + lead.source + '</td>' +
                    '<td class="px-3 py-3">' + lead.score + '/100</td>' +
                    '<td class="px-3 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold ' + statusClass + '">' + lead.status + '</span></td>' +
                    '<td class="px-3 py-3 font-semibold">' + lead.value + '</td>' +
                '</tr>';
            }).join('');
        }
    })();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
