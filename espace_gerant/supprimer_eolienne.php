<?php

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../base_donnees/bdd.php';
require_once __DIR__ . '/../includes/fonctions.php';
require_once __DIR__ . '/../includes/authentification.php';

// reste du code...


if (!is_logged()) redirect('login.php');

$id = intval($_GET['id'] ?? 0);
$uid = $_SESSION['user_id'];

$stmt = $pdo->prepare("DELETE FROM eolienne WHERE id=:id AND gerant_id=:uid");
$stmt->execute([':id'=>$id, ':uid'=>$uid]);

header('Location: mes_eoliennes.php');
exit;
