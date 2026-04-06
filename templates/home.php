<?php
$homeTitle = defined('HOME_H1') && HOME_H1 !== '' ? HOME_H1 : 'À Bordeaux, un bon prix se joue parfois en quelques semaines';
$homeSubtitle = defined('HOME_SOUS_TITRE') && HOME_SOUS_TITRE !== ''
    ? HOME_SOUS_TITRE
    : 'Recevez une estimation précise en moins de 2 minutes et évitez de sous-estimer (ou surévaluer) votre bien dans un marché qui évolue vite.';
$logoPath = defined('LOGO_PATH') ? LOGO_PATH : 'assets/images/logo.png';
$cities = [];
if (defined('CITIES_LIST')) {
    $decoded = json_decode((string) CITIES_LIST, true);
    if (is_array($decoded)) {
        $cities = $decoded;
    }
}
?>
<main class="min-h-screen bg-gradient-to-b from-blue-50 to-white">
    <section class="mx-auto max-w-5xl px-6 py-16">
        <div class="mb-10 text-center">
            <img src="<?= htmlspecialchars($logoPath, ENT_QUOTES, 'UTF-8') ?>" alt="Logo" style="max-height:84px;max-width:220px;object-fit:contain; margin: 0 auto 12px auto;">
            <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-blue-900"><?= htmlspecialchars($homeTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="mt-3 text-lg text-slate-600"><?= htmlspecialchars($homeSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="mt-4 inline-flex items-center rounded-full bg-amber-100 px-4 py-1.5 text-sm font-semibold text-amber-900">
                Forte demande sur Bordeaux Métropole : un positionnement juste peut faire la différence dès la mise en vente.
            </p>
        </div>

        <div class="mx-auto mb-8 grid max-w-3xl gap-4 md:grid-cols-3">
            <div class="rounded-xl bg-white p-4 text-sm text-slate-700 shadow ring-1 ring-slate-200">
                <p class="font-semibold text-slate-900">Rapide</p>
                <p class="mt-1">Estimation immédiate, sans rendez-vous.</p>
            </div>
            <div class="rounded-xl bg-white p-4 text-sm text-slate-700 shadow ring-1 ring-slate-200">
                <p class="font-semibold text-slate-900">Précise</p>
                <p class="mt-1">Analyse croisée : secteur, surface, type de bien et dynamique locale.</p>
            </div>
            <div class="rounded-xl bg-white p-4 text-sm text-slate-700 shadow ring-1 ring-slate-200">
                <p class="font-semibold text-slate-900">Pensée pour vendeurs et investisseurs</p>
                <p class="mt-1">Résidence principale ou investissement locatif : obtenez une base de prix claire.</p>
            </div>
        </div>

        <div class="mx-auto max-w-3xl rounded-2xl bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <form method="post" action="/" class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="prenom" class="mb-2 block text-sm font-medium text-slate-700">Prénom</label>
                    <input id="prenom" name="prenom" type="text" required class="w-full rounded-lg border border-slate-300 px-4 py-3">
                </div>
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" required class="w-full rounded-lg border border-slate-300 px-4 py-3">
                </div>
                <div>
                    <label for="type_bien" class="mb-2 block text-sm font-medium text-slate-700">Type de bien</label>
                    <select id="type_bien" name="type_bien" required class="w-full rounded-lg border border-slate-300 px-4 py-3">
                        <option value="">Sélectionnez</option>
                        <option value="appartement">Appartement</option>
                        <option value="maison">Maison</option>
                        <option value="terrain">Terrain</option>
                        <option value="commerce">Commerce</option>
                        <option value="immeuble">Immeuble</option>
                    </select>
                </div>
                <div>
                    <label for="surface" class="mb-2 block text-sm font-medium text-slate-700">Surface (m²)</label>
                    <input id="surface" name="surface" type="number" min="1" required class="w-full rounded-lg border border-slate-300 px-4 py-3">
                </div>
                <div class="md:col-span-2">
                    <label for="adresse" class="mb-2 block text-sm font-medium text-slate-700">Adresse</label>
                    <input id="adresse" name="adresse" type="text" required class="w-full rounded-lg border border-slate-300 px-4 py-3">
                </div>
                <div class="md:col-span-2">
                    <label for="ville" class="mb-2 block text-sm font-medium text-slate-700">Ville</label>
                    <select id="ville" name="ville" required class="w-full rounded-lg border border-slate-300 px-4 py-3">
                        <option value="">Sélectionnez</option>
                        <?php foreach ($cities as $c): ?>
                            <option value="<?= htmlspecialchars((string) $c, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $c, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="w-full rounded-lg bg-blue-600 px-5 py-3 text-base font-semibold text-white">Recevoir mon estimation premium</button>
                    <p class="mt-3 text-center text-xs text-slate-500">Gratuit • Sans engagement • Résultat en moins de 2 minutes</p>
                </div>
            </form>
        </div>

        <section class="mx-auto mt-10 max-w-5xl">
            <div class="rounded-2xl bg-blue-900 px-6 py-8 text-white shadow-lg">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-200">Méthode locale, données vérifiables</p>
                <h2 class="mt-2 text-2xl font-bold">Une estimation crédible pour Bordeaux Métropole</h2>
                <p class="mt-3 max-w-3xl text-sm text-blue-100">Notre estimation ne repose pas sur une simple moyenne. Elle combine des ventes réellement enregistrées, des références notariales et une lecture fine des quartiers bordelais.</p>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Sources de données</p>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900">DVF + notaires + ventes locales récentes</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">Nous croisons la base DVF (Demandes de Valeurs Foncières), les tendances notariales et les transactions récentes observées à Bordeaux, Talence, Mérignac, Pessac, Bègles et Le Bouscat.</p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Lecture micro-locale</p>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900">Le quartier fait la différence</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">Les écarts de prix peuvent être significatifs entre Chartrons, Caudéran, Bastide ou Saint-Michel. Nous tenons compte de la demande locale, de la proximité tram/commerces et des spécificités de votre rue.</p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-purple-700">Méthode transparente</p>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900">Comparables + ajustements concrets</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">Chaque estimation est calculée à partir de biens comparables puis ajustée selon la surface, l'état, l'étage, l'extérieur, le stationnement et la performance énergétique du bien.</p>
                </article>
            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Une expertise locale orientée résultat</h3>
                <p class="mt-3 text-sm leading-relaxed text-slate-600">Le marché bordelais reste exigeant : forte demande sur certains secteurs, mais délais de vente variables selon le positionnement prix. Notre objectif est de vous donner une fourchette réaliste et compréhensible pour vendre dans de bonnes conditions, sans sous-valoriser votre bien.</p>
            </div>
        </section>
    </section>
</main>
