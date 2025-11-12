<?php
require_once __DIR__ . '/../includes/bdd.php';
require_once __DIR__ . '/../includes/fonctions.php';
require_once __DIR__ . '/../includes/authentification.php';

session_start();
require_login(); // impose une session valide

// Option: limiter strictement aux rôles gérant/admin
if ($_SESSION['role'] !== 'gerant' && $_SESSION['role'] !== 'admin') {
  flash('success', "Accès refusé : réservé aux gérants.");
  redirect('../connexion.php');
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="page-dashboard">
  <section class="container">
    <header class="section-head">
      <h1>Tableau de bord</h1>
      <p class="muted">
        Bonjour <?= e($_SESSION['role'] === 'admin' ? 'Administrateur' : 'Gérant'); ?> —
        gérez vos éoliennes, consultez les stats et l’état du parc.
      </p>
    </header>

    <div class="dash-grid">
      <a class="dash-card" href="mes_eoliennes.php">
        <h3>Mes éoliennes</h3>
        <p>Ajouter, modifier, supprimer</p>
      </a>

      <a class="dash-card" href="../carte.php">
        <h3>Carte du parc</h3>
        <p>Statuts et localisation</p>
      </a>

      <?php if ($_SESSION['role'] === 'admin'): ?>
      <a class="dash-card" href="gerer_utilisateurs.php">
        <h3>Utilisateurs</h3>
        <p>Comptes gérants & droits (admin)</p>
      </a>
      <?php endif; ?>

      <a class="dash-card" href="../deconnexion.php">
        <h3>Déconnexion</h3>
        <p>Fermer ma session</p>
      </a>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
