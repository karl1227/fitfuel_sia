<?php
// banks_cards.php — manage saved cards/bank accounts (metadata only)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$pdo = getDBConnection();
$alert = ['type'=>'','msg'=>''];

/* Ensure table exists */
$pdo->exec("
CREATE TABLE IF NOT EXISTS user_payment_methods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type ENUM('card','bank') NOT NULL DEFAULT 'card',
  brand VARCHAR(50) NULL,
  last4 VARCHAR(4) NULL,
  name_on_card VARCHAR(100) NULL,
  exp_month TINYINT NULL,
  exp_year SMALLINT NULL,
  bank_name VARCHAR(100) NULL,
  account_last4 VARCHAR(4) NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
try {
  $pdo->exec("ALTER TABLE user_payment_methods
    ADD CONSTRAINT fk_upm_user
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE");
} catch (Throwable $e) {}

/* Fetch user (for sidebar) */
$u = $pdo->prepare("SELECT username, profile_picture FROM users WHERE user_id=?");
$u->execute([$user_id]);
$user = $u->fetch(PDO::FETCH_ASSOC) ?: ['username'=>'','profile_picture'=>null];

/* Actions */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  try {
    if ($action === 'add_card') {
      $name  = trim($_POST['name_on_card'] ?? '');
      $num   = preg_replace('/\D+/', '', (string)($_POST['card_number'] ?? ''));
      $brand = trim($_POST['brand'] ?? '');
      $exp_m = (int)($_POST['exp_month'] ?? 0);
      $exp_y = (int)($_POST['exp_year'] ?? 0);

      if ($name === '') throw new Exception('Name on card is required.');
      if (strlen($num) < 12 || strlen($num) > 19) throw new Exception('Enter a valid card number.');
      if ($exp_m < 1 || $exp_m > 12) throw new Exception('Enter a valid expiry month.');
      if ($exp_y < (int)date('Y') || $exp_y > (int)date('Y') + 20) throw new Exception('Enter a valid expiry year.');

      $last4 = substr($num, -4);
      $hasAny = (int)$pdo->query("SELECT COUNT(*) FROM user_payment_methods WHERE user_id = ".(int)$user_id)->fetchColumn() > 0;

      $pdo->prepare("INSERT INTO user_payment_methods (user_id,type,brand,last4,name_on_card,exp_month,exp_year,is_default)
                     VALUES (?,'card',?,?,?,?,?,?)")
          ->execute([$user_id, ($brand ?: null), $last4, $name, $exp_m, $exp_y, $hasAny ? 0 : 1]);

      $alert = ['type'=>'success','msg'=>'Card saved (only last 4 stored).'];
    } elseif ($action === 'set_default') {
      $id = (int)($_POST['id'] ?? 0);
      $pdo->beginTransaction();
      $pdo->prepare("UPDATE user_payment_methods SET is_default=0 WHERE user_id=?")->execute([$user_id]);
      $pdo->prepare("UPDATE user_payment_methods SET is_default=1 WHERE id=? AND user_id=?")->execute([$id,$user_id]);
      $pdo->commit();
      $alert = ['type'=>'success','msg'=>'Default payment method updated.'];
    } elseif ($action === 'delete') {
      $id = (int)($_POST['id'] ?? 0);
      $pdo->prepare("DELETE FROM user_payment_methods WHERE id=? AND user_id=?")->execute([$id,$user_id]);
      $alert = ['type'=>'success','msg'=>'Payment method removed.'];
    }
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $alert = ['type'=>'error','msg'=>$e->getMessage()];
  }
}

/* Load methods for display */
$items = [];
try {
  $s = $pdo->prepare("SELECT * FROM user_payment_methods WHERE user_id=? ORDER BY is_default DESC, updated_at DESC, created_at DESC");
  $s->execute([$user_id]);
  $items = $s->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {}

$cart_count = 0;
try {
  $cs=$pdo->prepare("SELECT COALESCE(SUM(ci.quantity),0) c
                     FROM cart c LEFT JOIN cart_items ci ON c.cart_id=ci.cart_id
                     WHERE c.user_id=?");
  $cs->execute([$user_id]); $cart_count=(int)($cs->fetch()['c']??0);
} catch (Throwable $e) {}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Banks & Cards - FitFuel</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>.orange-btn{background:#ee4d2d}.orange-btn:hover{background:#d63f20}.muted{color:#6b7280}</style>
</head>
<body class="bg-[#f6f6f6] text-slate-700 min-h-screen flex flex-col">
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
        <div class="p-6 border-b">
          <h1 class="text-[20px] font-semibold">Banks & Cards</h1>
          <p class="muted text-sm">Only non-sensitive metadata is stored (brand, last 4, expiry, name).</p>
        </div>

        <?php if($alert['type']): ?>
          <div class="mx-6 mt-4 rounded px-4 py-3 border <?php echo $alert['type']==='success'?'bg-emerald-50 text-emerald-700 border-emerald-200':'bg-red-50 text-red-700 border-red-200'; ?>">
            <?php echo h($alert['msg']); ?>
          </div>
        <?php endif; ?>

        <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Add Card -->
          <div class="lg:col-span-1">
            <h2 class="font-semibold mb-3">Add a Card</h2>
            <form method="post" class="space-y-3">
              <input type="hidden" name="action" value="add_card">
              <label class="block">
                <span class="text-sm muted">Name on Card</span>
                <input type="text" name="name_on_card" class="w-full border rounded px-3 py-2" required>
              </label>
              <label class="block">
                <span class="text-sm muted">Card Number</span>
                <input type="text" inputmode="numeric" name="card_number" class="w-full border rounded px-3 py-2" placeholder="•••• •••• •••• ••••" required>
              </label>
              <div class="grid grid-cols-3 gap-3">
                <label class="block">
                  <span class="text-sm muted">Brand</span>
                  <input type="text" name="brand" class="w-full border rounded px-3 py-2" placeholder="Visa/Mastercard">
                </label>
                <label class="block">
                  <span class="text-sm muted">Exp. Month</span>
                  <input type="number" min="1" max="12" name="exp_month" class="w-full border rounded px-3 py-2" required>
                </label>
                <label class="block">
                  <span class="text-sm muted">Exp. Year</span>
                  <input type="number" min="<?php echo (int)date('Y');?>" max="<?php echo (int)date('Y')+20;?>" name="exp_year" class="w-full border rounded px-3 py-2" required>
                </label>
              </div>
              <button class="orange-btn text-white px-5 py-2 rounded">Save Card</button>
            </form>
            <p class="text-xs muted mt-3">We do not store full card numbers; only the last 4 digits and metadata.</p>
          </div>

          <!-- Saved Methods -->
          <div class="lg:col-span-2">
            <h2 class="font-semibold mb-3">Saved Payment Methods</h2>

            <?php if (empty($items)): ?>
              <div class="border border-dashed rounded p-6 text-center text-gray-500">
                No payment methods yet.
              </div>
            <?php else: ?>
              <div class="space-y-3">
                <?php foreach ($items as $it): ?>
                  <div class="border rounded p-4 flex items-center justify-between">
                    <div>
                      <?php if ($it['type'] === 'card'): ?>
                        <div class="font-medium">
                          <?php echo h($it['brand'] ?: 'Card'); ?> •••• <?php echo h($it['last4']); ?>
                          <?php if ((int)$it['is_default'] === 1): ?>
                            <span class="ml-2 text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">Default</span>
                          <?php endif; ?>
                        </div>
                        <div class="text-sm muted">
                          <?php echo h($it['name_on_card']); ?> — Exp <?php echo sprintf('%02d/%02d', (int)$it['exp_month'], (int)($it['exp_year']%100)); ?>
                        </div>
                      <?php else: ?>
                        <div class="font-medium"><?php echo h($it['bank_name'] ?: 'Bank'); ?> •••• <?php echo h($it['account_last4']); ?></div>
                      <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-2">
                      <?php if ((int)$it['is_default'] !== 1): ?>
                        <form method="post">
                          <input type="hidden" name="action" value="set_default">
                          <input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
                          <button class="px-3 py-1 text-sm border rounded hover:bg-gray-50">Set Default</button>
                        </form>
                      <?php endif; ?>
                      <form method="post" onsubmit="return confirm('Delete this payment method?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
                        <button class="px-3 py-1 text-sm border rounded text-red-700 border-red-600 hover:bg-red-50">Delete</button>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </section>
    </div>
  </main>
  <?php // partials/footer.php ?>
<footer class="bg-slate-800 text-white py-12 mt-auto">
  <div class="container mx-auto px-4 text-center">
    <p>&copy; 2024 FitFuel. All rights reserved.</p>
  </div>
</footer>

</html>
