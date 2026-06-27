<?php
require_once __DIR__ . '/../../../config/Config.php';
require_once __DIR__ . '/../../../includes/NotificationService.php';

requireAuth();
$pdo = getDBConnection();

header('Content-Type: application/json');

$user = getCurrentUser();
$notificationService = new NotificationService($pdo);
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'mark_read':
        // Marquer une notification comme lue
        $notificationId = $input['notification_id'] ?? null;

        if (!$notificationId) {
            echo json_encode(['success' => false, 'message' => 'ID de notification manquant']);
            break;
        }

        $success = $notificationService->markAsRead($notificationId, $user['id']);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Notification marquée comme lue' : 'Erreur'
        ]);
        break;

    case 'mark_all_read':
        // Marquer toutes les notifications comme lues
        $success = $notificationService->markAllAsRead($user['id']);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Toutes les notifications sont marquées comme lues' : 'Erreur'
        ]);
        break;

    case 'delete':
        // Supprimer une notification
        $notificationId = $input['notification_id'] ?? null;

        if (!$notificationId) {
            echo json_encode(['success' => false, 'message' => 'ID de notification manquant']);
            break;
        }

        $success = $notificationService->delete($notificationId, $user['id']);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Notification supprimée' : 'Erreur'
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}
?>
