<?php

class Webhook
{
    /**
     * Envoyer un webhook pour un événement
     */
    public static function fire(string $event, array $data): bool
    {
        $url = Settings::get('webhook_url');
        if (empty($url)) {
            return false;
        }

        $payload = [
            'event' => $event,
            'timestamp' => date('c'),
            'site' => SITE_NAME,
            'city' => CITY_NAME,
            'data' => $data,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            appLog('Webhook payload JSON encoding failed for event: ' . $event, 'ERROR', 'webhooks');
            return false;
        }

        // Signature HMAC pour sécuriser le webhook
        $secret = defined('WEBHOOK_SECRET') ? WEBHOOK_SECRET : 'estimia_webhook';
        $signature = hash_hmac('sha256', $json, $secret);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-EstimIA-Event: ' . $event,
                'X-EstimIA-Signature: ' . $signature,
                'User-Agent: EstimIA-Webhook/1.0',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Logger
        $status = ($httpCode >= 200 && $httpCode < 300) ? 'success' : 'failed';
        appLog(
            "Webhook [$event] → $url → HTTP $httpCode" . ($error ? " (Error: $error)" : ''),
            $status === 'success' ? 'INFO' : 'ERROR',
            'webhooks'
        );

        // Enregistrer en DB
        $db = Database::getConnection();
        $db->prepare(
            'INSERT INTO webhook_logs (event, url, payload, http_code, response, status)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([$event, $url, $json, $httpCode, substr((string) $response, 0, 1000), $status]);

        return $status === 'success';
    }

    /**
     * Envoyer webhook nouvelle estimation
     */
    public static function newEstimation(array $lead): bool
    {
        return self::fire('estimation.created', [
            'id' => $lead['id'] ?? null,
            'type' => $lead['type_estimation'] ?? null,
            'nom' => trim(($lead['prenom'] ?? '') . ' ' . ($lead['nom'] ?? '')),
            'email' => $lead['email'] ?? null,
            'telephone' => $lead['telephone'] ?? null,
            'adresse' => $lead['adresse'] ?? null,
            'ville' => $lead['ville'] ?? null,
            'type_bien' => $lead['type_bien'] ?? null,
            'surface' => $lead['surface'] ?? null,
            'prix_estime' => $lead['prix_estime'] ?? null,
            'lead_score' => $lead['lead_score'] ?? null,
        ]);
    }

    /**
     * Envoyer webhook nouveau RDV
     */
    public static function newRdv(array $lead, array $rdvData): bool
    {
        return self::fire('rdv.created', [
            'estimation_id' => $lead['id'] ?? null,
            'nom' => trim(($lead['prenom'] ?? '') . ' ' . ($lead['nom'] ?? '')),
            'email' => $lead['email'] ?? null,
            'telephone' => $lead['telephone'] ?? null,
            'date_rdv' => $rdvData['date'] ?? null,
            'heure_rdv' => $rdvData['heure'] ?? null,
            'ville' => $lead['ville'] ?? null,
            'prix_estime' => $lead['prix_estime'] ?? null,
        ]);
    }

    /**
     * Envoyer webhook changement de statut
     */
    public static function statusChanged(array $lead, string $oldStatus, string $newStatus): bool
    {
        return self::fire('lead.status_changed', [
            'estimation_id' => $lead['id'] ?? null,
            'nom' => trim(($lead['prenom'] ?? '') . ' ' . ($lead['nom'] ?? '')),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }
}
