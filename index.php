<?php

declare(strict_types=1);

/**
 * Page d'accueil premium et sécurisée.
 *
 * Point d'entrée élégant qui évite les erreurs HTTP 500 et guide
 * l'utilisateur vers l'installation ou l'administration.
 */

http_response_code(200);
header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Installation | Estimation Immobilier Nantes</title>
  <meta name="description" content="Plateforme premium d'estimation immobilière à Nantes : installation rapide, interface professionnelle et expérience moderne.">
  <style>
    :root {
      --bg: #f1f5f9;
      --card: #ffffff;
      --text: #0f172a;
      --muted: #64748b;
      --primary: #1d4ed8;
      --primary-2: #2563eb;
      --primary-dark: #1e40af;
      --ring: rgba(37, 99, 235, 0.35);
      --shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
      --radius: 16px;
    }

    * { box-sizing: border-box; }

    body {
      font-family: Inter, "Segoe UI", Roboto, -apple-system, system-ui, sans-serif;
      line-height: 1.6;
      color: var(--text);
      margin: 0;
      min-height: 100vh;
      background:
        radial-gradient(1200px 500px at 50% -100px, rgba(29, 78, 216, 0.22), transparent 60%),
        var(--bg);
      display: flex;
      justify-content: center;
      padding: 2.5rem 1rem;
    }

    .container {
      width: 100%;
      max-width: 940px;
      display: grid;
      gap: 1.25rem;
    }

    .hero {
      padding: 1.2rem 0 0.25rem;
      text-align: center;
    }

    .kicker {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      border: 1px solid rgba(37, 99, 235, 0.25);
      background: rgba(255, 255, 255, 0.7);
      border-radius: 999px;
      color: var(--primary-dark);
      font-weight: 700;
      font-size: 0.78rem;
      padding: 0.35rem 0.7rem;
      letter-spacing: 0.03em;
      text-transform: uppercase;
    }

    h1 {
      margin: 0.9rem 0 0.4rem;
      font-size: clamp(2rem, 2.6vw + 1rem, 3rem);
      line-height: 1.15;
      color: #0b1a3a;
    }

    .subtitle {
      margin: 0 auto;
      color: var(--muted);
      max-width: 700px;
      font-size: 1.05rem;
    }

    .card {
      background: var(--card);
      border: 1px solid rgba(148, 163, 184, 0.25);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: clamp(1.25rem, 2vw, 2rem);
    }

    .card h2 {
      margin-top: 0;
      margin-bottom: 0.7rem;
      font-size: 1.45rem;
      color: #0b1a3a;
    }

    .lead {
      margin-top: 0;
      color: #334155;
    }

    .steps {
      list-style: none;
      margin: 1rem 0 0;
      padding: 0;
      display: grid;
      gap: 0.65rem;
    }

    .steps li {
      background: linear-gradient(180deg, #f8fafc, #f1f5f9);
      border: 1px solid rgba(148, 163, 184, 0.25);
      border-radius: 10px;
      padding: 0.85rem 0.95rem 0.85rem 2.35rem;
      position: relative;
      color: #1e293b;
    }

    .steps li::before {
      content: "✓";
      position: absolute;
      left: 0.9rem;
      top: 0.82rem;
      width: 1.1rem;
      height: 1.1rem;
      border-radius: 999px;
      background: #dbeafe;
      color: var(--primary-dark);
      display: inline-flex;
      justify-content: center;
      align-items: center;
      font-size: 0.8rem;
      font-weight: 700;
    }

    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 0.8rem;
      margin-top: 1.25rem;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 44px;
      text-decoration: none;
      border-radius: 10px;
      border: 1px solid transparent;
      padding: 0.72rem 1.2rem;
      font-weight: 700;
      transition: transform .12s ease, box-shadow .2s ease, background-color .2s ease;
      cursor: pointer;
    }

    .btn-primary {
      background: linear-gradient(180deg, var(--primary-2), var(--primary));
      color: #fff;
      box-shadow: 0 10px 25px rgba(37, 99, 235, 0.35);
    }

    .btn-primary:hover { transform: translateY(-1px); }

    .btn-secondary {
      background: #fff;
      border-color: #cbd5e1;
      color: #1e293b;
    }

    .btn:focus {
      outline: none;
      box-shadow: 0 0 0 4px var(--ring);
    }

    .hint {
      margin-top: 1rem;
      color: var(--muted);
      font-size: 0.95rem;
    }

    .hint a,
    .support a {
      color: var(--primary);
      text-underline-offset: 3px;
    }

    .support {
      margin: 0;
      padding-left: 1rem;
      color: #334155;
    }

    footer {
      text-align: center;
      color: var(--muted);
      font-size: 0.9rem;
      padding: 0.8rem 0 0.4rem;
    }

    @media (max-width: 700px) {
      body { padding: 1.2rem 0.8rem; }
      .actions { flex-direction: column; }
      .btn { width: 100%; }
    }
  </style>
</head>
<body>
  <main class="container">
    <header class="hero">
      <span class="kicker">Plateforme Premium • Nantes</span>
      <h1>Estimation Immobilier Nantes</h1>
      <p class="subtitle">
        Une expérience professionnelle, rapide et fiable pour lancer votre application
        d'estimation immobilière dans les meilleures conditions.
      </p>
    </header>

    <section class="card" aria-labelledby="install-title">
      <h2 id="install-title">Assistant d'installation</h2>
      <p class="lead">
        Bienvenue. Votre environnement est prêt à être configuré. Suivez les étapes ci-dessous
        pour activer l'application en quelques minutes.
      </p>

      <ol class="steps">
        <li>Ouvrez l'assistant et vérifiez les prérequis serveur.</li>
        <li>Renseignez les paramètres de connexion (base de données, accès admin).</li>
        <li>Finalisez la configuration de votre agence et publiez votre plateforme.</li>
      </ol>

      <div class="actions">
        <a href="/install/install.php" class="btn btn-primary">Lancer l'installation</a>
        <a href="/admin/" class="btn btn-secondary">Accéder à l'administration</a>
      </div>

      <p class="hint">
        Le script d'installation est disponible ici : <a href="/install/install.php">/install/install.php</a>.
        Déjà installé ? Revenez sur <a href="/">la page d'accueil</a> ou continuez vers votre interface.
      </p>
    </section>

    <section class="card" aria-labelledby="help-title">
      <h2 id="help-title">Support & Documentation</h2>
      <ul class="support">
        <li>Documentation : <a href="/docs/">Guide d'installation</a></li>
        <li>Support technique : <a href="mailto:support@estimation-nantes.fr">support@estimation-nantes.fr</a></li>
      </ul>
    </section>

    <footer>
      <p>© <?= date('Y') ?> Estimation Immobilier Nantes — Tous droits réservés.</p>
    </footer>
  </main>
</body>
</html>
