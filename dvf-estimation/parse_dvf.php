<?php

declare(strict_types=1);

require_once __DIR__ . '/config_dvf.php';

/**
 * Parse une ligne fixe selon le mapping de champs.
 *
 * @return array<string, string>
 */
function parseFixedWidthLine(string $line): array
{
    $row = [];
    foreach (DVF_FIXED_WIDTH_FIELDS as $field => [$start, $length]) {
        $row[$field] = trim(substr($line, $start, $length));
    }

    return $row;
}

/**
 * Convertit YYYYMMDD en DateTimeImmutable.
 */
function parseDvfDate(string $rawDate): ?DateTimeImmutable
{
    if (!preg_match('/^\d{8}$/', $rawDate)) {
        return null;
    }

    $dt = DateTimeImmutable::createFromFormat('Ymd', $rawDate);
    return $dt instanceof DateTimeImmutable ? $dt : null;
}

/**
 * Valide et normalise une ligne DVF.
 *
 * @param array<string, string> $row
 * @return array<string, int|float|string>|null
 */
function normalizeRow(array $row): ?array
{
    $date = parseDvfDate((string) ($row['date_mutation'] ?? ''));
    if ($date === null) {
        return null;
    }

    $nature = mb_strtolower(trim((string) ($row['nature_mutation'] ?? '')));
    if ($nature !== 'vente') {
        return null;
    }

    $typeCode = trim((string) ($row['type_local'] ?? ''));
    $type = DVF_TYPE_LOCAL_MAP[$typeCode] ?? null;
    if ($type === null) {
        return null;
    }

    $valeur = (float) str_replace(',', '.', trim((string) ($row['valeur_fonciere'] ?? '0')));
    $surface = (float) str_replace(',', '.', trim((string) ($row['surface_reelle'] ?? '0')));

    if ($valeur <= 0 || $surface <= 0) {
        return null;
    }

    $commune = mb_strtoupper(trim((string) ($row['commune'] ?? '')));
    if ($commune === '') {
        return null;
    }

    return [
        'id_mutation' => (string) ($row['id_mutation'] ?? ''),
        'date_mutation' => $date->format('Y-m-d'),
        'valeur_fonciere' => round($valeur, 2),
        'code_postal' => trim((string) ($row['code_postal'] ?? '')),
        'commune' => $commune,
        'type_local' => $type,
        'surface_reelle' => round($surface, 2),
        'nombre_pieces' => (int) trim((string) ($row['nombre_pieces'] ?? '0')),
        'prix_m2' => round($valeur / $surface, 2),
    ];
}

/**
 * Parse le fichier TXT DVF en CSV+JSON.
 *
 * @return array{total:int,kept:int,csv_path:string,json_path:string}
 */
function parseDvfFile(): array
{
    if (!is_file(DVF_TXT_PATH)) {
        throw new RuntimeException(sprintf('Fichier TXT introuvable: %s', DVF_TXT_PATH));
    }

    ensureDvfDataDirectory();

    $in = fopen(DVF_TXT_PATH, 'rb');
    if ($in === false) {
        throw new RuntimeException('Impossible d\'ouvrir le fichier TXT DVF.');
    }

    $csv = fopen(DVF_CSV_PATH, 'wb');
    if ($csv === false) {
        fclose($in);
        throw new RuntimeException('Impossible d\'écrire le CSV DVF.');
    }

    $headers = ['id_mutation', 'date_mutation', 'valeur_fonciere', 'code_postal', 'commune', 'type_local', 'surface_reelle', 'nombre_pieces', 'prix_m2'];
    fputcsv($csv, $headers);

    $rows = [];
    $total = 0;
    $kept = 0;

    while (($line = fgets($in)) !== false) {
        $total++;

        if (trim($line) === '') {
            continue;
        }

        $parsed = parseFixedWidthLine($line);
        $normalized = normalizeRow($parsed);

        if ($normalized === null) {
            continue;
        }

        $rows[] = $normalized;
        fputcsv($csv, $normalized);
        $kept++;
    }

    fclose($in);
    fclose($csv);

    $json = json_encode($rows, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents(DVF_JSON_PATH, $json, LOCK_EX);

    @unlink(DVF_CACHE_PATH);

    return [
        'total' => $total,
        'kept' => $kept,
        'csv_path' => DVF_CSV_PATH,
        'json_path' => DVF_JSON_PATH,
    ];
}

if (PHP_SAPI === 'cli' && realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    try {
        $result = parseDvfFile();

        echo sprintf('Parsing terminé. Lignes lues: %d, transactions retenues: %d', $result['total'], $result['kept']) . PHP_EOL;
        echo 'CSV: ' . $result['csv_path'] . PHP_EOL;
        echo 'JSON: ' . $result['json_path'] . PHP_EOL;
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, 'Erreur parsing DVF: ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
}
