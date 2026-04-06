<?php
declare(strict_types=1);

$options = getopt('', ['file:']);
$file = (string) ($options['file'] ?? '');

if ($file === '' || !is_file($file)) {
    fwrite(STDERR, "CSV file not found. Use --file=lexique.csv\n");
    exit(1);
}

$rows = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$count = is_array($rows) ? max(count($rows) - 1, 0) : 0;

echo "Imported {$count} keyword rows from {$file}" . PHP_EOL;
