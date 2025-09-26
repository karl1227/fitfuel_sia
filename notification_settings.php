<?php
if (session_status()===PHP_SESSION_NONE){ session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function active_link($f){ $c=basename(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)); return $c===$f?'text-emerald-600 font-semibold':'hover:text-emerald-600'; }

$pdo = getDBConnection();
$alert=['type'=>'','msg'=>''];

$pdo->exec("
CREATE TABLE IF NOT EXISTS user_notification_settings (
  user_id INT PRIMARY KEY,
  email_orders TINYINT(1) NOT NULL DEFAULT 1,
  email_promos TINYINT(1) NOT NULL DEFAULT 0,
  sms_orders   TINYINT(1) NOT NULL DEFAULT 0,
  sms_promos   TINYINT(1) NOT NULL DEFAULT 0,
  push_general TINYINT(1) NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT uns_user_fk FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if($_SERVER['REQUEST_METHOD']==='POST'){
  $email_orders = isset($_POST['email_orders']) ? 1 : 0;
  $email_promos = isset($_POST['email_promos']) ? 1 : 0;
  $sms_orders   = isset($_POST['sms_orders'])   ? 1 : 0;
  $sms_promos   = isset($_POST['sms_promos'])   ? 1 : 0;
  $push_general = isset($_POST['push_general']) ? 1 : 0;

  $up=$pdo->prepare("INSERT INTO user_notification_settings (user_id,email_orders,email_promos,sms_orders,sms_promos,push_general)
                     VALUES (?,?,?,?,?,?)
                     ON DUPLICATE KEY UPDATE email_orders=VALUES(email_orders),email_promos=VALUES(email_promos),
                                             sms_orders=VALUES(sms_orders),sms_promos=VALUES(sms_promos),
                                             push_general=VALUES(push_general)");
  $up->execute([$user_id,$email_orders,$email_promos,$sms_orders,$sms_promos,$push_general]);

  $alert=['type'=>'success','msg'=>'Notification settings saved.'];
}

$st=$pdo->prepare("SELECT username,profile_picture FROM users WHERE user_id=?");
$st->execute([$user_id]); $user=$st->fetch(PDO::FETCH_ASSOC) ?: ['username'=>'','profile_picture'=>null];

$cfg=$pdo->prepare("SELECT * FROM user_notification_settings WHERE user_id=?");
$cfg->execute([$user_id]); $cfg=$cfg->fetch(PDO::FETCH_ASSOC) ?: ['email_orders'=>1,'email_promos'=>0,'sms_orders'=>0,'sms_promos'=>0,'push_general'=>0];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Notification Settings - FitFuel</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>.sidebar-title{color:#9ca3af;font-size:.75rem;text-transform:uppercase;margin:.5rem 0}.sidebar-link{display:block;margin:.35rem 0}</style>
</head>
<body class="bg-[#f6f6f6] text-slate-700">
  <nav class="bg-white text-black py-2"><div class="container mx-auto px-4 flex justify-end space-x-6 text-sm"><a class="hover:text-emerald-400">Review</a><a class="hover:text-emerald-400">Help</a><a href="logout.php" class="hover:text-emerald-400">Logout</a></div></nav>
  <nav class="bg-black py-4"><div class="container mx-auto px-4 flex items-center justify-between"><a href="index.php"><img src="img/LOGO-Fitfuel.png" width="75"></a><div class="hidden md:flex items-center space-x-8"><a href="index.php" class="text-white hover:text-emerald-600">Home</a><a href="shop.php" class="text-white hover:text-emerald-600">Shop</a><a href="#" class="text-white hover:text-emerald-600">About</a><a href="#" class="text-white hover:text-emerald-600">Contact</a></div><div class="flex items-center space-x-4"><a href="cart.php" class="p-2 text-white hover:text-emerald-600"><i class="fas fa-shopping-cart text-xl"></i></a><a href="profile.php" class="p-2 text-white hover:text-emerald-600"><i class="fas fa-user text-xl"></i></a></div></div></nav>

  <div class="container mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-4 gap-6">
    <?php include 'sidebar.php'; ?>

    <section class="md:col-span-3 bg-white rounded-lg border border-gray-200">
      <div class="p-6 border-b"><h1 class="text-[20px] font-semibold">Notification Settings</h1></div>

      <?php if($alert['type']==='success'):?>
        <div class="mx-6 mt-4 rounded bg-emerald-50 text-emerald-700 px-4 py-3 border border-emerald-200"><?php echo h($alert['msg']);?></div>
      <?php elseif($alert['type']==='error'):?>
        <div class="mx-6 mt-4 rounded bg-red-50 text-red-700 px-4 py-3 border border-red-200"><?php echo h($alert['msg']);?></div>
      <?php endif;?>

      <form class="p-6 max-w-lg" method="post">
        <fieldset class="mb-6">
          <legend class="font-semibold mb-2">Email</legend>
          <label class="flex items-center gap-3 mb-2"><input type="checkbox" name="email_orders" <?php if($cfg['email_orders']) echo 'checked';?>> Order updates</label>
          <label class="flex items-center gap-3"><input type="checkbox" name="email_promos" <?php if($cfg['email_promos']) echo 'checked';?>> Promotions</label>
        </fieldset>

        <fieldset class="mb-6">
          <legend class="font-semibold mb-2">SMS</legend>
          <label class="flex items-center gap-3 mb-2"><input type="checkbox" name="sms_orders" <?php if($cfg['sms_orders']) echo 'checked';?>> Order updates</label>
          <label class="flex items-center gap-3"><input type="checkbox" name="sms_promos" <?php if($cfg['sms_promos']) echo 'checked';?>> Promotions</label>
        </fieldset>

        <fieldset class="mb-6">
          <legend class="font-semibold mb-2">Push</legend>
          <label class="flex items-center gap-3"><input type="checkbox" name="push_general" <?php if($cfg['push_general']) echo 'checked';?>> General notifications</label>
        </fieldset>

        <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded">Save</button>
      </form>
    </section>
  </div>
</body>
</html>
