<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/admin-auth.php';

$db = Database::getConnection();
$filters = [
    'strategy_id' => (int) ($_GET['strategy_id'] ?? 0),
    'city_id' => (int) ($_GET['city_id'] ?? 0),
    'status' => (string) ($_GET['status'] ?? ''),
    'date_from' => (string) ($_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'))),
    'date_to' => (string) ($_GET['date_to'] ?? date('Y-m-d')),
];

$sql = "SELECT l.created_at, s.name AS strategy_name, v.ville AS city_name, l.name, l.email, l.phone, l.type, l.status, l.conversion_value
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
$sql .= ' ORDER BY l.created_at DESC';

$rows = [];
try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Erreur export leads: ' . $e->getMessage());
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="leads-google-ads.csv"');
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'wb');
fputcsv($out, ['Date', 'Stratégie', 'Ville', 'Nom', 'Email', 'Téléphone', 'Type', 'Statut', 'Valeur']);
foreach ($rows as $row) {
    fputcsv($out, [
        (string) $row['created_at'],
        (string) ($row['strategy_name'] ?? ''),
        (string) ($row['city_name'] ?? ''),
        (string) ($row['name'] ?? ''),
        (string) ($row['email'] ?? ''),
        (string) ($row['phone'] ?? ''),
        (string) ($row['type'] ?? ''),
        (string) ($row['status'] ?? ''),
        number_format((float) ($row['conversion_value'] ?? 0), 2, '.', ''),
    ]);
}
fclose($out);
exit;
