<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/NotificationService.php';

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
            if ($is_admin) {
                // Admin voit tous les services
                $stmt = $pdo->prepare("
                    SELECT s.*,
                           CONCAT(u.firstname, ' ', u.lastname) as user_name,
                           u.pseudo,
                           c.name as category_name
                    FROM services s
                    JOIN users u ON u.id = s.user_id
                    LEFT JOIN categories c ON c.id = s.category_id
                    ORDER BY s.created_at DESC
                ");
                $stmt->execute();
            } else {
                // Utilisateur voit ses services
                $stmt = $pdo->prepare("
                    SELECT s.*, c.name as category_name
                    FROM services s
                    LEFT JOIN categories c ON c.id = s.category_id
                    WHERE s.user_id = ?
                    ORDER BY s.created_at DESC
                ");
                $stmt->execute([$user_id]);
            }

            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'services' => $services]);
            break;

        case 'POST':
            // Créer un nouveau service
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                // Fallback pour form-data
                $data = $_POST;
            }

            $title = $data['title'] ?? null;
            $description = $data['description'] ?? null;
            $category_id = $data['category_id'] ?? null;
            $price = $data['price'] ?? null;
            $delivery_days = $data['delivery_days'] ?? null;
            $image = $data['image'] ?? null;

            if (!$title || !$description || !$price || !$delivery_days) {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
                exit;
            }

            // Valider le prix
            $price = floatval($price);
            if ($price <= 0) {
                echo json_encode(['success' => false, 'error' => 'Le prix doit être supérieur à 0']);
                exit;
            }

            // Valider les jours de livraison
            $delivery_days = intval($delivery_days);
            if ($delivery_days <= 0) {
                echo json_encode(['success' => false, 'error' => 'Le délai de livraison doit être supérieur à 0']);
                exit;
            }

            // Créer le service
            $stmt = $pdo->prepare("
                INSERT INTO services (user_id, title, description, category_id, price, delivery_days, image, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([
                $user_id,
                $title,
                $description,
                $category_id ?: null,
                $price,
                $delivery_days,
                $image ?: null
            ]);

            $service_id = $pdo->lastInsertId();

            echo json_encode([
                'success' => true,
                'message' => 'Service créé avec succès',
                'service_id' => $service_id
            ]);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID du service manquant']);
                exit;
            }

            $service_id = $data['id'];

            // Vérifier que l'utilisateur peut modifier ce service
            $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND (user_id = ? OR ? = 1)");
            $stmt->execute([$service_id, $user_id, $is_admin ? 1 : 0]);
            $service = $stmt->fetch();

            if (!$service) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Accès refusé']);
                exit;
            }

            // Construire la requête de mise à jour
            $updates = [];
            $params = [];

            if (isset($data['title'])) {
                $updates[] = "title = ?";
                $params[] = $data['title'];
            }
            if (isset($data['description'])) {
                $updates[] = "description = ?";
                $params[] = $data['description'];
            }
            if (isset($data['category_id'])) {
                $updates[] = "category_id = ?";
                $params[] = $data['category_id'] ?: null;
            }
            if (isset($data['price'])) {
                $price = floatval($data['price']);
                if ($price <= 0) {
                    echo json_encode(['success' => false, 'error' => 'Le prix doit être supérieur à 0']);
                    exit;
                }
                $updates[] = "price = ?";
                $params[] = $price;
            }
            if (isset($data['delivery_days'])) {
                $delivery_days = intval($data['delivery_days']);
                if ($delivery_days <= 0) {
                    echo json_encode(['success' => false, 'error' => 'Le délai de livraison doit être supérieur à 0']);
                    exit;
                }
                $updates[] = "delivery_days = ?";
                $params[] = $delivery_days;
            }
            if (isset($data['image'])) {
                $updates[] = "image = ?";
                $params[] = $data['image'] ?: null;
            }
            if (isset($data['status'])) {
                $updates[] = "status = ?";
                $params[] = $data['status'];
            }

            if (empty($updates)) {
                echo json_encode(['success' => false, 'error' => 'Aucune donnée à mettre à jour']);
                exit;
            }

            $params[] = $service_id;
            $sql = "UPDATE services SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Notifier les clients qui ont des commandes actives pour ce service
            try {
                $notificationService = new NotificationService($pdo);

                // Récupérer les acheteurs ayant des commandes actives pour ce service
                $stmt = $pdo->prepare("
                    SELECT DISTINCT o.buyer_id
                    FROM orders o
                    WHERE o.service_id = ?
                    AND o.status IN ('pending', 'in_progress')
                    AND o.buyer_id != ?
                ");
                $stmt->execute([$service_id, $user_id]);
                $buyers = $stmt->fetchAll(PDO::FETCH_COLUMN);

                // Envoyer une notification à chaque acheteur
                foreach ($buyers as $buyer_id) {
                    $notificationService->notifyServiceUpdate(
                        $buyer_id,
                        $service['title'],
                        "Le service \"{$service['title']}\" a été mis à jour par le prestataire."
                    );
                }
            } catch (Exception $e) {
                error_log("Erreur lors de l'envoi des notifications de mise à jour: " . $e->getMessage());
            }

            echo json_encode(['success' => true, 'message' => 'Service mis à jour']);
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID du service manquant']);
                exit;
            }

            $service_id = $data['id'];

            // Vérifier que l'utilisateur peut supprimer ce service
            $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND (user_id = ? OR ? = 1)");
            $stmt->execute([$service_id, $user_id, $is_admin ? 1 : 0]);
            $service = $stmt->fetch();

            if (!$service) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Accès refusé']);
                exit;
            }

            // Supprimer le service
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$service_id]);

            echo json_encode(['success' => true, 'message' => 'Service supprimé']);
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