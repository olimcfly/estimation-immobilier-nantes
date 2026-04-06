<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/admin-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/google-ads/index.php');
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$color = trim((string) ($_POST['color'] ?? '#2563eb'));
$color = preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#2563eb';

if ($name === '') {
    header('Location: /admin/google-ads/index.php');
    exit;
}

try {
    $db = Database::getConnection();
    $stmt = $db->prepare('INSERT INTO google_ads_strategies (name, description, color, is_active) VALUES (:name, :description, :color, 1)');
    $stmt->execute([
        ':name' => mb_substr($name, 0, 255),
        ':description' => $description,
        ':color' => $color,
    ]);
} catch (Throwable $e) {
    error_log('Erreur création stratégie Google Ads: ' . $e->getMessage());
}

header('Location: /admin/google-ads/index.php');
exit;
