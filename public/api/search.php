<?php
require_once __DIR__ . '/../../config/Config.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$pdo = getDBConnection();

// Rechercher dans les services ET les prestataires
$sql = "
    SELECT DISTINCT
        s.id AS service_id,
        s.title AS service_title,
        s.description AS service_description,
        s.price,
        u.id AS user_id,
        u.pseudo,
        u.firstname,
        u.lastname,
        u.avatar,
        u.rating,
        c.name AS category_name,
        'service' AS result_type
    FROM services s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN categories c ON s.category_id = c.id
    WHERE s.status = 'active' AND (
        LOWER(s.title) LIKE LOWER(?)
        OR LOWER(s.description) LIKE LOWER(?)
        OR LOWER(u.pseudo) LIKE LOWER(?)
        OR LOWER(u.firstname) LIKE LOWER(?)
        OR LOWER(u.lastname) LIKE LOWER(?)
        OR LOWER(c.name) LIKE LOWER(?)
    )
    ORDER BY s.created_at DESC
    LIMIT 4
";

try {
    $stmt = $pdo->prepare($sql);
    $searchTerm = '%' . $query . '%';
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater les résultats
    $formatted = [];
    foreach ($results as $result) {
        $provider_name = trim($result['firstname'] . ' ' . $result['lastname']);
        if (empty($provider_name)) {
            $provider_name = $result['pseudo'];
        }
        
        $formatted[] = [
            'id' => $result['service_id'],
            'title' => $result['service_title'],
            'description' => substr($result['service_description'], 0, 60) . '...',
            'price' => $result['price'],
            'provider_id' => $result['user_id'],
            'provider_name' => $provider_name,
            'avatar' => $result['avatar'],
            'rating' => $result['rating'],
            'category' => $result['category_name']
        ];
    }
    
    echo json_encode(['results' => $formatted]);
} catch (PDOException $e) {
    echo json_encode(['results' => [], 'error' => $e->getMessage()]);
}
?>
