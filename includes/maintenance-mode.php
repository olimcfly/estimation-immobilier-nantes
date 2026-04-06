<?php
/**
 * Mode maintenance.
 *
 * Utilisation:
 * - définir MAINTENANCE_MODE à true pour forcer la maintenance
 * - ou créer/supprimer le fichier maintenance.flag
 */

function maintenanceFlagPath(): string
{
    return __DIR__ . '/../maintenance.flag';
}

function isMaintenanceEnabled(): bool
{
    if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE === true) {
        return true;
    }

    return file_exists(maintenanceFlagPath());
}

function enableMaintenanceMode(?string $message = null): bool
{
    $payload = [
        'enabled_at' => date(DATE_ATOM),
        'message' => $message ?? 'Le site est temporairement indisponible pour maintenance.',
    ];

    return file_put_contents(
        maintenanceFlagPath(),
        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    ) !== false;
}

function disableMaintenanceMode(): bool
{
    $flag = maintenanceFlagPath();
    if (!file_exists($flag)) {
        return true;
    }

    return unlink($flag);
}

function canBypassMaintenance(): bool
{
    if (PHP_SAPI === 'cli') {
        return true;
    }

    $token = defined('MAINTENANCE_BYPASS_TOKEN') ? MAINTENANCE_BYPASS_TOKEN : null;
    if ($token !== null && isset($_GET['maintenance_bypass']) && hash_equals($token, (string) $_GET['maintenance_bypass'])) {
        return true;
    }

    return false;
}

function maintenanceData(): array
{
    $flag = maintenanceFlagPath();
    if (!file_exists($flag)) {
        return [];
    }

    $raw = file_get_contents($flag);
    if ($raw === false) {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function handleMaintenanceMode(): void
{
    if (!isMaintenanceEnabled() || canBypassMaintenance()) {
        return;
    }

    http_response_code(503);
    $maintenance = maintenanceData();
    $maintenanceMessage = $maintenance['message'] ?? 'Le site est en maintenance. Merci de réessayer dans quelques minutes.';

    include __DIR__ . '/../pages/503.php';
    exit;
}
