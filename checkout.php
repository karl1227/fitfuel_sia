<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';
require_once 'config/currency_helper.php';
$user_id = (int) $_SESSION['user_id'];

/* ========== 2) Read & sanitize selected_items once ========== */
$selected_items = [];
if (isset($_GET['selected_items'])) {
    $selected_items = json_decode($_GET['selected_items'], true) ?: [];
    $selected_items = array_map('intval', $selected_items);
}
if (empty($selected_items)) {
    header('Location: cart.php');
    exit();
}

try {
    $pdo = getDBConnection();
    // (optional but recommended)
    if (method_exists($pdo, 'setAttribute')) {
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /* ========== 3) Load selected cart items ========== */
    $placeholders = str_repeat('?,', count($selected_items) - 1) . '?';
    $cart_sql = "SELECT c.cart_id, ci.cart_item_id, ci.product_id, ci.quantity, 
                        p.name, p.price, p.sale_percentage, p.images,
                        CASE 
                            WHEN p.sale_percentage > 0 THEN p.price * (1 - p.sale_percentage / 100)
                            ELSE p.price
                        END as final_price
                 FROM cart c 
                 LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id 
                 LEFT JOIN products p ON ci.product_id = p.product_id 
                 WHERE c.user_id = ? AND ci.cart_item_id IN ($placeholders)
                 ORDER BY ci.added_at DESC";
    $params = array_merge([$user_id], $selected_items);
    $cart_stmt = $pdo->prepare($cart_sql);
    $cart_stmt->execute($params);
    $cart_items = $cart_stmt->fetchAll();

    if (empty($cart_items)) {
        header('Location: cart.php');
        exit();
    }

    /* ========== 4) Load shipping address (selected or default) ========== */
    $selected_address_id = isset($_GET['address_id']) ? (int)$_GET['address_id'] : 0;
    if ($selected_address_id > 0) {
        $address_stmt = $pdo->prepare("SELECT * FROM shipping_addresses WHERE user_id = ? AND address_id = ? LIMIT 1");
        $address_stmt->execute([$user_id, $selected_address_id]);
        $default_address = $address_stmt->fetch();
    }
    if (empty($default_address)) {
        $address_sql = "SELECT * FROM shipping_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1";
        $address_stmt = $pdo->prepare($address_sql);
        $address_stmt->execute([$user_id]);
        $default_address = $address_stmt->fetch();
    }
    if (!$default_address) {
        $address_sql = "SELECT * FROM shipping_addresses WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
        $address_stmt = $pdo->prepare($address_sql);
        $address_stmt->execute([$user_id]);
        $default_address = $address_stmt->fetch();
    }

    /* ========== 5) Totals ========== */
    $subtotal = 0;
    $shipping_fee = 100.00;
    $total_items = 0;

    foreach ($cart_items as $item) {
        $subtotal += ((float)$item['final_price']) * ((int)$item['quantity']);
        $total_items += (int)$item['quantity'];
    }
    $total = $subtotal + $shipping_fee;

} catch (PDOException $e) {
    $cart_items = [];
    $subtotal = 0;
    $shipping_fee = 100.00;
    $total = 0;
    $total_items = 0;
    $default_address = null;
    $error = "Database error: " . $e->getMessage();
}

/* ========== 6) Cart count for header ========== */
$cart_count = 0;
try {
    $cart_count_sql = "SELECT COALESCE(SUM(ci.quantity), 0) as count 
                       FROM cart c 
                       LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id 
                       WHERE c.user_id = ?";
    $cart_count_stmt = $pdo->prepare($cart_count_sql);
    $cart_count_stmt->execute([$user_id]);
    $cart_count_result = $cart_count_stmt->fetch();
    $cart_count = (int) ($cart_count_result['count'] ?? 0);
} catch (PDOException $e) {
    $cart_count = 0;
}

/* ========== 7) Load Settings ========== */
$settings = [];
try {
    $settings_stmt = $pdo->query("SELECT key_name, value FROM settings");
    while ($row = $settings_stmt->fetch()) {
        $settings[$row['key_name']] = $row['value'];
    }
} catch (PDOException $e) {
    // If no settings, use defaults
}

function getSetting($key, $default = '0') {
    global $settings;
    return $settings[$key] ?? $default;
}

// Get enabled payment methods
$paypal_enabled = getSetting('paypal_enabled') === '1';
$cod_enabled = getSetting('cash_on_delivery_enabled') === '1';
$bank_transfer_enabled = getSetting('bank_transfer_enabled') === '1';

// Calculate tax if enabled (before discount)
$tax_enabled = getSetting('tax_enabled') === '1';
$tax_rate = floatval(getSetting('tax_rate', '0.12'));
$tax_amount = 0;

if ($tax_enabled) {
    // Tax is calculated on subtotal + shipping (before discount)
    $tax_amount = ($subtotal + $shipping_fee) * $tax_rate;
}
$total = $subtotal + $shipping_fee + $tax_amount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Checkout - FitFuel</title>
  <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/shop.css">
</head>
<body class="font-body bg-white text-slate-600">

  <!-- First Navigation Bar -->
  <nav class="bg-white text-black py-2">
    <div class="container mx-auto px-4">
      <div class="flex justify-end space-x-6 text-sm">
        <a href="testimonials.php" class="hover:text-emerald-400 transition-colors">Review</a>
        <a href="contact.php" class="hover:text-emerald-400 transition-colors">Help</a>
        <a href="profile.php" class="hover:text-emerald-400 transition-colors">Account</a>
        <a href="logout.php" class="hover:text-emerald-400 transition-colors">Logout</a>
      </div>
    </div>
  </nav>

  <!-- Second Navigation Bar -->
  <nav class="sticky-nav bg-black border-b border-white py-4">
    <div class="container mx-auto px-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <a href="index.php">
            <img src="img/LOGO-Fitfuel.png" width="75" height="auto" alt="LOGO">
          </a>
        </div>
        <div class="hidden md:flex items-center space-x-8">
          <a href="index.php" class="text-white hover:text-emerald-600 transition-colors">Home</a>
          <a href="shop.php" class="text-white hover:text-emerald-600 transition-colors">Shop</a>
          <a href="aboutus.php" class="text-white hover:text-emerald-600 transition-colors">About</a>
          <a href="contact.php" class="text-white hover:text-emerald-600 transition-colors">Contact</a>
        </div>
        <div class="flex items-center space-x-4">
          <a href="cart.php" class="relative p-2 text-white hover:text-emerald-600 transition-colors">
            <i class="fas fa-shopping-cart text-xl"></i>
            <?php if ($cart_count > 0): ?>
              <span class="absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                <?php echo $cart_count; ?>
              </span>
            <?php endif; ?>
          </a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">

      <!-- Breadcrumb -->
      <div class="mb-8">
        <nav class="flex" aria-label="Breadcrumb">
          <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
              <a href="index.php" class="text-gray-700 hover:text-emerald-600">
                <i class="fas fa-home mr-2"></i> Home
              </a>
            </li>
            <li>
              <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                <a href="cart.php" class="text-gray-700 hover:text-emerald-600">Cart</a>
              </div>
            </li>
            <li aria-current="page">
              <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                <span class="text-gray-500">Checkout</span>
              </div>
            </li>
          </ol>
        </nav>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left -->
        <div class="lg:col-span-2 space-y-6">

          <!-- Delivery Address -->
          <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xl font-semibold text-slate-800">Delivery Address</h2>
              <button type="button" onclick="openAddressPicker()" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors">
                <i class="fas fa-map-marker-alt mr-2"></i> Change Address
              </button>
            </div>

            <?php if ($default_address): ?>
              <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h3 class="font-semibold text-slate-800 mb-2">
                      <?php echo htmlspecialchars($default_address['full_name']); ?>
                    </h3>
                    <p class="text-gray-600 mb-1">
                      <?php echo htmlspecialchars($default_address['phone']); ?>
                    </p>
                    <p class="text-gray-600 mb-1">
                      <?php echo htmlspecialchars($default_address['address_line1']); ?>
                      <?php if (!empty($default_address['address_line2'])): ?>
                        <br><?php echo htmlspecialchars($default_address['address_line2']); ?>
                      <?php endif; ?>
                    </p>
                    <p class="text-gray-600">
                      <?php
                        // Prefer PSGC names/codes if present; fallback to legacy columns
                        $city  = $default_address['city_muni_name'] ?? $default_address['city'] ?? '';
                        $prov  = $default_address['province_name'] ?? $default_address['state'] ?? '';
                        $zip   = $default_address['postal_code'] ?? '';
                        echo htmlspecialchars($city) . ( $city && $prov ? ', ' : '' ) . htmlspecialchars($prov) . ' ' . htmlspecialchars($zip);
                      ?>
                    </p>
                  </div>
                  <?php 
                    $isDefault = (int)($default_address['is_default'] ?? 0) === 1;
                    $selId = isset($selected_address_id) ? (int)$selected_address_id : 0;
                    $addrId = (int)($default_address['address_id'] ?? 0);
                    if ($isDefault && ($selId === 0 || $selId === $addrId)) { ?>
                      <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded-full">Default</span>
                  <?php } elseif ($selId > 0 && $selId === $addrId && !$isDefault) { ?>
                      <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Selected</span>
                  <?php } ?>
                </div>
              </div>
            <?php else: ?>
              <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                <i class="fas fa-map-marker-alt text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500 mb-4">No delivery address found</p>
                <button onclick="openAddressModalCreate()" class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 transition-colors">
                  Add Address
                </button>
              </div>
            <?php endif; ?>
          </div>

          <!-- Payment Method -->
          <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Payment Method</h2>
            <div class="space-y-4">
              <?php if ($cod_enabled): ?>
              <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="radio" name="payment_method" value="cod" class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500" <?php echo (!$paypal_enabled && !$bank_transfer_enabled) ? 'checked' : ''; ?>>
                <div class="ml-3">
                  <div class="flex items-center">
                    <i class="fas fa-money-bill-wave text-green-600 text-xl mr-3"></i>
                    <span class="font-semibold text-slate-800">Cash on Delivery</span>
                  </div>
                  <p class="text-sm text-gray-600 mt-1">Pay when your order arrives</p>
                </div>
              </label>
              <?php endif; ?>

              <?php if ($paypal_enabled): ?>
              <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="radio" name="payment_method" value="paypal" class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500">
                <div class="ml-3">
                  <div class="flex items-center">
                    <i class="fab fa-paypal text-blue-600 text-xl mr-3"></i>
                    <span class="font-semibold text-slate-800">PayPal</span>
                  </div>
                  <p class="text-sm text-gray-600 mt-1">Pay securely with PayPal</p>
                </div>
              </label>
              <?php endif; ?>

              <?php if ($bank_transfer_enabled): ?>
              <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="radio" name="payment_method" value="bank_transfer" class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500">
                <div class="ml-3">
                  <div class="flex items-center">
                    <i class="fas fa-university text-indigo-600 text-xl mr-3"></i>
                    <span class="font-semibold text-slate-800">Bank Transfer</span>
                  </div>
                  <p class="text-sm text-gray-600 mt-1">Transfer directly to our bank account</p>
                </div>
              </label>
              <?php endif; ?>

              <?php if (!$cod_enabled && !$paypal_enabled && !$bank_transfer_enabled): ?>
              <div class="p-4 border border-red-200 rounded-lg bg-red-50">
                <p class="text-sm text-red-600 text-center">
                  <i class="fas fa-exclamation-triangle mr-2"></i>
                  No payment methods are currently enabled. Please contact support.
                </p>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Right: Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-lg p-6 sticky top-24">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Order Summary</h2>

            <div class="space-y-4 mb-6">
              <?php foreach ($cart_items as $item): ?>
                <?php
                $image_url = 'img/Featured/1.png';
                if (!empty($item['images'])) {
                    $images = json_decode($item['images'], true);
                    if (is_array($images) && !empty($images)) $image_url = $images[0];
                }
                ?>
                <div class="flex items-center space-x-3">
                  <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-12 h-12 object-cover rounded-lg">
                  <div class="flex-1">
                    <h3 class="font-semibold text-slate-800 text-sm"><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p class="text-gray-600 text-sm">Qty: <?php echo (int)$item['quantity']; ?></p>
                  </div>
                  <div class="text-right">
                    <?php if ($item['sale_percentage'] > 0): ?>
                      <p class="font-semibold text-red-600"><?php echo formatCurrency($item['final_price'] * $item['quantity']); ?></p>
                      <p class="text-xs text-gray-500 line-through"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></p>
                      <p class="text-xs text-gray-500"><?php echo formatCurrency($item['final_price']); ?> each</p>
                    <?php else: ?>
                      <p class="font-semibold text-emerald-600"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></p>
                      <p class="text-xs text-gray-500"><?php echo formatCurrency($item['price']); ?> each</p>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Promo -->
            <div class="mb-6">
              <label class="block text-sm font-semibold text-slate-800 mb-2">Promo Code</label>
              <div class="flex space-x-2">
                <input type="text" id="promo_code" placeholder="Enter promo code"
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <button onclick="applyPromoCode()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                  Apply
                </button>
              </div>
              <div id="promo_message" class="mt-2 text-sm"></div>
            </div>

            <!-- Totals -->
            <div class="border-t border-gray-200 pt-4 space-y-2">
              <div class="flex justify-between">
                <span class="text-gray-600">Subtotal (<?php echo (int)$total_items; ?> items)</span>
                <span class="font-semibold"><?php echo formatCurrency($subtotal); ?></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Shipping</span>
                <span class="font-semibold"><?php echo formatCurrency($shipping_fee); ?></span>
              </div>
              <?php if ($tax_enabled && $tax_amount > 0): ?>
              <div class="flex justify-between">
                <span class="text-gray-600">Tax (<?php echo number_format($tax_rate * 100, 0); ?>%)</span>
                <span class="font-semibold"><?php echo formatCurrency($tax_amount); ?></span>
              </div>
              <?php endif; ?>
              <div id="promo_discount" class="flex justify-between text-emerald-600 hidden">
                <span>Discount</span>
                <span class="font-semibold">-<span id="discount_amount"><?php echo getCurrencySymbol(); ?>0.00</span></span>
              </div>
              <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
                <span>Total</span>
                <span class="text-emerald-600"><span id="total_amount"><?php echo formatCurrency($total); ?></span></span>
              </div>
            </div>

            <button onclick="processCheckout()" class="w-full bg-emerald-600 text-white py-3 rounded-lg font-semibold hover:bg-emerald-700 transition-colors mt-6">
              <i class="fas fa-lock mr-2"></i> Complete Order
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Address Picker Modal (list + actions) -->
  <div id="addressPicker" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-slate-800">Select a delivery address</h3>
            <button onclick="closeAddressPicker()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
          </div>
          <div id="addressList" class="space-y-3">
            <div class="text-gray-500">Loading addresses…</div>
          </div>
          <div class="mt-4 flex justify-end gap-2">
            <button onclick="openAddressModalCreate()" id="addAddressBtn" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Add New Address</button>
            <button onclick="closeAddressPicker()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Address Modal (Add/Edit) -->
  <div id="addressModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 id="addressModalTitle" class="text-xl font-semibold text-slate-800">Edit Delivery Address</h3>
            <button onclick="closeAddressModal()" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <form id="addressForm" onsubmit="saveAddress(event)">
            <input type="hidden" id="address_id_hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Full Name *</label>
                <input type="text" name="full_name" required autocomplete="name"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                       value="<?php echo htmlspecialchars($default_address['full_name'] ?? ''); ?>">
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Phone Number *</label>
                <input type="tel" name="phone" required autocomplete="tel"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                       value="<?php echo htmlspecialchars($default_address['phone'] ?? ''); ?>">
              </div>
            </div>

            <!-- PSGC selects -->
            <div class="mb-4">
              <label class="block text-sm font-semibold text-slate-800 mb-2">Region *</label>
              <select id="region" name="region" required class="w-full px-3 py-2 border rounded-lg"></select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
              <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Province *</label>
                <select id="province" name="province" required class="w-full px-3 py-2 border rounded-lg"></select>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">City/Municipality *</label>
                <select id="city" name="city" required class="w-full px-3 py-2 border rounded-lg"></select>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Barangay *</label>
                <select id="barangay" name="barangay" required class="w-full px-3 py-2 border rounded-lg"></select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Postal Code *</label>
                <input type="text" name="postal_code" required autocomplete="postal-code"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                       value="<?php echo htmlspecialchars($default_address['postal_code'] ?? ''); ?>">
              </div>
            </div>

            <div class="mb-6">
              <label class="block text-sm font-semibold text-slate-800 mb-2">Street Name, Building, House No. *</label>
              <input type="text" name="street_address" required autocomplete="address-line1"
                     class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                     value="<?php echo htmlspecialchars($default_address['address_line1'] ?? ''); ?>">
            </div>

            <div class="flex items-center space-x-2 mb-6">
              <input type="checkbox" id="is_default_checkbox" name="is_default" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
              <label for="is_default_checkbox" class="text-sm text-slate-700">Set as default address</label>
            </div>

            <div class="flex justify-end space-x-3">
              <button type="button" onclick="closeAddressModal()"
                      class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
              <button type="submit"
                      class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">Save Address</button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>

  <!-- ===== Page JS (at the end) ===== -->
  <script>
let editingAddress = null; // holds current address being edited from picker

function renderAddressList(addresses){
  const wrap = document.getElementById('addressList');
  if(!addresses || addresses.length===0){
    wrap.innerHTML = `<div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
      <i class=\"fas fa-map-marker-alt text-gray-400 text-4xl mb-3\"></i>
      <p class=\"text-gray-500\">No saved addresses yet</p></div>`;
    const addBtn = document.getElementById('addAddressBtn');
    addBtn.disabled = false; addBtn.classList.remove('hidden');
    return;
  }
  const addBtn = document.getElementById('addAddressBtn');
  const reachedMax = addresses.length >= 3;
  addBtn.disabled = reachedMax;
  if (reachedMax) addBtn.classList.add('hidden'); else addBtn.classList.remove('hidden');
  wrap.innerHTML = addresses.map(a=>`
    <div class=\"border border-gray-200 rounded-lg p-4\">
      <div class=\"flex items-start justify-between\">
        <div class=\"flex-1\">
          <div class=\"flex items-center gap-2\">
            <h4 class=\"font-semibold text-slate-800\">${a.full_name||''}</h4>
            ${a.is_default==1?'<span class=\"bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded-full\">Default</span>':''}
          </div>
          <div class=\"text-sm text-gray-600 mt-1\">${a.phone||''}</div>
          <div class=\"text-sm text-gray-600\">${a.address_line1||''}${a.address_line2?(', '+a.address_line2):''}${a.address_line3?(', '+a.address_line3):''}</div>
          <div class=\"text-sm text-gray-600\">${(a.city||'')}${a.state?(', '+a.state):''} ${a.postal_code||''}</div>
        </div>
        <div class=\"flex items-center gap-2\">
          <button onclick=\"useAddress(${a.address_id})\" class=\"px-3 py-1 bg-emerald-600 text-white rounded hover:bg-emerald-700\">Use</button>
          <button onclick=\"editAddressFromPicker(${a.address_id})\" class=\"px-3 py-1 border rounded hover:bg-gray-50\">Edit</button>
          <button onclick=\"deleteAddress(${a.address_id})\" class=\"px-3 py-1 border rounded text-red-700 border-red-600 hover:bg-red-50\">Delete</button>
        </div>
      </div>
    </div>
  `).join('');
}

function openAddressPicker(){
  const modal = document.getElementById('addressPicker');
  if (!modal) {
    console.error('Address picker modal not found');
    return;
  }
  modal.classList.remove('hidden');
  document.body.style.overflow='hidden';
  fetch('get_addresses.php').then(r=>r.json()).then(d=>{
    if(d.success){ renderAddressList(d.addresses||[]); } else { document.getElementById('addressList').innerHTML = `<div class='text-red-600'>${d.message||'Failed to load addresses'}</div>`; }
  }).catch(()=>{ document.getElementById('addressList').innerHTML = `<div class='text-red-600'>Network error</div>`; });
}
function closeAddressPicker(){ document.getElementById('addressPicker').classList.add('hidden'); document.body.style.overflow=''; }
function openAddressModalCreate(){ 
  editingAddress=null; 
  document.getElementById('addressModalTitle').textContent='Add Address'; 
  document.getElementById('address_id_hidden').value='';
  document.getElementById('is_default_checkbox').checked=false; // New addresses not default by default
  openAddressModal(); 
}
function editAddressFromPicker(addressId){
  fetch('get_addresses.php').then(r=>r.json()).then(d=>{
    if(!d.success) return alert(d.message||'Error fetching addresses');
    const a=(d.addresses||[]).find(x=>x.address_id==addressId); if(!a) return;
    editingAddress=a; document.getElementById('addressModalTitle').textContent='Edit Address';
    document.querySelector('#addressForm [name="full_name"]').value=a.full_name||'';
    document.querySelector('#addressForm [name="phone"]').value=a.phone||'';
    document.querySelector('#addressForm [name="postal_code"]').value=a.postal_code||'';
    document.querySelector('#addressForm [name="street_address"]').value=a.address_line1||'';
    document.getElementById('is_default_checkbox').checked=(a.is_default==1);
    document.getElementById('address_id_hidden').value=a.address_id;
    openAddressModal();
    // PSGC preselect if you have codes stored
  });
}
function useAddress(addressId){
  // Use for this checkout only: redirect with address_id param, keep selected_items
  const url = new URL(window.location.href);
  url.searchParams.set('address_id', addressId);
  // keep selected_items if present
  window.location.href = url.toString();
}

function deleteAddress(addressId){
  if (!confirm('Delete this address? This action cannot be undone.')) return;
  fetch('delete_address.php',{
    method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({address_id:addressId})
  }).then(r=>r.json()).then(d=>{
    if(d.success){
      // Refresh the list in picker
      fetch('get_addresses.php').then(r=>r.json()).then(res=>{
        if(res.success){ renderAddressList(res.addresses||[]); }
        else { alert(res.message||'Failed to refresh addresses'); }
      }).catch(()=> alert('Network error while refreshing addresses'));
    } else {
      alert(d.message||'Error deleting address');
    }
  }).catch(()=> alert('Network error while deleting address'));
}
/* ===== PSGC address loader (handles NCR & district-based cities) ===== */
const PSGC = "https://psgc.gitlab.io/api";

function setOptions(el, items, { placeholder = "Select...", value = "code", label = "name" } = {}) {
  el.innerHTML = "";
  const opt = document.createElement("option");
  opt.value = ""; opt.textContent = placeholder;
  el.appendChild(opt);
  (items || []).forEach(it => {
    const o = document.createElement("option");
    o.value = it[value]; o.textContent = it[label];
    el.appendChild(o);
  });
  el.disabled = false;
}
function setNA(el, text = "Not applicable") {
  el.innerHTML = `<option value="">${text}</option>`;
  el.disabled = true;
}
async function jget(url, fallback = []) {
  try {
    const r = await fetch(url);
    if (!r.ok) throw new Error("HTTP " + r.status);
    return await r.json();
  } catch (e) {
    console.error("[PSGC]", url, e);
    return fallback;
  }
}
async function loadRegions() {
  const regions = await jget(`${PSGC}/regions/`);
  setOptions(document.getElementById("region"), regions, { placeholder: "Select Region" });
}
async function loadProvincesOrCities(regionCode) {
  const provSel = document.getElementById("province");
  const citySel = document.getElementById("city");
  const brgySel = document.getElementById("barangay");
  provSel.disabled = citySel.disabled = brgySel.disabled = true;
  provSel.innerHTML = `<option value="">Loading…</option>`;
  citySel.innerHTML = `<option value="">Select City/Municipality</option>`;
  brgySel.innerHTML = `<option value="">Select Barangay</option>`;

  const provs = await jget(`${PSGC}/regions/${regionCode}/provinces/`, []);
  if (provs.length > 0) {
    setOptions(provSel, provs, { placeholder: "Select Province" });
  } else {
    // NCR-like regions without provinces: load cities/municipalities from region
    setNA(provSel, "Not applicable");
    const [cities, munis] = await Promise.all([
      jget(`${PSGC}/regions/${regionCode}/cities/`, []),
      jget(`${PSGC}/regions/${regionCode}/municipalities/`, []),
    ]);
    const merged = [...cities, ...munis];
    if (merged.length > 0) {
      setOptions(citySel, merged, { placeholder: "Select City/Municipality" });
      citySel.disabled = false;
    } else {
      setOptions(citySel, [], { placeholder: "No cities/municipalities found" });
      citySel.disabled = true;
    }
  }
}
async function loadCitiesFromProvince(provCode) {
  const citySel = document.getElementById("city");
  const brgySel = document.getElementById("barangay");
  citySel.disabled = brgySel.disabled = true;
  citySel.innerHTML = `<option value="">Loading…</option>`;
  brgySel.innerHTML = `<option value="">Select Barangay</option>`;

  const [cities, munis] = await Promise.all([
    jget(`${PSGC}/provinces/${provCode}/cities/`, []),
    jget(`${PSGC}/provinces/${provCode}/municipalities/`, []),
  ]);
  const merged = [...cities, ...munis];
  if (merged.length > 0) {
    setOptions(citySel, merged, { placeholder: "Select City/Municipality" });
  } else {
    setOptions(citySel, [], { placeholder: "No cities/municipalities found" });
    citySel.disabled = true;
  }
}
async function loadBarangays(cityOrMuniCode) {
  const brgySel = document.getElementById("barangay");
  brgySel.disabled = true;
  brgySel.innerHTML = `<option value="">Loading…</option>`;

  // Try City → then Municipality → then Districts (e.g., Manila)
  let brgys = await jget(`${PSGC}/cities/${cityOrMuniCode}/barangays/`, []);
  if (brgys.length === 0) brgys = await jget(`${PSGC}/municipalities/${cityOrMuniCode}/barangays/`, []);
  if (brgys.length === 0) {
    const districts = await jget(`${PSGC}/cities/${cityOrMuniCode}/districts/`, []);
    if (districts.length > 0) {
      const perDistrict = await Promise.all(
        districts.map(d => jget(`${PSGC}/districts/${d.code}/barangays/`, []))
      );
      brgys = perDistrict.flat();
    }
  }

  if (brgys.length > 0) {
    setOptions(brgySel, brgys, { placeholder: "Select Barangay" });
  } else {
    setOptions(brgySel, [], { placeholder: "No barangays found" });
    brgySel.disabled = true;
  }
}

/* ===== Modal + actions ===== */
function openAddressModal(){ 
  document.getElementById('addressModal').classList.remove('hidden'); 
  document.body.style.overflow='hidden';
  // Ensure checkbox is unchecked if no address_id (creating new address)
  if (!document.getElementById('address_id_hidden').value) {
    document.getElementById('is_default_checkbox').checked = false;
  }
}
function closeAddressModal(){ 
  document.getElementById('addressModal').classList.add('hidden'); 
  document.body.style.overflow=''; 
}
function saveAddress(event) {
  event.preventDefault();
  const pick = (id) => {
    const el = document.getElementById(id);
    return { code: el.value, name: el.options[el.selectedIndex]?.text || "" };
  };
  const region   = pick('region');
  const province = pick('province');
  const city     = pick('city');
  const barangay = pick('barangay');

  const formData = new FormData(event.target);
  const addressData = {
    full_name: formData.get('full_name'),
    phone: formData.get('phone'),
    postal_code: formData.get('postal_code'),
    street_address: formData.get('street_address'),
    is_default: formData.get('is_default') ? 1 : 0,
    region_name: region.name,   region_code: region.code,
    province_name: province.name, province_code: province.code,
    city_muni_name: city.name,  city_muni_code: city.code,
    barangay_name: barangay.name, barangay_code: barangay.code
  };
  const editId = document.getElementById('address_id_hidden').value;
  if (editId) addressData.address_id = editId;

  fetch('save_address.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(addressData)
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) {
      closeAddressModal();
      // If new address was created, redirect with address_id to auto-select it
      // If editing and it's the selected address, keep it selected
      const url = new URL(window.location.href);
      if (d.address_id) {
        url.searchParams.set('address_id', d.address_id);
      }
      // Preserve selected_items if present (it's already in URL, so it stays)
      // Just ensure it's properly encoded if needed
      window.location.href = url.toString();
    } else {
      alert('Error saving address: ' + (d.message || 'Unknown error'));
    }
  })
  .catch(() => { alert('Error saving address'); });
}
function applyPromoCode(){
  const code = document.getElementById('promo_code').value.trim();
  const msg = document.getElementById('promo_message');
  if(!code){ msg.textContent='Please enter a promo code'; msg.className='mt-2 text-sm text-red-600'; return; }
  fetch('apply_promo_code.php',{
    method:'POST', headers:{'Content-Type':'application/json'},
    body:JSON.stringify({ promo_code: code })
  })
  .then(r=>r.json())
  .then(d=>{
    if(d.success){
      msg.textContent=d.message; msg.className='mt-2 text-sm text-green-600';
      document.getElementById('promo_discount').classList.remove('hidden');
      document.getElementById('discount_amount').textContent=d.discount_amount;
      const currentTotal=parseFloat(document.getElementById('total_amount').textContent.replace(/,/g,''));
      document.getElementById('total_amount').textContent=(currentTotal - parseFloat(d.discount_amount)).toFixed(2);
    }else{
      msg.textContent=d.message||'Invalid promo code';
      msg.className='mt-2 text-sm text-red-600';
    }
  })
  .catch(()=>{ msg.textContent='Error applying promo code'; msg.className='mt-2 text-sm text-red-600'; });
}
function processCheckout(){
  const method=document.querySelector('input[name="payment_method"]:checked')?.value;
  if(!method){ alert('Please select a payment method'); return; }
  <?php if (!$default_address): ?>
    alert('Please add a delivery address before proceeding');
    return;
  <?php endif; ?>
  if(!confirm('Are you sure you want to complete this order?')) return;

  fetch('process_checkout.php',{
    method:'POST', headers:{'Content-Type':'application/json'},
    body:JSON.stringify({
      payment_method: method,
      promo_code: document.getElementById('promo_code').value.trim(),
      selected_items: <?php echo json_encode($selected_items); ?>,
      selected_address_id: <?php echo isset($_GET['address_id']) ? (int)$_GET['address_id'] : 0; ?>
    })
  })
  .then(r=>r.json())
  .then(d=>{
    if(d.success){
      window.location.href = method==='paypal' ? d.paypal_url
                         : 'order_success.php?order_id='+(d.custom_order_id||d.order_id);
    }else{
      alert('Error processing order: ' + (d.message||'Unknown error'));
    }
  })
  .catch(()=>alert('Error processing order'));
}

