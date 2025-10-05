<?php
require_once __DIR__ . '/EmailService.php';

/**
 * Service de gestion des notifications utilisateur
 * Gère les notifications in-app, email et push
 */
class NotificationService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->createTablesIfNotExist();
    }

    /**
     * Crée les tables si elles n'existent pas
     */
    private function createTablesIfNotExist() {
        // Table des notifications
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS user_notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                link VARCHAR(255) DEFAULT NULL,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                read_at TIMESTAMP NULL,
                INDEX idx_user_unread (user_id, is_read),
                INDEX idx_created (created_at),
                INDEX idx_type (type)
            )
        ");

        // Table des événements
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS notification_events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                notification_id INT NOT NULL,
                event_type VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                metadata JSON DEFAULT NULL,
                INDEX idx_notification (notification_id),
                INDEX idx_event_type (event_type)
            )
        ");
    }

    /**
     * Crée une notification pour un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @param string $type Type de notification (order, message, security, payment, service_update)
     * @param string $title Titre de la notification
     * @param string $message Message de la notification
     * @param string|null $link Lien optionnel vers la ressource
     * @return int|false ID de la notification créée ou false en cas d'erreur
     */
    public function create($userId, $type, $title, $message, $link = null) {
        try {
            // Insérer la notification
            $stmt = $this->pdo->prepare("
                INSERT INTO user_notifications (user_id, type, title, message, link)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $type, $title, $message, $link]);
            $notificationId = $this->pdo->lastInsertId();

            // Logger l'événement de création
            $this->logEvent($notificationId, 'created');

            // Envoyer par email si l'utilisateur a activé les notifications email
            $this->sendEmailIfEnabled($userId, $notificationId);

            return $notificationId;

        } catch (Exception $e) {
            error_log("Erreur lors de la création de la notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie une notification par email si l'utilisateur a activé cette option
     */
    private function sendEmailIfEnabled($userId, $notificationId) {
        try {
            // Vérifier les préférences de l'utilisateur
            $stmt = $this->pdo->prepare("
                SELECT up.email_notifications, u.email, u.firstname, u.lastname
                FROM user_preferences up
                JOIN users u ON u.id = up.user_id
                WHERE up.user_id = ?
            ");
            $stmt->execute([$userId]);
            $prefs = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$prefs || !$prefs['email_notifications']) {
                return; // Notifications email désactivées
            }

            // Récupérer la notification
            $stmt = $this->pdo->prepare("
                SELECT title, message, link FROM user_notifications WHERE id = ?
            ");
            $stmt->execute([$notificationId]);
            $notification = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($notification) {
                $sent = EmailService::sendNotificationEmail(
                    $prefs['email'],
                    $prefs['firstname'],
                    $prefs['lastname'],
                    $notification['title'],
                    $notification['message'],
                    $notification['link']
                );

                if ($sent) {
                    $this->logEvent($notificationId, 'sent_email');
                }
            }

        } catch (Exception $e) {
            error_log("Erreur lors de l'envoi de l'email de notification: " . $e->getMessage());
        }
    }

    /**
     * Marque une notification comme lue
     */
    public function markAsRead($notificationId, $userId) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE user_notifications
                SET is_read = TRUE, read_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$notificationId, $userId]);

            $this->logEvent($notificationId, 'read');

            return true;
        } catch (Exception $e) {
            error_log("Erreur lors du marquage comme lu: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marque toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead($userId) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE user_notifications
                SET is_read = TRUE, read_at = NOW()
                WHERE user_id = ? AND is_read = FALSE
            ");
            $stmt->execute([$userId]);

            return true;
        } catch (Exception $e) {
            error_log("Erreur lors du marquage de toutes les notifications: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les notifications d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @param bool|null $onlyUnread Si true, récupère uniquement les non lues
     * @param int $limit Nombre max de notifications à récupérer
     * @return array Liste des notifications
     */
    public function getUserNotifications($userId, $onlyUnread = null, $limit = 50) {
        try {
            $sql = "SELECT * FROM user_notifications WHERE user_id = ?";
            $params = [$userId];

            if ($onlyUnread === true) {
                $sql .= " AND is_read = FALSE";
            }

            // LIMIT doit être ajouté directement car PDO n'accepte pas les entiers dans LIMIT via binding
            $limit = (int)$limit;
            $sql .= " ORDER BY created_at DESC LIMIT " . $limit;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des notifications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compte les notifications non lues d'un utilisateur
     */
    public function countUnread($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count
                FROM user_notifications
                WHERE user_id = ? AND is_read = FALSE
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int)$result['count'];

        } catch (Exception $e) {
            error_log("Erreur lors du comptage des notifications: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Supprime une notification
     */
    public function delete($notificationId, $userId) {
        try {
            $this->logEvent($notificationId, 'deleted');

            $stmt = $this->pdo->prepare("
                DELETE FROM user_notifications
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$notificationId, $userId]);

            return true;
        } catch (Exception $e) {
            error_log("Erreur lors de la suppression de la notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime les anciennes notifications (plus de 30 jours)
     */
    public function deleteOldNotifications($days = 30) {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM user_notifications
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$days]);

            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Erreur lors de la suppression des anciennes notifications: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Logger un événement de notification
     */
    private function logEvent($notificationId, $eventType, $metadata = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO notification_events (notification_id, event_type, metadata)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $notificationId,
                $eventType,
                $metadata ? json_encode($metadata) : null
            ]);
        } catch (Exception $e) {
            error_log("Erreur lors du logging de l'événement: " . $e->getMessage());
        }
    }

    // ========== MÉTHODES SPÉCIFIQUES PAR TYPE ==========

    /**
     * Crée une notification de nouvelle commande
     */
    public function notifyNewOrder($userId, $orderId, $orderAmount) {
        return $this->create(
            $userId,
            'order',
            'Nouvelle commande reçue',
            "Vous avez reçu une nouvelle commande d'un montant de {$orderAmount}€.",
            "/Novatis/public/orders/{$orderId}"
        );
    }

    /**
     * Crée une notification de nouveau message
     */
    public function notifyNewMessage($userId, $senderName, $conversationId) {
        return $this->create(
            $userId,
            'message',
            'Nouveau message',
            "{$senderName} vous a envoyé un message.",
            "/Novatis/public/messages/{$conversationId}"
        );
    }

    /**
     * Crée une notification de sécurité
     */
    public function notifySecurityAlert($userId, $alertMessage) {
        return $this->create(
            $userId,
            'security',
            'Alerte de sécurité',
            $alertMessage,
            "/Novatis/public/Parametres?section=security"
        );
    }

    /**
     * Crée une notification de rappel de paiement
     */
    public function notifyPaymentReminder($userId, $amount, $dueDate) {
        return $this->create(
            $userId,
            'payment',
            'Rappel de paiement',
            "Un paiement de {$amount}€ est dû le {$dueDate}.",
            "/Novatis/public/payments"
        );
    }

    /**
     * Crée une notification de mise à jour de service
     */
    public function notifyServiceUpdate($userId, $serviceName, $updateMessage) {
        return $this->create(
            $userId,
            'service_update',
            "Mise à jour: {$serviceName}",
            $updateMessage,
            "/Novatis/public/services"
        );
    }
}
?>
