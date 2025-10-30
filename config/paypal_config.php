<?php
/**
 * PayPal Configuration
 * Replace these with your actual PayPal credentials
 */

class PayPalConfig {
    // PayPal Sandbox Credentials (for testing)
    const SANDBOX_CLIENT_ID = 'AV6l68EDqFlqQstFqEvMJoAjKDvQ2qGbo4YaQsRqz2LG5xfBqFRt_njCVRSLMupfpCGDR6VZgKuYU0lP';
    const SANDBOX_CLIENT_SECRET = 'EMNNkNOd49KB8AVdmT0kBb6DGEKjgaLfr25jlb640rawMfyJCbzfJbCylKSvn4MyiYbG8cL_4CW7jyCu';
    
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
