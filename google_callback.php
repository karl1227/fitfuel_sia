<?php
session_start();
require_once 'config/database.php';
require_once 'config/google_config.php';

try {
    // Check if authorization code is present
    if (!isset($_GET['code'])) {
        throw new Exception('Authorization code not found');
    }
    
    $code = $_GET['code'];
    
    // Exchange code for access token
    $tokenData = getGoogleAccessToken($code);
    
    if (!isset($tokenData['access_token'])) {
        throw new Exception('Failed to get access token');
    }
    
    // Get user info from Google
    $userInfo = getGoogleUserInfo($tokenData['access_token']);
    
    if (!isset($userInfo['id']) || !isset($userInfo['email'])) {
        throw new Exception('Invalid user data from Google');
    }
    
    $googleId = $userInfo['id'];
    $email = $userInfo['email'];
    $name = $userInfo['name'] ?? '';
    $picture = $userInfo['picture'] ?? '';
    
    // Connect to database
    $pdo = getDBConnection();
    
    // Check if user already exists with this Google ID
    $stmt = $pdo->prepare("SELECT user_id, username, email, role, status FROM users WHERE google_id = ?");
    $stmt->execute([$googleId]);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        // User exists, log them in
        if ($existingUser['status'] !== 'active') {
            throw new Exception('Your account is not active. Please contact support.');
        }
        
        // Update last login
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $updateStmt->execute([$existingUser['user_id']]);
        
        // Set session variables
        $_SESSION['user_id'] = $existingUser['user_id'];
        $_SESSION['username'] = $existingUser['username'];
        $_SESSION['email'] = $existingUser['email'];
        $_SESSION['role'] = $existingUser['role'];
        
        // Redirect based on role
        if (in_array($existingUser['role'], ['admin', 'manager', 'staff'])) {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: index.html');
        }
        exit();
        
    } else {
        // Check if user exists with same email but different Google ID
        $stmt = $pdo->prepare("SELECT user_id, username, email, role, status FROM users WHERE email = ? AND google_id IS NULL");
        $stmt->execute([$email]);
        $emailUser = $stmt->fetch();
        
        if ($emailUser) {
            // User exists with email but no Google ID, link the accounts
            if ($emailUser['status'] !== 'active') {
                throw new Exception('Your account is not active. Please contact support.');
            }
            
            // Update user with Google ID
            $updateStmt = $pdo->prepare("UPDATE users SET google_id = ?, last_login = NOW() WHERE user_id = ?");
            $updateStmt->execute([$googleId, $emailUser['user_id']]);
            
            // Set session variables
            $_SESSION['user_id'] = $emailUser['user_id'];
            $_SESSION['username'] = $emailUser['username'];
            $_SESSION['email'] = $emailUser['email'];
            $_SESSION['role'] = $emailUser['role'];
            
            // Redirect based on role
            if (in_array($emailUser['role'], ['admin', 'manager', 'staff'])) {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.html');
            }
            exit();
            
        } else {
            // New user, create account
            $username = strtolower(str_replace(' ', '', $name));
            if (empty($username)) {
                $username = 'user_' . substr($googleId, 0, 8);
            }
            
            // Ensure username is unique
            $originalUsername = $username;
            $counter = 1;
            while (true) {
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if (!$stmt->fetch()) {
                    break;
                }
                $username = $originalUsername . $counter;
                $counter++;
            }
            
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, google_id, role, status) VALUES (?, ?, ?, 'customer', 'active')");
            $stmt->execute([$username, $email, $googleId]);
            
            $userId = $pdo->lastInsertId();
            
            // Set session variables
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'customer';
            
            // Redirect to main page
            header('Location: index.html');
            exit();
        }
    }
    
} catch (Exception $e) {
    // Redirect back to login with error
    $_SESSION['google_error'] = $e->getMessage();
    header('Location: login.php');
    exit();
}
?>
