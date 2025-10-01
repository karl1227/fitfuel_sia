<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function active_link($f){
  $c = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
  return $c === $f ? 'text-emerald-600 font-semibold' : 'hover:text-emerald-600';
}

$pdo = getDBConnection();
$alert = ['type'=>'','msg'=>''];

/* Determine password column */
$cols = $pdo->query("
  SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
")->fetchAll(PDO::FETCH_COLUMN);

$has_hash = in_array('password_hash', (array)$cols, true);
$has_pw   = in_array('password', (array)$cols, true);
$pw_col = $has_hash ? 'password_hash' : ($has_pw ? 'password' : null);

/* Fetch user */
$st = $pdo->prepare("SELECT user_id, username, profile_picture, ".($pw_col ? $pw_col : "'' AS password_hash")." AS pw FROM users WHERE user_id=?");
$st->execute([$user_id]);
$user = $st->fetch(PDO::FETCH_ASSOC) ?: ['username'=>'','profile_picture'=>null,'pw'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (!$pw_col) throw new Exception("No password column found on 'users' table.");

    $current = (string)($_POST['current_password'] ?? '');
    $new     = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');

    if ($new === '' || strlen($new) < 8) throw new Exception('New password must be at least 8 characters.');
    if ($new !== $confirm)             throw new Exception('New password confirmation does not match.');

    // Prevent reuse
    $same = false;
    if (!empty($user['pw'])) {
      if (preg_match('/^\$2[aby]\$|\$argon2/i', $user['pw'])) $same = password_verify($new, $user['pw']);
      else $same = hash_equals($user['pw'], $new) || hash_equals($user['pw'], md5($new));
    }
    if ($same) throw new Exception('New password must be different from your current password.');

    // Verify current
    $ok = false;
    if (!empty($user['pw'])) {
      if (preg_match('/^\$2[aby]\$|\$argon2/i', $user['pw'])) $ok = password_verify($current, $user['pw']);
      else $ok = hash_equals($user['pw'], $current) || hash_equals($user['pw'], md5($current));
    }
    if (!$ok) throw new Exception('Current password is incorrect.');

    $new_hash = password_hash($new, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET {$pw_col}=? WHERE user_id=?")->execute([$new_hash, $user_id]);
    session_regenerate_id(true);
    $alert = ['type'=>'success', 'msg'=>'Password updated successfully.'];
  } catch (Exception $e) {
    $alert = ['type'=>'error', 'msg'=>$e->getMessage()];
  }
}

/* cart badge */
$cart_count = 0;
try{
  $cs=$pdo->prepare("SELECT COALESCE(SUM(ci.quantity),0) c
                     FROM cart c LEFT JOIN cart_items ci ON c.cart_id=ci.cart_id
                     WHERE c.user_id=?");
  $cs->execute([$user_id]); $cart_count=(int)($cs->fetch()['c']??0);
}catch(Throwable $e){}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Change Password - FitFuel</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#f6f6f6] text-slate-700 min-h-screen flex flex-col">
  <nav class="bg-white text-black py-2">
    <div class="container mx-auto px-4 flex justify-end space-x-6 text-sm">
      <a class="hover:text-emerald-400">Review</a>
      <a class="hover:text-emerald-400">Help</a>
      <a href="logout.php" class="hover:text-emerald-400">Logout</a>
    </div>
  </nav>
  <nav class="bg-black py-4">
    <div class="container mx-auto px-4 flex items-center justify-between">
      <a href="index.php"><img src="img/LOGO-Fitfuel.png" width="75" alt=""></a>
      <div class="hidden md:flex items-center space-x-8">
        <a href="index.php" class="text-white hover:text-emerald-600">Home</a>
        <a href="shop.php" class="text-white hover:text-emerald-600">Shop</a>
        <a href="#" class="text-white hover:text-emerald-600">About</a>
        <a href="#" class="text-white hover:text-emerald-600">Contact</a>
      </div>
      <div class="flex items-center space-x-4">
        <a href="cart.php" class="p-2 text-white hover:text-emerald-600"><i class="fas fa-shopping-cart text-xl"></i></a>
        <a href="profile.php" class="p-2 text-white hover:text-emerald-600"><i class="fas fa-user text-xl"></i></a>
      </div>
    </div>
  </nav>

  <main class="flex-1">
    <div class="container mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-4 gap-6">
      <?php include __DIR__.'/sidebar.php'; ?>

      <section class="md:col-span-3 bg-white rounded-lg border border-gray-200">
        <div class="p-6 border-b"><h1 class="text-[20px] font-semibold">Change Password</h1></div>

        <?php if($alert['type']==='success'):?>
          <div class="mx-6 mt-4 rounded bg-emerald-50 text-emerald-700 px-4 py-3 border border-emerald-200"><?php echo h($alert['msg']); ?></div>
        <?php elseif($alert['type']==='error'):?>
          <div class="mx-6 mt-4 rounded bg-red-50 text-red-700 px-4 py-3 border border-red-200"><?php echo h($alert['msg']); ?></div>
        <?php endif;?>

        <form class="p-6 max-w-lg" method="post" autocomplete="off" id="passwordForm">
          <label class="block mb-3">
            <span class="text-sm text-slate-600">Current Password</span>
            <input type="password" name="current_password" class="w-full border rounded px-3 py-2" required>
          </label>
          
          <label class="block mb-3">
            <span class="text-sm text-slate-600">New Password</span>
            <input type="password" name="new_password" id="newPassword" class="w-full border rounded px-3 py-2" minlength="8" required>
            
            <!-- Password Strength Meter -->
            <div class="mt-2">
              <div class="flex items-center space-x-2 mb-2">
                <div class="flex-1 bg-gray-200 rounded-full h-2">
                  <div id="strengthMeter" class="h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <span id="strengthText" class="text-xs font-medium text-gray-500">Enter password</span>
              </div>
              
              <!-- Password Requirements -->
              <div class="text-xs text-gray-600 space-y-1">
                <div class="flex items-center space-x-2">
                  <i id="req-length" class="fas fa-times text-red-500"></i>
                  <span>At least 8 characters</span>
                </div>
                <div class="flex items-center space-x-2">
                  <i id="req-uppercase" class="fas fa-times text-red-500"></i>
                  <span>One uppercase letter</span>
                </div>
                <div class="flex items-center space-x-2">
                  <i id="req-lowercase" class="fas fa-times text-red-500"></i>
                  <span>One lowercase letter</span>
                </div>
                <div class="flex items-center space-x-2">
                  <i id="req-number" class="fas fa-times text-red-500"></i>
                  <span>One number</span>
                </div>
                <div class="flex items-center space-x-2">
                  <i id="req-special" class="fas fa-times text-red-500"></i>
                  <span>One special character</span>
                </div>
              </div>
            </div>
          </label>
          
          <label class="block mb-4">
            <span class="text-sm text-slate-600">Confirm New Password</span>
            <input type="password" name="confirm_password" id="confirmPassword" class="w-full border rounded px-3 py-2" minlength="8" required>
            
            <!-- Password Match Indicator -->
            <div id="matchIndicator" class="mt-2 text-xs hidden">
              <div class="flex items-center space-x-2">
                <i id="matchIcon" class="fas"></i>
                <span id="matchText"></span>
              </div>
            </div>
          </label>
          
          <button type="submit" id="submitBtn" class="bg-gray-400 text-white px-6 py-2 rounded cursor-not-allowed" disabled>Update Password</button>
        </form>
      </section>
    </div>
  </main>

  <?php // partials/footer.php ?>
<footer class="bg-slate-800 text-white py-12 mt-auto">
  <div class="container mx-auto px-4 text-center">
    <p>&copy; 2024 FitFuel. All rights reserved.</p>
  </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.getElementById('newPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const strengthMeter = document.getElementById('strengthMeter');
    const strengthText = document.getElementById('strengthText');
    const matchIndicator = document.getElementById('matchIndicator');
    const matchIcon = document.getElementById('matchIcon');
    const matchText = document.getElementById('matchText');
    const submitBtn = document.getElementById('submitBtn');
    
    // Password requirements elements
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');
    
    let passwordStrength = 0;
    let passwordsMatch = false;
    
    // Check password requirements
    function checkPasswordRequirements(password) {
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password),
            special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
        };
        
        // Update requirement icons
        reqLength.className = requirements.length ? 'fas fa-check text-green-500' : 'fas fa-times text-red-500';
        reqUppercase.className = requirements.uppercase ? 'fas fa-check text-green-500' : 'fas fa-times text-red-500';
        reqLowercase.className = requirements.lowercase ? 'fas fa-check text-green-500' : 'fas fa-times text-red-500';
        reqNumber.className = requirements.number ? 'fas fa-check text-green-500' : 'fas fa-times text-red-500';
        reqSpecial.className = requirements.special ? 'fas fa-check text-green-500' : 'fas fa-times text-red-500';
        
        // Calculate strength
        const metRequirements = Object.values(requirements).filter(Boolean).length;
        passwordStrength = metRequirements;
        
        // Update strength meter
        const percentage = (metRequirements / 5) * 100;
        strengthMeter.style.width = percentage + '%';
        
        // Update strength text and colors
        if (metRequirements === 0) {
            strengthText.textContent = 'Enter password';
            strengthText.className = 'text-xs font-medium text-gray-500';
            strengthMeter.className = 'h-2 rounded-full transition-all duration-300 bg-gray-300';
        } else if (metRequirements <= 2) {
            strengthText.textContent = 'Weak';
            strengthText.className = 'text-xs font-medium text-red-500';
            strengthMeter.className = 'h-2 rounded-full transition-all duration-300 bg-red-500';
        } else if (metRequirements <= 3) {
            strengthText.textContent = 'Fair';
            strengthText.className = 'text-xs font-medium text-yellow-500';
            strengthMeter.className = 'h-2 rounded-full transition-all duration-300 bg-yellow-500';
        } else if (metRequirements <= 4) {
            strengthText.textContent = 'Good';
            strengthText.className = 'text-xs font-medium text-blue-500';
            strengthMeter.className = 'h-2 rounded-full transition-all duration-300 bg-blue-500';
        } else {
            strengthText.textContent = 'Strong';
            strengthText.className = 'text-xs font-medium text-green-500';
            strengthMeter.className = 'h-2 rounded-full transition-all duration-300 bg-green-500';
        }
        
        updateSubmitButton();
    }
    
    // Check if passwords match
    function checkPasswordMatch() {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword.length === 0) {
            matchIndicator.classList.add('hidden');
            passwordsMatch = false;
        } else if (newPassword === confirmPassword) {
            matchIndicator.classList.remove('hidden');
            matchIcon.className = 'fas fa-check text-green-500';
            matchText.textContent = 'Passwords match';
            matchText.className = 'text-green-500';
            passwordsMatch = true;
        } else {
            matchIndicator.classList.remove('hidden');
            matchIcon.className = 'fas fa-times text-red-500';
            matchText.textContent = 'Passwords do not match';
            matchText.className = 'text-red-500';
            passwordsMatch = false;
        }
        
        updateSubmitButton();
    }
    
    // Update submit button state
    function updateSubmitButton() {
        const isStrongPassword = passwordStrength >= 4; // At least 4/5 requirements met
        const canSubmit = isStrongPassword && passwordsMatch;
        
        if (canSubmit) {
            submitBtn.disabled = false;
            submitBtn.className = 'bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded transition-colors';
        } else {
            submitBtn.disabled = true;
            submitBtn.className = 'bg-gray-400 text-white px-6 py-2 rounded cursor-not-allowed';
        }
    }
    
    // Event listeners
    newPasswordInput.addEventListener('input', function() {
        checkPasswordRequirements(this.value);
        checkPasswordMatch(); // Re-check match when new password changes
    });
    
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    
    // Prevent form submission if validation fails
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        if (!passwordsMatch || passwordStrength < 4) {
            e.preventDefault();
            alert('Please ensure your password meets all requirements and both passwords match.');
        }
    });
});
</script>

</body>
</html>
