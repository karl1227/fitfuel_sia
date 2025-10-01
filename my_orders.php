<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function active_link($f){
  $c = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
  return $c === $f ? 'text-emerald-600 font-semibold' : 'hover:text-emerald-600';
}

/* ---------- simple flash helpers ---------- */
function set_flash($key, $msg) { $_SESSION['flash_'.$key] = $msg; }
function get_flash($key) {
    if (!empty($_SESSION['flash_'.$key])) {
        $m = $_SESSION['flash_'.$key];
        unset($_SESSION['flash_'.$key]);
        return $m;
    }
    return null;
}

/* ---------- helpers (UI) ---------- */
function status_pill($status) {
    $status = strtolower(trim($status));
    switch ($status) {
        case 'pending':    return ['To Pay',      'bg-yellow-100 text-yellow-800'];
        case 'processing': return ['To Ship',     'bg-amber-100 text-amber-800'];
        case 'shipped':    return ['To Receive',  'bg-sky-100 text-sky-800'];
        case 'delivered':  return ['Completed',   'bg-emerald-100 text-emerald-800'];
        case 'cancelled':  return ['Cancelled',   'bg-red-100 text-red-800'];
        case 'returned':   return ['Return',      'bg-slate-200 text-slate-700'];
        default:           return [ucfirst($status),'bg-slate-100 text-slate-700'];
    }
}
function short_desc($text, $limit = 120) {
    $t = strip_tags((string)$text);
    return mb_strlen($t) <= $limit ? $t : mb_substr($t, 0, $limit-1).'…';
}
/* Accepts JSON array string or CSV and returns the first image path */
function first_image($images, $fallback = 'img/placeholder-product.png') {
    if (!$images) return $fallback;
    $images = trim($images);

    // Try JSON
    if ($images !== '' && ($images[0] === '[' || $images[0] === '{')) {
        $decoded = json_decode($images, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (is_string($item) && $item !== '') return $item;
                    if (is_array($item)) {
                        if (!empty($item['url'])) return $item['url'];
                        if (!empty($item['path'])) return $item['path'];
                    }
                }
            }
        }
    }
    // Fallback: CSV
    $parts = array_filter(array_map('trim', explode(',', $images)));
    return !empty($parts) ? $parts[0] : $fallback;
}

/* ---------- fetch ---------- */
$pdo = getDBConnection();

/* fetch user data for sidebar */
$user = ['username' => 'User', 'profile_picture' => null];
try {
    $userStmt = $pdo->prepare("SELECT username, profile_picture FROM users WHERE user_id = ?");
    $userStmt->execute([$user_id]);
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    if ($userData) {
        $user = $userData;
    }
} catch (Throwable $e) {}

