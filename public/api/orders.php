<?php
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$is_admin = $_SESSION['user']['role'] === 'admin';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            if ($is_admin) {
                // Admin voit toutes les commandes
                $stmt = $pdo->prepare("
                    SELECT o.*,
                           s.title as service_title,
                           buyer.name as buyer_name,
                           seller.name as seller_name,
                           buyer.avatar as buyer_avatar,
                           seller.avatar as seller_avatar
                    FROM orders o
                    JOIN services s ON s.id = o.service_id
                    JOIN users buyer ON buyer.id = o.buyer_id
                    JOIN users seller ON seller.id = o.seller_id
                    ORDER BY o.created_at DESC
                ");
                $stmt->execute();
            } else {
                // Utilisateur voit ses commandes (achetées et vendues)
                $stmt = $pdo->prepare("
                    SELECT o.*,
                           s.title as service_title,
                           buyer.name as buyer_name,
                           seller.name as seller_name,
                           buyer.avatar as buyer_avatar,
                           seller.avatar as seller_avatar,
                           CASE WHEN o.buyer_id = ? THEN 'buyer' ELSE 'seller' END as user_role
                    FROM orders o
                    JOIN services s ON s.id = o.service_id
                    JOIN users buyer ON buyer.id = o.buyer_id
                    JOIN users seller ON seller.id = o.seller_id
                    WHERE o.buyer_id = ? OR o.seller_id = ?
                    ORDER BY o.created_at DESC
                ");
                $stmt->execute([$user_id, $user_id, $user_id]);
            }

            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'orders' => $orders]);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['order_id']) && isset($data['status'])) {
                $order_id = $data['order_id'];
                $new_status = $data['status'];

                // Vérifier que l'utilisateur peut modifier cette commande
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND (seller_id = ? OR ? = 1)");
                $stmt->execute([$order_id, $user_id, $is_admin ? 1 : 0]);
                $order = $stmt->fetch();

                if (!$order) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'Accès refusé']);
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $order_id]);

                echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
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