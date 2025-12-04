<?php
require_once __DIR__ . '/../../../config/Config.php';

requireAuth();
$pdo = getDBConnection();

header('Content-Type: application/json');

$user = getCurrentUser();
$user_id = $user['id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            // Récupérer le portfolio de l'utilisateur
            $stmt = $pdo->prepare("
                SELECT p.*,
                       c.name as category_name
                FROM portfolio p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE p.user_id = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $portfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'portfolio' => $portfolio]);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['title']) && isset($data['description'])) {
                $title = trim($data['title']);
                $description = trim($data['description']);
                $category_id = $data['category_id'] ?? null;
                $image = $data['image'] ?? null;

                if (empty($title) || empty($description)) {
                    echo json_encode(['success' => false, 'error' => 'Titre et description requis']);
                    exit;
                }

                $stmt = $pdo->prepare("INSERT INTO portfolio (user_id, title, description, category_id, image) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $title, $description, $category_id, $image]);

                echo json_encode(['success' => true, 'message' => 'Projet ajouté au portfolio']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['id']) && isset($data['title']) && isset($data['description'])) {
                $id = $data['id'];
                $title = trim($data['title']);
                $description = trim($data['description']);
                $category_id = $data['category_id'] ?? null;
                $image = $data['image'] ?? null;

                // Vérifier que l'utilisateur possède ce projet
                $stmt = $pdo->prepare("SELECT * FROM portfolio WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $user_id]);
                $project = $stmt->fetch();

                if (!$project) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'Accès refusé']);
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE portfolio SET title = ?, description = ?, category_id = ?, image = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$title, $description, $category_id, $image, $id, $user_id]);

                echo json_encode(['success' => true, 'message' => 'Projet mis à jour']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
            }
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['id'])) {
                $id = $data['id'];

                // Vérifier que l'utilisateur possède ce projet
                $stmt = $pdo->prepare("SELECT * FROM portfolio WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $user_id]);
                $project = $stmt->fetch();

                if (!$project) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'Accès refusé']);
                    exit;
                }

                $stmt = $pdo->prepare("DELETE FROM portfolio WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $user_id]);

                echo json_encode(['success' => true, 'message' => 'Projet supprimé']);
            } else {
                echo json_encode(['success' => false, 'error' => 'ID manquant']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>