<?php
/* =========================================================
   path: profile.php
   why: Show default shipping address in Profile → Address
   ========================================================= */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';

$user_id = (int) ($_SESSION['user_id'] ?? 0);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function mask_email($email){
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $email;
  [$u,$d] = explode('@',$email,2);
  $keep = max(1, min(3, strlen($u)));
  return substr($u,0,$keep) . str_repeat('*', max(3, strlen($u)-$keep)) . '@' . $d;
}
function fmt_or_dash($v){ return $v !== null && $v !== '' ? h($v) : '—'; }

/**
 * Formats a shipping address row into a single line.
 * why: Uniform display across NCR/non-NCR and with/without PSGC fields.
 */
function format_address_line(?array $a): string {
  if (!$a) return '';
  $parts = [];

  // Street / lines
  foreach (['address_line1','address_line2','address_line3'] as $k) {
    if (!empty($a[$k])) { $parts[] = trim($a[$k]); }
  }

  // PSGC-preferred labels
  $geo = [];
  if (!empty($a['barangay_name']))   $geo[] = $a['barangay_name'];
  if (!empty($a['city_muni_name']))  $geo[] = $a['city_muni_name'];
  if (!empty($a['province_name']))   $geo[] = $a['province_name'];

  // Fallbacks (legacy columns)
  if (empty($geo)) {
    if (!empty($a['city']))  $geo[] = $a['city'];
    if (!empty($a['state'])) $geo[] = $a['state'];
  }

  if ($geo) { $parts[] = implode(', ', $geo); }
  if (!empty($a['postal_code'])) { $parts[] = $a['postal_code']; }

  return trim(implode(', ', array_filter($parts)));
}

try {
  $pdo = getDBConnection();

  // Keep old users.address for backward-compat only (safe to remove later)
  try {
    $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'")
                ->fetchAll(PDO::FETCH_COLUMN);
    if (is_array($cols) && !in_array('address', $cols, true)) {
      $pdo->exec("ALTER TABLE users ADD COLUMN address VARCHAR(255) NULL AFTER date_of_birth");
    }
  } catch (Throwable $e) {}

  // Fetch user
  $st = $pdo->prepare("
    SELECT user_id, username, email, phone, date_of_birth, first_name, last_name, address, profile_picture
    FROM users WHERE user_id = ?
  ");
  $st->execute([$user_id]);
  $user = $st->fetch(PDO::FETCH_ASSOC);
  if (!$user) { throw new Exception('User not found.'); }

  // Fetch default (or latest) shipping address for display
  $da = $pdo->prepare("
    SELECT *
    FROM shipping_addresses
    WHERE user_id = ?
    ORDER BY is_default DESC, updated_at DESC, created_at DESC
    LIMIT 1
  ");
  $da->execute([$user_id]);
  $default_address_row = $da->fetch(PDO::FETCH_ASSOC);
  $default_address_str = format_address_line($default_address_row);

} catch (Throwable $e) {
  $user = ['username'=>'','email'=>'','phone'=>'','date_of_birth'=>null,'first_name'=>'','last_name'=>'','address'=>'','profile_picture'=>''];
  $default_address_str = '';
  $load_error = $e->getMessage();
}

// Cart count
$cart_count = 0;
try {
  $cs=$pdo->prepare("SELECT COALESCE(SUM(ci.quantity),0) c FROM cart c LEFT JOIN cart_items ci ON c.cart_id=ci.cart_id WHERE c.user_id=?");
  $cs->execute([$user_id]); $cart_count=(int)($cs->fetch()['c']??0);
}catch(Throwable $e){}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Profile - FitFuel</title>
  <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    .kv{display:grid;grid-template-columns:220px 1fr;gap:10px}
    @media (max-width: 640px){ .kv{grid-template-columns:1fr} }
  </style>
</head>
<body class="font-body bg-[#f6f6f6] text-slate-700 min-h-screen flex flex-col">
  <nav class="bg-white text-black py-2">
    <div class="container mx-auto px-4 flex justify-end space-x-6 text-sm">
      <a href="#" class="hover:text-emerald-400">Review</a>
      <a href="#" class="hover:text-emerald-400">Help</a>
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
          <?php if($cart_count>0):?><span class="absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count;?></span><?php endif;?>
        </a>
        <a href="profile.php" class="p-2 text-white hover:text-emerald-600"><i class="fas fa-user text-xl"></i></a>
      </div>
    </div>
  </nav>

  <main class="flex-1">
    <div class="container mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-4 gap-6">
      <?php include __DIR__ . '/sidebar.php'; ?>

      <section class="md:col-span-3 bg-white rounded-lg border border-gray-200">
        <div class="p-6 border-b">
          <h1 class="text-[20px] font-semibold text-slate-900">My Profile</h1>
          <p class="text-gray-500 text-sm">View your account information</p>
        </div>

        <?php if(!empty($load_error)):?>
          <div class="mx-6 mt-4 rounded bg-red-50 text-red-700 px-4 py-3 border border-red-200"><?php echo h($load_error);?></div>
        <?php endif;?>

        <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-10">
          <!-- left: details -->
          <div class="lg:col-span-2">
            <div class="kv">


              <div class="text-gray-500">First Name</div>
              <div class="text-slate-800"><?php echo fmt_or_dash($user['first_name']);?></div>

              <div class="text-gray-500">Last Name</div>
              <div class="text-slate-800"><?php echo fmt_or_dash($user['last_name']);?></div>

              <div class="text-gray-500">Email</div>
              <div class="text-slate-800"><?php echo h(mask_email($user['email']));?></div>

              <div class="text-gray-500">Phone</div>
              <div class="text-slate-800"><?php echo fmt_or_dash($user['phone']); ?></div>

              <div class="text-gray-500">Date of Birth</div>
              <div class="text-slate-800">
                <?php echo $user['date_of_birth'] ? h($user['date_of_birth']) : '—'; ?>
              </div>

              <div class="text-gray-500">Address</div>
              <div class="text-slate-800">
                <?php
                  // prefer default shipping address; fallback to legacy users.address
                  $addr_to_show = $default_address_str ?: (string)($user['address'] ?? '');
                  echo fmt_or_dash($addr_to_show);
                ?>
              </div>
            </div>
          </div>

          <!-- right: avatar -->
          <div class="lg:col-span-1 flex flex-col items-center">
            <div class="w-48 h-48 rounded-full overflow-hidden border border-gray-200 shadow-sm">
              <img src="<?php echo h(!empty($user['profile_picture'])?$user['profile_picture']:'img/placeholder.svg');?>" 
                   class="w-full h-full object-cover" alt="avatar">
            </div>
            <div class="mt-5 text-center">
              <div class="font-semibold text-slate-900 text-xl">
                <?php echo h($user['username']);?>
              </div>
              <div class="text-gray-500 text-base">
               
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>

  <footer class="bg-slate-800 text-white py-12">
    <div class="container mx-auto px-4 text-center">
      <p>&copy; 2024 FitFuel. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
