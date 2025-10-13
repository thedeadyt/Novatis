<?php
require_once __DIR__ . '/../../../config/config.php';

header('Content-Type: application/json');

try {
    // Récupérer toutes les catégories avec leurs services
    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.name,
            c.slug,
            c.icon,
            COUNT(s.id) as service_count
        FROM categories c
        LEFT JOIN services s ON c.id = s.category_id AND s.status = 'active'
        GROUP BY c.id, c.name, c.slug, c.icon
        ORDER BY c.name
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque catégorie, récupérer ses services actifs
    foreach ($categories as &$category) {
        $stmt = $pdo->prepare("
            SELECT
                s.id,
                s.user_id,
                s.title,
                s.description,
                s.price,
                s.delivery_days,
                s.image,
                s.rating,
                s.orders_count,
                CONCAT(COALESCE(u.firstname, ''), ' ', COALESCE(u.lastname, '')) as provider_name,
                u.pseudo as provider_pseudo
            FROM services s
            INNER JOIN users u ON s.user_id = u.id
            WHERE s.category_id = ? AND s.status = 'active'
            ORDER BY s.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$category['id']]);
        $category['services'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['success' => true, 'categories' => $categories]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
