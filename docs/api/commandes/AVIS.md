# API Avis et Évaluations

Documentation de l'API Avis et Évaluations de Novatis.

---

## Vue d'ensemble

L'API Avis permet aux utilisateurs de laisser des évaluations après la réalisation d'une commande. Les avis incluent une note de 1 à 5 étoiles et un commentaire optionnel.

**Base URL :** `/api/orders/`

---

## Authentification

Tous les endpoints de cette API nécessitent une authentification utilisateur.

---

## Endpoints

### 1. Lister les avis

**Méthode :** `GET`
**URL :** `/api/orders/reviews.php`
**Authentification :** Requise

Récupère tous les avis laissés par ou pour l'utilisateur connecté.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| order_id | integer | Non | Pour récupérer l'avis d'une commande spécifique |

**Exemple de requête (tous les avis) :**

```javascript
const response = await fetch('/api/orders/reviews.php', {
  method: 'GET'
});
const data = await response.json();
```

**Exemple de requête (avis d'une commande) :**

```javascript
const response = await fetch('/api/orders/reviews.php?order_id=42', {
  method: 'GET'
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "reviews": [
    {
      "id": 1,
      "order_id": 42,
      "reviewer_id": 10,
      "reviewee_id": 15,
      "rating": 5,
      "comment": "Excellent travail ! Très professionnel et rapide.",
      "created_at": "2024-01-20 15:30:00",
      "reviewer_name": "Jean Dupont",
      "reviewee_name": "Marie Martin",
      "service_title": "Design de logo"
    }
  ]
}
```

---

### 2. Créer un avis

**Méthode :** `POST`
**URL :** `/api/orders/reviews.php`
**Authentification :** Requise

Crée un nouvel avis pour une commande complétée. L'utilisateur doit être l'acheteur ou le vendeur.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| order_id | integer | Oui | ID de la commande à évaluer |
| rating | integer | Oui | Note de 1 à 5 étoiles |
| comment | string | Non | Commentaire détaillé (optionnel) |

**Validations :**
- La commande doit exister
- L'utilisateur connecté doit être l'acheteur ou le vendeur
- La note doit être entre 1 et 5
- L'utilisateur ne peut laisser qu'un seul avis par commande

**Exemple de requête :**

```javascript
const response = await fetch('/api/orders/reviews.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    order_id: 42,
    rating: 5,
    comment: 'Excellent travail ! Les délais ont été respectés et la qualité est impeccable.'
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Évaluation ajoutée avec succès"
}
```

**Réponse (erreur) :**

```json
{
  "success": false,
  "error": "Vous avez déjà évalué cette commande"
}
```

**Codes d'erreur possibles :**
- Données manquantes (order_id, rating requis)
- La note doit être entre 1 et 5
- Commande non trouvée ou accès refusé
- Vous avez déjà évalué cette commande

---

## Système de notation

### Échelle de 1 à 5

| Note | Signification |
|------|---------------|
| 1 | Très insatisfait |
| 2 | Insatisfait |
| 3 | Satisfait |
| 4 | Très satisfait |
| 5 | Excellent |

### Calcul de la note moyenne

La note moyenne d'un utilisateur est calculée automatiquement :
```
Note moyenne = (Somme des notes) / (Nombre d'avis)
```

La note moyenne est affichée sur le profil de l'utilisateur et influence sa réputation.

---

## Cas d'usage courants

### Évaluer un prestataire après livraison

```javascript
// Après que le vendeur a marqué la commande comme "delivered"
// L'acheteur reçoit une notification et peut évaluer

const response = await fetch('/api/orders/reviews.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    order_id: 42,
    rating: 5,
    comment: 'Magnifique ! Je suis très satisfait du logo'
  })
});

const data = await response.json();
if (data.success) {
  console.log('Avis enregistré avec succès');
  // Rediriger vers le profil du prestataire
}
```

### Consulter les avis reçus

```javascript
const response = await fetch('/api/orders/reviews.php', {
  method: 'GET'
});
const data = await response.json();

// Filtrer les avis reçus (où l'utilisateur est reviewee_id)
const receivedReviews = data.reviews.filter(r => r.reviewee_id === currentUser.id);

// Calculer la note moyenne
const avgRating = receivedReviews.reduce((sum, r) => sum + r.rating, 0) / receivedReviews.length;

console.log(`Note moyenne : ${avgRating.toFixed(1)}/5`);
```

---

## Flux d'évaluation typique

1. **Commande créée** : `pending`
2. **Acceptée et commencée** : `in_progress`
3. **Livrée** : `delivered`
   - Notification envoyée à l'acheteur
   - Message automatique demandant l'évaluation
4. **Évaluation laissée** :
   - Notification envoyée au prestataire
   - Note moyenne mise à jour
5. **Complétée** : `completed`

---

## Protection contre les abus

- Un utilisateur ne peut laisser qu'un seul avis par commande
- Les avis ne peuvent être laissés que pour les commandes auxquelles l'utilisateur a participé
- Les avis contiennent le nom du revieweur (transparent)

---

## Impacts des avis

### Sur le profil du prestataire
- Note moyenne affichée
- Nombre d'avis reçus
- Derniers avis visibles

### Sur le système de recommandation
- Les prestataires avec les meilleures notes sont favorisés
- Les avis aident les nouveaux clients à faire un choix

### Sur les statistiques
- Contribuent à la réputation globale
- Affectent le classement des prestataires

---

## Codes d'erreur HTTP

| Code | Message | Description |
|------|---------|-------------|
| 200 | OK | Requête réussie |
| 400 | Bad Request | Paramètres manquants ou invalides |
| 401 | Unauthorized | Authentification requise |
| 403 | Forbidden | Accès refusé (pas partie de la commande) |
| 404 | Not Found | Commande introuvable |
| 405 | Method Not Allowed | Méthode HTTP non autorisée |
| 500 | Server Error | Erreur serveur |

---

## Notifications associées

Lorsqu'un avis est créé :
1. Une notification est créée pour la personne évaluée
2. La notification indique la note et l'auteur
3. Un lien vers le profil est fourni

---

## Exemple de réponse détaillée

```json
{
  "id": 1,
  "order_id": 42,
  "reviewer_id": 10,
  "reviewee_id": 15,
  "rating": 5,
  "comment": "Excellent travail ! Très professionnel, respecte les délais.",
  "created_at": "2024-01-20 15:30:00",
  "reviewer_name": "Jean Dupont",
  "reviewee_name": "Marie Martin",
  "order_title": "Logo Design Project",
  "service_title": "Design de logo"
}
```

---

## Recommandations pour l'interface

1. **Affichage des étoiles** : Utiliser des icones visuelles pour la note
2. **Filtrage** : Permettre de filtrer par note (5 étoiles, 4+, etc.)
3. **Tri** : Trier par date ou pertinence
4. **Contexte** : Afficher le service et la commande associée
5. **Authentification** : Vérifier que l'utilisateur ne peut évaluer qu'une fois

---

## Ressources

- [Documentation API](API.md)
- [Commandes (COMMANDES.md)](COMMANDES.md)
- [Profils (PROFILS.md)](PROFILS.md)
- [Documentation Complète](../DOCUMENTATION.md)

---

<div align="center">
[← Notifications](NOTIFICATIONS.md) • [Paramètres →](PARAMETRES.md)
</div>
