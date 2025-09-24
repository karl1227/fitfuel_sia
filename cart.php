<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';
$user_id = (int)$_SESSION['user_id'];

$cart_items = [];
$subtotal = 0.0;
$shipping = 100.00; // Fixed shipping
$total = 0.0;
$total_items = 0;
$error = '';

try {
    $pdo = getDBConnection();

    // Get user's cart and items
    $cart_sql = "SELECT c.cart_id, ci.cart_item_id, ci.product_id, ci.quantity, 
                        p.name, p.price, p.sale_percentage, p.images,
                        CASE 
                            WHEN p.sale_percentage > 0 THEN p.price * (1 - p.sale_percentage / 100)
                            ELSE p.price
                        END AS final_price
                 FROM cart c
                 LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id
                 LEFT JOIN products p   ON ci.product_id = p.product_id
                 WHERE c.user_id = ? AND ci.cart_item_id IS NOT NULL
                 ORDER BY ci.added_at DESC";

    $cart_stmt = $pdo->prepare($cart_sql);
    $cart_stmt->execute([$user_id]);
    $cart_items = $cart_stmt->fetchAll();

    foreach ($cart_items as $item) {
        $subtotal += (float)$item['final_price'] * (int)$item['quantity'];
        $total_items += (int)$item['quantity'];
    }
    $total = $subtotal + $shipping;

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    // fallbacks already set above
}

