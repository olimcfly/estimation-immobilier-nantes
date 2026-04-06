<?php
declare(strict_types=1);

/** @var array<int, array<string, mixed>> $schemaBlocks */
$schemaBlocks = $schemaBlocks ?? [];

foreach ($schemaBlocks as $schema) {
    echo '<script type="application/ld+json">';
    echo json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo '</script>';
}
