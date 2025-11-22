<?php
// ====================================================================
//  BDD.PHP — connexion + initialisation automatique SQLite
// ====================================================================

// Emplacement de la base de données
$db_path = __DIR__ . '/../base_donnees/kemel_gale.sqlite';

// Crée le dossier si absent
if (!is_dir(dirname($db_path))) {
    mkdir(dirname($db_path), 0777, true);
}

// Connexion PDO
try {
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur connexion SQLite : ' . $e->getMessage());
}

// ====================================================================
// Création des tables au premier lancement
// ====================================================================

$pdo->exec("
CREATE TABLE IF NOT EXISTS user (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT,
    prenom TEXT,
    email TEXT UNIQUE NOT NULL,
    pass_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('admin','gerant','internaute')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS auth_token (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token_hash TEXT NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY(user_id) REFERENCES user(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS site (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    latitude REAL,
    longitude REAL,
    adresse TEXT
);

CREATE TABLE IF NOT EXISTS eolienne (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    site_id INTEGER,
    identifiant TEXT UNIQUE,
    latitude REAL,
    longitude REAL,
    capacite_kw REAL,
    etat TEXT DEFAULT 'OK' CHECK(etat IN ('OK','STOP','ALERTE','MAINT')),
    energie_t_kw REAL DEFAULT 0,
    gerant_id INTEGER,
    FOREIGN KEY(site_id) REFERENCES site(id) ON DELETE SET NULL,
    FOREIGN KEY(gerant_id) REFERENCES user(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS gerant_profil (
    user_id INTEGER PRIMARY KEY,
    prenom TEXT,
    nom TEXT,
    role_texte TEXT,
    photo_url TEXT,
    FOREIGN KEY(user_id) REFERENCES user(id) ON DELETE CASCADE
);
");

// ====================================================================
// Insère un admin par défaut si la base est vide
// ====================================================================
$check = $pdo->query("SELECT COUNT(*) AS c FROM user")->fetchColumn();
if ($check == 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO user (nom, prenom, email, pass_hash, role)
                   VALUES ('Admin', 'Site', 'admin@kemelgale.fr', :p, 'admin')")
        ->execute([':p' => $hash]);
    
    // Message de debug (à désactiver en production)
    if (getenv('DEBUG_MODE') === 'true') {
        echo '<p style="color:lime;font-family:monospace">✅ Base initialisée avec succès.<br>
              Compte admin : <b>admin@kemelgale.fr</b><br>
              Mot de passe : <b>admin123</b></p>';
    }
}
?>
