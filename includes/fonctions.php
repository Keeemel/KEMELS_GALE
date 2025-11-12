<?php
// Échapper le HTML
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// Redirection
function redirect(string $path){
  header('Location: ' . $path);
  exit;
}

// CSRF simple (stocké en session)
function csrf_token(): string {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}
function csrf_verify(string $token): bool {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

// Flash messages
function flash(string $key, ?string $msg = null){
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  if ($msg !== null){
    $_SESSION['flash'][$key] = $msg;
    return;
  }
  if (!empty($_SESSION['flash'][$key])){
    $m = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $m;
  }
  return null;
}

// Génération de token + hash
function random_token(int $len = 32): string { return bin2hex(random_bytes($len)); }
function token_hash(string $t): string { return hash('sha256', $t); }

// Détecte si l’utilisateur est connecté
function current_user_id(): ?int {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  return $_SESSION['user_id'] ?? null;
}
function is_logged(): bool { return current_user_id() !== null; }
