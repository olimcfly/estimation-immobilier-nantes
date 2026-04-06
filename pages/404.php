<?php
http_response_code(404);

include __DIR__ . '/../includes/header.php';
?>
<section class="min-h-[60vh] flex items-center justify-center">
  <div class="text-center max-w-md">
    <div class="text-8xl font-black text-gray-200 select-none">404</div>
    <div class="text-5xl mt-4">🏠❓</div>

    <h1 class="text-2xl font-bold text-gray-900 mt-6">
      Cette page n'existe pas
    </h1>

    <p class="text-gray-600 mt-3">
      L'adresse que vous cherchez semble introuvable.
      Peut-être que le bien a déjà été vendu ? 😉
    </p>

    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
      <a href="/"
         class="bg-primary text-white px-6 py-3 rounded-xl font-semibold
                hover:bg-primary/90 transition">
        Estimer mon bien
      </a>
      <a href="/pages/prix-m2.php"
         class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-semibold
                hover:bg-gray-200 transition">
        Prix au m²
      </a>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
