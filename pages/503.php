<?php
http_response_code(503);
?><!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance en cours</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f3f4f6;color:#111827;padding:2rem}
        .box{max-width:680px;margin:2rem auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:2rem}
        h1{margin-top:0}
    </style>
</head>
<body>
<div class="box">
    <h1>Maintenance</h1>
    <p><?= htmlspecialchars($maintenanceMessage ?? 'Le service est momentanément indisponible.', ENT_QUOTES, 'UTF-8') ?></p>
</div>
</body>
</html>
