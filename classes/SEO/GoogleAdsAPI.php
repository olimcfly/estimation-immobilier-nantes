<?php
declare(strict_types=1);

class GoogleAdsAPI
{
    private string $clientId;
    private string $conversionId;

    /**
     * @param array<string, mixed> $siteConfig
     */
    public function __construct(array $siteConfig)
    {
        $ads = isset($siteConfig['google_ads']) && is_array($siteConfig['google_ads'])
            ? $siteConfig['google_ads']
            : [];

        $this->clientId = (string) ($ads['client_id'] ?? '');
        $this->conversionId = (string) ($ads['conversion_id'] ?? '');
    }

    public function isConfigured(): bool
    {
        return $this->clientId !== '' && $this->conversionId !== '';
    }

    /**
     * @param array<string, mixed> $event
     * @return array<string, mixed>
     */
    public function buildConversionEvent(array $event): array
    {
        return [
            'client_id' => $this->clientId,
            'conversion_id' => $this->conversionId,
            'event_name' => (string) ($event['event_name'] ?? 'lead_submit'),
            'gclid' => (string) ($event['gclid'] ?? ''),
            'value' => (float) ($event['value'] ?? 0),
            'currency' => (string) ($event['currency'] ?? 'EUR'),
            'occurred_at' => gmdate('c'),
        ];
    }
}
