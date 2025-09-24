<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';
$user_id = $_SESSION['user_id'];

/* ---------- helpers ---------- */
function status_pill($status) {
    $status = strtolower(trim($status));
    switch ($status) {
        case 'pending':    return ['To Pay',    'bg-yellow-100 text-yellow-800'];
        case 'processing': return ['To Ship',   'bg-amber-100 text-amber-800'];
        case 'shipped':    return ['To Ship',   'bg-amber-100 text-amber-800'];
        case 'delivered':  return ['Completed', 'bg-emerald-100 text-emerald-800'];
        case 'cancelled':  return ['Cancelled', 'bg-red-100 text-red-800'];
        case 'returned':   return ['Return',    'bg-slate-200 text-slate-700'];
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
    if ($images[0] === '[' || $images[0] === '{') {
        $decoded = json_decode($images, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (is_array($decoded)) {
                // JSON array of strings or objects with 'url'/'path'
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
try {
    $pdo = getDBConnection();
    $sql = "
        SELECT 
            o.order_id, o.custom_order_id, o.total_amount, o.status, o.created_at,
            oi.quantity, oi.price AS item_price,
            p.product_id, p.name AS product_name, p.description AS product_description,
            p.images AS product_images
        FROM orders o
        JOIN order_items oi ON oi.order_id = o.order_id
        JOIN products p ON p.product_id = oi.product_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC, o.order_id DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    $rows = [];
    $error = "Database error: " . $e->getMessage();
}

/* ---------- prepare data ---------- */
$tabMap = [
    'all'       => [],
    'to_pay'    => ['pending'],
    'to_ship'   => ['processing','shipped'],
    'completed' => ['delivered'],
    'cancelled' => ['cancelled'],
    'return'    => ['returned'],
];
$counts = array_fill_keys(array_keys($tabMap), 0);

$cards = [];
foreach ($rows as $r) {
    $statusKey = strtolower($r['status']);
    if (in_array($statusKey, ['processing','shipped'])) $tabKey = 'to_ship';
    elseif ($statusKey === 'pending') $tabKey = 'to_pay';
    elseif ($statusKey === 'delivered') $tabKey = 'completed';
    elseif ($statusKey === 'cancelled') $tabKey = 'cancelled';
    elseif ($statusKey === 'returned') $tabKey = 'return';
    else $tabKey = 'all';

    $img = first_image($r['product_images']);

    $cards[] = [
        'order_id'        => $r['order_id'],
        'custom_order_id' => $r['custom_order_id'],
        'status'          => $r['status'],
        'tabKey'          => $tabKey,
        'created_at'      => $r['created_at'],
        'product_name'    => $r['product_name'],
        'product_desc'    => short_desc($r['product_description']),
        'product_image'   => $img,
        'qty'             => (int)$r['quantity'],
        'line_total'      => (float)$r['item_price'] * (int)$r['quantity'],
    ];

    $counts['all']++;
    foreach ($tabMap as $tab => $statuses) {
        if ($tab === 'all') continue;
        if (in_array($statusKey, $statuses, true)) $counts[$tab]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Orders - FitFuel</title>
  <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <style>.tab-active{background-color:rgb(16 185 129 / 0.15);color:#065f46}</style>
</head>
<body class="bg-gray-50 font-body text-slate-700">
  <div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
      <a href="profile.php" class="flex items-center text-emerald-600 hover:text-emerald-800">
        <i class="fas fa-arrow-left mr-2"></i> Back
      </a>
      <h1 class="text-2xl font-bold text-slate-900 ml-4">My Orders</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-3 flex gap-2 overflow-x-auto">
      <?php
        $tabLabels = ['all'=>'All','to_pay'=>'To Pay','to_ship'=>'To Ship','completed'=>'Completed','cancelled'=>'Cancelled','return'=>'Return'];
        foreach ($tabLabels as $key=>$label): ?>
        <button class="tab-btn px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100 whitespace-nowrap"
                data-tab="<?= htmlspecialchars($key) ?>">
          <?= $label ?> <span class="ml-1 text-xs text-slate-500">(<?= (int)($counts[$key] ?? 0) ?>)</span>
        </button>
      <?php endforeach; ?>
    </div>

    <div id="orderList" class="mt-5 space-y-4">
      <?php if (isset($error)): ?>
        <div class="p-4 rounded-lg bg-red-50 text-red-700 border border-red-200"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if (empty($cards)): ?>
        <div class="p-8 text-center text-gray-500 bg-white rounded-xl shadow-sm border">
          You don’t have any orders yet.
        </div>
      <?php else: ?>
        <?php foreach ($cards as $c): [$pillText,$pillClass]=status_pill($c['status']); ?>
          <div class="order-card bg-white rounded-2xl shadow-sm border p-4 flex items-center gap-4"
               data-status="<?= htmlspecialchars($c['tabKey']) ?>">
            <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0">
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
                    <span>Order: <span class="font-medium text-slate-700"><?= htmlspecialchars($c['custom_order_id']) ?></span></span>
                    <span class="mx-2">•</span>
                    <span><?= date("M d, Y", strtotime($c['created_at'])) ?></span>
                    <?php if ($c['qty']>1): ?><span class="mx-2">•</span><span>Qty: <?= (int)$c['qty'] ?></span><?php endif; ?>
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
