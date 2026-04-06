<?php

declare(strict_types=1);

final class VendeurManager
{
    public const STATUTS = ['nouveau', 'contact_etabli', 'visite_planifiee', 'mandat_signe', 'perdu'];

    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFromEstimation(array $data): int
    {
        $sql = 'INSERT INTO crm_vendeurs
            (source, campagne_ads, nom, email, telephone, type_bien, ville, quartier, surface, nb_pieces, estimation_min, estimation_max, date_estimation)
            VALUES (:source, :campagne_ads, :nom, :email, :telephone, :type_bien, :ville, :quartier, :surface, :nb_pieces, :estimation_min, :estimation_max, NOW())';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':source' => $this->sanitizeSource((string) ($data['source'] ?? 'site_web')),
            ':campagne_ads' => $this->nullableString($data['campagne_ads'] ?? null),
            ':nom' => $this->requiredString($data['nom'] ?? null, 'nom'),
            ':email' => $this->requiredString($data['email'] ?? null, 'email'),
            ':telephone' => $this->nullableString($data['telephone'] ?? null),
            ':type_bien' => $this->sanitizeTypeBien((string) ($data['type_bien'] ?? '')),
            ':ville' => $this->requiredString($data['ville'] ?? null, 'ville'),
            ':quartier' => $this->nullableString($data['quartier'] ?? null),
            ':surface' => $this->nullableInt($data['surface'] ?? null),
            ':nb_pieces' => $this->nullableInt($data['nb_pieces'] ?? null),
            ':estimation_min' => $this->nullableFloat($data['estimation_min'] ?? null),
            ':estimation_max' => $this->nullableFloat($data['estimation_max'] ?? null),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateStatut(int $vendeurId, string $statut): bool
    {
        if (!in_array($statut, self::STATUTS, true)) {
            throw new InvalidArgumentException('Statut vendeur invalide.');
        }

        $stmt = $this->db->prepare('UPDATE crm_vendeurs SET statut = :statut, updated_at = NOW() WHERE id = :id');

        return $stmt->execute([
            ':statut' => $statut,
            ':id' => $vendeurId,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getLeadsByStatut(string $statut, int $limit = 100): array
    {
        if (!in_array($statut, self::STATUTS, true)) {
            throw new InvalidArgumentException('Statut vendeur invalide.');
        }

        $stmt = $this->db->prepare('SELECT * FROM crm_vendeurs WHERE statut = :statut ORDER BY updated_at DESC LIMIT :limit');
        $stmt->bindValue(':statut', $statut, PDO::PARAM_STR);
        $stmt->bindValue(':limit', max(1, min($limit, 500)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, int>
     */
    public function getKpiByStatut(): array
    {
        $stmt = $this->db->query('SELECT statut, COUNT(*) AS total FROM crm_vendeurs GROUP BY statut');
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $kpis = array_fill_keys(self::STATUTS, 0);

        foreach ($rows as $row) {
            $statut = (string) ($row['statut'] ?? '');
            if (array_key_exists($statut, $kpis)) {
                $kpis[$statut] = (int) ($row['total'] ?? 0);
            }
        }

        return $kpis;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentLeads(int $limit = 50): array
    {
        $stmt = $this->db->prepare('SELECT * FROM crm_vendeurs ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', max(1, min($limit, 200)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function sanitizeSource(string $source): string
    {
        $allowed = ['site_web', 'google_ads', 'facebook', 'bouche_a_oreille'];

        return in_array($source, $allowed, true) ? $source : 'site_web';
    }

    private function sanitizeTypeBien(string $typeBien): string
    {
        $allowed = ['appartement', 'maison', 'loft', 'terrain'];
        if (!in_array($typeBien, $allowed, true)) {
            throw new InvalidArgumentException('Type de bien vendeur invalide.');
        }

        return $typeBien;
    }

    /**
     * @param mixed $value
     */
    private function requiredString($value, string $field): string
    {
        $stringValue = trim((string) $value);
        if ($stringValue === '') {
            throw new InvalidArgumentException(sprintf('Le champ %s est requis.', $field));
        }

        return $stringValue;
    }

    /**
     * @param mixed $value
     */
    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $stringValue = trim((string) $value);

        return $stringValue === '' ? null : $stringValue;
    }

    /**
     * @param mixed $value
     */
    private function nullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * @param mixed $value
     */
    private function nullableFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
