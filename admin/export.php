<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/admin-auth.php';

$type = $_GET['type'] ?? '';
$db = Database::getConnection();

switch ($type) {
    case 'leads_csv':
        $filters = [];
        $where = '1=1';

        if (!empty($_GET['date_from'])) {
            $where .= ' AND created_at >= ?';
            $filters[] = $_GET['date_from'];
        }

        if (!empty($_GET['date_to'])) {
            $where .= ' AND created_at <= ?';
            $filters[] = $_GET['date_to'] . ' 23:59:59';
        }

        if (!empty($_GET['type_estimation'])) {
            $where .= ' AND type_estimation = ?';
            $filters[] = $_GET['type_estimation'];
        }

        if (!empty($_GET['statut'])) {
            $where .= ' AND lead_statut = ?';
            $filters[] = $_GET['statut'];
        }

        $stmt = $db->prepare("SELECT * FROM estimations WHERE {$where} ORDER BY created_at DESC");
        $stmt->execute($filters);
        $leads = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="leads_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'wb');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'ID', 'Date', 'Type estimation', 'Statut', 'Score',
            'Nom', 'Prénom', 'Email', 'Téléphone',
            'Adresse', 'Ville', 'Code postal',
            'Type bien', 'Surface m²', 'Pièces', 'Chambres',
            'État', 'Étage', 'DPE',
            'Prix estimé €', 'Prix bas €', 'Prix haut €', 'Prix m² €',
            'RDV pris', 'Source', 'UTM Source', 'UTM Medium', 'UTM Campaign',
        ], ';');

        foreach ($leads as $lead) {
            fputcsv($output, [
                $lead['id'] ?? '',
                $lead['created_at'] ?? '',
                $lead['type_estimation'] ?? '',
                $lead['lead_statut'] ?? '',
                $lead['lead_score'] ?? '',
                $lead['nom'] ?? '',
                $lead['prenom'] ?? '',
                $lead['email'] ?? '',
                $lead['telephone'] ?? '',
                $lead['adresse'] ?? '',
                $lead['ville'] ?? '',
                $lead['code_postal'] ?? '',
                $lead['type_bien'] ?? '',
                $lead['surface'] ?? '',
                $lead['nb_pieces'] ?? '',
                $lead['nb_chambres'] ?? '',
                $lead['etat_bien'] ?? '',
                $lead['etage'] ?? '',
                $lead['dpe'] ?? '',
                $lead['prix_estime'] ?? '',
                $lead['prix_bas'] ?? '',
                $lead['prix_haut'] ?? '',
                $lead['prix_m2'] ?? '',
                !empty($lead['rdv_pris']) ? 'Oui' : 'Non',
                $lead['source'] ?? '',
                $lead['utm_source'] ?? '',
                $lead['utm_medium'] ?? '',
                $lead['utm_campaign'] ?? '',
            ], ';');
        }

        fclose($output);
        exit;

    case 'leads_pdf':
        $stmt = $db->query('SELECT * FROM estimations ORDER BY created_at DESC LIMIT 100');
        $leads = $stmt->fetchAll();

        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!doctype html>
        <html lang="fr">
        <head>
            <meta charset="utf-8">
            <title>Export leads - <?= htmlspecialchars((string) SITE_NAME, ENT_QUOTES, 'UTF-8') ?></title>
            <style>
                @media print {
                    body { font-size: 10px; }
                    table { page-break-inside: auto; }
                    tr { page-break-inside: avoid; }
                }
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { font-size: 18px; }
                table { width: 100%; border-collapse: collapse; font-size: 11px; }
                th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
                th { background: #f3f4f6; font-weight: bold; }
                tr:nth-child(even) { background: #f9fafb; }
                .header-info { color: #666; margin-bottom: 20px; }
            </style>
        </head>
        <body onload="window.print()">
            <h1><?= htmlspecialchars((string) SITE_NAME, ENT_QUOTES, 'UTF-8') ?> - Export des leads</h1>
            <p class="header-info">
                Généré le <?= date('d/m/Y à H:i') ?> · <?= count($leads) ?> leads
            </p>
            <table>
                <thead>
                <tr>
                    <th>#</th><th>Date</th><th>Nom</th><th>Email</th><th>Tél</th><th>Ville</th>
                    <th>Type</th><th>Surface</th><th>Prix estimé</th><th>Statut</th><th>Score</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($leads as $l): ?>
                    <tr>
                        <td><?= (int) ($l['id'] ?? 0) ?></td>
                        <td><?= !empty($l['created_at']) ? date('d/m/Y', strtotime((string) $l['created_at'])) : '' ?></td>
                        <td><?= htmlspecialchars(trim(($l['prenom'] ?? '') . ' ' . ($l['nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($l['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($l['telephone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($l['ville'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($l['type_bien'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($l['surface'] ?? ''), ENT_QUOTES, 'UTF-8') ?> m²</td>
                        <td><?= number_format((float) ($l['prix_estime'] ?? 0), 0, ',', ' ') ?> €</td>
                        <td><?= htmlspecialchars((string) ($l['lead_statut'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) ($l['lead_score'] ?? 0) ?>/100</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </body>
        </html>
        <?php
        exit;

    case 'db_backup':
        $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="backup_' . DB_NAME . '_' . date('Y-m-d_His') . '.sql"');

        echo "-- EstimIA Database Backup\n";
        echo '-- Date: ' . date('Y-m-d H:i:s') . "\n";
        echo '-- Database: ' . DB_NAME . "\n\n";
        echo "SET NAMES utf8mb4;\n";
        echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = (string) $table;

            $create = $db->query("SHOW CREATE TABLE `{$tableName}`")->fetch();
            if (!$create || !isset($create['Create Table'])) {
                continue;
            }

            echo "DROP TABLE IF EXISTS `{$tableName}`;\n";
            echo $create['Create Table'] . ";\n\n";

            $rows = $db->query("SELECT * FROM `{$tableName}`")->fetchAll(PDO::FETCH_ASSOC);
            if (!$rows) {
                echo "\n";
                continue;
            }

            foreach ($rows as $row) {
                $columns = array_map(static fn ($col) => "`{$col}`", array_keys($row));
                $values = array_map(static function ($value) use ($db) {
                    if ($value === null) {
                        return 'NULL';
                    }

                    return $db->quote((string) $value);
                }, array_values($row));

                echo 'INSERT INTO `' . $tableName . '` (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ");\n";
            }

            echo "\n";
        }

        echo "SET FOREIGN_KEY_CHECKS=1;\n";
        exit;

    default:
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Type d\'export invalide. Valeurs autorisées : leads_csv, leads_pdf, db_backup.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
}
