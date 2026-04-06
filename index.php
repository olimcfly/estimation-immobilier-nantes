<?php

declare(strict_types=1);

$configPath = __DIR__ . '/config/config.php';
$config = [];
$installed = false;

if (is_file($configPath)) {
    $loaded = require $configPath;

    if (is_array($loaded)) {
        $config = $loaded;
        $installed = !empty($config['installed']);
    } else {
        // Ancien format de configuration basé sur des constantes.
        // Dans ce cas, la présence du fichier signifie que l'installation est faite.
        $installed = true;
    }
}

if (!$installed) {
    header('Location: /install/index.php');
    exit;
}

$villes = array_values(array_unique($villesNettoyees));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($agenceNom, ENT_QUOTES); ?> · Estimation immobilière à Nantes et sa région</title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES); ?> Découvrez la valeur de votre bien à Nantes, Saint-Nazaire, Rezé ou dans toute la Loire-Atlantique.">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-slate-900 antialiased">
    <main>
        <section id="hero" class="text-white" style="background: linear-gradient(135deg, <?= htmlspecialchars($couleur, ENT_QUOTES); ?>, #1d4ed8);">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 md:py-20 lg:px-8">
                <div class="mx-auto max-w-4xl text-center">
                    <p class="mx-auto inline-flex items-center rounded-full bg-white/15 px-4 py-2 text-sm font-semibold backdrop-blur">
                        ✨ Estimation gratuite en 30 secondes - Nantes Métropole
                    </p>
                    <?php if ($logo !== ''): ?>
                        <img src="<?= htmlspecialchars('/' . ltrim($logo, '/'), ENT_QUOTES); ?>" alt="Logo <?= htmlspecialchars($agenceNom, ENT_QUOTES); ?>" class="mx-auto mt-4 h-16 w-auto rounded bg-white p-2">
                    <?php endif; ?>
                    <h1 class="mt-6 text-4xl font-extrabold leading-tight md:text-5xl">
                        <?= htmlspecialchars(str_replace('{ville}', $villePrincipale, "Estimation immobilière à {ville} et en Loire-Atlantique"), ENT_QUOTES); ?>
                    </h1>
                    <p class="mt-4 text-base text-blue-100 md:text-lg">
                        Obtenez une estimation précise de votre bien à Nantes, Saint-Nazaire, Rezé ou dans toute la région nantaise.
                    </p>
                </div>

                <div class="mx-auto mt-12 max-w-4xl">
                    <form id="estimation-form" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="w-full">
                            <label for="type_bien" class="mb-1 block text-sm font-medium text-blue-100">🏠 Type de bien</label>
                            <select id="type_bien" name="type_bien" required class="w-full rounded-xl border-0 bg-gray-50 px-4 py-4 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <option value="">Choisir</option>
                                <option value="Appartement">Appartement</option>
                                <option value="Maison">Maison</option>
                                <option value="Terrain">Terrain</option>
                                <option value="Local commercial">Local commercial</option>
                            </select>
                        </div>

                        <div class="w-full lg:flex-1 lg:px-3">
                            <label for="surface_tranche" class="mb-1 block text-sm font-medium text-blue-100">📏 Surface</label>
                            <select id="surface_tranche" name="surface_tranche" required class="w-full rounded-xl border-0 bg-gray-50 px-4 py-4 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <option value="">Choisir</option>
                                <option value="lt30">Moins de 30 m²</option>
                                <option value="30_50">30-50 m²</option>
                                <option value="50_80">50-80 m²</option>
                                <option value="80_120">80-120 m²</option>
                                <option value="120_200">120-200 m²</option>
                                <option value="gt200">Plus de 200 m²</option>
                            </select>
                        </div>

                        <div class="w-full lg:flex-1 lg:px-3 lg:border-r lg:border-white/20">
                            <label for="ville" class="mb-1 block text-sm font-medium text-blue-100">📍 Quartier / Ville</label>
                            <select id="ville" name="ville" required class="w-full rounded-xl border-0 bg-gray-50 px-4 py-4 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <option value="">Choisir</option>
                                <?php foreach ($villes as $ville): ?>
                                    <option value="<?= htmlspecialchars((string) $ville, ENT_QUOTES); ?>"><?= htmlspecialchars((string) $ville, ENT_QUOTES); ?></option>
                                <?php endforeach; ?>
                                <option value="Nantes">Nantes</option>
                                <option value="Saint-Nazaire">Saint-Nazaire</option>
                                <option value="Rezé">Rezé</option>
                                <option value="Saint-Herblain">Saint-Herblain</option>
                                <option value="Orvault">Orvault</option>
                                <option value="Vertou">Vertou</option>
                                <option value="Couëron">Couëron</option>
                                <option value="Bouguenais">Bouguenais</option>
                            </select>
                        </div>

                        <div class="w-full">
                            <button type="submit" class="mt-6 w-full rounded-xl bg-white px-4 py-4 font-semibold text-blue-600 transition hover:bg-gray-50">
                                Estimer mon bien →
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section id="result-section" class="mx-auto max-w-4xl px-4 py-12 transition-all duration-500 sm:px-6 lg:px-8">
            <div class="pointer-events-none max-h-0 -translate-y-4 opacity-0">
                <div id="result-workflow" class="space-y-4">
                    <div id="estimation-page" class="space-y-4">
                        <p class="text-center text-sm text-slate-700">Estimation calculée avec une double lecture IA : <span class="font-semibold text-slate-900">Perplexity</span> et <span class="font-semibold text-slate-900">Mammouth AI</span>.</p>
                        <div class="grid gap-3 text-sm text-slate-700 sm:grid-cols-2">
                            <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-3">🤖 Perplexity IA · Analyse du marché nantais</div>
                            <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-3">🧠 Mammouth AI · Ajustements par quartier et typologie</div>
                        </div>

                        <div class="rounded-2xl bg-white p-6 shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-slate-500">Votre estimation pour</p>
                                    <p class="text-lg font-bold text-slate-900" id="estimation-type-ville">Appartement à Nantes</p>
                                    <p class="text-sm text-slate-600" id="estimation-surface">65 m²</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-3xl font-bold text-blue-600" id="estimation-price">285 000 €</p>
                                    <p class="text-sm text-slate-500">Fourchette : <span id="estimation-range">265 000 € - 305 000 €</span></p>
                                </div>
                            </div>
                        </div>

                        <button id="go-rdv-page" type="button" class="w-full rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-800">Obtenir mon rapport détaillé et prendre RDV →</button>
                    </div>

                    <div id="rdv-page" class="hidden space-y-4">
                        <div class="wizard-steps relative flex items-center justify-between">
                            <div class="absolute left-0 top-1/2 h-0.5 w-full -translate-y-1/2 bg-slate-200"></div>
                            <div class="relative z-10 flex w-full justify-between">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs font-semibold text-white">1</span>
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-300 text-xs font-semibold text-slate-500">2</span>
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-300 text-xs font-semibold text-slate-500">3</span>
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-300 text-xs font-semibold text-slate-500">4</span>
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-300 text-xs font-semibold text-slate-500">5</span>
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-300 text-xs font-semibold text-slate-500">6</span>
                            </div>
                        </div>

                        <div class="wizard-container relative h-96 overflow-hidden">
                            <div id="wizard-track" class="flex h-full w-[700%] transition-transform duration-300">
                                <!-- Step 1 -->
                                <div class="wizard-step w-full shrink-0 space-y-4 px-1">
                                    <h3 class="text-center text-xl font-bold text-slate-900">Votre email pour recevoir le rapport</h3>
                                    <input type="email" id="rapport-email" name="email" placeholder="votre@email.com" required class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button id="step-email-next" type="button" class="w-full rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-800">Recevoir mon rapport →</button>
                                </div>

                                <!-- Step 2 -->
                                <div class="wizard-step w-full shrink-0 space-y-4 px-1">
                                    <h3 class="text-center text-xl font-bold text-slate-900">Comment nous avez-vous connu ?</h3>
                                    <select id="source-site" name="source" required class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Choisir</option>
                                        <option value="Google">Google</option>
                                        <option value="Facebook">Facebook</option>
                                        <option value="Instagram">Instagram</option>
                                        <option value="Recommandation">Recommandation</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                    <button id="step-source-next" type="button" class="w-full rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-800">Suivant →</button>
                                </div>

                                <!-- Step 3 -->
                                <div class="wizard-step w-full shrink-0 space-y-4 px-1">
                                    <h3 class="text-center text-xl font-bold text-slate-900">Quel est votre projet ?</h3>
                                    <div id="projet-pills" class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                        <label class="cursor-pointer rounded-xl border border-blue-200 px-3 py-4 text-center text-sm font-semibold text-blue-700 transition hover:border-blue-500 hover:bg-blue-50 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-600 has-[:checked]:text-white">
                                            <input type="radio" name="projet" value="Vendre mon bien" class="sr-only">
                                            🏠 Vendre
                                        </label>
                                        <label class="cursor-pointer rounded-xl border border-blue-200 px-3 py-4 text-center text-sm font-semibold text-blue-700 transition hover:border-blue-500 hover:bg-blue-50 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-600 has-[:checked]:text-white">
                                            <input type="radio" name="projet" value="Acheter un bien" class="sr-only">
                                            🔑 Acheter
                                        </label>
                                        <label class="cursor-pointer rounded-xl border border-blue-200 px-3 py-4 text-center text-sm font-semibold text-blue-700 transition hover:border-blue-500 hover:bg-blue-50 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-600 has-[:checked]:text-white">
                                            <input type="radio" name="projet" value="Investir" class="sr-only">
                                            💰 Investir
                                        </label>
                                    </div>
                                    <button id="step-projet-next" type="button" class="w-full rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-800">Suivant →</button>
                                </div>

                                <!-- Step 4 -->
                                <div class="wizard-step w-full shrink-0 space-y-4 px-1">
                                    <h3 class="text-center text-xl font-bold text-slate-900">Quel est votre budget ?</h3>
                                    <select id="budget-bant" name="budget" required class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Choisir</option>
                                        <option value="lt200">Moins de 200 000 €</option>
                                        <option value="200_300">200 000 € - 300 000 €</option>
                                        <option value="300_400">300 000 € - 400 000 €</option>
                                        <option value="400_500">400 000 € - 500 000 €</option>
                                        <option value="gt500">Plus de 500 000 €</option>
                                    </select>
                                    <button id="step-budget-next" type="button" class="w-full rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-800">Suivant →</button>
                                </div>

                                <!-- Step 5 -->
                                <div class="wizard-step w-full shrink-0 space-y-4 px-1">
                                    <h3 class="text-center text-xl font-bold text-slate-900">Dans quel délai ?</h3>
                                    <select id="delai" name="delai" required class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Choisir</option>
                                        <option value="Moins d'1 mois">Moins d'1 mois</option>
                                        <option value="1_3">1 à 3 mois</option>
                                        <option value="3_6">3 à 6 mois</option>
                                        <option value="Dans les 6 mois">Dans les 6 mois</option>
                                        <option value="Dans l'année">Dans l'année</option>
                                        <option value="Pas de délai précis">Pas de délai précis</option>
                                    </select>
                                    <button id="step-timing-next" type="button" class="w-full rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-800">Dernière étape →</button>
                                </div>

                                <!-- Step 6 -->
                                <div class="wizard-step w-full shrink-0 space-y-4 px-1">
                                    <h3 class="text-center text-xl font-bold text-slate-900">Vos coordonnées</h3>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="prenom" placeholder="Prénom" required class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <input type="text" name="nom" placeholder="Nom" required class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <input type="tel" name="telephone" placeholder="Téléphone" required class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button id="contact-submit" type="button" class="w-full rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-800">Valider ma demande</button>
                                    <p id="contact-feedback" class="mt-4 hidden rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button id="new-estimation" type="button" class="mt-4 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    ← Nouvelle estimation
                </button>
            </div>
        </section>

        <section class="bg-slate-50 px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-slate-900">Pourquoi estimer votre bien à Nantes ?</h2>
                    <p class="mt-3 text-sm text-slate-600">Le marché immobilier nantais est dynamique et en constante évolution.</p>
                </div>

                <div class="mt-10 grid gap-8 md:grid-cols-3">
                    <article class="rounded-2xl bg-white p-6 shadow-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                            📈
                        </div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Marché en tension</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Nantes attire de plus en plus d'investisseurs et de nouveaux habitants, ce qui maintient une forte demande immobilière.
                        </p>
                    </article>

                    <article class="rounded-2xl bg-white p-6 shadow-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                            🏗️
                        </div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Projets urbains</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Les grands projets comme l'Île de Nantes ou le CHU transforment la ville et impactent les valeurs immobilières.
                        </p>
                    </article>

                    <article class="rounded-2xl bg-white p-6 shadow-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                            🚆
                        </div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Accessibilité</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Avec le TGV, l'aéroport et un réseau de transports en commun performant, Nantes est parfaitement connectée.
                        </p>
                    </article>
                </div>

                <div class="mt-12 rounded-2xl bg-blue-50 p-6">
                    <article>
                        <h3 class="text-xl font-bold text-slate-900">Le saviez-vous ?</h3>
                        <p class="mt-3 text-sm text-slate-600">
                            Nantes est régulièrement classée parmi les villes les plus attractives de France. Cette attractivité se traduit par une hausse constante des prix de l'immobilier, avec des disparités importantes entre les quartiers.
                        </p>
                        <p class="mt-3 text-sm text-slate-600">
                            Une estimation précise vous permet d'optimiser votre projet, que ce soit pour vendre au meilleur prix ou investir judicieusement.
                        </p>
                    </article>
                </div>

                <p class="mt-8 text-center text-sm font-medium text-slate-600">
                    Une estimation fiable est la première étape pour réussir votre projet immobilier à Nantes.
                </p>
            </div>
        </section>

        <section class="bg-white px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-slate-900">Estimations récentes en Loire-Atlantique</h2>
                    <p class="mt-3 text-sm text-slate-600">Des exemples concrets pour vous situer sur le marché nantais.</p>
                </div>

                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    <article class="rounded-2xl border border-blue-100 bg-blue-50/40 p-5 text-left">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Exemple · Nantes Centre</p>
                        <h3 class="mt-2 text-lg font-bold text-slate-900">Appartement T2 · 45 m² · 3ème étage</h3>
                        <p class="mt-3 text-sm text-slate-600">Fourchette estimative : <span class="font-semibold text-slate-900">220 000 € à 250 000 €</span></p>
                        <p class="mt-2 text-xs text-slate-500">Soit environ 4 900 à 5 500 €/m² selon l'état et l'exposition.</p>
                    </article>

                    <article class="rounded-2xl border border-blue-100 bg-blue-50/40 p-5 text-left">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Exemple · Rezé</p>
                        <h3 class="mt-2 text-lg font-bold text-slate-900">Maison T4 · 95 m² · jardin</h3>
                        <p class="mt-3 text-sm text-slate-600">Fourchette estimative : <span class="font-semibold text-slate-900">380 000 € à 420 000 €</span></p>
                        <p class="mt-2 text-xs text-slate-500">Soit environ 4 000 à 4 400 €/m² selon la proximité des commodités.</p>
                    </article>

                    <article class="rounded-2xl border border-blue-100 bg-blue-50/40 p-5 text-left">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Exemple · Saint-Herblain</p>
                        <h3 class="mt-2 text-lg font-bold text-slate-900">Appartement T3 · 68 m² · balcon</h3>
                        <p class="mt-3 text-sm text-slate-600">Fourchette estimative : <span class="font-semibold text-slate-900">290 000 € à 325 000 €</span></p>
                        <p class="mt-2 text-xs text-slate-500">Soit environ 4 250 à 4 750 €/m² selon l'étage et l'orientation.</p>
                    </article>
                </div>

                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    <article class="rounded-2xl border border-blue-100 bg-blue-50/40 p-5 text-left">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Exemple · Saint-Nazaire</p>
                        <h3 class="mt-2 text-lg font-bold text-slate-900">Maison T5 · 120 m² · garage</h3>
                        <p class="mt-3 text-sm text-slate-600">Fourchette estimative : <span class="font-semibold text-slate-900">320 000 € à 360 000 €</span></p>
                        <p class="mt-2 text-xs text-slate-500">Soit environ 2 650 à 3 000 €/m² selon la proximité du centre.</p>
                    </article>

                    <article class="rounded-2xl border border-blue-100 bg-blue-50/40 p-5 text-left">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Exemple · Vertou</p>
                        <h3 class="mt-2 text-lg font-bold text-slate-900">Maison récente · 140 m² · jardin</h3>
                        <p class="mt-3 text-sm text-slate-600">Fourchette estimative : <span class="font-semibold text-slate-900">480 000 € à 530 000 €</span></p>
                        <p class="mt-2 text-xs text-slate-500">Soit environ 3 400 à 3 800 €/m² selon les prestations.</p>
                    </article>

                    <article class="rounded-2xl border border-blue-100 bg-blue-50/40 p-5 text-left">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Exemple · Orvault</p>
                        <h3 class="mt-2 text-lg font-bold text-slate-900">Appartement neuf · 55 m² · parking</h3>
                        <p class="mt-3 text-sm text-slate-600">Fourchette estimative : <span class="font-semibold text-slate-900">260 000 € à 290 000 €</span></p>
                        <p class="mt-2 text-xs text-slate-500">Soit environ 4 700 à 5 250 €/m² selon la résidence.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="bg-slate-50 px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-slate-900">Témoignages de clients satisfaits</h2>
                    <p class="mt-3 text-sm text-slate-600">Ils ont estimé leur bien avec nous et ont concrétisé leur projet.</p>
                </div>

                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <blockquote class="rounded-2xl bg-white p-6 shadow-sm">
                        <p class="text-sm text-slate-600">"L'estimation était très précise et m'a permis de vendre mon appartement à Nantes centre en seulement 10 jours !"</p>
                        <footer class="mt-3 text-xs font-semibold text-slate-500">— Thomas R., vendeur · Nantes</footer>
                    </blockquote>

                    <blockquote class="rounded-2xl bg-white p-6 shadow-sm">
                        <p class="text-sm text-slate-600">"Grâce à l'estimation, j'ai pu négocier efficacement l'achat de ma maison à Rezé. Un gain de temps et d'argent !"</p>
                        <footer class="mt-3 text-xs font-semibold text-slate-500">— Sophie M., acheteuse · Rezé</footer>
                    </blockquote>

                    <blockquote class="rounded-2xl bg-white p-6 shadow-sm">
                        <p class="text-sm text-slate-600">"L'équipe a su me conseiller sur les quartiers porteurs de Saint-Herblain pour mon investissement locatif."</p>
                        <footer class="mt-3 text-xs font-semibold text-slate-500">— Karim D., investisseur · Saint-Herblain</footer>
                    </blockquote>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-white px-4 py-6 text-center text-xs text-slate-400 sm:px-6 lg:px-8">
        © <?= date('Y'); ?> · <?= htmlspecialchars($agenceNom, ENT_QUOTES); ?> · <a href="/pages/mentions-legales.php" class="hover:text-slate-600">Mentions légales</a> ·
        <a href="/pages/politique-confidentialite.php" class="hover:text-slate-600">Politique de confidentialité</a>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('estimation-form');
            const resultSection = document.getElementById('result-section');
            const estimationPage = document.getElementById('estimation-page');
            const rdvPage = document.getElementById('rdv-page');
            const goRdvPageBtn = document.getElementById('go-rdv-page');
            const newEstimationBtn = document.getElementById('new-estimation');
            const contactFeedback = document.getElementById('contact-feedback');

            // Wizard elements
            const wizardTrack = document.getElementById('wizard-track');
            const wizardDots = document.querySelectorAll('.wizard-steps span');
            const stepEmailNext = document.getElementById('step-email-next');
            const stepSourceNext = document.getElementById('step-source-next');
            const stepProjetNext = document.getElementById('step-projet-next');
            const stepBudgetNext = document.getElementById('step-budget-next');
            const stepTimingNext = document.getElementById('step-timing-next');
            const contactSubmit = document.getElementById('contact-submit');
            const rapportEmail = document.getElementById('rapport-email');
            const sourceSite = document.getElementById('source-site');
            const budgetBant = document.getElementById('budget-bant');
            const delai = document.getElementById('delai');

            let wizardStep = 0;

            const applyCtaVariant = () => {
                const ctaButtons = document.querySelectorAll('button[type="button"], button[type="submit"]');
                ctaButtons.forEach(button => {
                    if (button.classList.contains('bg-slate-900')) {
                        button.addEventListener('mouseenter', () => {
                            button.classList.remove('bg-slate-900');
                            button.classList.add('bg-slate-800');
                        });
                        button.addEventListener('mouseleave', () => {
                            button.classList.remove('bg-slate-800');
                            button.classList.add('bg-slate-900');
                        });
                    }
                });
            };

            const surfaceLabels = {
                lt30: 'Moins de 30 m²',
                '30_50': '30-50 m²',
                '50_80': '50-80 m²',
                '80_120': '80-120 m²',
                '120_200': '120-200 m²',
                gt200: 'Plus de 200 m²'
            };

            const formatPrice = (value) => new Intl.NumberFormat('fr-FR').format(Math.round(value)) + ' €';

            const setWizardStep = (step) => {
                wizardStep = Math.max(0, Math.min(6, step));
                wizardTrack.style.transform = `translateX(-${wizardStep * 100}%)`;
                wizardDots.forEach((dot, index) => {
                    dot.classList.toggle('bg-blue-600', index <= wizardStep);
                    dot.classList.toggle('bg-slate-300', index > wizardStep);
                });
            };

            const setResultPage = (page) => {
                const showRdv = page === 'rdv';
                estimationPage.classList.toggle('hidden', showRdv);
                rdvPage.classList.toggle('hidden', !showRdv);
            };

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitButton = form.querySelector('button[type="submit"]');
                const buttonText = submitButton.textContent;
                submitButton.disabled = true;
                submitButton.textContent = 'Calcul en cours...';

                try {
                    const formData = new FormData(form);
                    const response = await fetch('/api/estimation.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Une erreur est survenue.');
                    }

                    const selectedType = formData.get('type_bien');
                    const selectedVille = formData.get('ville');
                    const selectedSurface = surfaceLabels[formData.get('surface_tranche')] || '';

                    document.getElementById('estimation-type-ville').textContent = `${selectedType} à ${selectedVille}`;
                    document.getElementById('estimation-surface').textContent = selectedSurface;
                    document.getElementById('estimation-price').textContent = formatPrice(data.estimation);
                    document.getElementById('estimation-range').textContent = `${formatPrice(data.min)} - ${formatPrice(data.max)}`;

                    resultSection.classList.remove('pointer-events-none', 'max-h-0', '-translate-y-4', 'opacity-0', 'py-0');
                    resultSection.classList.add('max-h-[1200px]', 'translate-y-0', 'opacity-100', 'py-12');
                    setResultPage('estimation');

                    resultSection.scrollIntoView({ behavior: 'smooth' });
                } catch (error) {
                    contactFeedback.className = 'mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700';
                    contactFeedback.textContent = error.message || 'Le service est momentanément indisponible.';
                    contactFeedback.classList.remove('hidden');
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = buttonText;
                }
            });

            goRdvPageBtn.addEventListener('click', () => {
                setResultPage('rdv');
            });

            stepEmailNext.addEventListener('click', async () => {
                if (!rapportEmail.reportValidity()) {
                    return;
                }
                stepEmailNext.disabled = true;
                const originalText = stepEmailNext.textContent;
                stepEmailNext.textContent = 'Envoi...';
                try {
                    const payload = new FormData();
                    payload.append('email', rapportEmail.value.trim());
                    const response = await fetch('/api/rapport.php', { method: 'POST', body: payload });
                    if (!response.ok) {
                        throw new Error('Impossible d\'envoyer le rapport pour le moment.');
                    }
                    setWizardStep(1);
                } catch (error) {
                    contactFeedback.className = 'mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700';
                    contactFeedback.textContent = error.message || 'Service temporairement indisponible.';
                    contactFeedback.classList.remove('hidden');
                } finally {
                    stepEmailNext.disabled = false;
                    stepEmailNext.textContent = originalText;
                }
            });

            stepSourceNext.addEventListener('click', () => {
                if (sourceSite.value === '') {
                    sourceSite.reportValidity();
                    return;
                }
                setWizardStep(2);
            });

            stepProjetNext.addEventListener('click', () => {
                const projetSelected = document.querySelector('input[name="projet"]:checked');
                if (!projetSelected) {
                    alert('Veuillez sélectionner un projet');
                    return;
                }
                setWizardStep(3);
            });

            stepBudgetNext.addEventListener('click', () => {
                if (budgetBant.value === '') {
                    budgetBant.reportValidity();
                    return;
                }
                setWizardStep(4);
            });

            stepTimingNext.addEventListener('click', () => {
                if (delai.value === '') {
                    delai.reportValidity();
                    return;
                }
                setWizardStep(5);
            });

            contactSubmit.addEventListener('click', async () => {
                const prenom = document.querySelector('input[name="prenom"]').value.trim();
                const nom = document.querySelector('input[name="nom"]').value.trim();
                const telephone = document.querySelector('input[name="telephone"]').value.trim();

                if (!prenom || !nom || !telephone) {
                    alert('Veuillez remplir tous les champs');
                    return;
                }

                contactSubmit.disabled = true;
                const originalText = contactSubmit.textContent;
                contactSubmit.textContent = 'Envoi en cours...';

                try {
                    const formData = new FormData();
                    formData.append('prenom', prenom);
                    formData.append('nom', nom);
                    formData.append('telephone', telephone);
                    formData.append('email', rapportEmail.value.trim());
                    formData.append('source', sourceSite.value);
                    formData.append('projet', document.querySelector('input[name="projet"]:checked').value);
                    formData.append('budget', budgetBant.value);
                    formData.append('delai', delai.value);
                    formData.append('type_bien', document.getElementById('estimation-type-ville').textContent);
                    formData.append('surface', document.getElementById('estimation-surface').textContent);
                    formData.append('estimation', document.getElementById('estimation-price').textContent);

                    const response = await fetch('/api/contact.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error('Une erreur est survenue lors de l\'envoi.');
                    }

                    contactFeedback.className = 'mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700';
                    contactFeedback.textContent = 'Votre demande a bien été enregistrée. Nous vous contacterons dans les plus brefs délais.';
                    contactFeedback.classList.remove('hidden');
                    setWizardStep(6);
                } catch (error) {
                    contactFeedback.className = 'mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700';
                    contactFeedback.textContent = error.message || 'Le service est momentanément indisponible.';
                    contactFeedback.classList.remove('hidden');
                } finally {
                    contactSubmit.disabled = false;
                    contactSubmit.textContent = originalText;
                }
            });

            newEstimationBtn.addEventListener('click', () => {
                resultSection.classList.add('pointer-events-none', 'max-h-0', '-translate-y-4', 'opacity-0', 'py-0');
                resultSection.classList.remove('max-h-[1200px]', 'translate-y-0', 'opacity-100', 'py-12');
                form.reset();
                rapportEmail.value = '';
                sourceSite.value = '';
                budgetBant.value = '';
                delai.value = '';
                setWizardStep(0);
                setResultPage('estimation');
                contactFeedback.classList.add('hidden');
                contactFeedback.textContent = '';
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });

            applyCtaVariant();
        });
    </script>
</body>
</html>
