<?php
require_once 'config.php'; // connexion à la BDD (mysqli ou PDO)

// Requête pour récupérer les prestataires avec leurs services
$sql = "
    SELECT 
        u.id AS user_id,
        u.name,
        u.avatar,
        u.bio,
        s.title AS competence,
        s.price,
        s.delivery_days
    FROM users u
    JOIN services s ON u.id = s.user_id
    WHERE s.status = 'active'
    ORDER BY u.name ASC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prestataires</title>
    <link rel="stylesheet" href="style.css"> <!-- ton CSS -->
</head>
<body>
    <h1>Liste des Prestataires</h1>

    <div class="prestataires">
        <?php 
        $currentUser = null;
        while($row = $result->fetch_assoc()): 
            if ($currentUser !== $row['user_id']): 
                if ($currentUser !== null) echo "</ul></div>"; 
        ?>
            <div class="prestataire-card">
                <img src="<?= htmlspecialchars($row['avatar'] ?? 'default.png') ?>" alt="Avatar" class="avatar">
                <h2><?= htmlspecialchars($row['name']) ?></h2>
                <p><?= htmlspecialchars($row['bio']) ?></p>
                <h3>Compétences :</h3>
                <ul>
        <?php 
            $currentUser = $row['user_id']; 
            endif; 
        ?>
                    <li>
                        <?= htmlspecialchars($row['competence']) ?> 
                        — <?= number_format($row['price'], 2, ',', ' ') ?> € 
                        (<?= (int)$row['delivery_days'] ?> jours)
                    </li>
        <?php endwhile; ?>
                </ul>
                <a href="dashboard.php?user=<?= $currentUser ?>" class="btn-contact">Contacter</a>
            </div>
    </div>
</body>
</html>
