<?php
// Configuration Estimation Immobilier Nantes
define('DEBUG_MODE', false);
define('MAINTENANCE_MODE', false);
define('SITE_NAME', 'Estimation Immobilier Nantes');
define('CITY_NAME', 'Nantes');
define('SITE_PHONE', '0785611700'); 
// Base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'sc2tasq5564_nantes');
define('DB_USER', 'sc2tasq5564_nt');
define('DB_PASS', 'Xt$-~(YnBZ!bL5n%'); // 
// Email (à configurer avec vos identifiants SMTP)
define('SMTP_HOST', 'mail.estimation-immobilier-nantes.fr');
define('SMTP_USER', 'contact@estimation-immobilier-nantes.fr');
define('SMTP_PASS', 'M=5JSvWdl9f[+;q(');
define('SMTP_PORT', 465);
define('SMTP_FROM', 'contact@estimation-immobilier-nantes.fr');
define('MAIL_FROM', SMTP_FROM);
define('MAIL_FROM_NAME', 'Estimation Immobilier Nantes');

// IA — Multi-provider fallback
define('AI_OPENAI_KEY', '');
define('AI_ANTHROPIC_KEY', '');
define('AI_PERPLEXITY_KEY', '');
define('AI_MISTRAL_KEY', '');

// Google Ads (optionnel)
define('GOOGLE_ADS_DEVELOPER_TOKEN', '');
define('GOOGLE_ADS_CUSTOMER_ID', '');
define('GOOGLE_ADS_CLIENT_ID', '');
define('GOOGLE_ADS_CLIENT_SECRET', '');
define('GOOGLE_ADS_REFRESH_TOKEN', '');

// SEO / Ads lexicon seeds - Adapté pour Nantes et sa région
define('SEO_KEYWORD_SEEDS', [
    'estimer' => [
        'estimation gratuite appartement nantes centre',
        'prix m2 nantes île de nantes',
        'estimer maison rezé',
        'valeur terrain saint-herblain',
        'estimation loft nantes',
    ],
    'vendre' => [
        'vendre maison nantes rapidement',
        'vendre appartement rezé',
        'vendre terrain saint-nazaire',
        'meilleur prix immobilier nantes',
    ],
    'acheter' => [
        'acheter appartement nantes centre',
        'acheter maison orvault',
        'investir immobilier saint-nazaire',
        'loft nantes pas cher',
    ],
    'investir' => [
        'investir immobilier nantes 2024',
        'meilleur quartier nantes investissement',
        'rendement locatif rezé',
        'acheter pour louer nantes',
    ],
    'blog' => [
        'prix immobilier nantes 2024',
        'évolution marché immobilier nantes',
        'quartiers en hausse nantes',
        'projets urbains nantes 2025',
    ],
]);

// Sécurité
define('ADMIN_EMAIL', 'admin@estimation-immobilier-nantes.fr');
define('SECRET_KEY', bin2hex(random_bytes(32)));

// Chemins
define('BASE_URL', 'https://estimation-immobilier-nantes.fr');
define('BASE_PATH', __DIR__ . '/..');

// Configuration locale spécifique
define('DEFAULT_CITY', 'Nantes');
define('DEFAULT_REGION', 'Pays de la Loire');
define('DEFAULT_DEPARTMENT', 'Loire-Atlantique');
define('CITIES_LIST', [
    'Nantes', 'Saint-Nazaire', 'Rezé', 'Saint-Herblain', 'Orvault',
    'Vertou', 'Couëron', 'Bouguenais', 'Carquefou', 'La Chapelle-sur-Erdre'
]);

require_once BASE_PATH . '/includes/error-handler.php';
