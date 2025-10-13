<?php
require_once __DIR__ . '/../../../config/config.php';

header('Content-Type: application/json');

try {
    // Récupérer les 4 prestataires les plus récents par catégorie
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            c.id as category_id,
            c.name as category_name,
            c.slug as category_slug,
            c.icon as category_icon
        FROM categories c
        ORDER BY c.name
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];

    foreach ($categories as $category) {
        // Pour chaque catégorie, trouver le dernier prestataire (le plus récent)
        // qui a des services actifs dans cette catégorie
        $stmt = $pdo->prepare("
            SELECT DISTINCT
                u.id,
                CONCAT(COALESCE(u.firstname, ''), ' ', COALESCE(u.lastname, '')) as name,
                u.pseudo,
                u.avatar,
                u.bio,
                u.rating,
                u.created_at,
                COUNT(DISTINCT s.id) as service_count
            FROM users u
            INNER JOIN services s ON u.id = s.user_id
            WHERE s.category_id = ? AND u.account_status = 'active'
            GROUP BY u.id
            ORDER BY u.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$category['category_id']]);
        $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($providers)) {
            $result[] = [
                'category' => [
                    'id' => $category['category_id'],
                    'name' => $category['category_name'],
                    'slug' => $category['category_slug'],
                    'icon' => $category['category_icon']
                ],
                'providers' => $providers
            ];
        }
    }

    echo json_encode(['success' => true, 'data' => $result]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
