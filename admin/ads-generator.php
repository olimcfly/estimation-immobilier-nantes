<?php
$pageTitle = "Générateur d'annonces";
require_once __DIR__ . '/../includes/security.php';
initSecureSession();

if (defined('CITY_NAME')) {
    $ville = CITY_NAME;
} else {
    $ville = 'Bordeaux';
}

$villesRayon = [
    'Mérignac',
    'Pessac',
    'Talence',
    'Bègles',
    'Le Bouscat',
    'Cenon',
    'Floirac',
    'Lormont',
    'Villenave-d\'Ornon',
    'Bruges',
    'Eysines',
    'Gradignan',
    'Saint-Médard-en-Jalles',
];

$siteUrl = defined('SITE_URL') ? SITE_URL : 'https://www.example.com';

$banqueTitres = [
    "Estimation Immobilière $ville",
    "Estimez Votre Bien à $ville",
    "Prix m² $ville " . date('Y'),
    "Estimation Gratuite $ville",
    "Valeur Bien $ville",
    "$ville - Estimation Pro",
    "Estimer Maison $ville",
    "Prix Immobilier $ville",
    "Estimation Locative $ville",
    "Combien Vaut Votre Bien à $ville",
    "Vendre au Meilleur Prix à $ville",
    "Agence Expert $ville",
    "Audit Valeur Bien $ville",
    "Estimation Patrimoine $ville",
    "Analyse Marché $ville",
    "Estimation en 30 Secondes",
    "Résultat Instantané",
    "100% Gratuit Sans Engagement",
    "Estimation Fiable & Rapide",
    "Combien Vaut Votre Bien ?",
    "Estimation Professionnelle",
    "Votre Bien Prend de la Valeur",
    "Estimez en Ligne Maintenant",
    "Résultat Immédiat Garanti",
    "Votre Estimation Offerte",
    "Expertise Immobilière Gratuite",
    "Estimation Sans Inscription",
    "Connaître la Valeur Réelle",
    "Évaluez Votre Logement",
    "Prix au m² Actualisé",
    "Réponse Immédiate",
    "Rapport de Valeur Complet",
    "Simulation de Prix Gratuite",
    "Valeur Actuelle de Votre Bien",
    "Les Prix Montent à $ville",
    "Est-ce le Moment de Vendre ?",
    "Marché en Hausse à $ville",
    "Ne Ratez Pas le Bon Timing",
    "Tendance Prix Immobilier",
    "Demande Forte dans Votre Secteur",
    "+500 Estimations Réalisées",
    "Noté 4.8/5 par Nos Clients",
    "Recommandé par les Experts",
    "Des Milliers de Propriétaires Convaincus",
    "Méthode Approuvée par Pros",
    "Estimez Maintenant →",
    "Découvrir Mon Prix",
    "Obtenir Mon Estimation",
    "Je Lance Mon Estimation",
    "Voir Mon Prix en 1 Min",
    "Estimation Maison $ville",
    "Estimation Appartement",
    "Prix Maison $ville",
    "Valeur Appartement $ville",
    "Estimation Studio $ville",
    "Prix Villa $ville",
    "Valeur T2/T3 en Ville",
];

$banqueDescriptions = [
    "Estimez votre bien immobilier à $ville gratuitement. Résultat instantané et sans engagement.",
    "Découvrez la valeur de votre maison ou appartement en 30 secondes. Prix au m² actualisé " . date('Y') . ".",
    "Estimation immobilière professionnelle basée sur les transactions réelles à $ville et environs.",
    "Obtenez une fourchette de prix fiable pour votre bien. Plus de 500 estimations réalisées à $ville.",
    "Votre bien a peut-être pris de la valeur. Estimez-le gratuitement en ligne. Résultat immédiat.",
    "Estimation gratuite + option RDV avec un conseiller immobilier expert de $ville. Sans obligation.",
    "Les prix évoluent à $ville. Connaissez la valeur actuelle de votre bien en quelques clics.",
    "Estimation rapide et fiable. Données basées sur le marché immobilier local de $ville.",
];

$campaignLabels = [
    'hot' => '🔥 Intention directe',
    'warm' => '🟡 Recherche info',
    'cold' => '❄️ Audience large',
];

$campaignDefaults = [
    'hot' => ['path1' => 'estimation', 'path2' => strtolower($ville)],
    'warm' => ['path1' => 'prix-immo', 'path2' => strtolower($ville)],
    'cold' => ['path1' => 'valuation', 'path2' => 'france'],
];

