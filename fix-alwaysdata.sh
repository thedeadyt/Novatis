#!/bin/bash

# Script de correction rapide pour AlwaysData
# À exécuter sur le serveur AlwaysData via SSH

echo "🔍 Diagnostic du problème..."
echo ""

# Vérifier où on est
echo "📍 Répertoire actuel:"
pwd
echo ""

# Vérifier si le fichier .env existe
if [ -f ".env" ]; then
    echo "✅ Fichier .env trouvé"
    echo ""
    echo "📄 Contenu de APP_URL:"
    grep "APP_URL" .env
    echo ""
else
    echo "❌ Fichier .env introuvable!"
    echo "Vous devez être dans /home/alex2pro/www/"
    exit 1
fi

# Vérifier la valeur actuelle de APP_URL
CURRENT_APP_URL=$(grep "^APP_URL=" .env | cut -d'=' -f2)

echo "🔧 Correction en cours..."

# Si APP_URL contient /novatis, le corriger
if [[ "$CURRENT_APP_URL" == *"/novatis"* ]] || [[ "$CURRENT_APP_URL" == "/novatis" ]]; then
    echo "⚠️  APP_URL contient '/novatis' - correction nécessaire"

    # Backup du .env
    cp .env .env.backup.$(date +%Y%m%d-%H%M%S)
    echo "💾 Sauvegarde créée: .env.backup.$(date +%Y%m%d-%H%M%S)"

    # Corriger APP_URL
    sed -i 's|^APP_URL=.*|APP_URL=|g' .env

    echo "✅ APP_URL corrigé!"
    echo ""
    echo "📄 Nouvelle valeur:"
    grep "APP_URL" .env
else
    echo "✅ APP_URL est déjà vide ou correct"
fi

echo ""
echo "🔍 Vérification de la structure des fichiers..."

# Vérifier que public/ existe
if [ -d "public" ]; then
    echo "✅ Dossier public/ existe"

    # Vérifier que les assets existent
    if [ -d "public/assets" ]; then
        echo "✅ Dossier public/assets/ existe"

        # Lister quelques fichiers pour vérifier
        echo ""
        echo "📁 Contenu de public/assets/:"
        ls -la public/assets/
    else
        echo "❌ Dossier public/assets/ introuvable!"
    fi
else
    echo "❌ Dossier public/ introuvable!"
    echo "Vous devez être dans /home/alex2pro/www/"
fi

echo ""
echo "🔍 Vérification des permissions..."
ls -la storage/ 2>/dev/null

if [ -d "storage" ]; then
    echo ""
    echo "🔧 Application des permissions sur storage..."
    chmod -R 777 storage
    echo "✅ Permissions appliquées"
else
    echo "⚠️  Dossier storage/ introuvable"
fi

echo ""
echo "✅ Diagnostic terminé!"
echo ""
echo "🌐 Testez maintenant votre site: https://novatis.alwaysdata.net"
echo ""
echo "Si les erreurs persistent:"
echo "1. Videz le cache de votre navigateur (Ctrl+Shift+R)"
echo "2. Vérifiez que la configuration du site dans AlwaysData pointe vers: /home/alex2pro/www/public"
