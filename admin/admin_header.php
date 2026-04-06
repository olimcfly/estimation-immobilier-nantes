<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/admin-auth.php';

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
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= admin_h($pageTitle) ?> · <?= admin_h(SITE_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900">
<header class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
        <a href="/" class="font-semibold text-lg"><?= admin_h(SITE_NAME) ?></a>
        <nav class="flex gap-4 text-sm">
            <a href="/admin/settings.php" class="hover:underline">Paramètres</a>
            <a href="/admin/google-ads/index.php" class="hover:underline">Google Ads</a>
            <a href="/admin/export.php?type=leads_csv" class="hover:underline">Exports</a>
        </nav>
    </div>
</header>
<main class="max-w-7xl mx-auto px-4 py-8">
