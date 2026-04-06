<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../classes/CRM/VendeurManager.php';
require_once __DIR__ . '/../classes/CRM/GoogleAdsTracker.php';

session_start();

if (empty($_SESSION['admin_logged']) || empty($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$currentPage = 'crm-vendeurs';
$topNavCurrent = 'dashboard';
$pageTitle = 'CRM Vendeurs';

$kpis = array_fill_keys(VendeurManager::STATUTS, 0);
$recentLeads = [];
$campaignPerformance = [];
$errorMessage = null;

try {
    $db = Database::getConnection();
    $vendeurManager = new VendeurManager($db);
    $adsTracker = new GoogleAdsTracker($db);

    $kpis = $vendeurManager->getKpiByStatut();
    $recentLeads = $vendeurManager->getRecentLeads(40);
    $campaignPerformance = $adsTracker->getCampaignPerformance();
} catch (Throwable $exception) {
    $errorMessage = 'Le module CRM vendeurs nécessite les nouvelles tables CRM (core/sql/crm_vendeurs.sql).';
}

$statutLabels = [
    'nouveau' => 'Nouveau',
    'contact_etabli' => 'Contact établi',
    'visite_planifiee' => 'Visite planifiée',
    'mandat_signe' => 'Mandat signé',
    'perdu' => 'Perdu',
];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<h1 class="text-3xl font-bold text-slate-800">CRM Vendeurs</h1>
<p class="mt-2 text-slate-600">Pilotage vendeur-first : pipeline commercial + campagnes Google Ads.</p>

<?php if ($errorMessage !== null): ?>
    <div class="mt-6 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-amber-800">
        <?= admin_h($errorMessage) ?>
    </div>
<?php else: ?>
    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <?php foreach ($kpis as $status => $total): ?>
            <article class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs font-medium uppercase text-slate-500"><?= admin_h($statutLabels[$status] ?? $status) ?></p>
                <p class="mt-2 text-3xl font-extrabold text-slate-800"><?= (int) $total ?></p>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-3">
        <article class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
            <h2 class="text-lg font-semibold text-slate-800">Derniers leads vendeurs</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-3 py-2 font-medium">Date</th>
                            <th class="px-3 py-2 font-medium">Nom</th>
                            <th class="px-3 py-2 font-medium">Ville</th>
                            <th class="px-3 py-2 font-medium">Bien</th>
                            <th class="px-3 py-2 font-medium">Estimation</th>
                            <th class="px-3 py-2 font-medium">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php if ($recentLeads === []): ?>
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-slate-500">Aucun lead vendeur disponible.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentLeads as $lead): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2"><?= admin_h((string) ($lead['created_at'] ?? '')) ?></td>
                                <td class="px-3 py-2"><?= admin_h((string) ($lead['nom'] ?? '')) ?></td>
                                <td class="px-3 py-2"><?= admin_h((string) ($lead['ville'] ?? '')) ?></td>
                                <td class="px-3 py-2"><?= admin_h((string) ($lead['type_bien'] ?? '')) ?></td>
                                <td class="px-3 py-2">
                                    <?= number_format((float) ($lead['estimation_min'] ?? 0), 0, ',', ' ') ?> €
                                    -
                                    <?= number_format((float) ($lead['estimation_max'] ?? 0), 0, ',', ' ') ?> €
                                </td>
                                <td class="px-3 py-2"><?= admin_h($statutLabels[(string) ($lead['statut'] ?? 'nouveau')] ?? (string) ($lead['statut'] ?? '-')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-lg font-semibold text-slate-800">Performance Google Ads</h2>
            <ul class="mt-4 space-y-3 text-sm">
                <?php if ($campaignPerformance === []): ?>
                    <li class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-500">Aucune donnée campagne.</li>
                <?php else: ?>
                    <?php foreach (array_slice($campaignPerformance, 0, 8) as $row): ?>
                        <li class="rounded-lg border border-slate-200 px-3 py-2">
                            <p class="font-semibold text-slate-800"><?= admin_h((string) ($row['campagne'] ?? 'Sans campagne')) ?></p>
                            <p class="text-slate-600"><?= (int) ($row['leads'] ?? 0) ?> leads · <?= (int) ($row['mandats'] ?? 0) ?> mandats</p>
                            <p class="text-emerald-700"><?= number_format((float) ($row['taux_conversion'] ?? 0), 2, ',', ' ') ?> % conversion</p>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </article>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
