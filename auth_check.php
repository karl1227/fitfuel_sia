<?php
// Authentication check - include this file in pages that require login
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
        header('Location: login.php');
        exit();
    }
} catch (PDOException $e) {
    // If database error, redirect to login
    session_destroy();
    header('Location: login.php');
    exit();
}
?>
