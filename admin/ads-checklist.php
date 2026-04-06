<?php
session_start();

$pageTitle = "Google Ads - Checklist de lancement";
$currentPage = 'google-ads';
$topNavCurrent = 'google-ads';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    exit('Accès non autorisé');
}

$ville = defined('CITY_NAME') ? CITY_NAME : 'Votre ville';
$rayon = $rayon ?? 15;
$siteUrl = $siteUrl ?? '/';

$allSteps = [
    // Phase 1 : Préparation (1-8)
    [
        'phase' => 'phase_1',
        'key' => 'prep_account',
        'title' => 'Créer votre compte Google Ads',
        'desc' => 'Rendez-vous sur ads.google.com avec votre compte Google.',
        'details' => "1) Ouvrez ads.google.com.\n2) Connectez-vous avec votre compte Google principal.\n3) Cliquez sur \"Commencer\".\n4) Ignorez la création rapide de campagne.\n5) Validez les informations de base de votre entreprise.",
        'link' => 'https://ads.google.com',
        'link_text' => '↗ Ouvrir Google Ads',
        'time' => '~5 min',
    ],
    [
        'phase' => 'phase_1',
        'key' => 'prep_expert_mode',
        'title' => 'Passer en mode Expert',
        'desc' => 'Le mode intelligent limite vos options. Passez en mode Expert.',
        'details' => "Quand Google vous propose de créer votre première campagne, cliquez sur le lien discret en bas \"Passer en mode Expert\" ou \"Créer un compte sans campagne\".",
        'time' => '~1 min',
    ],
    [
        'phase' => 'phase_1',
        'key' => 'prep_billing',
        'title' => 'Configurer la facturation',
        'desc' => 'Ajoutez votre moyen de paiement pour activer le compte.',
        'details' => "Outils > Facturation > Paramètres > Ajouter un mode de paiement. Google facture après 500€ dépensés OU le 30 du mois.",
        'time' => '~2 min',
    ],
    [
        'phase' => 'phase_1',
        'key' => 'prep_gtag',
        'title' => 'Installer le code de suivi sur votre site',
        'desc' => 'Ajoutez le code Google Ads/Analytics sur votre site EstimIA.',
        'details' => "Copiez le code ci-dessous et ajoutez-le dans Paramètres > Intégrations (ou header.php) :\n\n<code>&lt;script async src=\"https://www.googletagmanager.com/gtag/js?id=AW-XXXXXXXXX\"&gt;&lt;/script&gt;\n&lt;script&gt;\nwindow.dataLayer = window.dataLayer || [];\nfunction gtag(){dataLayer.push(arguments);}\ngtag('js', new Date());\ngtag('config', 'AW-XXXXXXXXX');\n&lt;/script&gt;</code>",
        'time' => '~3 min',
    ],
    [
        'phase' => 'phase_1',
        'key' => 'prep_conversion1',
        'title' => "Créer la conversion 'Estimation soumise'",
        'desc' => 'Créez votre objectif principal de génération de leads.',
        'details' => "Google Ads > Outils > Conversions > + Nouvelle conversion > Site Web. Nom : Estimation soumise. Catégorie : Prospect. Valeur : 35€. Comptabilisation : Une. Fenêtre de conversion : 30 jours. Enregistrez puis récupérez le tag si nécessaire.",
        'time' => '~5 min',
    ],
    [
        'phase' => 'phase_1',
        'key' => 'prep_conversion2',
        'title' => "Créer la conversion 'RDV pris'",
        'desc' => 'Ajoutez une conversion secondaire pour qualifier la qualité du trafic.',
        'details' => "Même procédure que l'étape précédente. Nom : RDV pris. Catégorie : Prospect qualifié. Valeur : 80€. Comptabilisation : Une.",
        'time' => '~3 min',
    ],
    [
        'phase' => 'phase_1',
        'key' => 'prep_audience_remarketing',
        'title' => "Créer l'audience de remarketing",
        'desc' => 'Ciblez les visiteurs qui ont fait une estimation mais pas pris RDV.',
        'details' => "Outils > Gestion des audiences > + Segment. Inclure URL contient /estimation-validee. Exclure URL contient /rdv-confirme. Durée d'adhésion : 30 jours.",
        'time' => '~5 min',
    ],
    [
        'phase' => 'phase_1',
        'key' => 'prep_test_conversion',
        'title' => 'Tester le suivi de conversion',
        'desc' => 'Faites une estimation test et vérifiez que la conversion remonte.',
        'details' => "1) Ouvrez votre site.\n2) Faites une estimation test.\n3) Attendez 24h.\n4) Vérifiez dans Google Ads > Outils > Conversions.",
        'link' => $siteUrl,
        'link_text' => '↗ Ouvrir votre site',
        'time' => '~2 min',
    ],

    // Phase 2 : Campagne intention directe 🔥 (9-20)
    [
        'phase' => 'phase_2',
        'key' => 'hot_create_campaign',
        'title' => "Créer la campagne 'Estimation Directe'",
        'desc' => 'Créez la campagne orientée prospects à forte intention.',
        'details' => "1) Google Ads > Campagnes > + Nouvelle campagne\n2) Objectif : Prospects\n3) Type : Réseau de recherche\n4) Nom : EstimIA - Intention Directe\n5) Continuer",
        'time' => '~2 min',
    ],
    [
        'phase' => 'phase_2',
        'key' => 'hot_budget',
        'title' => 'Définir le budget quotidien',
        'desc' => 'Budget recommandé : ' . round(500 * 0.6 / 30, 2) . '€/jour (basé sur 500€/mois, 60% sur cette campagne).',
        'details' => 'Vous pourrez ajuster plus tard. Commencez conservateur et augmentez si ça fonctionne.',
        'time' => '~1 min',
    ],
    [
        'phase' => 'phase_2',
        'key' => 'hot_bidding',
        'title' => "Configurer la stratégie d'enchères",
        'desc' => "Sélectionnez 'Maximiser les conversions' pour démarrer.",
        'details' => "Maximiser les conversions est idéale sans historique. Après 30+ conversions, testez CPA cible. Évitez CPC manuel au démarrage.",
        'time' => '~1 min',
    ],
    [
        'phase' => 'phase_2',
        'key' => 'hot_location',
        'title' => 'Configurer le ciblage géographique',
        'desc' => "Ciblez {$ville} + rayon de {$rayon} km.",
        'details' => "Paramètres > Zones géographiques > Saisir {$ville} puis rayon {$rayon} km. Option de présence : personnes situées dans la zone ciblée.",
        'time' => '~2 min',
    ],
    [
        'phase' => 'phase_2',
        'key' => 'hot_language',
        'title' => 'Définir les langues',
        'desc' => 'Français uniquement pour conserver la pertinence locale.',
        'details' => 'Paramètres > Langues > Français. Supprimez les autres langues suggérées.',
        'time' => '~1 min',
    ],
    [
        'phase' => 'phase_2',
        'key' => 'hot_network',
        'title' => 'Désactiver le Réseau Display',
        'desc' => 'Gardez uniquement le Réseau de recherche au lancement.',
        'details' => 'Dans les paramètres de campagne, décochez les partenaires Display pour éviter un trafic froid.',
        'time' => '~1 min',
    ],
    [
        'phase' => 'phase_2',
        'key' => 'hot_adgroup1',
        'title' => 'Créer le groupe d’annonces "Estimation"',
        'desc' => 'Regroupez les mots-clés à intention immédiate.',
        'details' => 'Nommez le groupe "Estimation immédiate". Ajoutez uniquement des requêtes transactionnelles.',
        'time' => '~2 min',
    ],
    [
        'phase' => 'phase_2',
        'key' => 'hot_keywords',
        'title' => 'Ajouter les mots-clés principaux',
        'desc' => 'Exemple : [estimation maison bordeaux], [prix maison bordeaux].',
        'details' => 'Privilégiez les correspondances exacte et expression. Limitez à 10-15 mots-clés ultra qualifiés.',
        'time' => '~4 min',
    ],
    [
        'phase' => 'phase_2',
        'key' => 'hot_negative_keywords',
        'title' => 'Ajouter des mots-clés négatifs',
        'desc' => 'Filtrez les intentions non vendeuses.',
        'details' => 'Ajoutez : gratuit, location, emploi, formation, stage, notaire, cadeau, définition.',
        'time' => '~2 min',
    ],
    [
        'phase' => 'phase_2',
        'key' => 'hot_rsa',
        'title' => 'Créer une annonce RSA performante',
        'desc' => 'Rédigez 12 titres et 4 descriptions orientés conversion.',
        'details' => 'Incluez la ville, la rapidité, la preuve sociale et un appel à action fort. Épinglez 1 titre marque si besoin.',
        'time' => '~8 min',
    ],
    [
        'phase' => 'phase_2',
        'key' => 'hot_assets',
        'title' => 'Ajouter les assets d’annonce',
        'desc' => 'Liens annexes, accroches, extrait de site et appel.',
        'details' => 'Ajoutez 4 liens annexes minimum, 8 accroches, 1 extension d’appel et 1 extension de lieu si pertinent.',
        'time' => '~5 min',
    ],
    [
        'phase' => 'phase_2',
        'key' => 'hot_launch',
        'title' => 'Publier la campagne',
        'desc' => 'Vérifiez puis lancez la campagne intention directe.',
        'details' => 'Passez en revue les paramètres, vérifiez les conversions sélectionnées puis cliquez sur Publier.',
        'time' => '~1 min',
    ],

    // Phase 3 : Campagne comparaison (21-30)
    ['phase' => 'phase_3', 'key' => 'warm_create_campaign', 'title' => 'Créer la campagne "Comparaison"', 'desc' => 'Ciblez les prospects en phase de comparaison.', 'details' => 'Dupliquez la campagne 1 et renommez-la EstimIA - Comparaison.', 'time' => '~2 min'],
    ['phase' => 'phase_3', 'key' => 'warm_budget', 'title' => 'Allouer le budget', 'desc' => 'Allouez 25% du budget mensuel à cette campagne.', 'details' => 'Budget conseillé: 4,17€/jour sur base 500€/mois.', 'time' => '~1 min'],
    ['phase' => 'phase_3', 'key' => 'warm_keywords', 'title' => 'Ajouter mots-clés comparaison', 'desc' => 'Ex: meilleure agence immo bordeaux.', 'details' => 'Ajoutez des requêtes "avis", "comparatif", "meilleur".', 'time' => '~3 min'],
    ['phase' => 'phase_3', 'key' => 'warm_ads', 'title' => 'Adapter les annonces', 'desc' => 'Mettez en avant les différences EstimIA.', 'details' => 'Insistez sur rapidité, transparence, accompagnement local.', 'time' => '~5 min'],
    ['phase' => 'phase_3', 'key' => 'warm_lp', 'title' => 'Vérifier la landing page', 'desc' => 'Adéquation message-annonce-page.', 'details' => 'La promesse de l’annonce doit être visible au-dessus de la ligne de flottaison.', 'time' => '~3 min'],
    ['phase' => 'phase_3', 'key' => 'warm_extensions', 'title' => 'Compléter les extensions', 'desc' => 'Rassurez avec preuves sociales.', 'details' => 'Ajoutez avis clients, zones couvertes, estimation gratuite.', 'time' => '~2 min'],
    ['phase' => 'phase_3', 'key' => 'warm_schedule', 'title' => 'Programmer la diffusion', 'desc' => 'Privilégiez heures ouvrées au début.', 'details' => 'Diffusion 8h-20h pour limiter les leads hors traitement.', 'time' => '~1 min'],
    ['phase' => 'phase_3', 'key' => 'warm_review', 'title' => 'Contrôler la cohérence', 'desc' => 'Faites une relecture complète avant activation.', 'details' => 'Budget, mots-clés, annonces, conversions, URL finale.', 'time' => '~2 min'],
    ['phase' => 'phase_3', 'key' => 'warm_publish', 'title' => 'Lancer la campagne', 'desc' => 'Publiez la campagne comparaison.', 'details' => 'Cliquez Publier après validation finale.', 'time' => '~1 min'],
    ['phase' => 'phase_3', 'key' => 'warm_quality_score', 'title' => 'Vérifier le niveau de qualité', 'desc' => 'Inspectez le Quality Score après premiers clics.', 'details' => 'Ajustez RSA/LP si score < 6/10.', 'time' => '~3 min'],

    // Phase 4 : Remarketing & optimisation (31-42)
    ['phase' => 'phase_4', 'key' => 'ret_create_campaign', 'title' => 'Créer campagne remarketing', 'desc' => 'Reciblez les visiteurs chauds non convertis.', 'details' => 'Créer campagne Display/Discovery dédiée au remarketing.', 'time' => '~3 min'],
    ['phase' => 'phase_4', 'key' => 'ret_audience_link', 'title' => 'Associer audience remarketing', 'desc' => 'Sélectionnez l’audience créée en phase 1.', 'details' => 'Ajoutez segment "estimation sans RDV".', 'time' => '~2 min'],
    ['phase' => 'phase_4', 'key' => 'ret_budget', 'title' => 'Définir budget remarketing', 'desc' => 'Allouez 15% du budget total.', 'details' => 'Budget conseillé: 2,50€/jour.', 'time' => '~1 min'],
    ['phase' => 'phase_4', 'key' => 'ret_creatives', 'title' => 'Créer messages de relance', 'desc' => 'Annonce axée réassurance et prise de RDV.', 'details' => 'Incluez bénéfice + urgence douce + CTA.', 'time' => '~5 min'],
    ['phase' => 'phase_4', 'key' => 'ret_frequency_cap', 'title' => 'Limiter la fréquence', 'desc' => 'Évitez la sur-sollicitation.', 'details' => 'Cap conseillé: 3 impressions/jour/utilisateur.', 'time' => '~1 min'],
    ['phase' => 'phase_4', 'key' => 'opt_search_terms', 'title' => 'Analyser les termes de recherche', 'desc' => 'Ajoutez chaque semaine des négatifs.', 'details' => 'Rapport termes > exclure les requêtes non pertinentes.', 'time' => '~5 min'],
    ['phase' => 'phase_4', 'key' => 'opt_device_bid', 'title' => 'Ajuster par appareil', 'desc' => 'Réallouez selon performance mobile/desktop.', 'details' => 'Augmentez l’appareil le plus rentable de +10% à +20%.', 'time' => '~3 min'],
    ['phase' => 'phase_4', 'key' => 'opt_geo_bid', 'title' => 'Ajuster par zone', 'desc' => 'Priorisez les zones à meilleur taux de conversion.', 'details' => 'Analysez ville/quartier et ajustez enchères.', 'time' => '~3 min'],
    ['phase' => 'phase_4', 'key' => 'opt_ad_test', 'title' => 'Lancer un A/B test annonces', 'desc' => 'Testez 2 angles marketing différents.', 'details' => 'Exemple: rapidité vs précision. Gardez un seul changement à la fois.', 'time' => '~4 min'],
    ['phase' => 'phase_4', 'key' => 'opt_landing_test', 'title' => 'Tester la landing page', 'desc' => 'Améliorez le taux de conversion sur site.', 'details' => 'Testez titre, CTA, preuve sociale, longueur formulaire.', 'time' => '~6 min'],
    ['phase' => 'phase_4', 'key' => 'opt_weekly_dashboard', 'title' => 'Mettre en place un suivi hebdo', 'desc' => 'Créez un tableau CPC / Conv / CPA / RDV.', 'details' => 'Suivi chaque lundi pour décisions rapides.', 'time' => '~4 min'],
    ['phase' => 'phase_4', 'key' => 'opt_scale', 'title' => 'Scaler les campagnes gagnantes', 'desc' => 'Augmentez progressivement les budgets performants.', 'details' => 'Augmentez max +15% par semaine pour préserver l’algorithme.', 'time' => '~2 min'],
];

