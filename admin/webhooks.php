<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Webhook.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/admin-auth.php';

initSecureSession();

$db = Database::getConnection();
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken()) {
        $message = 'Session expirée (CSRF). Rechargez la page.';
    } else {
    $action = $_POST['action'] ?? '';

    if ($action === 'test') {
        $ok = Webhook::fire('webhook.test', [
            'message' => 'Ping depuis l\'interface admin',
            'admin_time' => date('Y-m-d H:i:s'),
        ]);
        $message = $ok ? 'Webhook de test envoyé avec succès.' : 'Échec de l\'envoi du webhook de test.';
    }

    if ($action === 'retry' && !empty($_POST['log_id'])) {
        $stmt = $db->prepare('SELECT * FROM webhook_logs WHERE id = ? LIMIT 1');
        $stmt->execute([(int) $_POST['log_id']]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($log) {
            $payload = json_decode((string) $log['payload'], true);
            $ok = Webhook::fire((string) $log['event'], (array) ($payload['data'] ?? []));
            $message = $ok ? 'Webhook renvoyé avec succès.' : 'Échec du renvoi du webhook.';
        }
    }
    }
}

$stats = $db->query(
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS success_count,
        MAX(CASE WHEN status = 'success' THEN created_at ELSE NULL END) AS last_success
     FROM webhook_logs"
)->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'success_count' => 0, 'last_success' => null];

$total = (int) ($stats['total'] ?? 0);
$successCount = (int) ($stats['success_count'] ?? 0);
$successRate = $total > 0 ? round(($successCount / $total) * 100, 1) : 0;
$lastSuccess = $stats['last_success'] ?? 'Aucun envoi réussi';

$rows = $db->query('SELECT * FROM webhook_logs ORDER BY id DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Webhooks';
$currentPage = 'webhooks';
$topNavCurrent = 'settings';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<section class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Logs Webhook</h1>
            <p class="text-gray-600">Suivi des envois webhook et relances manuelles.</p>
        </div>
        <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="test">
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                Tester le webhook
            </button>
        </form>
    </div>

    <?php if ($message): ?>
        <div class="rounded-lg border border-blue-200 bg-blue-50 text-blue-800 px-4 py-3">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-xl border p-4 bg-white">
            <p class="text-sm text-gray-500">Taux de succès</p>
            <p class="text-3xl font-bold text-green-600"><?= $successRate ?>%</p>
            <p class="text-xs text-gray-500 mt-1"><?= $successCount ?> / <?= $total ?> envois</p>
        </div>
        <div class="rounded-xl border p-4 bg-white">
            <p class="text-sm text-gray-500">Dernier envoi réussi</p>
            <p class="text-lg font-semibold"><?= htmlspecialchars((string) $lastSuccess, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Événement</th>
                    <th class="px-4 py-3 text-left">URL</th>
                    <th class="px-4 py-3 text-left">HTTP Code</th>
                    <th class="px-4 py-3 text-left">Statut</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Aucun log webhook.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr class="border-t">
                            <td class="px-4 py-3"><?= htmlspecialchars((string) $row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-4 py-3 font-medium"><?= htmlspecialchars((string) $row['event'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-4 py-3 max-w-xs truncate" title="<?= htmlspecialchars((string) $row['url'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars((string) $row['url'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="px-4 py-3"><?= (int) $row['http_code'] ?></td>
                            <td class="px-4 py-3">
                                <?php if ($row['status'] === 'success'): ?>
                                    <span class="text-green-700 bg-green-100 px-2 py-1 rounded">success</span>
                                <?php else: ?>
                                    <span class="text-red-700 bg-red-100 px-2 py-1 rounded">failed</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <?php if ($row['status'] === 'failed'): ?>
                                    <form method="post" class="inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="retry">
                                        <input type="hidden" name="log_id" value="<?= (int) $row['id'] ?>">
                                        <button class="text-blue-600 hover:text-blue-800 font-semibold">Renvoyer</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
