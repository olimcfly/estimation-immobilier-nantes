<?php

declare(strict_types=1);

require_once __DIR__ . '/auth-utils.php';

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!empty($_SESSION['admin_logged']) && !empty($_SESSION['admin_id'])) {
    header('Location: /admin/index.php');
    exit;
}

$error = null;
$emailValue = '';
$prenomValue = '';
$nomValue = '';

try {
    $db = Database::getConnection();
    adminEnsureTables($db);

    // Le verrouillage de l'onboarding dépend uniquement de la table `admins`.
    // Aucun fichier setup.lock n'est utilisé dans ce projet.
    $adminCount = (int) $db->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    if ($adminCount > 0) {
        header('Location: /admin/login.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submittedToken = (string) ($_POST['csrf_token'] ?? '');
        $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
        if ($submittedToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
            $error = 'Session invalide. Veuillez recharger la page puis réessayer.';
        }

        $prenomValue = trim((string) ($_POST['prenom'] ?? ''));
        $nomValue = trim((string) ($_POST['nom'] ?? ''));
        $emailValue = adminLowercase(trim((string) ($_POST['email'] ?? '')));

        if ($error === null && ($prenomValue === '' || $nomValue === '' || $emailValue === '')) {
            $error = 'Tous les champs sont obligatoires.';
        } elseif ($error === null && !filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
            $error = 'Adresse email invalide.';
        } elseif ($error === null) {
            $insert = $db->prepare('INSERT INTO admins (prenom, nom, email) VALUES (:prenom, :nom, :email)');
            $insert->execute([
                'prenom' => $prenomValue,
                'nom' => $nomValue,
                'email' => $emailValue,
            ]);

            $adminId = (int) $db->lastInsertId();
            $admin = [
                'id' => $adminId,
                'prenom' => $prenomValue,
                'nom' => $nomValue,
                'email' => $emailValue,
            ];

            $welcomeHtml = adminRenderEmailTemplate('install-success', [
                'prenom' => $prenomValue,
                'nom' => $nomValue,
                'email' => $emailValue,
            ]);
            adminSendEmail($emailValue, 'Installation terminée - Espace administrateur', $welcomeHtml);

            adminGenerateAndSendCode($db, $admin, 'onboarding');

            $_SESSION['admin_pending_id'] = $adminId;
            $_SESSION['admin_pending_email'] = $emailValue;
            $_SESSION['admin_pending_name'] = trim($prenomValue . ' ' . $nomValue);
            $_SESSION['admin_code_sent_at'] = time();

            header('Location: /admin/verify-code.php');
            exit;
        }
    }
} catch (Throwable $exception) {
    $error = 'Impossible de finaliser l\'onboarding pour le moment.';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Onboarding Admin | EstimIA Bordeaux</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-b from-blue-100 via-sky-50 to-white text-slate-900">
<div class="mx-auto flex min-h-screen max-w-6xl items-center justify-center px-6 py-16">
    <section class="w-full max-w-xl rounded-3xl bg-white p-8 shadow-2xl ring-1 ring-slate-200 sm:p-10">
        <p class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-800">Première configuration</p>
        <h1 class="mt-4 text-3xl font-extrabold text-blue-950">Créer le premier compte administrateur</h1>
        <p class="mt-2 text-sm text-slate-600">Ce compte recevra les codes de connexion et les emails système.</p>

        <?php if ($error !== null): ?>
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="mt-8 space-y-5">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string) ($_SESSION['csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            <div>
                <label for="prenom" class="mb-2 block text-sm font-semibold text-slate-700">Prénom</label>
                <input id="prenom" name="prenom" required value="<?php echo htmlspecialchars($prenomValue, ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
            </div>
            <div>
                <label for="nom" class="mb-2 block text-sm font-semibold text-slate-700">Nom</label>
                <input id="nom" name="nom" required value="<?php echo htmlspecialchars($nomValue, ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
            </div>
            <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                <input id="email" name="email" type="email" required autocomplete="email" value="<?php echo htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
            </div>
            <button type="submit" class="w-full rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                Créer le compte et recevoir mon code
            </button>
        </form>
    </section>
</div>
</body>
</html>
