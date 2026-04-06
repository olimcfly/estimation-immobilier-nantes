<?php require_once __DIR__ . '/../includes/header.php'; ?>

<section class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow-sm space-y-6">
    <h1 class="text-2xl font-semibold">Mentions légales</h1>

    <section>
        <h2 class="text-lg font-medium mb-2">Identification</h2>
        <p>Éditeur du site : <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?></p>
        <p>Responsable de publication : [À compléter dans les paramètres]</p>
        <p>Email : <?= htmlspecialchars(ADMIN_EMAIL, ENT_QUOTES, 'UTF-8') ?></p>
        <p>Téléphone : <?= htmlspecialchars(SITE_PHONE, ENT_QUOTES, 'UTF-8') ?></p>
        <p>SIRET : [À compléter]</p>
    </section>

    <section>
        <h2 class="text-lg font-medium mb-2">Hébergeur</h2>
        <p>O2Switch - 222 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand</p>
    </section>

    <section>
        <h2 class="text-lg font-medium mb-2">Propriété intellectuelle</h2>
        <p>Le contenu de ce site (textes, visuels, structure, scripts) est protégé par les lois françaises et internationales relatives à la propriété intellectuelle.</p>
    </section>

    <section>
        <h2 class="text-lg font-medium mb-2">Limitation de responsabilité</h2>
        <p>Les estimations fournies sont indicatives et ne constituent pas une expertise immobilière opposable. L'éditeur ne peut être tenu responsable d'un usage inadapté des informations.</p>
    </section>

    <section>
        <h2 class="text-lg font-medium mb-2">Droit applicable</h2>
        <p>Le présent site est soumis au droit français. En cas de litige, les tribunaux compétents seront ceux du ressort du siège de l'éditeur.</p>
    </section>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
