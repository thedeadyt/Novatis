<?php
require_once __DIR__ . '/../../config/config.php'; // connexion à la BDD (PDO)

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

$stmt = $pdo->query($sql);
$prestataires = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prestataires</title>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/Variables.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/prestataires.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/Footer.css'>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- React & ReactDOM CDN -->
    <script script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>

    <!-- Babel CDN pour JSX -->
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../../includes/Header.php';?>
    <div class="content" id="content">
        <div class="Prestataires""> <!-- Contenu principal de la page -->
    <h1>Liste des Prestataires</h1>

        <div class="prestataires mt-20">
            <?php 
            $currentUser = null;
            foreach ($prestataires as $row): 
                if ($currentUser !== $row['user_id']): 
                    if ($currentUser !== null) echo "</ul><a href=\"dashboard.php?user={$currentUser}\" class=\"btn-contact\">Contacter</a></div>"; 
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
            <?php endforeach; ?>
                    </ul>
                    <a href="dashboard.php?user=<?= $currentUser ?>" class="btn-contact">Contacter</a>
                </div>
        </div>
        </div>
     </div>
    <?php include __DIR__ . '/../../includes/Footer.php';?>
</body>
</html>