$campaigns = [];
foreach ($campaignLabels as $key => $label) {
    $campaigns[$key] = [
        'titres' => array_fill(0, 15, ''),
        'descriptions' => array_fill(0, 4, ''),
        'pinned' => [null, null, null],
        'final_url' => $siteUrl,
        'path1' => $campaignDefaults[$key]['path1'],
        'path2' => $campaignDefaults[$key]['path2'],
    ];
}

$pdo = null;
if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
    $pdo = $GLOBALS['pdo'];
} elseif (isset($pdo) && $pdo instanceof PDO) {
    // already set in included bootstrap
}

$flash = null;
$flashType = 'success';
$loadedDraft = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken()) {
        $flashType = 'error';
        $flash = 'Session expirée (CSRF). Rechargez la page.';
    } else {
    $action = $_POST['action'] ?? '';

    if ($action === 'save' && $pdo instanceof PDO) {
        $campaignType = $_POST['campaign_type'] ?? 'hot';
        $payload = json_decode((string)($_POST['campaign_payload'] ?? ''), true);

        if (!is_array($payload)) {
            $flashType = 'error';
            $flash = "Payload invalide.";
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO google_ads_drafts (campaign_type, titres, descriptions, final_url, path1, path2, is_active)
                 VALUES (:campaign_type, :titres, :descriptions, :final_url, :path1, :path2, 1)'
            );
            $stmt->execute([
                ':campaign_type' => $campaignType,
                ':titres' => json_encode($payload['titres'] ?? [], JSON_UNESCAPED_UNICODE),
                ':descriptions' => json_encode($payload['descriptions'] ?? [], JSON_UNESCAPED_UNICODE),
                ':final_url' => $payload['final_url'] ?? $siteUrl,
                ':path1' => substr((string)($payload['path1'] ?? ''), 0, 15),
                ':path2' => substr((string)($payload['path2'] ?? ''), 0, 15),
            ]);
            $flash = "Brouillon sauvegardé.";
        }
    }

    if ($action === 'delete' && $pdo instanceof PDO) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM google_ads_drafts WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $flash = "Brouillon supprimé.";
        }
    }
    }
}

$drafts = [];
if ($pdo instanceof PDO) {
    $stmt = $pdo->query('SELECT * FROM google_ads_drafts ORDER BY updated_at DESC LIMIT 50');
    $drafts = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    if (isset($_GET['load'])) {
        $loadId = (int)$_GET['load'];
        if ($loadId > 0) {
            $stmtLoad = $pdo->prepare('SELECT * FROM google_ads_drafts WHERE id = :id LIMIT 1');
            $stmtLoad->execute([':id' => $loadId]);
            $loadedDraft = $stmtLoad->fetch(PDO::FETCH_ASSOC);
            if ($loadedDraft) {
                $cType = $loadedDraft['campaign_type'];
                if (isset($campaigns[$cType])) {
                    $campaigns[$cType]['titres'] = array_pad(json_decode((string)$loadedDraft['titres'], true) ?: [], 15, '');
                    $campaigns[$cType]['descriptions'] = array_pad(json_decode((string)$loadedDraft['descriptions'], true) ?: [], 4, '');
                    $campaigns[$cType]['final_url'] = $loadedDraft['final_url'] ?: $siteUrl;
                    $campaigns[$cType]['path1'] = $loadedDraft['path1'] ?: $campaignDefaults[$cType]['path1'];
                    $campaigns[$cType]['path2'] = $loadedDraft['path2'] ?: $campaignDefaults[$cType]['path2'];
                    $flash = "Brouillon #{$loadId} chargé.";
                }
            }
        }
    }
}

