<?php
declare(strict_types=1);

class PageGenerator
{
    private KeywordManager $keywordManager;
    private string $templateDir;

    public function __construct(KeywordManager $keywordManager, string $templateDir)
    {
        $this->keywordManager = $keywordManager;
        $this->templateDir = rtrim($templateDir, '/');
    }

    public function generateEstimationPage(string $typeBien, string $ville, ?string $quartier = null): string
    {
        $title = $this->keywordManager->generateTitle($typeBien, $ville, $quartier);
        $keywords = $this->keywordManager->getKeywordsForPage($typeBien, $ville, $quartier);
        $h1 = 'Estimation ' . $typeBien . ' à ' . ($quartier ? ($quartier . ' (' . $ville . ')') : $ville);

        $metaDescription = $this->generateMetaDescription($keywords);
        $keywordList = implode(', ', array_column($keywords, 'keyword'));

        $templatePath = $this->templateDir . '/seo/estimation.php';

        if (!is_file($templatePath)) {
            throw new RuntimeException('Template SEO introuvable: ' . $templatePath);
        }

        $template = (string) file_get_contents($templatePath);

        return str_replace(
            ['{{TITLE}}', '{{H1}}', '{{META_DESCRIPTION}}', '{{KEYWORDS}}'],
            [$title, $h1, $metaDescription, $keywordList],
            $template
        );
    }

    /**
     * @param array<int, array<string, mixed>> $keywords
     */
    private function generateMetaDescription(array $keywords): string
    {
        $primary = isset($keywords[0]['keyword']) ? (string) $keywords[0]['keyword'] : '';

        return 'Obtenez une estimation gratuite de votre bien (' . $primary . ') en 30 secondes. Basée sur les données DVF et l\'IA.';
    }
}
