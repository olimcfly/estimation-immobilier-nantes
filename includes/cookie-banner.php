<?php
$showCookieBanner = !isset($_COOKIE['cookies_accepted']) && !isset($_COOKIE['cookies_refused']);
?>
<?php if ($showCookieBanner): ?>
<div id="cookie-banner" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 z-50">
    <div class="max-w-5xl mx-auto flex flex-col md:flex-row md:items-center gap-3 md:gap-6">
        <p class="text-sm flex-1">
            Ce site utilise des cookies pour améliorer votre expérience et analyser le trafic.
            <a class="underline" href="/pages/politique-confidentialite.php">En savoir plus</a>
        </p>
        <div class="flex items-center gap-2">
            <button id="cookie-accept" class="px-4 py-2 rounded bg-primary text-white">Accepter</button>
            <button id="cookie-refuse" class="px-4 py-2 rounded bg-gray-600 text-white">Refuser</button>
        </div>
    </div>
</div>
<script>
(function () {
    const banner = document.getElementById('cookie-banner');
    const acceptBtn = document.getElementById('cookie-accept');
    const refuseBtn = document.getElementById('cookie-refuse');

    function setCookie(name, value, days) {
        const expires = new Date(Date.now() + days * 864e5).toUTCString();
        document.cookie = `${name}=${value}; expires=${expires}; path=/; SameSite=Lax`;
    }

    function hideBanner() {
        if (banner) {
            banner.style.display = 'none';
        }
    }

    function loadAnalytics() {
        if (window.__estimiaAnalyticsLoaded) return;
        window.__estimiaAnalyticsLoaded = true;
        // Placeholder: charger ici vos scripts analytics / maps avancés si nécessaire.
    }

    if (acceptBtn) {
        acceptBtn.addEventListener('click', function () {
            setCookie('cookies_accepted', '1', 365);
            hideBanner();
            loadAnalytics();
        });
    }

    if (refuseBtn) {
        refuseBtn.addEventListener('click', function () {
            setCookie('cookies_refused', '1', 365);
            hideBanner();
        });
    }
})();
</script>
<?php endif; ?>
