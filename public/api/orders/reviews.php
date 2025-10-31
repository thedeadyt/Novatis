<?php
require_once __DIR__ . '/../../../config/Config.php';
require_once __DIR__ . '/../notifications/create_notification.php';

requireAuth();
$pdo = getDBConnection();

header('Content-Type: application/json');

$user = getCurrentUser();
$user_id = $user['id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            if (isset($_GET['order_id'])) {
                // Récupérer l'évaluation d'une commande spécifique
                $order_id = $_GET['order_id'];

                $stmt = $pdo->prepare("
                    SELECT r.*, reviewer.name as reviewer_name, reviewee.name as reviewee_name
                    FROM reviews r
                    JOIN users reviewer ON reviewer.id = r.reviewer_id
                    JOIN users reviewee ON reviewee.id = r.reviewee_id
                    WHERE r.order_id = ?
                ");
                $stmt->execute([$order_id]);
                $review = $stmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode(['success' => true, 'review' => $review]);
            } else {
                // Récupérer toutes les évaluations pour l'utilisateur
                $stmt = $pdo->prepare("
                    SELECT r.*,
                           reviewer.name as reviewer_name,
                           reviewee.name as reviewee_name,
                           o.title as order_title,
                           s.title as service_title
                    FROM reviews r
                    JOIN users reviewer ON reviewer.id = r.reviewer_id
                    JOIN users reviewee ON reviewee.id = r.reviewee_id
                    JOIN orders o ON o.id = r.order_id
                    JOIN services s ON s.id = o.service_id
                    WHERE r.reviewee_id = ? OR r.reviewer_id = ?
                    ORDER BY r.created_at DESC
                ");
                $stmt->execute([$user_id, $user_id]);
                $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(['success' => true, 'reviews' => $reviews]);
            }
            break;

        case 'POST':
            // Créer une nouvelle évaluation
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['order_id']) || !isset($data['rating'])) {
                throw new Exception('Données manquantes (order_id, rating requis)');
            }

            $order_id = $data['order_id'];
            $rating = intval($data['rating']);
            $comment = $data['comment'] ?? '';

            // Validation du rating
            if ($rating < 1 || $rating > 5) {
                throw new Exception('La note doit être entre 1 et 5');
            }

            // Vérifier que l'utilisateur peut évaluer cette commande
            $stmt = $pdo->prepare("
                SELECT o.*, s.user_id as seller_id
                FROM orders o
                JOIN services s ON s.id = o.service_id
                WHERE o.id = ? AND (o.buyer_id = ? OR s.user_id = ?)
            ");
            $stmt->execute([$order_id, $user_id, $user_id]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new Exception('Commande non trouvée ou accès refusé');
            }

            // Déterminer qui évalue qui
            $reviewer_id = $user_id;
            $reviewee_id = ($user_id == $order['buyer_id']) ? $order['seller_id'] : $order['buyer_id'];

            // Vérifier qu'une évaluation n'existe pas déjà
            $stmt = $pdo->prepare("SELECT id FROM reviews WHERE order_id = ? AND reviewer_id = ?");
            $stmt->execute([$order_id, $reviewer_id]);
            if ($stmt->fetch()) {
                throw new Exception('Vous avez déjà évalué cette commande');
            }

            // Créer l'évaluation
            $stmt = $pdo->prepare("
                INSERT INTO reviews (order_id, reviewer_id, reviewee_id, rating, comment)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$order_id, $reviewer_id, $reviewee_id, $rating, $comment]);

            // Mettre à jour la note moyenne de l'utilisateur évalué
            $stmt = $pdo->prepare("
                UPDATE users
                SET rating = (
                    SELECT AVG(rating)
                    FROM reviews
                    WHERE reviewee_id = ?
                )
                WHERE id = ?
            ");
            $stmt->execute([$reviewee_id, $reviewee_id]);

            // Créer une notification pour la personne évaluée
            try {
                $stmt = $pdo->prepare("SELECT CONCAT(firstname, ' ', lastname) as name FROM users WHERE id = ?");
                $stmt->execute([$reviewer_id]);
                $reviewer_name = $stmt->fetchColumn();

                createNotification(
                    $reviewee_id,
                    'review',
                    'Nouvelle évaluation',
                    "{$reviewer_name} vous a laissé une évaluation de {$rating}/5 étoiles !",
                    '/dashboard?tab=reviews',
                    ['order_id' => $order_id, 'rating' => $rating]
                );
            } catch (Exception $e) {
                error_log("Erreur notification évaluation: " . $e->getMessage());
            }

            echo json_encode(['success' => true, 'message' => 'Évaluation ajoutée avec succès']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            break;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>