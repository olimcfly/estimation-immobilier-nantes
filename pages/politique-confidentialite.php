<?php require_once __DIR__ . '/../includes/header.php'; ?>

<section class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow-sm space-y-6">
    <h1 class="text-2xl font-semibold">Politique de confidentialité</h1>

    <section>
        <h2 class="text-lg font-medium mb-2">Responsable du traitement</h2>
        <p><?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?> est responsable du traitement des données collectées via ce site.</p>
    </section>

    <section>
        <h2 class="text-lg font-medium mb-2">Données collectées</h2>
        <p>Nous collectons : nom, email, téléphone, adresse, caractéristiques du bien, adresse IP et préférences de cookies.</p>
    </section>

    <section>
        <h2 class="text-lg font-medium mb-2">Finalités</h2>
        <p>Ces données sont utilisées pour l'estimation immobilière, le contact commercial et l'amélioration du service.</p>
    </section>

    <section>
        <h2 class="text-lg font-medium mb-2">Base légale</h2>
        <p>Le traitement repose sur le consentement explicite de l'utilisateur et sur l'intérêt légitime de l'éditeur.</p>
    </section>

    <section>
        <h2 class="text-lg font-medium mb-2">Durée de conservation</h2>
        <p>Les données sont conservées pendant 36 mois, puis supprimées ou anonymisées.</p>
    </section>

    <section>
        <h2 class="text-lg font-medium mb-2">Droits de l'utilisateur</h2>
        <p>Vous disposez des droits d'accès, de rectification, de suppression, de portabilité et d'opposition. Vous pouvez les exercer à tout moment.</p>
    </section>

    <section>
        <h2 class="text-lg font-medium mb-2">Contact DPO</h2>
        <p>Pour toute demande relative à vos données : <?= htmlspecialchars(ADMIN_EMAIL, ENT_QUOTES, 'UTF-8') ?></p>
    </section>

    <section>
        <h2 class="text-lg font-medium mb-2">Cookies utilisés</h2>
        <p>Le site utilise des cookies de session, des cookies analytics et des éléments Google Maps selon votre consentement.</p>
    </section>

    <section>
        <h2 class="text-lg font-medium mb-2">Transferts hors UE</h2>
        <p>Certaines données techniques peuvent transiter hors Union européenne via l'API Google Maps.</p>
    </section>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
