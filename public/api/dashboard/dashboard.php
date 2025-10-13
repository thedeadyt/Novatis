<?php
require_once __DIR__ . '/../../../config/config.php';

requireAuth();
$pdo = getDBConnection();

header('Content-Type: application/json');

$user = getCurrentUser();
$user_id = $user['id'];
$is_admin = $user['role'] === 'admin';

try {
    // Statistiques utilisateur
    $stats = [];

    if ($is_admin) {
        // Stats admin
        $stmt = $pdo->prepare("
            SELECT
                (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
                (SELECT COUNT(*) FROM orders) as total_orders,
                (SELECT COUNT(*) FROM services WHERE status = 'active') as active_services,
                (SELECT COUNT(*) FROM support_tickets WHERE status IN ('open', 'in_progress')) as open_tickets
        ");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Stats utilisateur
        $stmt = $pdo->prepare("
            SELECT
                (SELECT COALESCE(SUM(price), 0) FROM orders WHERE seller_id = ? AND status = 'completed') as earnings,
                (SELECT COUNT(*) FROM orders WHERE seller_id = ?) as sales,
                (SELECT COUNT(*) FROM orders WHERE buyer_id = ?) as purchases,
                (SELECT rating FROM users WHERE id = ?) as rating,
                (SELECT COUNT(*) FROM services WHERE user_id = ? AND status = 'active') as active_services
        ");
        $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Services récents
    if ($is_admin) {
        $stmt = $pdo->prepare("
            SELECT s.*,
                   COALESCE(NULLIF(CONCAT(u.firstname, ' ', u.lastname), ' '), u.pseudo) as user_name,
                   c.name as category_name
            FROM services s
            JOIN users u ON u.id = s.user_id
            LEFT JOIN categories c ON c.id = s.category_id
            ORDER BY s.created_at DESC
            LIMIT 5
        ");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("
            SELECT s.*, c.name as category_name
            FROM services s
            LEFT JOIN categories c ON c.id = s.category_id
            WHERE s.user_id = ?
            ORDER BY s.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
    }
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Commandes récentes
    if ($is_admin) {
        $stmt = $pdo->prepare("
            SELECT o.*,
                   s.title as service_title,
                   COALESCE(NULLIF(CONCAT(buyer.firstname, ' ', buyer.lastname), ' '), buyer.pseudo) as buyer_name,
                   COALESCE(NULLIF(CONCAT(seller.firstname, ' ', seller.lastname), ' '), seller.pseudo) as seller_name
            FROM orders o
            JOIN services s ON s.id = o.service_id
            JOIN users buyer ON buyer.id = o.buyer_id
            JOIN users seller ON seller.id = o.seller_id
            ORDER BY o.created_at DESC
            LIMIT 5
        ");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("
            SELECT o.*,
                   s.title as service_title,
                   COALESCE(NULLIF(CONCAT(buyer.firstname, ' ', buyer.lastname), ' '), buyer.pseudo) as buyer_name,
                   COALESCE(NULLIF(CONCAT(seller.firstname, ' ', seller.lastname), ' '), seller.pseudo) as seller_name,
                   CASE WHEN o.buyer_id = ? THEN 'buyer' ELSE 'seller' END as user_role
            FROM orders o
            JOIN services s ON s.id = o.service_id
            JOIN users buyer ON buyer.id = o.buyer_id
            JOIN users seller ON seller.id = o.seller_id
            WHERE o.buyer_id = ? OR o.seller_id = ?
            ORDER BY o.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id, $user_id, $user_id]);
    }
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Messages non lus
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as unread_count
        FROM messages m
        JOIN orders o ON o.id = m.order_id
        WHERE (o.buyer_id = ? OR o.seller_id = ?)
        AND m.sender_id != ?
        AND m.is_read = 0
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $unread_messages = $stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

    // Anciens clients (utilisateurs ayant commandé mes services)
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.id,
               COALESCE(NULLIF(CONCAT(u.firstname, ' ', u.lastname), ' '), u.pseudo) as name,
               u.email,
               u.avatar,
               COUNT(o.id) as order_count,
               MAX(o.created_at) as last_order
        FROM users u
        JOIN orders o ON o.buyer_id = u.id
        JOIN services s ON s.id = o.service_id
        WHERE s.user_id = ?
        GROUP BY u.id, u.firstname, u.lastname, u.pseudo, u.email, u.avatar
        ORDER BY last_order DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $former_clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Portfolio
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name
        FROM portfolio p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
        LIMIT 6
    ");
    $stmt->execute([$user_id]);
    $portfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Catégories pour les formulaires
    $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'stats' => $stats,
            'services' => $services,
            'orders' => $orders,
            'unread_messages' => $unread_messages,
            'former_clients' => $former_clients,
            'portfolio' => $portfolio,
            'categories' => $categories
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>