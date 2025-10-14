<?php
require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novatis | À propos</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logos/Logo_Novatis.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/Apropos.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>

    <div class="container">
        <div class="header">
            <h1>À propos - Novatis</h1>
        </div>

        <div class="section">
            <h2>Notre Groupe</h2>
            <p>Groupe 15</p>

            <div class="members">
                <div class="member">
                    <strong>Alexandre BOUVY</strong>
                    <p>Tout en même temps</p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Compétences informatiques</h2>

            <div class="skills">
                <span class="skill">HTML & CSS</span>
                <span class="skill">JavaScript</span>
                <span class="skill">PHP & MySQL</span>
                <span class="skill">React.js</span>
                <span class="skill">Node.js</span>
                <span class="skill">Design Responsive</span>
                <span class="skill">UX/UI Design</span>
                <span class="skill">API REST</span>
                <span class="skill">Git & GitHub</span>
                <span class="skill">Bootstrap</span>
                <span class="skill">SASS/SCSS</span>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>