
<?php
require_once __DIR__ . '/../config/config.php';

// Vérifier si un message est passé en paramètre
$message = $_GET['message'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Novatis</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/Variables.css'>
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
     <?php include __DIR__ . '/../includes/header.php';?>

     <!-- Message de confirmation de suppression de compte -->
     <?php if ($message === 'account_deleted'): ?>
     <div class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 animate-fade-in">
         <div class="bg-green-50 border-l-4 border-green-500 rounded-lg shadow-lg px-6 py-4 max-w-md">
             <div class="flex items-center">
                 <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                 </svg>
                 <div>
                     <p class="font-semibold text-green-800">Compte supprimé avec succès</p>
                     <p class="text-sm text-green-700">Vos données ont été définitivement supprimées.</p>
                 </div>
                 <button onclick="this.parentElement.parentElement.parentElement.remove()" class="ml-4 text-green-500 hover:text-green-700">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                     </svg>
                 </button>
             </div>
         </div>
     </div>
     <script>
         // Masquer le message après 5 secondes
         setTimeout(() => {
             const alert = document.querySelector('.animate-fade-in');
             if (alert) {
                 alert.style.opacity = '0';
                 alert.style.transition = 'opacity 0.5s';
                 setTimeout(() => alert.remove(), 500);
             }
         }, 5000);
     </script>
     <?php endif; ?>

     <div class="content" id="content">
         <!-- Contenu principal de la page -->

     </div>
     <?php include __DIR__ . '/../includes/footer.php';?>
</body>
</html>