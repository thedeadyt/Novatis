# Système de Notifications par Email - Novatis

## 📧 Vue d'ensemble

Le système de notifications par email envoie automatiquement des emails aux utilisateurs pour les informer des événements importants sur la plateforme.

## 🔔 Types de Notifications

### 1. **Nouvelles Commandes et Messages**

#### Nouvelle Commande (Vendeur)
- **Déclencheur** : Quand un acheteur passe une nouvelle commande
- **Destinataire** : Le vendeur du service
- **Contenu** : "Vous avez reçu une nouvelle commande d'un montant de XXX€"
- **Lien email** : `Dashboard?tab=orders` (onglet Mes Ventes)
- **Fichier** : `public/api/orders/orders.php` (ligne 111)

#### Commande Créée (Acheteur)
- **Déclencheur** : Quand l'acheteur crée une commande
- **Destinataire** : L'acheteur
- **Contenu** : "Votre commande a été créée avec succès"
- **Lien email** : `Dashboard?tab=purchases` (onglet Mes Achats)
- **Fichier** : `public/api/orders/orders.php` (ligne 114-120)

#### Mise à Jour de Commande
- **Déclencheur** : Changement de statut (acceptée, livrée, terminée, annulée)
- **Destinataires** : Acheteur ET vendeur
- **Contenu** : Message personnalisé selon le statut
- **Lien email** :
  - Acheteur → `Dashboard?tab=purchases`
  - Vendeur → `Dashboard?tab=orders`
- **Fichier** : `public/api/orders/orders.php` (ligne 202-223)

#### Nouveau Message
- **Déclencheur** : Réception d'un nouveau message dans une conversation
- **Destinataire** : Le destinataire du message
- **Contenu** : "[Expéditeur] vous a envoyé un message"
- **Lien email** : `Dashboard?tab=messages` (onglet Messages)
- **Fichier** : `public/api/messaging/messages.php` (ligne 139-145)

### 2. **Mises à Jour de Services**

- **Déclencheur** : Modification d'un service par le prestataire
- **Destinataires** : Clients ayant des commandes actives (pending/in_progress) pour ce service
- **Contenu** : "Le service [nom] a été mis à jour par le prestataire"
- **Lien email** : `Dashboard?tab=services` (onglet Mes Services)
- **Fichier** : `public/api/services/services.php` (ligne 185-210)

### 3. **Alertes de Sécurité**

#### Nouvelle Connexion
- **Déclencheur** : À chaque connexion réussie
- **Destinataire** : L'utilisateur qui se connecte
- **Contenu** : Détails de la connexion (date, heure, navigateur, IP)
- **Lien email** : `Parametres` (page Paramètres de sécurité)
- **Fichier** : `public/api/auth/login.php` (ligne 115-135)

#### Activation A2F
- **Déclencheur** : Activation de l'authentification à deux facteurs
- **Destinataire** : L'utilisateur
- **Contenu** : "L'A2F a été activée sur votre compte"
- **Lien email** : `Parametres` (page Paramètres de sécurité)
- **Fichier** : `public/api/parametres/2fa-setup.php` (ligne 128-137)

#### Désactivation A2F
- **Déclencheur** : Désactivation de l'A2F
- **Destinataire** : L'utilisateur
- **Contenu** : "⚠️ L'A2F a été désactivée. Si ce n'était pas vous, réactivez-la immédiatement"
- **Lien email** : `Parametres` (page Paramètres de sécurité)
- **Fichier** : `public/api/parametres/2fa-setup.php` (ligne 186-195)

### 4. **Rappels de Paiements**

#### Rappel Avant Échéance
- **Déclencheur** : Commande en pending avec deadline dans 3 jours ou moins
- **Destinataire** : L'acheteur
- **Contenu** : "Rappel : Votre commande expire dans X jours. Merci de finaliser le paiement"
- **Lien email** : `Dashboard?tab=purchases` (onglet Mes Achats)
- **Fichier** : `public/api/cron/payment-reminders.php`
- **Exécution** : Quotidiennement via cron (recommandé : 9h00)

#### Alerte Retard de Paiement
- **Déclencheur** : Commande en pending avec deadline dépassée
- **Destinataires** : Acheteur (alerte) + Vendeur (information)
- **Contenu** : "⚠️ RETARD DE PAIEMENT : X jour(s) de retard"
- **Lien email** :
  - Acheteur → `Dashboard?tab=purchases`
  - Vendeur → `Dashboard?tab=orders`
- **Action automatique** : Annulation après 7 jours de retard
- **Fichier** : `public/api/cron/overdue-payment-alerts.php`
- **Exécution** : Quotidiennement via cron (recommandé : 10h00)

## ⚙️ Configuration

### Activation des Notifications Email (Utilisateur)

Les utilisateurs peuvent activer/désactiver les notifications email dans leurs paramètres :

**Chemin** : Dashboard > Paramètres > Notifications > Notifications par email

Par défaut, les notifications sont **activées** pour tous les nouveaux utilisateurs.

### Préférences Stockées

Les préférences sont stockées dans la table `user_preferences` :
```sql
CREATE TABLE user_preferences (
    user_id INT PRIMARY KEY,
    email_notifications BOOLEAN DEFAULT TRUE,
    ...
)
```

## 🔧 Architecture Technique

### NotificationService.php

Le service principal qui gère toutes les notifications :

