<?php
session_start();
require_once __DIR__ . '/includes/bdd.php';
require_once __DIR__ . '/includes/fonctions.php';
require_once __DIR__ . '/includes/authentification.php';


auth_logout($pdo);
flash('success', 'Tu es déconnecté.');
redirect('index.php');
