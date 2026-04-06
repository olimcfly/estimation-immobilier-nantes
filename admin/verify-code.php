<?php

declare(strict_types=1);

require_once __DIR__ . '/auth-utils.php';

session_start();

if (!empty($_SESSION['admin_logged']) && !empty($_SESSION['admin_id'])) {
    header('Location: /admin/index.php');
    exit;
}

$pendingAdminId = (int) ($_SESSION['admin_pending_id'] ?? 0);
$pendingEmail = (string) ($_SESSION['admin_pending_email'] ?? '');

if ($pendingAdminId <= 0 || $pendingEmail === '') {
    header('Location: /admin/login.php');
    exit;
}

$error = null;

try {
    $db = Database::getConnection();
    adminEnsureTables($db);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $code = preg_replace('/\D+/', '', (string) ($_POST['code'] ?? ''));
        if ($code === null) {
            $code = '';
        }

        if (strlen($code) !== 6) {
            $error = 'Le code doit contenir exactement 6 chiffres.';
        } else {
            $stmt = $db->prepare('SELECT id, code_hash, expires_at, attempts FROM admin_codes WHERE admin_id = :admin_id AND used_at IS NULL ORDER BY created_at DESC LIMIT 1');
            $stmt->execute(['admin_id' => $pendingAdminId]);
            $codeRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($codeRow)) {
                $error = 'Aucun code actif trouvé. Demandez un nouveau code.';
            } elseif ((int) $codeRow['attempts'] >= 5) {
                $error = 'Trop de tentatives. Demandez un nouveau code.';
            } elseif (strtotime((string) $codeRow['expires_at']) < time()) {
                $error = 'Code expiré. Demandez un nouveau code.';
            } elseif (!password_verify($code, (string) $codeRow['code_hash'])) {
                $db->prepare('UPDATE admin_codes SET attempts = attempts + 1 WHERE id = :id')->execute(['id' => (int) $codeRow['id']]);
                $error = 'Code invalide.';
            } else {
                $db->prepare('UPDATE admin_codes SET used_at = NOW() WHERE id = :id')->execute(['id' => (int) $codeRow['id']]);
                $db->prepare('UPDATE admins SET last_login = NOW() WHERE id = :id')->execute(['id' => $pendingAdminId]);

                $adminStmt = $db->prepare('SELECT id, prenom, nom, email FROM admins WHERE id = :id LIMIT 1');
                $adminStmt->execute(['id' => $pendingAdminId]);
                $admin = $adminStmt->fetch(PDO::FETCH_ASSOC);

                if (!is_array($admin)) {
                    throw new RuntimeException('Compte administrateur introuvable.');
                }

                $_SESSION['admin_logged'] = true;
                $_SESSION['admin_id'] = (int) $admin['id'];
                $_SESSION['admin_email'] = (string) $admin['email'];
                $_SESSION['admin_name'] = trim((string) $admin['prenom'] . ' ' . (string) $admin['nom']);

                unset($_SESSION['admin_pending_id'], $_SESSION['admin_pending_email'], $_SESSION['admin_pending_name'], $_SESSION['admin_code_sent_at']);

                header('Location: /admin/index.php');
                exit;
            }
        }
    }
} catch (Throwable $exception) {
    $error = 'Impossible de vérifier le code pour le moment.';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du code | EstimIA Bordeaux</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-b from-blue-100 via-sky-50 to-white text-slate-900">
<div class="mx-auto flex min-h-screen max-w-6xl items-center justify-center px-6 py-16">
    <section class="w-full max-w-xl rounded-3xl bg-white p-8 shadow-2xl ring-1 ring-slate-200 sm:p-10">
        <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-700">
            <span class="text-2xl">🔐</span>
        </div>
        <h1 class="text-center text-3xl font-extrabold text-blue-950">Entrez votre code à 6 chiffres</h1>
        <p class="mt-3 text-center text-sm text-slate-600">Nous avons envoyé un code à <strong><?php echo htmlspecialchars($pendingEmail, ENT_QUOTES, 'UTF-8'); ?></strong>.</p>

        <?php if ($error !== null): ?>
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="mt-8 space-y-5">
            <div>
                <label for="code" class="mb-2 block text-sm font-semibold text-slate-700">Code de sécurité</label>
                <input id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" minlength="6" required autofocus class="w-full rounded-xl border border-slate-300 px-4 py-3 text-center text-3xl tracking-[0.45em] text-slate-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
            </div>
            <button type="submit" class="w-full rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                Vérifier et accéder au dashboard
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-600">
            <button id="resend-button" type="button" class="font-semibold text-blue-700 hover:text-blue-800 disabled:cursor-not-allowed disabled:opacity-50">
                Renvoyer le code
            </button>
            <span id="resend-message" class="ml-2"></span>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">Le code expire dans 10 minutes.</p>
    </section>
</div>

<script>
const resendButton = document.getElementById('resend-button');
const resendMessage = document.getElementById('resend-message');

resendButton.addEventListener('click', async () => {
    resendButton.disabled = true;
    resendMessage.textContent = 'Envoi...';

    try {
        const response = await fetch('/admin/send-code.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
            },
            body: new URLSearchParams({ action: 'resend' }).toString()
        });

        const payload = await response.json();
        resendMessage.textContent = payload.message || 'Action terminée.';
    } catch (error) {
        resendMessage.textContent = 'Erreur réseau, réessayez.';
    }

    setTimeout(() => {
        resendButton.disabled = false;
    }, 30000);
});
</script>
</body>
</html>
