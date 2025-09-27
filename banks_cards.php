<?php
// banks_cards.php
if (session_status()===PHP_SESSION_NONE){ session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';
require_once 'config/audit_logger.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function active_link($f){
  $c = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
  return $c === $f ? 'text-emerald-600 font-semibold' : 'hover:text-emerald-600';
}

$pdo   = getDBConnection();
$audit = new AuditLogger();
$alert = ['type'=>'','msg'=>''];

/* ---------- Schema (dev-friendly) ---------- */
$pdo->exec("
CREATE TABLE IF NOT EXISTS user_payment_methods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  brand VARCHAR(20) NOT NULL,
  last4 CHAR(4) NOT NULL,
  name_on_card VARCHAR(80) NULL,
  exp_month TINYINT UNSIGNED NOT NULL,
  exp_year  SMALLINT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT upm_user_fk FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  INDEX (user_id, brand, last4)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

/* ---------- Helpers ---------- */
function detect_brand($pan){
  $pan = preg_replace('/\D/','',$pan);
  if(preg_match('/^4/',$pan)) return 'Visa';
  if(preg_match('/^5[1-5]/',$pan) || preg_match('/^2(2[2-9]|[3-6]\d|7[01])\d/',$pan)) return 'Mastercard';
  if(preg_match('/^3[47]/',$pan)) return 'AmEx';
  if(preg_match('/^6(?:011|5)/',$pan)) return 'Discover';
  return 'Card';
}

/* ---------- Add card ---------- */
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='add'){
  try{
    $name   = trim($_POST['name_on_card'] ?? '');
    $number = preg_replace('/\D/','', $_POST['card_number'] ?? '');
    $exp    = trim($_POST['exp'] ?? ''); // MM/YY or MM/YYYY

    if($number === '' || strlen($number) < 12 || strlen($number) > 19){
      throw new Exception('Invalid card number (demo validation).');
    }
    if(!preg_match('/^\d{2}\/(\d{2}|\d{4})$/', $exp)){
      throw new Exception('Expiry must be in MM/YY format.');
    }

    [$mm, $yy] = explode('/', $exp);
    $mm = (int)$mm; $yy = (int)$yy;
    if($mm < 1 || $mm > 12){ throw new Exception('Invalid expiry month.'); }
    if($yy < 100){ $yy += 2000; } // convert YY -> YYYY

    // Expiry must be this month or later
    $firstOfThisMonth = new DateTime('first day of this month 00:00:00');
    $expDate = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $yy, $mm));
    if(!$expDate){ throw new Exception('Invalid expiry date.'); }
    if($expDate < $firstOfThisMonth){
      throw new Exception('Card is expired.');
    }

    // We only store brand + last4 (no PAN)
    $brand = detect_brand($number);
    $last4 = substr($number, -4);
    if($name !== '' && mb_strlen($name) > 80){
      throw new Exception('Name on card is too long (max 80 chars).');
    }

    // Optional: prevent obvious duplicates for same user
    $dup = $pdo->prepare("SELECT 1 FROM user_payment_methods
                          WHERE user_id=? AND brand=? AND last4=? AND exp_month=? AND exp_year=?");
    $dup->execute([$user_id,$brand,$last4,$mm,$yy]);
    if($dup->fetch()){ throw new Exception('This card already exists.'); }

    $ins = $pdo->prepare("INSERT INTO user_payment_methods
      (user_id, brand, last4, name_on_card, exp_month, exp_year)
      VALUES (?,?,?,?,?,?)");
    $ins->execute([$user_id, $brand, $last4, ($name?:null), $mm, $yy]);

    // Audit (best-effort)
    try{
      $audit->log('add_card','user_payment_methods','Added card','[]',
        ['brand'=>$brand,'last4'=>$last4,'exp_month'=>$mm,'exp_year'=>$yy],
        $user_id,'user','medium','success',$user_id);
    }catch(Throwable $e){}

    $alert = ['type'=>'success','msg'=>'Card saved (****'.$last4.').'];
  }catch(Exception $e){
    $alert = ['type'=>'error','msg'=>$e->getMessage()];
  }
}

/* ---------- Delete card ---------- */
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='delete'){
  $id = (int)($_POST['id'] ?? 0);
  // fetch for audit
  $g = $pdo->prepare("SELECT brand,last4,exp_month,exp_year FROM user_payment_methods WHERE id=? AND user_id=?");
  $g->execute([$id,$user_id]); $old = $g->fetch(PDO::FETCH_ASSOC);

  $del = $pdo->prepare("DELETE FROM user_payment_methods WHERE id=? AND user_id=?");
  $del->execute([$id,$user_id]);

  if($del->rowCount()>0 && $old){
    try{
      $audit->log('delete_card','user_payment_methods','Deleted card',
        $old,[], $user_id,'user','medium','success',$user_id);
    }catch(Throwable $e){}
  }
}

/* ---------- List cards ---------- */
$rows  = $pdo->prepare("SELECT * FROM user_payment_methods WHERE user_id=? ORDER BY created_at DESC");
$rows->execute([$user_id]);
$cards = $rows->fetchAll(PDO::FETCH_ASSOC);

