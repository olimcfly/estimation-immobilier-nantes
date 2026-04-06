<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../classes/Webhook.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function updateLeadStatus(array $lead, string $newStatus): void
{
    $db = Database::getConnection();

    $oldStatus = (string) ($lead['lead_statut'] ?? 'nouveau');

    $stmt = $db->prepare('UPDATE estimations SET lead_statut = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$newStatus, (int) $lead['id']]);

    $payloadLead = $lead;
    $payloadLead['status'] = $newStatus;
    $payloadLead['lead_statut'] = $newStatus;

    Webhook::statusChanged($payloadLead, $oldStatus, $newStatus);
}

$allowedStatus = ['nouveau', 'contacte', 'qualifie', 'estimation_rdv', 'mandat', 'perdu'];
$flashMessage = null;
$flashType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $postedToken = (string) ($_POST['csrf_token'] ?? '');

    if (!hash_equals((string) $_SESSION['csrf_token'], $postedToken)) {
        $flashMessage = 'Token CSRF invalide. Merci de recharger la page.';
        $flashType = 'error';
    } else {
        $leadId = (int) ($_POST['lead_id'] ?? 0);
        $newStatus = (string) ($_POST['new_status'] ?? '');

        if ($leadId <= 0 || !in_array($newStatus, $allowedStatus, true)) {
            $flashMessage = 'Requête invalide : statut ou identifiant incorrect.';
            $flashType = 'error';
        } else {
            try {
                $db = Database::getConnection();
                $leadStmt = $db->prepare('SELECT * FROM estimations WHERE id = ? LIMIT 1');
                $leadStmt->execute([$leadId]);
                $lead = $leadStmt->fetch(PDO::FETCH_ASSOC);

                if (!$lead) {
                    $flashMessage = 'Lead introuvable.';
                    $flashType = 'error';
                } else {
                    updateLeadStatus($lead, $newStatus);
                    $flashMessage = 'Statut du lead mis à jour avec succès.';
                }
            } catch (Throwable $exception) {
                $flashMessage = 'Impossible de mettre à jour le statut pour le moment.';
                $flashType = 'error';
            }
        }
    }
}

$selectedStatus = (string) ($_GET['status'] ?? '');
$search = trim((string) ($_GET['q'] ?? ''));
$leadId = (int) ($_GET['id'] ?? 0);

$whereParts = [];
$params = [];

if ($selectedStatus !== '' && in_array($selectedStatus, $allowedStatus, true)) {
    $whereParts[] = 'lead_statut = ?';
    $params[] = $selectedStatus;
}

