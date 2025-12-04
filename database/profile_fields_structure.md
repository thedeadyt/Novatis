# Structure des champs de profil - Novatis

Basé sur la structure de la table `users` dans la base de données.

## 📋 Champs à afficher dans la section PROFIL

### Informations personnelles (obligatoires)
- ✅ **Prénom** (`firstname`) - varchar(100) - Déjà affiché
- ✅ **Nom** (`lastname`) - varchar(100) - Déjà affiché
- ✅ **Pseudo** (`pseudo`) - varchar(50) - Déjà affiché
- ✅ **Email** (`email`) - varchar(100) - Déjà affiché

### Informations de contact (optionnelles)
- ✅ **Téléphone** (`phone`) - varchar(20) - Déjà affiché
- ❌ **Téléphone vérifié** (`phone_verified`) - Indicateur à afficher (non modifiable)

### Profil public (optionnelles)
- ✅ **Bio** (`bio`) - text - Déjà affiché
- ✅ **Localisation** (`location`) - Pas dans la BDD actuellement
- ✅ **Site web** (`website`) - Pas dans la BDD actuellement
- ❌ **Avatar** (`avatar`) - varchar(255) - À ajouter (upload d'image)

### Informations système (affichage uniquement)
- ❌ **Note moyenne** (`rating`) - decimal(2,1) - À afficher en lecture seule
- ❌ **Email vérifié** (`is_verified`) - Badge à afficher
- ❌ **Statut du compte** (`account_status`) - À afficher
- ❌ **Membre depuis** (`created_at`) - À afficher en lecture seule
- ❌ **Dernière connexion** (`last_login`) - À afficher en lecture seule

### Préférences utilisateur
- ✅ **Langue** (`language` dans user_preferences) - Déjà affiché
- ✅ **Fuseau horaire** (`timezone` dans user_preferences) - Déjà affiché

## 🔧 Champs manquants dans la BDD à ajouter

```sql
ALTER TABLE users
ADD COLUMN location VARCHAR(100) DEFAULT NULL AFTER bio,
ADD COLUMN website VARCHAR(255) DEFAULT NULL AFTER location;
```

## 📝 Recommandations d'affichage

### Section 1: Informations personnelles (modifiable)
```
┌─────────────────────────────────────────┐
│ Prénom          │ Nom                   │
│ [Jean          ]│ [Dupont              ]│
├─────────────────────────────────────────┤
│ Pseudo                                  │
│ [jeandupont                            ]│
├─────────────────────────────────────────┤
│ Email                                   │
│ [jean@exemple.com] ✓ Vérifié           │
└─────────────────────────────────────────┘
```

### Section 2: Contact (modifiable)
```
┌─────────────────────────────────────────┐
│ Téléphone                               │
│ [+33 6 12 34 56 78] ⚠️ Non vérifié     │
│ [Envoyer un code de vérification]       │
└─────────────────────────────────────────┘
```

### Section 3: Profil public (modifiable)
```
┌─────────────────────────────────────────┐
│ Photo de profil                         │
│ [  Avatar  ] [Changer]                  │
├─────────────────────────────────────────┤
│ Bio                                     │
│ [Développeur freelance passionné...   ]│
│ [                                      ]│
├─────────────────────────────────────────┤
│ Localisation    │ Site web              │
│ [Paris, France ]│ [https://...         ]│
└─────────────────────────────────────────┘
```

### Section 4: Informations du compte (lecture seule)
```
┌─────────────────────────────────────────┐
│ Statut du compte: ● Actif               │
│ Note moyenne: ⭐ 4.8/5                   │
│ Membre depuis: 15 janvier 2025          │
│ Dernière connexion: Il y a 2 heures     │
└─────────────────────────────────────────┘
```

## 🎨 Améliorations suggérées

1. **Avatar/Photo de profil**
   - Ajouter un système d'upload d'image
   - Afficher l'avatar actuel
   - Possibilité de supprimer

2. **Badges de vérification**
   - Badge "Email vérifié" ✓
   - Badge "Téléphone vérifié" ✓
   - Bouton pour vérifier le téléphone

3. **Statistiques du compte**
   - Note moyenne avec étoiles
   - Nombre de services créés
   - Nombre de commandes effectuées

4. **Sécurité visuelle**
   - Indicateur de force du mot de passe
   - Dernière modification du mot de passe
   - Authentification à deux facteurs (A2F)
