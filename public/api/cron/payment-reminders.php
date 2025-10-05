<?php
/**
 * Script cron pour envoyer des rappels de paiement
 * À exécuter quotidiennement via cron job
 *
 * Exemple cron: 0 9 * * * php /path/to/payment-reminders.php
 */

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/NotificationService.php';

try {
    // Rechercher les commandes qui approchent de leur deadline
    // et qui ne sont pas encore payées (statut 'pending')
    $stmt = $pdo->prepare("
        SELECT
            o.*,
            s.title as service_title,
            COALESCE(NULLIF(CONCAT(u.firstname, ' ', u.lastname), ' '), u.pseudo) as buyer_name
        FROM orders o
        JOIN services s ON s.id = o.service_id
        JOIN users u ON u.id = o.buyer_id
        WHERE o.status = 'pending'
        AND o.deadline IS NOT NULL
        AND DATEDIFF(o.deadline, NOW()) <= 3
        AND DATEDIFF(o.deadline, NOW()) >= 0
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $notificationService = new NotificationService($pdo);
    $count = 0;

    foreach ($orders as $order) {
        $daysRemaining = (new DateTime($order['deadline']))->diff(new DateTime())->days;

        $message = "Rappel : Votre commande \"{$order['service_title']}\" ";

        if ($daysRemaining == 0) {
            $message .= "expire aujourd'hui ! ";
        } elseif ($daysRemaining == 1) {
            $message .= "expire demain. ";
        } else {
            $message .= "expire dans {$daysRemaining} jours. ";
        }

        $message .= "Merci de finaliser le paiement de {$order['price']}€.";

        // Envoyer une notification de rappel de paiement
        $notificationService->notifyPaymentReminder(
            $order['buyer_id'],
            $order['price'],
            date('d/m/Y', strtotime($order['deadline']))
        );

        $count++;
    }

    echo "Rappels de paiement envoyés: {$count}\n";

} catch (Exception $e) {
    error_log("Erreur cron payment reminders: " . $e->getMessage());
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
