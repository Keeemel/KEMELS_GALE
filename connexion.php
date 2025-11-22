<?php
session_start();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/bdd.php';
require_once __DIR__ . '/includes/fonctions.php';
require_once __DIR__ . '/includes/authentification.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_verify($_POST['csrf'] ?? '')) {
    $errors[] = "Session expirée. Réessaie.";
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
        redirect('index.php');
      } else {
        $errors[] = "Identifiants invalides.";
      }
    }
  }
}
?>

<main class="page-auth">
  <section class="auth-hero">
    <div class="auth-hero__overlay"></div>
    <div class="auth-card">
      <div class="auth-card__brand">
        <img src="assets/images/eoliennes/logo.png" alt="KEMEL’S GALE" />
        <h1>Connexion</h1>
        <p>Accède à ton espace pour gérer le parc.</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
          <?php foreach ($errors as $e): ?>
            <p><?= e($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($msg = flash('success')): ?>
        <div class="alert alert--success"><p><?= e($msg) ?></p></div>
      <?php endif; ?>

      <form method="post" class="auth-form" novalidate>
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <label class="field">
          <span>Email</span>
          <input type="email" name="email" required placeholder="ex: jean.dupont@mail.com" value="<?= e($_POST['email'] ?? '') ?>">
        </label>

        <label class="field">
          <span>Mot de passe</span>
          <input type="password" name="password" required placeholder="Ton mot de passe">
        </label>

        <label class="checkbox">
          <input type="checkbox" name="remember" value="1">
          <span>Se souvenir de moi</span>
        </label>

        <button class="btn btn--light btn--full" type="submit">Se connecter</button>

        <p class="auth-help">
          <a href="#">Mot de passe oublié ?</a>
        </p>
      </form>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
