<?php
require_once __DIR__ . '/../../config/config.php';


if (session_status() === PHP_SESSION_NONE) {
session_start();
}


// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
header('Location: ' . BASE_URL . '/login');
exit;

}


// Exemple simple de détection d'admin
$isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Novatis | Dashboard</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/sidebar.css'>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/asset/css/index.css'>
    <!-- React & ReactDOM -->
    <script src="https://unpkg.com/react@18/umd/react.production.min.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
</head>
<body>
         <?php include __DIR__ . '/../../includes/header.php';?>
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <span class="logo" aria-hidden="true"></span>
            <h1>Novatis</h1>
        </div>

        <nav aria-label="Navigation principale">
            <ul>
                <li><a href="/dashboard/messages.php" id="nav-messages"><span>✉️</span><span>Messages</span><span class="badge" style="margin-left:auto">3</span></a></li>
                <li><a href="/dashboard/clients.php" id="nav-clients"><span>👥</span><span>Clients</span></a></li>
                <li><a href="/dashboard/projets.php" id="nav-projets"><span>📁</span><span>Projets</span></a></li>
                <li><a href="/dashboard/portfolio.php" id="nav-portfolio"><span>🖼️</span><span>Portfolio</span></a></li>

                <?php if ($isAdmin): ?>
                <li><a href="/admin/" id="nav-admin"><span>⚙️</span><span>Administration</span></a></li>
                <?php endif; ?>

            </ul>
        </nav>

        <div class="spacer"></div>

        <div class="user-info">
            <div>Connecté en tant que <strong><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Utilisateur') ?></strong></div>
            <div style="margin-top:6px"><a href="./logout" class="logout-link">Se déconnecter</a></div>
        </div>
    </aside>

    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

    <main class="content">
        <!-- petit contrôle responsive -->
        <button class="toggle-btn" id="toggleBtn" aria-controls="sidebar" aria-expanded="false" onclick="toggleSidebar()">☰ Menu</button>

        <h2>Tableau de bord</h2>
        <p>Contenu principal — remplacez par votre interface.</p>

    </main>

    <script>
        // Gestion simple de l'état "actif" sur la nav selon l'URL
        (function(){
            var path = window.location.pathname;
            var map = {
                'messages.php': 'nav-messages',
                'clients.php': 'nav-clients',
                'projets.php': 'nav-projets',
                'portfolio.php': 'nav-portfolio',
                '/admin/': 'nav-admin'
            };
            for (var k in map){
                if (path.indexOf(k) !== -1){
                    var el = document.getElementById(map[k]);
                    if(el) el.classList.add('active');
                }
            }
        })();

        // Responsive toggle
        function toggleSidebar(){
            var sb = document.getElementById('sidebar');
            sb.classList.toggle('open');
            var expanded = sb.classList.contains('open');
            document.getElementById('toggleBtn').setAttribute('aria-expanded', expanded);
            document.body.classList.toggle('sidebar-open', expanded);
        }
        function closeSidebar(){
            var sb = document.getElementById('sidebar');
            sb.classList.remove('open');
            document.getElementById('toggleBtn').setAttribute('aria-expanded', false);
            document.body.classList.remove('sidebar-open');
        }
    </script>
</body>
</html>