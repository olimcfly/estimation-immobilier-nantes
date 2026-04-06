<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function jsonResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Méthode non autorisée.',
    ], 405);
}

$typeBien = trim((string) ($_POST['type_bien'] ?? ''));
$ville = trim((string) ($_POST['ville'] ?? ''));
$surfaceTranche = trim((string) ($_POST['surface_tranche'] ?? ''));
$budgetTranche = trim((string) ($_POST['budget_tranche'] ?? 'non_renseigne'));

if ($typeBien === '' || $ville === '' || $surfaceTranche === '') {
    jsonResponse([
        'success' => false,
        'message' => 'Merci de renseigner tous les champs.',
    ], 422);
}

$prixParDefaut = [
    'Bordeaux' => 4500,
    'Mérignac' => 3800,
    'Pessac' => 3500,
    'Talence' => 3600,
    'Bègles' => 3200,
];

$surfacesMoyennes = [
    'lt30' => 20,
    '30_50' => 40,
    '50_80' => 65,
    '80_120' => 100,
    '120_200' => 160,
    'gt200' => 250,
];

if (!array_key_exists($surfaceTranche, $surfacesMoyennes)) {
    jsonResponse([
        'success' => false,
        'message' => 'Tranche de surface invalide.',
    ], 422);
}

$prixM2 = $prixParDefaut[$ville] ?? 3400;
$surfaceMoyenne = $surfacesMoyennes[$surfaceTranche];
$db = null;

try {
    $db = Database::getConnection();

    $stmt = $db->prepare('SELECT prix_m2 FROM villes_prix WHERE ville = :ville LIMIT 1');
    $stmt->execute(['ville' => $ville]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (is_array($row) && isset($row['prix_m2']) && is_numeric($row['prix_m2'])) {
        $prixM2 = (float) $row['prix_m2'];
    }
} catch (Throwable $exception) {
    // On conserve le prix par défaut si la DB ou la table n'est pas disponible.
}

$estimationCentrale = $surfaceMoyenne * $prixM2;
$estimationBasse = (int) round($estimationCentrale * 0.88);
$estimationHaute = (int) round($estimationCentrale * 1.12);

if ($db instanceof PDO) {
    try {
        $insert = $db->prepare(
            'INSERT INTO estimations (type_bien, ville, surface_tranche, budget_tranche, prix_m2, estimation_basse, estimation_haute, created_at)
            VALUES (:type_bien, :ville, :surface_tranche, :budget_tranche, :prix_m2, :estimation_basse, :estimation_haute, NOW())'
        );

        $insert->execute([
            'type_bien' => $typeBien,
            'ville' => $ville,
            'surface_tranche' => $surfaceTranche,
            'budget_tranche' => $budgetTranche,
            'prix_m2' => $prixM2,
            'estimation_basse' => $estimationBasse,
            'estimation_haute' => $estimationHaute,
        ]);
    } catch (Throwable $exception) {
        // Ne jamais casser la réponse utilisateur si l'insert échoue.
    }
}

jsonResponse([
    'success' => true,
    'estimation_basse' => $estimationBasse,
    'estimation_haute' => $estimationHaute,
    'prix_m2' => $prixM2,
    'ville' => $ville,
    'surface_moyenne' => $surfaceMoyenne,
]);