// Header cart count badge
$cart_count = 0;
try {
    if (!isset($pdo)) { $pdo = getDBConnection(); }
    $cart_count_sql = "SELECT COALESCE(SUM(ci.quantity), 0) AS count
                       FROM cart c
                       LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id
                       WHERE c.user_id = ?";
    $cart_count_stmt = $pdo->prepare($cart_count_sql);
    $cart_count_stmt->execute([$user_id]);
    $cart_count = (int)($cart_count_stmt->fetch()['count'] ?? 0);
} catch (Throwable $e) {
    $cart_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cart - FitFuel</title>
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
        <a href="#" class="hover:text-emerald-400 transition-colors">Review</a>
        <a href="#" class="hover:text-emerald-400 transition-colors">Help</a>
        <a href="profile.php" class="hover:text-emerald-400 transition-colors">Account</a>
        <a href="logout.php" class="hover:text-emerald-400 transition-colors">Logout</a>
      </div>
    </div>
  </nav>

  <!-- Second Navigation Bar -->
  <nav class="sticky-nav bg-black border-b border-white py-4">
    <div class="container mx-auto px-4">
      <div class="flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center">
          <a href="index.php">
            <img src="img/LOGO-Fitfuel.png" width="75" height="auto" alt="LOGO">
          </a>
        </div>

        <!-- Navigation Links -->
        <div class="hidden md:flex space-x-8">
          <a href="shop.php" class="font-medium text-white hover:text-emerald-600 transition-colors">Shop</a>
          <a href="shop.php" class="font-medium text-white hover:text-emerald-600 transition-colors">Categories</a>
          <a href="#" class="font-medium text-white hover:text-emerald-600 transition-colors">About</a>
        </div>

        <!-- Search and Icons -->
        <div class="flex items-center space-x-4">
          <!-- Search Bar -->
          <div class="relative hidden md:block">
            <input type="text" placeholder="Search products..."
                   class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
          </div>

          <!-- Bell -->
          <a href="#" class="relative p-2 text-white hover:text-emerald-600 transition-colors">
            <i class="fas fa-bell text-xl"></i>
          </a>

          <!-- Cart -->
          <a href="cart.php" class="relative p-2 text-white hover:text-emerald-600 transition-colors">
            <i class="fas fa-shopping-cart text-xl"></i>
            <?php if ($cart_count > 0): ?>
              <span class="absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 min-w-[20px] px-1 flex items-center justify-center">
                <?php echo $cart_count; ?>
              </span>
            <?php endif; ?>
          </a>

          <!-- Profile (CLICKABLE) -->
          <a href="profile.php" class="p-2 text-white hover:text-emerald-600 transition-colors" title="My Profile">
            <i class="fas fa-user text-xl"></i>
          </a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Breadcrumbs -->
  <div class="bg-gray-50 py-3">
    <div class="container mx-auto px-4">
      <nav class="text-sm">
        <a href="index.php" class="text-gray-500 hover:text-emerald-600">Home</a>
        <span class="mx-2 text-gray-400">></span>
        <a href="shop.php" class="text-gray-500 hover:text-emerald-600">Shop</a>
        <span class="mx-2 text-gray-400">></span>
        <span class="text-gray-700 font-medium">Cart</span>
      </nav>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container mx-auto px-4 py-8">
    <div class="mb-8">
      <h1 class="text-4xl font-bold text-slate-800 mb-2">Your Cart</h1>
      <p class="text-lg text-slate-600">Review your items and proceed to checkout</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="mb-6 rounded bg-red-50 text-red-700 px-4 py-3 border border-red-200">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i><?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
      <!-- Empty Cart -->
      <div class="text-center py-16">
        <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-2xl font-semibold text-slate-600 mb-2">Your cart is empty</h3>
        <p class="text-slate-500 mb-6">Add some products to get started</p>
        <a href="shop.php" class="bg-emerald-600 text-white px-8 py-3 rounded-lg hover:bg-emerald-700 transition-colors">
          Continue Shopping
        </a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Cart Items -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center space-x-3">
                <input type="checkbox" id="select-all" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                <h2 class="text-xl font-semibold text-slate-800">Items in your cart</h2>
              </div>
              <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500"><?php echo (int)$total_items; ?> items</span>
                <button onclick="removeSelected()" id="remove-selected-btn" class="text-red-500 hover:text-red-700 text-sm font-medium flex items-center hidden">
                  <i class="fas fa-trash mr-1"></i>
                  Remove Selected
                </button>
              </div>
            </div>

            <div class="space-y-4">
              <?php foreach ($cart_items as $item): ?>
                <?php
                  $image_url = 'img/Featured/1.png'; // default
                  if (!empty($item['images'])) {
                    $decoded = json_decode($item['images'], true);
                    if (is_array($decoded) && !empty($decoded)) {
                      $image_url = $decoded[0];
                    }
                  }
                ?>
                <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                  <!-- Checkbox -->
                  <input type="checkbox"
                         class="item-checkbox w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                         data-item-id="<?php echo (int)$item['cart_item_id']; ?>"
                         data-price="<?php echo htmlspecialchars($item['final_price']); ?>">

                  <!-- Image -->
                  <img src="<?php echo htmlspecialchars($image_url); ?>"
                       alt="<?php echo htmlspecialchars($item['name']); ?>"
                       class="w-16 h-16 object-cover rounded-lg">

                  <!-- Info -->
                  <div class="flex-1">
                    <h3 class="font-semibold text-slate-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                    <?php if ((float)$item['sale_percentage'] > 0): ?>
                      <div class="flex flex-col">
                        <span class="text-red-600 font-semibold">₱<?php echo number_format($item['final_price'], 2); ?></span>
                        <span class="text-gray-500 line-through text-sm">₱<?php echo number_format($item['price'], 2); ?></span>
                      </div>
                    <?php else: ?>
                      <p class="text-emerald-600 font-semibold">₱<?php echo number_format($item['price'], 2); ?></p>
                    <?php endif; ?>
                  </div>

                  <!-- Quantity -->
                  <div class="flex items-center space-x-2">
                    <button onclick="updateQuantity(<?php echo (int)$item['cart_item_id']; ?>, -1)"
                            class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">
                      <i class="fas fa-minus text-xs"></i>
                    </button>
                    <span class="quantity-display w-8 text-center font-semibold"
                          data-item-id="<?php echo (int)$item['cart_item_id']; ?>"><?php echo (int)$item['quantity']; ?></span>
                    <button onclick="updateQuantity(<?php echo (int)$item['cart_item_id']; ?>, 1)"
                            class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">
                      <i class="fas fa-plus text-xs"></i>
                    </button>
                  </div>

                  <!-- Remove -->
                  <button onclick="removeItem(<?php echo (int)$item['cart_item_id']; ?>)"
                          class="text-red-500 hover:text-red-700 p-2">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Continue Shopping -->
            <div class="mt-6 pt-6 border-t border-gray-200">
              <a href="shop.php" class="inline-flex items-center text-emerald-600 hover:text-emerald-700 font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>
                Continue Shopping
              </a>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-lg p-6 sticky top-24">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Order Summary</h2>

            <!-- Promo -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Promo Code</label>
              <div class="flex">
                <input type="text" id="promo-code" placeholder="Enter promo code"
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <button onclick="applyPromoCode()"
                        class="px-4 py-2 bg-gray-800 text-white rounded-r-lg hover:bg-gray-700 transition-colors">
                  Apply
                </button>
              </div>
              <div id="promo-message" class="mt-2 text-sm hidden"></div>
            </div>

            <!-- Totals -->
            <div class="space-y-3 mb-6">
              <div class="flex justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-semibold" id="subtotal">₱<?php echo number_format($subtotal, 2); ?></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Shipping</span>
                <span class="font-semibold" id="shipping">₱<?php echo number_format($shipping, 2); ?></span>
              </div>
              <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-3">
                <span>Total</span>
                <span id="total">₱<?php echo number_format($total, 2); ?></span>
              </div>
            </div>

            <button onclick="proceedToCheckout()"
                    class="w-full bg-black text-white py-3 rounded-lg font-semibold hover:bg-gray-800 transition-colors mb-4">
              Checkout
            </button>

            <div class="flex items-center justify-center text-sm text-gray-500">
              <i class="fas fa-lock mr-2"></i>
              <span>Secure checkout with SSL encryption</span>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Footer -->
  <footer class="bg-slate-800 text-white py-12">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
          <h3 class="font-heading text-2xl font-bold text-White-400 mb-4">FitFuel</h3>
          <p class="text-slate-300 mb-4">
            Your ultimate destination for premium fitness equipment, supplements, and accessories.
          </p>
          <div class="flex space-x-4">
            <a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors"><i class="fab fa-facebook text-xl"></i></a>
            <a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors"><i class="fab fa-instagram text-xl"></i></a>
            <a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors"><i class="fab fa-twitter text-xl"></i></a>
            <a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors"><i class="fab fa-youtube text-xl"></i></a>
          </div>
        </div>

        <div>
          <h4 class="font-semibold text-lg mb-4">Quick Links</h4>
          <ul class="space-y-2">
            <li><a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors">About Us</a></li>
            <li><a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors">Contact</a></li>
            <li><a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors">Blog</a></li>
            <li><a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors">FAQs</a></li>
          </ul>
        </div>

        <div>
          <h4 class="font-semibold text-lg mb-4">Categories</h4>
          <ul class="space-y-2">
            <li><a href="shop.php?category=2" class="text-slate-300 hover:text-emerald-400 transition-colors">Gym Equipment</a></li>
            <li><a href="shop.php?category=3" class="text-slate-300 hover:text-emerald-400 transition-colors">Supplements</a></li>
            <li><a href="shop.php?category=1" class="text-slate-300 hover:text-emerald-400 transition-colors">Accessories</a></li>
            <li><a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors">Apparel</a></li>
          </ul>
        </div>

        <div>
          <h4 class="font-semibold text-lg mb-4">Customer Service</h4>
          <ul class="space-y-2">
            <li><a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors">Shipping Info</a></li>
            <li><a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors">Returns</a></li>
            <li><a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors">Size Guide</a></li>
            <li><a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors">Track Order</a></li>
          </ul>
        </div>
      </div>

      <div class="border-t border-slate-700 mt-8 pt-8 text-center">
        <p class="text-slate-300">
          &copy; 2024 FitFuel. All rights reserved. | Privacy Policy | Terms of Service
        </p>
      </div>
    </div>
  </footer>

  <script>
    // Update quantity
    function updateQuantity(cartItemId, change) {
      const quantityDisplay = document.querySelector(`[data-item-id="${cartItemId}"].quantity-display`);
      const currentQuantity = parseInt(quantityDisplay.textContent);
      const newQuantity = currentQuantity + change;
      if (newQuantity < 1) { removeItem(cartItemId); return; }

      quantityDisplay.textContent = newQuantity;
      updateTotals();
      updateCartCounter();

      fetch('update_cart_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cart_item_id: cartItemId, quantity: newQuantity })
      })
      .then(r => r.json())
      .then(d => { if (!d.success) window.location.reload(); })
      .catch(() => window.location.reload());
    }

    // Header counter
    function updateCartCounter() {
      let totalItems = 0;
      document.querySelectorAll('.quantity-display').forEach(el => totalItems += parseInt(el.textContent));
      const badge = document.querySelector('a[href="cart.php"] .bg-emerald-500');
      if (badge) {
        if (totalItems > 0) { badge.textContent = totalItems; badge.style.display = 'flex'; }
        else { badge.style.display = 'none'; }
      }
      const countSpan = document.querySelector('.text-sm.text-gray-500');
      if (countSpan) countSpan.textContent = `${totalItems} items`;
    }

    // Remove single
    function removeItem(cartItemId) {
      if (!confirm('Are you sure you want to remove this item from your cart?')) return;
      const checkbox = document.querySelector(`[data-item-id="${cartItemId}"].item-checkbox`);
      if (!checkbox) return;
      const row = checkbox.closest('.flex');
      row.remove();
      updateTotals(); updateSelectAll();
      const countSpan = document.querySelector('.text-sm.text-gray-500');
      if (countSpan) countSpan.textContent = `${document.querySelectorAll('.item-checkbox').length} items`;

      fetch('remove_cart_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cart_item_id: cartItemId })
      })
      .then(r => r.json())
      .then(d => { if (!d.success) window.location.reload(); if (document.querySelectorAll('.item-checkbox').length===0) window.location.reload(); })
      .catch(() => window.location.reload());
    }

    // Totals for selected
    function updateTotals() {
      let subtotal = 0;
      let totalItems = 0;
      document.querySelectorAll('.item-checkbox:checked').forEach(cb => {
        const price = parseFloat(cb.dataset.price);
        const qEl = document.querySelector(`[data-item-id="${cb.dataset.itemId}"].quantity-display`);
        const qty = parseInt(qEl.textContent);
        subtotal += price * qty; totalItems += qty;
      });
      const shipping = 100.00;
      const total = subtotal + shipping;
      document.getElementById('subtotal').textContent = '₱' + subtotal.toFixed(2);
      document.getElementById('total').textContent = '₱' + total.toFixed(2);
    }

    // Promo
    function applyPromoCode() {
      const promoCode = document.getElementById('promo-code').value.trim();
      const msg = document.getElementById('promo-message');
      if (!promoCode) {
        msg.textContent = 'Please enter a promo code'; msg.className='mt-2 text-sm text-red-500'; msg.classList.remove('hidden'); return;
      }
      const codes = { FITFUEL10:0.10, WELCOME20:0.20, SAVE15:0.15 };
      const d = codes[promoCode.toUpperCase()];
      if (d) {
        const currentSubtotal = parseFloat(document.getElementById('subtotal').textContent.replace(/[₱,]/g,''));
        const discountAmount = currentSubtotal * d;
        const newSubtotal = currentSubtotal - discountAmount;
        const newTotal = newSubtotal + 100.00;
        document.getElementById('subtotal').textContent = '₱' + newSubtotal.toFixed(2);
        document.getElementById('total').textContent = '₱' + newTotal.toFixed(2);
        msg.textContent = `Promo code applied! You saved ₱${discountAmount.toFixed(2)}`;
        msg.className='mt-2 text-sm text-green-500'; msg.classList.remove('hidden');
      } else {
        msg.textContent = 'Invalid promo code'; msg.className='mt-2 text-sm text-red-500'; msg.classList.remove('hidden');
      }
    }

    // Checkout
    function proceedToCheckout() {
      const checked = document.querySelectorAll('.item-checkbox:checked');
      if (checked.length === 0) { alert('Please select at least one item to checkout'); return; }
      const ids = Array.from(checked).map(cb => cb.dataset.itemId);
      window.location.href = 'checkout.php?selected_items=' + encodeURIComponent(JSON.stringify(ids));
    }

    // Remove selected
    function removeSelected() {
      const checked = document.querySelectorAll('.item-checkbox:checked');
      if (checked.length === 0) return;
      if (!confirm(`Are you sure you want to remove ${checked.length} selected item(s)?`)) return;

      const ids = [];
      checked.forEach(cb => { ids.push(cb.dataset.itemId); cb.closest('.flex').remove(); });
      updateTotals(); updateSelectAll();
      const span = document.querySelector('.text-sm.text-gray-500');
      if (span) span.textContent = `${document.querySelectorAll('.item-checkbox').length} items`;

      Promise.all(ids.map(id =>
        fetch('remove_cart_item.php', {
          method: 'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ cart_item_id:id })
        })
      ))
      .then(rs => Promise.all(rs.map(r=>r.json())))
      .then(() => { if (document.querySelectorAll('.item-checkbox').length===0) window.location.reload(); })
      .catch(()=>window.location.reload());
    }

    function toggleSelectAll() {
      const selectAll = document.getElementById('select-all');
      const boxes = document.querySelectorAll('.item-checkbox');
      const removeBtn = document.getElementById('remove-selected-btn');
      boxes.forEach(cb => cb.checked = selectAll.checked);
      removeBtn.classList.toggle('hidden', !selectAll.checked);
      updateTotals();
    }

    function updateSelectAll() {
      const boxes = document.querySelectorAll('.item-checkbox');
      const selectAll = document.getElementById('select-all');
      const removeBtn = document.getElementById('remove-selected-btn');
      const checked = document.querySelectorAll('.item-checkbox:checked').length;
      const total = boxes.length;

      if (checked === 0) {
        selectAll.indeterminate = false; selectAll.checked = false; removeBtn.classList.add('hidden');
      } else if (checked === total) {
        selectAll.indeterminate = false; selectAll.checked = true; removeBtn.classList.remove('hidden');
      } else {
        selectAll.indeterminate = true; selectAll.checked = false; removeBtn.classList.remove('hidden');
      }
      updateTotals();
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('select-all').addEventListener('change', toggleSelectAll);
      document.querySelectorAll('.item-checkbox').forEach(cb => cb.addEventListener('change', updateSelectAll));
      updateTotals();
    });
  </script>
</body>
</html>
