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
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            if (isset($_GET['conversations'])) {
                // Récupérer toutes les conversations
                $stmt = $pdo->prepare("
                    SELECT DISTINCT
                        CASE
                            WHEN o.buyer_id = ? THEN o.seller_id
                            ELSE o.buyer_id
                        END as contact_id,
                        u.name as contact_name,
                        u.avatar as contact_avatar,
                        o.id as order_id,
                        o.title as order_title,
                        (SELECT content FROM messages WHERE order_id = o.id ORDER BY created_at DESC LIMIT 1) as last_message,
                        (SELECT created_at FROM messages WHERE order_id = o.id ORDER BY created_at DESC LIMIT 1) as last_message_time,
                        (SELECT COUNT(*) FROM messages WHERE order_id = o.id AND sender_id != ? AND is_read = 0) as unread_count
                    FROM orders o
                    JOIN users u ON u.id = CASE WHEN o.buyer_id = ? THEN o.seller_id ELSE o.buyer_id END
                    WHERE o.buyer_id = ? OR o.seller_id = ?
                    ORDER BY last_message_time DESC
                ");
                $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
                $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(['success' => true, 'conversations' => $conversations]);

            } elseif (isset($_GET['order_id'])) {
                // Récupérer les messages d'une commande
                $order_id = $_GET['order_id'];

                // Vérifier que l'utilisateur fait partie de cette commande
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND (buyer_id = ? OR seller_id = ?)");
                $stmt->execute([$order_id, $user_id, $user_id]);
                $order = $stmt->fetch();

                if (!$order) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'Accès refusé']);
                    exit;
                }

                // Récupérer les messages
                $stmt = $pdo->prepare("
                    SELECT m.*, u.name as sender_name, u.avatar as sender_avatar
                    FROM messages m
                    JOIN users u ON u.id = m.sender_id
                    WHERE m.order_id = ?
                    ORDER BY m.created_at ASC
                ");
                $stmt->execute([$order_id]);
                $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Marquer les messages comme lus
                $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE order_id = ? AND sender_id != ?");
                $stmt->execute([$order_id, $user_id]);

                echo json_encode(['success' => true, 'messages' => $messages, 'order' => $order]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['order_id']) && isset($data['content'])) {
                $order_id = $data['order_id'];
                $content = trim($data['content']);

                if (empty($content)) {
                    echo json_encode(['success' => false, 'error' => 'Message vide']);
                    exit;
                }

                // Vérifier que l'utilisateur fait partie de cette commande
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND (buyer_id = ? OR seller_id = ?)");
                $stmt->execute([$order_id, $user_id, $user_id]);
                $order = $stmt->fetch();

                if (!$order) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'Accès refusé']);
                    exit;
                }

                // Insérer le message
                $stmt = $pdo->prepare("INSERT INTO messages (order_id, sender_id, content) VALUES (?, ?, ?)");
                $stmt->execute([$order_id, $user_id, $content]);

                echo json_encode(['success' => true, 'message' => 'Message envoyé']);
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