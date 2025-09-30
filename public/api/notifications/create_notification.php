<?php
require_once __DIR__ . '/../../../config/config.php';

/**
 * Fonction pour créer une notification
 *
 * @param int $user_id ID de l'utilisateur qui recevra la notification
 * @param string $type Type de notification (order, message, system, etc.)
 * @param string $title Titre de la notification
 * @param string $message Contenu de la notification
 * @param string|null $action_url URL d'action (optionnel)
 * @param array|null $metadata Données supplémentaires (optionnel)
 */
function createNotification($user_id, $type, $title, $message, $action_url = null, $metadata = null) {
    global $pdo;

    try {
        // Vérifier d'abord si la table existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
        if ($stmt->rowCount() == 0) {
            // Table n'existe pas, on ne peut pas créer la notification
            error_log("Table notifications n'existe pas, notification ignorée");
            return false;
        }

        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, action_url, metadata, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $user_id,
            $type,
            $title,
            $message,
            $action_url,
            $metadata ? json_encode($metadata) : null
        ]);

        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Erreur création notification: " . $e->getMessage());
        return false;
    }
}

// Si appelé directement via HTTP (pour tests ou usage externe)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename($_SERVER['SCRIPT_NAME']) === 'create_notification.php') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    header('Content-Type: application/json');

    // Vérifier si l'utilisateur est admin
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Non autorisé - Admin requis']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $required = ['user_id', 'type', 'title', 'message'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Champ requis: $field"]);
            exit;
        }
    }

    $notification_id = createNotification(
        $data['user_id'],
        $data['type'],
        $data['title'],
        $data['message'],
        $data['action_url'] ?? null,
        $data['metadata'] ?? null
    );

    if ($notification_id) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification créée',
            'notification_id' => $notification_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la création']);
    }
}
?>