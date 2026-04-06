<?php

declare(strict_types=1);

/**
 * Script d'installation (MVP).
 *
 * Cette page sert de base concrète pour démarrer la configuration de l'app.
 */

http_response_code(200);
header('Content-Type: text/html; charset=UTF-8');

$errors = [];
$success = null;

$checks = [
    'PHP version >= 8.1' => version_compare(PHP_VERSION, '8.1.0', '>='),
    'PDO extension loaded' => extension_loaded('PDO'),
    'JSON extension loaded' => extension_loaded('json'),
    'OpenSSL extension loaded' => extension_loaded('openssl'),
];

$defaults = [
    'app_name' => 'Estimation Immobilier Nantes',
    'app_url' => ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
    'db_host' => '127.0.0.1',
    'db_port' => '3306',
    'db_name' => '',
    'db_user' => '',
    'db_pass' => '',
    'admin_email' => '',
];

$data = $defaults;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($data as $key => $value) {
        $data[$key] = trim((string)($_POST[$key] ?? $value));
    }

    if ($data['db_name'] === '') {
        $errors[] = 'Le nom de la base de données est requis.';
    }
    if ($data['db_user'] === '') {
        $errors[] = 'L’utilisateur base de données est requis.';
    }
    if ($data['admin_email'] === '' || filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors[] = 'Un email administrateur valide est requis.';
    }

    if ($errors === []) {
        $output = "<?php\n";
        $output .= "return [\n";
        foreach ($data as $key => $value) {
            $safeValue = str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
            $output .= "    '{$key}' => '{$safeValue}',\n";
        }
        $output .= "    'generated_at' => '" . date('c') . "',\n";
        $output .= "];\n";

        $targetDir = __DIR__ . '/generated';
        $targetFile = $targetDir . '/app.config.php';

        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            $errors[] = 'Impossible de créer le dossier de configuration : ' . htmlspecialchars($targetDir, ENT_QUOTES, 'UTF-8');
        } elseif (file_put_contents($targetFile, $output) === false) {
            $errors[] = 'Impossible d’écrire le fichier de configuration : ' . htmlspecialchars($targetFile, ENT_QUOTES, 'UTF-8');
        } else {
            $success = 'Configuration générée avec succès dans install/generated/app.config.php';
        }
    }
}

?><!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Script d'installation | Estimation Immobilier Nantes</title>
  <style>
    :root {
      --bg: #f8fafc;
      --card: #ffffff;
      --text: #0f172a;
      --muted: #64748b;
      --primary: #2563eb;
      --danger: #b91c1c;
      --success: #166534;
    }
    body { font-family: Inter, Segoe UI, sans-serif; margin: 0; background: var(--bg); color: var(--text); }
    .container { max-width: 920px; margin: 2rem auto; padding: 0 1rem; }
    .card { background: var(--card); border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.2rem; margin-bottom: 1rem; }
    h1, h2 { margin-top: 0; }
    .muted { color: var(--muted); }
    .ok { color: var(--success); }
    .ko { color: var(--danger); }
    .grid { display: grid; gap: .85rem; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); }
    label { font-weight: 600; display: block; margin-bottom: .35rem; }
    input { width: 100%; min-height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; padding: .5rem .65rem; }
    .btn { background: var(--primary); color: #fff; border: 0; border-radius: 8px; padding: .72rem 1rem; font-weight: 700; cursor: pointer; }
    .alert { border-radius: 10px; padding: .7rem .9rem; margin: .5rem 0 1rem; }
    .alert-error { background: #fee2e2; color: #7f1d1d; }
    .alert-success { background: #dcfce7; color: #14532d; }
    code { background: #f1f5f9; padding: .12rem .3rem; border-radius: 4px; }
  </style>
</head>
<body>
  <main class="container">
    <section class="card">
      <h1>Script d'installation de votre app</h1>
      <p class="muted">Tu le cherchais : il est ici <code>/install/install.php</code>. Cette page initialise la configuration.</p>
      <p><a href="/install/index.php">← Retour à la page d’installation</a></p>
    </section>

    <section class="card">
      <h2>Vérifications système</h2>
      <ul>
        <?php foreach ($checks as $label => $status): ?>
          <li class="<?= $status ? 'ok' : 'ko' ?>">
            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?> — <?= $status ? 'OK' : 'À corriger' ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <p>Version PHP détectée : <code><?= htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8') ?></code></p>
    </section>

    <section class="card">
      <h2>Paramètres de base</h2>

      <?php if ($errors !== []): ?>
        <div class="alert alert-error">
          <strong>Merci de corriger :</strong>
          <ul>
            <?php foreach ($errors as $error): ?>
              <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($success !== null): ?>
        <div class="alert alert-success">
          <strong><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="grid">
          <div>
            <label for="app_name">Nom application</label>
            <input id="app_name" name="app_name" value="<?= htmlspecialchars($data['app_name'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div>
            <label for="app_url">URL application</label>
            <input id="app_url" name="app_url" value="<?= htmlspecialchars($data['app_url'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div>
            <label for="db_host">DB Host</label>
            <input id="db_host" name="db_host" value="<?= htmlspecialchars($data['db_host'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div>
            <label for="db_port">DB Port</label>
            <input id="db_port" name="db_port" value="<?= htmlspecialchars($data['db_port'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div>
            <label for="db_name">DB Name</label>
            <input id="db_name" name="db_name" value="<?= htmlspecialchars($data['db_name'], ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
          <div>
            <label for="db_user">DB User</label>
            <input id="db_user" name="db_user" value="<?= htmlspecialchars($data['db_user'], ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
          <div>
            <label for="db_pass">DB Password</label>
            <input id="db_pass" name="db_pass" type="password" value="<?= htmlspecialchars($data['db_pass'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div>
            <label for="admin_email">Email admin</label>
            <input id="admin_email" name="admin_email" type="email" value="<?= htmlspecialchars($data['admin_email'], ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
        </div>

        <p style="margin-top:1rem;">
          <button type="submit" class="btn">Générer la configuration</button>
        </p>
      </form>
    </section>
  </main>
</body>
</html>
