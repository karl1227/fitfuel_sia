<?php
/**
 * PayPal Configuration
 * Replace these with your actual PayPal credentials
 */

class PayPalConfig {
    // PayPal Sandbox Credentials (for testing)
    const SANDBOX_CLIENT_ID = 'AT_LSaC3hbNUsLFeUS-Y149YK7AEhblLhpOIhlvwrlYil4GIR_D_Gw90eurBcN4IH5CRj-vEWsQJwUwU';
    const SANDBOX_CLIENT_SECRET = 'EMqJlOOgVl-7_7iqYiavMWk_1KjRK5WDlkHhsiNf6-PSuJfeQ2Lo4bY84XycAzQckI3j6Z3Bijl5DTuH';
    
    // PayPal Live Credentials (for production)
    const LIVE_CLIENT_ID = 'YOUR_LIVE_CLIENT_ID_HERE';
    const LIVE_CLIENT_SECRET = 'YOUR_LIVE_CLIENT_SECRET_HERE';
    
    // PayPal URLs
    const SANDBOX_BASE_URL = 'https://api.sandbox.paypal.com';
    const LIVE_BASE_URL = 'https://api.paypal.com';
    
    // Current environment (change to 'live' for production)
    const ENVIRONMENT = 'sandbox'; // or 'live'
    
    /**
     * Get current PayPal credentials based on environment
     */
    public static function getCredentials() {
        if (self::ENVIRONMENT === 'live') {
            return [
                'client_id' => self::LIVE_CLIENT_ID,
                'client_secret' => self::LIVE_CLIENT_SECRET,
                'base_url' => self::LIVE_BASE_URL
            ];
        } else {
            return [
                'client_id' => self::SANDBOX_CLIENT_ID,
                'client_secret' => self::SANDBOX_CLIENT_SECRET,
                'base_url' => self::SANDBOX_BASE_URL
            ];
        }
    }
    
    /**
     * Get PayPal redirect URLs
     */
    public static function getRedirectUrls() {
        $base_url = 'http://localhost/fitfuel_sia'; // Your local development URL
        
        return [
            'return_url' => $base_url . '/paypal_success.php',
            'cancel_url' => $base_url . '/paypal_cancel.php'
        ];
    }
}
?>
