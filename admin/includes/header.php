<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/navigation.php';

initSecureSession();

if (!function_exists('admin_h')) {
    function admin_h(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!isset($pageTitle) || !is_string($pageTitle) || $pageTitle === '') {
    $pageTitle = 'Administration';
}

$adminMockData = [
    'notifications' => [
        ['id' => 1, 'type' => 'lead', 'title' => 'Nouveau lead premium', 'detail' => 'Appartement T4 · Chartrons · Budget 670k€', 'time' => 'Il y a 4 min'],
        ['id' => 2, 'type' => 'meeting', 'title' => 'RDV confirmé', 'detail' => 'Visite propriétaire demain à 10h30', 'time' => 'Il y a 22 min'],
        ['id' => 3, 'type' => 'ads', 'title' => 'Campagne performante', 'detail' => 'ROAS Google Ads en hausse de 18%', 'time' => 'Il y a 1h'],
    ],
    'user' => ['name' => 'Camille Martin', 'role' => 'Admin premium', 'agency' => 'Bordeaux Signature'],
];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= admin_h($pageTitle) ?> · <?= admin_h(SITE_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root,
        [data-theme="light"] {
            --admin-sidebar-bg: #0b1c32;
            --admin-sidebar-text: #f8fafc;
            --admin-sidebar-muted: #aac0de;
            --admin-sidebar-active-bg: #132e50;
            --admin-content-bg: #f8fafc;
            --admin-header-bg: #ffffff;
            --admin-header-text: #0f172a;
            --admin-border: #e2e8f0;
            --admin-sidebar-accent: #f97316;
            --admin-sidebar-hover: #102846;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
        }

        [data-theme="dark"] {
            --admin-sidebar-bg: #081426;
            --admin-sidebar-text: #e2e8f0;
            --admin-sidebar-muted: #92abd0;
            --admin-sidebar-active-bg: #10335a;
            --admin-content-bg: #0b1220;
            --admin-header-bg: #0f172a;
            --admin-header-text: #f8fafc;
            --admin-border: #334155;
            --admin-sidebar-accent: #fb923c;
            --admin-sidebar-hover: #10233f;
            --card-bg: #0f172a;
            --card-border: #334155;
        }

        .focus-visible-ring:focus-visible {
            outline: 3px solid #f59e0b;
            outline-offset: 2px;
        }

        .admin-card { background: var(--card-bg); border-color: var(--card-border); }

        body.sidebar-compact #admin-sidebar { width: 92px; }
        body.sidebar-compact [data-compact-hide] { display: none !important; }
        body.sidebar-compact #admin-sidebar nav > div > a { justify-content: center; }
    </style>
</head>
<body class="bg-[var(--admin-content-bg)] text-slate-900 transition-colors duration-200" data-theme="light">
<script>
window.ADMIN_MOCK_DATA = <?= json_encode($adminMockData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<header class="fixed inset-x-0 top-0 z-50 h-16 border-b bg-[var(--admin-header-bg)] text-[var(--admin-header-text)]" style="border-color: var(--admin-border)">
    <div class="mx-auto flex h-full max-w-[1700px] items-center gap-3 px-3 md:px-6">
        <div class="flex items-center gap-2 md:gap-3">
            <button type="button" id="sidebar-toggle" class="focus-visible-ring inline-flex items-center justify-center rounded-md border p-2 text-inherit transition hover:bg-slate-100/20 lg:hidden" style="border-color: var(--admin-border)" aria-controls="admin-sidebar" aria-expanded="false" aria-label="Ouvrir le menu latéral">☰</button>
            <a href="/admin/index.php" class="focus-visible-ring hidden text-xl font-bold tracking-tight sm:block">EstimIA Pro</a>
        </div>

        <div class="relative min-w-0 flex-1">
            <label for="global-search" class="sr-only">Recherche globale</label>
            <input id="global-search" type="search" placeholder="Rechercher un lead, une campagne, une ville..." class="w-full rounded-xl border bg-transparent px-4 py-2.5 text-sm outline-none transition focus:border-orange-400" style="border-color: var(--admin-border)" />
        </div>

        <div class="flex items-center gap-1.5 md:gap-2 text-sm font-medium">
            <div class="relative">
                <button type="button" id="notif-toggle" class="focus-visible-ring rounded-lg border px-3 py-2 hover:bg-slate-100/20" style="border-color: var(--admin-border)">🔔<span class="ml-1 hidden md:inline">Alertes</span></button>
                <div id="notif-panel" class="absolute right-0 z-50 mt-2 hidden w-[320px] rounded-xl border p-3 shadow-xl admin-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notifications</p>
                    <div id="notif-list" class="mt-2 space-y-2"></div>
                </div>
            </div>

            <?php foreach ($adminTopNav as $item): ?>
                <?php if (!empty($item['isToggle'])): ?>
                    <button type="button" id="theme-toggle" class="focus-visible-ring rounded-lg border px-3 py-2 transition hover:bg-slate-100/20" style="border-color: var(--admin-border)" aria-pressed="false">🌙<span class="ml-1 hidden md:inline"><?= admin_h($item['label']) ?></span></button>
                <?php else: ?>
                    <a href="<?= admin_h($item['href']) ?>" class="focus-visible-ring rounded-lg border px-3 py-2 transition hover:bg-slate-100/20" style="border-color: var(--admin-border)"><?= admin_h($item['label']) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>

            <button type="button" id="profile-toggle" class="focus-visible-ring rounded-lg border px-3 py-2 hover:bg-slate-100/20" style="border-color: var(--admin-border)">👤<span class="ml-1 hidden lg:inline">Profil</span></button>
        </div>
    </div>
</header>
<div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-950/50 lg:hidden"></div>
<div class="mx-auto flex min-h-screen max-w-[1700px] pt-16">
