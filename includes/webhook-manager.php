<?php
/**
 * Système de webhooks (abonnements + dispatch).
 */

class WebhookManager
{
    private string $storagePath;

    public function __construct(?string $storagePath = null)
    {
        $this->storagePath = $storagePath ?? __DIR__ . '/../logs/webhooks.json';

        $dir = dirname($this->storagePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($this->storagePath)) {
            file_put_contents($this->storagePath, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    public function register(string $event, string $url, ?string $secret = null): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $webhooks = $this->load();
        $webhooks[] = [
            'id' => bin2hex(random_bytes(8)),
            'event' => $event,
            'url' => $url,
            'secret' => $secret,
            'created_at' => date(DATE_ATOM),
        ];

        return $this->save($webhooks);
    }

    public function dispatch(string $event, array $payload): array
    {
        $webhooks = array_values(array_filter(
            $this->load(),
            static fn(array $hook): bool => ($hook['event'] ?? '') === $event
        ));

        $results = [];
        foreach ($webhooks as $hook) {
            $results[] = $this->sendToWebhook($hook, $event, $payload);
        }

        return $results;
    }

    public function list(?string $event = null): array
    {
        $webhooks = $this->load();
        if ($event === null) {
            return $webhooks;
        }

        return array_values(array_filter(
            $webhooks,
            static fn(array $hook): bool => ($hook['event'] ?? '') === $event
        ));
    }

    private function sendToWebhook(array $hook, string $event, array $payload): array
    {
        $body = json_encode([
            'event' => $event,
            'sent_at' => date(DATE_ATOM),
            'payload' => $payload,
        ], JSON_UNESCAPED_UNICODE);

        $headers = [
            'Content-Type: application/json',
            'X-Webhook-Event: ' . $event,
        ];

        if (!empty($hook['secret'])) {
            $signature = hash_hmac('sha256', $body ?: '', (string) $hook['secret']);
            $headers[] = 'X-Webhook-Signature: sha256=' . $signature;
        }

        $ch = curl_init((string) $hook['url']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $ok = $error === '' && $status >= 200 && $status < 300;

        file_put_contents(
            __DIR__ . '/../logs/webhook-delivery.log',
            sprintf(
                "%s [%s] %s status=%d error=%s\n",
                date('Y-m-d H:i:s'),
                $event,
                (string) $hook['url'],
                $status,
                $error !== '' ? $error : '-'
            ),
            FILE_APPEND | LOCK_EX
        );

        return [
            'id' => $hook['id'] ?? null,
            'url' => $hook['url'] ?? null,
            'status' => $status,
            'success' => $ok,
            'error' => $error !== '' ? $error : null,
            'response' => $response,
        ];
    }

    private function load(): array
    {
        $raw = file_get_contents($this->storagePath);
        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function save(array $webhooks): bool
    {
        return file_put_contents(
            $this->storagePath,
            json_encode($webhooks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        ) !== false;
    }
}
