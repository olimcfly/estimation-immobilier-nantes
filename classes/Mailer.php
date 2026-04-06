<?php

class Mailer
{
    private string $fromEmail;
    private string $fromName;
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUser;
    private string $smtpPass;

    public function __construct()
    {
        $this->fromEmail = SMTP_FROM;
        $this->fromName = SITE_NAME;
        $this->smtpHost = SMTP_HOST;
        $this->smtpPort = SMTP_PORT;
        $this->smtpUser = SMTP_USER;
        $this->smtpPass = SMTP_PASS;
    }

    /**
     * Envoyer un email avec template HTML.
     */
    public function send(string $to, string $subject, string $template, array $data = []): bool
    {
        $html = $this->renderTemplate($template, $data + ['subject' => $subject]);

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . $this->fromEmail,
            'X-Mailer: EstimIA',
        ];

        $success = mail($to, $subject, $html, implode("\r\n", $headers));

        $this->logEmail($to, $subject, $template, $success);

        return $success;
    }

    /**
     * Rendre un template avec les données.
     */
    private function renderTemplate(string $template, array $data): string
    {
        $data['site_name'] = SITE_NAME;
        $data['site_url'] = SITE_URL;
        $data['site_color'] = SITE_COLOR;
        $data['site_phone'] = SITE_PHONE ?? '';
        $data['year'] = date('Y');
        $data['city'] = CITY_NAME;

        $layoutPath = __DIR__ . '/../templates/emails/layout.html';
        $templatePath = __DIR__ . '/../templates/emails/' . $template . '.html';

        $layout = file_exists($layoutPath) ? file_get_contents($layoutPath) : '';
        $content = file_exists($templatePath) ? file_get_contents($templatePath) : '';

        $html = str_replace('{{CONTENT}}', $content, (string) $layout);

        foreach ($data as $key => $value) {
            $html = str_replace('{{' . $key . '}}', htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'), $html);
        }

        foreach ($data as $key => $value) {
            $html = str_replace('{!!' . $key . '!!}', (string) $value, $html);
        }

        return $html;
    }

    /**
     * Logger les emails envoyés.
     */
    private function logEmail(string $to, string $subject, string $template, bool $success): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO email_logs (recipient, subject, template, status, sent_at) VALUES (?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$to, $subject, $template, $success ? 'sent' : 'failed']);
    }
}
