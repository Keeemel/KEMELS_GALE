<?php
/**
 * PAGE : GESTION DES UTILISATEURS (Admin)
 * Permet de créer, modifier, supprimer des comptes et gérer les photos/profils.
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../base_donnees/bdd.php';
require_once __DIR__ . '/../includes/fonctions.php';
require_once __DIR__ . '/../includes/authentification.php';

session_start();
require_role('admin'); // Sécurité : seul un admin peut accéder ici

$errors = [];
$success = '';

// --- TRAITEMENT DES FORMULAIRES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!csrf_verify($_POST['csrf'] ?? '')) {
        $errors[] = "Session expirée, veuillez rafraîchir la page.";
    } else {
        $action = $_POST['action'] ?? '';

        // 1. AJOUT ou MODIFICATION
        if ($action === 'save') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'gerant';
            $role_texte = trim($_POST['role_texte'] ?? ''); // Ex: "Expert maintenance"
            $password = $_POST['password'] ?? '';

            // Validation basique
            if (empty($nom) || empty($prenom) || empty($email)) {
                $errors[] = "Nom, prénom et email sont obligatoires.";
            }
            
            // Si c'est une création, mot de passe obligatoire
            if (!$id && empty($password)) {
                $errors[] = "Le mot de passe est obligatoire pour un nouvel utilisateur.";
            }

            if (empty($errors)) {
                try {
                    $pdo->beginTransaction();

                    // Gestion de l'upload photo
                    $photo_url = null;
                    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            $filename = uniqid('gerant_') . '.' . $ext;
                            $targetDir = __DIR__ . '/../assets/images/gerants/';
                            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                            
                            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetDir . $filename)) {
                                $photo_url = 'assets/images/gerants/' . $filename;
                            } else {
                                $errors[] = "Erreur lors de l'enregistrement de l'image.";
                            }
                        } else {
                            $errors[] = "Format d'image invalide (jpg, png, webp acceptés).";
                        }
                    }

                    // UPDATE
                    if ($id) {
                        // Update table user
                        $sql = "UPDATE user SET nom=?, prenom=?, email=?, role=? WHERE id=?";
                        $params = [$nom, $prenom, $email, $role, $id];
                        
                        // Si changement de mot de passe
                        if (!empty($password)) {
                            $sql = "UPDATE user SET nom=?, prenom=?, email=?, role=?, pass_hash=? WHERE id=?";
                            $params = [$nom, $prenom, $email, $role, password_hash($password, PASSWORD_DEFAULT), $id];
                        }
                        $pdo->prepare($sql)->execute($params);

                        // Update table profil
                        // Vérifie si profil existe
                        $check = $pdo->prepare("SELECT user_id FROM gerant_profil WHERE user_id=?");
                        $check->execute([$id]);
                        
                        if ($check->fetch()) {
                            $sqlProfil = "UPDATE gerant_profil SET nom=?, prenom=?, role_texte=?";
                            $paramsProfil = [$nom, $prenom, $role_texte];
                            if ($photo_url) {
                                $sqlProfil .= ", photo_url=?";
                                $paramsProfil[] = $photo_url;
                            }
                            $sqlProfil .= " WHERE user_id=?";
                            $paramsProfil[] = $id;
                            $pdo->prepare($sqlProfil)->execute($paramsProfil);
                        } else {
                            // Crée le profil s'il manquait
                            $pdo->prepare("INSERT INTO gerant_profil (user_id, nom, prenom, role_texte, photo_url) VALUES (?,?,?,?,?)")
                                ->execute([$id, $nom, $prenom, $role_texte, $photo_url]);
                        }
                        
                        $success = "Utilisateur modifié avec succès.";

                    // CREATE
                    } else {
                        // Vérif email unique
                        $stmt = $pdo->prepare("SELECT id FROM user WHERE email=?");
                        $stmt->execute([$email]);
                        if ($stmt->fetch()) {
                            throw new Exception("Cet email est déjà utilisé.");
                        }

                        // Insert User
                        $stmt = $pdo->prepare("INSERT INTO user (nom, prenom, email, pass_hash, role) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$nom, $prenom, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
                        $new_id = $pdo->lastInsertId();

                        // Insert Profil
                        $stmt = $pdo->prepare("INSERT INTO gerant_profil (user_id, nom, prenom, role_texte, photo_url) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$new_id, $nom, $prenom, $role_texte, $photo_url]);

                        $success = "Utilisateur créé avec succès.";
                    }

                    $pdo->commit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $errors[] = "Erreur : " . $e->getMessage();
                }
            }
        }

        // 2. SUPPRESSION
        elseif ($action === 'delete') {
            $del_id = (int)$_POST['id'];
            if ($del_id === $_SESSION['user_id']) {
                $errors[] = "Vous ne pouvez pas supprimer votre propre compte.";
            } else {
                $pdo->prepare("DELETE FROM user WHERE id=?")->execute([$del_id]);
                // Cascade gère la suppression du profil
                $success = "Utilisateur supprimé.";
            }
        }
    }
}

// --- RÉCUPÉRATION DES DONNÉES ---
$users = $pdo->query("
    SELECT u.*, gp.role_texte, gp.photo_url 
    FROM user u 
    LEFT JOIN gerant_profil gp ON u.id = gp.user_id 
    ORDER BY u.role ASC, u.nom ASC
")->fetchAll();

// Mode édition ?
$editUser = null;
if (isset($_GET['edit'])) {
    foreach ($users as $u) {
        if ($u['id'] == $_GET['edit']) {
            $editUser = $u;
            break;
        }
    }
}
?>

<main class="page-dashboard">
  <div class="container">
    
    <header class="section-head">
      <h1>Gestion des utilisateurs</h1>
      <p class="muted">Gérez les comptes gérants, leurs photos et leurs informations.</p>
      <div style="margin-top: 20px;">
        <a href="tableau_bord.php" class="btn btn--ghost" style="color:#0c3b2e; border-color:#0c3b2e;">← Retour au tableau de bord</a>
      </div>
    </header>

    <!-- Messages Flash -->
    <?php if (!empty($errors)): ?>
      <div class="alert alert--error" style="margin-bottom:20px;">
        <?php foreach($errors as $e) echo "<p>".e($e)."</p>"; ?>
      </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="alert alert--success" style="margin-bottom:20px;">
        <p><?= e($success) ?></p>
      </div>
    <?php endif; ?>


    <div style="display: grid; grid-template-columns: 1fr 400px; gap: 40px; align-items: start;">
      
      <!-- LISTE DES UTILISATEURS -->
      <div style="background:#fff; padding:30px; border-radius:20px; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
        <h2 style="margin-top:0;">Liste des comptes</h2>
        
        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
            <thead>
                <tr style="text-align:left; border-bottom:2px solid #f0f0f0;">
                    <th style="padding:10px;">Photo</th>
                    <th style="padding:10px;">Identité</th>
                    <th style="padding:10px;">Rôle</th>
                    <th style="padding:10px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:10px;">
                        <img src="../<?= !empty($user['photo_url']) ? e($user['photo_url']) : 'assets/images/gerants/default.jpg' ?>" 
                             alt="Avatar" 
                             style="width:40px; height:40px; border-radius:50%; object-fit:cover; background:#eee;">
                    </td>
                    <td style="padding:10px;">
                        <strong><?= e($user['nom']) ?> <?= e($user['prenom']) ?></strong><br>
                        <small class="muted"><?= e($user['email']) ?></small>
                    </td>
                    <td style="padding:10px;">
                        <span style="
                            padding:4px 8px; border-radius:6px; font-size:0.85rem; font-weight:600;
                            background: <?= $user['role'] === 'admin' ? '#e0f2fe' : '#dcfce7' ?>;
                            color: <?= $user['role'] === 'admin' ? '#0369a1' : '#15803d' ?>;
                        ">
                            <?= ucfirst(e($user['role'])) ?>
                        </span>
                        <?php if(!empty($user['role_texte'])): ?>
                            <br><small class="muted"><?= e($user['role_texte']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px;">
                        <a href="?edit=<?= $user['id'] ?>" style="text-decoration:none; color:#0c3b2e; font-weight:600; margin-right:10px;">Modifier</a>
                        
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');">
                            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button type="submit" style="background:none; border:none; color:#dc2626; cursor:pointer; font-weight:600; padding:0;">Supprimer</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
      </div>

      <!-- FORMULAIRE AJOUT / EDIT -->
      <div class="login-panel" style="position:sticky; top:100px; background:#0c3b2e;">
        <h2><?= $editUser ? 'Modifier' : 'Ajouter' ?> un utilisateur</h2>
        <p class="muted"><?= $editUser ? 'Modifiez les informations ci-dessous.' : 'Remplissez le formulaire pour créer un compte.' ?></p>

        <form method="POST" enctype="multipart/form-data" class="auth-form">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="save">
            <?php if ($editUser): ?>
                <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
            <?php endif; ?>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <label class="field">
                    <span>Nom *</span>
                    <input type="text" name="nom" required value="<?= $editUser ? e($editUser['nom']) : '' ?>" placeholder="Dupont">
                </label>
                <label class="field">
                    <span>Prénom *</span>
                    <input type="text" name="prenom" required value="<?= $editUser ? e($editUser['prenom']) : '' ?>" placeholder="Jean">
                </label>
            </div>

            <label class="field">
                <span>Email *</span>
                <input type="email" name="email" required value="<?= $editUser ? e($editUser['email']) : '' ?>" placeholder="jean.dupont@mail.com">
            </label>

            <label class="field">
                <span>Mot de passe <?= $editUser ? '(laisser vide si inchangé)' : '*' ?></span>
                <input type="password" name="password" placeholder="••••••••">
            </label>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <label class="field">
                    <span>Rôle système</span>
                    <select name="role" style="width:100%; padding:12px; border-radius:12px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:#fff;">
                        <option value="gerant" <?= ($editUser && $editUser['role'] === 'gerant') ? 'selected' : '' ?>>Gérant</option>
                        <option value="admin" <?= ($editUser && $editUser['role'] === 'admin') ? 'selected' : '' ?>>Administrateur</option>
                    </select>
                </label>
                <label class="field">
                    <span>Intitulé (public)</span>
                    <input type="text" name="role_texte" value="<?= $editUser ? e($editUser['role_texte']) : '' ?>" placeholder="Ex: Expert réseau">
                </label>
            </div>

            <label class="field">
                <span>Photo de profil (JPG, PNG)</span>
                <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" style="padding:10px; background:none; border:none;">
                <?php if ($editUser && !empty($editUser['photo_url'])): ?>
                    <div style="margin-top:5px;">
                        <small class="muted">Actuelle :</small>
                        <img src="../<?= e($editUser['photo_url']) ?>" style="height:40px; vertical-align:middle; margin-left:10px; border-radius:4px;">
                    </div>
                <?php endif; ?>
            </label>

            <div style="display:flex; gap:10px; margin-top:10px;">
                <button type="submit" class="btn btn--full btn--light">
                    <?= $editUser ? 'Enregistrer les modifications' : 'Créer l\'utilisateur' ?>
                </button>
                <?php if ($editUser): ?>
                    <a href="gerer_utilisateurs.php" class="btn btn--ghost">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
      </div>

    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>