```php
class NotificationService {
    // Crée une notification in-app ET envoie l'email si activé
    public function create($userId, $type, $title, $message, $link = null)

    // Méthodes spécifiques
    public function notifyNewOrder($userId, $orderId, $orderAmount)
    public function notifyNewMessage($userId, $senderName, $conversationId)
    public function notifySecurityAlert($userId, $alertMessage)
    public function notifyPaymentReminder($userId, $amount, $dueDate)
    public function notifyServiceUpdate($userId, $serviceName, $updateMessage)
}
```

### EmailService.php

Gère l'envoi des emails via PHPMailer :

```php
class EmailService {
    // Email générique de notification
    public static function sendNotificationEmail($email, $firstname, $lastname, $title, $message, $link = null)

    // Emails spécifiques
    public static function sendVerificationEmail(...)
    public static sendWelcomeEmail(...)
}
```

### Flux de Notification

1. **Événement** se produit (nouvelle commande, message, etc.)
2. **NotificationService->create()** est appelé
3. **Notification in-app** créée dans la BDD
4. **Vérification** des préférences utilisateur
5. Si `email_notifications = TRUE` :
   - **EmailService->sendNotificationEmail()** est appelé
   - Email envoyé via SMTP (Gmail)
   - Événement loggé dans `notification_events`

## 📊 Tables de la Base de Données

### user_notifications
```sql
CREATE TABLE user_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,  -- 'order', 'message', 'security', 'payment', 'service_update'
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL
)
```

### notification_events
```sql
CREATE TABLE notification_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL,  -- 'created', 'sent_email', 'read', 'deleted'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metadata JSON DEFAULT NULL
)
```

## 🚀 Configuration des Cron Jobs

Pour activer les rappels de paiement automatiques, configurer les cron jobs suivants :

### Linux/macOS (crontab)
```bash
# Ouvrir crontab
crontab -e

# Ajouter ces lignes
# Rappels de paiement (tous les jours à 9h)
0 9 * * * php /path/to/Novatis/public/api/cron/payment-reminders.php

# Alertes de retard (tous les jours à 10h)
0 10 * * * php /path/to/Novatis/public/api/cron/overdue-payment-alerts.php
```

### Windows (Planificateur de tâches)
1. Ouvrir **Planificateur de tâches**
2. Créer une tâche de base
3. **Déclencheur** : Quotidien à 9h00
4. **Action** : Démarrer un programme
5. **Programme** : `C:\xampp\php\php.exe`
6. **Arguments** : `C:\xampp\htdocs\Novatis\public\api\cron\payment-reminders.php`
7. Répéter pour `overdue-payment-alerts.php` à 10h00

### Exécution Manuelle (Test)
```bash
cd C:\xampp\htdocs\Novatis\public\api\cron
php payment-reminders.php
php overdue-payment-alerts.php
```

## 📧 Configuration SMTP

Le système utilise Gmail SMTP. Configuration dans `EmailService.php` :

```php
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'no.reply.alex2@gmail.com';
$mail->Password = 'cyoxzqbplgcojlpe';  // App Password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

**⚠️ Important** : Pour utiliser Gmail SMTP, activer "Mots de passe d'application" dans les paramètres Google.

## 🎨 Template Email

Les emails utilisent un design HTML professionnel avec :
- Header avec gradient rouge Novatis
- Contenu formaté avec box de message
- Bouton CTA pour accéder à l'action
- Footer avec lien vers préférences
- Version texte brut (AltBody) pour compatibilité

## 🧪 Tests

Pour tester le système de notifications :

1. **Créer une commande** → Vérifier email vendeur/acheteur
2. **Envoyer un message** → Vérifier email destinataire
3. **Modifier un service** → Vérifier email clients actifs
4. **Se connecter** → Vérifier email de sécurité
5. **Activer/Désactiver A2F** → Vérifier email de sécurité
6. **Exécuter manuellement les crons** → Vérifier rappels de paiement

## 📝 Logs et Debugging

Les erreurs sont loggées via `error_log()` :
- Erreurs d'envoi d'email
- Erreurs de création de notification
- Erreurs cron jobs

Vérifier les logs PHP (selon configuration serveur) :
- XAMPP : `C:\xampp\apache\logs\error.log`
- Linux : `/var/log/apache2/error.log`

## 🔐 Sécurité

- ✅ Emails envoyés uniquement si utilisateur a activé les notifications
- ✅ Toutes les données échappées avant insertion dans templates HTML
- ✅ Liens absolus avec BASE_URL pour éviter phishing
- ✅ Vérification des permissions avant envoi
- ✅ Rate limiting recommandé pour éviter spam

## 📈 Statistiques

Suivre l'engagement via la table `notification_events` :
- Taux d'ouverture (événement 'read')
- Emails envoyés (événement 'sent_email')
- Notifications créées (événement 'created')

```sql
-- Exemple : Taux d'emails envoyés
SELECT
    COUNT(CASE WHEN event_type = 'sent_email' THEN 1 END) as emails_sent,
    COUNT(CASE WHEN event_type = 'created' THEN 1 END) as total_notifications,
    ROUND(COUNT(CASE WHEN event_type = 'sent_email' THEN 1 END) * 100.0 / COUNT(CASE WHEN event_type = 'created' THEN 1 END), 2) as email_rate
FROM notification_events
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);
```

## 🎯 Roadmap Futur

- [ ] Digest emails (résumé quotidien/hebdomadaire)
- [ ] Templates personnalisables par type de notification
- [ ] Notifications push (web push API)
- [ ] SMS pour alertes critiques
- [ ] Préférences granulaires (choisir types de notifications)
- [ ] Tracking d'ouverture des emails (pixels invisibles)
- [ ] A/B testing des templates email
