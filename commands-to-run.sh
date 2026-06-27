#!/bin/bash
# Commandes à copier-coller dans le terminal SSH AlwaysData
# Connectez-vous d'abord avec: ssh alex2pro@ssh-alex2pro.alwaysdata.net

echo "🔧 Correction du fichier .env sur AlwaysData"
echo "=============================================="
echo ""

# Aller dans le bon répertoire
cd /home/alex2pro/www

# Vérifier où on est
echo "📍 Répertoire actuel:"
pwd
echo ""

# Sauvegarder le .env actuel
echo "💾 Création d'une sauvegarde..."
cp .env .env.backup-$(date +%Y%m%d-%H%M%S)
echo "✅ Sauvegarde créée"
echo ""

# Afficher la valeur actuelle
echo "📄 Valeur actuelle de APP_URL:"
grep "^APP_URL=" .env
echo ""

# Corriger APP_URL
echo "🔧 Correction de APP_URL..."
sed -i 's|^APP_URL=.*|APP_URL=|g' .env
echo "✅ Correction effectuée"
echo ""

# Afficher la nouvelle valeur
echo "📄 Nouvelle valeur de APP_URL:"
grep "^APP_URL=" .env
echo ""

# Vérifier les permissions du dossier storage
echo "🔒 Vérification des permissions..."
chmod -R 777 storage
echo "✅ Permissions appliquées sur storage/"
echo ""

# Vérifier que les fichiers assets existent
echo "📁 Vérification des assets..."
if [ -d "public/assets" ]; then
    echo "✅ public/assets/ existe"
    ls -la public/assets/
else
    echo "❌ public/assets/ introuvable!"
fi
echo ""

echo "✅ Correction terminée!"
echo ""
echo "🌐 Testez maintenant: https://novatis.alwaysdata.net"
echo "💡 N'oubliez pas de recharger avec Ctrl+Shift+R pour vider le cache"
