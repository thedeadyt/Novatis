<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

class TwoFactorAuth {

    private $g;

    public function __construct() {
        $this->g = new GoogleAuthenticator();
    }

    /**
     * Génère un nouveau secret pour l'utilisateur
     * @return string Le secret généré
     */
    public function generateSecret() {
        return $this->g->generateSecret();
    }

    /**
     * Génère l'URL du QR Code pour Google Authenticator
     * @param array $user Les données de l'utilisateur (doit contenir 'email')
     * @param string $secret Le secret de l'utilisateur
     * @return string L'URL du QR code
     */
    public function getQRCodeUrl($user, $secret) {
        $issuer = 'Novatis';
        $accountName = $user['email'] ?? 'user@novatis.com';

        return GoogleQrUrl::generate($accountName, $secret, $issuer);
    }

    /**
     * Vérifie un code TOTP
     * @param string $secret Le secret de l'utilisateur
     * @param string $code Le code à vérifier
     * @return bool True si le code est valide
     */
    public function verifyCode($secret, $code) {
        // Nettoyer le code (enlever les espaces)
        $code = str_replace(' ', '', $code);

        return $this->g->checkCode($secret, $code);
    }

    /**
     * Vérifie un code de sauvegarde
     * @param array $backupCodes Les codes de sauvegarde disponibles
     * @param string $code Le code à vérifier
     * @return array|false Retourne le tableau des codes restants si valide, false sinon
     */
    public function verifyBackupCode($backupCodes, $code) {
        $code = strtoupper(str_replace(' ', '', $code));

        $key = array_search($code, $backupCodes);
        if ($key !== false) {
            // Retirer le code utilisé
            unset($backupCodes[$key]);
            return array_values($backupCodes); // Réindexer le tableau
        }

        return false;
    }

    /**
     * Génère des codes de sauvegarde
     * @param int $count Nombre de codes à générer
     * @return array Tableau de codes de sauvegarde
     */
    public function generateBackupCodes($count = 10) {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        }
        return $codes;
    }
}
?>
