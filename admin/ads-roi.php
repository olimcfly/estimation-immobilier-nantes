<?php
$pageTitle = "Simulateur ROI Google Ads";
$ville = defined('CITY_NAME') ? CITY_NAME : 'votre ville';

$currentPage = 'google-ads';
$topNavCurrent = 'google-ads';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="max-w-7xl space-y-8">
    <section class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm space-y-8">
        <div class="space-y-2">
            <p class="text-sm text-slate-500">Marché local : <strong><?= htmlspecialchars($ville, ENT_QUOTES, 'UTF-8') ?></strong></p>
            <h1 class="text-3xl font-bold text-slate-900">Combien pouvez-vous gagner avec Google Ads ?</h1>
            <p class="text-slate-600">Simulez votre investissement et visualisez votre retour sur investissement en temps réel.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div>
                <form id="roi-form" class="space-y-6">
                    <div>
                        <label for="budget" class="block font-semibold text-slate-800">1. Budget mensuel Google Ads</label>
                        <input id="budget" name="budget" type="range" min="200" max="5000" step="50" value="500" class="w-full mt-2">
                        <p class="text-2xl font-bold text-blue-700 mt-1"><span data-bind="budget">500</span> €/mois</p>
                    </div>

                    <div>
                        <label for="commission" class="block font-semibold text-slate-800">2. Commission moyenne par vente</label>
                        <input id="commission" name="commission" type="range" min="3000" max="30000" step="500" value="8000" class="w-full mt-2">
                        <p class="text-2xl font-bold text-blue-700 mt-1"><span data-bind="commission">8 000</span> €</p>
                        <p class="text-sm text-slate-500 mt-1">Honoraires moyens sur une transaction immobilière</p>
                    </div>

                    <div>
                        <label for="mandatRate" class="block font-semibold text-slate-800">3. Taux de conversion estimation → mandat (%)</label>
                        <input id="mandatRate" name="mandatRate" type="range" min="2" max="20" step="1" value="8" class="w-full mt-2">
                        <p class="text-2xl font-bold text-blue-700 mt-1"><span data-bind="mandatRate">8</span>%</p>
                        <p class="text-sm text-slate-500 mt-1">Sur 100 estimations reçues, combien deviennent des mandats ?</p>
                    </div>

                    <div>
                        <label for="venteRate" class="block font-semibold text-slate-800">4. Taux de transformation mandat → vente (%)</label>
                        <input id="venteRate" name="venteRate" type="range" min="30" max="90" step="5" value="60" class="w-full mt-2">
                        <p class="text-2xl font-bold text-blue-700 mt-1"><span data-bind="venteRate">60</span>%</p>
                        <p class="text-sm text-slate-500 mt-1">Sur 100 mandats signés, combien aboutissent à une vente ?</p>
                    </div>

                    <div>
                        <label for="delay" class="block font-semibold text-slate-800">5. Délai moyen de vente (mois)</label>
                        <input id="delay" name="delay" type="range" min="1" max="12" step="1" value="4" class="w-full mt-2">
                        <p class="text-2xl font-bold text-blue-700 mt-1"><span data-bind="delay">4</span> mois</p>
                    </div>

                    <button type="button" id="calculate" class="w-full bg-blue-600 hover:bg-blue-700 transition text-white font-semibold py-3 px-4 rounded-xl">Calculer mon ROI</button>
                </form>
            </div>

            <div class="space-y-6">
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-4">
                    <h2 class="text-xl font-semibold text-slate-900">Funnel mensuel estimé</h2>

                    <p class="text-sm text-slate-600">Budget : <strong><span data-result="budget">500</span> €/mois</strong></p>

                    <div class="space-y-2">
                        <p class="text-sm font-medium text-slate-700">Clics : ~<span data-result="clicks">333</span> clics/mois</p>
                        <div class="h-9 bg-blue-100 rounded-xl overflow-hidden">
                            <div data-bar="clicks" class="h-full bg-blue-500 text-white text-sm font-semibold flex items-center justify-center transition-all duration-700" style="width:100%"></div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="text-sm font-medium text-slate-700">Estimations : ~<span data-result="leads">17</span> leads/mois</p>
                        <div class="h-9 bg-emerald-100 rounded-xl overflow-hidden">
                            <div data-bar="leads" class="h-full bg-emerald-500 text-white text-sm font-semibold flex items-center justify-center transition-all duration-700" style="width:70%"></div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="text-sm font-medium text-slate-700">Mandats : ~<span data-result="mandats">1.3</span> mandats/mois</p>
                        <div class="h-9 bg-violet-100 rounded-xl overflow-hidden">
                            <div data-bar="mandats" class="h-full bg-violet-500 text-white text-sm font-semibold flex items-center justify-center transition-all duration-700" style="width:45%"></div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="text-sm font-medium text-slate-700">Ventes : ~<span data-result="ventes">0.8</span> ventes/mois</p>
                        <div class="h-9 bg-amber-100 rounded-xl overflow-hidden">
                            <div data-bar="ventes" class="h-full bg-amber-500 text-white text-sm font-semibold flex items-center justify-center transition-all duration-700" style="width:30%"></div>
                        </div>
                    </div>

                    <p class="text-lg font-bold text-green-700">💰 CA généré : <span data-result="ca">6 400</span> €/mois</p>
                    <p class="text-xs text-slate-500">Hypothèses fixes : CPC moyen 1,50€ • Conversion clic → lead 5%.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <article class="rounded-2xl p-5 text-white bg-gradient-to-br from-blue-600 to-blue-700">
                        <h3 class="text-sm uppercase tracking-wide">Investissement</h3>
                        <p class="text-3xl font-bold mt-2"><span data-result="budgetCard">500</span>€/mois</p>
                        <p class="text-sm mt-1"><span data-result="budgetYear">6 000</span>€/an</p>
                    </article>

                    <article class="rounded-2xl p-5 text-white bg-gradient-to-br from-emerald-600 to-emerald-700">
                        <h3 class="text-sm uppercase tracking-wide">Revenus estimés</h3>
                        <p class="text-3xl font-bold mt-2"><span data-result="caCard">6 400</span>€/mois</p>
                        <p class="text-sm mt-1"><span data-result="caYear">76 800</span>€/an</p>
                    </article>

                    <article class="rounded-2xl p-5 text-white bg-gradient-to-br from-violet-600 to-violet-700 space-y-1">
                        <h3 class="text-sm uppercase tracking-wide">ROI</h3>
                        <p class="text-3xl font-bold mt-2"><span data-result="roi">1180</span>%</p>
                        <p class="text-sm">Pour 1€ investi → <span data-result="xroi">12.8</span>€ de CA</p>
                        <span id="roi-badge" class="inline-block text-xs font-semibold mt-2 px-2 py-1 rounded-full bg-green-200 text-green-900">Excellent ROI 🎯</span>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-2xl font-bold text-slate-900">Projection 12 mois</h2>
        <p class="text-slate-600">Les ventes apparaissent après le délai moyen de vente configuré.</p>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-700">
                        <th class="p-3 text-left">Mois</th>
                        <th class="p-3 text-left">Budget</th>
                        <th class="p-3 text-left">Leads</th>
                        <th class="p-3 text-left">Mandats</th>
                        <th class="p-3 text-left">Ventes</th>
                        <th class="p-3 text-left">CA</th>
                        <th class="p-3 text-left">Bénéfice net</th>
                        <th class="p-3 text-left">ROI cumulé</th>
                    </tr>
                </thead>
                <tbody id="projection-body"></tbody>
            </table>
        </div>

        <div class="space-y-2">
            <p class="text-sm font-semibold text-slate-800">Investissement (rouge) vs revenus (vert)</p>
            <div id="projection-chart" class="grid grid-cols-12 gap-2 items-end h-56 bg-slate-50 border border-slate-200 rounded-xl p-3"></div>
            <p class="text-xs text-slate-500">Ligne pointillée = point de break-even (premier mois avec bénéfice cumulé positif).</p>
        </div>
    </section>

    <section class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-2xl font-bold text-slate-900">Comparaison des canaux d'acquisition</h2>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-700">
                        <th class="p-3 text-left">Canal</th>
                        <th class="p-3 text-left">Coût/lead</th>
                        <th class="p-3 text-left">Conversion</th>
                        <th class="p-3 text-left">Temps setup</th>
                        <th class="p-3 text-left">Contrôle</th>
                        <th class="p-3 text-left">Recommandation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b"><td class="p-3">Google Ads Search</td><td class="p-3">10-20€</td><td class="p-3">3-8%</td><td class="p-3">30 min</td><td class="p-3">Total</td><td class="p-3">✅ Priorité 1</td></tr>
                    <tr class="border-b"><td class="p-3">Google Ads Display</td><td class="p-3">15-50€</td><td class="p-3">0.3-1%</td><td class="p-3">1h</td><td class="p-3">Élevé</td><td class="p-3">⚠️ Phase 2</td></tr>
                    <tr class="border-b"><td class="p-3">Facebook Ads</td><td class="p-3">5-15€</td><td class="p-3">1-4%</td><td class="p-3">1h</td><td class="p-3">Élevé</td><td class="p-3">✅ Complémentaire</td></tr>
                    <tr class="border-b"><td class="p-3">SEO (blog)</td><td class="p-3">0€ (temps)</td><td class="p-3">2-5%</td><td class="p-3">3-6 mois</td><td class="p-3">Faible</td><td class="p-3">✅ Long terme</td></tr>
                    <tr class="border-b"><td class="p-3">Flyers/Publipostage</td><td class="p-3">50-200€</td><td class="p-3">0.1-0.5%</td><td class="p-3">2-3j</td><td class="p-3">Faible</td><td class="p-3">⚠️ Coûteux</td></tr>
                    <tr class="border-b"><td class="p-3">Portails (SeLoger)</td><td class="p-3">200-500€/mois</td><td class="p-3">Variable</td><td class="p-3">1j</td><td class="p-3">Faible</td><td class="p-3">⚠️ Dépendance</td></tr>
                    <tr><td class="p-3">Bouche à oreille</td><td class="p-3">0€</td><td class="p-3">20-50%</td><td class="p-3">Continu</td><td class="p-3">Nul</td><td class="p-3">✅ Toujours</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-2xl font-bold text-slate-900">Recommandation personnalisée</h2>
        <div id="recommendation" class="bg-slate-50 rounded-xl border border-slate-200 p-5 text-slate-800 leading-relaxed"></div>
    </section>
