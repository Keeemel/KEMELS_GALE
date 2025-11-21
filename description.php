<?php
// description.php
// Affiche la page Description publique du site KEMEL'S GALE

// include backend si besoin (pour dynamisme éventuel)
// require_once __DIR__ . '/includes/bdd.php';
// require_once __DIR__ . '/includes/fonctions.php';

require_once __DIR__ . '/includes/header.php';
?>

<main class="page-description">

  <header class="desc-hero">
    <div class="container">
      <div class="desc-hero__inner">
        <div class="desc-hero__text">
          <h1>DESCRIPTION</h1>
          <p class="lead">
            Kemel’s Gale Wind Farm — un parc éolien pensé pour maximiser l’énergie renouvelable locale
            tout en minimisant l’impact environnemental et le bruit. Découvrez la technologie, l’histoire,
            et les actions menées pour la biodiversité.
          </p>

          <div class="hero-ctas">
            <a class="btn btn--light" href="carte.php">Voir la carte</a>
            <a class="btn btn--ghost" href="gerants.php">Nos gérants</a>           
          </div>
        </div>

        <figure class="desc-hero__media" aria-hidden="true">
          <img src="assets/images/fonds/eoliennes_pano.jpg" alt="Panorama du parc éolien">
        </figure>
      </div>
    </div>
  </header>

  <section class="desc-section desc-tech">
    <div class="container grid-2">
      <div class="panel">
        <h2>Technologies utilisées</h2>
        <p class="muted">Composition technique du parc et innovations intégrées.</p>

        <ul class="tech-list">
          <li><strong>Turbines à pas variable</strong> — optimisation par vent et charge.</li>
          <li><strong>SCADA & capteurs IoT</strong> — maintenance prédictive et monitoring 24/7.</li>
          <li><strong>Réduction du bruit</strong> — pales profilées et découplage antivibratoire.</li>
          <li><strong>Matériaux recyclables</strong> — conception orientée circularité.</li>
        </ul>
      </div>

      <figure class="panel media">
        <img src="assets/images/eoliennes/technique.png" alt="Schéma technique d'une turbine">
        <figcaption>Exemple : architecture mécanique & électronique d'une turbine.</figcaption>
      </figure>
    </div>
  </section>

  <section class="desc-section desc-history">
    <div class="container">
      <h2>Histoire du parc</h2>
      <p class="muted">Du projet initial à la mise en service — étapes clés.</p>

      <ol class="timeline">
        <li>
          <strong>Études</strong>
          <p>Mesures anémométriques, études d'impact écologique, choix d'implantation.</p>
        </li>

        <li>
          <strong>Autorisations</strong>
          <p>Consultations locales, étude acoustique, obtention des permis et accords environnementaux.</p>
        </li>

        <li>
          <strong>Construction</strong>
          <p>Installation des fondations, montage des mâts et pales, raccordement électrique.</p>
        </li>

        <li>
          <strong>Mise en service</strong>
          <p>Tests, calibrations, synchronisation avec le réseau, début de la production.</p>
        </li>
      </ol>
    </div>
  </section>

  <section class="desc-section desc-impact">
    <div class="container impact-grid">
      <article class="impact-card">
        <div class="impact-icon"><img src="assets/icons/leaf.svg" alt=""></div>
        <h3>Réduction CO₂</h3>
        <p>Production locale qui remplace des sources fossiles et réduit significativement les émissions.</p>
      </article>

      <article class="impact-card">
        <div class="impact-icon"><img src="assets/icons/sound.svg" alt=""></div>
        <h3>Faible nuisance sonore</h3>
        <p>Design des pales & stratégies d’exploitation réduisent l’impact sonore dans les villages proches.</p>
      </article>

      <article class="impact-card">
        <div class="impact-icon"><img src="assets/icons/recycle.svg" alt=""></div>
        <h3>Recyclage élevé</h3>
        <p>Choix de matériaux et filières de recyclage pour limiter l’empreinte sur le long terme.</p>
      </article>
    </div>
  </section>

  <section class="desc-section desc-data">
    <div class="container">
      <h2>Données et performances</h2>
      <p class="muted">Indicateurs mesurés en continu — exemple :</p>

      <div class="data-cards">
        <div class="data-card">
          <span class="value">24</span>
          <span class="label">Éoliennes</span>
        </div>
        <div class="data-card">
          <span class="value">96 GWh</span>
          <span class="label">Production / an (est.)</span>
        </div>
        <div class="data-card">
          <span class="value">-28 kt</span>
          <span class="label">CO₂ évité / an</span>
        </div>
      </div>
    </div>
  </section>

  <section class="desc-final">
    <div class="container">
      <p class="final-text">Produire une énergie propre, durable et locale.</p>
      <a class="btn btn--light" href="carte.php">Voir la carte</a>
    </div>
  </section>

</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
