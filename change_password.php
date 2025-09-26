<?php
if (session_status()===PHP_SESSION_NONE){ session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function active_link($f){ $c=basename(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)); return $c===$f?'text-emerald-600 font-semibold':'hover:text-emerald-600'; }

$pdo = getDBConnection();
$alert = ['type'=>'','msg'=>''];

// fetch user for sidebar and verification
$st=$pdo->prepare("SELECT user_id, username, profile_picture, password_hash FROM users WHERE user_id=?");
$st->execute([$user_id]); $user=$st->fetch(PDO::FETCH_ASSOC);
if(!$user){ $user=['username'=>'','profile_picture'=>null,'password_hash'=>'']; }

if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if($new==='' || strlen($new)<8) throw new Exception('New password must be at least 8 characters.');
    if($new!==$confirm) throw new Exception('New password confirmation does not match.');

    // verify current (adjust if your schema uses a different column)
    if(!password_verify($current, $user['password_hash'] ?? '')){
      throw new Exception('Current password is incorrect.');
    }

    $hash=password_hash($new, PASSWORD_DEFAULT);
    $upd=$pdo->prepare("UPDATE users SET password_hash=? WHERE user_id=?");
    $upd->execute([$hash,$user_id]);

    $alert=['type'=>'success','msg'=>'Password updated successfully.'];
  }catch(Exception $e){
    $alert=['type'=>'error','msg'=>$e->getMessage()];
  }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Change Password - FitFuel</title>
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
      <div class="p-6 border-b"><h1 class="text-[20px] font-semibold">Change Password</h1></div>

      <?php if($alert['type']==='success'):?>
        <div class="mx-6 mt-4 rounded bg-emerald-50 text-emerald-700 px-4 py-3 border border-emerald-200"><?php echo h($alert['msg']);?></div>
      <?php elseif($alert['type']==='error'):?>
        <div class="mx-6 mt-4 rounded bg-red-50 text-red-700 px-4 py-3 border border-red-200"><?php echo h($alert['msg']);?></div>
      <?php endif;?>

      <form class="p-6 max-w-lg" method="post">
        <label class="block mb-3"><span class="text-sm text-slate-600">Current Password</span><input type="password" name="current_password" class="w-full border rounded px-3 py-2" required></label>
        <label class="block mb-3"><span class="text-sm text-slate-600">New Password</span><input type="password" name="new_password" class="w-full border rounded px-3 py-2" required></label>
        <label class="block mb-4"><span class="text-sm text-slate-600">Confirm New Password</span><input type="password" name="confirm_password" class="w-full border rounded px-3 py-2" required></label>
        <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded">Update Password</button>
      </form>
    </section>
  </div>
</body>
</html>
