<?php
session_start();

require_once 'config/database.php';

if (!isset($_SESSION['temp_user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_otp = $_POST['otp'];
    $stored_otp = $_SESSION['temp_user']['otp'];
    $user_id = $_SESSION['temp_user']['user_id'];

    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND otp = ?");
        $stmt->execute([$user_id, $user_otp]);
        $data = $stmt->fetch();

        if ($data) {
            $otp_expiry = strtotime($data['otp_expiry']);
            if ($otp_expiry >= time()) {
                // OTP is valid, complete login
                $_SESSION['user_id'] = $data['user_id'];
                $_SESSION['username'] = $data['username'];
                $_SESSION['email'] = $data['email'];
                $_SESSION['role'] = $data['role'];
                
                // Clear OTP from database
                $clearStmt = $pdo->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL WHERE user_id = ?");
                $clearStmt->execute([$user_id]);
                
                // Clear temp session
                unset($_SESSION['temp_user']);
                
                // Redirect based on role
                if ($data['role'] == 'admin' || $data['role'] == 'manager' || $data['role'] == 'staff') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "OTP has expired. Please try again.";
            }
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    } catch (PDOException $e) {
        $error = "Verification failed. Please try again.";
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
    <link rel="stylesheet" href="css/style.css">
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
        
        .verify-btn {
            background: #1f2937;
            transition: all 0.3s ease;
        }
        
        .verify-btn:hover {
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
        
        .otp-input {
            font-size: 24px;
            text-align: center;
            letter-spacing: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body class="font-body min-h-screen otp-bg">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="otp-panel p-8 rounded-2xl shadow-2xl">
                <!-- Logo -->
                <div class="flex justify-center mb-6">
                    <img src="img/fitfuel_login.png" alt="FitFuel Logo" class="h-16">
                </div>
                
                <!-- Title -->
                <h2 class="text-3xl font-bold text-black text-center font-heading mb-2">Two-Step Verification</h2>
                
                <!-- Description -->
                <p class="text-gray-600 text-center mb-8">
                    Enter the 6-digit OTP code that has been sent to your email address:<br>
                    <span class="font-semibold text-black"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                </p>
                
                <!-- Error Message -->
                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <!-- OTP Form -->
                <form method="POST" class="space-y-6">
                    <div>
                        <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">Enter OTP Code:</label>
                        <input type="text" 
                               name="otp" 
                               id="otp"
                               pattern="\d{6}" 
                               maxlength="6"
                               placeholder="000000" 
                               class="w-full px-4 py-4 rounded-lg input-field focus:outline-none otp-input"
                               required>
                    </div>
                    
                    <button type="submit" 
                            class="w-full py-3 rounded-lg text-white font-semibold verify-btn focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        VERIFY OTP
                    </button>
                </form>
                
                <!-- Back to Login -->
                <div class="text-center mt-6">
                    <a href="login.php" class="text-emerald-600 hover:text-emerald-700 font-medium underline">
                        Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-format OTP input
        document.getElementById('otp').addEventListener('input', function(e) {
            // Remove any non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Limit to 6 digits
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
        });
        
        // Auto-submit when 6 digits are entered
        document.getElementById('otp').addEventListener('input', function(e) {
            if (this.value.length === 6) {
                // Small delay to show the complete OTP
                setTimeout(() => {
                    this.form.submit();
                }, 500);
            }
        });
        
        // Focus on OTP input when page loads
        window.onload = function() {
            document.getElementById('otp').focus();
        };
    </script>
</body>
</html>
