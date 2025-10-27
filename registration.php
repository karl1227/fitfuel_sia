<?php
require_once 'config/database.php';
require_once 'config/google_config.php';

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
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    } elseif (!preg_match('/\d/', $password)) {
        $errors[] = "Password must contain at least one number";
    } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        $errors[] = "Password must contain at least one special character";
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
                    
                    <!-- Google SSO Error Message -->
                    <?php if (isset($_SESSION['google_error'])): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                            <?php echo htmlspecialchars($_SESSION['google_error']); unset($_SESSION['google_error']); ?>
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
                                   placeholder="Enter password" 
                                   class="w-full px-4 py-3 rounded-lg input-field focus:outline-none pr-12"
                                   required>
                            <button type="button" 
                                    onclick="togglePassword('password')" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye" id="passwordToggle"></i>
                            </button>
                        </div>
                        
                        <!-- Password Strength Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2 transition-all duration-300" id="strength-container" style="height: 0; margin: 0; padding: 0;">
                            <div class="bg-gray-400 h-2 rounded-full transition-all duration-300" id="strength-bar" style="width: 0%;"></div>
                        </div>
                        
                        <!-- Password Requirements -->
                        <div class="bg-gray-50 p-4 rounded-lg transition-all duration-500 linear overflow-hidden" id="password-requirements" style="max-height: 0; opacity: 0; margin: 0;">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Password Requirements:</h4>
                            <ul class="space-y-1 text-sm">
                                <li class="flex items-center" id="req-length">
                                    <i class="fas fa-times text-red-500 mr-2"></i>
                                    <span>At least 8 characters</span>
                                </li>
                                <li class="flex items-center" id="req-uppercase">
                                    <i class="fas fa-times text-red-500 mr-2"></i>
                                    <span>One uppercase letter</span>
                                </li>
                                <li class="flex items-center" id="req-lowercase">
                                    <i class="fas fa-times text-red-500 mr-2"></i>
                                    <span>One lowercase letter</span>
                                </li>
                                <li class="flex items-center" id="req-number">
                                    <i class="fas fa-times text-red-500 mr-2"></i>
                                    <span>One number</span>
                                </li>
                                <li class="flex items-center" id="req-special">
                                    <i class="fas fa-times text-red-500 mr-2"></i>
                                    <span>One special character</span>
                                </li>
                            </ul>
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
                                <i class="fas fa-eye" id="confirm_passwordToggle"></i>
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
                        <a href="<?php echo getGoogleAuthUrl(); ?>" 
                           class="w-full py-3 rounded-lg google-btn flex items-center justify-center space-x-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-decoration-none">
                            <i class="fab fa-google text-blue-500"></i>
                            <span class="text-gray-700 font-medium">Sign up with Google</span>
                        </a>
                        
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
        // Simple password validation
        function updatePasswordRequirements() {
            const password = document.getElementById('password').value;
            
            // Check each requirement
            const length = password.length >= 8;
            const uppercase = /[A-Z]/.test(password);
            const lowercase = /[a-z]/.test(password);
            const number = /\d/.test(password);
            const special = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
            
            // Update icons
            updateIcon('req-length', length);
            updateIcon('req-uppercase', uppercase);
            updateIcon('req-lowercase', lowercase);
            updateIcon('req-number', number);
            updateIcon('req-special', special);
            
            // Update strength bar
            const strength = [length, uppercase, lowercase, number, special].filter(Boolean).length;
            const strengthBar = document.getElementById('strength-bar');
            if (strengthBar) {
                strengthBar.style.width = (strength / 5 * 100) + '%';
                
                if (strength === 0) strengthBar.className = 'bg-gray-400 h-2 rounded-full transition-all duration-300';
                else if (strength === 1) strengthBar.className = 'bg-red-500 h-2 rounded-full transition-all duration-300';
                else if (strength === 2) strengthBar.className = 'bg-orange-500 h-2 rounded-full transition-all duration-300';
                else if (strength === 3) strengthBar.className = 'bg-yellow-500 h-2 rounded-full transition-all duration-300';
                else if (strength === 4) strengthBar.className = 'bg-blue-500 h-2 rounded-full transition-all duration-300';
                else if (strength === 5) strengthBar.className = 'bg-green-500 h-2 rounded-full transition-all duration-300';
            }
            
            // Show/hide requirements
            const requirements = document.getElementById('password-requirements');
            if (requirements) {
                if (password.length > 0) {
                    requirements.style.maxHeight = '200px';
                    requirements.style.opacity = '1';
                    requirements.style.margin = '8px 0';
                } else {
                    requirements.style.maxHeight = '0';
                    requirements.style.opacity = '0';
                    requirements.style.margin = '0';
                }
            }
            
            // Update submit button
            updateSubmitButton();
        }
        
        function updateIcon(elementId, isValid) {
            const element = document.getElementById(elementId);
            if (element) {
                const icon = element.querySelector('i');
                if (icon) {
                    icon.className = isValid ? 'fas fa-check text-green-500 mr-2' : 'fas fa-times text-red-500 mr-2';
                }
            }
        }
        
        function updateSubmitButton() {
            const submitBtn = document.querySelector('button[type="submit"]');
            if (!submitBtn) return;
            
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const termsChecked = document.getElementById('terms').checked;
            
            const length = password.length >= 8;
            const uppercase = /[A-Z]/.test(password);
            const lowercase = /[a-z]/.test(password);
            const number = /\d/.test(password);
            const special = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
            const strongPassword = length && uppercase && lowercase && number && special;
            const passwordsMatch = password === confirmPassword && password !== '';
            const allFieldsFilled = username !== '' && email !== '' && password !== '' && confirmPassword !== '';
            
            const canSubmit = strongPassword && passwordsMatch && allFieldsFilled && termsChecked;
            
            if (canSubmit) {
                submitBtn.disabled = false;
                submitBtn.className = 'w-full py-3 rounded-lg text-white font-semibold register-btn focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2';
            } else {
                submitBtn.disabled = true;
                submitBtn.className = 'w-full py-3 rounded-lg text-white font-semibold bg-gray-400 cursor-not-allowed focus:outline-none';
            }
        }
        
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(fieldId + 'Toggle');
            
            if (passwordField && toggleIcon) {
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
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Password field
            const passwordField = document.getElementById('password');
            if (passwordField) {
                passwordField.addEventListener('input', updatePasswordRequirements);
                passwordField.addEventListener('focus', function() {
                    if (this.value.length > 0) {
                        const requirements = document.getElementById('password-requirements');
                        if (requirements) {
                            requirements.style.maxHeight = '200px';
                            requirements.style.opacity = '1';
                            requirements.style.margin = '8px 0';
                        }
                    }
                });
            }
            
            // Other fields
            const fields = ['username', 'email', 'confirm_password'];
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', updateSubmitButton);
                }
            });
            
            // Terms checkbox
            const termsCheckbox = document.getElementById('terms');
            if (termsCheckbox) {
                termsCheckbox.addEventListener('change', updateSubmitButton);
            }
            
            // Confirm password specific
            const confirmPasswordField = document.getElementById('confirm_password');
            if (confirmPasswordField) {
                confirmPasswordField.addEventListener('input', function() {
                    const password = document.getElementById('password').value;
                    const confirmPassword = this.value;
                    
                    if (password !== confirmPassword) {
                        this.setCustomValidity('Passwords do not match');
                    } else {
                        this.setCustomValidity('');
                    }
                    
                    updateSubmitButton();
                });
            }
            
            // Form submission
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const username = document.getElementById('username').value.trim();
                    const email = document.getElementById('email').value.trim();
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    const termsChecked = document.getElementById('terms').checked;
                    
                    const length = password.length >= 8;
                    const uppercase = /[A-Z]/.test(password);
                    const lowercase = /[a-z]/.test(password);
                    const number = /\d/.test(password);
                    const special = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
                    const strongPassword = length && uppercase && lowercase && number && special;
                    const passwordsMatch = password === confirmPassword && password !== '';
                    const allFieldsFilled = username !== '' && email !== '' && password !== '' && confirmPassword !== '';
                    
                    if (!strongPassword || !passwordsMatch || !allFieldsFilled || !termsChecked) {
                        e.preventDefault();
                        alert('Please fill in all fields, ensure your password meets all requirements, passwords match, and agree to the terms.');
                    }
                });
            }
            
            // Initialize
            updateSubmitButton();
        });
    </script>
</body>
</html>
