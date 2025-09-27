<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';
require_once 'config/audit_logger.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function mask_email($email){
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $email;
  [$u,$d] = explode('@',$email,2);
  $keep = max(1, min(3, strlen($u)));
  return substr($u,0,$keep) . str_repeat('*', max(3, strlen($u)-$keep)) . '@' . $d;
}
function active_link($file){
  $curr = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
  return $curr === $file ? 'text-emerald-600 font-semibold' : 'hover:text-emerald-600';
}

$alert = ['type'=>'','msg'=>''];

try {
  $pdo = getDBConnection();
  $auditLogger = new AuditLogger();

  // Ensure extra columns exist (dev convenience)
  try {
    $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'")->fetchAll(PDO::FETCH_COLUMN);
    if (is_array($cols)) {
      if (!in_array('phone',$cols,true))        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email");
      if (!in_array('date_of_birth',$cols,true))$pdo->exec("ALTER TABLE users ADD COLUMN date_of_birth DATE NULL AFTER phone");
    }
  } catch(Throwable $e){}

  // Upload dir
  $uploadDir = __DIR__ . '/uploads/profile';
  if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);
  if (!is_writable($uploadDir)) @chmod($uploadDir, 0775);

  // Fetch user (for page + sidebar)
  $st = $pdo->prepare("SELECT * FROM users WHERE user_id=?");
  $st->execute([$user_id]);
  $user = $st->fetch(PDO::FETCH_ASSOC);
  if(!$user) throw new Exception('User not found.');

  // Save
  if($_SERVER['REQUEST_METHOD']==='POST'){
    $first_name = trim($_POST['first_name'] ?? $user['first_name']);
    $last_name  = trim($_POST['last_name']  ?? $user['last_name']);
    $email      = trim($_POST['email']      ?? $user['email']);
    $phone      = trim($_POST['phone']      ?? $user['phone']);
    $dob        = trim($_POST['date_of_birth'] ?? $user['date_of_birth']);

    if ($email==='' || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Invalid email.');

    $profile_path = $user['profile_picture'] ?? null;
    if (!empty($_FILES['profile_picture']['name'])) {
      if ($_FILES['profile_picture']['size'] > 1024*1024) throw new Exception('Image too large (max 1MB).');
      if (!class_exists('finfo')) throw new Exception('Enable php_fileinfo.');
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      $mime  = $finfo->file($_FILES['profile_picture']['tmp_name']);
      $allowed = ['image/jpeg'=>'jpg','image/png'=>'png'];
      if (!isset($allowed[$mime])) throw new Exception('Only JPG/PNG allowed.');
      $ext = $allowed[$mime];
      $fname = 'u'.$user_id.'_'.time().'.'.$ext;
      $dest = $uploadDir.'/'.$fname;
      if (!is_uploaded_file($_FILES['profile_picture']['tmp_name'])) throw new Exception('Security check failed.');
      if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'],$dest)) throw new Exception('Failed to save uploaded file.');
      @chmod($dest, 0664);
      $profile_path = 'uploads/profile/'.$fname;
    }

    $upd = $pdo->prepare("UPDATE users SET email=?, phone=?, date_of_birth=?, first_name=?, last_name=?, profile_picture=? WHERE user_id=?");
    $ok  = $upd->execute([$email, ($phone?:null), ($dob?:null), $first_name, $last_name, $profile_path, $user_id]);
    if ($ok === false) throw new Exception('Database error while saving.');

    // reload for sidebar/UI
    $st->execute([$user_id]);
    $user = $st->fetch(PDO::FETCH_ASSOC);

    $alert = ['type'=>'success','msg'=>'Profile saved successfully!'];
  }
} catch(Exception $e){
  $alert = ['type'=>'error','msg'=>$e->getMessage()];
  if (!isset($user)) $user = ['username'=>'','email'=>'','phone'=>'','date_of_birth'=>null,'first_name'=>'','last_name'=>'','profile_picture'=>''];
}

