<?php
require_once __DIR__ . '/includes/bdd.php';
require_once __DIR__ . '/includes/fonctions.php';
require_once __DIR__ . '/includes/authentification.php';
require_once __DIR__ . '/base_donnees/bdd.php';


session_start();
auth_logout($pdo);
flash('success', 'Tu es déconnecté.');
redirect('index.php');
