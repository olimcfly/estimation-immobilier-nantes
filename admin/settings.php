<?php

declare(strict_types=1);

$pageTitle = 'Paramètres';

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../classes/Settings.php';
$currentPage = 'settings';
$topNavCurrent = 'settings';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$sectionFields = [
    'general' => ['site_name', 'site_phone', 'site_email', 'target_radius_km'],
    'company' => ['company_name', 'company_address', 'company_siret', 'company_rcs', 'company_vat'],
    'appearance' => ['site_color', 'site_logo_url', 'hero_tagline'],
    'estimation' => [
        'estimation_coef_neuf',
        'estimation_coef_renove',
        'estimation_coef_bon',
        'estimation_coef_travaux',
        'estimation_coef_refaire',
        'estimation_coef_terrasse',
        'estimation_coef_parking',
        'estimation_coef_piscine',
        'estimation_coef_vue',
    ],
    'emails' => [
        'smtp_host',
        'smtp_port',
        'smtp_user',
        'smtp_password',
        'smtp_encryption',
        'relance_j3_active',
        'relance_j7_active',
        'relance_j14_active',
    ],
    'notifications' => [
        'notification_new_lead',
        'notification_rdv',
        'notification_high_score',
        'notification_daily_summary',
        'notification_email',
    ],
    'integrations' => [
        'google_analytics_id',
        'google_ads_id',
        'google_maps_api_key',
        'webhook_url',
        'calendly_url',
        'whatsapp_number',
        'dvf_source_url',
        'dvf_source_label',
    ],
];

$checkboxFields = [
    'relance_j3_active',
    'relance_j7_active',
    'relance_j14_active',
    'notification_new_lead',
    'notification_rdv',
    'notification_high_score',
    'notification_daily_summary',
];

$defaultCoefficients = [
    'estimation_coef_neuf' => '1.15',
    'estimation_coef_renove' => '1.05',
    'estimation_coef_bon' => '1.00',
    'estimation_coef_travaux' => '0.85',
    'estimation_coef_refaire' => '0.70',
    'estimation_coef_terrasse' => '1.05',
    'estimation_coef_parking' => '1.03',
    'estimation_coef_piscine' => '1.07',
    'estimation_coef_vue' => '1.04',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], (string) $postedToken)) {
        $_SESSION['flash_error'] = 'Token CSRF invalide. Veuillez réessayer.';
        header('Location: settings.php');
        exit;
    }

    $section = (string) ($_POST['setting_section'] ?? '');
    if ($section === 'estimation_reset') {
        Settings::setMany($defaultCoefficients);
        $_SESSION['flash_success'] = 'Les coefficients par défaut ont été rétablis.';
        header('Location: settings.php#estimation');
        exit;
    }

    if (isset($sectionFields[$section])) {
        $payload = [];
        foreach ($sectionFields[$section] as $field) {
            if (in_array($field, $checkboxFields, true)) {
                $payload[$field] = isset($_POST[$field]) ? '1' : '0';
                continue;
            }

            $payload[$field] = trim((string) ($_POST[$field] ?? ''));
        }

        if (isset($payload['target_radius_km'])) {
            $radius = (int) $payload['target_radius_km'];
            $payload['target_radius_km'] = (string) max(10, min(100, $radius));
        }

        Settings::setMany($payload);
        $_SESSION['flash_success'] = 'Paramètres enregistrés avec succès.';
        header('Location: settings.php#' . urlencode($section));
        exit;
    }
}

$keys = [
    'site_name',
    'site_phone',
    'site_email',
    'site_url',
    'target_city',
    'target_radius_km',
    'site_color',
    'site_logo_url',
    'hero_tagline',
    'company_name',
    'company_address',
    'company_siret',
    'company_rcs',
    'company_vat',
    'estimation_coef_neuf',
    'estimation_coef_renove',
    'estimation_coef_bon',
    'estimation_coef_travaux',
    'estimation_coef_refaire',
    'estimation_coef_terrasse',
    'estimation_coef_parking',
    'estimation_coef_piscine',
    'estimation_coef_vue',
    'smtp_host',
    'smtp_port',
    'smtp_user',
    'smtp_password',
    'smtp_encryption',
    'relance_j3_active',
    'relance_j7_active',
    'relance_j14_active',
    'notification_new_lead',
    'notification_rdv',
    'notification_high_score',
    'notification_daily_summary',
    'notification_email',
    'google_analytics_id',
    'google_ads_id',
    'google_maps_api_key',
    'webhook_url',
    'calendly_url',
    'whatsapp_number',
    'dvf_source_url',
    'dvf_source_label',
];

