<?php
declare(strict_types=1);

$slug = (string) ($_GET['slug'] ?? '');

http_response_code(200);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog immobilier | <?= htmlspecialchars($slug !== '' ? $slug : 'Bordeaux', ENT_QUOTES); ?></title>
</head>
<body>
    <h1>Article blog : <?= htmlspecialchars($slug !== '' ? $slug : 'bordeaux', ENT_QUOTES); ?></h1>
</body>
</html>
