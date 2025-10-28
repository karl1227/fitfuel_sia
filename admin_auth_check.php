<?php
// Admin authentication check - include this file in admin pages
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if user has admin privileges
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager', 'staff'])) {
    header('Location: ../index.php');
    exit();
}

// Optional: Check if user is active
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT status FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user || $user['status'] !== 'active') {
        session_destroy();
        header('Location: ../login.php');
        exit();
    }
} catch (PDOException $e) {
    // If database error, redirect to login
    session_destroy();
    header('Location: ../login.php');
    exit();
}

/**
 * Check if the current user has permission to access a specific module
 * @param string $module The module name to check
 * @return bool True if user has access, false otherwise
 */
function hasAccess($module) {
    $role = $_SESSION['role'] ?? '';
    
    // Define module permissions based on roles
    $permissions = [
        'admin' => [
            'dashboard', 'products', 'orders', 'inventory', 
            'users', 'analytics', 'content', 'audit_logs', 'settings'
        ],
        'manager' => [
            'dashboard', 'analytics', 'settings'
        ],
        'staff' => [
            'dashboard', 'orders', 'inventory', 'settings'
        ]
    ];
    
    // Admin has access to everything
    if ($role === 'admin') {
        return true;
    }
    
    // Check if the module is in the user's allowed modules
    if (isset($permissions[$role])) {
        return in_array($module, $permissions[$role]);
    }
    
    return false;
}

/**
 * Require access to a specific module
 * Redirects to dashboard if access is denied
 * @param string $module The module name to require access to
 */
function requireAccess($module) {
    if (!hasAccess($module)) {
        header('Location: ../admin/dashboard.php');
        exit();
    }
}
?>
