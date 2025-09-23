<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }
$user_id = (int) $_SESSION['user_id'];

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function mask_email($email){
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $email;
  [$u,$d] = explode('@',$email,2);
  $keep = max(1, min(3, strlen($u)));
  return substr($u,0,$keep) . str_repeat('*', max(3, strlen($u)-$keep)) . '@' . $d;
}
function mask_phone($phone){
  $digits = preg_replace('/\D+/', '', $phone);
  if ($digits === '') return $phone;
  $show = substr($digits, -2);
  return str_repeat('*', max(6, strlen($digits)-2)) . $show;
}

$alert = ['type'=>'','msg'=>''];

try {
  $pdo = getDBConnection();

  // Load current user
  $st = $pdo->prepare("SELECT user_id, username, email, first_name, last_name, phone, dob, profile_picture
                       FROM users WHERE user_id = ?");
  $st->execute([$user_id]);
  $user = $st->fetch();
  if (!$user) throw new Exception('User not found.');

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? $user['email']);
    $phone = trim($_POST['phone'] ?? $user['phone']);
    $dob   = trim($_POST['dob'] ?? ($user['dob'] ?? ''));

    // Validate
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new Exception('Please enter a valid email address.');
    }
    // Email must be unique
    $chk = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id <> ?");
    $chk->execute([$email, $user_id]);
    if ($chk->fetch()) throw new Exception('Email is already in use.');

    // Handle avatar (max 1MB)
    $profile_path = $user['profile_picture'];
    if (!empty($_FILES['profile_picture']['name'])) {
      $allowed = ['image/jpeg'=>'jpg','image/png'=>'png'];
      $f = $_FILES['profile_picture'];
      if ($f['error'] !== UPLOAD_ERR_OK) throw new Exception('Failed to upload image.');
      if (!isset($allowed[$f['type']])) throw new Exception('Only JPEG and PNG are allowed.');
      if ($f['size'] > 1024*1024) throw new Exception('Image is too large (max 1MB).');

      $dir = __DIR__ . '/uploads/profile';
      if (!is_dir($dir)) { if (!mkdir($dir, 0775, true) && !is_dir($dir)) throw new Exception('Cannot create upload folder.'); }
      $ext = $allowed[$f['type']];
      $fname = 'u'.$user_id.'_'.time().'.'.$ext;
      $dest = $dir.'/'.$fname;
      if (!move_uploaded_file($f['tmp_name'], $dest)) throw new Exception('Failed to save uploaded image.');
      $profile_path = 'uploads/profile/'.$fname;
    }

    // Parse DOB (optional)
    $dobSql = null;
    if ($dob !== '') {
      $ts = strtotime($dob);
      if ($ts === false) throw new Exception('Invalid date of birth.');
      $dobSql = date('Y-m-d', $ts);
    }

    // One-name style -> store as first_name
    $first_name = $name;
    $last_name  = '';

    $upd = $pdo->prepare("UPDATE users SET email=?, first_name=?, last_name=?, phone=?, dob=?, profile_picture=? WHERE user_id=?");
    $upd->execute([$email, $first_name, $last_name, $phone, $dobSql, $profile_path, $user_id]);

    // Reload
    $st->execute([$user_id]);
    $user = $st->fetch();

    $alert = ['type'=>'success','msg'=>'Saved successfully.'];
  }
} catch (Exception $e) {
  $alert = ['type'=>'error','msg'=>$e->getMessage()];
  if (!isset($user)) {
    $user = ['username'=>'','email'=>'','first_name'=>'','last_name'=>'','phone'=>'','dob'=>null,'profile_picture'=>''];
  }
}

