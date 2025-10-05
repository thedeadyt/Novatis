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
                // Admin voit toutes les commandes
                $stmt = $pdo->prepare("
                    SELECT o.*,
                           s.title as service_title,
                           COALESCE(NULLIF(CONCAT(buyer.firstname, ' ', buyer.lastname), ' '), buyer.pseudo) as buyer_name,
                           COALESCE(NULLIF(CONCAT(seller.firstname, ' ', seller.lastname), ' '), seller.pseudo) as seller_name,
                           buyer.avatar as buyer_avatar,
                           seller.avatar as seller_avatar
                    FROM orders o
                    JOIN services s ON s.id = o.service_id
                    JOIN users buyer ON buyer.id = o.buyer_id
                    JOIN users seller ON seller.id = o.seller_id
                    ORDER BY o.created_at DESC
                ");
                $stmt->execute();
            } else {
                // Utilisateur voit ses commandes (achetées et vendues)
                $stmt = $pdo->prepare("
                    SELECT o.*,
                           s.title as service_title,
                           COALESCE(NULLIF(CONCAT(buyer.firstname, ' ', buyer.lastname), ' '), buyer.pseudo) as buyer_name,
                           COALESCE(NULLIF(CONCAT(seller.firstname, ' ', seller.lastname), ' '), seller.pseudo) as seller_name,
                           buyer.avatar as buyer_avatar,
                           seller.avatar as seller_avatar,
                           CASE WHEN o.buyer_id = ? THEN 'buyer' ELSE 'seller' END as user_role
                    FROM orders o
                    JOIN services s ON s.id = o.service_id
                    JOIN users buyer ON buyer.id = o.buyer_id
                    JOIN users seller ON seller.id = o.seller_id
                    WHERE o.buyer_id = ? OR o.seller_id = ?
                    ORDER BY o.created_at DESC
                ");
                $stmt->execute([$user_id, $user_id, $user_id]);
            }

            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'orders' => $orders]);
            break;

        case 'POST':
            // Créer une nouvelle commande
            $service_id = $_POST['service_id'] ?? null;
            $seller_id = $_POST['seller_id'] ?? null;
            $buyer_id = $_POST['buyer_id'] ?? null;
            $price = $_POST['price'] ?? null;
            $description = $_POST['description'] ?? '';
            $message = $_POST['message'] ?? '';

            if (!$service_id || !$seller_id || !$buyer_id || !$price) {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
                exit;
            }

            // Vérifier que l'utilisateur connecté est bien l'acheteur
            if ($buyer_id != $user_id) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Accès refusé']);
                exit;
            }

            // Vérifier que le service existe et est actif
            $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND status = 'active'");
            $stmt->execute([$service_id]);
            $service = $stmt->fetch();

            if (!$service) {
                echo json_encode(['success' => false, 'error' => 'Service non trouvé ou inactif']);
                exit;
            }

            // Calculer la deadline
            $deadline = date('Y-m-d H:i:s', strtotime('+' . $service['delivery_days'] . ' days'));

            // Créer la commande
            $stmt = $pdo->prepare("
                INSERT INTO orders (service_id, buyer_id, seller_id, price, description, deadline, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$service_id, $buyer_id, $seller_id, $price, $description, $deadline]);

            $order_id = $pdo->lastInsertId();

            // Créer des notifications pour la nouvelle commande
            $notificationService = new NotificationService($pdo);

            // Notification pour le vendeur
            $notificationService->notifyNewOrder($seller_id, $order_id, $price);

            // Notification pour l'acheteur
            $notificationService->create(
                $buyer_id,
                'order',
                'Commande créée',
                "Votre commande pour \"{$service['title']}\" a été créée avec succès",
                "/Novatis/public/Dashboard?tab=purchases"
            );

            // Si un message a été fourni, l'envoyer automatiquement
            if (!empty($message)) {
                // Récupérer le nom de l'utilisateur pour personnaliser le message
                $stmt = $pdo->prepare("SELECT CONCAT(firstname, ' ', lastname) as name FROM users WHERE id = ?");
                $stmt->execute([$buyer_id]);
                $buyer_name = $stmt->fetchColumn();

                // Construire le message complet
                $full_message = "🆕 Nouvelle commande pour le service : " . $service['title'] . "\n\n";
                $full_message .= "Message de " . $buyer_name . " :\n";
                $full_message .= $message;

                // Insérer le message dans la table messages
                $stmt = $pdo->prepare("INSERT INTO messages (order_id, sender_id, content, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$order_id, $buyer_id, $full_message]);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Commande créée avec succès' . (!empty($message) ? ' et message envoyé' : ''),
                'order_id' => $order_id,
                'redirect' => BASE_URL . '/dashboard?tab=messages'
            ]);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['order_id']) && isset($data['status'])) {
                $order_id = $data['order_id'];
                $new_status = $data['status'];

                // Vérifier que l'utilisateur peut modifier cette commande
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND (seller_id = ? OR ? = 1)");
                $stmt->execute([$order_id, $user_id, $is_admin ? 1 : 0]);
                $order = $stmt->fetch();

                if (!$order) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'Accès refusé']);
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $order_id]);

                // Récupérer les détails de la commande pour les notifications
                $stmt = $pdo->prepare("
                    SELECT o.*, s.title as service_title, s.user_id as seller_id,
                           buyer.name as buyer_name, seller.name as seller_name
                    FROM orders o
                    JOIN services s ON s.id = o.service_id
                    JOIN users buyer ON buyer.id = o.buyer_id
                    JOIN users seller ON seller.id = s.user_id
                    WHERE o.id = ?
                ");
                $stmt->execute([$order_id]);
                $order_details = $stmt->fetch();

                // Créer des notifications selon le nouveau statut
                $status_messages = [
                    'in_progress' => [
                        'buyer' => "Votre commande \"{$order_details['service_title']}\" a été acceptée et est en cours de traitement",
                        'seller' => "Vous avez accepté la commande \"{$order_details['service_title']}\""
                    ],
                    'delivered' => [
                        'buyer' => "Votre commande \"{$order_details['service_title']}\" a été livrée",
                        'seller' => "Vous avez livré la commande \"{$order_details['service_title']}\""
                    ],
                    'completed' => [
                        'buyer' => "Votre commande \"{$order_details['service_title']}\" est terminée",
                        'seller' => "La commande \"{$order_details['service_title']}\" a été marquée comme terminée"
                    ],
                    'cancelled' => [
                        'buyer' => "Votre commande \"{$order_details['service_title']}\" a été annulée",
                        'seller' => "La commande \"{$order_details['service_title']}\" a été annulée"
                    ]
                ];

                if (isset($status_messages[$new_status])) {
                    $notificationService = new NotificationService($pdo);

                    // Notification pour l'acheteur
                    $notificationService->create(
                        $order_details['buyer_id'],
                        'order',
                        'Mise à jour de commande',
                        $status_messages[$new_status]['buyer'],
                        '/Novatis/public/Dashboard?tab=purchases'
                    );

                    // Notification pour le vendeur (sauf si c'est lui qui fait l'action)
                    if ($order_details['seller_id'] != $user_id) {
                        $notificationService->create(
                            $order_details['seller_id'],
                            'order',
                            'Mise à jour de commande',
                            $status_messages[$new_status]['seller'],
                            '/Novatis/public/Dashboard?tab=orders'
                        );
                    }
                }

                // Si le statut passe à "delivered", envoyer un message automatique pour demander une évaluation
                if ($new_status === 'delivered') {
                    try {
                        // Créer un message automatique dans le chat pour demander l'évaluation
                        $rating_message = "🎉 Félicitations ! Votre commande \"{$order_details['service_title']}\" a été livrée avec succès !\n\n" .
                                        "✨ Nous espérons que vous êtes pleinement satisfait(e) du travail réalisé.\n\n" .
                                        "⭐ Pour aider notre communauté, pourriez-vous prendre un moment pour évaluer ce prestataire ?\n" .
                                        "Votre avis est précieux et aide les autres utilisateurs à faire leurs choix.\n\n" .
                                        "📝 Voici ce que vous pouvez faire :\n" .
                                        "• Noter le travail de 1 à 5 étoiles\n" .
                                        "• Laisser un commentaire constructif\n" .
                                        "• Partager votre expérience\n\n" .
                                        "Merci de contribuer à la qualité de notre plateforme ! 🙏";

                        // Insérer le message automatique (expéditeur = système, donc on utilise l'ID du vendeur pour faire comme si c'était lui)
                        $stmt = $pdo->prepare("INSERT INTO messages (order_id, sender_id, content, created_at) VALUES (?, ?, ?, NOW())");
                        $stmt->execute([$order_id, $order_details['seller_id'], $rating_message]);

                        // Créer une notification pour ce nouveau message
                        $notificationService = new NotificationService($pdo);
                        $notificationService->create(
                            $order_details['buyer_id'],
                            'message',
                            'Demande d\'évaluation',
                            "Votre commande a été livrée ! Laissez une évaluation pour aider les autres utilisateurs.",
                            '/Novatis/public/Dashboard?tab=messages'
                        );
                    } catch (Exception $e) {
                        // Ne pas faire échouer la mise à jour du statut si le message automatique échoue
                        error_log("Erreur lors de l'envoi du message d'évaluation: " . $e->getMessage());
                    }
                }

                echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
            }
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