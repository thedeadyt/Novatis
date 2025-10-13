<?php
require_once __DIR__ . '/../../../config/config.php';

header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté
requireAuth();

$user = getCurrentUser();
$pdo = getDBConnection();

// Créer la table favorites si elle n'existe pas
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            favorited_user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_favorite (user_id, favorited_user_id),
            INDEX idx_user_id (user_id),
            INDEX idx_favorited_user_id (favorited_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    // Table existe déjà
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $favorited_user_id = $input['favorited_user_id'] ?? 0;

    if (!$favorited_user_id) {
        echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
        exit;
    }

    // Ne pas pouvoir s'ajouter soi-même en favori
    if ($favorited_user_id == $user['id']) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas vous ajouter vous-même en favori']);
        exit;
    }

    switch ($action) {
        case 'add':
            try {
                // Vérifier que l'utilisateur cible existe
                $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
                $stmt->execute([$favorited_user_id]);
                if (!$stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
                    exit;
                }

                // Ajouter aux favoris
                $stmt = $pdo->prepare("
                    INSERT INTO favorites (user_id, favorited_user_id)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE created_at = created_at
                ");
                $stmt->execute([$user['id'], $favorited_user_id]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Ajouté aux favoris',
                    'is_favorite' => true
                ]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout aux favoris']);
            }
            break;

        case 'remove':
            try {
                $stmt = $pdo->prepare("
                    DELETE FROM favorites
                    WHERE user_id = ? AND favorited_user_id = ?
                ");
                $stmt->execute([$user['id'], $favorited_user_id]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Retiré des favoris',
                    'is_favorite' => false
                ]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression des favoris']);
            }
            break;

        case 'toggle':
            try {
                // Vérifier si déjà en favori
                $stmt = $pdo->prepare("
                    SELECT id FROM favorites
                    WHERE user_id = ? AND favorited_user_id = ?
                ");
                $stmt->execute([$user['id'], $favorited_user_id]);
                $isFavorite = $stmt->fetch();

                if ($isFavorite) {
                    // Retirer des favoris
                    $stmt = $pdo->prepare("
                        DELETE FROM favorites
                        WHERE user_id = ? AND favorited_user_id = ?
                    ");
                    $stmt->execute([$user['id'], $favorited_user_id]);

                    echo json_encode([
                        'success' => true,
                        'message' => 'Retiré des favoris',
                        'is_favorite' => false
                    ]);
                } else {
                    // Ajouter aux favoris
                    $stmt = $pdo->prepare("
                        INSERT INTO favorites (user_id, favorited_user_id)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$user['id'], $favorited_user_id]);

                    echo json_encode([
                        'success' => true,
                        'message' => 'Ajouté aux favoris',
                        'is_favorite' => true
                    ]);
                }
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification des favoris']);
            }
            break;

        case 'check':
            try {
                $stmt = $pdo->prepare("
                    SELECT id FROM favorites
                    WHERE user_id = ? AND favorited_user_id = ?
                ");
                $stmt->execute([$user['id'], $favorited_user_id]);
                $isFavorite = $stmt->fetch();

                echo json_encode([
                    'success' => true,
                    'is_favorite' => (bool)$isFavorite
                ]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la vérification']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
    }

} elseif ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';

    switch ($action) {
        case 'list':
            try {
                $stmt = $pdo->prepare("
                    SELECT
                        u.id,
                        u.firstname,
                        u.lastname,
                        u.pseudo,
                        u.avatar,
                        u.bio,
                        u.location,
                        u.rating,
                        f.created_at as favorited_at
                    FROM favorites f
                    JOIN users u ON f.favorited_user_id = u.id
                    WHERE f.user_id = ?
                    ORDER BY f.created_at DESC
                ");
                $stmt->execute([$user['id']]);
                $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    'success' => true,
                    'favorites' => $favorites,
                    'count' => count($favorites)
                ]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des favoris']);
            }
            break;

        case 'count':
            try {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as count
                    FROM favorites
                    WHERE user_id = ?
                ");
                $stmt->execute([$user['id']]);
                $result = $stmt->fetch();

                echo json_encode([
                    'success' => true,
                    'count' => (int)$result['count']
                ]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du comptage']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>
