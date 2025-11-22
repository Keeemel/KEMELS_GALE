<?php
// API pour récupérer les informations des gérants
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/bdd.php';
require_once __DIR__ . '/../includes/fonctions.php';
require_once __DIR__ . '/../includes/authentification.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Récupérer tous les gérants ou un gérant spécifique
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("
                    SELECT u.id, u.nom, u.prenom, u.email, u.role,
                           gp.role_texte, gp.photo_url
                    FROM user u
                    LEFT JOIN gerant_profil gp ON u.id = gp.user_id
                    WHERE u.id = :id AND u.role IN ('gerant', 'admin')
                ");
                $stmt->execute([':id' => intval($_GET['id'])]);
                $gerant = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $gerant]);
            } else {
                $stmt = $pdo->query("
                    SELECT u.id, u.nom, u.prenom, u.email, u.role,
                           gp.role_texte, gp.photo_url
                    FROM user u
                    LEFT JOIN gerant_profil gp ON u.id = gp.user_id
                    WHERE u.role IN ('gerant', 'admin')
                    ORDER BY u.nom ASC
                ");
                $gerants = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $gerants]);
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
