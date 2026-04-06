<?php
/**
 * Gestionnaire d'erreurs global
 * Inclus dans config.php après la définition des constantes
 */

// Créer le dossier logs s'il n'existe pas
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
    file_put_contents($logDir . '/.htaccess', 'Deny from all');
}

// Mode debug basé sur la config
if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', $logDir . '/php_errors.log');
}

// Handler d'erreurs personnalisé
set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    // Ignorer les erreurs supprimées par @
    if (!(error_reporting() & $severity)) {
        return false;
    }

    $levelNames = [
        E_WARNING => 'WARNING',
        E_NOTICE => 'NOTICE',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_DEPRECATED => 'DEPRECATED',
    ];

    $level = $levelNames[$severity] ?? 'UNKNOWN';
    $logMessage = date('Y-m-d H:i:s') . " [$level] $message in $file:$line\n";

    file_put_contents(
        __DIR__ . '/../logs/app.log',
        $logMessage,
        FILE_APPEND | LOCK_EX
    );

    // Les erreurs fatales (USER_ERROR) : afficher page 500
    if ($severity === E_USER_ERROR) {
        http_response_code(500);
        include __DIR__ . '/../pages/500.php';
        exit;
    }

    return false; // Laisser le handler PHP par défaut aussi
});

// Handler d'exceptions non attrapées
set_exception_handler(function (Throwable $e): void {
    $logMessage = date('Y-m-d H:i:s') . ' [EXCEPTION] '
        . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine()
        . "\nStack trace:\n" . $e->getTraceAsString() . "\n\n";

    file_put_contents(
        __DIR__ . '/../logs/app.log',
        $logMessage,
        FILE_APPEND | LOCK_EX
    );

    if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
        echo '<h1>Exception</h1>';
        echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
        http_response_code(500);
        include __DIR__ . '/../pages/500.php';
    }
    exit;
});

// Handler de shutdown (erreurs fatales)
register_shutdown_function(function (): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $logMessage = date('Y-m-d H:i:s') . ' [FATAL] '
            . $error['message']
            . ' in ' . $error['file'] . ':' . $error['line'] . "\n";

        file_put_contents(
            __DIR__ . '/../logs/app.log',
            $logMessage,
            FILE_APPEND | LOCK_EX
        );

        if (!(defined('DEBUG_MODE') && DEBUG_MODE === true)) {
            http_response_code(500);
            // Ne pas inclure la page 500 si déjà en cours d'envoi de contenu
            if (!headers_sent()) {
                include __DIR__ . '/../pages/500.php';
            }
        }
    }
});

/**
 * Logger personnalisé pour l'application
 */
function appLog(string $message, string $level = 'INFO', string $channel = 'app'): void
{
    $logMessage = date('Y-m-d H:i:s') . " [$level] [$channel] $message\n";
    file_put_contents(
        __DIR__ . '/../logs/' . $channel . '.log',
        $logMessage,
        FILE_APPEND | LOCK_EX
    );
}
