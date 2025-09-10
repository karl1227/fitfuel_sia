<?php
require_once 'config/database.php';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validation
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();
            
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $errors[] = "Username or email already exists";
            } else {
                // Hash password and insert user
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, status) VALUES (?, ?, ?, 'customer', 'active')");
                $stmt->execute([$username, $email, $password_hash]);
                
                // Registration successful
                $_SESSION['success'] = "Registration successful! Please login with your credentials.";
                header('Location: login.php');
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - FitFuel</title>
    <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cousine:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .register-bg {
            background-image: url('img/Banner/banner2.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .register-panel {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }
        
        .google-btn {
            background: white;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .google-btn:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }
        
        .register-btn {
            background: #1f2937;
            transition: all 0.3s ease;
        }
        
        .register-btn:hover {
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
        
        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }
        
        .social-icon:hover {
            transform: scale(1.1);
        }
        
        .facebook-icon {
            background: #1877f2;
            color: white;
        }
        
        .google-icon {
            background: white;
            color: #4285f4;
            border: 1px solid #e5e7eb;
        }
        
        .instagram-icon {
            background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);
            color: white;
        }
    </style>
</head>
<body class="font-body min-h-screen register-bg">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-6xl">
            <div class="register-panel p-8 lg:p-12 rounded-2xl shadow-2xl">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <!-- Left Side - Branding -->
                    <div class="space-y-8 flex flex-col justify-between h-full">
                        <!-- Logo - Top Left -->
                        <div class="flex justify-start">
                            <img src="img/fitfuel_login.png" alt="FitFuel Logo" class="h-16">
                        </div>
                        
                        <!-- Slogan - Center -->
                        <div class="space-y-4">
                            <h2 class="text-3xl font-semibold text-black leading-relaxed" style="font-family: 'Cousine', monospace;">
                                Fuel your fitness,<br>
                                find the right supplements,<br>
                                track your orders & power up<br>
                                your performance.
                            </h2>
                        </div>
                        
                        <!-- Social Media - Bottom Left -->
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <h3 class="text-lg font-semibold text-black">Connect With Us</h3>
                                <div class="flex-1 h-px bg-gray-300"></div>
                            </div>
                            
                            <div class="flex space-x-4">
                                <a href="#" class="social-icon facebook-icon">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="social-icon google-icon">
                                    <i class="fab fa-google"></i>
                                </a>
                                <a href="#" class="social-icon instagram-icon">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Side - Registration Form -->
                    <div class="space-y-6">
                        <!-- Title -->
                        <h2 class="text-3xl font-bold text-black text-center font-heading">Create Account</h2>
                    
                    <!-- Success Message -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                            <ul class="list-disc list-inside space-y-1">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Registration Form -->
                    <form method="POST" class="space-y-6">
                        <!-- Username Field -->
                        <div>
                            <input type="text" 
                                   name="username" 
                                   placeholder="Username" 
                                   class="w-full px-4 py-3 rounded-lg input-field focus:outline-none"
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   required>
                        </div>
                        
                        <!-- Email Field -->
                        <div>
                            <input type="email" 
                                   name="email" 
                                   placeholder="Email Address" 
                                   class="w-full px-4 py-3 rounded-lg input-field focus:outline-none"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   required>
                        </div>
                        
                        <!-- Password Field -->
                        <div class="relative">
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   placeholder="Password" 
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
                                   placeholder="Confirm Password" 
                                   class="w-full px-4 py-3 rounded-lg input-field focus:outline-none pr-12"
                                   required>
                            <button type="button" 
                                    onclick="togglePassword('confirm_password')" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye" id="confirmPasswordToggle"></i>
                            </button>
                        </div>
                        
                        <!-- Terms and Conditions -->
                        <div class="flex items-start space-x-2">
                            <input type="checkbox" 
                                   name="terms" 
                                   id="terms"
                                   class="mt-1 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                   required>
                            <label for="terms" class="text-sm text-gray-700">
                                I agree to the <a href="#" class="text-emerald-600 hover:text-emerald-700 underline">Terms and Conditions</a> 
                                and <a href="#" class="text-emerald-600 hover:text-emerald-700 underline">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <!-- Register Button -->
                        <button type="submit" 
                                class="w-full py-3 rounded-lg text-white font-semibold register-btn focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            CREATE ACCOUNT
                        </button>
                        
                        <!-- OR Divider -->
                        <div class="flex items-center space-x-4">
                            <div class="flex-1 h-px bg-gray-300"></div>
                            <span class="text-gray-500 font-medium">OR</span>
                            <div class="flex-1 h-px bg-gray-300"></div>
                        </div>
                        
                        <!-- Google SSO Button -->
                        <button type="button" 
                                onclick="handleGoogleSSO()"
                                class="w-full py-3 rounded-lg google-btn flex items-center justify-center space-x-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            <i class="fab fa-google text-blue-500"></i>
                            <span class="text-gray-700 font-medium">Sign up with Google</span>
                        </button>
                        
                        <!-- Login Link -->
                        <div class="text-center text-gray-700">
                            <span>Already Have An Account? </span>
                            <a href="login.php" class="text-emerald-600 hover:text-emerald-700 font-medium underline">Login Here</a>
                        </div>
                    </form>
                    </div>
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
        
        function handleGoogleSSO() {
            // Google SSO implementation would go here
            alert('Google SSO functionality will be implemented in the next phase');
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
