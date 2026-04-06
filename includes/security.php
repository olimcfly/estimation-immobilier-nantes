<?php

require_once __DIR__ . '/database.php';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Démarrer session sécurisée
function initSecureSession(): void
{
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '1');
    ini_set('session.use_strict_mode', '1');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Régénérer session ID toutes les 30 min
    if (!isset($_SESSION['last_regen']) || time() - $_SESSION['last_regen'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['last_regen'] = time();
    }
}

// Générer token CSRF
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Input HTML caché CSRF
function csrfField(): string
{
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

// Vérifier token CSRF
function verifyCsrf(): bool
{
    $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    return hash_equals($_SESSION['csrf_token'] ?? '', (string) $token);
}

// Rate limiting par IP (table rate_limits en DB)
function checkRateLimit(string $action, int $maxPerHour = 10): bool
{
    $db = Database::getConnection();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Nettoyer les anciennes entrées (> 1h)
    $db->prepare('DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)')->execute();

    // Compter les tentatives
    $stmt = $db->prepare(
        'SELECT COUNT(*) FROM rate_limits
         WHERE ip = ? AND action = ?
         AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)'
    );
    $stmt->execute([$ip, $action]);
    $count = (int) $stmt->fetchColumn();

    if ($count >= $maxPerHour) {
        return false; // Rate limit atteint
    }

    // Enregistrer cette tentative
    $db->prepare('INSERT INTO rate_limits (ip, action) VALUES (?, ?)')->execute([$ip, $action]);

    return true;
}

// Sanitizer les inputs
function clean(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function cleanEmail(string $email): string
{
    return (string) filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

function cleanPhone(string $phone): string
{
    return (string) preg_replace('/[^0-9+]/', '', $phone);
}

function cleanInt($val): int
{
    return (int) filter_var($val, FILTER_SANITIZE_NUMBER_INT);
}

function cleanFloat($val): float
{
    return (float) filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

// Headers de sécurité
function setSecurityHeaders(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://maps.googleapis.com https://cdn.tailwindcss.com https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https://*.googleapis.com https://*.gstatic.com; connect-src 'self' https://maps.googleapis.com;");
}

// Protection brute force login
function checkLoginAttempts(string $email): bool
{
    $db = Database::getConnection();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    $stmt = $db->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE (ip = ? OR email = ?)
         AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
    );
    $stmt->execute([$ip, $email]);

    return (int) $stmt->fetchColumn() < 5; // Max 5 tentatives / 15 min
}

function recordLoginAttempt(string $email, bool $success): void
{
    $db = Database::getConnection();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    $db->prepare('INSERT INTO login_attempts (ip, email, success) VALUES (?, ?, ?)')
        ->execute([$ip, $email, (int) $success]);

    if ($success) {
        // Nettoyer les tentatives après un login réussi
        $db->prepare('DELETE FROM login_attempts WHERE email = ?')->execute([$email]);
    }
}

// Suivre l'activité de l'utilisateur
function trackUserActivity(int $userId, string $action): void
{
    $db = Database::getConnection();
    $stmt = $db->prepare(
        'INSERT INTO user_activities (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId,
        $action,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    ]);
}

// Déconnecter un utilisateur
function logoutUser(int $userId): void
{
    $db = Database::getConnection();

    // Mettre à jour le statut en ligne
    $db->prepare('UPDATE admins SET is_online = FALSE WHERE id = ?')->execute([$userId]);

    // Supprimer la session
    $db->prepare('DELETE FROM user_sessions WHERE user_id = ?')->execute([$userId]);

    // Détruire la session PHP
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

// Rafraîchir le statut en ligne
function refreshOnlineStatuses(): void
{
    $db = Database::getConnection();
    $db->exec(
        'UPDATE admins a
         SET is_online = EXISTS (
             SELECT 1 FROM user_sessions s
             WHERE s.user_id = a.id AND s.is_online = TRUE
         )'
    );
}
