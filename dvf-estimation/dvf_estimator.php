<?php

declare(strict_types=1);

require_once __DIR__ . '/config_dvf.php';

/**
 * Estimateur immobilier basé sur les transactions DVF.
 */
final class DVFEstimator
{
    /**
     * @var list<array<string, int|float|string>>
     */
    private array $transactions = [];

    public function __construct(
        private readonly string $jsonPath = DVF_JSON_PATH,
        private readonly string $cachePath = DVF_CACHE_PATH,
    ) {
    }

    /**
     * Charge les transactions depuis le cache ou le JSON source.
     *
     * @throws RuntimeException
     */
    public function load(): void
    {
        if (!is_file($this->jsonPath)) {
            throw new RuntimeException(sprintf('Fichier JSON DVF introuvable: %s', $this->jsonPath));
        }

        $jsonMtime = (int) filemtime($this->jsonPath);

        if (is_file($this->cachePath)) {
            $cacheMtime = (int) filemtime($this->cachePath);
            if ($cacheMtime >= $jsonMtime) {
                $cacheData = require $this->cachePath;
                if (is_array($cacheData)) {
                    $this->transactions = $cacheData;
                    return;
                }
            }
        }

        $raw = file_get_contents($this->jsonPath);
        if ($raw === false) {
            throw new RuntimeException('Impossible de lire le fichier JSON DVF.');
        }

        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new RuntimeException('Format JSON DVF invalide.');
        }

        $this->transactions = $decoded;
        $this->persistCache();
    }

    /**
     * Calcule une estimation de prix.
     *
     * @return array<string, int|float|string|bool>
     */
    public function estimate(string $commune, string $typeBien, float $surface, int $months = DVF_DEFAULT_MONTHS): array
    {
        if ($surface <= 0) {
            throw new InvalidArgumentException('La surface doit être supérieure à zéro.');
        }

        if ($months <= 0) {
            throw new InvalidArgumentException('La période doit être positive.');
        }

        if ($this->transactions === []) {
            $this->load();
        }

        $communeNorm = mb_strtoupper(trim($commune));
        $typeNorm = mb_strtolower(trim($typeBien));
        $allowedTypes = array_values(DVF_TYPE_LOCAL_MAP);

        if (!in_array($typeNorm, $allowedTypes, true)) {
            throw new InvalidArgumentException('Type de bien invalide (maison|appartement).');
        }

        $cutoff = (new DateTimeImmutable('now'))->modify(sprintf('-%d months', $months));

        $prixM2 = [];
        foreach ($this->transactions as $transaction) {
            $txCommune = (string) ($transaction['commune'] ?? '');
            $txType = (string) ($transaction['type_local'] ?? '');
            $txDate = (string) ($transaction['date_mutation'] ?? '');
            $txPrixM2 = (float) ($transaction['prix_m2'] ?? 0);

            if ($txCommune !== $communeNorm || $txType !== $typeNorm || $txPrixM2 <= 0) {
                continue;
            }

            $date = DateTimeImmutable::createFromFormat('Y-m-d', $txDate);
            if (!$date instanceof DateTimeImmutable || $date < $cutoff) {
                continue;
            }

            $prixM2[] = $txPrixM2;
        }

        $count = count($prixM2);
        if ($count === 0) {
            return [
                'success' => false,
                'message' => 'Aucune transaction correspondante sur la période demandée.',
                'estimation' => null,
            ];
        }

        sort($prixM2);

        $avgM2 = array_sum($prixM2) / $count;
        $p10 = $prixM2[(int) floor(($count - 1) * 0.10)];
        $p90 = $prixM2[(int) floor(($count - 1) * 0.90)];

        $prixMin = (int) round($p10 * $surface);
        $prixMax = (int) round($p90 * $surface);
        $prixMoyen = (int) round($avgM2);

        return [
            'success' => true,
            'estimation' => [
                'prix_min' => $prixMin,
                'prix_max' => $prixMax,
                'prix_m2_moyen' => $prixMoyen,
                'nombre_transactions' => $count,
                'source' => 'DVF (Etalab)',
                'periode' => sprintf('%d derniers mois', $months),
            ],
            'message' => sprintf('Estimation basée sur %d transactions récentes.', $count),
        ];
    }

    /**
     * Écrit un cache PHP pour éviter le décodage JSON à chaque requête.
     */
    private function persistCache(): void
    {
        $export = var_export($this->transactions, true);
        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . $export . ";\n";
        file_put_contents($this->cachePath, $content, LOCK_EX);
    }
}
