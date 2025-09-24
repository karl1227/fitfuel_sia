<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';

/* ---------- Flash helpers ---------- */
function set_flash($key, $msg) { $_SESSION['flash_'.$key] = $msg; }
function get_flash($key) {
  if (!empty($_SESSION['flash_'.$key])) {
    $m = $_SESSION['flash_'.$key];
    unset($_SESSION['flash_'.$key]);
    return $m;
  }
  return null;
}

/* ---------- Auth check ---------- */
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
$user_id = (int) $_SESSION['user_id'];

/* ---------- Get order id ---------- */
if (!isset($_GET['order_id'])) {
  header("Location: my_orders.php");
  exit();
}
$custom_order_id = trim($_GET['order_id']);

try {
  $pdo = getDBConnection();

  /* ---------- Handle cancel (POST) ---------- */
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $post_order_id = isset($_POST['order_pk']) ? (int)$_POST['order_pk'] : 0;

    $chk = $pdo->prepare("SELECT order_id, custom_order_id, status FROM orders WHERE order_id = ? AND user_id = ? LIMIT 1");
    $chk->execute([$post_order_id, $user_id]);
    $own = $chk->fetch();

    if (!$own) {
      set_flash('error', 'Order not found or not yours.');
    } else {
      $st = strtolower(trim($own['status']));
      if ($st !== 'pending') {
        set_flash('error', 'Only orders in "To Pay" can be cancelled.');
      } else {
        $pdo->prepare("UPDATE orders SET status='cancelled' WHERE order_id = ? LIMIT 1")->execute([$post_order_id]);
        set_flash('success', 'Order '.$own['custom_order_id'].' has been cancelled.');
        header("Location: order_details.php?order_id=".urlencode($own['custom_order_id']));
        exit();
      }
    }
  }

  /* ---------- Fetch order (by custom_order_id + owner) ---------- */
  $stmt = $pdo->prepare("SELECT * FROM orders WHERE custom_order_id = ? AND user_id = ? LIMIT 1");
  $stmt->execute([$custom_order_id, $user_id]);
  $order = $stmt->fetch();
  if (!$order) die("Order not found or you don't have permission to view it.");

  /* ---------- Fetch items ---------- */
  $items_stmt = $pdo->prepare("SELECT oi.*, p.name, p.images 
                               FROM order_items oi
                               LEFT JOIN products p ON oi.product_id = p.product_id
                               WHERE oi.order_id = ?");
  $items_stmt->execute([$order['order_id']]);
  $items = $items_stmt->fetchAll();

} catch (PDOException $e) {
  die("Database error: " . $e->getMessage());
}

/* ---------- Helpers ---------- */
function first_image($images, $fallback = 'img/placeholder-product.png') {
  if (empty($images)) return $fallback;
  $images = trim($images);
  if ($images !== '' && ($images[0] === '[' || $images[0] === '{')) {
    $decoded = json_decode($images, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
      foreach ($decoded as $it) {
        if (is_string($it) && $it !== '') return $it;
        if (is_array($it)) {
          if (!empty($it['url'])) return $it['url'];
          if (!empty($it['path'])) return $it['path'];
        }
      }
    }
  }
  $parts = array_filter(array_map('trim', explode(',', $images)));
  return !empty($parts) ? $parts[0] : $fallback;
}

function status_banner_text($status) {
  $s = strtolower(trim((string)$status));
  if (in_array($s, ['processing','shipped'], true)) return 'Waiting for delivery';
  if ($s === 'pending') return 'Waiting for seller to ship';
  if ($s === 'delivered') return 'Delivered';
  if ($s === 'cancelled') return 'Cancelled';
  if ($s === 'returned') return 'Return';
  return ucfirst($s);
}

function build_delivery_info(array $order): array {
  $name='';$phone='';$address='';
  if (!empty($order['shipping_address'])) {
    $raw = trim((string)$order['shipping_address']);
    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
      $name   = $decoded['full_name'] ?? $decoded['name'] ?? '';
      $phone  = $decoded['phone'] ?? $decoded['contact'] ?? '';
      $addr_parts = [
        $decoded['address'] ?? '',
        $decoded['barangay'] ?? '',
        $decoded['city'] ?? '',
        $decoded['state'] ?? '',
        $decoded['province'] ?? '',
        $decoded['postal_code'] ?? '',
        $decoded['country'] ?? ''
      ];
      $address = implode(', ', array_filter(array_map('trim',$addr_parts)));
    } else {
      $address = $raw;
    }
  }
  if ($name==='')  $name  = $order['recipient_name'] ?? $order['shipping_name'] ?? '';
  if ($phone==='') $phone = $order['recipient_phone'] ?? $order['phone'] ?? '';
  if ($address==='') {
    $addr_parts = [
      $order['address_line1'] ?? '',
      $order['address_line2'] ?? '',
      $order['barangay'] ?? '',
      $order['city'] ?? ($order['municipality'] ?? ''),
      $order['province'] ?? '',
      $order['postal_code'] ?? '',
      $order['country'] ?? ''
    ];
    $address = implode(', ', array_filter(array_map('trim',$addr_parts)));
  }
  $address = trim(preg_replace('/\s+/', ' ', preg_replace('/\s*,\s*,+/', ',', $address)));
  return [$name,$phone,$address];
}

