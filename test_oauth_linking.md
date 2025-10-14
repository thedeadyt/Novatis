# Test de liaison de multiples comptes OAuth

## Problème corrigé
Le système créait un nouveau compte au lieu de lier les services OAuth à un compte existant lorsque l'utilisateur était déjà connecté.

## Modifications apportées

### 1. **callback.php** - Nouvelle logique de priorité
```
PRIORITÉ 1 : Utilisateur déjà connecté
  → Lie le nouveau provider au compte actuel
  → Vérifie que le provider n'est pas déjà lié à un autre compte

PRIORITÉ 2 : Connexion OAuth existante
  → Connecte l'utilisateur correspondant
  → Met à jour les tokens

PRIORITÉ 3 : Email existant dans users
  → Lie le compte OAuth à l'utilisateur existant
  → Connecte l'utilisateur

PRIORITÉ 4 : Nouveau compte
  → Crée un nouveau compte
  → Lie le provider
```

### 2. **disconnect.php** - Nouvelle API de déconnexion
- Vérifie que l'utilisateur est connecté
- Empêche la déconnexion si c'est la seule méthode de connexion et qu'il n'y a pas de mot de passe
- Supprime la connexion OAuth de la base de données

### 3. **Parametres.php** - Fonction JavaScript `disconnectOAuth()`
- Confirmation avant déconnexion
- Appel AJAX vers l'API
- Rechargement de la page en cas de succès

## Test manuel

### Scénario 1 : Lier Google puis Microsoft (utilisateur connecté)
1. Se connecter avec un compte normal (email/password)
2. Aller dans Paramètres → Intégrations
3. Cliquer sur "Connecter" pour Google
4. Autoriser l'accès Google
5. ✅ Le compte Google devrait être lié SANS vous déconnecter
6. Cliquer sur "Connecter" pour Microsoft
7. Autoriser l'accès Microsoft
8. ✅ Le compte Microsoft devrait être lié SANS vous déconnecter
9. Vérifier que les 2 providers sont affichés comme "Connecté"

### Scénario 2 : Déconnexion d'un provider
1. Dans Paramètres → Intégrations
2. Cliquer sur "Déconnecter" pour Google
3. Confirmer
4. ✅ Le compte Google devrait être déconnecté
5. ✅ Le compte Microsoft devrait rester connecté

### Scénario 3 : Protection contre le blocage
1. Créer un compte via OAuth uniquement (sans mot de passe)
2. Dans Paramètres → Intégrations
3. Essayer de déconnecter l'unique provider
4. ✅ Devrait afficher un message d'erreur empêchant la déconnexion

## Vérification en base de données

### Table oauth_connections
```sql
SELECT user_id, provider, email, created_at
FROM oauth_connections
WHERE user_id = [VOTRE_USER_ID];
```

Devrait montrer plusieurs lignes avec le même `user_id` mais des `provider` différents.

### Table users
```sql
SELECT id, email, firstname, lastname
FROM users
WHERE id = [VOTRE_USER_ID];
```

Devrait montrer UN SEUL utilisateur, pas de duplication.

## Messages attendus

- Liaison réussie : "Compte Google lié avec succès à votre profil !"
- Déconnexion réussie : "Connexion Google déconnectée avec succès"
- Protection : "Impossible de supprimer cette connexion. Vous devez définir un mot de passe..."

## En cas de problème

### Si vous êtes toujours déconnecté
1. Vérifier que vous êtes bien connecté avant de cliquer sur "Connecter"
2. Vérifier dans callback.php ligne 48 que `$_SESSION['user_id']` est défini
3. Vérifier les logs d'erreur PHP

### Si un compte est créé en double
1. Vérifier que la session est active
2. Vérifier que `session_start()` est bien appelé dans config.php
3. Supprimer les comptes en double manuellement

## Points de sécurité

✅ Empêche la liaison d'un provider déjà utilisé par un autre compte
✅ Empêche de se bloquer sans moyen de connexion
✅ Vérification CSRF avec state OAuth
✅ Messages d'erreur clairs pour l'utilisateur
