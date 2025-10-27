<?php
session_start();
require_once 'config/database.php';
require_once 'config/google_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    
    // Debug: Log the user info structure (remove in production)
    error_log('Google User Info: ' . json_encode($userInfo));
    
    if (!isset($userInfo['sub']) || !isset($userInfo['email'])) {
        $missingFields = [];
        if (!isset($userInfo['sub'])) $missingFields[] = 'sub (user ID)';
        if (!isset($userInfo['email'])) $missingFields[] = 'email';
        throw new Exception('Invalid user data from Google. Missing fields: ' . implode(', ', $missingFields));
    }
    
    $googleId = $userInfo['sub'];
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
        // User exists, check if they need 2FA
        if ($existingUser['status'] !== 'active') {
            throw new Exception('Your account is not active. Please contact support.');
        }
        
        // Check if user is admin/manager/staff (skip 2FA)
        if (in_array($existingUser['role'], ['admin', 'manager', 'staff'])) {
            // Direct login for admin users
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $updateStmt->execute([$existingUser['user_id']]);
            
            // Set session variables
            $_SESSION['user_id'] = $existingUser['user_id'];
            $_SESSION['username'] = $existingUser['username'];
            $_SESSION['email'] = $existingUser['email'];
            $_SESSION['role'] = $existingUser['role'];
            
            header('Location: admin/dashboard.php');
            exit();
        } else {
            // Customer login - require 2FA
            $otp = rand(100000, 999999);
            $otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
            
            // Update OTP in database
            $updateStmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expiry = ?, last_login = NOW() WHERE user_id = ?");
            $updateStmt->execute([$otp, $otp_expiry, $existingUser['user_id']]);
            
            // Send OTP via email
            require_once './PHPMailer/src/Exception.php';
            require_once './PHPMailer/src/PHPMailer.php';
            require_once './PHPMailer/src/SMTP.php';
            
            $mail = new PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'siafitfuel@gmail.com';
                $mail->Password   = 'felclcbkazuspzde';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                
                // Recipients
                $mail->setFrom('siafitfuel@gmail.com', 'FitFuel');
                $mail->addAddress($existingUser['email']);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = "Your OTP Code - FitFuel Google Login";
                
                $mail->Body = '
                <div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;">
                <div style="max-width: 500px; margin: auto; background: #ffffff; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0px 2px 5px rgba(0,0,0,0.1);">
                    <h2 style="color: #333;">🔐 Email Verification</h2>
                    <p style="font-size: 16px; color: #555;">
                    Hello ' . htmlspecialchars($existingUser['username']) . ', <br> Use the OTP below to complete your Google login:
                    </p>
                    <div style="font-size: 32px; font-weight: bold; color: #2c3e50; margin: 20px 0; letter-spacing: 4px;">
                    ' . $otp . '
                    </div>
                    <p style="font-size: 14px; color: #999;">
                    This OTP will expire in 5 minutes. Please do not share it with anyone.
                    </p>
                </div>
                </div>';
                
                $mail->send();
                
                // Store temp session for OTP verification
                $_SESSION['temp_user'] = [
                    'user_id' => $existingUser['user_id'],
                    'username' => $existingUser['username'],
                    'email' => $existingUser['email'],
                    'role' => $existingUser['role']
                ];
                
                header('Location: otp_verification.php');
                exit();
                
            } catch (Exception $e) {
                throw new Exception('Failed to send verification code. Please try again.');
            }
        }
        
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
            
            // Check if user is admin/manager/staff (skip 2FA)
            if (in_array($emailUser['role'], ['admin', 'manager', 'staff'])) {
                // Direct login for admin users
                $updateStmt = $pdo->prepare("UPDATE users SET google_id = ?, last_login = NOW() WHERE user_id = ?");
                $updateStmt->execute([$googleId, $emailUser['user_id']]);
                
                // Set session variables
                $_SESSION['user_id'] = $emailUser['user_id'];
                $_SESSION['username'] = $emailUser['username'];
                $_SESSION['email'] = $emailUser['email'];
                $_SESSION['role'] = $emailUser['role'];
                
                header('Location: admin/dashboard.php');
                exit();
            } else {
                // Customer login - require 2FA
                $otp = rand(100000, 999999);
                $otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
                
                // Update user with Google ID and OTP
                $updateStmt = $pdo->prepare("UPDATE users SET google_id = ?, otp = ?, otp_expiry = ?, last_login = NOW() WHERE user_id = ?");
                $updateStmt->execute([$googleId, $otp, $otp_expiry, $emailUser['user_id']]);
                
                // Send OTP via email
                require_once './PHPMailer/src/Exception.php';
                require_once './PHPMailer/src/PHPMailer.php';
                require_once './PHPMailer/src/SMTP.php';
                
                $mail = new PHPMailer(true);
                
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'siafitfuel@gmail.com';
                    $mail->Password   = 'felclcbkazuspzde';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    
                    // Recipients
                    $mail->setFrom('siafitfuel@gmail.com', 'FitFuel');
                    $mail->addAddress($emailUser['email']);
                    
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "Your OTP Code - FitFuel Google Login";
                    
                    $mail->Body = '
                    <div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;">
                    <div style="max-width: 500px; margin: auto; background: #ffffff; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0px 2px 5px rgba(0,0,0,0.1);">
                        <h2 style="color: #333;">🔐 Email Verification</h2>
                        <p style="font-size: 16px; color: #555;">
                        Hello ' . htmlspecialchars($emailUser['username']) . ', <br> Use the OTP below to complete your Google login:
                        </p>
                        <div style="font-size: 32px; font-weight: bold; color: #2c3e50; margin: 20px 0; letter-spacing: 4px;">
                        ' . $otp . '
                        </div>
                        <p style="font-size: 14px; color: #999;">
                        This OTP will expire in 5 minutes. Please do not share it with anyone.
                        </p>
                    </div>
                    </div>';
                    
                    $mail->send();
                    
                    // Store temp session for OTP verification
                    $_SESSION['temp_user'] = [
                        'user_id' => $emailUser['user_id'],
                        'username' => $emailUser['username'],
                        'email' => $emailUser['email'],
                        'role' => $emailUser['role']
                    ];
                    
                    header('Location: otp_verification.php');
                    exit();
                    
                } catch (Exception $e) {
                    throw new Exception('Failed to send verification code. Please try again.');
                }
            }
            
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
            
            // New customer users also need 2FA
            $otp = rand(100000, 999999);
            $otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
            
            // Update OTP in database
            $updateStmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE user_id = ?");
            $updateStmt->execute([$otp, $otp_expiry, $userId]);
            
            // Send OTP via email
            require_once './PHPMailer/src/Exception.php';
            require_once './PHPMailer/src/PHPMailer.php';
            require_once './PHPMailer/src/SMTP.php';
            
            $mail = new PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'siafitfuel@gmail.com';
                $mail->Password   = 'felclcbkazuspzde';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                
                // Recipients
                $mail->setFrom('siafitfuel@gmail.com', 'FitFuel');
                $mail->addAddress($email);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = "Welcome to FitFuel - Complete Your Registration";
                
                $mail->Body = '
                <div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;">
                <div style="max-width: 500px; margin: auto; background: #ffffff; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0px 2px 5px rgba(0,0,0,0.1);">
                    <h2 style="color: #333;">🎉 Welcome to FitFuel!</h2>
                    <p style="font-size: 16px; color: #555;">
                    Hello ' . htmlspecialchars($username) . ', <br> Welcome to FitFuel! Use the OTP below to complete your registration:
                    </p>
                    <div style="font-size: 32px; font-weight: bold; color: #2c3e50; margin: 20px 0; letter-spacing: 4px;">
                    ' . $otp . '
                    </div>
                    <p style="font-size: 14px; color: #999;">
                    This OTP will expire in 5 minutes. Please do not share it with anyone.
                    </p>
                </div>
                </div>';
                
                $mail->send();
                
                // Store temp session for OTP verification
                $_SESSION['temp_user'] = [
                    'user_id' => $userId,
                    'username' => $username,
                    'email' => $email,
                    'role' => 'customer'
                ];
                
                header('Location: otp_verification.php');
                exit();
                
            } catch (Exception $e) {
                throw new Exception('Failed to send verification code. Please try again.');
            }
        }
    }
    
} catch (Exception $e) {
    // Redirect back to login with error
    $_SESSION['google_error'] = $e->getMessage();
    header('Location: login.php');
    exit();
}
?>
