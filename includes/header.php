<?php
if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE === true) {
    // Ne pas bloquer l'admin
    if (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') === false) {
        include __DIR__ . '/../pages/maintenance.php';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= defined('SITE_NAME') ? SITE_NAME : 'EstimIA' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-900">
<header class="border-b">
    <div class="max-w-6xl mx-auto px-4 py-4 font-bold">EstimIA</div>
</header>
<main class="max-w-6xl mx-auto px-4 py-6">
