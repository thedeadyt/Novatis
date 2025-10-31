# 👤 Gestion des Profils

Documentation du système de gestion des profils utilisateurs et prestataires.

---

## 📋 Vue d'ensemble

Novatis permet deux types de profils :
- **Utilisateurs** : Clients recherchant des services
- **Prestataires** : Professionnels offrant des services

Chaque utilisateur peut avoir un profil client ET prestataire.

---

## ✨ Fonctionnalités

### 1. Profil Utilisateur

**Page :** `/profil?id={user_id}`

**Informations affichées :**
- Photo de profil
- Nom et prénom
- Adresse email
- Date d'inscription
- Biographie
- Localisation
- Liens sociaux
- Statistiques (commandes, avis laissés)

**Actions possibles :**
- Modifier son propre profil
- Voir les profils d'autres utilisateurs
- Ajouter aux favoris (prestataires)
- Contacter via messagerie
- Signaler un profil

### 2. Profil Prestataire

**Page :** `/profil?id={provider_id}`

**Informations supplémentaires :**
- Services proposés
- Portfolio / Réalisations
- Catégories de services
- Note moyenne et avis
- Tarifs et disponibilités
- Expérience professionnelle
- Certifications
- Statistiques (commandes réalisées, taux de satisfaction)

### 3. Édition du Profil

**Page :** `/Parametres?section=profile`

**Champs modifiables :**

#### Informations générales
- Photo de profil
- Nom et prénom
- Email (avec vérification)
- Téléphone
- Date de naissance
- Genre
- Adresse
- Code postal et ville
- Pays

#### Biographie
- Description personnelle (500 caractères max)
- Présentation professionnelle (pour prestataires)

#### Liens sociaux
- Site web
- LinkedIn
- Facebook
- Twitter
- Instagram

### 4. Devenir Prestataire

**Page :** `/Parametres?section=provider`

**Processus :**
1. Activer le mode prestataire
2. Compléter les informations professionnelles
3. Ajouter les services proposés
4. Renseigner les tarifs
5. Validation du profil

**Informations requises :**
- Catégories de services
- Description professionnelle
- Expérience
- Portfolio (optionnel)
- Tarifs et disponibilités

### 5. Portfolio / Réalisations

**Fonctionnalités :**
- Ajout de photos de réalisations
- Description de chaque projet
- Organisation par catégorie
- Mise en avant des meilleurs travaux

**Formats acceptés :**
- Images : JPG, PNG, WebP
- Taille max : 5 MB par image
- Dimensions recommandées : 1200x800px

---

## 🔒 Confidentialité

### Visibilité du Profil

**Paramètres disponibles :**
- Profil public / privé
- Masquer l'email
- Masquer le téléphone
- Masquer la localisation exacte

**Par défaut :**
- Profil utilisateur : Public (nom, photo, avis)
- Email et téléphone : Privés
- Localisation : Ville uniquement

### Données Partagées

**Visible par tous :**
- Nom et prénom
- Photo de profil
- Biographie
- Services proposés (prestataires)
- Avis et notes

**Visible uniquement après contact :**
- Email
- Téléphone
- Adresse complète

---

## 🎨 Interface Utilisateur

### Page de Profil

**Sections :**
1. **En-tête** : Photo, nom, note, localisation
2. **À propos** : Biographie, informations
3. **Services** : Liste des services (prestataires)
4. **Portfolio** : Galerie de réalisations
5. **Avis** : Évaluations des clients
6. **Contact** : Boutons d'action (message, favoris)

**Actions rapides :**
- Envoyer un message
- Ajouter aux favoris
- Partager le profil
- Signaler

### Édition du Profil

**Emplacement :** `public/pages/Parametres.php`

**Interface :**
- Formulaire avec sections dépliables
- Upload d'image avec prévisualisation
- Validation en temps réel
- Sauvegarde automatique

---

## 📡 API

Documentation API complète : [API Profils](../api/PROFILS.md)

**Endpoints principaux :**
- `GET /api/profil/get.php?id={user_id}` - Récupérer un profil
- `PUT /api/profil/update.php` - Mettre à jour son profil
- `POST /api/profil/upload-photo.php` - Upload photo de profil
- `POST /api/profil/portfolio/add.php` - Ajouter réalisation
- `GET /api/profil/stats.php?id={user_id}` - Statistiques

---

## 🐛 Dépannage

### Problèmes Courants

#### 1. Upload de photo échoue

**Solutions :**
- Vérifier la taille (max 5 MB)
- Format accepté : JPG, PNG, WebP
- Vérifier les permissions du dossier `storage/uploads/profiles/`

```bash
# Donner les permissions
chmod 755 storage/uploads/profiles/
```

#### 2. Modifications non sauvegardées

**Vérifications :**
- Tous les champs obligatoires remplis
- Format email valide
- Vérifier les logs pour erreurs SQL

#### 3. Profil n'apparaît pas dans les recherches

**Causes possibles :**
- Profil incomplet
- Mode prestataire non activé
- Aucun service publié

---

## 📚 Ressources

- [Documentation API Profils](../api/PROFILS.md)
- [Gestion des Services](SERVICES.md)
- [Paramètres Utilisateur](PARAMETRES.md)

---

<div align="center">

[← Retour à la Documentation](../DOCUMENTATION.md) • [API Profils →](../api/PROFILS.md)

</div>
