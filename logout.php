<?php
session_start();
require_once 'config/audit_logger.php';

// Log logout before destroying session
if (isset($_SESSION['user_id'])) {
    try {
        $auditLogger = new AuditLogger();
        $auditLogger->logLogout($_SESSION['user_id']);
    } catch (Exception $e) {
        // Ignore audit logging errors during logout
        error_log("Audit log error during logout: " . $e->getMessage());
    }
}

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?>