$settings = [];
foreach ($keys as $key) {
    $settings[$key] = Settings::get($key, '');
}

$users = [];
try {
    $db = Database::getConnection();
    $usersQuery = "SELECT name, email, role, last_login_at FROM users ORDER BY id DESC";
    $users = $db->query($usersQuery)->fetchAll();
} catch (Throwable $error) {
    try {
        $db = Database::getConnection();
        $users = $db->query("SELECT
                SUBSTRING_INDEX(email, '@', 1) AS name,
                email,
                'admin' AS role,
                created_at AS last_login_at
            FROM admin_users
            ORDER BY id DESC")->fetchAll();
    } catch (Throwable $ignored) {
        $users = [];
    }
}

$systemInfo = [
    'php_version' => PHP_VERSION,
    'disk_free' => function_exists('disk_free_space') ? disk_free_space('/') : null,
    'lead_count' => 0,
    'db_size_mb' => 0,
];

try {
    $db = Database::getConnection();
    $systemInfo['lead_count'] = (int) $db->query('SELECT COUNT(*) FROM estimations')->fetchColumn();
    $sizeStmt = $db->query('SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) FROM information_schema.tables WHERE table_schema = DATABASE()');
    $systemInfo['db_size_mb'] = (float) $sizeStmt->fetchColumn();
} catch (Throwable $ignored) {
    // noop
}
?>

<style>
.settings-layout{display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:start}
.settings-nav{position:sticky;top:84px;border:1px solid #e5e7eb;border-radius:12px;padding:16px;background:#fff}
.settings-nav a{display:block;padding:8px 10px;border-radius:8px;color:#334155;text-decoration:none;font-weight:600;margin-bottom:4px}
.settings-nav a:hover{background:#eff6ff;color:#1d4ed8}
.settings-section{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:20px}
.settings-section h2{margin:0 0 16px;font-size:1.2rem}
.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-group.full{grid-column:1/-1}
.form-group input,.form-group textarea,.form-group select{border:1px solid #cbd5e1;border-radius:8px;padding:10px;font-size:.95rem}
.form-actions{margin-top:14px;display:flex;gap:10px;align-items:center}
.btn{background:#2563eb;color:#fff;border:0;border-radius:8px;padding:10px 14px;cursor:pointer;font-weight:600}
.btn.secondary{background:#0f766e}
.btn.danger{background:#b91c1c}
.badge{padding:3px 8px;border-radius:999px;background:#eef2ff;color:#3730a3;font-size:.78rem}
.table{width:100%;border-collapse:collapse}
.table th,.table td{border-bottom:1px solid #e5e7eb;padding:10px;text-align:left}
.flash{padding:12px;border-radius:10px;margin-bottom:16px}
.flash.success{background:#ecfdf5;color:#047857}
.flash.error{background:#fef2f2;color:#b91c1c}
.toggle{display:flex;align-items:center;gap:8px}
.preview-box{padding:10px;border-radius:10px;border:1px dashed #94a3b8;background:#f8fafc}
@media (max-width:1000px){.settings-layout{grid-template-columns:1fr}.settings-nav{position:static}.form-grid{grid-template-columns:1fr}}
</style>

<div class="container py-4">
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="flash success"><?= h($_SESSION['flash_success']) ?></div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flash error"><?= h($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="settings-layout">
        <aside class="settings-nav">
            <a href="#general">🏢 Général</a>
            <a href="#company">🏗️ Société</a>
            <a href="#appearance">🎨 Apparence</a>
            <a href="#estimation">📐 Coefficients d'estimation</a>
            <a href="#emails">📧 Emails & Relances</a>
            <a href="#notifications">🔔 Notifications</a>
            <a href="#integrations">🔗 Intégrations</a>
            <a href="#users">👥 Utilisateurs</a>
            <a href="#backup">💾 Sauvegarde</a>
        </aside>

        <main>
            <section id="general" class="settings-section">
                <h2>🏢 Général</h2>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="setting_section" value="general">
                    <div class="form-grid">
                        <label class="form-group"><span>Nom du site</span><input type="text" name="site_name" value="<?= h($settings['site_name']) ?>" required></label>
                        <label class="form-group"><span>Téléphone</span><input type="tel" name="site_phone" value="<?= h($settings['site_phone']) ?>"></label>
                        <label class="form-group"><span>Email principal</span><input type="email" name="site_email" value="<?= h($settings['site_email']) ?>"></label>
                        <label class="form-group"><span>URL du site</span><input type="url" value="<?= h($settings['site_url']) ?>" readonly></label>
                        <label class="form-group"><span>Ville cible</span><input type="text" value="<?= h($settings['target_city']) ?>" readonly></label>
                        <label class="form-group"><span>Rayon (10-100 km)</span><input type="number" min="10" max="100" name="target_radius_km" value="<?= h($settings['target_radius_km']) ?>"></label>
                    </div>
                    <div class="form-actions"><button class="btn" type="submit">Enregistrer</button></div>
                </form>
            </section>

            <section id="company" class="settings-section">
                <h2>🏗️ Société</h2>
                <p>Utilisé pour les mentions légales auto-générées.</p>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="setting_section" value="company">
                    <div class="form-grid">
                        <label class="form-group"><span>Raison sociale</span><input type="text" name="company_name" value="<?= h($settings['company_name']) ?>"></label>
                        <label class="form-group"><span>SIRET</span><input type="text" pattern="\d{14}" name="company_siret" value="<?= h($settings['company_siret']) ?>"></label>
                        <label class="form-group"><span>RCS</span><input type="text" name="company_rcs" value="<?= h($settings['company_rcs']) ?>"></label>
                        <label class="form-group"><span>TVA intracommunautaire</span><input type="text" name="company_vat" value="<?= h($settings['company_vat']) ?>"></label>
                        <label class="form-group full"><span>Adresse</span><textarea name="company_address" rows="3"><?= h($settings['company_address']) ?></textarea></label>
                    </div>
                    <div class="form-actions"><button class="btn" type="submit">Enregistrer</button></div>
                </form>
            </section>

            <section id="appearance" class="settings-section">
                <h2>🎨 Apparence</h2>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="setting_section" value="appearance">
                    <div class="form-grid">
                        <label class="form-group"><span>Couleur principale</span><input id="site_color" type="color" name="site_color" value="<?= h($settings['site_color'] ?: '#2563eb') ?>"></label>
                        <div class="form-group"><span>Preview couleur</span><div id="color_preview" class="preview-box" style="background:<?= h($settings['site_color'] ?: '#2563eb') ?>;color:#fff">Couleur principale</div></div>
                        <label class="form-group full"><span>Logo URL</span><input id="site_logo_url" type="url" name="site_logo_url" value="<?= h($settings['site_logo_url']) ?>"></label>
                        <div class="form-group full"><span>Aperçu logo</span><img id="logo_preview" src="<?= h($settings['site_logo_url']) ?>" alt="Aperçu logo" style="max-height:70px;max-width:220px;border:1px solid #e5e7eb;padding:8px;border-radius:8px"></div>
                        <label class="form-group full"><span>Texte d'accroche</span><textarea name="hero_tagline" rows="4"><?= h($settings['hero_tagline']) ?></textarea></label>
                    </div>
                    <div class="form-actions"><button class="btn" type="submit">Enregistrer</button></div>
                </form>
            </section>

            <section id="estimation" class="settings-section">
                <h2>📐 Coefficients d'estimation</h2>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="setting_section" value="estimation">
                    <table class="table">
                        <thead><tr><th>Critère</th><th>Coefficient</th><th>Impact sur le prix</th></tr></thead>
                        <tbody>
                        <?php
                        $coefMap = [
                            'estimation_coef_neuf' => 'État neuf',
                            'estimation_coef_renove' => 'Rénové récemment',
                            'estimation_coef_bon' => 'Bon état',
                            'estimation_coef_travaux' => 'Travaux à prévoir',
                            'estimation_coef_refaire' => 'À rénover entièrement',
                            'estimation_coef_terrasse' => 'Terrasse/Balcon',
                            'estimation_coef_parking' => 'Parking',
                            'estimation_coef_piscine' => 'Piscine',
                            'estimation_coef_vue' => 'Vue dégagée',
                        ];
                        foreach ($coefMap as $key => $label):
                            $val = (float) ($settings[$key] ?: '1');
                            $impact = round(($val - 1) * 100);
                        ?>
                            <tr>
                                <td><?= h($label) ?></td>
                                <td><input type="number" step="0.01" min="0" max="3" name="<?= h($key) ?>" value="<?= h((string) $val) ?>" class="coef-input" data-target="<?= h($key) ?>"></td>
                                <td><span id="impact_<?= h($key) ?>"><?= $impact > 0 ? '+' . $impact : $impact ?>%</span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="preview-box" id="coef_preview">Exemple : un bien à 200 000€ en état “travaux” → 200 000 × <?= h($settings['estimation_coef_travaux']) ?> = <?= number_format(200000 * (float) $settings['estimation_coef_travaux'], 0, ',', ' ') ?>€</div>
                    <div class="form-actions">
                        <button class="btn" type="submit">Enregistrer</button>
                    </div>
                </form>
                <form method="post" style="margin-top:10px">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="setting_section" value="estimation_reset">
                    <button type="submit" class="btn secondary">Réinitialiser les coefficients par défaut</button>
                </form>
            </section>

            <section id="emails" class="settings-section">
                <h2>📧 Emails & Relances</h2>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="setting_section" value="emails">
                    <div class="form-grid">
                        <label class="form-group"><span>Serveur SMTP</span><input type="text" name="smtp_host" value="<?= h($settings['smtp_host']) ?>" placeholder="ssl0.ovh.net"></label>
                        <label class="form-group"><span>Port</span><input type="number" name="smtp_port" value="<?= h($settings['smtp_port']) ?>" placeholder="587"></label>
                        <label class="form-group"><span>Utilisateur</span><input type="text" name="smtp_user" value="<?= h($settings['smtp_user']) ?>"></label>
                        <label class="form-group"><span>Mot de passe</span><input type="password" name="smtp_password" value="<?= h($settings['smtp_password']) ?>"></label>
                        <label class="form-group"><span>Chiffrement</span>
                            <select name="smtp_encryption">
                                <option value="ssl" <?= $settings['smtp_encryption'] === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                <option value="tls" <?= $settings['smtp_encryption'] === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="none" <?= $settings['smtp_encryption'] === 'none' ? 'selected' : '' ?>>Aucun</option>
                            </select>
                        </label>
                    </div>

                    <h3>Relances automatiques</h3>
                    <p>Les relances sont envoyées par le cron job quotidien à 10h.</p>
                    <div class="form-grid">
                        <label class="toggle"><input type="checkbox" name="relance_j3_active" value="1" <?= $settings['relance_j3_active'] === '1' ? 'checked' : '' ?>> Relance J+3</label>
                        <label class="toggle"><input type="checkbox" name="relance_j7_active" value="1" <?= $settings['relance_j7_active'] === '1' ? 'checked' : '' ?>> Relance J+7</label>
                        <label class="toggle"><input type="checkbox" name="relance_j14_active" value="1" <?= $settings['relance_j14_active'] === '1' ? 'checked' : '' ?>> Relance J+14</label>
                    </div>

                    <div class="form-actions">
                        <button class="btn" type="submit">Enregistrer</button>
                        <button type="button" class="btn secondary" id="btn-test-email">📧 Envoyer un email test</button>
                        <a class="badge" href="../templates" target="_blank" rel="noopener">Voir les templates d'emails</a>
                    </div>
                </form>
            </section>

            <section id="notifications" class="settings-section">
                <h2>🔔 Notifications</h2>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="setting_section" value="notifications">
                    <div class="form-grid">
                        <label class="toggle"><input type="checkbox" name="notification_new_lead" value="1" <?= $settings['notification_new_lead'] === '1' ? 'checked' : '' ?>> Nouveau lead estimation</label>
                        <label class="toggle"><input type="checkbox" name="notification_rdv" value="1" <?= $settings['notification_rdv'] === '1' ? 'checked' : '' ?>> Nouveau RDV</label>
                        <label class="toggle"><input type="checkbox" name="notification_high_score" value="1" <?= $settings['notification_high_score'] === '1' ? 'checked' : '' ?>> Lead score élevé (&gt;70)</label>
                        <label class="toggle"><input type="checkbox" name="notification_daily_summary" value="1" <?= $settings['notification_daily_summary'] === '1' ? 'checked' : '' ?>> Résumé quotidien</label>
                        <label class="form-group full"><span>Email de notification</span><input type="email" name="notification_email" value="<?= h($settings['notification_email']) ?>"></label>
                    </div>
                    <div class="form-actions"><button class="btn" type="submit">Enregistrer</button></div>
                </form>
            </section>

            <section id="integrations" class="settings-section">
                <h2>🔗 Intégrations</h2>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="setting_section" value="integrations">
                    <h3>Google</h3>
                    <div class="form-grid">
                        <label class="form-group"><span>Google Analytics ID</span><input type="text" name="google_analytics_id" value="<?= h($settings['google_analytics_id']) ?>" placeholder="G-XXXXXXXXXX"></label>
                        <label class="form-group"><span>Google Ads ID</span><input type="text" name="google_ads_id" value="<?= h($settings['google_ads_id']) ?>" placeholder="AW-XXXXXXXXXX"></label>
                        <label class="form-group full"><span>Google Maps API Key</span><input type="text" name="google_maps_api_key" value="<?= h($settings['google_maps_api_key']) ?>"></label>
                    </div>

                    <h3>Outils externes</h3>
                    <div class="form-grid">
                        <label class="form-group full"><span>Webhook URL</span><input type="url" name="webhook_url" value="<?= h($settings['webhook_url']) ?>"></label>
                        <div class="form-group full"><small>Chaque nouveau lead enverra un POST JSON à cette URL. Compatible Zapier, Make, n8n.</small></div>
                        <label class="form-group full"><span>Calendly URL</span><input type="url" name="calendly_url" value="<?= h($settings['calendly_url']) ?>"></label>
                        <div class="form-group full"><small>Si renseigné, le bouton RDV redirigera vers votre Calendly.</small></div>
                        <label class="form-group full"><span>WhatsApp</span><input type="tel" name="whatsapp_number" value="<?= h($settings['whatsapp_number']) ?>"></label>
                        <div class="form-group full"><small>Si renseigné, un bouton WhatsApp apparaîtra sur le site.</small></div>
                    </div>

                    <h3>DVF / Data.gouv</h3>
                    <div class="form-grid">
                        <label class="form-group full"><span>URL de la source DVF (ZIP/GZ)</span><input type="url" name="dvf_source_url" value="<?= h($settings['dvf_source_url']) ?>" placeholder="https://files.data.gouv.fr/.../valeursfoncieres-latest.txt.gz"></label>
                        <label class="form-group full"><span>Libellé source (API/front)</span><input type="text" name="dvf_source_label" value="<?= h($settings['dvf_source_label']) ?>" placeholder="DVF 2025 (Etalab)"></label>
                        <div class="form-group full"><small>Vous pouvez déclencher ici le téléchargement + parsing DVF pour mettre à jour la base locale sans accès serveur.</small></div>
                    </div>

                    <div class="form-actions">
                        <button class="btn" type="submit">Enregistrer</button>
                        <button type="button" class="btn secondary" id="btn-test-webhook">Tester le webhook</button>
                        <button type="button" class="btn secondary" id="btn-sync-dvf">Mettre à jour DVF maintenant</button>
                    </div>
                </form>
            </section>

            <section id="users" class="settings-section">
                <h2>👥 Utilisateurs</h2>
                <table class="table">
                    <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Dernière connexion</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="5">Aucun utilisateur trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= h($user['name'] ?? '') ?></td>
                                <td><?= h($user['email'] ?? '') ?></td>
                                <td><span class="badge"><?= h($user['role'] ?? 'agent') ?></span></td>
                                <td><?= h((string) ($user['last_login_at'] ?? '-')) ?></td>
                                <td><a href="settings.php#users">Gérer</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>

                <div class="form-actions">
                    <button class="btn" type="button" onclick="document.getElementById('addUserModal').showModal()">+ Ajouter un utilisateur</button>
                </div>

                <dialog id="addUserModal">
                    <form method="dialog">
                        <h3>Ajouter un utilisateur</h3>
                        <p><label>Nom <input type="text"></label></p>
                        <p><label>Email <input type="email"></label></p>
                        <p><label>Mot de passe <input type="password"></label></p>
                        <p><label>Rôle
                            <select>
                                <option>admin</option>
                                <option>agent</option>
                            </select>
                        </label></p>
                        <p><small>Admin : tout accès. Agent : voit ses leads assignés, pas les settings.</small></p>
                        <button class="btn" value="close">Fermer</button>
                    </form>
                </dialog>
            </section>

            <section id="backup" class="settings-section">
                <h2>💾 Sauvegarde</h2>
                <div class="form-actions">
                    <a class="btn" href="/admin/export.php?type=db_backup">📥 Exporter la base de données</a>
                    <a class="btn secondary" href="/admin/export.php?type=leads_csv">📥 Exporter tous les leads (CSV)</a>
                    <a class="btn danger" href="/pages/admin-actions.php?action=purge_old_leads" onclick="return confirm('Confirmer la purge des leads de plus de 36 mois ?');">🗑️ Purger les leads &gt; 36 mois</a>
                </div>

                <h3>Infos système</h3>
                <ul>
                    <li>Version PHP : <strong><?= h($systemInfo['php_version']) ?></strong></li>
                    <li>Taille DB : <strong><?= h(number_format((float) $systemInfo['db_size_mb'], 2, ',', ' ')) ?> MB</strong></li>
                    <li>Nombre de leads : <strong><?= h((string) $systemInfo['lead_count']) ?></strong></li>
                    <li>Espace disque libre : <strong><?= $systemInfo['disk_free'] !== null ? h(number_format((float) $systemInfo['disk_free'] / 1024 / 1024 / 1024, 2, ',', ' ')) . ' GB' : 'N/A' ?></strong></li>
                </ul>
            </section>
<script>
const colorInput = document.getElementById('site_color');
const colorPreview = document.getElementById('color_preview');
if (colorInput && colorPreview) {
    colorInput.addEventListener('input', () => {
        colorPreview.style.background = colorInput.value;
    });
}

const logoInput = document.getElementById('site_logo_url');
const logoPreview = document.getElementById('logo_preview');
if (logoInput && logoPreview) {
    logoInput.addEventListener('input', () => {
        logoPreview.src = logoInput.value;
    });
}

document.querySelectorAll('.coef-input').forEach((input) => {
    input.addEventListener('input', () => {
        const value = parseFloat(input.value || '1');
        const impact = Math.round((value - 1) * 100);
        const impactEl = document.getElementById('impact_' + input.dataset.target);
        if (impactEl) {
            impactEl.textContent = `${impact > 0 ? '+' : ''}${impact}%`;
        }

        const travaux = parseFloat(document.querySelector('[name="estimation_coef_travaux"]').value || '1');
        const result = 200000 * travaux;
        const fmt = new Intl.NumberFormat('fr-FR');
        document.getElementById('coef_preview').textContent = `Exemple : un bien à 200 000€ en état “travaux” → 200 000 × ${travaux.toFixed(2)} = ${fmt.format(result)}€`;
    });
});

function postTest(endpoint, message) {
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= h($_SESSION['csrf_token']) ?>'
        },
        body: JSON.stringify({type: 'test'})
    })
    .then(() => alert(message))
    .catch(() => alert('Action de test indisponible pour le moment.'));
}

const testEmailBtn = document.getElementById('btn-test-email');
if (testEmailBtn) {
    testEmailBtn.addEventListener('click', () => postTest('ajax/test_email.php', 'Email de test envoyé (si la route est disponible).'));
}

const testWebhookBtn = document.getElementById('btn-test-webhook');
if (testWebhookBtn) {
    testWebhookBtn.addEventListener('click', () => postTest('ajax/test_webhook.php', 'Webhook de test envoyé (si la route est disponible).'));
}

const syncDvfBtn = document.getElementById('btn-sync-dvf');
if (syncDvfBtn) {
    syncDvfBtn.addEventListener('click', async () => {
        syncDvfBtn.disabled = true;
        const originalText = syncDvfBtn.textContent;
        syncDvfBtn.textContent = 'Synchronisation DVF...';

        try {
            const response = await fetch('/admin/ajax/dvf_sync.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= h($_SESSION['csrf_token']) ?>'
                },
                body: JSON.stringify({ action: 'sync_dvf' })
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Erreur DVF.');
            }
            alert(`${data.message}\nTransactions retenues: ${data.parse?.kept ?? 0}`);
        } catch (error) {
            alert(`Synchronisation DVF impossible: ${error.message}`);
        } finally {
            syncDvfBtn.disabled = false;
            syncDvfBtn.textContent = originalText;
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
