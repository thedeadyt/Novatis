#!/bin/bash

# Script de déploiement vers AlwaysData
# Usage: ./deploy-to-alwaysdata.sh

echo "🚀 Déploiement de Novatis vers AlwaysData..."

# Couleurs pour les messages
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
REMOTE_USER="alex2pro"
REMOTE_HOST="ssh-alex2pro.alwaysdata.net"
REMOTE_PATH="/home/alex2pro/www"  # Les fichiers vont directement dans www/ (pas de sous-dossier)
LOCAL_PATH="/var/www/html/novatis"

echo -e "${YELLOW}📋 Vérification des prérequis...${NC}"

# Vérifier que le fichier .env.production existe
if [ ! -f "$LOCAL_PATH/.env.production" ]; then
    echo -e "${RED}❌ Erreur: Le fichier .env.production n'existe pas${NC}"
    echo "Créez d'abord ce fichier avec les bonnes configurations pour AlwaysData"
    exit 1
fi

echo -e "${GREEN}✅ Fichier .env.production trouvé${NC}"

# Créer une archive temporaire
echo -e "${YELLOW}📦 Création de l'archive...${NC}"
TEMP_DIR=$(mktemp -d)
ARCHIVE_NAME="novatis-$(date +%Y%m%d-%H%M%S).tar.gz"

# Copier les fichiers nécessaires dans le dossier temporaire
rsync -av \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='storage/logs/*' \
    --exclude='storage/uploads/*' \
    --exclude='storage/cache/*' \
    --exclude='.env' \
    --exclude='*.log' \
    "$LOCAL_PATH/" "$TEMP_DIR/"

# Copier .env.production comme .env
cp "$LOCAL_PATH/.env.production" "$TEMP_DIR/.env"

# Créer les dossiers de storage vides
mkdir -p "$TEMP_DIR/storage/logs"
mkdir -p "$TEMP_DIR/storage/uploads"
mkdir -p "$TEMP_DIR/storage/cache"

# Créer l'archive
cd "$TEMP_DIR"
tar -czf "$ARCHIVE_NAME" --exclude="$ARCHIVE_NAME" .
echo -e "${GREEN}✅ Archive créée: $ARCHIVE_NAME${NC}"

# Afficher les instructions de déploiement manuel
echo ""
echo -e "${YELLOW}📝 Instructions de déploiement:${NC}"
echo ""
echo "1. Connectez-vous à AlwaysData via SSH:"
echo -e "   ${GREEN}ssh $REMOTE_USER@$REMOTE_HOST${NC}"
echo ""
echo "2. Téléchargez l'archive sur votre compte AlwaysData"
echo -e "   L'archive se trouve ici: ${GREEN}$TEMP_DIR/$ARCHIVE_NAME${NC}"
echo ""
echo "3. Sur AlwaysData, décompressez l'archive dans www/:"
echo -e "   ${GREEN}cd /home/$REMOTE_USER/www && tar -xzf ../$ARCHIVE_NAME${NC}"
echo ""
echo "4. Configurez les permissions:"
echo -e "   ${GREEN}chmod -R 755 /home/$REMOTE_USER/www${NC}"
echo -e "   ${GREEN}chmod -R 777 /home/$REMOTE_USER/www/storage${NC}"
echo ""
echo "5. Dans l'interface AlwaysData, configurez votre site web:"
echo "   - Type: PHP"
echo "   - Répertoire racine: /home/$REMOTE_USER/www/public"
echo "   - Version PHP: 8.1 ou supérieure"
echo ""
echo "6. Si vous utilisez Composer, installez les dépendances:"
echo -e "   ${GREEN}cd /home/$REMOTE_USER/www && composer install --no-dev --optimize-autoloader${NC}"
echo ""
echo -e "${YELLOW}⚠️  Important:${NC}"
echo "- Assurez-vous que APP_URL dans .env correspond à votre URL AlwaysData"
echo "- Vérifiez que les informations de base de données sont correctes"
echo "- Activez HTTPS dans les paramètres AlwaysData pour la sécurité"
echo ""
echo -e "${GREEN}✅ Archive prête pour le déploiement!${NC}"
echo ""
echo "Voulez-vous copier l'archive via SCP maintenant? (y/n)"
read -r response

if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
    echo -e "${YELLOW}📤 Transfert de l'archive vers AlwaysData...${NC}"
    scp "$TEMP_DIR/$ARCHIVE_NAME" "$REMOTE_USER@$REMOTE_HOST:/home/$REMOTE_USER/"

    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Archive transférée avec succès!${NC}"
        echo ""
        echo "Connectez-vous maintenant à AlwaysData pour décompresser l'archive:"
        echo -e "${GREEN}ssh $REMOTE_USER@$REMOTE_HOST${NC}"
        echo "Puis exécutez:"
        echo -e "${GREEN}cd /home/$REMOTE_USER/www && tar -xzf ../$ARCHIVE_NAME${NC}"
    else
        echo -e "${RED}❌ Erreur lors du transfert${NC}"
    fi
else
    echo -e "${YELLOW}📁 L'archive est disponible ici: $TEMP_DIR/$ARCHIVE_NAME${NC}"
    echo "Vous pouvez la transférer manuellement via FTP ou l'interface AlwaysData"
fi

echo ""
echo -e "${GREEN}🎉 Déploiement préparé!${NC}"
