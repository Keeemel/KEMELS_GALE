<?php 
session_start();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/bdd.php';
require_once __DIR__ . '/includes/fonctions.php';
require_once __DIR__ . '/includes/securite.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!csrf_verify($_POST['csrf'] ?? '')) {
        $errors[] = "Session expirée. Réessaie.";
    } else {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        // Validation
        if ($nom === '') $errors[] = "Le nom est obligatoire.";
        if ($prenom === '') $errors[] = "Le prénom est obligatoire.";
        
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide.";
        }
        
        if ($password === '') {
            $errors[] = "Le mot de passe est obligatoire.";
        } else {
            $pwd_check = validate_password_strength($password);
            if (!$pwd_check['valid']) {
                $errors[] = $pwd_check['message'];
            }
        }
        
        if ($password !== $password_confirm) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
        
        if (empty($errors)) {
            try {
                // Vérifier si l'email existe déjà
                $stmt = $pdo->prepare("SELECT id FROM user WHERE email = :email");
                $stmt->execute([':email' => $email]);
                if ($stmt->fetch()) {
                    $errors[] = "Cet email est déjà utilisé.";
                } else {
                    // Créer le compte
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        INSERT INTO user (nom, prenom, email, pass_hash, role)
                        VALUES (:nom, :prenom, :email, :hash, 'internaute')
                    ");
                    $stmt->execute([
                        ':nom' => $nom,
                        ':prenom' => $prenom,
                        ':email' => $email,
                        ':hash' => $hash
                    ]);
                    
                    $success = true;
                    flash('success', 'Compte créé avec succès ! Tu peux maintenant te connecter.');
                    redirect('connexion.php');
                }
            } catch (PDOException $e) {
                $errors[] = "Erreur lors de la création du compte.";
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
                <img src="assets/images/eoliennes/logo.png" alt="KEMEL'S GALE" />
                <h1>Inscription</h1>
                <p>Crée ton compte pour rejoindre KEMEL'S GALE</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert--error">
                    <?php foreach ($errors as $e): ?>
                        <p><?= e($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="auth-form" novalidate>
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                <label class="field">
                    <span>Nom</span>
                    <input type="text" name="nom" required value="<?= e($_POST['nom'] ?? '') ?>">
                </label>

                <label class="field">
                    <span>Prénom</span>
                    <input type="text" name="prenom" required value="<?= e($_POST['prenom'] ?? '') ?>">
                </label>

                <label class="field">
                    <span>Email</span>
                    <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
                </label>

                <label class="field">
                    <span>Mot de passe</span>
                    <input type="password" name="password" required>
                    <small>Au moins 8 caractères, avec majuscule, minuscule et chiffre</small>
                </label>

                <label class="field">
                    <span>Confirmer le mot de passe</span>
                    <input type="password" name="password_confirm" required>
                </label>

                <button class="btn btn--light btn--full" type="submit">S'inscrire</button>

                <p class="auth-help">
                    Déjà un compte ? <a href="connexion.php">Se connecter</a>
                </p>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
