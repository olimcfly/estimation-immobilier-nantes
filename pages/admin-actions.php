<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/admin-auth.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'db_backup':
        $db = Database::getConnection();
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="backup-' . date('Y-m-d-His') . '.sql"');

        $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $rows = $db->query('SELECT * FROM `' . str_replace('`', '``', $table) . '`')->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $values = array_map(function ($v) use ($db) {
                    if ($v === null) {
                        return 'NULL';
                    }
                    return $db->quote($v);
                }, $row);
                echo 'INSERT INTO `' . $table . '` VALUES (' . implode(',', $values) . ");\n";
            }
            echo "\n";
        }
        exit;

    case 'export_leads':
        header('Location: /admin/export.php?type=leads_csv');
        exit;

    case 'purge_old_leads':
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM estimations WHERE created_at < DATE_SUB(NOW(), INTERVAL 36 MONTH)');
        $stmt->execute();
        header('Location: /admin/settings.php?purge=success');
        exit;

    default:
        header('Location: /admin/settings.php');
        exit;
}
