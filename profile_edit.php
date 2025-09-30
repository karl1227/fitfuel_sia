<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';
require_once 'config/audit_logger.php';

$user_id = (int) $_SESSION['user_id'];

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function mask_email($email){
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $email;
  [$u,$d] = explode('@',$email,2);
  $keep = max(1, min(3, strlen($u)));
  return substr($u,0,$keep) . str_repeat('*', max(3, strlen($u)-$keep)) . '@' . $d;
}

$alert = ['type'=>'','msg'=>''];

try {
  $pdo = getDBConnection();
  $auditLogger = new AuditLogger();

  // Ensure new columns exist (safe guard)
  try {
    $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'")
                ->fetchAll(PDO::FETCH_COLUMN);
    if (is_array($cols)) {
      if (!in_array('phone', $cols, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone varchar(20) NULL AFTER email");
      }
      if (!in_array('date_of_birth', $cols, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN date_of_birth date NULL AFTER phone");
      }
    }
  } catch (Throwable $e) { /* ignore */ }

  // Ensure upload folder exists
  $uploadDir = __DIR__ . '/uploads/profile';
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
  if (!is_writable($uploadDir)) throw new Exception('Upload folder not writable. Run chmod -R 775 uploads');

  // Fetch user
  $st = $pdo->prepare("
    SELECT user_id, username, email, phone, date_of_birth, first_name, last_name, profile_picture
    FROM users WHERE user_id = ?
  ");
  $st->execute([$user_id]);
  $user = $st->fetch(PDO::FETCH_ASSOC);
  if (!$user) throw new Exception('User not found.');

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? $user['first_name']);
    $last_name  = trim($_POST['last_name']  ?? $user['last_name']);
    $email      = trim($_POST['email']      ?? $user['email']);
    $phone      = trim($_POST['phone']      ?? $user['phone']);
    $dob        = trim($_POST['date_of_birth'] ?? ($user['date_of_birth'] ?? ''));

    if ($first_name === '' && $last_name === '') throw new Exception('Please enter your first or last name.');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Please enter a valid email.');

    // Unique email check
    $chk = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id <> ?");
    $chk->execute([$email, $user_id]);
    if ($chk->fetch()) throw new Exception('Email already in use.');

    // Phone validation
    if ($phone !== '' && !preg_match('/^[+0-9][0-9\s\-()]{6,}$/', $phone)) throw new Exception('Invalid phone number.');

    // DOB validation
    if ($dob !== '') {
      $dt = DateTime::createFromFormat('Y-m-d', $dob);
      if (!$dt || $dt > new DateTime('today')) throw new Exception('Invalid date of birth.');
      $dob = $dt->format('Y-m-d');
    } else $dob = null;

    // Handle avatar
    $profile_path = $user['profile_picture'] ?? null;
    if (!empty($_FILES['profile_picture']['name'])) {
      if ($_FILES['profile_picture']['size'] > 1024*1024) throw new Exception('Image too large (max 1 MB).');
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      $mime  = $finfo->file($_FILES['profile_picture']['tmp_name']);
      $allowed = ['image/jpeg'=>'jpg','image/png'=>'png'];
      if (!isset($allowed[$mime])) throw new Exception('Only JPEG/PNG allowed.');
      $ext = $allowed[$mime];
      $fname = 'u'.$user_id.'_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
      $dest  = $uploadDir.'/'.$fname;
      if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'],$dest)) throw new Exception('Failed to save image.');
      if (!empty($profile_path) && strpos($profile_path,'uploads/profile/')===0 && is_file(__DIR__.'/'.$profile_path)) {
        @unlink(__DIR__.'/'.$profile_path);
      }
      $profile_path = 'uploads/profile/'.$fname;
    }

    // Save update
    $upd = $pdo->prepare("UPDATE users SET email=?, phone=?, date_of_birth=?, first_name=?, last_name=?, profile_picture=? WHERE user_id=?");
    $upd->execute([$email,($phone!==''?$phone:null),$dob,$first_name,$last_name,$profile_path,$user_id]);

    // Optional: audit log example
    try { $auditLogger->log($user_id, 'profile_update', 'User updated profile.'); } catch (Throwable $e) {}

    // Reload updated data
    $st->execute([$user_id]);
    $user = $st->fetch(PDO::FETCH_ASSOC);

    $alert = ['type'=>'success','msg'=>'Profile updated successfully!'];
  }
} catch (Exception $e) {
  $alert = ['type'=>'error','msg'=>$e->getMessage()];
  if (!isset($user)) $user = ['username'=>'','email'=>'','phone'=>'','date_of_birth'=>null,'first_name'=>'','last_name'=>'','profile_picture'=>''];
}

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
  <title>Edit Profile - FitFuel</title>
  <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    .form-grid{display:grid;grid-template-columns:200px 1fr;}
    .label-cell{display:flex;align-items:center;color:#4b5563}
    .orange-btn{background:#ee4d2d}
    .orange-btn:hover{background:#d63f20}
  </style>
</head>
<body class="font-body bg-[#f6f6f6] text-slate-700 min-h-screen flex flex-col">
  <!-- top navs -->
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
        <div class="p-6 border-b flex items-center justify-between">
          <div>
            <h1 class="text-[20px] font-semibold text-slate-900">Edit Profile</h1>
            <p class="text-gray-500 text-sm">Manage and protect your account</p>
          </div>
          <a href="profile.php" class="inline-flex items-center px-4 py-2 rounded text-slate-700 border hover:bg-gray-50">
            <i class="fa fa-arrow-left mr-2"></i> Back to Profile
          </a>
        </div>

        <?php if($alert['type']==='success'):?>
          <div class="mx-6 mt-4 rounded bg-emerald-50 text-emerald-700 px-4 py-3 border border-emerald-200"><?php echo h($alert['msg']);?></div>
        <?php elseif($alert['type']==='error'):?>
          <div class="mx-6 mt-4 rounded bg-red-50 text-red-700 px-4 py-3 border border-red-200"><?php echo h($alert['msg']);?></div>
        <?php endif;?>

        <form class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-8" method="post" enctype="multipart/form-data">
          <!-- left -->
          <div class="lg:col-span-2">
            <div class="form-grid gap-y-5">
              <div class="label-cell">Username</div>
              <div><div class="text-slate-800"><?php echo h($user['username']);?></div></div>

              <div class="label-cell">First Name</div>
              <div><input name="first_name" type="text" value="<?php echo h($user['first_name']);?>" class="w-full border rounded px-3 py-2 focus:ring-1 focus:ring-emerald-500" required></div>

              <div class="label-cell">Last Name</div>
              <div><input name="last_name" type="text" value="<?php echo h($user['last_name']);?>" class="w-full border rounded px-3 py-2 focus:ring-1 focus:ring-emerald-500"></div>

              <div class="label-cell">Email</div>
              <div>
                <div id="emailView" class="flex items-center space-x-3">
                  <span><?php echo h(mask_email($user['email']));?></span>
                  <button type="button" class="text-emerald-600 hover:underline text-sm" onclick="toggleEmail(true)">Change</button>
                </div>
                <div id="emailEdit" class="hidden flex items-center space-x-3">
                  <input name="email" type="email" value="<?php echo h($user['email']);?>" class="w-full border rounded px-3 py-2">
                  <button type="button" class="text-gray-500 hover:underline text-sm" onclick="toggleEmail(false)">Cancel</button>
                </div>
              </div>

              <div class="label-cell">Phone</div>
              <div><input name="phone" type="tel" value="<?php echo h($user['phone']);?>" class="w-full border rounded px-3 py-2 focus:ring-1 focus:ring-emerald-500"></div>

              <div class="label-cell">Date of Birth</div>
              <div><input name="date_of_birth" type="date" value="<?php echo h($user['date_of_birth']);?>" class="w-full border rounded px-3 py-2 focus:ring-1 focus:ring-emerald-500"></div>
            </div>

            <div class="mt-8">
              <button type="submit" class="orange-btn text-white px-6 py-2 rounded shadow-sm">Save</button>
            </div>
          </div>

          <!-- right avatar -->
          <div class="lg:col-span-1 flex flex-col items-center">
            <div class="w-40 h-40 rounded-full overflow-hidden border border-gray-200">
              <img id="avatarPreview" src="<?php echo h(!empty($user['profile_picture'])?$user['profile_picture']:'img/placeholder.svg');?>" class="w-full h-full object-cover" alt="avatar">
            </div>
            <label class="mt-4 inline-block">
              <span class="px-4 py-2 border rounded text-gray-700 cursor-pointer hover:bg-gray-50">Select Image</span>
              <input type="file" name="profile_picture" id="profile_picture" class="hidden" accept="image/jpeg,image/png">
            </label>
            <div class="text-gray-500 text-sm mt-4 text-center">
              <div>File size: max 1 MB</div><div>File types: JPEG, PNG</div>
            </div>
          </div>
        </form>
      </section>
    </div>
  </main>

  <footer class="bg-slate-800 text-white py-12">
    <div class="container mx-auto px-4 text-center">
      <p>&copy; 2024 FitFuel. All rights reserved.</p>
    </div>
  </footer>

  <script>
    function toggleEmail(edit){
      document.getElementById('emailView').classList.toggle('hidden',edit);
      document.getElementById('emailEdit').classList.toggle('hidden',!edit);
    }
    document.getElementById('profile_picture')?.addEventListener('change',e=>{
      const f=e.target.files?.[0]; if(f) document.getElementById('avatarPreview').src=URL.createObjectURL(f);
    });
  </script>
</body>
</html>