function payment_text($method) {
  $m = strtolower(trim((string)$method));
  return match($m) {
    'cod'    => 'Pay by Cash on Delivery',
    'gcash'  => 'Pay by GCash',
    'paypal' => 'Pay by PayPal',
    default  => ($method ? ucfirst($method) : ''),
  };
}

/* ---- Compute subtotals and a robust shipping fee ---- */
$items_subtotal = 0.0;
foreach ($items as $it) {
  $items_subtotal += ((float)$it['price']) * ((int)$it['quantity']);
}
$discount_amount  = isset($order['discount_amount'])  ? (float)$order['discount_amount']  : 0.0;
$voucher_discount = isset($order['voucher_discount']) ? (float)$order['voucher_discount'] : 0.0;
$other_fee        = isset($order['other_fee'])        ? (float)$order['other_fee']        : 0.0;

/* Try to read shipping from multiple possible places */
$shipping_fee = null;
$possible_cols = ['shipping_fee','shipping','shipping_cost','delivery_fee'];
foreach ($possible_cols as $col) {
  if (array_key_exists($col, $order) && $order[$col] !== null && $order[$col] !== '') {
    $shipping_fee = (float)$order[$col];
    break;
  }
}
/* Try JSON inside shipping_address */
if ($shipping_fee === null && !empty($order['shipping_address'])) {
  $decoded = json_decode((string)$order['shipping_address'], true);
  if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
    foreach (['shipping_fee','delivery_fee','shipping','shippingCost'] as $k) {
      if (isset($decoded[$k]) && is_numeric($decoded[$k])) { $shipping_fee = (float)$decoded[$k]; break; }
    }
  }
}
/* Fallback: derive from total */
$total_amount  = isset($order['total_amount']) ? (float)$order['total_amount'] : 0.0;
if ($shipping_fee === null) {
  $derived = $total_amount - $items_subtotal - $other_fee + $discount_amount + $voucher_discount;
  if (!is_finite($derived)) $derived = 0.0;
  $shipping_fee = round(max(0.0, $derived), 2);
}

$banner      = status_banner_text($order['status']);
$can_cancel  = (strtolower(trim($order['status'])) === 'pending');
[$recipient,$phone,$addr_full] = build_delivery_info($order);
$maps_query  = $addr_full !== '' ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($addr_full) : '';
$pay_text    = !empty($order['payment_method']) ? payment_text($order['payment_method']) : '';

