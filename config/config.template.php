<?php
/**
 * Configuration EstimIA
 * Généré automatiquement le {INSTALL_DATE}
 * Ne pas modifier manuellement sauf nécessité.
 */

// === BASE DE DONNÉES (O2Switch) ===
define('DB_HOST', 'localhost');
define('DB_NAME', '{DB_NAME}');
define('DB_USER', '{DB_USER}');
define('DB_PASS', '{DB_PASS}');
define('DB_CHARSET', 'utf8mb4');

// === SITE ===
define('SITE_NAME', '{SITE_NAME}');
define('SITE_URL', '{SITE_URL}');
define('SITE_PHONE', '{SITE_PHONE}');
define('SITE_COLOR', '#2563eb');
define('ADMIN_EMAIL', '{ADMIN_EMAIL}');

// === LOCALISATION ===
define('CITY_NAME', '{CITY_NAME}');
define('CITY_LAT', (float) '{CITY_LAT}');
define('CITY_LNG', (float) '{CITY_LNG}');
define('CITY_RADIUS_KM', (int) '{CITY_RADIUS}');

// === SMTP ===
define('SMTP_HOST', '{SMTP_HOST}');
define('SMTP_PORT', (int) '{SMTP_PORT}');
define('SMTP_USER', '{SMTP_USER}');
define('SMTP_PASS', '{SMTP_PASS}');
define('SMTP_FROM', SMTP_USER);
define('MAIL_FROM', SMTP_FROM);
define('SMTP_ENCRYPTION', '{SMTP_ENCRYPTION}');

// === GOOGLE ===
define('GOOGLE_MAPS_API_KEY', '{GOOGLE_MAPS_KEY}');


// === IA — Multi-provider fallback ===
define('AI_OPENAI_KEY', '{AI_OPENAI_KEY}');
define('AI_ANTHROPIC_KEY', '{AI_ANTHROPIC_KEY}');
define('AI_PERPLEXITY_KEY', '{AI_PERPLEXITY_KEY}');
define('AI_MISTRAL_KEY', '{AI_MISTRAL_KEY}');

// === SÉCURITÉ ===
define('APP_SECRET', '{APP_SECRET}');
define('WEBHOOK_SECRET', '{WEBHOOK_SECRET}');
define('DEBUG_MODE', false);
define('MAINTENANCE_MODE', false);

// === CHEMINS ===
define('ROOT_PATH', __DIR__ . '/..');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('CLASSES_PATH', ROOT_PATH . '/classes');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');

// === AUTOLOAD ===
spl_autoload_register(function ($class) {
    $file = CLASSES_PATH . '/' . $class . '.php';
    if (file_exists($file)) require_once $file;
});

// === ERROR HANDLER ===
require_once INCLUDES_PATH . '/error-handler.php';

// === DATABASE ===
require_once __DIR__ . '/../includes/database.php';
