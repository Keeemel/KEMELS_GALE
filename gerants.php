<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/base_donnees/bdd.php';
require_once __DIR__ . '/includes/fonctions.php';
require_once __DIR__ . '/includes/authentification.php';

session_start();

// ——— Mini traitement connexion sur cette page ———
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_from_gerants'])) {
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
        // Si connecté → va au tableau de bord (gérant ou admin)
        redirect('espace_gerant/tableau_bord.php');
      } else {
        $errors[] = "Identifiants invalides.";
      }
    }
  }
}

// ——— Récupération des gérants (liste publique) ———
// Adapte si ta structure diffère : ici on suppose table user(role='gerant') + gerant_profil optionnelle.
$gerants = [];
try {
  $stmt = $pdo->query("
    SELECT u.id, u.email, COALESCE(gp.prenom,'') AS prenom, COALESCE(gp.nom,'') AS nom,
           COALESCE(gp.photo_url,'assets/images/gerants/default.jpg') AS photo_url,
           COALESCE(gp.role_texte,'Gérant') AS role_texte
    FROM user u
    LEFT JOIN gerant_profil gp ON gp.user_id = u.id
    WHERE u.role IN ('gerant','admin')  -- on peut afficher admin aussi si tu veux
    ORDER BY gp.nom, gp.prenom
  ");
  $gerants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  // silencieux: affiche juste une page vide si la table n'existe pas encore
}
?>

<main class="page-gerants">
  <section class="container layout-2col">
    <div class="col-main">
      <header class="section-head">
        <h1>Gérants</h1>
        <p class="muted">Découvrez les responsables du parc et leur rôle.</p>
      </header>

      <div class="cards">
        <?php if (empty($gerants)): ?>
          <p class="muted">Aucun gérant enregistré pour le moment.</p>
        <?php else: ?>
          <?php foreach ($gerants as $g): ?>
            <article class="card-gerant">
              <img class="card-gerant__photo" src="<?= e($g['photo_url']) ?>" alt="Photo de <?= e($g['prenom'].' '.$g['nom']) ?>">
              <div class="card-gerant__body">
                <h3><?= e(trim($g['prenom'].' '.$g['nom']) ?: 'Profil') ?></h3>
                <p class="role"><?= e($g['role_texte']) ?></p>
                <p class="email"><?= e($g['email']) ?></p>
                <!-- Bouton vers carte ou détails si besoin -->
                <a class="btn btn--ghost" href="carte.php">Voir ses éoliennes</a>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <aside class="col-side">
      <div class="login-panel">
        <h2>Connexion gérant</h2>
        <p class="muted">Accédez à l’espace de gestion de vos éoliennes.</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert--error">
            <?php foreach ($errors as $e): ?><p><?= e($e) ?></p><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" class="auth-form" novalidate>
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="login_from_gerants" value="1">

          <label class="field">
            <span>Email</span>
            <input type="email" name="email" required placeholder="ex: jean.dupont@mail.com" value="<?= e($_POST['email'] ?? '') ?>">
          </label>

          <label class="field">
            <span>Mot de passe</span>
            <input type="password" name="password" required placeholder="Votre mot de passe">
          </label>

          <label class="checkbox">
            <input type="checkbox" name="remember" value="1">
            <span>Se souvenir de moi</span>
          </label>

          <button class="btn btn--light btn--full" type="submit">Se connecter</button>
          <p class="auth-help"><a href="connexion.php">Plus d’options</a></p>
        </form>
      </div>
    </aside>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
