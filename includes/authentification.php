<?php
// ====================================================================
//  includes/authentification.php — Gestion de l'authentification
// ====================================================================

/**
 * Connexion utilisateur
 * @param PDO $pdo Instance PDO
 * @param string $email Email de l'utilisateur
 * @param string $password Mot de passe
 * @param bool $remember Activer "Se souvenir de moi"
 * @return bool True si connexion réussie
 */
function auth_login(PDO $pdo, string $email, string $password, bool $remember = false): bool {
  // 1) Cherche l'utilisateur
  $stmt = $pdo->prepare("SELECT id, pass_hash, role FROM user WHERE email = :email LIMIT 1");
  $stmt->execute([':email' => $email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$user) {
    return false;
  }

  // 2) Vérifie le mot de passe
  if (!password_verify($password, $user['pass_hash'])) {
    return false;
  }

  // 3) Crée la session
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
  
  session_regenerate_id(true);
  $_SESSION['user_id'] = (int)$user['id'];
  $_SESSION['role'] = $user['role'] ?? 'internaute';

  // 4) Remember me optionnel (nécessite table auth_token)
  if ($remember) {
    try {
      $token = random_token(32);
      $hash  = token_hash($token);
      $exp   = (new DateTime('+30 days'))->format('Y-m-d H:i:s');

      $pdo->prepare("INSERT INTO auth_token(user_id, token_hash, expires_at) VALUES(:u,:h,:e)")
          ->execute([':u' => $user['id'], ':h' => $hash, ':e' => $exp]);

      // Cookie httpOnly + SameSite=Lax
      setcookie('remember', $user['id'].':'.$token, [
        'expires'  => time() + 60*60*24*30,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
      ]);
    } catch (Throwable $e) {
      // Si la table n'existe pas, on ignore sans casser la connexion
    }
  }

  return true;
}

/**
 * Déconnexion utilisateur
 * @param PDO|null $pdo Instance PDO (peut être null si base non accessible)
 */
function auth_logout(?PDO $pdo = null): void {
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }

  // Supprime le token côté BDD si présent
  if ($pdo && !empty($_COOKIE['remember'])) {
    $parts = explode(':', $_COOKIE['remember'] ?? '');
    if (count($parts) === 2) {
      [$uid, $tok] = $parts;
      if ($uid && $tok) {
        try {
          $pdo->prepare("DELETE FROM auth_token WHERE user_id=:u AND token_hash=:h")
              ->execute([':u' => (int)$uid, ':h' => token_hash($tok)]);
        } catch (Throwable $e) {
          // Silencieux si erreur BDD
        }
      }
    }
  }
  
  // Supprime le cookie
  if (isset($_COOKIE['remember'])) {
    setcookie('remember', '', [
      'expires' => time() - 3600,
      'path' => '/',
      'secure' => isset($_SERVER['HTTPS']),
      'httponly' => true,
      'samesite' => 'Lax'
    ]);
  }

  // Détruit la session
  $_SESSION = [];
  
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
      session_name(), 
      '', 
      time() - 42000, 
      $params['path'], 
      $params['domain'], 
      $params['secure'], 
      $params['httponly']
    );
  }
  
  session_destroy();
}

/**
 * Vérifie qu'un utilisateur est connecté
 * Redirige vers connexion.php si pas connecté
 */
function require_login(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
  
  if (!isset($_SESSION['user_id'])) {
    // Essaie de restaurer via cookie "remember me"
    if (isset($_COOKIE['remember'])) {
      global $pdo;
      
      if ($pdo) {
        $parts = explode(':', $_COOKIE['remember'] ?? '');
        if (count($parts) === 2) {
          [$uid, $tok] = $parts;
          
          if ($uid && $tok) {
            try {
              $stmt = $pdo->prepare("
                SELECT user_id 
                FROM auth_token 
                WHERE user_id=:u AND token_hash=:h 
                AND datetime(expires_at) > datetime('now')
              ");
              $stmt->execute([':u' => (int)$uid, ':h' => token_hash($tok)]);
              
              if ($stmt->fetch()) {
                // Récupère l'utilisateur
                $u = $pdo->prepare("SELECT id, role FROM user WHERE id=:id LIMIT 1");
                $u->execute([':id' => (int)$uid]);
                
                if ($row = $u->fetch(PDO::FETCH_ASSOC)) {
                  session_regenerate_id(true);
                  $_SESSION['user_id'] = (int)$row['id'];
                  $_SESSION['role'] = $row['role'] ?? 'internaute';
                  return; // Connexion restaurée !
                }
              }
            } catch (Throwable $e) {
              // Silencieux
            }
          }
        }
      }
    }
    
    // Sinon redirige vers connexion
    flash('success', 'Connecte-toi pour accéder à cette page.');
    redirect('connexion.php');
  }
}

/**
 * Vérifie qu'un utilisateur a un rôle spécifique
 * @param string|array $roles Rôle(s) autorisé(s)
 * @return bool
 */
function has_role($roles): bool {
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
  
  if (!isset($_SESSION['role'])) {
    return false;
  }
  
  $roles = is_array($roles) ? $roles : [$roles];
  return in_array($_SESSION['role'], $roles, true);
}

/**
 * Exige un rôle spécifique (sinon redirige)
 * @param string|array $roles Rôle(s) autorisé(s)
 * @param string $redirect_url URL de redirection si pas autorisé
 */
function require_role($roles, string $redirect_url = '../connexion.php'): void {
  require_login();
  
  if (!has_role($roles)) {
    flash('success', "Accès refusé : vous n'avez pas les permissions nécessaires.");
    redirect($redirect_url);
  }
}