<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="KEMEL'S GALE - Parc éolien durable produisant une énergie propre et locale.">
  <title>KEMEL'S GALE - Parc Éolien Durable</title>

  <?php
  // Détecte le chemin relatif vers la racine
  $depth = substr_count($_SERVER['PHP_SELF'], '/') - 2;
  $base = str_repeat('../', max(0, $depth));
  if (empty($base)) $base = './';
  ?>

  <!-- Chemins relatifs dynamiques -->
  <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
  <link rel="stylesheet" href="<?= $base ?>assets/css/carte.css">
  <link rel="icon" href="<?= $base ?>assets/images/eoliennes/logo2.png">

</head>

<body>

<header class="main-header">
  <div class="container">
    <a href="<?= $base ?>index.php" class="logo">
      <img src="<?= $base ?>assets/images/eoliennes/logo2.png" alt="Logo KEMEL'S GALE" class="logo__img">
      <span class="logo__text">KEMEL'S GALE</span>
    </a>

    <nav class="navbar" id="navbar">
      <ul class="nav-list">
        <li><a href="<?= $base ?>index.php" class="nav-link">Accueil</a></li>
        <li><a href="<?= $base ?>description.php" class="nav-link">Description</a></li>
        <li><a href="<?= $base ?>gerants.php" class="nav-link">Gérants</a></li>
        <li><a href="<?= $base ?>carte.php" class="nav-link">Carte</a></li>
        <li><a href="<?= $base ?>contact.php" class="nav-link">Contact</a></li>
      </ul>
    </nav>

    <button class="burger" id="burger" aria-label="Ouvrir le menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>
