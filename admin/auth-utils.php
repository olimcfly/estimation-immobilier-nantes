<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';

/**
 * Ensure authentication tables exist with required minimal structure.
 */
function adminEnsureTables(PDO $db): void
{
    $db->exec(
        "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            prenom VARCHAR(100) NOT NULL,
            nom VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS admin_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            code_hash VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            attempts INT NOT NULL DEFAULT 0,
            used_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_admin_created (admin_id, created_at),
            INDEX idx_expires (expires_at),
            CONSTRAINT fk_admin_codes_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}


function adminLowercase(string $value): string
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($value, 'UTF-8');
    }

    return strtolower($value);
}

function adminRenderEmailTemplate(string $template, array $data = []): string
{
    $templatePath = __DIR__ . '/../templates/emails/' . $template . '.php';
    if (!is_file($templatePath)) {
        return '';
    }

    $siteName = defined('SITE_NAME') ? (string) SITE_NAME : 'EstimIA';
    $cityName = defined('CITY_NAME') ? (string) CITY_NAME : 'Bordeaux';
    $baseUrl = defined('BASE_URL') ? (string) BASE_URL : '';

    extract($data, EXTR_SKIP);

    ob_start();
    require $templatePath;

    return (string) ob_get_clean();
}

function adminSendEmail(string $to, string $subject, string $html): bool
{
    $fromEmail = defined('MAIL_FROM') ? (string) MAIL_FROM : ((defined('SMTP_FROM') ? (string) SMTP_FROM : 'no-reply@localhost'));
    $fromName = defined('MAIL_FROM_NAME') ? (string) MAIL_FROM_NAME : ((defined('SITE_NAME') ? (string) SITE_NAME : 'EstimIA'));

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        sprintf('From: %s <%s>', $fromName, $fromEmail),
        'Reply-To: ' . $fromEmail,
        'X-Mailer: PHP/' . phpversion(),
    ];

    return mail($to, $subject, $html, implode("\r\n", $headers));
}

function adminGenerateAndSendCode(PDO $db, array $admin, string $purpose = 'login'): array
{
    $code = (string) random_int(100000, 999999);
    $hash = password_hash($code, PASSWORD_DEFAULT);

    $db->prepare('UPDATE admin_codes SET used_at = NOW() WHERE admin_id = :admin_id AND used_at IS NULL')
        ->execute(['admin_id' => (int) $admin['id']]);

    $stmt = $db->prepare('INSERT INTO admin_codes (admin_id, code_hash, expires_at) VALUES (:admin_id, :code_hash, DATE_ADD(NOW(), INTERVAL 10 MINUTE))');
    $stmt->execute([
        'admin_id' => (int) $admin['id'],
        'code_hash' => $hash,
    ]);

    $subject = $purpose === 'onboarding'
        ? 'Votre code de connexion administrateur'
        : 'Votre code de connexion EstimIA';

    $html = adminRenderEmailTemplate('login-code', [
        'prenom' => (string) ($admin['prenom'] ?? ''),
        'nom' => (string) ($admin['nom'] ?? ''),
        'email' => (string) ($admin['email'] ?? ''),
        'code' => $code,
        'expiresMinutes' => 10,
    ]);

    $sent = adminSendEmail((string) $admin['email'], $subject, $html);

    return ['sent' => $sent, 'code' => $code];
}