// cart count (optional)
$cart_count=0;
try{
  $cs=$pdo->prepare("SELECT COALESCE(SUM(ci.quantity),0) c FROM cart c LEFT JOIN cart_items ci ON c.cart_id=ci.cart_id WHERE c.user_id=?");
  $cs->execute([$user_id]); $cart_count=(int)($cs->fetch()['c']??0);
}catch(Throwable $e){}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Profile - FitFuel</title>
<link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
  .form-grid{display:grid;grid-template-columns:200px 1fr;gap:12px}
  .label-cell{display:flex;align-items:center;color:#4b5563}
  .orange-btn{background:#ee4d2d}.orange-btn:hover{background:#d63f20}
  .sidebar-title{color:#9ca3af;font-size:.75rem;text-transform:uppercase;margin:.5rem 0}
  .sidebar-link{display:block;margin:.35rem 0}
</style>
</head>
<body class="bg-[#f6f6f6] text-slate-700">
  <!-- top -->
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
        <a href="cart.php" class="relative p-2 text-white hover:text-emerald-600"><i class="fas fa-shopping-cart text-xl"></i><?php if($cart_count>0):?><span class="absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count; ?></span><?php endif;?></a>
        <a href="profile.php" class="p-2 text-white hover:text-emerald-600"><i class="fas fa-user text-xl"></i></a>
      </div>
    </div>
  </nav>

  <div class="container mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-4 gap-6">
    <?php include 'sidebar.php'; ?>

    <section class="md:col-span-3 bg-white rounded-lg border border-gray-200">
      <div class="p-6 border-b">
        <h1 class="text-[20px] font-semibold">My Profile</h1>
        <p class="text-gray-500 text-sm">Manage and protect your account</p>
      </div>

      <?php if($alert['type']==='success'):?>
        <div class="mx-6 mt-4 rounded bg-emerald-50 text-emerald-700 px-4 py-3 border border-emerald-200"><i class="fa-solid fa-circle-check mr-2"></i><?php echo h($alert['msg']);?></div>
      <?php elseif($alert['type']==='error'):?>
        <div class="mx-6 mt-4 rounded bg-red-50 text-red-700 px-4 py-3 border border-red-200"><i class="fa-solid fa-triangle-exclamation mr-2"></i><?php echo h($alert['msg']);?></div>
      <?php endif;?>

      <form class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-8" method="post" enctype="multipart/form-data">
        <div class="lg:col-span-2">
          <div class="form-grid">
            <div class="label-cell">Username</div>
            <div><div class="text-slate-800"><?php echo h($user['username']); ?></div></div>

            <div class="label-cell">First Name</div>
            <div><input name="first_name" type="text" value="<?php echo h($user['first_name']); ?>" class="w-full border rounded px-3 py-2"></div>

            <div class="label-cell">Last Name</div>
            <div><input name="last_name" type="text" value="<?php echo h($user['last_name']); ?>" class="w-full border rounded px-3 py-2"></div>

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

            <div class="label-cell">Phone</div>
            <div><input name="phone" type="tel" value="<?php echo h($user['phone']); ?>" class="w-full border rounded px-3 py-2"></div>

            <div class="label-cell">Date of Birth</div>
            <div><input name="date_of_birth" type="date" value="<?php echo h($user['date_of_birth']); ?>" class="w-full border rounded px-3 py-2"></div>
          </div>
          <div class="mt-8"><button class="orange-btn text-white px-6 py-2 rounded shadow-sm">Save</button></div>
        </div>

        <div class="lg:col-span-1 flex flex-col items-center">
          <div class="w-40 h-40 rounded-full overflow-hidden border border-gray-200">
            <img id="avatarPreview" src="<?php echo h($user['profile_picture'] ?: 'img/placeholder.svg'); ?>" class="w-full h-full object-cover" alt="">
          </div>
          <label class="mt-4 inline-block">
            <span class="px-4 py-2 border rounded text-gray-700 cursor-pointer hover:bg-gray-50">Select Image</span>
            <input type="file" name="profile_picture" id="profile_picture" class="hidden" accept="image/jpeg,image/png">
          </label>
        </div>
      </form>
    </section>
  </div>

  <footer class="bg-slate-800 text-white py-12 mt-12 text-center"><p>&copy; 2024 FitFuel. All rights reserved.</p></footer>

  <script>
    function toggleEmail(edit){document.getElementById('emailView').classList.toggle('hidden', edit);document.getElementById('emailEdit').classList.toggle('hidden', !edit);}
    const input=document.getElementById('profile_picture'),preview=document.getElementById('avatarPreview');
    if(input&&preview){input.addEventListener('change',e=>{const f=e.target.files[0];if(f)preview.src=URL.createObjectURL(f);});}
  </script>
</body>
</html>
