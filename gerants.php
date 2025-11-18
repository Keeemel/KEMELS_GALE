<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/base_donnees/bdd.php';
require_once __DIR__ . '/includes/fonctions.php';
require_once __DIR__ . '/includes/authentification.php';

session_start();

// Gestion de la connexion depuis le formulaire
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
  if (!csrf_verify($_POST['csrf'] ?? '')) {
    $errors[] = "Session expirée. Réessayez.";
  } else {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($email === '' || $password === '') {
      $errors[] = "Email et mot de passe obligatoires.";
    } else {
      $ok = auth_login($pdo, $email, $password, $remember);
      if ($ok) {
        flash('success', "Connexion réussie. Bienvenue !");
        redirect('espace_gerant/tableau_bord.php');
      } else {
        $errors[] = "Identifiants invalides.";
      }
    }
  }
}

// Récupération des gérants depuis la BDD
$gerants = [];
try {
  $stmt = $pdo->query("
    SELECT u.id, u.nom, u.prenom, u.email, u.role,
           gp.role_texte, gp.photo_url
    FROM user u
    LEFT JOIN gerant_profil gp ON u.id = gp.user_id
    WHERE u.role IN ('gerant', 'admin')
    ORDER BY u.nom ASC
  ");
  $gerants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  // Si erreur, on continue avec tableau vide
}
?>

<main class="page-gerants">
  <div class="layout-2col">
    
    <!-- Colonne principale : liste des gérants -->
    <section>
      <header class="section-head">
        <h1>Nos gérants</h1>
        <p class="muted">
          Découvrez l'équipe qui gère et supervise le parc éolien KEMEL'S GALE.
          Chaque gérant assure le suivi technique et la maintenance des éoliennes.
        </p>
      </header>

      <?php if (empty($gerants)): ?>
        <div style="text-align: center; padding: 40px; color: #5a7368;">
          <p>Aucun gérant enregistré pour le moment.</p>
        </div>
      <?php else: ?>
        <div class="cards">
          <?php foreach ($gerants as $g): ?>
            <article class="card-gerant">
              <img 
                src="<?= e($g['photo_url'] ?? 'assets/images/gerants/default.jpg') ?>" 
                alt="<?= e($g['prenom'] . ' ' . $g['nom']) ?>"
                class="card-gerant__photo"
                onerror="this.src='assets/images/gerants/default.jpg'"
              >
              <div class="card-gerant__body">
                <h3><?= e($g['prenom'] . ' ' . $g['nom']) ?></h3>
                <span class="role">
                  <?= e($g['role_texte'] ?? ucfirst($g['role'])) ?>
                </span>
                <a href="mailto:<?= e($g['email']) ?>" class="email">
                  <?= e($g['email']) ?>
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- Colonne latérale : panneau de connexion -->
    <aside class="login-panel">
      <h2>Espace gérant</h2>
      <p class="muted">
        Connectez-vous pour accéder à votre tableau de bord et gérer vos éoliennes.
      </p>

      <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
          <?php foreach ($errors as $e): ?>
            <p><?= e($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($msg = flash('success')): ?>
        <div class="alert alert--success">
          <p><?= e($msg) ?></p>
        </div>
      <?php endif; ?>

      <?php if (is_logged()): ?>
        <!-- Si déjà connecté -->
        <div style="text-align: center; padding: 20px 0;">
          <p style="margin-bottom: 16px; color: #c9e7dc;">
            Vous êtes connecté en tant que <strong><?= e($_SESSION['role']) ?></strong>
          </p>
          <a href="espace_gerant/tableau_bord.php" class="btn btn--full" style="margin-bottom: 10px;">
            Accéder au tableau de bord
          </a>
          <a href="deconnexion.php" class="btn btn--ghost btn--full">
            Se déconnecter
          </a>
        </div>
      <?php else: ?>
        <!-- Formulaire de connexion -->
        <form method="post" class="auth-form" novalidate>
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="login">

          <label class="field">
            <span>Email</span>
            <input 
              type="email" 
              name="email" 
              required 
              placeholder="votre.email@mail.com"
              value="<?= e($_POST['email'] ?? '') ?>"
            >
          </label>

          <label class="field">
            <span>Mot de passe</span>
            <input 
              type="password" 
              name="password" 
              required 
              placeholder="••••••••"
            >
          </label>

          <label class="checkbox">
            <input type="checkbox" name="remember" value="1">
            <span>Se souvenir de moi</span>
          </label>

          <button class="btn btn--full" type="submit">
            Se connecter
          </button>
        </form>
      <?php endif; ?>
    </aside>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>