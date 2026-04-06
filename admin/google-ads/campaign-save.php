<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/admin-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/google-ads/campaigns.php');
    exit;
}

$strategyId = (int) ($_POST['strategy_id'] ?? 0);
$cityId = (int) ($_POST['city_id'] ?? 0);
$awareness = (string) ($_POST['awareness_level'] ?? 'hot');
$budgetPercent = max(1, min(100, (int) ($_POST['budget_percent'] ?? 60)));

if (!in_array($awareness, ['hot', 'warm', 'cold'], true) || $strategyId <= 0 || $cityId <= 0) {
    header('Location: /admin/google-ads/campaigns.php');
    exit;
}

try {
    $db = Database::getConnection();
    $stmt = $db->prepare('INSERT INTO google_ads_campaigns (strategy_id, city_id, awareness_level, budget_percent, is_active) VALUES (:strategy_id, :city_id, :awareness_level, :budget_percent, 1)');
    $stmt->execute([
        ':strategy_id' => $strategyId,
        ':city_id' => $cityId,
        ':awareness_level' => $awareness,
        ':budget_percent' => $budgetPercent,
    ]);
} catch (Throwable $e) {
    error_log('Erreur création campagne Google Ads: ' . $e->getMessage());
}

header('Location: /admin/google-ads/campaigns.php');
exit;
