<?php

declare(strict_types=1);

$adminMenu = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => '/admin/index.php', 'icon' => '🏠', 'badge' => null],
    [
        'key' => 'leads',
        'label' => 'Leads CRM',
        'href' => '/admin/lead.php',
        'icon' => '🏢',
        'badge' => '32',
        'children' => [
            ['label' => 'Pipeline', 'href' => '/admin/lead.php', 'badge' => '12'],
            ['label' => 'Nouveaux', 'href' => '/admin/leads/index.php', 'badge' => '8'],
            ['label' => 'Relances', 'href' => '/admin/leads/index.php?tab=followup', 'badge' => '5'],
        ],
    ],
    [
        'key' => 'google-ads',
        'label' => 'Acquisition',
        'href' => '/admin/google-ads/index.php',
        'icon' => '📈',
        'badge' => 'Pro',
        'children' => [
            ['label' => 'Stratégies', 'href' => '/admin/google-ads/index.php'],
            ['label' => 'Campagnes', 'href' => '/admin/google-ads/campaigns.php'],
            ['label' => 'ROI Ads', 'href' => '/admin/ads-roi.php'],
        ],
    ],
    ['key' => 'traffic', 'label' => 'Trafic', 'href' => '/admin/traffic/index.php', 'icon' => '📢', 'badge' => null],
    [
        'key' => 'automations',
        'label' => 'Automations',
        'href' => '/admin/webhooks.php',
        'icon' => '⚡',
        'badge' => '7',
        'children' => [
            ['label' => 'Webhooks', 'href' => '/admin/webhooks.php'],
            ['label' => 'Exports', 'href' => '/admin/settings.php#backup'],
            ['label' => 'Rapports', 'href' => '/admin/export.php'],
        ],
    ],
    [
        'key' => 'settings',
        'label' => 'Paramètres',
        'href' => '/admin/settings.php',
        'icon' => '⚙️',
        'badge' => null,
        'children' => [
            ['label' => 'Général', 'href' => '/admin/settings.php#general'],
            ['label' => 'Société', 'href' => '/admin/settings.php#company'],
            ['label' => 'Intégrations', 'href' => '/admin/settings.php#integrations'],
        ],
    ],
];

$adminResources = [
    ['label' => 'Centre d\'aide', 'href' => '/pages/faq.php'],
    ['label' => 'Playbook conversion', 'href' => '/site-specific/pages/ressources/index.php'],
    ['label' => 'Exporter les leads', 'href' => '/admin/export.php'],
];

$adminTopNav = [
    ['key' => 'theme', 'label' => 'Mode sombre', 'href' => '#', 'isToggle' => true],
    ['key' => 'logout', 'label' => 'Déconnexion', 'href' => '/admin/logout.php'],
];
