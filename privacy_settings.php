<?php
if (session_status()===PHP_SESSION_NONE){session_start();}
require_once 'customer_auth_check.php';
require_once 'config/database.php';

$user_id=(int)($_SESSION['user_id'] ?? 0);
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function active_link($f){$c=basename(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));return $c===$f?'text-emerald-600 font-semibold':'hover:text-emerald-600';}

$pdo=getDBConnection();
$alert=['type'=>'','msg'=>''];

$pdo->exec("
CREATE TABLE IF NOT EXISTS user_privacy_settings (
  user_id INT PRIMARY KEY,
  profile_visibility ENUM('public','friends','private') NOT NULL DEFAULT 'public',
  show_email TINYINT(1) NOT NULL DEFAULT 1,
  show_phone TINYINT(1) NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
try{
  $pdo->exec("ALTER TABLE user_privacy_settings
    ADD CONSTRAINT ups_user_fk FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE");
}catch(Throwable $e){}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $vis=in_array($_POST['profile_visibility']??'public',['public','friends','private'],true)?$_POST['profile_visibility']:'public';
  $show_email=isset($_POST['show_email'])?1:0;
  $show_phone=isset($_POST['show_phone'])?1:0;

  $pdo->prepare("INSERT INTO user_privacy_settings (user_id,profile_visibility,show_email,show_phone)
    VALUES (?,?,?,?)
    ON DUPLICATE KEY UPDATE profile_visibility=VALUES(profile_visibility),show_email=VALUES(show_email),show_phone=VALUES(show_phone)")
      ->execute([$user_id,$vis,$show_email,$show_phone]);
  $alert=['type'=>'success','msg'=>'Privacy settings saved.'];
}

$s = $pdo->prepare("SELECT * FROM user_privacy_settings WHERE user_id=?");
$s->execute([$user_id]);
$settings = $s->fetch(PDO::FETCH_ASSOC) ?: ['profile_visibility'=>'public','show_email'=>1,'show_phone'=>0];

/* cart count */
$cart_count=0;
try{
  $cs=$pdo->prepare("SELECT COALESCE(SUM(ci.quantity),0) c FROM cart c LEFT JOIN cart_items ci ON c.cart_id=ci.cart_id WHERE c.user_id=?");
  $cs->execute([$user_id]); $cart_count=(int)($cs->fetch()['c']??0);
}catch(Throwable $e){}

/* user for sidebar */
$u=$pdo->prepare("SELECT username, profile_picture FROM users WHERE user_id=?");
$u->execute([$user_id]);
$user=$u->fetch(PDO::FETCH_ASSOC) ?: ['username'=>'','profile_picture'=>null];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Privacy Settings - FitFuel</title>
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
      <?php include __DIR__.'/sidebar.php'; ?>

      <section class="md:col-span-3 bg-white rounded-lg border border-gray-200">
        <div class="p-6 border-b"><h1 class="text-[20px] font-semibold">Privacy Settings</h1></div>

        <?php if($alert['type']): ?>
          <div class="mx-6 mt-4 rounded px-4 py-3 border <?php echo $alert['type']==='success'?'bg-emerald-50 text-emerald-700 border-emerald-200':'bg-red-50 text-red-700 border-red-200'; ?>">
            <?php echo h($alert['msg']); ?>
          </div>
        <?php endif; ?>

        <form class="p-6 max-w-xl" method="post">
          <label class="block mb-4">
            <span class="text-sm text-slate-600">Profile Visibility</span>
            <select name="profile_visibility" class="w-full border rounded px-3 py-2">
              <option value="public"  <?php if($settings['profile_visibility']==='public')  echo 'selected';?>>Public</option>
              <option value="friends" <?php if($settings['profile_visibility']==='friends') echo 'selected';?>>Friends only</option>
              <option value="private" <?php if($settings['profile_visibility']==='private') echo 'selected';?>>Private</option>
            </select>
          </label>
          <label class="flex items-center gap-2 mb-3">
            <input type="checkbox" name="show_email" <?php if($settings['show_email']) echo 'checked';?>>
            <span>Show my email on profile</span>
          </label>
          <label class="flex items-center gap-2 mb-6">
            <input type="checkbox" name="show_phone" <?php if($settings['show_phone']) echo 'checked';?>>
            <span>Show my phone on profile</span>
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
