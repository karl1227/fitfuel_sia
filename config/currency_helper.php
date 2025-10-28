<?php
/**
 * Currency Helper Functions
 * Provides functions to format and display currency according to settings
 */

require_once 'database.php';

/**
 * Get currency settings from database
 */
function getCurrencySettings() {
    static $settings = null;
    
    if ($settings === null) {
        $settings = [
            'symbol' => '₱',
            'code' => 'PHP',
            'position' => 'before'
        ];
        
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->query("SELECT key_name, value FROM settings WHERE key_name IN ('currency_symbol', 'currency_code', 'currency_position')");
            while ($row = $stmt->fetch()) {
                switch ($row['key_name']) {
                    case 'currency_symbol':
                        $settings['symbol'] = $row['value'] ?: '₱';
                        break;
                    case 'currency_code':
                        $settings['code'] = $row['value'] ?: 'PHP';
                        break;
                    case 'currency_position':
                        $settings['position'] = $row['value'] ?: 'before';
                        break;
                }
            }
        } catch (Exception $e) {
            // Use defaults if error
        }
    }
    
    return $settings;
}

/**
 * Format a price with currency symbol
 * @param float $amount The amount to format
 * @param int $decimals Number of decimal places
 * @return string Formatted price string
 */
function formatCurrency($amount, $decimals = 2) {
    $settings = getCurrencySettings();
    $formatted = number_format((float)$amount, $decimals);
    
    if ($settings['position'] === 'after') {
        return $formatted . $settings['symbol'];
    } else {
        return $settings['symbol'] . $formatted;
    }
}

/**
 * Get currency symbol
 */
function getCurrencySymbol() {
    $settings = getCurrencySettings();
    return $settings['symbol'];
}

/**
 * Get currency code
 */
function getCurrencyCode() {
    $settings = getCurrencySettings();
    return $settings['code'];
}

/**
 * Get currency position
 */
function getCurrencyPosition() {
    $settings = getCurrencySettings();
    return $settings['position'];
}
?>

