<?php
require_once __DIR__ . '/../../../config/config.php';

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
                // Admin voit tous les tickets
                $stmt = $pdo->prepare("
                    SELECT st.*,
                           COALESCE(NULLIF(CONCAT(u.firstname, ' ', u.lastname), ' '), u.pseudo) as user_name,
                           u.email as user_email,
                           u.avatar as user_avatar
                    FROM support_tickets st
                    JOIN users u ON u.id = st.user_id
                    ORDER BY
                        CASE st.status
                            WHEN 'open' THEN 1
                            WHEN 'in_progress' THEN 2
                            WHEN 'resolved' THEN 3
                            WHEN 'closed' THEN 4
                        END,
                        st.created_at DESC
                ");
                $stmt->execute();
            } else {
                // Utilisateur voit ses tickets
                $stmt = $pdo->prepare("
                    SELECT * FROM support_tickets
                    WHERE user_id = ?
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$user_id]);
            }

            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'tickets' => $tickets]);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['subject']) && isset($data['message'])) {
                $subject = trim($data['subject']);
                $message = trim($data['message']);

                if (empty($subject) || empty($message)) {
                    echo json_encode(['success' => false, 'error' => 'Sujet et message requis']);
                    exit;
                }

                $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, subject, message) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $subject, $message]);

                echo json_encode(['success' => true, 'message' => 'Ticket créé']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['ticket_id'])) {
                $ticket_id = $data['ticket_id'];

                if ($is_admin) {
                    // Admin peut modifier le statut et ajouter une réponse
                    $updates = [];
                    $params = [];

                    if (isset($data['status'])) {
                        $updates[] = 'status = ?';
                        $params[] = $data['status'];
                    }

                    if (isset($data['admin_response'])) {
                        $updates[] = 'admin_response = ?';
                        $params[] = $data['admin_response'];
                    }

                    if (isset($data['status']) || isset($data['admin_response'])) {
                        $updates[] = 'updated_at = NOW()';
                    }

                    if (!empty($updates)) {
                        $params[] = $ticket_id;
                        $sql = "UPDATE support_tickets SET " . implode(', ', $updates) . " WHERE id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);

                        echo json_encode(['success' => true, 'message' => 'Ticket mis à jour']);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Aucune modification fournie']);
                    }
                } else {
                    // Utilisateur peut seulement fermer son ticket
                    if (isset($data['status']) && $data['status'] === 'closed') {
                        $stmt = $pdo->prepare("UPDATE support_tickets SET status = 'closed', updated_at = NOW() WHERE id = ? AND user_id = ?");
                        $stmt->execute([$ticket_id, $user_id]);

                        echo json_encode(['success' => true, 'message' => 'Ticket fermé']);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Action non autorisée']);
                    }
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'ID ticket manquant']);
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