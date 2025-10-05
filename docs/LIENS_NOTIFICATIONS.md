# 🔗 Liens Directs dans les Emails - Novatis

## 📍 Comment ça marche ?

Chaque email de notification contient un **bouton CTA** qui redirige directement vers l'onglet concerné du Dashboard.

### Exemple d'Email

```
┌─────────────────────────────────────────┐
│   🔔 Notification Novatis              │
├─────────────────────────────────────────┤
│ Bonjour John Doe,                       │
│                                         │
│ ┌───────────────────────────────────┐  │
│ │ 📦 Nouvelle commande reçue        │  │
│ │                                   │  │
│ │ Vous avez reçu une nouvelle       │  │
│ │ commande d'un montant de 50€.     │  │
│ └───────────────────────────────────┘  │
│                                         │
│        ┌──────────────────┐             │
│        │ Voir les détails │  ← BOUTON  │
│        └──────────────────┘             │
│          ↓                              │
│  http://localhost/Novatis/public/      │
│  pages/Dashboard?tab=orders             │
│                                         │
└─────────────────────────────────────────┘
```

## 🎯 Mapping des Liens

### Tableau de Correspondance

| Type de Notification | Destinataire | Lien Email | Onglet Ouvert |
|---------------------|--------------|------------|---------------|
| **Nouvelle commande** | Vendeur | `Dashboard?tab=orders` | 💼 Mes Ventes |
| **Commande créée** | Acheteur | `Dashboard?tab=purchases` | 🛒 Mes Achats |
| **Commande acceptée** | Acheteur | `Dashboard?tab=purchases` | 🛒 Mes Achats |
| **Commande livrée** | Acheteur | `Dashboard?tab=purchases` | 🛒 Mes Achats |
| **Commande annulée** | Acheteur | `Dashboard?tab=purchases` | 🛒 Mes Achats |
| **Commande annulée** | Vendeur | `Dashboard?tab=orders` | 💼 Mes Ventes |
| **Nouveau message** | Tous | `Dashboard?tab=messages` | 💬 Messages |
| **Service modifié** | Client | `Dashboard?tab=services` | ⚙️ Mes Services |
| **Connexion détectée** | Utilisateur | `Parametres` | 🔐 Paramètres |
| **A2F activée** | Utilisateur | `Parametres` | 🔐 Paramètres |
| **A2F désactivée** | Utilisateur | `Parametres` | 🔐 Paramètres |
| **Rappel paiement** | Acheteur | `Dashboard?tab=purchases` | 🛒 Mes Achats |
| **Retard paiement** | Acheteur | `Dashboard?tab=purchases` | 🛒 Mes Achats |
| **Retard paiement** | Vendeur | `Dashboard?tab=orders` | 💼 Mes Ventes |

## 🔧 Implémentation Technique

### Structure du Lien

Tous les liens suivent ce format :

```
http://localhost/Novatis/public/pages/[PAGE]?tab=[ONGLET]
```

**Exemples :**
```
http://localhost/Novatis/public/pages/Dashboard?tab=orders
http://localhost/Novatis/public/pages/Dashboard?tab=purchases
http://localhost/Novatis/public/pages/Dashboard?tab=messages
http://localhost/Novatis/public/pages/Parametres
```

### Code dans NotificationService.php

```php
// Nouvelle commande (vendeur)
$notificationService->notifyNewOrder($sellerId, $orderId, $price);
// → Lien : "/Novatis/public/pages/Dashboard?tab=orders"

// Commande créée (acheteur)
$notificationService->create(
    $buyerId,
    'order',
    'Commande créée',
    "Votre commande a été créée avec succès",
    "/Novatis/public/pages/Dashboard?tab=purchases"
);

// Nouveau message
$notificationService->notifyNewMessage($userId, $senderName, $conversationId);
// → Lien : "/Novatis/public/pages/Dashboard?tab=messages"
```

### Code dans EmailService.php

Le lien est converti en URL complète dans le template email :

```php
if ($link) {
    $fullLink = 'http://localhost' . $link;
    $buttonHtml = '<a href="' . $fullLink . '" class="button">Voir les détails</a>';
}
```

## 📱 Comportement Utilisateur

### Scénario 1 : Nouvelle Commande

1. **Vendeur reçoit un email** : "Nouvelle commande reçue"
2. **Clique sur le bouton** "Voir les détails"
3. **Redirigé vers** : `Dashboard?tab=orders`
4. **Dashboard s'ouvre** avec l'onglet "Mes Ventes" actif
5. **Voit immédiatement** sa nouvelle commande en haut de la liste

### Scénario 2 : Nouveau Message

1. **Utilisateur reçoit un email** : "Nouveau message de John Doe"
2. **Clique sur le bouton** "Voir les détails"
3. **Redirigé vers** : `Dashboard?tab=messages`
4. **Dashboard s'ouvre** avec l'onglet "Messages" actif
5. **Conversation est affichée** avec le nouveau message

### Scénario 3 : Alerte Sécurité

1. **Utilisateur reçoit un email** : "Nouvelle connexion détectée"
2. **Clique sur le bouton** "Voir les détails"
3. **Redirigé vers** : `Parametres`
4. **Page Paramètres s'ouvre** directement
5. **Peut vérifier** et modifier ses paramètres de sécurité

## 🎨 Design du Bouton CTA

Le bouton dans l'email utilise ce style :

```html
<a href="{LIEN}" class="button">Voir les détails</a>

<style>
.button {
    display: inline-block;
    background: #B41200;  /* Rouge Novatis */
    color: white;
    padding: 15px 30px;
    text-decoration: none;
    border-radius: 5px;
    margin: 20px 0;
    font-weight: bold;
}
.button:hover {
    background: #E04830;  /* Rouge clair au survol */
}
</style>
```

## ✅ Avantages

1. **UX Optimale** : L'utilisateur arrive directement là où il doit agir
2. **Moins de clics** : Pas besoin de chercher l'onglet manuellement
3. **Taux d'engagement** : Plus d'utilisateurs interagissent avec les notifications
4. **Cohérence** : Tous les emails suivent le même pattern
5. **Mobile-friendly** : Les liens fonctionnent aussi sur mobile

## 🧪 Tests

Pour tester les liens :

1. **Créer une commande** → Vérifier que l'email redirige vers `Dashboard?tab=orders` (vendeur) et `Dashboard?tab=purchases` (acheteur)
2. **Envoyer un message** → Vérifier que l'email redirige vers `Dashboard?tab=messages`
3. **Se connecter** → Vérifier que l'email redirige vers `Parametres`
4. **Activer/Désactiver A2F** → Vérifier que l'email redirige vers `Parametres`

## 🔄 Mise à Jour des Liens

Si vous devez changer un lien, modifiez-le dans **NotificationService.php** :

```php
// Fichier : includes/NotificationService.php
// Lignes : 287-347

public function notifyNewOrder($userId, $orderId, $orderAmount) {
    return $this->create(
        $userId,
        'order',
        'Nouvelle commande reçue',
        "Vous avez reçu une nouvelle commande d'un montant de {$orderAmount}€.",
        "/Novatis/public/pages/Dashboard?tab=orders"  // ← Modifier ici
    );
}
```

Le changement sera automatiquement appliqué à tous les emails envoyés !

## 🌐 Production

En production, remplacer `http://localhost` par votre domaine dans **EmailService.php** :

```php
// Fichier : includes/EmailService.php
// Ligne : 220

if ($link) {
    $fullLink = 'https://novatis.com' . $link;  // ← Changer ici
    $buttonHtml = '<a href="' . $fullLink . '" class="button">Voir les détails</a>';
}
```
