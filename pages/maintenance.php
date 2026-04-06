<?php
// Vérifier le bypass maintenance (cookie ou IP admin)
$bypassCookie = $_COOKIE['maintenance_bypass'] ?? '';
$bypassValid = ($bypassCookie === md5('estimia_bypass_' . date('Y-m')));

if ($bypassValid || (defined('ADMIN_IP') && ($_SERVER['REMOTE_ADDR'] ?? '') === ADMIN_IP)) {
    return; // Laisser passer
}

http_response_code(503);
header('Retry-After: 3600');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance en cours</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen
             flex items-center justify-center">
    <div class="text-center max-w-lg px-4">
        <div class="text-6xl mb-6">🏗️</div>
        <h1 class="text-3xl font-bold text-gray-900">
            Maintenance en cours
        </h1>
        <p class="text-gray-600 mt-4 text-lg">
            Nous améliorons notre service d'estimation immobilière.
            Nous serons de retour très rapidement !
        </p>
        <div class="mt-8 bg-white rounded-xl p-6 shadow-sm border">
            <p class="text-sm text-gray-500">Durée estimée</p>
            <p class="text-2xl font-bold text-primary mt-1" id="countdown">
                --:--:--
            </p>
        </div>
        <p class="mt-6 text-sm text-gray-400">
            Pour toute urgence : <?= defined('SITE_PHONE') ? SITE_PHONE : '' ?>
        </p>
    </div>
    <script>
        let seconds = 3600;
        const el = document.getElementById('countdown');
        setInterval(() => {
            seconds--;
            if (seconds <= 0) { location.reload(); return; }
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = seconds % 60;
            el.textContent = `${h}h ${String(m).padStart(2, '0')}m ${String(s).padStart(2, '0')}s`;
        }, 1000);
    </script>
</body>
</html>
<?php exit; ?>
