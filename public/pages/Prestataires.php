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
<!-- Container Prestataires -->
<div class="Prestataires max-w-7xl mx-auto mt-32 px-4">
    <h1 class="text-3xl font-bold text-center mb-10">Liste des Prestataires</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <?php 
        $currentUser = null;
        foreach ($prestataires as $row): 
            if ($currentUser !== $row['user_id']): 
                if ($currentUser !== null) echo "</ul><a href=\"dashboard.php?user={$currentUser}\" class=\"btn-contact\">Contacter</a></div>"; 
        ?>
            <!-- Carte Prestataire -->
            <div class="prestataire-card text-black p-6 rounded-2xl shadow-lg">
                <img src="<?= htmlspecialchars($row['avatar'] ?? 'default.png') ?>" 
                     alt="Avatar" 
                     class="avatar w-24 h-24 rounded-full mx-auto mb-4">
                <h2 class="text-xl font-semibold text-center"><?= htmlspecialchars($row['name']) ?></h2>
                <p class="text-gray-300 text-center mb-4"><?= htmlspecialchars($row['bio']) ?></p>
                <h3 class="font-bold mb-2">Compétences :</h3>
                <ul class="list-disc list-inside text-sm space-y-1">
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
                <a href="dashboard.php?user=<?= $currentUser ?>" 
                   class="btn-contact mt-4 inline-block bg-white text-black px-4 py-2 rounded-lg hover:bg-gray-200 transition">
                   Contacter
                </a>
            </div>
    </div>
</div>

    </div>
    <?php include __DIR__ . '/../../includes/Footer.php';?>
</body>
</html>
