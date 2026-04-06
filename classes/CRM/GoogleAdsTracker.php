<?php

declare(strict_types=1);

final class GoogleAdsTracker
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function trackKeyword(int $vendeurId, ?string $gclid, string $keyword, string $campagne): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO crm_ads_keywords (vendeur_id, keyword, campagne, gclid)
            VALUES (:vendeur_id, :keyword, :campagne, :gclid)'
        );

        $stmt->execute([
            ':vendeur_id' => $vendeurId,
            ':keyword' => trim($keyword),
            ':campagne' => trim($campagne),
            ':gclid' => $gclid !== null && $gclid !== '' ? trim($gclid) : null,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCampaignPerformance(): array
    {
        $sql = "SELECT
                c.campagne,
                COUNT(DISTINCT v.id) AS leads,
                SUM(CASE WHEN v.statut = 'mandat_signe' THEN 1 ELSE 0 END) AS mandats,
                ROUND(
                    CASE
                        WHEN COUNT(DISTINCT v.id) = 0 THEN 0
                        ELSE (SUM(CASE WHEN v.statut = 'mandat_signe' THEN 1 ELSE 0 END) / COUNT(DISTINCT v.id)) * 100
                    END,
                2) AS taux_conversion
            FROM crm_ads_keywords c
            JOIN crm_vendeurs v ON c.vendeur_id = v.id
            GROUP BY c.campagne
            ORDER BY mandats DESC, leads DESC";

        $stmt = $this->db->query($sql);

        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
