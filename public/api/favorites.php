<?php
require_once __DIR__ . '/../../config/Config.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$pdo = getDBConnection();

// Créer ou mettre à jour la table favorites
try {
    // Vérifier si la colonne service_id existe
    $checkStmt = $pdo->query("SHOW COLUMNS FROM favorites LIKE 'service_id'");
    if ($checkStmt->rowCount() === 0) {
        // Ancienne structure avec provider_id ou favorited_user_id, supprimer et recréer
        $pdo->exec("DROP TABLE IF EXISTS favorites");
        $pdo->exec("
            CREATE TABLE favorites (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                service_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_favorite (user_id, service_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
} catch (PDOException $e) {
    // Si la table n'existe pas, la créer
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS favorites (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                service_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_favorite (user_id, service_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    } catch (PDOException $e2) {
        // Ignorer si erreur de création
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'toggle':
        // Ajouter ou retirer un service des favoris
        $serviceId = intval($_POST['service_id'] ?? 0);

        if ($serviceId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID service invalide']);
            exit;
        }

        try {
            // Vérifier si déjà en favori
            $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND service_id = ?");
            $stmt->execute([$user['id'], $serviceId]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Retirer des favoris
                $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND service_id = ?");
                $stmt->execute([$user['id'], $serviceId]);
                echo json_encode(['success' => true, 'action' => 'removed', 'is_favorite' => false]);
            } else {
                // Ajouter aux favoris
                $stmt = $pdo->prepare("INSERT INTO favorites (user_id, service_id) VALUES (?, ?)");
                $stmt->execute([$user['id'], $serviceId]);
                echo json_encode(['success' => true, 'action' => 'added', 'is_favorite' => true]);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
        break;

    case 'list':
        // Récupérer tous les services favoris de l'utilisateur
        try {
            $stmt = $pdo->prepare("
                SELECT service_id
                FROM favorites
                WHERE user_id = ?
            ");
            $stmt->execute([$user['id']]);
            $favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode(['success' => true, 'favorites' => $favorites]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
}