// Cart count for header badge
$cart_count = 0;
try {
  $cs = $pdo->prepare("SELECT COALESCE(SUM(ci.quantity),0) c FROM cart c LEFT JOIN cart_items ci ON c.cart_id=ci.cart_id WHERE c.user_id=?");
  $cs->execute([$user_id]);
  $cart_count = (int)($cs->fetch()['c'] ?? 0);
} catch(Throwable $e){}
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
    /* center card look like screenshot */
    .form-grid{display:grid;grid-template-columns:200px 1fr;}
    .label-cell{display:flex;align-items:center;color:#4b5563}
    .orange-btn{background:#ee4d2d}
    .orange-btn:hover{background:#d63f20}
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
        <a href="index.html" class="flex items-center"><img src="img/LOGO-Fitfuel.png" width="75" alt="LOGO"></a>
        <div class="hidden md:flex items-center space-x-8">
          <a href="index.html" class="text-white hover:text-emerald-600">Home</a>
          <a href="shop.php" class="text-white hover:text-emerald-600">Shop</a>
          <a href="#" class="text-white hover:text-emerald-600">About</a>
          <a href="#" class="text-white hover:text-emerald-600">Contact</a>
        </div>
        <div class="flex items-center space-x-4">
          <a href="cart.php" class="relative p-2 text-white hover:text-emerald-600">
            <i class="fas fa-shopping-cart text-xl"></i>
            <?php if ($cart_count>0): ?>
              <span class="absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count; ?></span>
            <?php endif; ?>
          </a>
          <a href="profile.php" class="p-2 text-white hover:text-emerald-600"><i class="fas fa-user text-xl"></i></a>
        </div>
      </div>
    </div>
  </nav>

  <div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <!-- LEFT SIDEBAR -->
      <aside class="md:col-span-1 bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-center space-x-3 mb-4">
          <img src="<?php echo $user['profile_picture'] ? h($user['profile_picture']) : 'img/placeholder.svg'; ?>" class="w-12 h-12 rounded-full object-cover border">
          <div>
            <div class="font-semibold"><?php echo h($user['username']); ?></div>
            <div class="text-xs text-gray-500"><i class="fa-regular fa-pen-to-square mr-1"></i>Edit Profile</div>
          </div>
        </div>
        <nav class="space-y-4 text-[15px]">
          <div>
            <div class="text-gray-400 uppercase text-xs mb-2">My Account</div>
            <a class="block text-emerald-600 font-medium">Profile</a>
            <a href="addresses.php" class="block hover:text-emerald-600">Addresses</a>
            <a href="#" class="block hover:text-emerald-600">Notification Settings</a>
          </div>
          <div>
            <div class="text-gray-400 uppercase text-xs mb-2">My Purchase</div>
            <a href="my_orders.php" class="block hover:text-emerald-600">My Orders</a>
          </div>
          <div>
            <div class="text-gray-400 uppercase text-xs mb-2">My Wishlist</div>
            <a href="wishlist.php" class="block hover:text-emerald-600">Wishlist</a>
          </div>
          <div>
            <div class="text-gray-400 uppercase text-xs mb-2">Notifications</div>
            <a href="#" class="block hover:text-emerald-600">Inbox</a>
          </div>
        </nav>
      </aside>

      <!-- MAIN CARD (center) -->
      <section class="md:col-span-3 bg-white rounded-lg border border-gray-200">
        <div class="p-6 border-b">
          <h1 class="text-[20px] font-semibold text-slate-900">My Profile</h1>
          <p class="text-gray-500 text-sm">Manage and protect your account</p>
        </div>

        <?php if ($alert['type']==='success'): ?>
          <div class="mx-6 mt-4 rounded bg-emerald-50 text-emerald-700 px-4 py-3 border border-emerald-200">
            <i class="fa-solid fa-circle-check mr-2"></i><?php echo h($alert['msg']); ?>
          </div>
        <?php elseif ($alert['type']==='error'): ?>
          <div class="mx-6 mt-4 rounded bg-red-50 text-red-700 px-4 py-3 border border-red-200">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i><?php echo h($alert['msg']); ?>
          </div>
        <?php endif; ?>

        <form class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-8" method="post" enctype="multipart/form-data">
          <!-- center two-column form -->
          <div class="lg:col-span-2">
            <div class="form-grid gap-y-5">
              <!-- username -->
              <div class="label-cell">Username</div>
              <div><div class="text-slate-800"><?php echo h($user['username']); ?></div></div>

              <!-- name -->
              <div class="label-cell">Name</div>
              <div>
                <input name="name" type="text" value="<?php echo h($user['first_name'] ?? ''); ?>" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-emerald-500">
              </div>

              <!-- email masked + change -->
              <div class="label-cell">Email</div>
              <div>
                <div id="emailView" class="flex items-center space-x-3">
                  <span><?php echo h(mask_email($user['email'])); ?></span>
                  <button type="button" class="text-emerald-600 hover:underline text-sm" onclick="toggleEmail(true)">Change</button>
                </div>
                <div id="emailEdit" class="hidden flex items-center space-x-3">
                  <input name="email" type="email" value="<?php echo h($user['email']); ?>" class="w-full border rounded px-3 py-2">
                  <button type="button" class="text-gray-500 hover:underline text-sm" onclick="toggleEmail(false)">Cancel</button>
                </div>
              </div>

              <!-- phone masked + change -->
              <div class="label-cell">Phone Number</div>
              <div>
                <div id="phoneView" class="flex items-center space-x-3">
                  <span><?php echo $user['phone'] ? h(mask_phone($user['phone'])) : '—'; ?></span>
                  <button type="button" class="text-emerald-600 hover:underline text-sm" onclick="togglePhone(true)">Change</button>
                </div>
                <div id="phoneEdit" class="hidden flex items-center space-x-3">
                  <input name="phone" type="text" value="<?php echo h($user['phone']); ?>" class="w-full border rounded px-3 py-2" placeholder="e.g. 09XXXXXXXXX">
                  <button type="button" class="text-gray-500 hover:underline text-sm" onclick="togglePhone(false)">Cancel</button>
                </div>
              </div>

              <!-- dob -->
              <div class="label-cell">Date of birth <i class="fa-regular fa-circle-question ml-2 text-gray-400"></i></div>
              <div>
                <input name="dob" type="date" value="<?php echo $user['dob'] ? h($user['dob']) : ''; ?>" class="w-60 border rounded px-3 py-2">
              </div>
            </div>

            <div class="mt-8">
              <button class="orange-btn text-white px-6 py-2 rounded shadow-sm">Save</button>
            </div>
          </div>

          <!-- right avatar column -->
          <div class="lg:col-span-1">
            <div class="flex flex-col items-center">
              <div class="w-40 h-40 rounded-full overflow-hidden border border-gray-200">
                <img id="avatarPreview" src="<?php echo $user['profile_picture'] ? h($user['profile_picture']) : 'img/placeholder.svg'; ?>" class="w-full h-full object-cover" alt="avatar">
              </div>
              <label class="mt-4 inline-block">
                <span class="px-4 py-2 border rounded text-gray-700 cursor-pointer hover:bg-gray-50">Select Image</span>
                <input type="file" name="profile_picture" id="profile_picture" class="hidden" accept="image/jpeg,image/png">
              </label>
              <div class="text-gray-500 text-sm mt-4">
                <div>File size: maximum 1 MB</div>
                <div>File extension: .JPEG, .PNG</div>
              </div>
            </div>
          </div>
        </form>
      </section>
    </div>
  </div>

  <footer class="bg-slate-800 text-white py-12 mt-12">
    <div class="container mx-auto px-4 text-center">
      <p>&copy; 2024 FitFuel. All rights reserved.</p>
    </div>
  </footer>

  <script>
    // Show/hide email edit
    function toggleEmail(edit){
      document.getElementById('emailView').classList.toggle('hidden', edit);
      document.getElementById('emailEdit').classList.toggle('hidden', !edit);
    }
    // Show/hide phone edit
    function togglePhone(edit){
      document.getElementById('phoneView').classList.toggle('hidden', edit);
      document.getElementById('phoneEdit').classList.toggle('hidden', !edit);
    }
    // Avatar preview
    const fileInput = document.getElementById('profile_picture');
    const preview = document.getElementById('avatarPreview');
    if (fileInput) {
      fileInput.addEventListener('change', e => {
        const f = e.target.files?.[0];
        if (f) preview.src = URL.createObjectURL(f);
      });
    }
  </script>
</body>
</html>
