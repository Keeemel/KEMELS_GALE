<?php
// API pour gérer les éoliennes
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/bdd.php';
require_once __DIR__ . '/../includes/fonctions.php';
require_once __DIR__ . '/../includes/authentification.php';

// Vérifier que la requête est bien en JSON
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Récupérer toutes les éoliennes ou une éolienne spécifique
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("
                    SELECT id, site_id, identifiant, latitude, longitude, 
                           capacite_kw, etat, energie_t_kw, gerant_id 
                    FROM eolienne 
                    WHERE id = :id
                ");
                $stmt->execute([':id' => intval($_GET['id'])]);
                $eolienne = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $eolienne]);
            } else {
                $stmt = $pdo->query("
                    SELECT id, site_id, identifiant, latitude, longitude, 
                           capacite_kw, etat, energie_t_kw, gerant_id 
                    FROM eolienne 
                    ORDER BY id DESC
                ");
                $eoliennes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $eoliennes]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
