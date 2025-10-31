# ❤️ Système de Favoris

Documentation complète du système de favoris et prestataires sauvegardés.

---

## 📋 Vue d'ensemble

Le système de favoris de Novatis permet aux utilisateurs de sauvegarder et gérer une liste personnalisée de prestataires et services préférés. Les utilisateurs peuvent ajouter/retirer des favoris avec un simple clic, consulter leur liste complète organisée, et recevoir des mises à jour sur leurs prestataires favoris. Les favoris sont synchronisés entre appareils pour les utilisateurs connectés et conservés localement sinon.

---

## ✨ Fonctionnalités

### 1. Ajouter/Retirer des Favoris

**Disponibilité :**
- Sur les pages de prestataires
- Sur les cartes de services
- Dans les listes de recherche
- Sur les profils publics

**Interaction :**
- Clic sur l'icône cœur
- Confirmation visuelle immédiate
- Animation du cœur
- Toast de notification

**Code exemple :**
```javascript
// Ajouter/Retirer un favori
const toggleFavorite = async (providerId) => {
  const response = await fetch(`${BASE_URL}/api/favorites/toggle.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ provider_id: providerId })
  });

  if (response.ok) {
    const result = await response.json();
    updateHeartIcon(providerId, result.is_favorite);
    showToast(result.message);
  }
};

// Vérifier si un élément est favori
const isFavorite = async (providerId) => {
  const response = await fetch(`${BASE_URL}/api/favorites/check.php?provider_id=${providerId}`);
  return await response.json();
};
```

### 2. Liste des Favoris

**Page :** `/Favoris`

**Fonctionnalités :**
- Affichage en grille ou liste
- Vue des cartes de prestataires
- Nombre de favoris
- Statistiques (nouveaux prestataires, évaluations)

**Informations affichées :**
- Avatar/image du prestataire
- Nom
- Catégorie
- Note moyenne
- Nombre d'avis
- Localisation
- Bouton d'actions (retirer, contacter)

### 3. Tri et Filtrage

**Options de tri :**
- Ordre d'ajout (plus récent d'abord)
- Ordre alphabétique
- Par note (meilleure d'abord)
- Par nombre d'avis
- Par activité récente

**Options de filtrage :**
- Par catégorie
- Par localisation
- Par plage de note
- Par popularité

**Code exemple :**
```javascript
// Récupérer les favoris avec filtres
const getFavorites = async (filters = {}) => {
  const params = new URLSearchParams(filters);
  const response = await fetch(`${BASE_URL}/api/favorites/list.php?${params}`);
  return await response.json();
};