$phaseMeta = [
    'phase_1' => ['title' => 'PHASE 1 : PRÉPARATION', 'icon' => '🧱', 'count' => 8],
    'phase_2' => ['title' => 'PHASE 2 : CAMPAGNE INTENTION DIRECTE 🔥', 'icon' => '🔥', 'count' => 12],
    'phase_3' => ['title' => 'PHASE 3 : CAMPAGNE COMPARAISON', 'icon' => '⚖️', 'count' => 10],
    'phase_4' => ['title' => 'PHASE 4 : REMARKETING & OPTIMISATION', 'icon' => '📈', 'count' => 12],
];

$totalSteps = 42;
$progress = [];

$stmt = $db->prepare("SELECT step_key, completed FROM ads_checklist_progress WHERE admin_id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $progress[$r['step_key']] = (int) $r['completed'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_step'])) {
    if (!validateCsrfToken()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Token CSRF invalide'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stepKey = trim((string) $_POST['toggle_step']);
    $isCompleted = isset($_POST['completed']) ? (int) $_POST['completed'] : null;

    if ($stepKey !== '' && in_array($isCompleted, [0, 1], true)) {
        $upsert = $db->prepare(
            "INSERT INTO ads_checklist_progress (admin_id, step_key, completed, completed_at)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE completed = VALUES(completed), completed_at = VALUES(completed_at)"
        );

        $completedAt = $isCompleted === 1 ? date('Y-m-d H:i:s') : null;
        $upsert->execute([$_SESSION['admin_id'], $stepKey, $isCompleted, $completedAt]);

        $progress[$stepKey] = $isCompleted;
    }

    $completedSteps = count(array_filter($progress));
    $percent = $totalSteps > 0 ? (int) round(($completedSteps / $totalSteps) * 100) : 0;

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'completedSteps' => $completedSteps,
        'totalSteps' => $totalSteps,
        'percent' => $percent,
    ]);
    exit;
}

