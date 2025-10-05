<?php
session_start();
require_once 'config/database.php';
require_once 'config/audit_logger.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if user is in temp session (came from login)
if (!isset($_SESSION['temp_user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['temp_user']['user_id'];
$email = $_SESSION['temp_user']['email'];

// Handle OTP verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_otp'])) {
    $user_otp = trim($_POST['otp']);
    
    // Debug: Log the received OTP
    error_log("OTP Verification Attempt - User ID: $user_id, Received OTP: '$user_otp'");
    
    if (empty($user_otp)) {
        $error = "Please enter the OTP code";
    } else {
        try {
            $pdo = getDBConnection();
            $auditLogger = new AuditLogger();
            
            // Get user data with OTP
            $stmt = $pdo->prepare("SELECT user_id, username, email, role, otp, otp_expiry FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            // Debug: Log user data
            error_log("User data from DB - ID: " . ($user ? $user['user_id'] : 'NULL') . ", OTP: " . ($user ? $user['otp'] : 'NULL') . ", Role: " . ($user ? $user['role'] : 'NULL'));
            
            if ($user && $user['otp'] === $user_otp) {
                // Check if OTP is not expired
                $otp_expiry = strtotime($user['otp_expiry']);
                if ($otp_expiry >= time()) {
                    // OTP is valid - complete login
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Clear OTP from database
                    $clearStmt = $pdo->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL, last_login = NOW() WHERE user_id = ?");
                    $clearStmt->execute([$user['user_id']]);
                    
                    // Clear temp session
                    unset($_SESSION['temp_user']);
                    
                    // Log successful login
                    $auditLogger->logLogin($user['username'], true, $user['user_id']);
                    
                    // Debug: Log the redirect
                    error_log("OTP Verification Success - User: " . $user['username'] . ", Role: " . $user['role']);
                    
                    // Redirect based on role
                    if ($user['role'] == 'admin' || $user['role'] == 'manager' || $user['role'] == 'staff') {
                        error_log("Redirecting admin to: admin/dashboard.php");
                        header('Location: admin/dashboard.php');
                    } else {
                        error_log("Redirecting customer to: index.php");
                        header('Location: index.php');
                    }
                    exit();
                } else {
                    $error = "OTP has expired. Please try logging in again.";
                    // Clear temp session on expiry
                    unset($_SESSION['temp_user']);
                }
            } else {
                // Debug: Log OTP mismatch
                error_log("OTP Mismatch - User ID: $user_id, Expected: " . ($user ? $user['otp'] : 'NULL') . ", Received: $user_otp");
                $error = "Invalid OTP. Please check and try again.";
            }
        } catch (PDOException $e) {
            $error = "Verification failed. Please try again.";
        }
    }
}

// Handle resend OTP
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resend_otp'])) {
    try {
        $pdo = getDBConnection();
        
        // Generate new OTP
        $otp = rand(100000, 999999);
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        
        // Update OTP in database
        $updateStmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE user_id = ?");
        $updateStmt->execute([$otp, $otp_expiry, $user_id]);
        
        // Send new OTP via email
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
            $mail->Subject = "Your New OTP Code - FitFuel";
            
            $mail->Body = '
            <div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;">
            <div style="max-width: 500px; margin: auto; background: #ffffff; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0px 2px 5px rgba(0,0,0,0.1);">
                <h2 style="color: #333;">🔐 New OTP Code</h2>
                <p style="font-size: 16px; color: #555;">
                Hello, <br> Use the new OTP below to complete your verification:
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
            $success = "New OTP has been sent to your email address.";
            
        } catch (Exception $e) {
            $error = "Failed to send new OTP. Please try again.";
        }
        
    } catch (PDOException $e) {
        $error = "Failed to generate new OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - FitFuel</title>
    <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cousine:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .otp-bg {
            background-image: url('img/Banner/banner2.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .otp-panel {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }
        
        .otp-input {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 8px;
        }
        
        .otp-input:focus {
            background: white;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
        
        .verify-btn {
            background: #1f2937;
            transition: all 0.3s ease;
        }
        
        .verify-btn:hover {
            background: #374151;
        }
        
        .resend-btn {
            background: #059669;
            transition: all 0.3s ease;
        }
        
        .resend-btn:hover {
            background: #047857;
        }
    </style>
</head>
<body class="font-body min-h-screen otp-bg">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="otp-panel p-8 rounded-2xl shadow-2xl">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <img src="img/fitfuel_login.png" alt="FitFuel Logo" class="h-16 mx-auto mb-4">
                    <h2 class="text-3xl font-bold text-black font-heading">Two-Step Verification</h2>
                </div>
                
                <!-- Instructions -->
                <div class="text-center mb-8">
                    <p class="text-gray-600 mb-2">
                        We've sent a 6-digit verification code to:
                    </p>
                    <p class="font-semibold text-black"><?php echo htmlspecialchars($email); ?></p>
                    <p class="text-sm text-gray-500 mt-2">
                        Enter the code below to complete your login
                    </p>
                </div>
                
                <!-- Error Message -->
                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Success Message -->
                <?php if (isset($success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <!-- OTP Verification Form -->
                <form method="POST" class="space-y-6">
                    <div>
                        <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">
                            Enter Verification Code
                        </label>
                        <input type="text" 
                               name="otp" 
                               id="otp"
                               maxlength="6"
                               pattern="[0-9]{6}"
                               placeholder="000000" 
                               class="w-full px-4 py-4 rounded-lg otp-input focus:outline-none"
                               required
                               autocomplete="off">
                    </div>
                    
                    <button type="submit" 
                            name="verify_otp"
                            class="w-full py-3 rounded-lg text-white font-semibold verify-btn focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        <i class="fas fa-check mr-2"></i>
                        Verify Code
                    </button>
                </form>
                
                <!-- Resend OTP -->
                <div class="mt-6 text-center">
                    <p class="text-gray-600 mb-4">Didn't receive the code?</p>
                    <form method="POST" class="inline">
                        <button type="submit" 
                                name="resend_otp"
                                class="px-6 py-2 rounded-lg text-white font-semibold resend-btn focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            <i class="fas fa-redo mr-2"></i>
                            Resend OTP
                        </button>
                    </form>
                </div>
                
                <!-- Back to Login -->
                <div class="mt-6 text-center">
                    <a href="login.php" class="text-gray-600 hover:text-gray-800 text-sm">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-focus on OTP input
        document.getElementById('otp').focus();
        
        // Auto-format OTP input (numbers only)
        document.getElementById('otp').addEventListener('input', function(e) {
            // Remove any non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Handle paste events
        document.getElementById('otp').addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const numbers = paste.replace(/[^0-9]/g, '').substring(0, 6);
            this.value = numbers;
        });
        
        // Add form submission debug
        document.querySelector('form').addEventListener('submit', function(e) {
            console.log('Form submitting with OTP:', document.getElementById('otp').value);
        });
        
        // Countdown timer for resend button
        let countdown = 60;
        const resendBtn = document.querySelector('button[name="resend_otp"]');
        const originalText = resendBtn.innerHTML;
        
        function updateCountdown() {
            if (countdown > 0) {
                resendBtn.disabled = true;
                resendBtn.innerHTML = `<i class="fas fa-clock mr-2"></i>Resend in ${countdown}s`;
                countdown--;
                setTimeout(updateCountdown, 1000);
            } else {
                resendBtn.disabled = false;
                resendBtn.innerHTML = originalText;
            }
        }
        
        // Start countdown
        updateCountdown();
    </script>
</body>
</html>
