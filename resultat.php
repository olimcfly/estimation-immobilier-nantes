<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$adresse = trim((string) ($_POST['adresse'] ?? ''));
$typeBien = (string) ($_POST['type_bien'] ?? 'appartement');
$latitude = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? (float) $_POST['latitude'] : null;
$longitude = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? (float) $_POST['longitude'] : null;

$prixM2Data = null;
$estimation = null;
$dansZone = null;
$distanceCentre = null;

if ($latitude !== null && $longitude !== null) {
    $prixM2Data = getPrixM2PourLocalisation($latitude, $longitude, $typeBien);
    // Exemple de calcul simple d'estimation (base 60 m²).
    $estimation = (float) ($prixM2Data['prix_m2'] ?? 3500) * 60;

    $dansZone = estDansLaZone($latitude, $longitude);
    $distanceCentre = getDistanceDuCentre($latitude, $longitude);
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Résultat estimation - EstimIA</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 860px; margin: 2rem auto; padding: 0 1rem; }
        .card { border: 1px solid #e3e3e3; border-radius: 8px; padding: 1rem; margin-top: 1rem; }
        .warn { background: #fff8db; border: 1px solid #f0d97a; color: #6f5a12; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <h1>Résultat de votre estimation</h1>

    <div class="card">
        <p><strong>Adresse :</strong> <?= htmlspecialchars($adresse, ENT_QUOTES) ?></p>
        <p><strong>Type :</strong> <?= htmlspecialchars($typeBien, ENT_QUOTES) ?></p>
        <?php if ($estimation !== null): ?>
            <p><strong>Estimation :</strong> <?= number_format($estimation, 0, ',', ' ') ?> €</p>
            <p class="muted">Basée sur <?= number_format((float) $prixM2Data['prix_m2'], 0, ',', ' ') ?> €/m² à <?= htmlspecialchars((string) $prixM2Data['ville'], ENT_QUOTES) ?>.</p>
            <?php if ($dansZone === false): ?>
                <div class="card warn">
                    ⚠️ Cette adresse est située à <?= number_format((float) $distanceCentre, 1, ',', ' ') ?> km de notre zone d'expertise.
                    L'estimation fonctionne, mais sa précision peut être réduite hors du rayon de <?= (float) CITY_RADIUS_KM ?> km.
                </div>
            <?php elseif ($dansZone === null): ?>
                <div class="card warn">
                    ⚠️ Les coordonnées de localisation sont incomplètes : la vérification de zone n'a pas pu être effectuée.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Impossible de calculer l'estimation sans coordonnées GPS.</p>
        <?php endif; ?>
    </div>
</body>
</html>
