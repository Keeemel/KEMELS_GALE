<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/fonctions.php';

// Initialisation
$errors = [];
$success = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!csrf_verify($_POST['csrf'] ?? '')) {
        $errors[] = "Erreur de sécurité (session expirée). Veuillez réessayer.";
    } else {
        // Récupération et nettoyage
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sujet = trim($_POST['sujet'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validation simple
        if (empty($nom) || empty($email) || empty($message)) {
            $errors[] = "Merci de remplir tous les champs obligatoires.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email n'est pas valide.";
        } else {
            // Configuration de l'email
            $destinataire = "kemelbelfquih@gmail.com";
            $titreEmail = "[KEMELS GALE] Nouveau message de : $nom";
            
            // En-têtes
            $headers = "From: no-reply@kemels-gale.fr\r\n"; // Adapter selon votre domaine
            $headers .= "Reply-To: $email\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            // Corps du message
            $corpsMessage = "Vous avez reçu un nouveau message via le formulaire de contact.\n\n";
            $corpsMessage .= "Nom : $nom\n";
            $corpsMessage .= "Email : $email\n";
            $corpsMessage .= "Sujet : " . ($sujet ?: 'Non précisé') . "\n\n";
            $corpsMessage .= "--- MESSAGE ---\n";
            $corpsMessage .= $message . "\n";
            $corpsMessage .= "---------------\n";

            // Envoi
            if (mail($destinataire, $titreEmail, $corpsMessage, $headers)) {
                flash('success', "Votre message a bien été envoyé. Nous vous répondrons sous peu.");
                redirect('contact.php'); // Redirection pour éviter le renvoi du form
            } else {
                $errors[] = "Une erreur est survenue lors de l'envoi de l'email. Réessayez plus tard.";
            }
        }
    }
}
?>

<main class="page-contact">
    <!-- En-tête Hero -->
    <section class="contact-hero">
        <div class="container">
            <h1>Contactez-nous</h1>
            <p>Une question sur le parc éolien ou une demande spécifique ?<br>Remplissez le formulaire ci-dessous.</p>
        </div>
    </section>

    <div class="container">
        <div class="contact-layout">
            
            <!-- Colonne de gauche : Infos -->
            <div class="contact-info">
                <h3>Nos Coordonnées</h3>
                
                <div class="contact-item">
                    <div class="contact-icon">📍</div>
                    <div class="contact-text">
                        <strong>Adresse</strong>
                        <p>Parc Éolien Kemel's Gale<br>123 Route du Vent<br>65100 Lourdes, France</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">📧</div>
                    <div class="contact-text">
                        <strong>Email</strong>
                        <p><a href="mailto:kemelbelfquih@gmail.com">kemelbelfquih@gmail.com</a></p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">📞</div>
                    <div class="contact-text">
                        <strong>Téléphone</strong>
                        <p><a href="tel:+33600000000">06 00 00 00 00</a></p>
                    </div>
                </div>
            </div>

            <!-- Colonne de droite : Formulaire -->
            <div class="contact-form-wrapper">
                
                <!-- Affichage des messages Flash (Succès) -->
                <?php if ($msg = flash('success')): ?>
                    <div class="alert alert--success" style="margin-bottom:20px; color:black;">
                        <?= e($msg) ?>
                    </div>
                <?php endif; ?>

                <!-- Affichage des erreurs -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert--error" style="margin-bottom:20px; color:black; ">
                        <?php foreach ($errors as $err): ?>
                            <p><?= e($err) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="contact-form">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                    <div class="form-row">
                        <label class="field">
                            <span>Votre Nom *</span>
                            <input type="text" name="nom" required placeholder="Jean Dupont" value="<?= e($_POST['nom'] ?? '') ?>">
                        </label>
                        
                        <label class="field">
                            <span>Votre Email *</span>
                            <input type="email" name="email" required placeholder="jean@exemple.com" value="<?= e($_POST['email'] ?? '') ?>">
                        </label>
                    </div>

                    <label class="field">
                        <span>Sujet</span>
                        <input type="text" name="sujet" placeholder="Demande d'information..." value="<?= e($_POST['sujet'] ?? '') ?>">
                    </label>

                    <label class="field">
                        <span>Message *</span>
                        <textarea name="message" required placeholder="Bonjour, je souhaiterais avoir des informations sur..."><?= e($_POST['message'] ?? '') ?></textarea>
                    </label>

                    <button type="submit" class="contact-btn">
                        Envoyer le message ✉
                    </button>
                </form>
            </div>

        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>