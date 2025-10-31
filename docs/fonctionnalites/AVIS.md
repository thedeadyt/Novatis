# ⭐ Avis et Évaluations

Documentation complète du système d'avis et d'évaluations des prestataires.

---

## 📋 Vue d'ensemble

Le système d'avis de Novatis permet aux clients d'évaluer les prestataires après une interaction ou une commande. Les utilisateurs peuvent laisser une note de 1 à 5 étoiles avec un commentaire détaillé. Le système inclut la modération des avis, les réponses des prestataires, et l'affichage de statistiques d'évaluation. Les avis sont visibles sur les profils des prestataires et dans les listes de recherche.

---

## ✨ Fonctionnalités

### 1. Création d'Avis

**Conditions :**
- Utilisateur authentifié et client
- Commande complétée (si applicable)
- Un avis par client par prestataire
- Délai minimum respecté après la commande

**Page :** `/avis/nouveau?prestataire_id=XXX`

**Formulaire :**
- Note de 1 à 5 étoiles (clic ou survol)
- Titre de l'avis (requis)
- Commentaire texte (requis, minimum 20 caractères)
- Évaluations spécifiques (optionnel) :
  - Qualité du service
  - Respect des délais
  - Communication
  - Rapport qualité-prix

**Code exemple :**
```javascript
// Créer un avis
const createReview = async (reviewData) => {
  const response = await fetch(`${BASE_URL}/api/reviews/create.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(reviewData)
  });
  return await response.json();
};
```

### 2. Affichage des Avis

**Localisation :**
- Profil du prestataire
- Pages de détails de services
- Listes de recherche (note moyenne)
- Historique des commandes

**Informations affichées :**
- Note avec étoiles
- Titre et commentaire
- Nom et avatar du client
- Date de publication
- Nombre de consultations utiles
- Réponse du prestataire (le cas échéant)

### 3. Statistiques d'Évaluation

**Affichage :**
- Note moyenne globale
- Nombre total d'avis
- Distribution des notes (5, 4, 3, 2, 1 étoiles)
- Pourcentage pour chaque catégorie
- Évolution dans le temps (graphique)

**Calcul :**
- Moyenne pondérée des notes
- Exclusion des avis signalés/supprimés
- Mise à jour quotidienne

### 4. Réponses aux Avis

**Permissions :**
- Uniquement le prestataire concerné
- Dans les 30 jours après l'avis
- Une réponse par avis

**Fonctionnalités :**
- Formulaire de réponse
- Notification au client
- Affichage attaché à l'avis
- Édition/suppression limitée

**Code exemple :**
```javascript
// Répondre à un avis
const respondToReview = async (reviewId, response) => {
  return await fetch(`${BASE_URL}/api/reviews/respond.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ review_id: reviewId, response: response })
  });
};
```

### 5. Modération des Avis

**Signalement :**
- Avis inapproprié, offensant ou faux
- Spam ou contenu dupliqué
- Informations personnelles exposées
- Lien ou publicité

**Processus :**
1. Signalement par utilisateur ou modérateur
2. Examen par modérateur
3. Suppression ou rejet du signalement
4. Notification au propriétaire

**Avis supprimés :**
- Masqués de l'affichage public
- Conservés en base pour audit
- Les statistiques sont recalculées

### 6. Édition et Suppression

**Édition :**
- Délai : 30 jours après création
- Modification du titre, note et commentaire
- Historique des modifications

**Suppression :**
- Droit de suppression : auteur ou modérateur
- Suppression douce (archivage)
- Raison de suppression optionnelle
- Impossible de supprimer après 90 jours

---

## 🎨 Interface Utilisateur

### Formulaire de Création

**Emplacement :** `/avis/nouveau?prestataire_id=XXX`

**Composants :**
- Sélecteur de note interactif (survol + clic)
- Champ titre avec validation
- Éditeur texte avec compteur
- Sélecteurs d'évaluations spécifiques
- Boutons Annuler/Envoyer
- Aperçu en temps réel

**Design :**
- Mode clair/sombre
- Responsive (mobile, desktop)
- Validation en temps réel
- Messages d'erreur clairs

### Affichage des Avis

**Profil du prestataire :**
- En-tête avec statistiques
- Tri par date/utilité
- Filtrage par note
- Pagination

**Chaque avis affiche :**
- Avatar du client
- Nom du client
- Note avec étoiles
- Titre en gras
- Commentaire
- Date au format relatif
- Icône « Utile » avec compteur
- Réponse du prestataire (indentée)

### Réponse du Prestataire

**Affichage :**
- Indentation légère
- Avatar du prestataire
- Label « Réponse du prestataire »
- Texte de la réponse
- Date

---

## 📡 API

Les endpoints API d'avis sont documentés dans [API Avis](../api/REVIEWS.md).

**Endpoints principaux :**
- `POST /api/reviews/create.php` - Créer un avis
- `GET /api/reviews/list.php` - Lister les avis (avec filtres)
- `GET /api/reviews/stats.php?provider_id=XXX` - Statistiques
- `PUT /api/reviews/update.php` - Mettre à jour un avis
- `DELETE /api/reviews/delete.php` - Supprimer un avis
- `POST /api/reviews/respond.php` - Répondre à un avis
- `POST /api/reviews/report.php` - Signaler un avis
- `POST /api/reviews/helpful.php` - Marquer comme utile

---

## 🐛 Dépannage

### Problèmes Courants

#### 1. Impossible de créer un avis

**Causes possibles :**
- Utilisateur non authentifié
- Avis déjà existant pour ce prestataire
- Commande non complétée
- Délai minimal non respecté

**Solution :**
```bash
# Vérifier les conditions
curl -X GET "http://localhost/Novatis/public/api/reviews/can-create.php?provider_id=XXX"
```

#### 2. Les statistiques d'avis sont incorrectes

**Causes possibles :**
- Cache non mis à jour
- Avis signalés non exclus
- Calcul de moyenne erroné

**Solutions :**
```sql
-- Recalculer les statistiques
SELECT provider_id, AVG(rating) as avg_rating, COUNT(*) as review_count
FROM reviews
WHERE status = 'approved' AND deleted_at IS NULL
GROUP BY provider_id;

-- Vider le cache
FLUSH CACHE;
```

#### 3. Les réponses du prestataire ne s'affichent pas

**Vérifications :**
- Réponse correctement enregistrée
- Permissions de lecture
- Cache à rafraîchir

#### 4. Les avis signalés restent visibles

**Solutions :**
- Vérifier l'état dans la base de données
- Redémarrer le service d'affichage
- Vider le cache

#### 5. Impossible de modifier un avis

**Causes possibles :**
- Délai de 30 jours dépassé
- Utilisateur non authentifié
- Permission insuffisante

---

## 📚 Ressources

- [Documentation API Avis](../api/REVIEWS.md)
- [Profil Prestataire](PROFILS.md)
- [Système de Notifications](NOTIFICATIONS.md)
- [Guide de Déploiement](../deploiement/DEPLOIEMENT.md)

---

<div align="center">

[← Retour à la Documentation](../DOCUMENTATION.md) • [API Avis →](../api/REVIEWS.md)

</div>