/* ===== Init PSGC + optional preselect from saved address ===== */
document.addEventListener("DOMContentLoaded", async () => {
  const regionSel = document.getElementById("region");
  const provSel   = document.getElementById("province");
  const citySel   = document.getElementById("city");
  const brgySel   = document.getElementById("barangay");

  if (!regionSel || !provSel || !citySel || !brgySel) {
    console.error("[PSGC] Missing selects (region/province/city/barangay).");
    return;
  }

  // Initial placeholders
  setOptions(regionSel, [], { placeholder: "Loading Regions…" });
  setOptions(provSel,   [], { placeholder: "Select Province" });
  setOptions(citySel,   [], { placeholder: "Select City/Municipality" });
  setOptions(brgySel,   [], { placeholder: "Select Barangay" });
  provSel.disabled = citySel.disabled = brgySel.disabled = true;

  await loadRegions();

  // Bind change events
  regionSel.addEventListener("change", (e) => {
    const regionCode = e.target.value;
    if (!regionCode) {
      setOptions(provSel, [], { placeholder: "Select Province" }); provSel.disabled = true;
      setOptions(citySel, [], { placeholder: "Select City/Municipality" }); citySel.disabled = true;
      setOptions(brgySel, [], { placeholder: "Select Barangay" }); brgySel.disabled = true;
      return;
    }
    loadProvincesOrCities(regionCode);
  });
  provSel.addEventListener("change", (e) => {
    const provCode = e.target.value;
    if (!provCode) {
      setOptions(citySel, [], { placeholder: "Select City/Municipality" }); citySel.disabled = true;
      setOptions(brgySel, [], { placeholder: "Select Barangay" }); brgySel.disabled = true;
      return;
    }
    loadCitiesFromProvince(provCode);
  });
  citySel.addEventListener("change", (e) => {
    const code = e.target.value;
    if (!code) {
      setOptions(brgySel, [], { placeholder: "Select Barangay" }); brgySel.disabled = true;
      return;
    }
    loadBarangays(code);
  });

  // Preselect from saved address (if any)
  const pre = {
    region:   "<?php echo htmlspecialchars($default_address['region_code']   ?? ''); ?>",
    province: "<?php echo htmlspecialchars($default_address['province_code'] ?? ''); ?>",
    city:     "<?php echo htmlspecialchars($default_address['city_muni_code']?? ''); ?>",
    barangay: "<?php echo htmlspecialchars($default_address['barangay_code'] ?? ''); ?>",
  };

  if (pre.region) {
    regionSel.value = pre.region;
    await loadProvincesOrCities(pre.region);
  }
  if (pre.province && !provSel.disabled) {
    provSel.value = pre.province;
    await loadCitiesFromProvince(pre.province);
  }
  if (pre.city && !citySel.disabled) {
    citySel.value = pre.city;
    await loadBarangays(pre.city);
  }
  if (pre.barangay && !brgySel.disabled) {
    brgySel.value = pre.barangay;
  }
  });
  </script>
  <?php include 'includes/footer.php'; ?>
