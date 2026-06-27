# 📦 Gestion des Commandes

Documentation du système de commandes et de suivi des projets.

---

## 📋 Vue d'ensemble

Le système de commandes permet aux clients de commander des services et aux prestataires de les gérer jusqu'à leur réalisation.

---

## ✨ Fonctionnalités

### 1. Passer une Commande

**Depuis la page du service :**
1. Cliquer sur "Commander ce service"
2. Remplir les détails de la demande
3. Choisir les options (si disponibles)
4. Confirmer la commande

**Informations requises :**
- Description détaillée de la demande
- Date souhaitée
- Adresse de prestation (si nécessaire)
- Coordonnées de contact

### 2. Statuts des Commandes

**Cycle de vie d'une commande :**

1. 🟡 **En attente** : Commande créée, en attente de validation du prestataire
2. 🔵 **Acceptée** : Prestataire a accepté, en cours de réalisation
3. 🟢 **Terminée** : Service réalisé, en attente de validation client
4. ⭐ **Validée** : Client a validé, attente d'avis
5. ❌ **Annulée** : Commande annulée (par client ou prestataire)
6. 🚫 **Refusée** : Prestataire a refusé la commande

### 3. Gestion des Commandes (Client)

**Page :** `/Dashboard?section=orders`

**Vues disponibles :**
- Liste de toutes les commandes
- Filtrage par statut
- Recherche par prestataire ou service

**Actions possibles :**
- Voir les détails
- Envoyer un message au prestataire
- Annuler (si en attente)
- Valider (si terminée)
- Laisser un avis (si validée)
- Signaler un problème

### 4. Gestion des Commandes (Prestataire)

**Page :** `/Dashboard?section=received-orders`

**Actions possibles :**
- Accepter une commande
- Refuser avec raison
- Marquer comme terminée
- Communiquer avec le client
- Voir l'historique

**Notifications :**
- Nouvelle commande reçue
- Message du client
- Annulation d'une commande
- Validation et avis du client

### 5. Détails d'une Commande

**Page :** `/order?id={order_id}`

**Informations affichées :**
- Numéro de commande
- Date de création
- Statut actuel
- Service commandé
- Prestataire/Client (selon le rôle)
- Montant
- Description de la demande
- Date souhaitée de réalisation
- Adresse (si nécessaire)
- Historique des statuts
- Messages échangés

### 6. Communication

**Messagerie intégrée :**
- Discussion directe entre client et prestataire
- Historique complet
- Notifications en temps réel
- Pièces jointes possibles

### 7. Validation et Avis

**Après réalisation :**
1. Prestataire marque comme "Terminée"
2. Client reçoit notification
3. Client valide ou demande modifications
4. Si validé : possibilité de laisser un avis

**Système d'avis :**
- Note sur 5 étoiles
- Commentaire écrit
- Critères d'évaluation (qualité, délais, communication)
- Réponse du prestataire possible

---

## 💳 Paiement

### Options de Paiement

**Modes disponibles :**
- Paiement en ligne (carte bancaire)
- Virement bancaire
- Paiement sur place
- PayPal (à configurer)

### Sécurité

- Paiements sécurisés via stripe/PayPal
- Données bancaires non stockées
- Transactions chiffrées

### Gestion des Litiges

**Procédure :**
1. Signalement du problème
2. Médiation par l'équipe Novatis
3. Remboursement ou solution amiable
4. Clôture du litige

---

## 📧 Notifications

**Événements notifiés :**
- Nouvelle commande (prestataire)
- Commande acceptée (client)
- Commande refusée (client)
- Commande terminée (client)
- Message reçu (client/prestataire)
- Annulation (prestataire)
- Avis laissé (prestataire)

**Canaux de notification :**
- Notifications in-app
- Email
- Badges de compteur

---

## 📡 API

Documentation API complète : [API Commandes](../api/COMMANDES.md)

**Endpoints principaux :**
- `GET /api/orders/orders.php?action=list` - Liste des commandes
- `GET /api/orders/orders.php?action=get&id={id}` - Détails commande
- `POST /api/orders/orders.php?action=create` - Créer une commande
- `PUT /api/orders/orders.php?action=update-status` - Changer le statut
- `DELETE /api/orders/orders.php?action=cancel` - Annuler une commande
- `POST /api/orders/reviews.php?action=create` - Laisser un avis

---

## 🐛 Dépannage

### Problèmes Courants

#### 1. Impossible de passer commande

**Vérifications :**
- Compte client actif
- Service disponible
- Tous les champs requis remplis

#### 2. Commande n'apparaît pas

**Causes possibles :**
- Problème de synchronisation
- Erreur lors de la création
- Vérifier les logs d'erreur

#### 3. Notification non reçue

**Solutions :**
- Vérifier les paramètres de notification
- Vérifier les emails (spam)
- Activer les notifications navigateur

---

## 📚 Ressources

- [Documentation API Commandes](../api/COMMANDES.md)
- [Documentation Avis](AVIS.md)
- [Documentation Messagerie](MESSAGERIE.md)

---

<div align="center">

[← Retour à la Documentation](../DOCUMENTATION.md) • [API Commandes →](../api/COMMANDES.md)

</div>
