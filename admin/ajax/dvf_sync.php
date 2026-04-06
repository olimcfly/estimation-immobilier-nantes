<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../classes/Settings.php';
require_once __DIR__ . '/../../dvf-estimation/download_dvf.php';
require_once __DIR__ . '/../../dvf-estimation/parse_dvf.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $tokenHeader = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $tokenSession = (string) ($_SESSION['csrf_token'] ?? '');
    if ($tokenSession === '' || !hash_equals($tokenSession, $tokenHeader)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sourceUrl = trim((string) Settings::get('dvf_source_url', getDvfSourceUrl()));
    $downloadResult = syncDvfSource($sourceUrl);
    $parseResult = parseDvfFile();

    echo json_encode([
        'success' => true,
        'message' => 'Source DVF téléchargée et parsée avec succès.',
        'download' => $downloadResult,
        'parse' => $parseResult,
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Échec de la synchronisation DVF.',
        'error' => $e->getMessage(),
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
}
