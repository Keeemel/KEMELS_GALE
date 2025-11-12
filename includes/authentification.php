<?php
// Besoin de $pdo (PDO SQLite) depuis includes/bdd.php
// Tables attendues : user(id, email UNIQUE, pass_hash, role)
// Optionnel pour "remember me": auth_token(id, user_id, token_hash, expires_at)

function auth_login(PDO $pdo, string $email, string $password, bool $remember = false): bool {
  // 1) Cherche l’utilisateur
  $stmt = $pdo->prepare("SELECT id, pass_hash, role FROM user WHERE email = :email LIMIT 1");
  $stmt->execute([':email' => $email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$user) return false;

  // 2) Vérifie le mot de passe
  if (!password_verify($password, $user['pass_hash'])) return false;

  // 3) Session
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
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
    } catch (Throwable $e){
      // Si la table n’existe pas, on ignore sans casser la connexion session
    }
  }

  return true;
}

function auth_logout(PDO $pdo): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();

  // Supprime le token côté BDD si présent
  if (!empty($_COOKIE['remember'])) {
    [$uid, $tok] = explode(':', $_COOKIE['remember']) + [null, null];
    if ($uid && $tok) {
      try {
        $pdo->prepare("DELETE FROM auth_token WHERE user_id=:u AND token_hash=:h")
            ->execute([':u' => (int)$uid, ':h' => token_hash($tok)]);
      } catch (Throwable $e) {}
    }
    setcookie('remember', '', time()-3600, '/');
  }

  // Détruit la session
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }
  session_destroy();
}

// Middleware simple (à appeler sur les pages protégées)
function require_login(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  if (!isset($_SESSION['user_id'])) {
    // Essayez cookie remember si disponible
    if (isset($_COOKIE['remember'])) {
      // Validation silencieuse du cookie
      global $pdo;
      [$uid, $tok] = explode(':', $_COOKIE['remember']) + [null, null];
      if ($uid && $tok) {
        try {
          $stmt = $pdo->prepare("SELECT user_id FROM auth_token WHERE user_id=:u AND token_hash=:h AND datetime(expires_at) > datetime('now')");
          $stmt->execute([':u' => (int)$uid, ':h' => token_hash($tok)]);
          if ($stmt->fetch()) {
            // Récupère l’utilisateur
            $u = $pdo->prepare("SELECT id, role FROM user WHERE id=:id LIMIT 1");
            $u->execute([':id' => (int)$uid]);
            if ($row = $u->fetch(PDO::FETCH_ASSOC)) {
              session_regenerate_id(true);
              $_SESSION['user_id'] = (int)$row['id'];
              $_SESSION['role'] = $row['role'] ?? 'internaute';
              return;
            }
          }
        } catch (Throwable $e) { /* ignore */ }
      }
    }
    // Sinon redirige vers connexion
    flash('success', 'Connecte-toi pour accéder à cette page.');
    redirect('connexion.php');
  }
}
