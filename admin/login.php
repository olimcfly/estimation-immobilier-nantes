<?php

declare(strict_types=1);

require_once __DIR__ . '/auth-utils.php';

session_start();

if (!empty($_SESSION['admin_logged']) && !empty($_SESSION['admin_id'])) {
    header('Location: /admin/index.php');
    exit;
}

$error = null;
$info = null;
$emailValue = '';

try {
    $db = Database::getConnection();
    adminEnsureTables($db);

    $adminCount = (int) $db->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    if ($adminCount === 0) {
        header('Location: /admin/onboarding.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $emailValue = adminLowercase(trim((string) ($_POST['email'] ?? '')));

        if ($emailValue === '' || !filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
            $error = 'Veuillez saisir une adresse email valide.';
        } else {
            $stmt = $db->prepare('SELECT id, prenom, nom, email FROM admins WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $emailValue]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($admin)) {
                $error = 'Aucun administrateur trouvé avec cet email.';
            } else {
                $result = adminGenerateAndSendCode($db, $admin);

                $_SESSION['admin_pending_id'] = (int) $admin['id'];
                $_SESSION['admin_pending_email'] = (string) $admin['email'];
                $_SESSION['admin_pending_name'] = trim((string) $admin['prenom'] . ' ' . (string) $admin['nom']);
                $_SESSION['admin_code_sent_at'] = time();

                if (!$result['sent']) {
                    $error = 'Le code a été généré, mais l\'email n\'a pas pu être envoyé. Vérifiez la configuration SMTP/mail().' ;
                } else {
                    $info = 'Un code de vérification vient d\'être envoyé.';
                }

                header('Location: /admin/verify-code.php');
                exit;
            }
        }
    }
} catch (Throwable $exception) {
    $error = 'Impossible de préparer la connexion administrateur. Vérifiez la base de données.';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Administrateur | Nantes Immo Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%);
        }
        .logo-gradient {
            background: linear-gradient(45deg, #2563eb, #7c3aed);
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="mx-auto flex min-h-screen max-w-7xl items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-4xl overflow-hidden rounded-2xl shadow-2xl">
            <div class="grid lg:grid-cols-2">
                <!-- Left Panel - Branding -->
                <div class="gradient-bg flex flex-col justify-between p-8 text-white lg:p-12">
                    <div>
                        <div class="flex items-center space-x-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg logo-gradient">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <span class="text-xl font-bold">Nantes Immo Pro</span>
                        </div>

                        <div class="mt-12">
                            <h1 class="text-3xl font-bold leading-tight">Espace Administrateur Sécurisé</h1>
                            <p class="mt-4 text-blue-100">Accédez à votre tableau de bord avec une authentification moderne et sécurisée.</p>
                        </div>

                        <div class="mt-8 space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <span class="text-sm">Authentification sans mot de passe</span>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <span class="text-sm">Protection des données renforcée</span>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <span class="text-sm">Accès rapide et intuitif</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 text-xs text-blue-200">
                        <p>© <?= date('Y') ?> Nantes Immo Pro - Tous droits réservés</p>
                        <p class="mt-1">Système d'estimation immobilière pour la région nantaise</p>
                    </div>
                </div>

                <!-- Right Panel - Login Form -->
                <div class="bg-white p-8 sm:p-10">
                    <div class="mb-8 text-center lg:hidden">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg logo-gradient">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h2 class="mt-3 text-xl font-bold text-slate-900">Nantes Immo Pro</h2>
                    </div>

                    <h2 class="text-2xl font-bold text-slate-900">Connexion à l'espace admin</h2>
                    <p class="mt-2 text-sm text-slate-600">Saisissez votre email professionnel pour recevoir un code d'accès sécurisé.</p>

                    <?php if ($error !== null): ?>
                        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($info !== null): ?>
                        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-emerald-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-emerald-700"><?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="mt-8 space-y-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700">Email professionnel</label>
                            <div class="mt-1">
                                <input id="email" name="email" type="email" required autocomplete="email" value="<?= htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8') ?>"
                                       class="block w-full appearance-none rounded-lg border border-slate-300 px-3 py-2 placeholder-slate-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm"
                                       placeholder="votre@email.pro">
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="flex w-full justify-center rounded-lg border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                    </svg>
                                    Recevoir mon code d'accès
                                </span>
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 text-center text-xs text-slate-500">
                        <p>Accès réservé aux administrateurs agréés de Nantes Immo Pro</p>
                        <p class="mt-1">Système d'authentification sécurisé par code temporaire</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