</div>

<script>
(() => {
    const CPC = 1.5;
    const LEAD_RATE = 0.05;

    const fields = {
        budget: document.getElementById('budget'),
        commission: document.getElementById('commission'),
        mandatRate: document.getElementById('mandatRate'),
        venteRate: document.getElementById('venteRate'),
        delay: document.getElementById('delay'),
    };

    const bindEls = document.querySelectorAll('[data-bind]');
    const resultEls = (name) => document.querySelectorAll(`[data-result="${name}"]`);

    const formatNumber = (value, digits = 0) => new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
    }).format(value);

    const animateTo = (el, value, digits = 0) => {
        const current = Number(el.dataset.current || 0);
        const start = performance.now();
        const duration = 550;

        const frame = (time) => {
            const progress = Math.min((time - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const next = current + (value - current) * eased;
            el.textContent = formatNumber(next, digits);
            if (progress < 1) {
                requestAnimationFrame(frame);
            } else {
                el.dataset.current = String(value);
            }
        };

        requestAnimationFrame(frame);
    };

    const updateText = (name, value, digits = 0) => {
        resultEls(name).forEach((el) => animateTo(el, value, digits));
    };

    const setBar = (name, value, max, label) => {
        const bar = document.querySelector(`[data-bar="${name}"]`);
        if (!bar) return;
        const width = Math.max(10, Math.min(100, (value / Math.max(max, 1)) * 100));
        bar.style.width = `${width}%`;
        bar.textContent = label;
    };

    const compute = () => {
        const budget = Number(fields.budget.value);
        const commission = Number(fields.commission.value);
        const mandatRate = Number(fields.mandatRate.value) / 100;
        const venteRate = Number(fields.venteRate.value) / 100;
        const delay = Number(fields.delay.value);

        bindEls.forEach((el) => {
            const key = el.dataset.bind;
            if (!fields[key]) return;
            el.textContent = formatNumber(Number(fields[key].value), 0);
        });

        const clicks = budget / CPC;
        const leads = clicks * LEAD_RATE;
        const mandats = leads * mandatRate;
        const ventes = mandats * venteRate;
        const ca = ventes * commission;

        updateText('budget', budget, 0);
        updateText('clicks', clicks, 0);
        updateText('leads', leads, 0);
        updateText('mandats', mandats, 1);
        updateText('ventes', ventes, 1);
        updateText('ca', ca, 0);
        updateText('budgetCard', budget, 0);
        updateText('budgetYear', budget * 12, 0);
        updateText('caCard', ca, 0);
        updateText('caYear', ca * 12, 0);

        const roi = budget > 0 ? ((ca - budget) / budget) * 100 : 0;
        const xroi = budget > 0 ? ca / budget : 0;
        updateText('roi', roi, 0);
        updateText('xroi', xroi, 1);

        setBar('clicks', clicks, clicks, `~${formatNumber(clicks, 0)}`);
        setBar('leads', leads, clicks, `~${formatNumber(leads, 0)}`);
        setBar('mandats', mandats, clicks, `~${formatNumber(mandats, 1)}`);
        setBar('ventes', ventes, clicks, `~${formatNumber(ventes, 1)}`);

        const roiBadge = document.getElementById('roi-badge');
        if (roi > 300) {
            roiBadge.className = 'inline-block text-xs font-semibold mt-2 px-2 py-1 rounded-full bg-green-200 text-green-900';
            roiBadge.textContent = 'Excellent ROI 🎯';
        } else if (roi >= 100) {
            roiBadge.className = 'inline-block text-xs font-semibold mt-2 px-2 py-1 rounded-full bg-amber-200 text-amber-900';
            roiBadge.textContent = 'Bon ROI 👍';
        } else {
            roiBadge.className = 'inline-block text-xs font-semibold mt-2 px-2 py-1 rounded-full bg-red-200 text-red-900';
            roiBadge.textContent = 'ROI insuffisant ⚠️ Optimisez les taux de conversion';
        }

        renderProjection({ budget, leads, mandats, ventes, ca, delay });
        renderRecommendation({ budget, leads, mandats });
    };

    const renderProjection = ({ budget, leads, mandats, ventes, ca, delay }) => {
        const tbody = document.getElementById('projection-body');
        const chart = document.getElementById('projection-chart');
        tbody.innerHTML = '';
        chart.innerHTML = '';

        let cumBudget = 0;
        let cumRevenue = 0;
        let breakEvenMonth = null;

        const rows = Array.from({ length: 12 }, (_, idx) => {
            const month = idx + 1;
            const monthlySales = month > delay ? ventes : 0;
            const monthlyRevenue = month > delay ? ca : 0;

            cumBudget += budget;
            cumRevenue += monthlyRevenue;
            const net = cumRevenue - cumBudget;
            const roiCum = cumBudget > 0 ? (net / cumBudget) * 100 : 0;

            if (breakEvenMonth === null && net >= 0) breakEvenMonth = month;

            return { month, monthlySales, monthlyRevenue, net, roiCum };
        });

        rows.forEach((row) => {
            const tr = document.createElement('tr');
            tr.className = 'border-b last:border-b-0';
            if (breakEvenMonth && row.month === breakEvenMonth) {
                tr.className += ' bg-green-50';
            }

            tr.innerHTML = `
                <td class="p-3 font-medium">${row.month}</td>
                <td class="p-3">${formatNumber(budget)}€</td>
                <td class="p-3">${formatNumber(leads, 0)}</td>
                <td class="p-3">${formatNumber(mandats, 1)}</td>
                <td class="p-3">${formatNumber(row.monthlySales, 1)}</td>
                <td class="p-3">${formatNumber(row.monthlyRevenue, 0)}€</td>
                <td class="p-3 ${row.net >= 0 ? 'text-green-700 font-semibold' : 'text-red-700 font-semibold'}">${row.net >= 0 ? '+' : ''}${formatNumber(row.net, 0)}€</td>
                <td class="p-3 ${row.roiCum >= 0 ? 'text-green-700' : 'text-red-700'}">${row.roiCum >= 0 ? '+' : ''}${formatNumber(row.roiCum, 0)}%</td>
            `;
            tbody.appendChild(tr);

            const max = Math.max(row.monthlyRevenue, budget, 1);
            const card = document.createElement('div');
            card.className = 'relative flex flex-col justify-end h-full';
            const breakEvenLabel = breakEvenMonth === row.month ? '<div class="absolute -top-5 left-0 right-0 text-[10px] text-center text-green-700 font-semibold">Break-even</div>' : '';
            card.innerHTML = `
                ${breakEvenLabel}
                <div class="w-full bg-red-400 rounded-t-sm" style="height:${(budget / max) * 100}%"></div>
                <div class="w-full bg-green-500 rounded-t-sm mt-1" style="height:${(row.monthlyRevenue / max) * 100}%"></div>
                <p class="text-[10px] text-center mt-1">M${row.month}</p>
            `;
            chart.appendChild(card);
        });
    };

    const renderRecommendation = ({ budget, leads, mandats }) => {
        const el = document.getElementById('recommendation');

        if (budget <= 300) {
            el.innerHTML = `
                Avec un budget de <strong>${formatNumber(budget)}€</strong>, concentrez-vous uniquement sur la campagne
                <strong>🔥 Intention directe</strong>. Chaque euro doit aller vers les prospects les plus chauds.<br>
                Objectif : <strong>${formatNumber(leads, 0)} leads/mois</strong>,
                <strong>${formatNumber(mandats, 1)} mandats potentiels</strong>.
            `;
            return;
        }

        if (budget <= 1000) {
            const b1 = budget * 0.6;
            const b2 = budget * 0.25;
            const b3 = budget * 0.15;
            el.innerHTML = `
                Budget idéal pour démarrer ! Répartissez :<br>
                - <strong>${formatNumber(b1, 0)}€</strong> → Campagne Intention directe<br>
                - <strong>${formatNumber(b2, 0)}€</strong> → Campagne Recherche info<br>
                - <strong>${formatNumber(b3, 0)}€</strong> → Réservé pour tests<br>
                Objectif : <strong>${formatNumber(leads, 0)} leads/mois</strong>.
            `;
            return;
        }

        const b1 = budget * 0.6;
        const b2 = budget * 0.25;
        const b3 = budget * 0.15;
        el.innerHTML = `
            Excellent budget ! Vous pouvez déployer les 3 campagnes :<br>
            - <strong>${formatNumber(b1, 0)}€</strong> → Intention directe<br>
            - <strong>${formatNumber(b2, 0)}€</strong> → Recherche info<br>
            - <strong>${formatNumber(b3, 0)}€</strong> → Display/Remarketing<br>
            Objectif : <strong>${formatNumber(leads, 0)} leads/mois</strong>,
            <strong>${formatNumber(mandats, 1)} mandats</strong>.<br>
            Envisagez aussi de créer des landing pages par quartier.
        `;
    };

    Object.values(fields).forEach((input) => input.addEventListener('input', compute));
    document.getElementById('calculate').addEventListener('click', compute);

    compute();
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
