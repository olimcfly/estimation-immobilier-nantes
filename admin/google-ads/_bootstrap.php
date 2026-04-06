<?php

declare(strict_types=1);

function gaFetchStrategies(PDO $db, bool $activeOnly = false): array
{
    $sql = 'SELECT id, name, description, color, is_active, created_at FROM google_ads_strategies';
    if ($activeOnly) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY name';

    $stmt = $db->query($sql);

    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

function gaFetchCities(PDO $db): array
{
    $stmt = $db->query('SELECT id, ville, population FROM villes_prix ORDER BY population DESC, ville ASC');

    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

function gaAwarenessLabel(string $level): string
{
    return match ($level) {
        'hot' => 'Chaud',
        'warm' => 'Tiède',
        'cold' => 'Froid',
        default => ucfirst($level),
    };
}

function gaAwarenessColor(string $level): string
{
    return match ($level) {
        'hot' => '#ef4444',
        'warm' => '#f59e0b',
        'cold' => '#3b82f6',
        default => '#6b7280',
    };
}
