<?php http_response_code(500); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur serveur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="text-center max-w-md px-4">
        <div class="text-8xl font-black text-gray-200 select-none">500</div>
        <div class="text-5xl mt-4">🔧</div>
        <h1 class="text-2xl font-bold text-gray-900 mt-6">
            Oups, une erreur est survenue
        </h1>
        <p class="text-gray-600 mt-3">
            Notre équipe technique a été notifiée.
            Veuillez réessayer dans quelques instants.
        </p>
        <a href="/" class="mt-8 inline-block bg-blue-600 text-white px-6 py-3
                          rounded-xl font-semibold hover:bg-blue-700 transition">
            Retour à l'accueil
        </a>
    </div>
</body>
</html>