$computed_total = $items_subtotal + $shipping_fee + $other_fee - $discount_amount - $voucher_discount;
$display_total  = $total_amount; // authoritative total from DB
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Details - FitFuel</title>
  <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-body text-slate-700">
  <div class="max-w-4xl mx-auto px-4 py-8">

    <!-- Header: Back + ORDER DETAILS -->
    <div class="flex items-center gap-3 mb-4">
      <a href="my_orders.php" class="inline-flex items-center text-emerald-600 hover:text-emerald-800">
        <i class="fa-solid fa-arrow-left mr-2"></i>
        <span></span>
      </a>
      <h1 class="text-2xl font-bold text-slate-900">Order Details</h1>
    </div>

    <!-- Status banner + Payment method -->
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 flex justify-between items-center">
      <div><i class="fa-solid fa-truck-fast mr-2"></i><span class="font-semibold"><?= htmlspecialchars($banner) ?></span></div>
      <?php if ($pay_text): ?>
        <div class="text-sm font-medium text-slate-700"><?= htmlspecialchars($pay_text) ?></div>
      <?php endif; ?>
    </div>

    <!-- Flash messages -->
    <?php if ($msg = get_flash('success')): ?>
      <div class="mb-4 p-4 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>
    <?php if ($msg = get_flash('error')): ?>
      <div class="mb-4 p-4 rounded-lg bg-red-50 text-red-700 border border-red-200">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border p-6">

      <!-- Order summary (Total Cost removed from here) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div>
          <p class="text-sm text-slate-500">Order ID</p>
          <p class="font-semibold text-slate-800"><?= htmlspecialchars($order['custom_order_id']) ?></p>
        </div>
        <div>
          <p class="text-sm text-slate-500">Status</p>
          <p class="font-semibold <?= $can_cancel ? 'text-yellow-700' : 'text-emerald-700' ?>">
            <?= htmlspecialchars(ucfirst($order['status'])) ?>
          </p>
        </div>
        <div>
          <p class="text-sm text-slate-500">Order Date</p>
          <p class="font-semibold text-slate-800">
            <?= htmlspecialchars($order['created_at']) ?>
          </p>
        </div>
      </div>

      <!-- Delivery Information -->
      <div class="mb-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-3">Delivery Information</h2>
        <div class="rounded-xl border bg-slate-50 p-4 space-y-2">
          <?php if ($recipient): ?>
            <p><span class="text-slate-500">Name:</span> <span class="font-medium"><?= htmlspecialchars($recipient) ?></span></p>
          <?php endif; ?>

          <?php if ($addr_full): ?>
            <p>
              <span class="text-slate-500">Address:</span>
              <?php if ($maps_query): ?>
                <a href="<?= htmlspecialchars($maps_query) ?>" target="_blank" class="font-medium underline hover:no-underline">
                  <?= htmlspecialchars($addr_full) ?>
                </a>
              <?php else: ?>
                <span class="font-medium"><?= htmlspecialchars($addr_full) ?></span>
              <?php endif; ?>
            </p>
          <?php endif; ?>

          <?php if ($phone): ?>
            <p><span class="text-slate-500">Contact:</span>
              <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $phone)) ?>" class="font-medium underline hover:no-underline">
                <?= htmlspecialchars($phone) ?>
              </a>
            </p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Items Purchased -->
      <h2 class="text-lg font-semibold text-slate-900 mb-3">Items Purchased</h2>
      <div class="space-y-4">
        <?php foreach ($items as $item): 
          $img = first_image($item['images'] ?? '') ?: 'img/Featured/1.png';
          $line_total = (float)$item['price'] * (int)$item['quantity'];
        ?>
          <div class="flex items-center border-b pb-4">
            <img src="<?= htmlspecialchars($img) ?>" class="w-16 h-16 rounded object-cover mr-4" alt="Product">
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-slate-800 line-clamp-1"><?= htmlspecialchars($item['name']) ?></h3>
              <p class="text-gray-600">Qty: <?= (int)$item['quantity'] ?></p>
            </div>
            <div class="text-right">
              <p class="font-semibold">₱<?= number_format($line_total, 2) ?></p>
              <p class="text-sm text-gray-500">₱<?= number_format((float)$item['price'], 2) ?> each</p>
            </div>
          </div>
        <?php endforeach; ?>

        <!-- Breakdown box (always shows Shipping Fee, even if 0.00) -->
        <div class="mt-4 rounded-xl border bg-slate-50 p-4 space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-slate-600">Items Subtotal</span>
            <span class="font-medium">₱<?= number_format($items_subtotal, 2) ?></span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-600">Shipping Fee</span>
            <span class="font-medium">₱<?= number_format($shipping_fee, 2) ?></span>
          </div>
          <?php if ($other_fee != 0): ?>
            <div class="flex justify-between text-sm">
              <span class="text-slate-600">Other Fee</span>
              <span class="font-medium">₱<?= number_format($other_fee, 2) ?></span>
            </div>
          <?php endif; ?>
          <?php if ($discount_amount != 0): ?>
            <div class="flex justify-between text-sm">
              <span class="text-slate-600">Discount</span>
              <span class="font-medium">-₱<?= number_format(abs($discount_amount), 2) ?></span>
            </div>
          <?php endif; ?>
          <?php if ($voucher_discount != 0): ?>
            <div class="flex justify-between text-sm">
              <span class="text-slate-600">Voucher</span>
              <span class="font-medium">-₱<?= number_format(abs($voucher_discount), 2) ?></span>
            </div>
          <?php endif; ?>

          <div class="flex justify-between items-center pt-3 mt-2 border-t">
            <span class="text-base font-semibold text-slate-800">Total Cost</span>
            <span class="text-lg font-bold text-emerald-600">₱<?= number_format($display_total, 2) ?></span>
          </div>

          <?php if (abs($computed_total - $display_total) > 0.01): ?>
            <p class="text-xs text-amber-700 mt-1">
              Note: Computed total (₱<?= number_format($computed_total, 2) ?>) differs from stored total. Check fees/discounts.
            </p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Bottom actions: ONLY Cancel -->
      <div class="mt-6 flex items-center justify-end">
        <?php if ($can_cancel): ?>
          <form method="post" onsubmit="return confirm('Cancel this order?');">
            <input type="hidden" name="action" value="cancel">
            <input type="hidden" name="order_pk" value="<?= (int)$order['order_id'] ?>">
            <button type="submit"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-red-300 text-red-700 hover:bg-red-50">
              <i class="fa-solid fa-ban"></i>
              Cancel Order
            </button>
          </form>
        <?php endif; ?>
      </div>

    </div>
  </div>
</body>
</html>
