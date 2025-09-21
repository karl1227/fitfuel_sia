<?php
require_once 'config/database.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($token) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        try {
            $pdo = getDBConnection();
            
            // Verify token
            $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
            $stmt->execute([$token]);
            $resetData = $stmt->fetch();
            
            if (!$resetData) {
                $error = "Invalid or expired reset token";
            } else {
                // Update password
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                $stmt->execute([$passwordHash, $resetData['email']]);
                
                // Delete used token
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->execute([$token]);
                
                $success = "Password reset successfully! You can now login with your new password.";
            }
        } catch (Exception $e) {
            $error = "An error occurred. Please try again.";
        }
    }
}

// If no token provided, show error
if (empty($token) && !$success) {
    $error = "Invalid reset link";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - FitFuel</title>
    <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cousine:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .reset-bg {
            background-image: url('img/Banner/banner2.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .reset-panel {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }
        
        .reset-btn {
            background: #1f2937;
            transition: all 0.3s ease;
        }
        
        .reset-btn:hover {
            background: #374151;
        }
        
        .input-field {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            background: white;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
    </style>
</head>
<body class="font-body min-h-screen reset-bg">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="reset-panel p-8 lg:p-12 rounded-2xl shadow-2xl">
                <div class="space-y-6">
                    <!-- Logo -->
                    <div class="flex justify-center">
                        <img src="img/fitfuel_login.png" alt="FitFuel Logo" class="h-16">
                    </div>
                    
                    <!-- Title -->
                    <h2 class="text-3xl font-bold text-black text-center font-heading">Reset Password</h2>
                    
                    <!-- Success Message -->
                    <?php if ($success): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                        <div class="text-center">
                            <a href="login.php" class="text-emerald-600 hover:text-emerald-700 font-medium underline">Back to Login</a>
                        </div>
                    <?php else: ?>
                    
                    <!-- Error Message -->
                    <?php if ($error): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Reset Form -->
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <!-- New Password Field -->
                        <div class="relative">
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   placeholder="New Password" 
                                   class="w-full px-4 py-3 rounded-lg input-field focus:outline-none pr-12"
                                   required>
                            <button type="button" 
                                    onclick="togglePassword('password')" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye" id="passwordToggle"></i>
                            </button>
                        </div>
                        
                        <!-- Confirm Password Field -->
                        <div class="relative">
                            <input type="password" 
                                   name="confirm_password" 
                                   id="confirm_password"
                                   placeholder="Confirm New Password" 
                                   class="w-full px-4 py-3 rounded-lg input-field focus:outline-none pr-12"
                                   required>
                            <button type="button" 
                                    onclick="togglePassword('confirm_password')" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye" id="confirmPasswordToggle"></i>
                            </button>
                        </div>
                        
                        <!-- Reset Button -->
                        <button type="submit" 
                                class="w-full py-3 rounded-lg text-white font-semibold reset-btn focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            Reset Password
                        </button>
                        
                        <!-- Back to Login -->
                        <div class="text-center text-gray-700">
                            <span>Remember your password? </span>
                            <a href="login.php" class="text-emerald-600 hover:text-emerald-700 font-medium underline">Back to Login</a>
                        </div>
                    </form>
                    
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(fieldId + 'Toggle');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
