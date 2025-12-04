<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

class EmailService {

    /**
     * Envoie un email de vérification à l'utilisateur
     */
    public static function sendVerificationEmail($email, $firstname, $lastname, $token) {
        $mail = new PHPMailer(true);

        try {
            // Configuration SMTP
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'no.reply.alex2@gmail.com';
            $mail->Password = 'cyoxzqbplgcojlpe';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Expéditeur et destinataire
            $mail->setFrom('no.reply.alex2@gmail.com', 'Novatis');
            $mail->addAddress($email, trim($firstname . ' ' . $lastname));

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Vérifiez votre compte Novatis';

            $verificationUrl = BASE_URL . '/verify-email?token=' . urlencode($token);

            $mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #B41200 0%, #7F0D00 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: #B41200; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        .button:hover { background: #E04830; }
        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue sur Novatis !</h1>
        </div>
        <div class="content">
            <p>Bonjour <strong>$firstname $lastname</strong>,</p>

            <p>Merci de vous être inscrit sur <strong>Novatis</strong>, la plateforme de services entre étudiants freelances.</p>

            <p>Pour activer votre compte et accéder à toutes les fonctionnalités, veuillez cliquer sur le bouton ci-dessous :</p>

            <p style="text-align: center;">
                <a href="$verificationUrl" class="button">Vérifier mon adresse email</a>
            </p>

            <p>Ou copiez ce lien dans votre navigateur :</p>
            <p style="background: #fff; padding: 10px; border-left: 4px solid #B41200; word-break: break-all;">
                $verificationUrl
            </p>

            <p><strong>⏰ Ce lien est valide pendant 24 heures.</strong></p>

            <p>Si vous n'avez pas créé de compte sur Novatis, vous pouvez ignorer cet email en toute sécurité.</p>

            <p>À bientôt sur Novatis !<br>L'équipe Novatis</p>
        </div>
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; 2025 Novatis - Plateforme de services freelance pour étudiants</p>
        </div>
    </div>
</body>
</html>
HTML;

            $mail->AltBody = "Bonjour $firstname $lastname,\n\n"
                . "Merci de vous être inscrit sur Novatis.\n\n"
                . "Pour vérifier votre compte, cliquez sur ce lien :\n"
                . "$verificationUrl\n\n"
                . "Ce lien est valide pendant 24 heures.\n\n"
                . "L'équipe Novatis";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Erreur envoi email vérification : " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Envoie un email de bienvenue après vérification
     */
    public static function sendWelcomeEmail($email, $firstname, $lastname) {
        $mail = new PHPMailer(true);

        try {
            // Configuration SMTP
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'no.reply.alex2@gmail.com';
            $mail->Password = 'cyoxzqbplgcojlpe';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Expéditeur et destinataire
            $mail->setFrom('no.reply.alex2@gmail.com', 'Novatis');
            $mail->addAddress($email, trim($firstname . ' ' . $lastname));

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = '✅ Votre compte Novatis est activé !';

            $dashboardUrl = BASE_URL . '/Dashboard';

            $mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #28a745 0%, #20893a 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: #B41200; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        .features { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .features li { margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Compte activé !</h1>
        </div>
        <div class="content">
            <p>Félicitations <strong>$firstname $lastname</strong> ! 🎉</p>

            <p>Votre compte Novatis est maintenant <strong>vérifié et actif</strong>.</p>

            <div class="features">
                <h3>Vous pouvez maintenant :</h3>
                <ul>
                    <li>✨ Créer et publier vos services</li>
                    <li>🛒 Commander des services auprès d'autres freelances</li>
                    <li>💬 Échanger avec la communauté</li>
                    <li>⭐ Recevoir et donner des avis</li>
                    <li>📊 Gérer votre portfolio</li>
                </ul>
            </div>

            <p style="text-align: center;">
                <a href="$dashboardUrl" class="button">Accéder à mon dashboard</a>
            </p>

            <p>Bonne chance dans vos projets freelance !</p>

            <p>L'équipe Novatis 🚀</p>
        </div>
    </div>
</body>
</html>
HTML;

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Erreur envoi email bienvenue : " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Envoie un email de notification générique
     */
    public static function sendNotificationEmail($email, $firstname, $lastname, $title, $message, $link = null) {
        $mail = new PHPMailer(true);

        try {
            // Configuration SMTP
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'no.reply.alex2@gmail.com';
            $mail->Password = 'cyoxzqbplgcojlpe';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Expéditeur et destinataire
            $mail->setFrom('no.reply.alex2@gmail.com', 'Novatis');
            $mail->addAddress($email, trim($firstname . ' ' . $lastname));

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = '🔔 ' . $title . ' - Novatis';

            $buttonHtml = '';
            if ($link) {
                $fullLink = 'http://localhost' . $link;
                $buttonHtml = '<p style="text-align: center;"><a href="' . $fullLink . '" class="button">Voir les détails</a></p>';
            }

            $mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #B41200 0%, #7F0D00 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: #B41200; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        .button:hover { background: #E04830; }
        .message-box { background: white; padding: 20px; border-left: 4px solid #B41200; margin: 20px 0; border-radius: 5px; }
        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Notification Novatis</h1>
        </div>
        <div class="content">
            <p>Bonjour <strong>$firstname $lastname</strong>,</p>

            <div class="message-box">
                <h2 style="margin-top: 0; color: #B41200;">$title</h2>
                <p>$message</p>
            </div>

            $buttonHtml

            <p>Vous pouvez également consulter vos notifications directement sur votre dashboard Novatis.</p>

            <p>L'équipe Novatis</p>
        </div>
        <div class="footer">
            <p>Vous recevez cet email car vous avez activé les notifications par email dans vos paramètres.</p>
            <p>Pour modifier vos préférences, rendez-vous dans <a href="{BASE_URL}/Parametres?section=notifications">Paramètres > Notifications</a></p>
            <p>&copy; 2025 Novatis - Plateforme de services freelance pour étudiants</p>
        </div>
    </div>
</body>
</html>
HTML;

            $mail->AltBody = "Bonjour $firstname $lastname,\n\n"
                . "$title\n\n"
                . "$message\n\n"
                . ($link ? "Lien: http://localhost$link\n\n" : "")
                . "L'équipe Novatis";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Erreur envoi email notification : " . $mail->ErrorInfo);
            return false;
        }
    }
}
?>
