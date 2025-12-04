<?php
require_once __DIR__ . '/../../config/Config.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$pdo = getDBConnection();

try {
    // Récupérer toutes les catégories avec les 4 services les plus proposés
    $sql_categories = "
        SELECT 
            c.id,
            c.name,
            c.icon,
            COUNT(s.id) as services_count
        FROM categories c
        LEFT JOIN services s ON c.id = s.category_id AND s.status = 'active'
        GROUP BY c.id, c.name, c.icon
        HAVING services_count > 0
        ORDER BY services_count DESC
    ";
    
    $categories_stmt = $pdo->prepare($sql_categories);
    $categories_stmt->execute();
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result = [];
    
    foreach ($categories as $category) {
        // Services les plus proposés dans cette catégorie
        $sql_services = "
            SELECT 
                s.id,
                s.title,
                s.description,
                s.price,
                s.image,
                s.rating,
                u.id as user_id,
                u.pseudo,
                u.firstname,
                u.lastname,
                u.avatar,
                COUNT(s2.id) as similar_count
            FROM services s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN services s2 ON u.id = s2.user_id AND s2.category_id = ? AND s2.status = 'active' AND s2.id != s.id
            WHERE s.category_id = ? AND s.status = 'active'
            GROUP BY s.id
            ORDER BY similar_count DESC
            LIMIT 4
        ";
        
        $services_stmt = $pdo->prepare($sql_services);
        $services_stmt->execute([$category['id'], $category['id']]);
        $services = $services_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Derniers utilisateurs qui ont créé des services dans cette catégorie
        $sql_users = "
            SELECT DISTINCT
                u.id,
                u.pseudo,
                u.firstname,
                u.lastname,
                u.avatar,
                u.rating,
                MAX(s.created_at) as last_service_date
            FROM users u
            JOIN services s ON u.id = s.user_id
            WHERE s.category_id = ? AND s.status = 'active'
            GROUP BY u.id
            ORDER BY MAX(s.created_at) DESC
            LIMIT 4
        ";
        
        $users_stmt = $pdo->prepare($sql_users);
        $users_stmt->execute([$category['id']]);
        $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result[] = [
            'category' => $category,
            'services' => $services,
            'users' => $users
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
