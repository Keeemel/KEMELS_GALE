<?php
// API pour récupérer les statistiques du parc éolien
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/bdd.php';
require_once __DIR__ . '/../includes/fonctions.php';
require_once __DIR__ . '/../includes/authentification.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Statistiques générales
        $stats = [];

        // Nombre total d'éoliennes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM eolienne");
        $stats['total_eoliennes'] = $stmt->fetchColumn();

        // Capacité totale
        $stmt = $pdo->query("SELECT SUM(capacite_kw) as capacite_totale FROM eolienne");
        $stats['capacite_totale_kw'] = $stmt->fetchColumn();

        // Énergie totale produite
        $stmt = $pdo->query("SELECT SUM(energie_t_kw) as energie_totale FROM eolienne");
        $stats['energie_totale_kw'] = $stmt->fetchColumn();

        // Répartition par état
        $stmt = $pdo->query("
            SELECT etat, COUNT(*) as count 
            FROM eolienne 
            GROUP BY etat
        ");
        $stats['repartition_etats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Nombre de gérants
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM user WHERE role IN ('gerant', 'admin')");
        $stats['total_gerants'] = $stmt->fetchColumn();

        echo json_encode(['success' => true, 'data' => $stats]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
