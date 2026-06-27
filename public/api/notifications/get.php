<?php
require_once __DIR__ . '/../../../config/Config.php';
require_once __DIR__ . '/../../../includes/NotificationService.php';

header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté
requireAuth();

$user = getCurrentUser();
$pdo = getDBConnection();
$notificationService = new NotificationService($pdo);

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        // Récupérer toutes les notifications
        $onlyUnread = isset($_GET['unread']) && $_GET['unread'] === 'true';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

        $notifications = $notificationService->getUserNotifications($user['id'], $onlyUnread, $limit);
        $unreadCount = $notificationService->countUnread($user['id']);

        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
        break;

    case 'count':
        // Compter les notifications non lues
        $count = $notificationService->countUnread($user['id']);

        echo json_encode([
            'success' => true,
            'count' => $count
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}
?>
