# 📁 Scripts de traduction automatique

Ce dossier contient les scripts pour traduire automatiquement les fichiers JSON du français vers l'anglais en utilisant l'API DeepL.

---

## 📄 Fichiers

### `translate.php` (⭐ Recommandé)
Script PHP pour traduire automatiquement les fichiers JSON.

**Avantages :**
- ✅ Fonctionne directement avec PHP (déjà installé sur votre serveur)
- ✅ Pas besoin de Node.js
- ✅ Simple à utiliser

**Utilisation :**
```bash
# 1. Éditer le fichier et ajouter votre clé API DeepL
# Ligne 10 : define('DEEPL_API_KEY', 'VOTRE_CLE_ICI');

# 2. Lancer le script
php translate.php
```

---

### `translate.js`
Script Node.js (alternative au script PHP).

**Utilisation :**
```bash
# 1. Installer Node.js si nécessaire
# 2. Configurer la clé API
export DEEPL_API_KEY="votre_clé_api"

# 3. Lancer
node translate.js
```

---

## 🔑 Obtenir une clé API DeepL (Gratuit)

1. Aller sur https://www.deepl.com/pro-api
2. Créer un compte **gratuit** (500 000 caractères/mois)
3. Récupérer votre clé API dans votre tableau de bord
4. Copier la clé dans le script

---

## ⚙️ Fonctionnement

Les scripts :
1. Lisent tous les fichiers JSON du dossier `/public/locales/fr/`
2. Traduisent chaque texte en anglais via DeepL
3. Créent les fichiers correspondants dans `/public/locales/en/`
4. Préservent la structure JSON exacte

---

## 📝 Fichiers traduits

```
public/locales/
├── fr/
│   ├── common.json      →  Traduit vers  →  en/common.json
│   ├── settings.json    →  Traduit vers  →  en/settings.json
│   ├── auth.json        →  Traduit vers  →  en/auth.json
│   ├── dashboard.json   →  Traduit vers  →  en/dashboard.json
│   └── pages.json       →  Traduit vers  →  en/pages.json
```

---

## ⚠️ Important

- **Les traductions actuelles en anglais ont été faites manuellement** et sont déjà complètes
- **Ces scripts sont facultatifs** : utilisez-les uniquement si vous ajoutez de nouvelles clés
- **Vérifiez toujours** les traductions automatiques (DeepL n'est pas parfait)
- **Limite gratuite** : 500 000 caractères/mois sur DeepL

---

## 🚀 Quand utiliser ces scripts ?

Utilisez ces scripts quand vous :
- Ajoutez de nouvelles pages et de nouveaux textes
- Modifiez des textes existants
- Voulez mettre à jour rapidement toutes les traductions

**Note :** Les fichiers anglais actuels sont déjà traduits et fonctionnels !

---

## 💡 Alternative : Traduction manuelle

Vous pouvez aussi traduire manuellement en éditant directement :
- `/public/locales/fr/pages.json`
- `/public/locales/en/pages.json`

C'est souvent plus rapide pour quelques textes !
