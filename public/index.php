
<?php
require_once __DIR__ . '/../config/Config.php';

// Vérifier si un message est passé en paramètre
$message = $_GET['message'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr" data-user-lang="fr">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title data-i18n="index.title" data-i18n-ns="pages">Novatis | Accueil</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logos/Logo_Novatis.png">
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/Variables.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/theme.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/Footer.css'>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>

    <!-- React & ReactDOM CDN -->
    <script script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>

    <!-- Babel CDN pour JSX -->
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    <!-- i18next pour les traductions -->
    <?php include __DIR__ . '/../includes/i18n-head.php'; ?>

    <!-- Script de thème global -->
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
</head>
<body class="flex flex-col min-h-screen bg-white dark:bg-slate-950">
     <?php include __DIR__ . '/../includes/Header.php';?>
     <main class="flex-1 pt-20">

     <!-- Hero Section -->
     <section class="bg-white dark:bg-slate-900 text-gray-900 dark:text-white py-12 sm:py-16 md:py-20 px-4 sm:px-6">
         <div class="max-w-7xl mx-auto">
             <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
                 <!-- Colonne texte -->
                 <div>
                     <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
                         <span data-i18n="index.welcome" data-i18n-ns="pages">Bienvenue sur</span> <span class="text-red-600 dark:text-red-500" data-i18n="index.welcomeBrand" data-i18n-ns="pages">Novatis</span>
                     </h1>
                     <p class="text-base sm:text-lg text-gray-700 dark:text-gray-300 mb-8 leading-relaxed" data-i18n="index.description" data-i18n-ns="pages">
                         Novatis est votre marketplace de services professionnels. Découvrez des prestataires qualifiés, explorez leurs services et construisez votre projet avec les meilleurs talents.
                     </p>
                     <div class="flex gap-2 sm:gap-4 flex-wrap">
                         <a href="<?= BASE_URL ?>/Prestataires" class="bg-red-600 hover:bg-red-700 text-white px-6 sm:px-8 py-2 sm:py-3 text-sm sm:text-base rounded-lg font-semibold transition-colors" data-i18n="index.discoverServices" data-i18n-ns="pages">
                             Découvrir les services
                         </a>
                         <a href="<?= BASE_URL ?>/Autentification?mode=register" class="border-2 border-red-600 text-red-600 hover:bg-red-600 hover:text-white px-6 sm:px-8 py-2 sm:py-3 text-sm sm:text-base rounded-lg font-semibold transition-colors" data-i18n="index.becomeProvider" data-i18n-ns="pages">
                             Devenir prestataire
                         </a>
                     </div>
                 </div>
                 
                 <!-- Colonne image/décoration -->
                 <div class="hidden md:flex items-center justify-center">
                     <div class="relative w-full h-80">
                         <!-- Décoration rouge -->
                         <div class="absolute inset-0 bg-gradient-to-br from-red-600/20 to-red-600/5 rounded-lg"></div>
                         <div class="absolute top-10 right-10 w-32 h-32 bg-red-600/10 rounded-full blur-3xl"></div>
                         <div class="absolute bottom-10 left-10 w-40 h-40 bg-red-600/5 rounded-full blur-3xl"></div>
                     </div>
                 </div>
             </div>
         </div>
     </section>

     <!-- Contenu principal -->
     <section class="py-12 sm:py-16 md:py-20 px-4 sm:px-6 bg-gray-50 dark:bg-slate-800">
         <div id="categories-container" class="max-w-7xl mx-auto">
             <!-- Les catégories seront chargées ici par React -->
         </div>
         <div class="text-center mt-12">
             <p class="text-gray-500 dark:text-gray-400 text-lg" data-i18n="index.loadingCategories" data-i18n-ns="pages">Chargement des catégories...</p>
         </div>
     </section>

     <script type="text/babel">
         const { useState, useEffect } = React;

         function CategoriesSection() {
             const [categories, setCategories] = useState([]);
             const [loading, setLoading] = useState(true);
             const BASE_URL = <?= json_encode(BASE_URL) ?>;

             const AnonymousAvatar = ({ size = 40 }) => (
                 <div className={`flex items-center justify-center bg-gray-200 dark:bg-slate-600 rounded-full`} style={{ width: size, height: size }}>
                     <svg width={size * 0.6} height={size * 0.6} viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                         <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="#6B7280"/>
                     </svg>
                 </div>
             );

             const ServiceCard = ({ service }) => {
                 const provider_name = `${service.firstname || ''} ${service.lastname || ''}`.trim() || service.pseudo;
                 const serviceLink = `${BASE_URL}/Prestataires?category=${service.category_id}&search=${encodeURIComponent(service.title)}`;
                 return (
                     <a href={serviceLink} className="bg-white dark:bg-slate-700 rounded-lg shadow hover:shadow-lg dark:hover:shadow-xl transition-all p-4 block group border border-gray-200 dark:border-slate-600 hover:border-red-600 dark:hover:border-red-500 relative">
                         {/* Accent rouge top */}
                         <div className="absolute -top-0.5 left-0 right-0 h-0.5 bg-red-600 rounded-t-lg group-hover:h-1 transition-all"></div>
                         
                         {service.image && (
                             <img src={service.image} alt={service.title} className="w-full h-32 object-cover rounded mb-3 group-hover:scale-105 transition-transform" />
                         )}
                         <h4 className="font-semibold text-gray-900 dark:text-white mb-1 line-clamp-2 text-sm">{service.title}</h4>
                         <p className="text-gray-600 dark:text-gray-400 text-xs mb-3 line-clamp-1">{service.description}</p>
                         <div className="flex items-center justify-between">
                             <span className="text-red-600 dark:text-red-500 font-bold text-lg">{service.price}€</span>
                             {service.rating && service.rating > 0 && (
                                 <div className="flex items-center gap-1">
                                     <svg className="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                         <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                     </svg>
                                     <span className="text-xs font-semibold text-gray-700 dark:text-gray-300">{parseFloat(service.rating).toFixed(1)}</span>
                                 </div>
                             )}
                         </div>
                     </a>
                 );
             };

             const UserCard = ({ user }) => {
                 const name = `${user.firstname || ''} ${user.lastname || ''}`.trim() || user.pseudo;
                 const profileLink = `${BASE_URL}/profil?id=${user.id}`;
                 return (
                     <a href={profileLink} className="bg-white dark:bg-slate-700 rounded-lg shadow hover:shadow-lg dark:hover:shadow-xl transition-all p-4 text-center block group border border-gray-200 dark:border-slate-600 hover:border-red-600 dark:hover:border-red-500 relative">
                         {/* Accent rouge ligne top */}
                         <div className="absolute -top-0.5 left-0 right-0 h-0.5 bg-red-600 rounded-t-lg group-hover:h-1 transition-all"></div>
                         
                         {user.avatar ? (
                             <img src={user.avatar} alt={name} className="w-12 h-12 rounded-full mx-auto mb-3 object-cover group-hover:scale-110 transition-transform border-2 border-red-600" />
                         ) : (
                             <div className="flex justify-center mb-3">
                                 <AnonymousAvatar size={48} />
                             </div>
                         )}
                         <p className="font-semibold text-gray-900 dark:text-white text-sm line-clamp-2">{name}</p>
                         <p className="text-gray-600 dark:text-gray-400 text-xs mb-2">@{user.pseudo}</p>
                         {user.rating && user.rating > 0 && (
                             <div className="flex items-center justify-center gap-1">
                                 <svg className="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                     <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                 </svg>
                                 <span className="text-xs font-semibold text-gray-700 dark:text-gray-300">{parseFloat(user.rating).toFixed(1)}</span>
                             </div>
                         )}
                     </a>
                 );
             };

             const CategorySection = ({ category, services, users }) => {
                 return (
                     <div className="mb-8 md:mb-16 bg-white dark:bg-slate-700 rounded-lg overflow-hidden shadow dark:shadow-lg border border-gray-200 dark:border-slate-600">
                         {/* Header de catégorie avec accent rouge */}
                         <div className="bg-white dark:bg-slate-800 px-4 sm:px-8 py-4 sm:py-6 border-l-4 border-red-600 flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                             {category.icon && (
                                 <div className="text-3xl sm:text-4xl flex-shrink-0">{category.icon}</div>
                             )}
                             <div className="min-w-0">
                                 <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">{category.name}</h2>
                                 <p className="text-sm sm:text-base text-gray-600 dark:text-gray-400">{category.services_count} {window.i18n ? window.i18n.t('index.servicesOffered', { ns: 'pages' }) : 'services proposés'}</p>
                             </div>
                         </div>

                         {/* Contenu : services à gauche, utilisateurs à droite */}
                         <div className="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 p-4 sm:p-8 bg-gray-50 dark:bg-slate-700">
                             {/* Gauche : Services les plus proposés */}
                             <div>
                                 <h3 className="text-base sm:text-lg font-bold text-gray-900 dark:text-white mb-4 sm:mb-6 flex items-center gap-2">
                                     <span className="w-1 h-6 bg-red-600 rounded"></span>
                                     {window.i18n ? window.i18n.t('index.popularServices', { ns: 'pages' }) : 'Services populaires'}
                                 </h3>
                                 <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                     {services.length > 0 ? (
                                         services.map((service, idx) => (
                                             <ServiceCard key={idx} service={service} />
                                         ))
                                     ) : (
                                         <p className="text-gray-500 dark:text-gray-400 col-span-2">{window.i18n ? window.i18n.t('index.noServicesInCategory', { ns: 'pages' }) : 'Aucun service dans cette catégorie'}</p>
                                     )}
                                 </div>
                             </div>

                             {/* Droite : Derniers utilisateurs */}
                             <div>
                                 <h3 className="text-base sm:text-lg font-bold text-gray-900 dark:text-white mb-4 sm:mb-6 flex items-center gap-2">
                                     <span className="w-1 h-6 bg-red-600 rounded"></span>
                                     {window.i18n ? window.i18n.t('index.newProviders', { ns: 'pages' }) : 'Nouveaux prestataires'}
                                 </h3>
                                 <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                     {users.length > 0 ? (
                                         users.map((user, idx) => (
                                             <UserCard key={idx} user={user} />
                                         ))
                                     ) : (
                                         <p className="text-gray-500 dark:text-gray-400 col-span-2">{window.i18n ? window.i18n.t('index.noProvidersInCategory', { ns: 'pages' }) : 'Aucun prestataire dans cette catégorie'}</p>
                                     )}
                                 </div>
                             </div>
                         </div>
                     </div>
                 );
             };

             useEffect(() => {
                 loadCategories();
             }, []);

             const loadCategories = async () => {
                 try {
                     const response = await fetch(`${BASE_URL}/api/home.php`);
                     const data = await response.json();
                     if (data.success) {
                         setCategories(data.data);
                     }
                 } catch (error) {
                     console.error('Erreur chargement catégories:', error);
                 } finally {
                     setLoading(false);
                 }
             };

             if (loading) {
                 return <div className="text-center py-20"><p className="text-gray-500 dark:text-gray-400">{window.i18n ? window.i18n.t('index.loading', { ns: 'pages' }) : 'Chargement...'}</p></div>;
             }

             return (
                 <div>
                     {categories.map((item, idx) => (
                         <CategorySection key={idx} category={item.category} services={item.services} users={item.users} />
                     ))}
                 </div>
             );
         }

         ReactDOM.createRoot(document.getElementById("categories-container")).render(<CategoriesSection />);
     </script>

     <!-- Message de confirmation de suppression de compte -->
     <?php if ($message === 'account_deleted'): ?>
     <div class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 animate-fade-in">
         <div class="bg-green-50 border-l-4 border-green-500 rounded-lg shadow-lg px-6 py-4 max-w-md">
             <div class="flex items-center">
                 <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                 </svg>
                 <div>
                     <p class="font-semibold text-green-800" data-i18n="index.accountDeleted.title" data-i18n-ns="pages">Compte supprimé avec succès</p>
                     <p class="text-sm text-green-700" data-i18n="index.accountDeleted.message" data-i18n-ns="pages">Vos données ont été définitivement supprimées.</p>
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

     </main>
     <?php include __DIR__ . '/../includes/Footer.php';?>
</body>
</html>