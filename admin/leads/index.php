<?php

declare(strict_types=1);

$pageTitle = 'Leads';
$currentPage = 'google-ads';
$topNavCurrent = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$db = Database::getConnection();
$filters = [
    'strategy_id' => (int) ($_GET['strategy_id'] ?? 0),
    'city_id' => (int) ($_GET['city_id'] ?? 0),
    'status' => (string) ($_GET['status'] ?? ''),
    'date_from' => (string) ($_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'))),
    'date_to' => (string) ($_GET['date_to'] ?? date('Y-m-d')),
];

$leads = [];
$strategies = [];
$villes = [];

try {
    $strategiesStmt = $db->query('SELECT id, name FROM google_ads_strategies WHERE is_active = 1 ORDER BY name');
    $strategies = $strategiesStmt ? $strategiesStmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $villesStmt = $db->query('SELECT id, ville FROM villes_prix ORDER BY population DESC, ville ASC');
    $villes = $villesStmt ? $villesStmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $sql = "SELECT l.*, s.name AS strategy_name, v.ville AS city_name
            FROM leads l
            LEFT JOIN google_ads_strategies s ON s.id = l.strategy_id
            LEFT JOIN villes_prix v ON v.id = l.city_id
            WHERE l.created_at BETWEEN :date_from AND :date_to";
    $params = [
        ':date_from' => $filters['date_from'] . ' 00:00:00',
        ':date_to' => $filters['date_to'] . ' 23:59:59',
    ];

    if ($filters['strategy_id'] > 0) {
        $sql .= ' AND l.strategy_id = :strategy_id';
        $params[':strategy_id'] = $filters['strategy_id'];
    }
    if ($filters['city_id'] > 0) {
        $sql .= ' AND l.city_id = :city_id';
        $params[':city_id'] = $filters['city_id'];
    }
    if (in_array($filters['status'], ['new', 'contacted', 'converted', 'lost'], true)) {
        $sql .= ' AND l.status = :status';
        $params[':status'] = $filters['status'];
    }

    $sql .= ' ORDER BY l.created_at DESC LIMIT 500';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Erreur leads admin: ' . $e->getMessage());
}

$statusColors = [
    'new' => '#3b82f6',
    'contacted' => '#f59e0b',
    'converted' => '#10b981',
    'lost' => '#ef4444',
];
?>

<div class="mx-auto max-w-7xl space-y-6">
    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h1 class="mb-4 text-2xl font-bold">📞 Leads Google Ads</h1>
        <p class="mb-4 text-sm text-slate-600">Connecté via la configuration DB centralisée (<code>includes/config.php</code> + <code>includes/database.php</code>).</p>
        <form method="GET" class="grid gap-4 md:grid-cols-6">
            <div>
                <label class="mb-1 block text-xs font-medium">Stratégie</label>
                <select name="strategy_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="0">Toutes</option>
                    <?php foreach ($strategies as $strategy): ?>
                        <option value="<?= (int) $strategy['id'] ?>" <?= $filters['strategy_id'] === (int) $strategy['id'] ? 'selected' : '' ?>><?= admin_h($strategy['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium">Ville</label>
                <select name="city_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="0">Toutes</option>
                    <?php foreach ($villes as $ville): ?>
                        <option value="<?= (int) $ville['id'] ?>" <?= $filters['city_id'] === (int) $ville['id'] ? 'selected' : '' ?>><?= admin_h($ville['ville']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium">Statut</label>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Tous</option><option value="new" <?= $filters['status'] === 'new' ? 'selected' : '' ?>>Nouveau</option><option value="contacted" <?= $filters['status'] === 'contacted' ? 'selected' : '' ?>>Contacté</option><option value="converted" <?= $filters['status'] === 'converted' ? 'selected' : '' ?>>Converti</option><option value="lost" <?= $filters['status'] === 'lost' ? 'selected' : '' ?>>Perdu</option>
                </select>
            </div>
            <div><label class="mb-1 block text-xs font-medium">Du</label><input type="date" name="date_from" value="<?= admin_h($filters['date_from']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="mb-1 block text-xs font-medium">Au</label><input type="date" name="date_to" value="<?= admin_h($filters['date_to']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div class="flex items-end gap-2"><button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Filtrer</button><a href="/admin/leads/export.php?<?= admin_h(http_build_query($_GET)) ?>" class="rounded-lg bg-slate-200 px-3 py-2 text-sm">CSV</a></div>
        </form>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-xl font-bold"><?= count($leads) ?> leads trouvés</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="border-b border-slate-200 text-left"><th class="p-2">Date</th><th class="p-2">Stratégie</th><th class="p-2">Ville</th><th class="p-2">Nom</th><th class="p-2">Email</th><th class="p-2">Téléphone</th><th class="p-2">Type</th><th class="p-2">Statut</th></tr></thead>
                <tbody>
                <?php foreach ($leads as $lead): ?>
                    <tr class="border-b border-slate-100">
                        <td class="p-2"><?= date('d/m/Y H:i', strtotime((string) $lead['created_at'])) ?></td>
                        <td class="p-2"><?= admin_h((string) ($lead['strategy_name'] ?? '-')) ?></td>
                        <td class="p-2"><?= admin_h((string) ($lead['city_name'] ?? '-')) ?></td>
                        <td class="p-2"><?= admin_h((string) ($lead['name'] ?? '-')) ?></td>
                        <td class="p-2"><?= admin_h((string) ($lead['email'] ?? '-')) ?></td>
                        <td class="p-2"><?= admin_h((string) ($lead['phone'] ?? '-')) ?></td>
                        <td class="p-2"><?= admin_h((string) ($lead['type'] ?? '-')) ?></td>
                        <td class="p-2">
                            <form method="POST" action="/admin/leads/update-status.php" class="flex gap-2">
                                <input type="hidden" name="id" value="<?= (int) $lead['id'] ?>">
                                <select name="status" class="rounded border border-slate-300 px-2 py-1 text-xs" style="background: <?= admin_h($statusColors[$lead['status']] ?? '#6b7280') ?>20;">
                                    <?php foreach (['new' => 'Nouveau', 'contacted' => 'Contacté', 'converted' => 'Converti', 'lost' => 'Perdu'] as $statusKey => $statusLabel): ?>
                                        <option value="<?= $statusKey ?>" <?= $lead['status'] === $statusKey ? 'selected' : '' ?>><?= $statusLabel ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="rounded bg-slate-200 px-2 py-1 text-xs">OK</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$leads): ?><tr><td colspan="8" class="p-3 text-slate-500">Aucun lead sur la période.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
