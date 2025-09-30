<?php
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // D'abord, synchroniser avec les messages non lus existants
            try {
                syncUnreadMessagesAsNotifications($user_id, $pdo);
            } catch (Exception $e) {
                error_log("Erreur sync notifications: " . $e->getMessage());
                // Continue sans sync si erreur
            }

            // Récupérer les notifications
            $limit = $_GET['limit'] ?? 20;
            $unread_only = isset($_GET['unread_only']) ? (bool)$_GET['unread_only'] : false;

            // Vérifier que la table notifications existe et la créer si nécessaire
            $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
            if ($stmt->rowCount() == 0) {
                // Créer la table notifications
                try {
                    $pdo->exec("
                        CREATE TABLE notifications (
                            id              INT PRIMARY KEY AUTO_INCREMENT,
                            user_id         INT NOT NULL,
                            type            ENUM('order','message','system','payment','service','review') DEFAULT 'system',
                            title           VARCHAR(200) NOT NULL,
                            message         TEXT NOT NULL,
                            action_url      VARCHAR(500),
                            metadata        JSON,
                            is_read         TINYINT(1) DEFAULT 0,
                            created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                            INDEX idx_user_notifications (user_id, is_read, created_at),
                            INDEX idx_created_at (created_at)
                        )
                    ");
                } catch (Exception $e) {
                    error_log("Erreur création table notifications: " . $e->getMessage());
                    // Retourner des notifications vides si échec
                    echo json_encode([
                        'success' => true,
                        'notifications' => [],
                        'unread_count' => 0
                    ]);
                    break;
                }
            }

            $sql = "SELECT * FROM notifications WHERE user_id = ?";
            $params = [$user_id];

            if ($unread_only) {
                $sql .= " AND is_read = 0";
            }

            $sql .= " ORDER BY created_at DESC LIMIT " . (int)$limit;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Compter les notifications non lues
            $stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$user_id]);
            $unread_count = $stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => (int)$unread_count
            ]);
            break;

        case 'PUT':
            // Marquer une notification comme lue
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['notification_id'])) {
                $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
                $stmt->execute([$data['notification_id'], $user_id]);

                echo json_encode(['success' => true, 'message' => 'Notification marquée comme lue']);
            } elseif (isset($data['mark_all_read']) && $data['mark_all_read']) {
                // Marquer toutes les notifications comme lues
                $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
                $stmt->execute([$user_id]);

                echo json_encode(['success' => true, 'message' => 'Toutes les notifications marquées comme lues']);
            } else {
                throw new Exception('ID de notification requis');
            }
            break;

        case 'DELETE':
            // Supprimer une notification
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['notification_id'])) {
                throw new Exception('ID de notification requis');
            }

            $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $stmt->execute([$data['notification_id'], $user_id]);

            echo json_encode(['success' => true, 'message' => 'Notification supprimée']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            break;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Synchroniser les messages non lus comme notifications
 */
function syncUnreadMessagesAsNotifications($user_id, $pdo) {
    try {
        // Vérifier si la table notifications existe et la créer si nécessaire
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
            if ($stmt->rowCount() == 0) {
                // Créer la table notifications si elle n'existe pas
                $pdo->exec("
                    CREATE TABLE notifications (
                        id              INT PRIMARY KEY AUTO_INCREMENT,
                        user_id         INT NOT NULL,
                        type            ENUM('order','message','system','payment','service','review') DEFAULT 'system',
                        title           VARCHAR(200) NOT NULL,
                        message         TEXT NOT NULL,
                        action_url      VARCHAR(500),
                        metadata        JSON,
                        is_read         TINYINT(1) DEFAULT 0,
                        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                        INDEX idx_user_notifications (user_id, is_read, created_at),
                        INDEX idx_created_at (created_at)
                    )
                ");
            }
        } catch (Exception $e) {
            error_log("Erreur création table notifications: " . $e->getMessage());
            // Continue sans les notifications si la table ne peut pas être créée
            return;
        }

        // Récupérer les conversations avec des messages non lus pour cet utilisateur
        $stmt = $pdo->prepare("
            SELECT
                o.id as order_id,
                o.buyer_id,
                o.seller_id,
                s.title as service_title,
                m.sender_id,
                sender.name as sender_name,
                COUNT(m.id) as unread_count,
                MAX(m.created_at) as last_message_time
            FROM orders o
            JOIN services s ON s.id = o.service_id
            JOIN messages m ON m.order_id = o.id
            JOIN users sender ON sender.id = m.sender_id
            WHERE (o.buyer_id = ? OR o.seller_id = ?)
            AND m.sender_id != ?
            AND m.is_read = 0
            GROUP BY o.id, m.sender_id
        ");
        $stmt->execute([$user_id, $user_id, $user_id]);
        $unreadConversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($unreadConversations as $conv) {
            // Vérifier si une notification existe déjà pour cette conversation
            $stmt = $pdo->prepare("
                SELECT id FROM notifications
                WHERE user_id = ?
                AND type = 'message'
                AND JSON_EXTRACT(metadata, '$.order_id') = ?
                AND JSON_EXTRACT(metadata, '$.sender_id') = ?
                AND is_read = 0
            ");
            $stmt->execute([$user_id, $conv['order_id'], $conv['sender_id']]);

            if ($stmt->rowCount() == 0) {
                // Créer une notification pour ce message non lu
                $stmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, action_url, metadata, created_at)
                    VALUES (?, 'message', 'Nouveau message', ?, '/dashboard?tab=messages', ?, ?)
                ");

                $message = $conv['unread_count'] == 1
                    ? "Vous avez reçu un message de {$conv['sender_name']} concernant \"{$conv['service_title']}\""
                    : "Vous avez reçu {$conv['unread_count']} messages de {$conv['sender_name']} concernant \"{$conv['service_title']}\"";

                $metadata = json_encode([
                    'order_id' => $conv['order_id'],
                    'sender_id' => $conv['sender_id'],
                    'unread_count' => $conv['unread_count']
                ]);

                $stmt->execute([$user_id, $message, $metadata, $conv['last_message_time']]);
            }
        }

    } catch (Exception $e) {
        error_log("Erreur sync notifications: " . $e->getMessage());
    }
}
?>