if ($search !== '') {
    $whereParts[] = '(prenom LIKE ? OR nom LIKE ? OR email LIKE ? OR telephone LIKE ? OR ville LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$whereSql = $whereParts === [] ? '1=1' : implode(' AND ', $whereParts);

$leads = [];
$selectedLead = null;
$loadError = null;

try {
    $db = Database::getConnection();

    $leadsStmt = $db->prepare(
        "SELECT id, created_at, prenom, nom, email, telephone, ville, type_bien, surface, prix_estime, lead_statut, lead_score
         FROM estimations
         WHERE {$whereSql}
         ORDER BY created_at DESC
         LIMIT 100"
    );
    $leadsStmt->execute($params);
    $leads = $leadsStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($leadId > 0) {
        $detailStmt = $db->prepare('SELECT * FROM estimations WHERE id = ? LIMIT 1');
        $detailStmt->execute([$leadId]);
        $selectedLead = $detailStmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    if ($selectedLead === null && $leads !== []) {
        $detailStmt = $db->prepare('SELECT * FROM estimations WHERE id = ? LIMIT 1');
        $detailStmt->execute([(int) $leads[0]['id']]);
        $selectedLead = $detailStmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
} catch (Throwable $exception) {
    $loadError = 'La page des leads est temporairement indisponible. Vérifiez la base de données.';
}

$statusLabels = [
    'nouveau' => 'Nouveau',
    'contacte' => 'Contacté',
    'qualifie' => 'Qualifié',
    'estimation_rdv' => 'Estimation RDV',
    'mandat' => 'Mandat',
    'perdu' => 'Perdu',
];

$pageTitle = 'Leads CRM (estimations)';
$currentPage = 'estimations';
$topNavCurrent = 'dashboard';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<h1 class="text-3xl font-bold text-slate-800">Leads CRM (estimations)</h1>
<p class="mt-2 text-slate-600">Leads issus du formulaire d'estimation : filtrez et mettez à jour les statuts CRM.</p>

<?php if ($flashMessage !== null): ?>
    <div class="mt-6 rounded-xl border px-4 py-3 <?= $flashType === 'error' ? 'border-red-300 bg-red-50 text-red-700' : 'border-emerald-300 bg-emerald-50 text-emerald-700' ?>">
        <?= admin_h($flashMessage) ?>
    </div>
<?php endif; ?>

<?php if ($loadError !== null): ?>
    <div class="mt-6 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-amber-800">
        <?= admin_h($loadError) ?>
    </div>
<?php else: ?>
    <section class="mt-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <form method="get" class="grid gap-3 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="q" class="mb-1 block text-sm font-medium text-slate-700">Recherche</label>
                <input id="q" name="q" value="<?= admin_h($search) ?>" class="w-full rounded-md border border-slate-300 px-3 py-2" placeholder="Nom, email, téléphone, ville">
            </div>
            <div>
                <label for="status" class="mb-1 block text-sm font-medium text-slate-700">Statut</label>
                <select id="status" name="status" class="w-full rounded-md border border-slate-300 px-3 py-2">
                    <option value="">Tous</option>
                    <?php foreach ($statusLabels as $statusKey => $statusLabel): ?>
                        <option value="<?= admin_h($statusKey) ?>" <?= $selectedStatus === $statusKey ? 'selected' : '' ?>><?= admin_h($statusLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">Filtrer</button>
            </div>
        </form>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-2">
        <article class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-lg font-semibold text-slate-800">Liste des leads</h2>
            <p class="mt-1 text-sm text-slate-500"><?= count($leads) ?> résultat(s) affiché(s).</p>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-3 py-2 font-medium">Lead</th>
                            <th class="px-3 py-2 font-medium">Ville</th>
                            <th class="px-3 py-2 font-medium">Prix</th>
                            <th class="px-3 py-2 font-medium">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php if ($leads === []): ?>
                        <tr>
                            <td colspan="4" class="px-3 py-5 text-center text-slate-500">Aucun lead trouvé avec ces filtres.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leads as $lead): ?>
                            <?php $isActive = $selectedLead !== null && (int) $selectedLead['id'] === (int) $lead['id']; ?>
                            <tr class="<?= $isActive ? 'bg-blue-50' : 'hover:bg-slate-50' ?>">
                                <td class="px-3 py-2">
                                    <a class="font-medium text-blue-700 hover:underline" href="/admin/lead.php?id=<?= (int) $lead['id'] ?>&status=<?= urlencode($selectedStatus) ?>&q=<?= urlencode($search) ?>">
                                        <?= admin_h(trim((string) ($lead['prenom'] ?? '') . ' ' . (string) ($lead['nom'] ?? ''))) ?>
                                    </a>
                                    <div class="text-xs text-slate-500"><?= admin_h((string) ($lead['email'] ?? '')) ?></div>
                                </td>
                                <td class="px-3 py-2"><?= admin_h((string) ($lead['ville'] ?? '')) ?></td>
                                <td class="px-3 py-2"><?= number_format((float) ($lead['prix_estime'] ?? 0), 0, ',', ' ') ?> €</td>
                                <td class="px-3 py-2"><?= admin_h($statusLabels[(string) ($lead['lead_statut'] ?? 'nouveau')] ?? (string) ($lead['lead_statut'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-lg font-semibold text-slate-800">Détail du lead</h2>

            <?php if ($selectedLead === null): ?>
                <p class="mt-4 text-slate-500">Sélectionnez un lead dans la liste pour afficher son détail.</p>
            <?php else: ?>
                <dl class="mt-4 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                    <div><dt class="text-slate-500">ID</dt><dd class="font-medium text-slate-800">#<?= (int) $selectedLead['id'] ?></dd></div>
                    <div><dt class="text-slate-500">Date</dt><dd class="font-medium text-slate-800"><?= admin_h((string) ($selectedLead['created_at'] ?? '')) ?></dd></div>
                    <div><dt class="text-slate-500">Prénom / Nom</dt><dd class="font-medium text-slate-800"><?= admin_h(trim((string) ($selectedLead['prenom'] ?? '') . ' ' . (string) ($selectedLead['nom'] ?? ''))) ?></dd></div>
                    <div><dt class="text-slate-500">Email</dt><dd class="font-medium text-slate-800"><?= admin_h((string) ($selectedLead['email'] ?? '')) ?></dd></div>
                    <div><dt class="text-slate-500">Téléphone</dt><dd class="font-medium text-slate-800"><?= admin_h((string) ($selectedLead['telephone'] ?? '')) ?></dd></div>
                    <div><dt class="text-slate-500">Ville</dt><dd class="font-medium text-slate-800"><?= admin_h((string) ($selectedLead['ville'] ?? '')) ?></dd></div>
                    <div><dt class="text-slate-500">Type de bien</dt><dd class="font-medium text-slate-800"><?= admin_h((string) ($selectedLead['type_bien'] ?? '')) ?></dd></div>
                    <div><dt class="text-slate-500">Surface</dt><dd class="font-medium text-slate-800"><?= (float) ($selectedLead['surface'] ?? 0) ?> m²</dd></div>
                    <div><dt class="text-slate-500">Prix estimé</dt><dd class="font-medium text-slate-800"><?= number_format((float) ($selectedLead['prix_estime'] ?? 0), 0, ',', ' ') ?> €</dd></div>
                    <div><dt class="text-slate-500">Score</dt><dd class="font-medium text-slate-800"><?= (int) ($selectedLead['lead_score'] ?? 0) ?>/100</dd></div>
                </dl>

                <form method="post" class="mt-5 border-t border-slate-200 pt-4">
                    <input type="hidden" name="csrf_token" value="<?= admin_h((string) $_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="lead_id" value="<?= (int) $selectedLead['id'] ?>">
                    <label for="new_status" class="mb-1 block text-sm font-medium text-slate-700">Mettre à jour le statut</label>
                    <div class="flex gap-2">
                        <select id="new_status" name="new_status" class="flex-1 rounded-md border border-slate-300 px-3 py-2">
                            <?php $currentStatus = (string) ($selectedLead['lead_statut'] ?? 'nouveau'); ?>
                            <?php foreach ($statusLabels as $statusKey => $statusLabel): ?>
                                <option value="<?= admin_h($statusKey) ?>" <?= $currentStatus === $statusKey ? 'selected' : '' ?>><?= admin_h($statusLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 font-semibold text-white hover:bg-emerald-700">Enregistrer</button>
                    </div>
                </form>
            <?php endif; ?>
        </article>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
