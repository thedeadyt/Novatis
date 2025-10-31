<?php
/**
 * Script cron pour alerter les paiements en retard
 * À exécuter quotidiennement via cron job
 *
 * Exemple cron: 0 10 * * * php /path/to/overdue-payment-alerts.php
 */

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/NotificationService.php';

try {
    // Rechercher les commandes en retard (deadline dépassée et toujours en pending)
    $stmt = $pdo->prepare("
        SELECT
            o.*,
            s.title as service_title,
            COALESCE(NULLIF(CONCAT(buyer.firstname, ' ', buyer.lastname), ' '), buyer.pseudo) as buyer_name,
            COALESCE(NULLIF(CONCAT(seller.firstname, ' ', seller.lastname), ' '), seller.pseudo) as seller_name
        FROM orders o
        JOIN services s ON s.id = o.service_id
        JOIN users buyer ON buyer.id = o.buyer_id
        JOIN users seller ON seller.id = o.seller_id
        WHERE o.status = 'pending'
        AND o.deadline IS NOT NULL
        AND o.deadline < NOW()
    ");
    $stmt->execute();
    $overdueOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $notificationService = new NotificationService($pdo);
    $count = 0;

    foreach ($overdueOrders as $order) {
        $daysOverdue = (new DateTime())->diff(new DateTime($order['deadline']))->days;

        // Notification pour l'acheteur
        $buyerMessage = "⚠️ RETARD DE PAIEMENT : Votre commande \"{$order['service_title']}\" est en retard de {$daysOverdue} jour(s). ";
        $buyerMessage .= "Montant dû : {$order['price']}€. Veuillez régulariser votre situation rapidement.";

        $notificationService->create(
            $order['buyer_id'],
            'payment',
            'Paiement en retard',
            $buyerMessage,
            '/Novatis/public/Dashboard?tab=purchases'
        );

        // Notification pour le vendeur
        $sellerMessage = "⚠️ Une commande pour votre service \"{$order['service_title']}\" est en retard de paiement ({$daysOverdue} jour(s)). ";
        $sellerMessage .= "L'acheteur {$order['buyer_name']} doit régler {$order['price']}€.";

        $notificationService->create(
            $order['seller_id'],
            'payment',
            'Paiement client en retard',
            $sellerMessage,
            '/Novatis/public/Dashboard?tab=orders'
        );

        $count++;

        // Si le retard dépasse 7 jours, annuler automatiquement la commande
        if ($daysOverdue > 7) {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$order['id']]);

            // Notifier l'annulation
            $notificationService->create(
                $order['buyer_id'],
                'order',
                'Commande annulée',
                "Votre commande \"{$order['service_title']}\" a été automatiquement annulée pour non-paiement après 7 jours de retard.",
                '/Novatis/public/Dashboard?tab=purchases'
            );

            $notificationService->create(
                $order['seller_id'],
                'order',
                'Commande annulée',
                "La commande \"{$order['service_title']}\" a été annulée automatiquement pour non-paiement.",
                '/Novatis/public/Dashboard?tab=orders'
            );
        }
    }

    echo "Alertes de retard envoyées: {$count}\n";

} catch (Exception $e) {
    error_log("Erreur cron overdue payment alerts: " . $e->getMessage());
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
