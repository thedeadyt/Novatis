# 💼 Gestion des Services

Documentation du système de marketplace de services de Novatis.

---

## 📋 Vue d'ensemble

La marketplace permet aux prestataires de publier leurs services et aux clients de les découvrir, comparer et commander.

---

## ✨ Fonctionnalités

### 1. Recherche de Services

**Page :** `/Prestataires`

**Fonctionnalités de recherche :**
- **Recherche par mot-clé** : Recherche dans titres et descriptions
- **Filtrage par catégorie** : Sélection d'une ou plusieurs catégories
- **Filtrage par localisation** : Ville, département, région
- **Filtrage par prix** : Fourchette de prix min/max
- **Tri** : Pertinence, Prix, Note, Récent

**Affichage des résultats :**
- Carte du service (photo, titre, prestataire, prix, note)
- Mode liste ou grille
- Pagination
- Nombre de résultats

### 2. Catégories de Services

**Catégories principales :**
- 🏠 **Maison & Jardin** : Bricolage, jardinage, ménage
- 💻 **Informatique & Tech** : Dépannage, développement, formation
- 🎨 **Création & Design** : Graphisme, photographie, vidéo
- 📚 **Cours & Formation** : Soutien scolaire, langues, musique
- 🚗 **Transport & Déménagement** : Livraison, déménagement
- 👔 **Services Professionnels** : Comptabilité, juridique, consulting
- 🎉 **Événementiel** : Organisation, animation, traiteur
- 💪 **Sport & Bien-être** : Coaching, massage, beauté
- 🐾 **Animaux** : Garde, toilettage, dressage
- 🔧 **Réparation** : Électronique, électroménager, auto

### 3. Page de Service

**URL :** `/service?id={service_id}`

**Informations affichées :**
- Titre et description détaillée
- Photos / Galerie
- Catégorie
- Prix (fixe ou à partir de)
- Durée estimée
- Profil du prestataire
- Note et avis
- Localisation
- Disponibilités

**Actions possibles :**
- Commander le service
- Contacter le prestataire
- Ajouter aux favoris
- Partager
- Signaler

### 4. Publication de Service (Prestataires)

**Page :** `/Dashboard?section=services`

**Processus de création :**
1. Cliquer sur "Nouveau service"
2. Remplir le formulaire
3. Ajouter des photos
4. Définir le prix
5. Publier

**Champs requis :**
- Titre (60 caractères max)
- Description (500 caractères min)
- Catégorie
- Prix
- Type de tarification (fixe, horaire, sur devis)
- Localisation

**Champs optionnels :**
- Photos (jusqu'à 5)
- Durée estimée
- Disponibilités
- Options/Variantes
- Délais de réalisation

### 5. Gestion des Services

**Page :** `/Dashboard?section=my-services`

**Actions disponibles :**
- Voir la liste de ses services
- Modifier un service
- Activer/Désactiver
- Supprimer
- Dupliquer
- Voir les statistiques (vues, commandes)

**Statuts d'un service :**
- 🟢 **Actif** : Visible et commandable
- 🟡 **Brouillon** : Non publié
- 🔴 **Désactivé** : Non visible temporairement
- ❌ **Supprimé** : Supprimé définitivement

### 6. Services Prédéfinis

Novatis propose des modèles de services prêts à l'emploi par catégorie pour faciliter la création.

**Exemples :**
- "Cours particuliers de mathématiques"
- "Dépannage informatique à domicile"
- "Garde d'animaux pendant les vacances"
- "Création de logo professionnel"

**Utilisation :**
1. Parcourir les services prédéfinis
2. Sélectionner un modèle
3. Personnaliser
4. Publier

---

## 🔍 Système de Recherche

### Algorithme de Pertinence

**Critères de classement :**
1. Correspondance du mot-clé (titre > description)
2. Note du prestataire
3. Nombre de commandes réalisées
4. Proximité géographique
5. Prix
6. Fraîcheur de l'annonce

### Indexation

Les services sont indexés pour une recherche rapide :
- Full-text search sur titre et description
- Index sur catégories
- Index sur localisation
- Cache des recherches populaires

---

## 💰 Tarification

### Types de Tarification

1. **Prix fixe** : Montant unique pour le service
2. **Prix horaire** : Tarif par heure de prestation
3. **Sur devis** : Prix à définir avec le client

### Options de Prix

- **Forfaits** : Plusieurs niveaux de service (Basic, Standard, Premium)
- **Options supplémentaires** : Extras payants
- **Réductions** : Promotions temporaires

---

## 📸 Gestion des Photos

### Upload de Photos

**Formats acceptés :** JPG, PNG, WebP
**Taille max :** 5 MB par photo
**Nombre max :** 5 photos par service
**Dimensions recommandées :** 1200x800px

**Optimisation automatique :**
- Compression
- Redimensionnement
- Génération de miniatures
- Conversion WebP

### Ordre des Photos

La première photo est utilisée comme vignette principale.

---

## 📡 API

Documentation API complète : [API Services](../api/SERVICES.md)

**Endpoints principaux :**
- `GET /api/services/services.php?action=list` - Liste des services
- `GET /api/services/services.php?action=get&id={id}` - Détails d'un service
- `POST /api/services/services.php?action=create` - Créer un service
- `PUT /api/services/services.php?action=update` - Modifier un service
- `DELETE /api/services/services.php?action=delete&id={id}` - Supprimer un service
- `GET /api/services/categories.php` - Liste des catégories
- `GET /api/services/predefined_services.php` - Services prédéfinis

---

## 🐛 Dépannage

### Problèmes Courants

#### 1. Service n'apparaît pas dans les résultats

**Vérifications :**
- Service publié (statut "Actif")
- Catégorie correctement sélectionnée
- Localisation renseignée
- Mode prestataire activé

#### 2. Photos ne s'uploadent pas

**Solutions :**
- Vérifier le format (JPG, PNG, WebP)
- Vérifier la taille (max 5 MB)
- Permissions dossier `storage/uploads/services/`

```bash
chmod 755 storage/uploads/services/
```

#### 3. Recherche ne renvoie aucun résultat

**Causes possibles :**
- Mot-clé trop spécifique
- Filtres trop restrictifs
- Aucun service dans la catégorie/localisation

---

## 📚 Ressources

- [Documentation API Services](../api/SERVICES.md)
- [Gestion des Profils](PROFILS.md)
- [Système de Commandes](COMMANDES.md)

---

<div align="center">

[← Retour à la Documentation](../DOCUMENTATION.md) • [API Services →](../api/SERVICES.md)

</div>
