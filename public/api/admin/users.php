<?php
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Accès administrateur requis']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            // Récupérer tous les utilisateurs avec leurs statistiques
            $stmt = $pdo->prepare("
                SELECT u.*,
                       (SELECT COUNT(*) FROM orders WHERE seller_id = u.id) as sales_count,
                       (SELECT COUNT(*) FROM orders WHERE buyer_id = u.id) as purchases_count,
                       (SELECT COALESCE(SUM(price), 0) FROM orders WHERE seller_id = u.id AND status = 'completed') as total_earnings,
                       (SELECT AVG(rating) FROM reviews WHERE reviewee_id = u.id) as avg_rating
                FROM users u
                ORDER BY u.created_at DESC
            ");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'users' => $users]);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['user_id'])) {
                $user_id = $data['user_id'];
                $updates = [];
                $params = [];

                // Champs modifiables par l'admin
                if (isset($data['role'])) {
                    $updates[] = 'role = ?';
                    $params[] = $data['role'];
                }

                if (isset($data['rating'])) {
                    $updates[] = 'rating = ?';
                    $params[] = $data['rating'];
                }

                if (isset($data['is_verified'])) {
                    $updates[] = 'is_verified = ?';
                    $params[] = $data['is_verified'];
                }

                if (empty($updates)) {
                    echo json_encode(['success' => false, 'error' => 'Aucune modification fournie']);
                    exit;
                }

                $params[] = $user_id;
                $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour']);
            } else {
                echo json_encode(['success' => false, 'error' => 'ID utilisateur manquant']);
            }
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['user_id'])) {
                $user_id = $data['user_id'];

                // Empêcher la suppression de son propre compte
                if ($user_id == $_SESSION['user']['id']) {
                    echo json_encode(['success' => false, 'error' => 'Impossible de supprimer votre propre compte']);
                    exit;
                }

                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);

                echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé']);
            } else {
                echo json_encode(['success' => false, 'error' => 'ID utilisateur manquant']);
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