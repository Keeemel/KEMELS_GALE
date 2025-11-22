<?php
session_start();
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/bdd.php';
require_once __DIR__ . '/../includes/fonctions.php';
require_once __DIR__ . '/../includes/authentification.php';

// reste du code...


if (!is_logged()) redirect('../connexion.php');

// Vérification CSRF
if (!csrf_verify($_GET['csrf'] ?? '')) {
    flash('success', 'Session expirée. Réessaie.');
    redirect('mes_eoliennes.php');
}

$id = intval($_GET['id'] ?? 0);
$uid = $_SESSION['user_id'];

$stmt = $pdo->prepare("DELETE FROM eolienne WHERE id=:id AND gerant_id=:uid");
$stmt->execute([':id'=>$id, ':uid'=>$uid]);

flash('success', 'Éolienne supprimée avec succès.');
header('Location: mes_eoliennes.php');
exit;
