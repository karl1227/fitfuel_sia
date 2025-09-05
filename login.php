<?php
require_once 'config/database.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_email = trim($_POST['username_email']);
    $password = $_POST['password'];
    
    if (empty($username_email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        try {
            $pdo = getDBConnection();
            
            // Check if user exists by username or email
            $stmt = $pdo->prepare("SELECT user_id, username, email, password_hash, role, status FROM users WHERE (username = ? OR email = ?) AND status = 'active'");
            $stmt->execute([$username_email, $username_email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Update last login
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                $updateStmt->execute([$user['user_id']]);
                
                // Redirect based on role
                if ($user['role'] == 'admin' || $user['role'] == 'manager' || $user['role'] == 'staff') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: index.html');
                }
                exit();
            } else {
                $error = "Invalid username/email or password";
            }
        } catch (PDOException $e) {
            $error = "Login failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FitFuel</title>
    <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-bg {
            background-image: url('img/Banner/banner2.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .brand-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .login-panel {
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
        
        .login-btn {
            background: #1f2937;
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
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
<body class="font-body min-h-screen login-bg">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <!-- Left Panel - Branding -->
            <div class="brand-panel p-8 lg:p-12 rounded-2xl shadow-2xl">
                <div class="space-y-8">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <img src="img/LOGO-Fitfuel.png" alt="FitFuel Logo" class="w-12 h-12">
                        <h1 class="text-3xl font-bold text-black font-heading">FITFUEL</h1>
                    </div>
                    
                    <!-- Slogan -->
                    <div class="space-y-4">
                        <h2 class="text-2xl font-semibold text-black leading-relaxed">
                            Fuel Your Fitness, Find The<br>
                            Right Supplements, Track Your<br>
                            Orders & Power Up Your<br>
                            Performance.
                        </h2>
                    </div>
                    
                    <!-- Social Media -->
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
            </div>
            
            <!-- Right Panel - Login Form -->
            <div class="login-panel p-8 lg:p-12 rounded-2xl shadow-2xl">
                <div class="space-y-6">
                    <!-- Title -->
                    <h2 class="text-3xl font-bold text-black text-center font-heading">Login</h2>
                    
                    <!-- Error Message -->
                    <?php if (isset($error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Login Form -->
                    <form method="POST" class="space-y-6">
                        <!-- Username/Email Field -->
                        <div>
                            <input type="text" 
                                   name="username_email" 
                                   placeholder="Username or Email Address" 
                                   class="w-full px-4 py-3 rounded-lg input-field focus:outline-none"
                                   value="<?php echo isset($_POST['username_email']) ? htmlspecialchars($_POST['username_email']) : ''; ?>"
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
                                    onclick="togglePassword()" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye" id="passwordToggle"></i>
                            </button>
                        </div>
                        
                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center space-x-2 text-gray-700">
                                <input type="checkbox" name="remember" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span>Remember Me</span>
                            </label>
                            <a href="#" class="text-emerald-600 hover:text-emerald-700 text-sm">Forget Password?</a>
                        </div>
                        
                        <!-- Login Button -->
                        <button type="submit" 
                                class="w-full py-3 rounded-lg text-white font-semibold login-btn focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            LOGIN
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
                            <span class="text-gray-700 font-medium">Google</span>
                        </button>
                        
                        <!-- Signup Link -->
                        <div class="text-center text-gray-700">
                            <span>Don't Have An Account? </span>
                            <a href="registration.php" class="text-emerald-600 hover:text-emerald-700 font-medium underline">Signup Here</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggle');
            
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
    </script>
</body>
</html>
