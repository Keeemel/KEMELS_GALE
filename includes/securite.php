<?php
// ====================================================================
// includes/securite.php — Fonctions de sécurité supplémentaires
// ====================================================================

/**
 * Nettoie et valide une entrée utilisateur
 * @param string $input Entrée à nettoyer
 * @param string $type Type de validation ('email', 'number', 'text')
 * @return string|bool Entrée nettoyée ou false si invalide
 */
function sanitize_input(string $input, string $type = 'text') {
    $input = trim($input);
    
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL);
        case 'number':
            return filter_var($input, FILTER_VALIDATE_INT);
        case 'float':
            return filter_var($input, FILTER_VALIDATE_FLOAT);
        case 'text':
        default:
            return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/**
 * Vérifie la force d'un mot de passe
 * @param string $password Mot de passe à vérifier
 * @return array ['valid' => bool, 'message' => string]
 */
function validate_password_strength(string $password): array {
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères'];
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'message' => 'Le mot de passe doit contenir au moins une majuscule'];
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'message' => 'Le mot de passe doit contenir au moins une minuscule'];
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'message' => 'Le mot de passe doit contenir au moins un chiffre'];
    }
    
    return ['valid' => true, 'message' => 'Mot de passe valide'];
}

/**
 * Limite le taux de requêtes par IP (simple throttling)
 * @param string $action Action à limiter
 * @param int $max_attempts Nombre maximal de tentatives
 * @param int $time_window Fenêtre de temps en secondes
 * @return bool True si autorisé, false si limite atteinte
 */
function rate_limit(string $action, int $max_attempts = 5, int $time_window = 300): bool {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "rate_limit_{$action}_{$ip}";
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 1, 'first_attempt' => time()];
        return true;
    }
    
    $data = $_SESSION[$key];
    $elapsed = time() - $data['first_attempt'];
    
    // Réinitialiser si la fenêtre de temps est dépassée
    if ($elapsed > $time_window) {
        $_SESSION[$key] = ['count' => 1, 'first_attempt' => time()];
        return true;
    }
    
    // Vérifier si la limite est atteinte
    if ($data['count'] >= $max_attempts) {
        return false;
    }
    
    // Incrémenter le compteur
    $_SESSION[$key]['count']++;
    return true;
}
