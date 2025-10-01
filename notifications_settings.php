<?php
// Show errors while developing (remove later)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$pdo = getDBConnection();
$alert = ['type'=>'','msg'=>''];

/* Ensure settings table exists */
$pdo->exec("
  CREATE TABLE IF NOT EXISTS user_notifications (
    user_id INT NOT NULL,
    email_enabled TINYINT(1) NOT NULL DEFAULT 1,
    sms_enabled   TINYINT(1) NOT NULL DEFAULT 0,
    push_enabled  TINYINT(1) NOT NULL DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
try {
  $pdo->exec("ALTER TABLE user_notifications
    ADD CONSTRAINT fk_user_notifications_user
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE");
} catch (Throwable $e) {}

/* Load user for sidebar */
$usrStmt = $pdo->prepare("SELECT username, profile_picture FROM users WHERE user_id = ?");
$usrStmt->execute([$user_id]);
$user = $usrStmt->fetch(PDO::FETCH_ASSOC) ?: ['username'=>'','profile_picture'=>null];

/* Load or create default settings */
$st = $pdo->prepare("SELECT email_enabled, sms_enabled, push_enabled FROM user_notifications WHERE user_id=?");
$st->execute([$user_id]);
$settings = $st->fetch(PDO::FETCH_ASSOC);
if (!$settings) {
  $pdo->prepare("INSERT INTO user_notifications (user_id) VALUES (?)")->execute([$user_id]);
  $settings = ['email_enabled'=>1,'sms_enabled'=>0,'push_enabled'=>1];
}

/* Save */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $email = isset($_POST['email_enabled']) ? 1 : 0;
    $sms   = isset($_POST['sms_enabled'])   ? 1 : 0;
    $push  = isset($_POST['push_enabled'])  ? 1 : 0;

    $pdo->prepare("UPDATE user_notifications SET email_enabled=?, sms_enabled=?, push_enabled=? WHERE user_id=?")
        ->execute([$email,$sms,$push,$user_id]);

    $settings = ['email_enabled'=>$email, 'sms_enabled'=>$sms, 'push_enabled'=>$push];
    $alert = ['type'=>'success','msg'=>'Notification settings updated.'];
  } catch (Throwable $e) {
    $alert = ['type'=>'error','msg'=>$e->getMessage()];
  }
}

/* cart count for header */
$cart_count = 0;
try {
  $cs = $pdo->prepare("SELECT COALESCE(SUM(ci.quantity),0) c
    FROM cart c LEFT JOIN cart_items ci ON c.cart_id=ci.cart_id WHERE c.user_id=?");
  $cs->execute([$user_id]);
  $cart_count = (int)($cs->fetch()['c'] ?? 0);
} catch (Throwable $e) {}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Notification Settings - FitFuel</title>
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
        <a href="cart.php" class="relative p-2 text-white hover:text-emerald-600">
          <i class="fas fa-shopping-cart text-xl"></i>
          <?php if ($cart_count>0): ?><span class="absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count; ?></span><?php endif; ?>
        </a>
        <a href="profile.php" class="p-2 text-white hover:text-emerald-600"><i class="fas fa-user text-xl"></i></a>
      </div>
    </div>
  </nav>

  <main class="flex-1">
    <div class="container mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-4 gap-6">
      <?php include __DIR__ . '/sidebar.php'; ?>

      <section class="md:col-span-3 bg-white rounded-lg border border-gray-200">
        <div class="p-6 border-b"><h1 class="text-[20px] font-semibold">Notification Settings</h1></div>

        <?php if($alert['type']): ?>
          <div class="mx-6 mt-4 rounded px-4 py-3 border <?php echo $alert['type']==='success'?'bg-emerald-50 text-emerald-700 border-emerald-200':'bg-red-50 text-red-700 border-red-200'; ?>">
            <?php echo h($alert['msg']); ?>
          </div>
        <?php endif; ?>

        <form class="p-6 space-y-4" method="post">
          <label class="flex items-center space-x-2">
            <input type="checkbox" name="email_enabled" <?php if(!empty($settings['email_enabled'])) echo 'checked'; ?>>
            <span>Email Notifications</span>
          </label>
          <label class="flex items-center space-x-2">
            <input type="checkbox" name="sms_enabled" <?php if(!empty($settings['sms_enabled'])) echo 'checked'; ?>>
            <span>SMS Notifications</span>
          </label>
          <label class="flex items-center space-x-2">
            <input type="checkbox" name="push_enabled" <?php if(!empty($settings['push_enabled'])) echo 'checked'; ?>>
            <span>Push Notifications</span>
          </label>
          <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded">Save</button>
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

</body>
</html>
