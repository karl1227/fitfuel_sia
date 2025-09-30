<?php
// inbox.php — simple message center for FitFuel
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$pdo = getDBConnection();
$alert = ['type'=>'','msg'=>''];

/* ------------------------------------------------------------------
   DB bootstrap: messages table (tiny “inbox” for each user)
-------------------------------------------------------------------*/
$pdo->exec("
CREATE TABLE IF NOT EXISTS user_inbox (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  from_name VARCHAR(100) DEFAULT 'FitFuel',
  subject VARCHAR(200) NOT NULL,
  body TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (user_id),
  INDEX (is_read),
  INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

try {
  $pdo->exec("
    ALTER TABLE user_inbox
    ADD CONSTRAINT fk_user_inbox_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE
  ");
} catch (Throwable $e) {
  // ok if FK cannot be added; table still works
}

/* fetch user for sidebar/header */
$u = $pdo->prepare("SELECT username, profile_picture FROM users WHERE user_id=?");
$u->execute([$user_id]);
$user = $u->fetch(PDO::FETCH_ASSOC) ?: ['username'=>'','profile_picture'=>null];

/* seed a welcome message if user has none */
try {
  $cnt = (int)$pdo->query("SELECT COUNT(*) FROM user_inbox WHERE user_id = ".(int)$user_id)->fetchColumn();
  if ($cnt === 0) {
    $seed = $pdo->prepare("INSERT INTO user_inbox (user_id, subject, body) VALUES (?,?,?)");
    $seed->execute([
      $user_id,
      "Welcome to FitFuel 🎉",
      "Hi ".$user['username']."! Thanks for joining FitFuel. We’ll use this inbox for order updates, promos, and account notices."
    ]);
  }
} catch (Throwable $e) {}

/* ------------------------------------------------------------------
   Actions: mark read/unread, delete
-------------------------------------------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'read') {
      $stmt = $pdo->prepare("UPDATE user_inbox SET is_read=1 WHERE id=? AND user_id=?");
      $stmt->execute([$id, $user_id]);
      $alert = ['type'=>'success','msg'=>'Marked as read.'];
    } elseif ($action === 'unread') {
      $stmt = $pdo->prepare("UPDATE user_inbox SET is_read=0 WHERE id=? AND user_id=?");
      $stmt->execute([$id, $user_id]);
      $alert = ['type'=>'success','msg'=>'Marked as unread.'];
    } elseif ($action === 'delete') {
      $stmt = $pdo->prepare("DELETE FROM user_inbox WHERE id=? AND user_id=?");
      $stmt->execute([$id, $user_id]);
      $alert = ['type'=>'success','msg'=>'Message deleted.'];
    }
  } catch (Throwable $e) {
    $alert = ['type'=>'error','msg'=>$e->getMessage()];
  }
}

/* ------------------------------------------------------------------
   Listing (simple pagination)
-------------------------------------------------------------------*/
$per_page = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

$total = 0;
try {
  $t = $pdo->prepare("SELECT COUNT(*) c FROM user_inbox WHERE user_id=?");
  $t->execute([$user_id]);
  $total = (int)($t->fetchColumn() ?: 0);
} catch (Throwable $e) {}

$messages = [];
try {
  $q = $pdo->prepare("
    SELECT id, from_name, subject, body, is_read, created_at
    FROM user_inbox
    WHERE user_id=?
    ORDER BY created_at DESC, id DESC
    LIMIT ? OFFSET ?
  ");
  $q->bindValue(1, $user_id, PDO::PARAM_INT);
  $q->bindValue(2, $per_page, PDO::PARAM_INT);
  $q->bindValue(3, $offset, PDO::PARAM_INT);
  $q->execute();
  $messages = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {}

$pages = max(1, (int)ceil($total / $per_page));

/* cart count for header (optional) */
$cart_count = 0;
try {
  $cs = $pdo->prepare("SELECT COALESCE(SUM(ci.quantity),0) c
                       FROM cart c LEFT JOIN cart_items ci ON c.cart_id=ci.cart_id
                       WHERE c.user_id=?");
  $cs->execute([$user_id]);
  $cart_count = (int)($cs->fetch()['c'] ?? 0);
} catch (Throwable $e) {}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Inbox - FitFuel</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
  .badge{font-size:11px;padding:2px 8px;border-radius:9999px}
  .badge-new{background:#dcfce7;color:#065f46}
  .muted{color:#6b7280}
  .truncate-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
</style>
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
          <?php if ($cart_count>0): ?>
            <span class="absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count; ?></span>
          <?php endif; ?>
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
            <h1 class="text-[20px] font-semibold">Inbox</h1>
            <p class="muted text-sm">Messages about your orders, promos, and account updates.</p>
          </div>
          <div class="muted text-sm">Total: <?php echo (int)$total; ?></div>
        </div>

        <?php if($alert['type']): ?>
          <div class="mx-6 mt-4 rounded px-4 py-3 border <?php echo $alert['type']==='success'?'bg-emerald-50 text-emerald-700 border-emerald-200':'bg-red-50 text-red-700 border-red-200'; ?>">
            <?php echo h($alert['msg']); ?>
          </div>
        <?php endif; ?>

        <div class="p-2 sm:p-4">
          <?php if (empty($messages)): ?>
            <div class="m-4 p-8 text-center border border-dashed rounded text-gray-500">
              No messages yet.
            </div>
          <?php else: ?>
            <ul class="divide-y">
              <?php foreach ($messages as $m): ?>
                <li class="p-4 sm:p-5 flex gap-4">
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <span class="font-semibold"><?php echo h($m['subject']); ?></span>
                      <?php if (!$m['is_read']): ?>
                        <span class="badge badge-new">NEW</span>
                      <?php endif; ?>
                    </div>
                    <div class="muted text-sm">
                      From <?php echo h($m['from_name']); ?> • <?php echo h(date('M d, Y h:i A', strtotime($m['created_at']))); ?>
                    </div>
                    <p class="mt-2 text-[15px] truncate-2"><?php echo nl2br(h($m['body'])); ?></p>
                  </div>
                  <div class="flex flex-col gap-2">
                    <form method="post">
                      <input type="hidden" name="id" value="<?php echo (int)$m['id']; ?>">
                      <input type="hidden" name="action" value="<?php echo $m['is_read'] ? 'unread' : 'read'; ?>">
                      <button class="px-3 py-1 text-sm border rounded hover:bg-gray-50">
                        <?php echo $m['is_read'] ? 'Mark Unread' : 'Mark Read'; ?>
                      </button>
                    </form>
                    <form method="post" onsubmit="return confirm('Delete this message?');">
                      <input type="hidden" name="id" value="<?php echo (int)$m['id']; ?>">
                      <input type="hidden" name="action" value="delete">
                      <button class="px-3 py-1 text-sm border rounded text-red-700 border-red-600 hover:bg-red-50">Delete</button>
                    </form>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>

            <!-- pagination -->
            <?php if ($pages > 1): ?>
              <div class="p-4 flex items-center justify-between text-sm">
                <div class="muted">Page <?php echo $page; ?> of <?php echo $pages; ?></div>
                <div class="flex gap-2">
                  <?php if ($page > 1): ?>
                    <a class="px-3 py-1 border rounded hover:bg-gray-50" href="?page=<?php echo $page-1; ?>">Prev</a>
                  <?php endif; ?>
                  <?php if ($page < $pages): ?>
                    <a class="px-3 py-1 border rounded hover:bg-gray-50" href="?page=<?php echo $page+1; ?>">Next</a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>
          <?php endif; ?>
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
