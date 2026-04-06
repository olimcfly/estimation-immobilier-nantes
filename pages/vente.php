<?php
declare(strict_types=1);

$type = (string) ($_GET['type'] ?? 'bien');
$ville = (string) ($_GET['ville'] ?? 'bordeaux');

http_response_code(200);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendre <?= htmlspecialchars($type, ENT_QUOTES); ?> à <?= htmlspecialchars($ville, ENT_QUOTES); ?></title>
</head>
<body>
    <h1>Vendre <?= htmlspecialchars($type, ENT_QUOTES); ?> à <?= htmlspecialchars($ville, ENT_QUOTES); ?></h1>
</body>
</html>
