<?php
declare(strict_types=1);

$options = getopt('', ['type:', 'ville:', 'quartier::']);
$type = (string) ($options['type'] ?? 'appartement');
$ville = (string) ($options['ville'] ?? 'bordeaux');
$quartier = isset($options['quartier']) ? (string) $options['quartier'] : null;

$path = '/estimation/' . $type . '/' . $ville . ($quartier ? '/' . $quartier : '') . '/';
$logPath = __DIR__ . '/../../var/logs/generation.log';
$line = sprintf("[%s] generated %s\n", gmdate('c'), $path);

file_put_contents($logPath, $line, FILE_APPEND);
echo "Generated route: {$path}" . PHP_EOL;
