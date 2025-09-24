<?php
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$is_admin = $_SESSION['user']['role'] === 'admin';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            // Récupérer toutes les catégories
            $stmt = $pdo->prepare("SELECT id, name, slug, icon, created_at FROM categories ORDER BY name");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'categories' => $categories]);
            break;

        case 'POST':
            // Seuls les admins peuvent créer des catégories
            if (!$is_admin) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Accès refusé - Admin requis']);
                exit;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                $data = $_POST;
            }

            $name = trim($data['name'] ?? '');
            $icon = trim($data['icon'] ?? '');

            if (empty($name)) {
                echo json_encode(['success' => false, 'error' => 'Le nom de la catégorie est requis']);
                exit;
            }

            // Créer le slug à partir du nom
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

            // Vérifier si le nom ou le slug existe déjà
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? OR slug = ?");
            $stmt->execute([$name, $slug]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Cette catégorie existe déjà']);
                exit;
            }

            // Créer la catégorie
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, icon, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$name, $slug, $icon]);

            $category_id = $pdo->lastInsertId();

            echo json_encode([
                'success' => true,
                'message' => 'Catégorie créée avec succès',
                'category_id' => $category_id
            ]);
            break;

        case 'PUT':
            // Seuls les admins peuvent modifier des catégories
            if (!$is_admin) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Accès refusé - Admin requis']);
                exit;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID de la catégorie manquant']);
                exit;
            }

            $category_id = $data['id'];
            $name = trim($data['name'] ?? '');
            $icon = trim($data['icon'] ?? '');

            if (empty($name)) {
                echo json_encode(['success' => false, 'error' => 'Le nom de la catégorie est requis']);
                exit;
            }

            // Vérifier que la catégorie existe
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$category_id]);
            $category = $stmt->fetch();

            if (!$category) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Catégorie non trouvée']);
                exit;
            }

            // Créer le nouveau slug
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

            // Vérifier si le nouveau nom ou slug existe déjà (sauf pour la catégorie actuelle)
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE (name = ? OR slug = ?) AND id != ?");
            $stmt->execute([$name, $slug, $category_id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Ce nom de catégorie est déjà utilisé']);
                exit;
            }

            // Mettre à jour la catégorie
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, icon = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $icon, $category_id]);

            echo json_encode(['success' => true, 'message' => 'Catégorie mise à jour avec succès']);
            break;

        case 'DELETE':
            // Seuls les admins peuvent supprimer des catégories
            if (!$is_admin) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Accès refusé - Admin requis']);
                exit;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID de la catégorie manquant']);
                exit;
            }

            $category_id = $data['id'];

            // Vérifier que la catégorie existe
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$category_id]);
            $category = $stmt->fetch();

            if (!$category) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Catégorie non trouvée']);
                exit;
            }

            // Vérifier si la catégorie est utilisée
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM (
                    SELECT category_id FROM services WHERE category_id = ?
                    UNION ALL
                    SELECT category_id FROM portfolio WHERE category_id = ?
                ) as usage
            ");
            $stmt->execute([$category_id, $category_id]);
            $usage = $stmt->fetch();

            if ($usage['count'] > 0) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Impossible de supprimer cette catégorie car elle est utilisée par des services ou projets'
                ]);
                exit;
            }

            // Supprimer la catégorie
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$category_id]);

            echo json_encode(['success' => true, 'message' => 'Catégorie supprimée avec succès']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>