// Paramètres de filtre
const getFavoritesFiltered = async () => {
  return getFavorites({
    sort: 'rating',
    category: 'electricien',
    location: 'Lyon'
  });
};
```

### 4. Gestionnaire de Favoris

**Actions :**
- Sélection multiple
- Suppression en masse
- Organiser en collections (optionnel)
- Exporter la liste
- Partager avec d'autres utilisateurs (optionnel)

### 5. Synchronisation entre Appareils

**Pour utilisateurs connectés :**
- Sauvegarde immédiate en base de données
- Synchronisation en temps réel
- Historique des modifications

**Pour utilisateurs non connectés :**
- Stockage local (IndexedDB)
- Synchronisation à la connexion
- Fusion des favoris locaux et cloud

**Code exemple :**
```javascript
// Synchroniser les favoris locaux
const syncFavoritesOnLogin = async (userId) => {
  const localFavorites = await getLocalFavorites();

  if (localFavorites.length > 0) {
    const response = await fetch(`${BASE_URL}/api/favorites/sync.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ favorites: localFavorites })
    });

    if (response.ok) {
      await clearLocalFavorites();
    }
  }
};
```

### 6. Notifications sur Favoris

**Types de notifications :**
- Nouveau prestataire similaire
- Promotion du prestataire favori
- Nouvel avis sur prestataire favori
- Réactivation d'un prestataire inactif
- Changement de tarif

**Paramètres :**
- Actif/inactif par type
- Fréquence

### 7. Favoris dans les Recherches

**Indicateur visuel :**
- Cœur rempli pour les favoris
- Badge "favori" optionnel
- Highlight distinctif

**Tri dans les résultats :**
- Option pour afficher les favoris en premier
- Priorité dans les suggestions

### 8. Statistiques des Favoris

**Pour les utilisateurs :**
- Nombre de favoris
- Distribution par catégorie
- Prestataire le plus sauvegardé
- Tendances

**Pour les prestataires :**
- Nombre de fois dans les favoris
- Tendance (croissant/décroissant)
- Visibilité accrue

---

## 🎨 Interface Utilisateur

### Page Favoris

**Emplacement :** `public/pages/Favoris.php`

**Layout :**
- **Barre supérieure :**
  - Titre "Mes Favoris"
  - Nombre de favoris
  - Boutons Vue (grille/liste)

- **Barre de filtres :**
  - Catégories (dropdown)
  - Localisation (input)
  - Tri (dropdown)
  - Recherche

- **Contenu :**
  - Grille ou liste de prestataires
  - Cartes avec informations
  - Icônes d'actions

- **Actions :**
  - Supprimer un favori (cœur)
  - Voir le profil (clic sur la carte)
  - Contacter le prestataire (bouton)

### Cœur Interactif

**États :**
- Cœur vide : non favori
- Cœur rempli : favori

**Interactions :**
- Hover : couleur secondaire
- Clic : animation de remplissage
- Disabled : pendant le traitement

**Design :**
```css
.favorite-btn {
  font-size: 1.5rem;
  cursor: pointer;
  transition: color 0.3s ease;
}

.favorite-btn:hover {
  color: #ff69b4;
}

.favorite-btn.active {
  color: #e63946;
  animation: heartBeat 0.5s ease;
}

@keyframes heartBeat {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.2); }
}
```

### Vue Vide

**Quand aucun favori :**
- Illustration d'un cœur vide
- Message "Aucun favori pour le moment"
- Bouton "Explorer les prestataires"
- Suggestions de prestataires populaires

---

## 📡 API

Les endpoints API de favoris sont documentés dans [API Favoris](../api/FAVORITES.md).

**Endpoints principaux :**
- `POST /api/favorites/toggle.php` - Ajouter/retirer un favori
- `GET /api/favorites/list.php` - Lister les favoris avec filtres
- `GET /api/favorites/check.php?provider_id=XXX` - Vérifier si favori
- `DELETE /api/favorites/delete.php?provider_id=XXX` - Retirer un favori
- `POST /api/favorites/bulk-delete.php` - Supprimer plusieurs favoris
- `POST /api/favorites/sync.php` - Synchroniser les favoris locaux
- `GET /api/favorites/stats.php` - Statistiques des favoris
- `GET /api/favorites/export.php` - Exporter en JSON/CSV

---

## 🐛 Dépannage

### Problèmes Courants

#### 1. Les favoris ne sont pas sauvegardés

**Causes possibles :**
- Utilisateur non authentifié
- IndexedDB désactivé (pour non-connectés)
- Erreur d'API
- Quota de stockage dépassé

**Solutions :**
```bash
# Vérifier les logs
tail -f storage/logs/app.log | grep "favorite"

# Vérifier la base de données
SELECT * FROM favorites WHERE user_id = 'XXX';
```

#### 2. Les favoris ne se synchronisent pas

**Vérifications :**
- Connexion réseau active
- API accessible
- Permissions utilisateur
- Contrats de service d'API

```bash
# Tester l'API
curl -X GET "http://localhost/Novatis/public/api/favorites/list.php" \
  -H "Authorization: Bearer XXX"
```

#### 3. Les favoris locaux ne fusionnent pas à la connexion

**Causes :**
- Service Worker non actif
- IndexedDB vide ou corrompue
- Erreur lors de la synchronisation

**Solutions :**
```javascript
// Forcer la synchronisation manuelle
const manualSync = async () => {
  const localFavorites = await getLocalFavorites();
  console.log('Favoris locaux:', localFavorites);

  await syncFavoritesOnLogin(currentUser.id);
};

// Appeler depuis la console
manualSync();
```

#### 4. Le cœur clignote sans raison

**Causes :**
- Race condition entre requêtes
- Cache non synchronisé
- Délai de réponse API

**Solutions :**
- Implémenter un debounce
- Ajouter un loading state
- Attendre la réponse avant mise à jour UI

#### 5. Les statistiques des favoris sont incorrectes

**Solution :**
```sql
-- Recalculer les statistiques
SELECT provider_id, COUNT(*) as favorite_count
FROM favorites
WHERE deleted_at IS NULL
GROUP BY provider_id;

-- Mettre à jour le cache
UPDATE provider_stats SET favorite_count = (
  SELECT COUNT(*) FROM favorites WHERE provider_id = providers.id AND deleted_at IS NULL
);
```

#### 6. Les favoris ne s'affichent pas sur d'autres appareils

**Vérifications :**
- Utilisateur connecté sur les deux appareils
- Même compte utilisateur
- Synchronisation activée
- Recharger la page

---

## 📚 Ressources

- [Documentation API Favoris](../api/FAVORITES.md)
- [Profil Prestataire](PROFILS.md)
- [Système de Recherche](SERVICES.md)
- [Configuration des Paramètres](PARAMETRES.md)
- [Guide de Déploiement](../deploiement/DEPLOIEMENT.md)

---

<div align="center">

[← Retour à la Documentation](../DOCUMENTATION.md) • [API Favoris →](../api/FAVORITES.md)

</div>
