<?php
declare(strict_types=1);

class SchemaMarkup
{
    /**
     * @param array<string, mixed> $pageData
     * @return array<string, mixed>
     */
    public function buildLocalBusiness(array $pageData): array
    {
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'EstimIA';
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $phone = defined('SITE_PHONE') ? SITE_PHONE : '';

        return [
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateAgent',
            'name' => $siteName,
            'url' => $baseUrl,
            'telephone' => $phone,
            'areaServed' => $pageData['zone_label'] ?? 'Bordeaux',
            'description' => $pageData['meta_description'] ?? '',
        ];
    }

    /**
     * @param array<int, array<string, string>> $faqItems
     * @return array<string, mixed>
     */
    public function buildFaqPage(array $faqItems): array
    {
        $mainEntity = [];

        foreach ($faqItems as $item) {
            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));

            if ($question === '' || $answer === '') {
                continue;
            }

            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $answer,
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];
    }
}