$currentPage = 'google-ads';
$topNavCurrent = 'google-ads';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<style>
    .ads-grid { display: grid; grid-template-columns: 60% 40%; gap: 24px; }
    .card { background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; padding: 16px; margin-bottom: 16px; }
    .campaign-tabs { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
    .campaign-tab { border: 1px solid #d1d5db; background: #f9fafb; padding: 8px 12px; border-radius: 999px; cursor: pointer; }
    .campaign-tab.active { background: #111827; color: #fff; border-color: #111827; }
    .input-group { display: grid; grid-template-columns: 1fr auto auto auto; gap: 8px; margin-bottom: 8px; align-items: center; }
    .input-group input[type="text"], .input-group textarea { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 9px 10px; }
    .input-group textarea { min-height: 72px; }
    .counter { min-width: 48px; font-size: 12px; color: #4b5563; text-align: right; }
    .counter.over { color: #dc2626; font-weight: 700; }
    .mini-btn { border: 1px solid #d1d5db; background: #f9fafb; border-radius: 8px; padding: 6px 8px; cursor: pointer; }
    .draggable-item { border: 1px dashed #e5e7eb; border-radius: 10px; padding: 10px; margin-bottom: 8px; background: #fcfcfd; }
    .drag-handle { cursor: move; margin-right: 8px; color: #9ca3af; }
    .pin-badges { display: flex; gap: 6px; margin-top: 6px; }
    .pin { font-size: 11px; padding: 2px 8px; border-radius: 999px; border: 1px solid #cbd5e1; cursor: pointer; }
    .pin.active { background: #e0f2fe; border-color: #0284c7; color: #075985; }
    .toolbar { display: flex; gap: 8px; margin: 16px 0; flex-wrap: wrap; }
    .btn-primary, .btn-secondary { border: 0; border-radius: 8px; padding: 10px 12px; cursor: pointer; }
    .btn-primary { background: #111827; color: #fff; }
    .btn-secondary { background: #f3f4f6; }
    .sticky { position: sticky; top: 20px; }
    .preview-tabs { display: flex; gap: 8px; margin-bottom: 12px; }
    .preview-tab { border: 1px solid #d1d5db; border-radius: 999px; padding: 6px 10px; cursor: pointer; }
    .preview-tab.active { background: #111827; color: #fff; }
    .google-ad { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px; }
    .google-ad.mobile { max-width: 400px; background: #f8fafc; }
    .ad-badge { color: #6b7280; font-size: 12px; margin-bottom: 4px; }
    .ad-url { color: #0f9d58; font-size: 14px; margin-bottom: 6px; }
    .ad-title { color: #1a0dab; font-size: 20px; line-height: 1.3; }
    .ad-desc { color: #4b5563; font-size: 14px; margin-top: 6px; }
    .combo { border: 1px solid #e5e7eb; border-radius: 10px; padding: 8px; margin-bottom: 8px; }
    .score-list { list-style: none; padding: 0; margin: 0; }
    .score-list li { margin-bottom: 6px; font-size: 14px; }
    .flash { padding: 10px 12px; border-radius: 8px; margin-bottom: 12px; }
    .flash.success { background: #ecfdf5; color: #065f46; }
    .flash.error { background: #fef2f2; color: #991b1b; }
    .history table { width: 100%; border-collapse: collapse; }
    .history th, .history td { text-align: left; border-bottom: 1px solid #e5e7eb; padding: 8px; }
    @media (max-width: 1100px) { .ads-grid { grid-template-columns: 1fr; } }
</style>

<div class="container">
    <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
    <p>Créez des variantes d'annonces Google Ads intelligentes avec prévisualisation en temps réel et export.</p>

    <?php if ($flash): ?>
        <div class="flash <?= htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div class="ads-grid" id="adsGeneratorApp"
        data-campaigns='<?= htmlspecialchars(json_encode($campaigns, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>'
        data-templates-titles='<?= htmlspecialchars(json_encode($banqueTitres, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>'
        data-templates-descriptions='<?= htmlspecialchars(json_encode($banqueDescriptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>'>

        <div>
            <div class="card">
                <div class="campaign-tabs" id="campaignTabs">
                    <?php foreach ($campaignLabels as $key => $label): ?>
                        <button type="button" class="campaign-tab<?= $key === 'hot' ? ' active' : ''; ?>" data-campaign="<?= $key; ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></button>
                    <?php endforeach; ?>
                </div>

                <h3>Titres (15)</h3>
                <div id="titlesContainer"></div>

                <h3>Descriptions (4)</h3>
                <div id="descriptionsContainer"></div>

                <h3>URL finale</h3>
                <div class="input-group" style="grid-template-columns:1fr;">
                    <input type="text" id="finalUrl" value="<?= htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="input-group" style="grid-template-columns:1fr 1fr;">
                    <input type="text" id="path1" maxlength="15" placeholder="estimation" />
                    <input type="text" id="path2" maxlength="15" placeholder="<?= htmlspecialchars(strtolower($ville), ENT_QUOTES, 'UTF-8'); ?>" />
                </div>

                <div class="toolbar">
                    <button class="btn-secondary" type="button" id="autoFillBtn">🎲 Remplir automatiquement</button>
                    <button class="btn-secondary" type="button" id="shuffleBtn">🔄 Mélanger</button>
                </div>

                <div class="toolbar">
                    <form method="post" id="saveForm" style="display:inline-flex; gap:8px;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="save" />
                        <input type="hidden" name="campaign_type" id="campaignTypeField" value="hot" />
                        <input type="hidden" name="campaign_payload" id="campaignPayloadField" />
                        <button class="btn-primary" type="submit">💾 Sauvegarder ce brouillon</button>
                    </form>
                    <button class="btn-secondary" type="button" id="copyBtn">📋 Copier pour Google Ads</button>
                    <button class="btn-secondary" type="button" id="exportBtn">📥 Exporter CSV Google Ads Editor</button>
                </div>
            </div>

            <div class="card history">
                <h3>Historique des brouillons</h3>
                <?php if (!$pdo): ?>
                    <p>Base de données non détectée (variable PDO manquante), historique indisponible.</p>
                <?php elseif (empty($drafts)): ?>
                    <p>Aucun brouillon pour le moment.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Campagne</th>
                                <th>Titre principal</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($drafts as $draft):
                            $titles = json_decode((string)$draft['titres'], true) ?: [];
                            $mainTitle = $titles[0] ?? '(vide)';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($draft['updated_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars($campaignLabels[$draft['campaign_type']] ?? $draft['campaign_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars($mainTitle, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <a href="?load=<?= (int)$draft['id']; ?>">charger</a>
                                    |
                                    <a href="#" class="duplicate-link" data-draft='<?= htmlspecialchars(json_encode($draft, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>'>dupliquer</a>
                                    |
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce brouillon ?');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete" />
                                        <input type="hidden" name="id" value="<?= (int)$draft['id']; ?>" />
                                        <button type="submit" style="border:0;background:none;color:#b91c1c;cursor:pointer;">supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="card sticky">
                <h3>Aperçu Google</h3>
                <div class="preview-tabs" id="previewTabs">
                    <button type="button" class="preview-tab active" data-preview="mobile">📱 Mobile</button>
                    <button type="button" class="preview-tab" data-preview="desktop">💻 Desktop</button>
                    <button type="button" class="preview-tab" data-preview="combos">📊 Toutes les combinaisons</button>
                </div>

                <div id="preview-mobile" class="google-ad mobile">
                    <div class="ad-badge">Sponsorisé</div>
                    <div class="ad-url" id="mobileUrl"></div>
                    <div class="ad-title" id="mobileTitle"></div>
                    <div class="ad-desc" id="mobileDesc"></div>
                </div>

                <div id="preview-desktop" class="google-ad" style="display:none;">
                    <div class="ad-badge">Sponsorisé</div>
                    <div class="ad-url" id="desktopUrl"></div>
                    <div class="ad-title" id="desktopTitle"></div>
                    <div class="ad-desc" id="desktopDesc"></div>
                </div>

                <div id="preview-combos" style="display:none;">
                    <div id="combinationsList"></div>
                    <button class="btn-secondary" type="button" id="newCombosBtn">🔄 Voir d'autres combinaisons</button>
                </div>

                <hr style="margin:16px 0;" />
                <h4>Quality Score estimé</h4>
                <ul class="score-list" id="qualityList"></ul>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const app = document.getElementById('adsGeneratorApp');
    if (!app) return;

    const campaigns = JSON.parse(app.dataset.campaigns || '{}');
    const titleBank = JSON.parse(app.dataset.templatesTitles || '[]');
    const descBank = JSON.parse(app.dataset.templatesDescriptions || '[]');
    const placeholders = [
        'Estimation Immobilière Bordeaux', 'Estimez Votre Bien Maintenant', 'Prix m² actualisé',
        'Combien vaut votre maison ?', 'Résultat en 30 secondes', 'Estimation gratuite et rapide',
        'Valeur bien + ville', 'Expertise immobilière locale', 'Découvrez votre prix',
        'Votre estimation offerte', 'Marché en hausse ?', 'Noté 4.8/5',
        'Obtenir mon estimation', 'Recommandé par experts', 'Sans engagement'
    ];

    let currentCampaign = 'hot';

    const titlesContainer = document.getElementById('titlesContainer');
    const descriptionsContainer = document.getElementById('descriptionsContainer');
    const finalUrl = document.getElementById('finalUrl');
    const path1 = document.getElementById('path1');
    const path2 = document.getElementById('path2');

    function randomUnique(bank, count) {
        const cloned = [...bank];
        for (let i = cloned.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [cloned[i], cloned[j]] = [cloned[j], cloned[i]];
        }
        return cloned.slice(0, count);
    }

    function renderEditor() {
        const state = campaigns[currentCampaign];
        document.getElementById('campaignTypeField').value = currentCampaign;

        titlesContainer.innerHTML = '';
        state.titres.forEach((value, index) => {
            const item = document.createElement('div');
            item.className = 'draggable-item';
            item.draggable = true;
            item.dataset.index = index;
            item.innerHTML = `
                <div class="input-group">
                    <span class="drag-handle">↕️</span>
                    <input type="text" class="title-input" data-index="${index}" placeholder="${placeholders[index] || 'Titre'}" value="${(value || '').replace(/"/g, '&quot;')}">
                    <span class="counter title-counter">${(value || '').length}/30</span>
                    <div style="display:flex;gap:4px;">
                        <button class="mini-btn suggest-title" data-index="${index}" type="button">🎲</button>
                        <button class="mini-btn clear-title" data-index="${index}" type="button">❌</button>
                    </div>
                </div>
                <div class="pin-badges">
                    ${[1,2,3].map(pos => `<span class="pin ${(state.pinned[pos - 1] === index) ? 'active' : ''}" data-pos="${pos}" data-index="${index}">Épinglé position ${pos}</span>`).join('')}
                </div>
            `;
            titlesContainer.appendChild(item);
        });

        descriptionsContainer.innerHTML = '';
        state.descriptions.forEach((value, index) => {
            const row = document.createElement('div');
            row.className = 'input-group';
            row.style.gridTemplateColumns = '1fr auto auto auto';
            row.innerHTML = `
                <textarea class="desc-input" data-index="${index}" placeholder="Description ${index + 1}">${value || ''}</textarea>
                <span class="counter desc-counter">${(value || '').length}/90</span>
                <button class="mini-btn suggest-desc" data-index="${index}" type="button">🎲</button>
                <button class="mini-btn clear-desc" data-index="${index}" type="button">❌</button>
            `;
            descriptionsContainer.appendChild(row);
        });

        finalUrl.value = state.final_url || '';
        path1.value = state.path1 || '';
        path2.value = state.path2 || '';

        bindEditorEvents();
        refreshPreview();
    }

    function bindEditorEvents() {
        titlesContainer.querySelectorAll('.title-input').forEach(input => {
            input.addEventListener('input', (e) => {
                const i = Number(e.target.dataset.index);
                campaigns[currentCampaign].titres[i] = e.target.value;
                const counter = e.target.closest('.input-group').querySelector('.title-counter');
                counter.textContent = `${e.target.value.length}/30`;
                counter.classList.toggle('over', e.target.value.length > 30);
                refreshPreview();
            });
        });

        titlesContainer.querySelectorAll('.suggest-title').forEach(btn => {
            btn.addEventListener('click', () => {
                const i = Number(btn.dataset.index);
                campaigns[currentCampaign].titres[i] = titleBank[Math.floor(Math.random() * titleBank.length)] || '';
                renderEditor();
            });
        });

        titlesContainer.querySelectorAll('.clear-title').forEach(btn => {
            btn.addEventListener('click', () => {
                const i = Number(btn.dataset.index);
                campaigns[currentCampaign].titres[i] = '';
                renderEditor();
            });
        });

        titlesContainer.querySelectorAll('.pin').forEach(pin => {
            pin.addEventListener('click', () => {
                const pos = Number(pin.dataset.pos) - 1;
                const index = Number(pin.dataset.index);
                campaigns[currentCampaign].pinned[pos] = campaigns[currentCampaign].pinned[pos] === index ? null : index;
                renderEditor();
            });
        });

        descriptionsContainer.querySelectorAll('.desc-input').forEach(textarea => {
            textarea.addEventListener('input', (e) => {
                const i = Number(e.target.dataset.index);
                campaigns[currentCampaign].descriptions[i] = e.target.value;
                const counter = e.target.parentElement.querySelector('.desc-counter');
                counter.textContent = `${e.target.value.length}/90`;
                counter.classList.toggle('over', e.target.value.length > 90);
                refreshPreview();
            });
        });

        descriptionsContainer.querySelectorAll('.suggest-desc').forEach(btn => {
            btn.addEventListener('click', () => {
                const i = Number(btn.dataset.index);
                campaigns[currentCampaign].descriptions[i] = descBank[Math.floor(Math.random() * descBank.length)] || '';
                renderEditor();
            });
        });

        descriptionsContainer.querySelectorAll('.clear-desc').forEach(btn => {
            btn.addEventListener('click', () => {
                const i = Number(btn.dataset.index);
                campaigns[currentCampaign].descriptions[i] = '';
                renderEditor();
            });
        });

        let dragSource = null;
        titlesContainer.querySelectorAll('.draggable-item').forEach(item => {
            item.addEventListener('dragstart', () => { dragSource = Number(item.dataset.index); });
            item.addEventListener('dragover', (e) => e.preventDefault());
            item.addEventListener('drop', () => {
                const target = Number(item.dataset.index);
                if (dragSource === null || dragSource === target) return;
                const arr = campaigns[currentCampaign].titres;
                const [moved] = arr.splice(dragSource, 1);
                arr.splice(target, 0, moved);
                campaigns[currentCampaign].pinned = campaigns[currentCampaign].pinned.map(pinIndex => pinIndex === dragSource ? target : pinIndex);
                renderEditor();
            });
        });

        [finalUrl, path1, path2].forEach((el) => {
            el.addEventListener('input', () => {
                campaigns[currentCampaign].final_url = finalUrl.value;
                campaigns[currentCampaign].path1 = path1.value.slice(0, 15);
                campaigns[currentCampaign].path2 = path2.value.slice(0, 15);
                refreshPreview();
            });
        });
    }

    function pickNonEmpty(values, count) {
        return values.filter(Boolean).slice(0, count);
    }

    function buildUrl(state) {
        const cleaned = [state.final_url.replace(/\/$/, ''), state.path1, state.path2].filter(Boolean);
        return cleaned.join('/');
    }

    function refreshPreview() {
        const state = campaigns[currentCampaign];
        const titles = pickNonEmpty(state.titres, 3);
        const desc = pickNonEmpty(state.descriptions, 2).join(' ');
        const titleText = titles.join(' | ') || 'Titre 1 | Titre 2 | Titre 3';
        const url = buildUrl(state);

        document.getElementById('mobileUrl').textContent = url;
        document.getElementById('desktopUrl').textContent = url;
        document.getElementById('mobileTitle').textContent = titleText;
        document.getElementById('desktopTitle').textContent = titleText;
        document.getElementById('mobileDesc').textContent = desc || 'Description de l’annonce.';
        document.getElementById('desktopDesc').textContent = desc || 'Description de l’annonce.';

        renderCombinations();
        renderQualityScore();
    }

    function renderCombinations() {
        const state = campaigns[currentCampaign];
        const titles = state.titres.filter(Boolean);
        const descs = state.descriptions.filter(Boolean);
        const combos = document.getElementById('combinationsList');
        combos.innerHTML = '';

        for (let i = 0; i < 6; i++) {
            const t = randomUnique(titles.length ? titles : ['Titre 1', 'Titre 2', 'Titre 3'], 3);
            const d = randomUnique(descs.length ? descs : ['Description 1', 'Description 2'], 2);
            const block = document.createElement('div');
            block.className = 'combo';
            block.innerHTML = `<div class="ad-title" style="font-size:16px;">${t.join(' | ')}</div><div class="ad-desc">${d.join(' ')}</div>`;
            combos.appendChild(block);
        }
    }

    function renderQualityScore() {
        const state = campaigns[currentCampaign];
        const allTitles = state.titres.filter(Boolean);
        const allDescriptions = state.descriptions.filter(Boolean);

        const titleRelevant = allTitles.some(t => /estimation|<?= strtolower(addslashes($ville)); ?>/i.test(t));
        const uniqueTitles = new Set(allTitles.map(t => t.toLowerCase())).size;
        const descLengthsOk = allDescriptions.every(d => d.length <= 90) && allDescriptions.length >= 2;
        const hasCTA = [...allTitles, ...allDescriptions].some(v => /estimez|obtenir|découvrir|maintenant|lance/i.test(v));
        const hasVille = [...allTitles, ...allDescriptions].some(v => new RegExp('<?= addslashes($ville); ?>', 'i').test(v));

        let score = 0;
        if (titleRelevant) score++;
        if (uniqueTitles >= 10) score++;
        if (descLengthsOk) score++;
        if (hasCTA) score++;
        if (hasVille) score++;

        const global = score >= 5 ? 'Excellent' : (score >= 3 ? 'Bon' : 'À améliorer');

        const list = document.getElementById('qualityList');
        list.innerHTML = `
            <li>Pertinence des titres : ${titleRelevant ? '✅' : '⚠️'}</li>
            <li>Nombre de titres uniques : ${uniqueTitles}/15 ${uniqueTitles >= 10 ? '✅' : '⚠️'}</li>
            <li>Longueur des descriptions : ${descLengthsOk ? '✅' : '⚠️'}</li>
            <li>Présence CTA : ${hasCTA ? '✅' : '⚠️'}</li>
            <li>Présence de la ville : ${hasVille ? '✅' : '⚠️'}</li>
            <li><strong>Score global : ${global}</strong></li>
        `;
    }

    document.querySelectorAll('.campaign-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.campaign-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            currentCampaign = tab.dataset.campaign;
            renderEditor();
        });
    });

    document.getElementById('autoFillBtn').addEventListener('click', () => {
        const randomTitles = randomUnique(titleBank, 15);
        const randomDescs = randomUnique(descBank, 4);
        campaigns[currentCampaign].titres = randomTitles;
        campaigns[currentCampaign].descriptions = randomDescs;
        renderEditor();
    });

    document.getElementById('shuffleBtn').addEventListener('click', () => {
        campaigns[currentCampaign].titres = randomUnique(campaigns[currentCampaign].titres, campaigns[currentCampaign].titres.length);
        campaigns[currentCampaign].descriptions = randomUnique(campaigns[currentCampaign].descriptions, campaigns[currentCampaign].descriptions.length);
        renderEditor();
    });

    document.getElementById('newCombosBtn').addEventListener('click', renderCombinations);

    document.querySelectorAll('.preview-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.preview-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            ['mobile', 'desktop', 'combos'].forEach(name => {
                document.getElementById(`preview-${name}`).style.display = name === tab.dataset.preview ? 'block' : 'none';
            });
        });
    });

    document.getElementById('copyBtn').addEventListener('click', async () => {
        const st = campaigns[currentCampaign];
        const text = [
            `Campagne: ${currentCampaign}`,
            'Titres:',
            ...st.titres.map((t, i) => `${i + 1}. ${t}`),
            'Descriptions:',
            ...st.descriptions.map((d, i) => `${i + 1}. ${d}`),
            `URL finale: ${buildUrl(st)}`,
        ].join('\n');

        await navigator.clipboard.writeText(text);
        alert('Copié dans le presse-papiers ✅');
    });

    document.getElementById('exportBtn').addEventListener('click', () => {
        const st = campaigns[currentCampaign];
        const headers = ['Campaign', 'Final URL', 'Path1', 'Path2'];
        for (let i = 1; i <= 15; i++) headers.push(`Headline ${i}`);
        for (let i = 1; i <= 4; i++) headers.push(`Description ${i}`);

        const row = [currentCampaign, st.final_url, st.path1, st.path2, ...st.titres, ...st.descriptions]
            .map(v => `"${(v || '').replace(/"/g, '""')}"`).join(',');
        const csv = headers.join(',') + '\n' + row;

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `google-ads-${currentCampaign}.csv`;
        a.click();
    });

    document.getElementById('saveForm').addEventListener('submit', () => {
        document.getElementById('campaignPayloadField').value = JSON.stringify(campaigns[currentCampaign]);
    });

    document.querySelectorAll('.duplicate-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            try {
                const draft = JSON.parse(link.dataset.draft);
                const type = draft.campaign_type;
                if (!campaigns[type]) return;
                campaigns[type].titres = JSON.parse(draft.titres || '[]');
                campaigns[type].descriptions = JSON.parse(draft.descriptions || '[]');
                campaigns[type].final_url = draft.final_url || campaigns[type].final_url;
                campaigns[type].path1 = draft.path1 || campaigns[type].path1;
                campaigns[type].path2 = draft.path2 || campaigns[type].path2;
                currentCampaign = type;
                document.querySelectorAll('.campaign-tab').forEach(t => t.classList.remove('active'));
                document.querySelector(`.campaign-tab[data-campaign="${type}"]`)?.classList.add('active');
                renderEditor();
            } catch (err) {
                console.error(err);
            }
        });
    });

    renderEditor();
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
