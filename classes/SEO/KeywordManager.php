<?php
declare(strict_types=1);

class KeywordManager
{
    private PDO $db;

    /**
     * @var array<string, mixed>
     */
    private array $siteConfig;

    /**
     * @param array<string, mixed> $siteConfig
     */
    public function __construct(PDO $db, array $siteConfig)
    {
        $this->db = $db;
        $this->siteConfig = $siteConfig;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKeywordsForPage(string $typeBien, string $ville, ?string $quartier = null): array
    {
        $query = 'SELECT keyword, volume_recherche, intent
                  FROM seo_keywords
                  WHERE type_bien = :type_bien
                  AND ville = :ville';

        $params = [
            ':type_bien' => $this->normalizeSlug($typeBien),
            ':ville' => $this->normalizeSlug($ville),
        ];

        if ($quartier !== null && $quartier !== '') {
            $query .= ' AND quartier = :quartier';
            $params[':quartier'] = $this->normalizeSlug($quartier);
        }

        $query .= ' ORDER BY volume_recherche DESC, keyword ASC';

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (is_array($rows) && $rows !== []) {
            return $rows;
        }

        return $this->buildFallbackKeywords($typeBien, $ville, $quartier);
    }

    public function generateTitle(string $typeBien, string $ville, ?string $quartier = null): string
    {
        $keywords = $this->getKeywordsForPage($typeBien, $ville, $quartier);
        $primaryKeyword = isset($keywords[0]['keyword']) ? (string) $keywords[0]['keyword'] : ($typeBien . ' ' . $ville);

        $year = (new DateTimeImmutable('now'))->format('Y');
        $siteName = (string) ($this->siteConfig['site_name'] ?? 'Skyline Bordeaux');

        return ucfirst($primaryKeyword) . ' – Prix m² ' . $year . ' | ' . $siteName;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildFallbackKeywords(string $typeBien, string $ville, ?string $quartier): array
    {
        $keyword = 'estimation ' . strtolower(trim($typeBien)) . ' ' . strtolower(trim($ville));

        if ($quartier !== null && trim($quartier) !== '') {
            $keyword .= ' ' . strtolower(trim($quartier));
        }

        return [[
            'keyword' => trim($keyword),
            'volume_recherche' => 0,
            'intent' => 'fallback',
        ]];
    }

    private function normalizeSlug(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^\p{L}0-9\-\s]/u', '', $value) ?? '';
        $value = preg_replace('/\s+/', '-', $value) ?? '';

        return trim($value, '-');
    }
}
