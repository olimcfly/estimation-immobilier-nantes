<?php
declare(strict_types=1);

class AIRouter
{
    private array $providers;
    private int $timeout = 8;

    public function __construct()
    {
        $this->providers = [
            [
                'name' => 'openai',
                'key' => defined('AI_OPENAI_KEY') ? AI_OPENAI_KEY : '',
                'method' => 'callOpenAI',
            ],
            [
                'name' => 'anthropic',
                'key' => defined('AI_ANTHROPIC_KEY') ? AI_ANTHROPIC_KEY : '',
                'method' => 'callAnthropic',
            ],
            [
                'name' => 'perplexity',
                'key' => defined('AI_PERPLEXITY_KEY') ? AI_PERPLEXITY_KEY : '',
                'method' => 'callPerplexity',
            ],
            [
                'name' => 'mistral',
                'key' => defined('AI_MISTRAL_KEY') ? AI_MISTRAL_KEY : '',
                'method' => 'callMistral',
            ],
        ];
    }

    public function getCities(string $city, int $radius): array
    {
        foreach ($this->providers as $provider) {
            if (empty($provider['key'])) {
                continue;
            }

            try {
                $result = $this->{$provider['method']}($city, $radius, $provider['key']);

                if (!empty($result) && is_array($result)) {
                    $this->log($provider['name'], 'success', $city);
                    return [
                        'cities' => $result,
                        'source' => $provider['name'],
                    ];
                }
            } catch (\Throwable $e) {
                $this->log($provider['name'], 'failed: ' . $e->getMessage(), $city);
                continue;
            }
        }

        return [
            'cities' => $this->hardcodedFallback($city, $radius),
            'source' => 'fallback',
        ];
    }

    private function callOpenAI(string $city, int $radius, string $key): array
    {
        $payload = [
            'model' => 'gpt-4o-mini',
            'max_tokens' => 300,
            'temperature' => 0,
            'messages' => [[
                'role' => 'user',
                'content' => $this->buildPrompt($city, $radius),
            ]],
        ];

        $response = $this->httpPost(
            'https://api.openai.com/v1/chat/completions',
            $payload,
            ['Authorization: Bearer ' . $key]
        );

        $data = json_decode($response, true);
        $text = $data['choices'][0]['message']['content'] ?? '';

        return $this->parseJsonArray((string) $text);
    }

    private function callAnthropic(string $city, int $radius, string $key): array
    {
        $payload = [
            'model' => 'claude-3-haiku-20240307',
            'max_tokens' => 300,
            'messages' => [[
                'role' => 'user',
                'content' => $this->buildPrompt($city, $radius),
            ]],
        ];

        $response = $this->httpPost(
            'https://api.anthropic.com/v1/messages',
            $payload,
            [
                'x-api-key: ' . $key,
                'anthropic-version: 2023-06-01',
            ]
        );

        $data = json_decode($response, true);
        $text = $data['content'][0]['text'] ?? '';

        return $this->parseJsonArray((string) $text);
    }

    private function callPerplexity(string $city, int $radius, string $key): array
    {
        $payload = [
            'model' => 'sonar-small-online',
            'messages' => [[
                'role' => 'user',
                'content' => $this->buildPrompt($city, $radius),
            ]],
        ];

        $response = $this->httpPost(
            'https://api.perplexity.ai/chat/completions',
            $payload,
            ['Authorization: Bearer ' . $key]
        );

        $data = json_decode($response, true);
        $text = $data['choices'][0]['message']['content'] ?? '';

        return $this->parseJsonArray((string) $text);
    }

    private function callMistral(string $city, int $radius, string $key): array
    {
        $payload = [
            'model' => 'mistral-small-latest',
            'messages' => [[
                'role' => 'user',
                'content' => $this->buildPrompt($city, $radius),
            ]],
        ];

        $response = $this->httpPost(
            'https://api.mistral.ai/v1/chat/completions',
            $payload,
            ['Authorization: Bearer ' . $key]
        );

        $data = json_decode($response, true);
        $text = $data['choices'][0]['message']['content'] ?? '';

        return $this->parseJsonArray((string) $text);
    }

    private function buildPrompt(string $city, int $radius): string
    {
        return "List real cities and towns located within {$radius}km of {$city}. "
            . "France only. Return ONLY a valid JSON array of city name strings, "
            . "ordered by distance. No explanations. Example: [\"Gardanne\",\"Venelles\"] "
            . "Return between 8 and 15 cities.";
    }

    private function parseJsonArray(string $text): array
    {
        $trimmed = trim($text);

        if ($trimmed === '') {
            return [];
        }

        if (preg_match('/\[[\s\S]*\]/', $trimmed, $matches) !== 1) {
            return [];
        }

        $decoded = json_decode($matches[0], true);

        if (!is_array($decoded)) {
            return [];
        }

        $cleaned = [];
        foreach ($decoded as $item) {
            if (!is_string($item)) {
                continue;
            }
            $item = trim($item);
            if ($item !== '') {
                $cleaned[] = $item;
            }
        }

        return array_values(array_unique($cleaned));
    }

    private function httpPost(string $url, array $payload, array $headers): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json'], $headers),
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (in_array($httpCode, [402, 429, 500, 503], true)) {
            throw new \RuntimeException("HTTP {$httpCode} from {$url}");
        }

        if ($response === false || $response === '') {
            throw new \RuntimeException("Empty response from {$url}");
        }

        return (string) $response;
    }

    private function hardcodedFallback(string $city, int $radius): array
    {
        return [
            $city . ' (centre)',
            'Ville A',
            'Ville B',
            'Ville C',
        ];
    }

    private function log(string $provider, string $status, string $city): void
    {
        $line = date('Y-m-d H:i:s') . " | {$provider} | {$status} | city={$city}\n";
        @file_put_contents(__DIR__ . '/../logs/ai-router.log', $line, FILE_APPEND);
    }
}