$completedSteps = count(array_filter($progress));
$percent = $totalSteps > 0 ? (int) round(($completedSteps / $totalSteps) * 100) : 0;

if ($percent < 25) {
    $badge = '🚀 Démarrage';
} elseif ($percent < 50) {
    $badge = '⚡ En cours';
} elseif ($percent < 75) {
    $badge = '🔥 Bientôt prêt';
} elseif ($percent < 100) {
    $badge = '✨ Presque fini !';
} else {
    $badge = '🎉 Campagnes prêtes !';
}

$stepsByPhase = [];
foreach ($allSteps as $step) {
    $stepsByPhase[$step['phase']][] = $step;
}
?>

<div class="sticky top-0 z-20 bg-white border-b shadow-sm py-4 mb-6">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <p id="progressLabel" class="font-semibold text-gray-800">Progression : <?= $completedSteps ?>/<?= $totalSteps ?> étapes (<?= $percent ?>%)</p>
            <span id="progressBadge" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100"><?= htmlspecialchars($badge) ?></span>
        </div>
        <div class="mt-3 bg-gray-200 rounded-full h-3 overflow-hidden">
            <div id="progressBar" class="h-3 bg-gradient-to-r from-blue-500 to-green-500 transition-all duration-500" style="width: <?= $percent ?>%"></div>
        </div>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 pb-12">
    <?php foreach ($phaseMeta as $phaseKey => $meta): ?>
        <?php
        $phaseSteps = $stepsByPhase[$phaseKey] ?? [];
        $phaseCompleted = 0;
        foreach ($phaseSteps as $st) {
            $phaseCompleted += !empty($progress[$st['key']]) ? 1 : 0;
        }
        ?>
        <div class="bg-white rounded-xl border mb-5 overflow-hidden">
            <button type="button" class="w-full flex items-center justify-between p-5 text-left hover:bg-gray-50 transition" onclick="togglePhase('<?= $phaseKey ?>')">
                <div class="flex items-center gap-3">
                    <span><?= $meta['icon'] ?></span>
                    <h3 class="font-bold text-gray-800"><?= htmlspecialchars($meta['title']) ?></h3>
                    <span class="text-sm text-gray-500"><?= $phaseCompleted ?>/<?= $meta['count'] ?> ✓</span>
                </div>
                <span id="chevron-<?= $phaseKey ?>" class="text-gray-400">⌄</span>
            </button>

            <div id="<?= $phaseKey ?>" class="border-t">
                <?php foreach ($phaseSteps as $step): ?>
                    <?php $done = !empty($progress[$step['key']]); ?>
                    <div class="step flex items-start gap-4 p-4 border-b last:border-0 hover:bg-gray-50 transition" data-step="<?= htmlspecialchars($step['key']) ?>">
                        <button onclick="toggleStep('<?= htmlspecialchars($step['key']) ?>')" class="mt-1 text-xl" aria-label="Basculer étape">
                            <?= $done ? '✅' : '⬜' ?>
                        </button>

                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($step['title']) ?></h4>
                            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($step['desc']) ?></p>

                            <details class="mt-3">
                                <summary class="text-sm text-primary cursor-pointer">📖 Voir les instructions détaillées</summary>
                                <div class="mt-3 p-4 bg-gray-50 rounded-lg text-sm whitespace-pre-line"><?= $step['details'] ?></div>
                            </details>

                            <?php if (!empty($step['link'])): ?>
                                <a href="<?= htmlspecialchars($step['link']) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1 mt-2 text-sm text-primary">
                                    <?= htmlspecialchars($step['link_text'] ?? '↗ Ouvrir') ?>
                                </a>
                            <?php endif; ?>
                        </div>

                        <span class="text-xs text-gray-400 whitespace-nowrap"><?= htmlspecialchars($step['time']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
const csrfToken = <?= json_encode(csrfToken(), JSON_UNESCAPED_UNICODE) ?>;

function getBadge(percent) {
    if (percent < 25) return '🚀 Démarrage';
    if (percent < 50) return '⚡ En cours';
    if (percent < 75) return '🔥 Bientôt prêt';
    if (percent < 100) return '✨ Presque fini !';
    return '🎉 Campagnes prêtes !';
}

function togglePhase(phaseId) {
    const body = document.getElementById(phaseId);
    const chevron = document.getElementById('chevron-' + phaseId);
    if (!body || !chevron) return;

    const hidden = body.style.display === 'none';
    body.style.display = hidden ? '' : 'none';
    chevron.textContent = hidden ? '⌄' : '›';
}

async function toggleStep(stepKey) {
    const row = document.querySelector(`[data-step="${stepKey}"]`);
    if (!row) return;

    const btn = row.querySelector('button');
    const completed = btn.textContent.trim() === '✅';
    const newValue = completed ? 0 : 1;

    const formData = new FormData();
    formData.append('toggle_step', stepKey);
    formData.append('completed', String(newValue));
    formData.append('_token', csrfToken);

    const res = await fetch(window.location.href, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: formData
    });

    const json = await res.json();
    btn.textContent = newValue ? '✅' : '⬜';

    document.getElementById('progressLabel').textContent = `Progression : ${json.completedSteps}/${json.totalSteps} étapes (${json.percent}%)`;
    document.getElementById('progressBar').style.width = `${json.percent}%`;
    document.getElementById('progressBadge').textContent = getBadge(json.percent);

    window.location.reload();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
