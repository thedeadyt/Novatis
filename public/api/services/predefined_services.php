<?php
require_once __DIR__ . '/../../../config/Config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Récupérer tous les services prédéfinis avec leur catégorie
        $stmt = $pdo->prepare("
            SELECT ps.id, ps.name, ps.description, ps.category_id, c.name as category_name
            FROM predefined_services ps
            LEFT JOIN categories c ON c.id = ps.category_id
            ORDER BY c.name, ps.name
        ");
        $stmt->execute();
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Grouper par catégorie
        $grouped = [];
        foreach ($services as $service) {
            $categoryName = $service['category_name'] ?? 'Sans catégorie';
            if (!isset($grouped[$categoryName])) {
                $grouped[$categoryName] = [];
            }
            $grouped[$categoryName][] = $service;
        }

        echo json_encode([
            'success' => true,
            'services' => $services,
            'grouped' => $grouped
        ]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