/* cart badge */
$cart_count = 0;
try{
  $cs=$pdo->prepare("SELECT COALESCE(SUM(ci.quantity),0) c
                     FROM cart c LEFT JOIN cart_items ci ON c.cart_id=ci.cart_id
                     WHERE c.user_id=?");
  $cs->execute([$user_id]); $cart_count=(int)($cs->fetch()['c']??0);
}catch(Throwable $e){}

try {
    $sql = "
        SELECT 
            o.order_id, o.custom_order_id, o.total_amount, o.status, o.created_at,
            COUNT(oi.order_item_id) as item_count,
            p.name as first_product_name,
            p.images as first_product_images
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.order_id
        LEFT JOIN products p ON p.product_id = oi.product_id
        WHERE o.user_id = ?
        GROUP BY o.order_id, o.custom_order_id, o.total_amount, o.status, o.created_at
        ORDER BY o.created_at DESC, o.order_id DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    $rows = [];
    $error = "Database error: " . $e->getMessage();
}

/* ---------- prepare data for tabs & cards ---------- */
$tabMap = [
    'all'        => [],
    'to_pay'     => ['pending'],
    'to_ship'    => ['processing'],
    'to_receive' => ['shipped'],
    'completed'  => ['delivered'],
    'cancelled'  => ['cancelled'],
    'return'     => ['returned'],
];
$counts = array_fill_keys(array_keys($tabMap), 0);

$cards = [];
foreach ($rows as $r) {
    $statusKey = strtolower($r['status']);
    if ($statusKey === 'processing')       $tabKey = 'to_ship';
    elseif ($statusKey === 'shipped')       $tabKey = 'to_receive';
    elseif ($statusKey === 'pending')       $tabKey = 'to_pay';
    elseif ($statusKey === 'delivered')     $tabKey = 'completed';
    elseif ($statusKey === 'cancelled')     $tabKey = 'cancelled';
    elseif ($statusKey === 'returned')      $tabKey = 'return';
    else                                    $tabKey = 'all';

    // Get first item details for display
    $item_count = (int)$r['item_count'];
    $first_product_name = $r['first_product_name'] ?? 'Unknown Product';
    $first_product_images = $r['first_product_images'] ?? '';
    
    // Get the first image from the first product
    $first_image = first_image($first_product_images);

    $cards[] = [
        'order_id'        => (int)$r['order_id'],
        'custom_order_id' => $r['custom_order_id'],
        'status'          => $r['status'],
        'tabKey'          => $tabKey,
        'created_at'      => $r['created_at'],
        'product_name'    => $first_product_name,
        'product_desc'    => $item_count > 1 ? "and {$item_count} other item" . ($item_count > 2 ? 's' : '') : '',
        'product_image'   => $first_image,
        'qty'             => $item_count,
        'line_total'      => (float)$r['total_amount'],
    ];

    $counts['all']++;
    foreach ($tabMap as $tab => $statuses) {
        if ($tab === 'all') continue;
        if (in_array($statusKey, $statuses, true)) $counts[$tab]++;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Orders - FitFuel</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>.tab-active{background-color:rgb(16 185 129 / 0.15);color:#065f46}</style>
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
          <?php if($cart_count > 0): ?>
            <span class="absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count; ?></span>
          <?php endif; ?>
        </a>
        <a href="profile.php" class="p-2 text-white hover:text-emerald-600"><i class="fas fa-user text-xl"></i></a>
      </div>
    </div>
  </nav>

  <main class="flex-1">
    <div class="container mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-4 gap-6">
      <?php include __DIR__.'/sidebar.php'; ?>

      <section class="md:col-span-3 bg-white rounded-lg border border-gray-200">
        <div class="p-6 border-b"><h1 class="text-[20px] font-semibold">My Orders</h1></div>

        <!-- Flash messages -->
        <?php if ($msg = get_flash('success')): ?>
          <div class="mx-6 mt-4 rounded bg-emerald-50 text-emerald-700 px-4 py-3 border border-emerald-200">
            <?= htmlspecialchars($msg) ?>
          </div>
        <?php endif; ?>
        <?php if ($msg = get_flash('error')): ?>
          <div class="mx-6 mt-4 rounded bg-red-50 text-red-700 px-4 py-3 border border-red-200">
            <?= htmlspecialchars($msg) ?>
          </div>
        <?php endif; ?>

        <div class="p-6">
          <div class="bg-gray-50 rounded-lg border p-3 flex gap-2 overflow-x-auto mb-6">
            <?php
              $tabLabels = [
                'all'        => 'All',
                'to_pay'     => 'To Pay',
                'to_ship'    => 'To Ship',
                'to_receive' => 'To Receive',
                'completed'  => 'Completed',
                'cancelled'  => 'Cancelled',
                'return'     => 'Return'
              ];
              foreach ($tabLabels as $key=>$label): ?>
              <button class="tab-btn px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100 whitespace-nowrap"
                      data-tab="<?= htmlspecialchars($key) ?>">
                <?= $label ?> <span class="ml-1 text-xs text-slate-500">(<?= (int)($counts[$key] ?? 0) ?>)</span>
              </button>
            <?php endforeach; ?>
          </div>

          <div id="orderList" class="space-y-4">
            <?php if (isset($error)): ?>
              <div class="p-4 rounded-lg bg-red-50 text-red-700 border border-red-200"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (empty($cards)): ?>
              <div class="p-8 text-center text-gray-500 bg-gray-50 rounded-lg border">
                You don't have any orders yet.
              </div>
            <?php else: ?>
              <?php foreach ($cards as $c): [$pillText,$pillClass]=status_pill($c['status']); ?>
                <div class="order-card bg-gray-50 rounded-lg border p-4 flex items-center gap-4"
                     data-status="<?= htmlspecialchars($c['tabKey']) ?>">
                  <div class="w-16 h-16 rounded-lg overflow-hidden bg-slate-100 flex-shrink-0">
                    <img src="<?= htmlspecialchars($c['product_image']) ?>" alt="<?= htmlspecialchars($c['product_name']) ?>" class="w-full h-full object-cover">
                  </div>

                  <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                      <div class="min-w-0">
                        <a href="order_details.php?order_id=<?= urlencode($c['custom_order_id']) ?>"
                           class="text-slate-900 font-semibold line-clamp-1 hover:underline">
                          <?= htmlspecialchars($c['product_name']) ?>
                        </a>
                        <p class="text-sm text-slate-500 mt-0.5 line-clamp-1"><?= htmlspecialchars($c['product_desc']) ?></p>
                        <div class="mt-2 text-xs text-slate-500">
                          <span><?= date("M d, Y", strtotime($c['created_at'])) ?></span>
                        </div>
                      </div>
                      <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $pillClass ?> whitespace-nowrap"><?= htmlspecialchars($pillText) ?></span>
                    </div>
                  </div>

                  <div class="text-right">
                    <div class="text-emerald-600 font-bold">₱<?= number_format($c['line_total'], 2) ?></div>
                    <a href="order_details.php?order_id=<?= urlencode($c['custom_order_id']) ?>" class="text-emerald-600 text-sm hover:underline">View Details</a>
                  </div>
                </div>
              <?php endforeach; ?>
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


  <script>
    const tabs=[...document.querySelectorAll('.tab-btn')];
    const cards=[...document.querySelectorAll('.order-card')];

    function setActiveTab(key){
      tabs.forEach(t=>t.classList.toggle('tab-active',t.dataset.tab===key));
      cards.forEach(c=>{
        if(key==='all') c.classList.remove('hidden');
        else c.classList.toggle('hidden',c.dataset.status!==key);
      });
      localStorage.setItem('orders_active_tab',key);
    }

    tabs.forEach(btn=>btn.addEventListener('click',()=>setActiveTab(btn.dataset.tab)));

    const saved=localStorage.getItem('orders_active_tab')||'all';
    setActiveTab(tabs.find(t=>t.dataset.tab===saved)?saved:'all');
  </script>
</body>
</html>
