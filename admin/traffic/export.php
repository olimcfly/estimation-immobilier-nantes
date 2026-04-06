<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/admin-auth.php';

$db = Database::getConnection();
$rows = [];

try {
    $stmt = $db->query("SELECT DATE(l.created_at) AS day, s.name AS strategy_name, v.ville AS city_name, c.awareness_level, COUNT(l.id) AS leads_count, COALESCE(SUM(l.conversion_value), 0) AS budget_spent FROM leads l LEFT JOIN google_ads_campaigns c ON c.id = l.campaign_id LEFT JOIN google_ads_strategies s ON s.id = l.strategy_id LEFT JOIN villes_prix v ON v.id = l.city_id WHERE l.source = 'google_ads' AND l.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day, strategy_name, city_name, awareness_level ORDER BY day DESC");
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Throwable $e) {
    error_log('Erreur export trafic: ' . $e->getMessage());
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="traffic-publicite-30j.csv"');

echo "\xEF\xBB\xBF";
$out = fopen('php://output', 'wb');
fputcsv($out, ['Date', 'Stratégie', 'Ville', 'Niveau', 'Leads', 'Budget']);

foreach ($rows as $row) {
    fputcsv($out, [
        (string) $row['day'],
        (string) ($row['strategy_name'] ?? ''),
        (string) ($row['city_name'] ?? ''),
        (string) ($row['awareness_level'] ?? ''),
        (int) ($row['leads_count'] ?? 0),
        number_format((float) ($row['budget_spent'] ?? 0), 2, '.', ''),
    ]);
}

fclose($out);
exit;
