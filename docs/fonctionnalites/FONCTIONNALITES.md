# 🔧 Fonctionnalités de Novatis

Vue d'ensemble complète de toutes les fonctionnalités de la plateforme Novatis.

---

## 📋 Table des Matières

1. [Authentification](#-authentification)
2. [Gestion des Profils](#-gestion-des-profils)
3. [Services](#-services)
4. [Commandes](#-commandes)
5. [Messagerie](#-messagerie)
6. [Notifications](#-notifications)
7. [Avis et Évaluations](#-avis-et-évaluations)
8. [Favoris](#-favoris)
9. [Paramètres](#-paramètres)
10. [Multi-langues](#-multi-langues)
11. [Thème Clair/Sombre](#-thème-clairsombre)

---

## 🔐 Authentification

**[→ Documentation complète](AUTHENTIFICATION.md)**

Système d'authentification sécurisé avec plusieurs méthodes de connexion.

### Fonctionnalités principales :
- **Inscription et connexion classique** (email/mot de passe)
- **OAuth 2.0** : Connexion avec Google, Microsoft, GitHub
- **Authentification à deux facteurs (2FA)** : Sécurité renforcée avec TOTP
- **Vérification par email** : Validation de l'adresse email
- **Réinitialisation de mot de passe** : Récupération de compte sécurisée

### Sécurité :
- Mots de passe hashés avec Bcrypt
- Sessions sécurisées
- Protection contre brute force
- Tokens sécurisés pour la réinitialisation

### Utilisation :
- **Page d'inscription** : `/Autentification?action=register`
- **Page de connexion** : `/Autentification?action=login`
- **Configuration 2FA** : `/Parametres?section=security`

---

## 👤 Gestion des Profils

**[→ Documentation complète](PROFILS.md)**

Gestion complète des profils utilisateurs et prestataires.

### Fonctionnalités principales :
- **Profils utilisateurs** : Informations personnelles, biographie, photo
- **Profils prestataires** : Services, portfolio, expérience
- **Édition du profil** : Modification des informations
- **Portfolio** : Galerie de réalisations pour les prestataires
- **Statistiques** : Commandes, avis, taux de satisfaction

### Types de profils :
- **Client** : Recherche et commande de services
- **Prestataire** : Publication et gestion de services
- **Double profil** : Un utilisateur peut être les deux

### Utilisation :
- **Voir un profil** : `/profil?id={user_id}`
- **Éditer son profil** : `/Parametres?section=profile`
- **Devenir prestataire** : `/Parametres?section=provider`

---

## 💼 Services

**[→ Documentation complète](SERVICES.md)**

Marketplace complète pour la publication et la recherche de services.

### Fonctionnalités principales :
- **Recherche avancée** : Mots-clés, catégories, localisation, prix
- **10 catégories principales** : Maison, Informatique, Création, Cours, etc.
- **Publication de services** : Création et gestion par les prestataires
- **Services prédéfinis** : Modèles prêts à l'emploi
- **Galerie photos** : Jusqu'à 5 photos par service
- **Tarification flexible** : Prix fixe, horaire ou sur devis

### Types de tarification :
- **Prix fixe** : Montant unique
- **Prix horaire** : Tarif par heure
- **Sur devis** : À définir avec le client

### Utilisation :
- **Rechercher** : `/Prestataires`
- **Voir un service** : `/service?id={service_id}`
- **Publier un service** : `/Dashboard?section=services`

---

## 📦 Commandes

**[→ Documentation complète](COMMANDES.md)**

Système complet de gestion des commandes et suivi des projets.

### Fonctionnalités principales :
- **Création de commandes** : Commander un service en quelques clics
- **6 statuts** : En attente, Acceptée, Terminée, Validée, Annulée, Refusée
- **Gestion pour clients** : Suivi de toutes les commandes
- **Gestion pour prestataires** : Acceptation, réalisation, validation
- **Communication intégrée** : Messages directs avec le prestataire/client
- **Validation et avis** : Évaluation après réalisation

### Cycle de vie :
1. 🟡 Client crée une commande
2. 🔵 Prestataire accepte
3. 🟢 Prestataire termine
4. ⭐ Client valide et laisse un avis

### Utilisation :
- **Mes commandes** : `/Dashboard?section=orders`
- **Commandes reçues** : `/Dashboard?section=received-orders`
- **Détails** : `/order?id={order_id}`

---

## 💬 Messagerie

**[→ Documentation complète](MESSAGERIE.md)**

Système de messagerie en temps réel entre clients et prestataires.

### Fonctionnalités principales :
- **Messages directs** : Communication privée 1-à-1
- **Notifications en temps réel** : Alertes instantanées
- **Historique complet** : Toutes les conversations sauvegardées
- **Pièces jointes** : Envoi de fichiers et images
- **Indicateurs** : Lu/non lu, en ligne/hors ligne
- **Recherche** : Recherche dans les conversations

### Utilisation :
- **Accéder à la messagerie** : `/Dashboard?section=messages`
- **Envoyer un message** : Depuis un profil ou une commande
- **Notifications** : Badge sur l'icône de messagerie

---

## 🔔 Notifications

**[→ Documentation complète](NOTIFICATIONS.md)**

Système de notifications en temps réel pour tous les événements importants.

### Fonctionnalités principales :
- **Notifications in-app** : Centre de notifications dans l'interface
- **Notifications email** : Envoi automatique d'emails
- **Notifications temps réel** : Mise à jour instantanée
- **Centre de notifications** : Historique complet
- **Badge de compteur** : Nombre de notifications non lues
- **Paramétrage** : Choix des notifications à recevoir

### Types de notifications :
- **Commandes** : Nouvelle, acceptée, terminée, annulée
- **Messages** : Nouveau message reçu
- **Avis** : Nouvel avis reçu
- **Services** : Service favoris disponible
- **Système** : Mises à jour, maintenances

### Utilisation :
- **Voir les notifications** : `/notifications`
- **Paramétrer** : `/Parametres?section=notifications`

---

## ⭐ Avis et Évaluations

**[→ Documentation complète](AVIS.md)**

Système d'évaluation et de réputation pour les prestataires.

### Fonctionnalités principales :
- **Notes sur 5 étoiles** : Évaluation globale
- **Commentaires détaillés** : Avis écrits
- **Critères multiples** : Qualité, délais, communication
- **Vérification** : Avis uniquement après commande validée
- **Réponse du prestataire** : Possibilité de répondre
- **Note moyenne** : Calcul automatique et affichage

### Critères d'évaluation :
- 🎯 **Qualité** : Qualité du service rendu
- ⏱️ **Délais** : Respect des délais convenus
- 💬 **Communication** : Qualité de la communication
- 💰 **Rapport qualité/prix** : Satisfaction générale

### Utilisation :
- **Laisser un avis** : Après validation d'une commande
- **Voir les avis** : Sur le profil du prestataire
- **Gérer mes avis** : `/Dashboard?section=reviews`

---

## ❤️ Favoris

**[→ Documentation complète](FAVORIS.md)**

Système de sauvegarde de prestataires favoris.

### Fonctionnalités principales :
- **Ajouter aux favoris** : Sauvegarder ses prestataires préférés
- **Liste de favoris** : Accès rapide à tous les favoris
- **Notifications** : Alertes si nouveau service ou disponibilité
- **Organisation** : Tri et filtrage des favoris
- **Accès rapide** : Contact et commande rapides

### Utilisation :
- **Ajouter** : Clic sur l'icône ❤️ sur un profil
- **Voir mes favoris** : `/Favoris`
- **Gérer** : Retirer, contacter, commander

---

## ⚙️ Paramètres

**[→ Documentation complète](PARAMETRES.md)**

Centre de configuration de tous les paramètres utilisateur.

### Sections disponibles :
1. **Profil** : Informations personnelles, photo, biographie
2. **Sécurité** : Mot de passe, 2FA, sessions actives
3. **Notifications** : Choix des notifications à recevoir
4. **Confidentialité** : Visibilité du profil, données partagées
5. **Prestataire** : Activation mode prestataire, services
6. **Compte** : Suppression de compte, export de données

### Paramètres de sécurité :
- Changement de mot de passe
- Activation/désactivation 2FA
- Gestion des sessions actives
- Connexions OAuth liées

### Utilisation :
- **Accéder aux paramètres** : `/Parametres`
- **Navigation par sections** : `/Parametres?section={nom}`

---

## 🌍 Multi-langues

**[→ Documentation complète](MULTILANGUE.md)**

Système d'internationalisation (i18n) avec support de plusieurs langues.

### Fonctionnalités principales :
- **2 langues disponibles** : Français, Anglais
- **Changement à la volée** : Pas besoin de recharger la page
- **Détection automatique** : Langue du navigateur
- **Sauvegarde des préférences** : Mémorisation du choix
- **Traduction complète** : Interface, emails, notifications
- **i18next** : Framework de traduction moderne

### Langues supportées :
- 🇫🇷 **Français** (par défaut)
- 🇬🇧 **English**

### Ajout de langues :
Les fichiers de traduction se trouvent dans `public/locales/{lang}/translation.json`

### Utilisation :
- **Changer de langue** : Sélecteur dans le header
- **Langue par défaut** : Définie dans les paramètres

---

## 🌓 Thème Clair/Sombre

**[→ Documentation complète](THEME.md)**

Système de thème avec mode clair et mode sombre.

### Fonctionnalités principales :
- **2 thèmes** : Clair (par défaut) et Sombre
- **Basculement instantané** : Changement en temps réel
- **Sauvegarde automatique** : Préférence mémorisée
- **Détection système** : Suit les préférences de l'OS (optionnel)
- **Transition fluide** : Animation douce lors du changement
- **Couleurs adaptées** : Palette optimisée pour chaque mode

### Implémentation :
- Variables CSS pour les couleurs
- Classes Tailwind pour le mode sombre
- LocalStorage pour la persistance

### Utilisation :
- **Changer de thème** : Icône dans le header (soleil/lune)
- **Préférence système** : `/Parametres?section=appearance`

---

## 📊 Statistiques

Chaque fonctionnalité dispose de statistiques pour suivre l'activité :

### Pour les utilisateurs :
- Nombre de commandes passées
- Nombre d'avis laissés
- Prestataires favoris
- Dépenses totales

### Pour les prestataires :
- Nombre de services publiés
- Nombre de commandes reçues
- Note moyenne et avis
- Revenus générés
- Taux de satisfaction
- Taux de réponse

---

## 🔗 Intégrations

Novatis s'intègre avec plusieurs services externes :

- **OAuth** : Google, Microsoft, GitHub
- **Email** : SMTP (Gmail, Outlook, etc.)
- **Paiement** : Stripe, PayPal (à configurer)
- **Stockage** : Local ou cloud (S3, etc.)
- **Analytics** : Google Analytics (optionnel)

---

## 🐛 Support

Pour toute question sur une fonctionnalité spécifique, consultez la documentation dédiée ou contactez le support.

### Ressources :
- [Documentation complète](../DOCUMENTATION.md)
- [API](../api/API.md)
- [Déploiement](../deploiement/DEPLOIEMENT.md)
- [Dépannage](../guides/troubleshooting/TROUBLESHOOTING.md)

---

<div align="center">

**Documentation complète de toutes les fonctionnalités Novatis**

[← Retour à la Documentation](../DOCUMENTATION.md)

</div>

---

*Dernière mise à jour : Octobre 2025*
