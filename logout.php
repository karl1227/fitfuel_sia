<?php
session_start();
require_once 'config/audit.php';

// Log before destroying session
if (isset($_SESSION['user_id'])) {
	audit_log('auth', 'logout', 'success', [], ['user_id'=>$_SESSION['user_id'], 'username'=>$_SESSION['username'] ?? null]);
}

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?>
