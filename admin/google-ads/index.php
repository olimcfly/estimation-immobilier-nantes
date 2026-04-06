<?php
$pageTitle = 'Google Ads - Stratégies';
$currentPage = 'google-ads';
$topNavCurrent = 'google-ads';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/_bootstrap.php';

$db = Database::getConnection();
$strategies = [];
$error = null;

try {
    $strategies = gaFetchStrategies($db);
} catch (Throwable $e) {
    error_log('Erreur stratégies Google Ads: ' . $e->getMessage());
    $error = 'Impossible de charger les stratégies pour le moment.';
}
?>

<div class="mx-auto max-w-7xl space-y-6">
    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">🎯 Stratégies Google Ads</h1>
                <p class="text-sm text-slate-600">Pilotez plusieurs services et plusieurs villes depuis l'admin.</p>
            </div>
            <a href="/admin/google-ads/campaigns.php" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">📊 Voir les campagnes</a>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-amber-800"><?= admin_h($error) ?></div>
        <?php endif; ?>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($strategies as $strategy): ?>
                <article class="rounded-xl border p-4" style="border-color: <?= admin_h($strategy['color'] ?: '#2563eb') ?>;">
                    <div class="mb-2 text-lg font-semibold" style="color: <?= admin_h($strategy['color'] ?: '#2563eb') ?>;"><?= admin_h($strategy['name']) ?></div>
                    <p class="min-h-10 text-sm text-slate-600"><?= admin_h($strategy['description'] ?: 'Aucune description.') ?></p>
                    <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                        <span><?= (int) $strategy['is_active'] === 1 ? '✅ Active' : '⏸️ Inactive' ?></span>
                        <span>Créée le <?= date('d/m/Y', strtotime((string) $strategy['created_at'])) ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (!$strategies): ?>
                <p class="text-sm text-slate-500">Aucune stratégie enregistrée.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-xl font-bold">➕ Créer une stratégie</h2>
        <form method="POST" action="/admin/google-ads/strategy-save.php" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Nom</label>
                    <input type="text" name="name" required class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Ex: Estimation Immobilière">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Couleur</label>
                    <input type="color" name="color" value="#2563eb" class="h-11 w-full rounded-lg border border-slate-300">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Description</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Description courte"></textarea>
            </div>
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">Créer la stratégie</button>
        </form>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
