<?php

declare(strict_types=1);

require_once __DIR__ . '/dvf_estimator.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../classes/Settings.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $input = $method === 'POST'
        ? (json_decode((string) file_get_contents('php://input'), true) ?? [])
        : $_GET;

    $commune = trim((string) ($input['ville'] ?? $input['commune'] ?? ''));
    $typeBien = trim((string) ($input['type_bien'] ?? ''));
    $surface = (float) ($input['surface'] ?? 0);
    $months = (int) ($input['mois'] ?? DVF_DEFAULT_MONTHS);

    if ($commune === '' || $typeBien === '' || $surface <= 0) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Paramètres invalides. Attendus: ville, type_bien, surface, mois(optionnel).',
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $estimator = new DVFEstimator();
    $result = $estimator->estimate($commune, $typeBien, $surface, $months);

    if (($result['success'] ?? false) === true && isset($result['estimation']) && is_array($result['estimation'])) {
        $sourceLabel = trim((string) Settings::get('dvf_source_label', 'DVF (Etalab)'));
        $result['estimation']['source'] = $sourceLabel !== '' ? $sourceLabel : 'DVF (Etalab)';
    }

    if (($result['success'] ?? false) === false) {
        http_response_code(404);
    }

    echo json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne lors de l\'estimation DVF.',
        'error' => $e->getMessage(),
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
}
