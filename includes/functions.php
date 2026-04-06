<?php

declare(strict_types=1);

/**
 * Vérifie si des coordonnées sont dans la zone de couverture configurée.
 *
 * @return bool|null true/false si connu, null si coordonnées inconnues
 */
function estDansLaZone(?float $lat, ?float $lng): ?bool
{
    if ($lat === null || $lng === null) {
        return null;
    }

    $distance = haversineDistance((float) CITY_LAT, (float) CITY_LNG, $lat, $lng);

    return $distance <= (float) CITY_RADIUS_KM;
}

/**
 * Distance entre deux points GPS en kilomètres (formule de Haversine).
 */
function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $earthRadiusKm = 6371.0;

    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);

    $a = sin($dLat / 2) * sin($dLat / 2)
        + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
        * sin($dLng / 2) * sin($dLng / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadiusKm * $c;
}

/**
 * Distance d'un bien par rapport au centre configuré.
 */
function getDistanceDuCentre(float $lat, float $lng): float
{
    return round(haversineDistance((float) CITY_LAT, (float) CITY_LNG, $lat, $lng), 1);
}

/**
 * Obtenir le prix m² le plus pertinent pour des coordonnées.
 *
 * @return array{prix_m2: float|int, ville: string, tendance: float|int|null, nb_transactions: int|null, fiabilite: string}
 */
function getPrixM2PourLocalisation(float $lat, float $lng, string $typeBien): array
{
    $db = Database::getConnection();

    // Chercher la ville la plus proche dans notre table.
    $sql = "SELECT *,
            (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?))
            + sin(radians(?)) * sin(radians(lat)))) AS distance
            FROM villes_prix
            HAVING distance <= 15
            ORDER BY distance ASC
            LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute([$lat, $lng, $lat]);
    $ville = $stmt->fetch(PDO::FETCH_ASSOC);

    $fiabilite = 'haute';

    if (!$ville) {
        // Aucune ville trouvée dans 15km : utiliser la ville centre (distance_centre pré-calculée).
        $stmt2 = $db->prepare("SELECT * FROM villes_prix ORDER BY distance_centre ASC LIMIT 1");
        $stmt2->execute();
        $ville = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];
        $fiabilite = 'moyenne';
    }

    $colonneType = 'prix_m2_' . preg_replace('/[^a-z0-9_]/i', '', $typeBien);

    return [
        'prix_m2' => $ville[$colonneType] ?? 3500,
        'ville' => $ville['ville'] ?? (defined('CITY_NAME') ? CITY_NAME : 'Zone configurée'),
        'tendance' => $ville['tendance_annuelle'] ?? null,
        'nb_transactions' => isset($ville['nb_transactions']) ? (int) $ville['nb_transactions'] : null,
        'fiabilite' => $fiabilite,
    ];
}
