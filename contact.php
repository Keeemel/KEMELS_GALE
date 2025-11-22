<?php 
session_start();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/bdd.php';
require_once __DIR__ . '/includes/fonctions.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!csrf_verify($_POST['csrf'] ?? '')) {
        $errors[] = "Session expirée. Réessayez.";
    } else {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sujet = trim($_POST['sujet'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        if ($nom === '') $errors[] = "Le nom est obligatoire.";
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide.";
        }
        if ($sujet === '') $errors[] = "Le sujet est obligatoire.";
        if ($message === '') $errors[] = "Le message est obligatoire.";
        
        if (empty($errors)) {
            // Ici, vous pourriez envoyer un email ou enregistrer en base de données
            // Pour l'instant, on simule juste un succès
            $success = true;
            flash('success', 'Message envoyé avec succès. Nous vous répondrons bientôt.');
        }
    }
}
?>

<main class="page-contact">
    <section class="container">
        <header class="section-head">
            <h1>Contactez-nous</h1>
            <p class="muted">
                Une question, une suggestion ou besoin d'informations sur le parc éolien KEMEL'S GALE ?
                N'hésitez pas à nous contacter.
            </p>
        </header>

        <?php if ($success): ?>
            <div class="alert alert--success">
                <p>Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <?php foreach ($errors as $e): ?>
                    <p><?= e($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="contact-grid">
            <form method="post" class="contact-form">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                <label class="field">
                    <span>Nom complet *</span>
                    <input type="text" name="nom" required value="<?= e($_POST['nom'] ?? '') ?>">
                </label>

                <label class="field">
                    <span>Email *</span>
                    <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
                </label>

                <label class="field">
                    <span>Sujet *</span>
                    <input type="text" name="sujet" required value="<?= e($_POST['sujet'] ?? '') ?>">
                </label>

                <label class="field">
                    <span>Message *</span>
                    <textarea name="message" rows="6" required><?= e($_POST['message'] ?? '') ?></textarea>
                </label>

                <button type="submit" class="btn btn--light">Envoyer le message</button>
            </form>

            <div class="contact-info">
                <h3>Informations de contact</h3>
                <p><strong>Email :</strong> <a href="mailto:contact@kemelsgale.fr">contact@kemelsgale.fr</a></p>
                <p><strong>Téléphone :</strong> +33 (0)1 23 45 67 89</p>
                <p><strong>Adresse :</strong><br>
                   Parc Éolien KEMEL'S GALE<br>
                   123 Route des Énergies Vertes<br>
                   75000 Paris, France</p>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
