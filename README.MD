# 🚀 Novatis – Marketplace Freelance

**Novatis** est une plateforme moderne de mise en relation entre **freelances** et **clients**, inspirée de sites comme **Fiverr** et **Malt**.  
L’objectif est de créer un espace simple, fiable et transparent où les freelances peuvent proposer leurs services et où les entreprises/particuliers peuvent trouver rapidement le bon prestataire.

---

## ✨ Fonctionnalités prévues

- 👤 **Gestion des profils**
  - Création et personnalisation de profils freelances et clients
  - Portfolio, compétences, expériences

- 🔍 **Recherche avancée**
  - Par compétences, budget, localisation, mots-clés

- 💼 **Offres & Services**
  - Publication d’annonces côté freelance
  - Demandes de missions côté client

- 💬 **Messagerie interne**
  - Communication directe entre freelances et clients

- ⭐ **Système d’avis & évaluations**
  - Notation après chaque mission pour instaurer la confiance

- 💳 **Paiements sécurisés**
  - Intégration Stripe / PayPal
  - Escrow pour sécuriser les transactions

- 📊 **Tableau de bord**
  - Suivi des projets, revenus (freelance) et dépenses (client)

---

## 💰 Modèle économique

- La plateforme prend une **commission fixe de 5 %** sur chaque transaction.  
- Exemple :  
  - Un freelance facture **1 000 €** → la plateforme prélève **50 €**, le freelance touche **950 €**.  
- Ce modèle est :
  - ✔️ Plus compétitif que **Fiverr** (20 % côté freelance)  
  - ✔️ Plus simple que **Malt** (10 % puis 5 % après 6 mois)  
- Option future : ajout de **frais côté client** pour diversifier les revenus.

---

## 🛠️ Stack technique

- **Frontend :**
  - **HTML5 / CSS3**
  - **JavaScript (vanilla)**
  - **TailwindCSS** via CDN
  - **React** via CDN

- **Backend :**
  - **PHP** (logique serveur et API)

- **Base de données :**
  - **SQL** (MySQL / MariaDB)

---

## 📂 Structure du projet

/public → Contient tout le site (fichiers visibles)
/include → Fichiers PHP réutilisables (header, footer, navbar)
/config → Configuration du projet (BDD, sécurité, constantes)
/database → Scripts SQL (tables et données de test)
/docs → Documentation, schémas, maquettes

--- 

## 🚧 Roadmap – Novatis

### Phase 1 : Préparation & Base technique
- [ ] Définir l’identité visuelle (logo, couleurs, design system)  
- [ ] Créer les maquettes UI/UX (Figma ou autre outil)  
- [ ] Mettre en place la structure du projet : `/public`, `/include`, `/config`, `/database`  
- [ ] Créer la base de données initiale (`users`, `services`, `messages`, `transactions`)  
- [ ] Développer un système d’includes réutilisables (header, navbar, footer)  

---

### Phase 2 : Authentification & Profils
- [ ] Système d’inscription (freelance & client)  
- [ ] Système de connexion avec sessions sécurisées (PHP + SQL)  
- [ ] Page de gestion de profil (photo, bio, compétences, portfolio)  
- [ ] Paramètres de sécurité (hashage mots de passe, protection CSRF, validations)  

---

### Phase 3 : Gestion des services & recherche
- [ ] Page de création/publication d’un service (freelance)  
- [ ] Liste des services disponibles (avec pagination)  
- [ ] Moteur de recherche (par mots-clés, compétences, prix, localisation)  
- [ ] Filtres avancés côté client (tri par prix, popularité, notes)  

---

### Phase 4 : Communication & Collaboration
- [ ] Messagerie interne entre freelance et client  
- [ ] Notifications (nouveau message, nouvelle commande, avis reçu)  
- [ ] Système de suivi de projet (états : en attente, en cours, terminé)  

---

### Phase 5 : Paiements & Transactions
- [ ] Intégration Stripe / PayPal pour paiements sécurisés  
- [ ] Mise en place d’un système d’**escrow** (argent bloqué jusqu’à validation de la mission)  
- [ ] Historique des transactions (côté freelance et client)  
- [ ] Calcul automatique de la commission (5 %)  

---

### Phase 6 : Avis & Réputation
- [ ] Notation après chaque mission (étoiles + commentaire)  
- [ ] Moyenne visible sur le profil freelance  
- [ ] Classement des freelances par popularité/note  

---

### Phase 7 : Tableau de bord & Statistiques
- [ ] Dashboard freelance : revenus, projets en cours, messages  
- [ ] Dashboard client : dépenses, services achetés, favoris  
- [ ] Graphiques simples (ex : progression des revenus)  

---

### Phase 8 : Lancement bêta
- [ ] Déploiement sur un serveur en ligne (OVH, Hostinger ou autre)  
- [ ] Tests utilisateurs fermés (20-50 testeurs)  
- [ ] Correction des bugs et amélioration UX  

---

### Phase 9 : Lancement officiel
- [ ] Ouverture publique  
- [ ] Mise en place d’un système de support (FAQ, contact, ticket)  
- [ ] Communication (réseaux sociaux, LinkedIn, campagnes ciblées)  

---

### Phase 10 : Scalabilité & Optimisations
- [ ] Optimisation performance (cache, requêtes SQL, CDN)  
- [ ] Sécurité avancée (anti-injection SQL, audit sécurité)  
- [ ] Système de plans premium (freelance mis en avant, options clients)  
- [ ] Mobile-friendly (responsive complet + PWA si besoin)  
