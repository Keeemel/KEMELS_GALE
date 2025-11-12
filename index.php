<?php include __DIR__ . '/includes/header.php'; ?>

<main class="home">

  <!-- HERO vidéo plein écran -->
  <section class="hero-video" aria-label="Présentation du parc éolien">
    <video class="hero-video__media" autoplay muted loop playsinline
           poster="assets/images/fonds/AcceuilVideo_poster.jpg">
      <source src="assets/images/fonds/AcceuilVideo.mp4" type="video/mp4">
      Votre navigateur ne supporte pas la vidéo HTML5.
    </video>

    <!-- Overlay sombre pour lisibilité -->
    <div class="hero-video__overlay"></div>

    <!-- Contenu du hero -->
    <div class="hero-video__content">
      <img class="hero-video__logo"
           src="assets/images/eoliennes/logo2.png"
           alt="Logo KEMEL’S GALE - Wind Farm" loading="eager" />
      <h1 class="hero-video__title">
        UN PARC ÉOLIEN <br><span>DURABLE</span>
      </h1>
      <p class="hero-video__subtitle">Produire une énergie propre, durable et locale.</p>

      <div class="hero-video__cta">
        <a href="description.php" class="btn btn--light">Découvrir</a>
        <a href="carte.php" class="btn btn--ghost">Voir la carte</a>
      </div>
    </div>
  </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
