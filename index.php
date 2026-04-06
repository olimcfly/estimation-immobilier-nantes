<?php

declare(strict_types=1);

/**
 * Safe homepage placeholder.
 *
 * The repository currently ships only setup/audit utilities. This file avoids
 * HTTP 500 on `/` by serving a minimal page with links to available entrypoints.
 */

http_response_code(200);
header('Content-Type: text/html; charset=UTF-8');

?><!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Estimation Immobilier Nantes</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 2rem; color: #1f2937; }
    a { color: #0f4c81; }
    .card { max-width: 760px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem 1.25rem; }
  </style>
</head>
<body>
  <h1>Estimation Immobilier Nantes</h1>
  <div class="card">
    <p>Le point d'entrée principal est disponible.</p>
    <p>Si vous configurez l'application, ouvrez ensuite la page d'installation: <a href="/install/index.php">/install/index.php</a>.</p>
  </div>
</body>
</html>
