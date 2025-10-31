<?php
require_once __DIR__ . '/../../config/Config.php';
?>
<!DOCTYPE html>
<html lang="fr" data-user-lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="apropos.title" data-i18n-ns="pages">Novatis | À propos</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logos/Logo_Novatis.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/Apropos.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- React & Babel pour le header -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    <!-- i18next -->
    <?php include __DIR__ . '/../../includes/i18n-head.php'; ?>
</head>
<body class="flex flex-col min-h-screen">
    <?php include __DIR__ . '/../../includes/header.php'; ?>

    <main class="flex-1">
    <div class="container">
        <div class="header">
            <h1 data-i18n="apropos.heading" data-i18n-ns="pages">À propos - Novatis</h1>
        </div>

        <div class="section">
            <h2 data-i18n="apropos.ourGroup" data-i18n-ns="pages">Notre Groupe</h2>
            <p data-i18n="apropos.group" data-i18n-ns="pages">Groupe 15</p>

            <div class="members">
                <div class="member">
                    <strong>Alexandre BOUVY</strong>
                    <p data-i18n="apropos.allInOne" data-i18n-ns="pages">Tout en même temps</p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2 data-i18n="apropos.skills" data-i18n-ns="pages">Compétences informatiques</h2>

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
    </main>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