/* ---------- Fetch user for sidebar avatar/name ---------- */
$usr = $pdo->prepare("SELECT username,profile_picture FROM users WHERE user_id=?");
$usr->execute([$user_id]);
$user = $usr->fetch(PDO::FETCH_ASSOC) ?: ['username'=>'','profile_picture'=>null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Banks &amp; Cards - FitFuel</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
  .sidebar-title{color:#9ca3af;font-size:.75rem;text-transform:uppercase;margin:.5rem 0}
  .sidebar-link{display:block;margin:.35rem 0}
</style>
</head>
<body class="font-body bg-[#f6f6f6] text-slate-700">
  <!-- top bars -->
  <nav class="bg-white text-black py-2">
    <div class="container mx-auto px-4">
      <div class="flex justify-end space-x-6 text-sm">
        <a href="#" class="hover:text-emerald-400">Review</a>
        <a href="#" class="hover:text-emerald-400">Help</a>
        <a href="logout.php" class="hover:text-emerald-400">Logout</a>
      </div>
    </div>
  </nav>
  <nav class="bg-black py-4">
    <div class="container mx-auto px-4">
      <div class="flex items-center justify-between">
        <a href="index.php" class="flex items-center"><img src="img/LOGO-Fitfuel.png" width="75" alt="LOGO"></a>
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
    </div>
  </nav>

  <div class="container mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-4 gap-6">
    <!-- Sidebar (exactly the items you requested) -->
    <aside class="md:col-span-1 bg-white rounded-lg border border-gray-200 p-4">
      <div class="flex items-center space-x-3 mb-4">
        <?php $avatar = !empty($user['profile_picture']) ? $user['profile_picture'] : 'img/placeholder.svg'; ?>
        <img src="<?php echo h($avatar); ?>" class="w-12 h-12 rounded-full object-cover border" alt="">
        <div>
          <div class="font-semibold"><?php echo h($user['username']); ?></div>
          <div class="text-xs text-gray-500"><i class="fa-regular fa-pen-to-square mr-1"></i>Edit Profile</div>
        </div>
      </div>
      <div class="sidebar-title">My Account</div>
      <nav class="text-[15px]">
        <a href="profile.php"               class="sidebar-link <?php echo active_link('profile.php');?>">Profile</a>
        <a href="banks_cards.php"           class="sidebar-link <?php echo active_link('banks_cards.php');?>">Banks &amp; Cards</a>
        <a href="addresses.php"             class="sidebar-link <?php echo active_link('addresses.php');?>">Addresses</a>
        <a href="change_password.php"       class="sidebar-link <?php echo active_link('change_password.php');?>">Change Password</a>
        <a href="notification_settings.php" class="sidebar-link <?php echo active_link('notification_settings.php');?>">Notification Settings</a>
      </nav>
      <div class="sidebar-title mt-6">My Purchase</div>
      <nav class="text-[15px]">
        <a href="my_orders.php" class="sidebar-link <?php echo active_link('my_orders.php');?>">My Orders</a>
      </nav>
      <div class="sidebar-title mt-6">My Wishlist</div>
      <nav class="text-[15px]">
        <a href="wishlist.php" class="sidebar-link <?php echo active_link('wishlist.php');?>">Wishlist</a>
      </nav>
      <div class="sidebar-title mt-6">Notifications</div>
      <nav class="text-[15px]">
        <a href="inbox.php" class="sidebar-link <?php echo active_link('inbox.php');?>">Inbox</a>
      </nav>
    </aside>

    <!-- Main -->
    <section class="md:col-span-3 bg-white rounded-lg border border-gray-200">
      <div class="p-6 border-b"><h1 class="text-[20px] font-semibold">Banks &amp; Cards</h1></div>

      <?php if($alert['type']==='success'):?>
        <div class="mx-6 mt-4 rounded bg-emerald-50 text-emerald-700 px-4 py-3 border border-emerald-200">
          <?php echo h($alert['msg']);?>
        </div>
      <?php elseif($alert['type']==='error'):?>
        <div class="mx-6 mt-4 rounded bg-red-50 text-red-700 px-4 py-3 border border-red-200">
          <?php echo h($alert['msg']);?>
        </div>
      <?php endif;?>

      <div class="p-6 grid md:grid-cols-2 gap-8">
        <!-- Add card -->
        <form method="post" class="max-w-lg">
          <input type="hidden" name="action" value="add">
          <label class="block mb-3">
            <span class="text-sm text-slate-600">Name on Card</span>
            <input name="name_on_card" class="w-full border rounded px-3 py-2" maxlength="80">
          </label>
          <label class="block mb-3">
            <span class="text-sm text-slate-600">Card Number (demo: only last4 saved)</span>
            <input name="card_number" class="w-full border rounded px-3 py-2" inputmode="numeric" pattern="[0-9 ]+" autocomplete="cc-number">
          </label>
          <label class="block mb-4">
            <span class="text-sm text-slate-600">Expiry (MM/YY)</span>
            <input name="exp" class="w-32 border rounded px-3 py-2" placeholder="MM/YY" autocomplete="cc-exp">
          </label>
          <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded">Add Card</button>
        </form>

        <!-- Saved cards -->
        <div>
          <h2 class="font-semibold mb-3">Saved Cards</h2>
          <?php if(!$cards):?>
            <p class="text-slate-500">No cards saved.</p>
          <?php else: foreach($cards as $c):?>
            <div class="border rounded p-4 mb-3 flex items-center justify-between">
              <div>
                <div class="font-medium"><?php echo h($c['brand']);?> •••• <?php echo h($c['last4']);?></div>
                <div class="text-sm text-slate-500">Exp <?php echo sprintf('%02d',$c['exp_month']).'/'.$c['exp_year'];?></div>
                <?php if($c['name_on_card']):?><div class="text-sm text-slate-500"><?php echo h($c['name_on_card']);?></div><?php endif;?>
              </div>
              <form method="post" onsubmit="return confirm('Remove this card?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo (int)$c['id'];?>">
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </div>
          <?php endforeach; endif;?>
        </div>
      </div>
    </section>
  </div>
</body>
</html>
