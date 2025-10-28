<?php
/**
 * Maintenance Mode Check
 * Include this in public-facing pages to redirect non-admin users when maintenance is active
 */

require_once 'database.php';

function isMaintenanceMode(): bool {
    try {
        $pdo = getDBConnection();
        
        // Check if settings table exists, if not, return false
        $stmt = $pdo->query("SHOW TABLES LIKE 'settings'");
        if ($stmt->rowCount() == 0) {
            return false;
        }
        
        // Get maintenance mode setting
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE key_name = 'maintenance_mode'");
        $stmt->execute();
        $result = $stmt->fetch();
        
        return isset($result['value']) && $result['value'] === '1';
    } catch (PDOException $e) {
        // On error, assume maintenance is off
        return false;
    }
}

function checkMaintenanceMode(): void {
    // Don't check maintenance for admins
    if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager', 'staff'])) {
        return;
    }
    
    // Don't check on login, registration, or admin pages
    $allowedPages = ['login.php', 'registration.php', 'forgot_password.php', 'reset_password.php', 
                     'otp_verification.php', 'google_callback.php', 'logout.php'];
    $currentPage = basename($_SERVER['PHP_SELF']);
    
    if (in_array($currentPage, $allowedPages)) {
        return;
    }
    
    // Check if site is in maintenance mode
    if (isMaintenanceMode()) {
        // Send 503 status code
        http_response_code(503);
        include dirname(__DIR__) . '/maintenance_page.php';
        exit();
    }
}
?>

