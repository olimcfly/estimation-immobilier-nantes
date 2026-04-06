<?php

declare(strict_types=1);

/**
 * Minimal installer landing page.
 *
 * This page intentionally avoids framework/bootstrap dependencies so that
 * `/install/index.php` never crashes with a fatal error when the app is not
 * fully configured yet.
 */

http_response_code(200);
header('Content-Type: text/html; charset=UTF-8');

$checks = [
    'PHP version >= 8.1' => version_compare(PHP_VERSION, '8.1.0', '>='),
    'PDO extension loaded' => extension_loaded('PDO'),
    'JSON extension loaded' => extension_loaded('json'),
];

?><!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Installation — Estimation Immobilier Nantes</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 2rem; color: #1f2937; }
    .ok { color: #166534; }
    .ko { color: #991b1b; }
    code { background: #f3f4f6; padding: .15rem .35rem; border-radius: 4px; }
  </style>
</head>
<body>
  <h1>Installation</h1>
  <p>La page d'installation est accessible.</p>
  <p><a href="/install/install.php">Ouvrir le script d'installation de l'application</a></p>

  <h2>Vérifications système</h2>
  <ul>
    <?php foreach ($checks as $label => $status): ?>
      <li class="<?= $status ? 'ok' : 'ko' ?>">
        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
        — <?= $status ? 'OK' : 'À corriger' ?>
      </li>
    <?php endforeach; ?>
  </ul>

  <p>Version PHP détectée : <code><?= htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8') ?></code></p>
</body>
</html>
