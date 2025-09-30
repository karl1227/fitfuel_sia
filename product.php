<?php
/* ============================================================================
   File: product.php
   ============================================================================ */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';
ini_set('display_errors','1'); ini_set('display_startup_errors','1'); error_reporting(E_ALL);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(400); die('Invalid product.'); }

try {
    $pdo = getDBConnection();
    $sql = "SELECT p.*,
                   c.name  AS category_name,
                   sc.name AS subcategory_name
            FROM products p
            LEFT JOIN categories c     ON p.category_id    = c.category_id
            LEFT JOIN subcategories sc ON p.subcategory_id = sc.subcategory_id
            WHERE p.product_id = ?
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) { $product = null; }

if (!$product) {
  http_response_code(404); ?>
  <!doctype html>
  <html><head>
    <meta charset="utf-8"><title>Product Not Found - FitFuel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  </head>
  <body class="min-h-screen bg-white flex items-center justify-center">
    <div class="text-center p-6">
      <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
      <h1 class="text-2xl font-semibold mb-2">Product not found</h1>
      <p class="text-gray-600 mb-6">The item you are looking for doesn’t exist or was removed.</p>
      <a href="shop.php" class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Back to Shop</a>
    </div>
  </body></html><?php exit;
}

/* -------- Display data -------- */
$images = json_decode($product['images'] ?? '[]', true);
$images = is_array($images) ? array_values(array_filter($images)) : [];
if (empty($images)) $images = ['img/placeholder.svg'];

$on_sale = ((float)($product['sale_percentage'] ?? 0) > 0);
$price   = (float)($product['price'] ?? 0);
$final   = $on_sale ? ($price * (1 - ((float)$product['sale_percentage'] / 100))) : $price;

/* Stock support (optional) */
$hasStockCol        = array_key_exists('stock', (array)$product);
$stock              = $hasStockCol ? max(0, (int)$product['stock']) : null;
$inStock            = !$hasStockCol ? true : ($stock > 0);
$lowStockThreshold  = 5;

/* Cart badge */
$cart_count = 0;
try {
  if (!empty($_SESSION['user_id'])) {
    $cart_sql = "SELECT COALESCE(SUM(ci.quantity),0)
                 FROM cart c LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id
                 WHERE c.user_id=?";
    $cart_stmt = $pdo->prepare($cart_sql);
    $cart_stmt->execute([ (int)$_SESSION['user_id'] ]);
    $cart_count = (int)($cart_stmt->fetchColumn() ?: 0);
  }
} catch (Throwable $e) {}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($product['name']); ?> - FitFuel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= htmlspecialchars(mb_strimwidth($product['description'] ?? '', 0, 155, '…')); ?>">
  <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    .cart-count-badge{position:absolute;top:-.25rem;right:-.25rem}
    /* why: hide native number spinners, we use custom +/- */
    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button{ -webkit-appearance:none; margin:0; }
    input[type=number]{ -moz-appearance:textfield; }
  </style>
</head>
<body class="font-body bg-white text-slate-700">
  <!-- Header -->
  <nav class="bg-black py-4">
    <div class="container mx-auto px-4 flex items-center justify-between">
      <a href="index.php" class="flex items-center"><img src="img/LOGO-Fitfuel.png" width="75" alt="LOGO"></a>
      <div class="flex items-center gap-6">
        <a href="shop.php" class="text-white hover:text-emerald-400">Shop</a>
        <a href="cart.php" class="relative p-2 text-white hover:text-emerald-400">
          <i class="fas fa-shopping-cart text-xl"></i>
          <?php if ($cart_count > 0): ?>
            <span class="cart-count-badge bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?= $cart_count; ?></span>
          <?php endif; ?>
        </a>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a href="logout.php" class="text-white hover:text-emerald-400">Logout</a>
        <?php else: ?>
          <a href="login.php" class="text-white hover:text-emerald-400">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <div class="container mx-auto px-4 py-8">
    <!-- Breadcrumbs -->
    <nav class="text-sm mb-6">
      <a href="index.php" class="text-slate-500 hover:text-emerald-600">Home</a>
      <span class="mx-2 text-slate-400">/</span>
      <a href="shop.php" class="text-slate-500 hover:text-emerald-600">Shop</a>
      <?php if (!empty($product['category_name'])): ?>
        <span class="mx-2 text-slate-400">/</span>
        <a class="text-slate-500 hover:text-emerald-600" href="shop.php?category=<?= (int)$product['category_id']; ?>">
          <?= htmlspecialchars($product['category_name']); ?>
        </a>
      <?php endif; ?>
      <span class="mx-2 text-slate-400">/</span>
      <span class="text-slate-700"><?= htmlspecialchars($product['name']); ?></span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
      <!-- Gallery -->
      <section>
        <div class="relative rounded-xl overflow-hidden border">
          <img id="mainImage" src="<?= htmlspecialchars($images[0]); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="w-full h-[480px] object-cover">
          <?php if ($on_sale): ?>
            <span class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 rounded text-sm font-semibold"><?= (int)$product['sale_percentage']; ?>% OFF</span>
          <?php endif; ?>
        </div>
        <?php if (count($images) > 1): ?>
          <div class="mt-4 grid grid-cols-5 gap-3">
            <?php foreach ($images as $idx => $img): ?>
              <button class="border rounded-lg overflow-hidden hover:opacity-80" onclick="swapImage('<?= htmlspecialchars($img); ?>')">
                <img src="<?= htmlspecialchars($img); ?>" alt="Thumb <?= $idx+1; ?>" class="w-full h-24 object-cover">
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <!-- Info -->
      <section>
        <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-2"><?= htmlspecialchars($product['name']); ?></h1>
        <?php if (!empty($product['subcategory_name'])): ?>
          <div class="text-sm text-slate-500 mb-2"><?= htmlspecialchars($product['subcategory_name']); ?></div>
        <?php endif; ?>

        <div class="flex items-end gap-3 mb-3">
          <?php if ($on_sale): ?>
            <div class="text-3xl font-extrabold text-red-600">₱<?= number_format($final, 2); ?></div>
            <div class="text-lg text-gray-500 line-through">₱<?= number_format($price, 2); ?></div>
          <?php else: ?>
            <div class="text-3xl font-extrabold text-emerald-600">₱<?= number_format($price, 2); ?></div>
          <?php endif; ?>
        </div>

        <!-- Stock display -->
        <?php if ($hasStockCol): ?>
          <?php if (!$inStock): ?>
            <div class="mb-4 inline-flex items-center gap-2 text-red-600 text-sm" aria-live="polite">
              <i class="fa-solid fa-circle-xmark"></i>
              <span>Out of stock</span>
            </div>
          <?php elseif ($stock <= $lowStockThreshold): ?>
            <div class="mb-4 inline-flex items-center gap-2 text-amber-600 text-sm" aria-live="polite">
              <i class="fa-solid fa-triangle-exclamation"></i>
              <span>Only <strong><?= $stock; ?></strong> left — order soon</span>
            </div>
          <?php else: ?>
            <div class="mb-4 inline-flex items-center gap-2 text-emerald-600 text-sm" aria-live="polite">
              <i class="fa-solid fa-circle-check"></i>
              <span>In stock: <strong><?= $stock; ?></strong></span>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <p class="text-slate-700 leading-relaxed mb-6"><?= nl2br(htmlspecialchars($product['description'] ?? '')); ?></p>

        <!-- Quantity + Add to Cart -->
        <div class="flex items-center gap-4 mb-8">
          <div class="inline-flex items-stretch rounded-xl border-2 border-emerald-300 focus-within:ring-2 focus-within:ring-emerald-500 overflow-hidden <?= $inStock ? '' : 'opacity-50'; ?>">
            <button type="button" id="qtyMinus"
                    class="px-3 md:px-4 text-xl select-none hover:bg-emerald-50 active:bg-emerald-100"
                    aria-label="Decrease quantity" <?= $inStock ? '' : 'disabled'; ?>>−</button>
            <input id="qty" type="number" inputmode="numeric" pattern="[0-9]*"
                   min="1" <?= ($hasStockCol && $stock > 0) ? 'max="'.(int)$stock.'"' : ''; ?>
                   value="1"
                   class="w-16 text-center outline-none border-0 focus:ring-0 py-2"
                   aria-label="Quantity" <?= $inStock ? '' : 'disabled'; ?>>
            <button type="button" id="qtyPlus"
                    class="px-3 md:px-4 text-xl select-none hover:bg-emerald-50 active:bg-emerald-100"
                    aria-label="Increase quantity" <?= $inStock ? '' : 'disabled'; ?>>+</button>
          </div>
          <span id="qtyHint" class="text-xs text-slate-500"></span>

          <button onclick="addToCartDetail(<?= (int)$product['product_id']; ?>)"
                  class="bg-black text-white px-6 py-3 rounded-lg hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                  <?= $inStock ? '' : 'disabled'; ?>>
            <i class="fas fa-shopping-cart mr-2"></i><?= $inStock ? 'Add to Cart' : 'Out of Stock'; ?>
          </button>

          <a href="wishlist.php?add=<?= (int)$product['product_id']; ?>" class="px-4 py-3 border rounded-lg hover:bg-gray-50" aria-label="Add to wishlist">
            <i class="far fa-heart"></i>
          </a>
        </div>

        <!-- Meta -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
          <div class="p-4 rounded-lg border">
            <div class="font-semibold text-slate-800 mb-1">Category</div>
            <div class="text-slate-600"><?= htmlspecialchars($product['category_name'] ?? '—'); ?></div>
          </div>
          <div class="p-4 rounded-lg border">
            <div class="font-semibold text-slate-800 mb-1">SKU / ID</div>
            <div class="text-slate-600">#<?= (int)$product['product_id']; ?></div>
          </div>
        </div>
      </section>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-slate-800 text-white">
    <div class="container mx-auto px-4 py-12">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <div>
          <div class="flex items-center gap-2 mb-4">
            <img src="img/LOGO-Fitfuel.png" alt="FitFuel Logo" class="w-10 h-10">
            <span class="text-xl font-semibold">FitFuel</span>
          </div>
          <p class="text-slate-300 text-sm">Quality gym supplements, equipment, and accessories you can trust.</p>
        </div>
        <div>
          <h4 class="font-semibold mb-3">Shop</h4>
          <ul class="space-y-2 text-slate-300 text-sm">
            <li><a href="shop.php?category=1" class="hover:text-emerald-400">Gym Accessories</a></li>
            <li><a href="shop.php?category=3" class="hover:text-emerald-400">Gym Supplements</a></li>
            <li><a href="shop.php?category=2" class="hover:text-emerald-400">Gym Equipment</a></li>
            <li><a href="shop.php" class="hover:text-emerald-400">All Products</a></li>
          </ul>
        </div>
        <div>
          <h4 class="font-semibold mb-3">Support</h4>
          <ul class="space-y-2 text-slate-300 text-sm">
            <li><a href="#" class="hover:text-emerald-400">Help Center</a></li>
            <li><a href="#" class="hover:text-emerald-400">Shipping & Returns</a></li>
            <li><a href="#" class="hover:text-emerald-400">Privacy Policy</a></li>
            <li><a href="#" class="hover:text-emerald-400">Terms of Service</a></li>
          </ul>
        </div>
        <div>
          <h4 class="font-semibold mb-3">Get in touch</h4>
          <ul class="space-y-2 text-slate-300 text-sm">
            <li><i class="far fa-envelope mr-2"></i> support@fitfuel.example</li>
            <li><i class="fas fa-phone mr-2"></i> +63 900 000 0000</li>
            <li class="flex gap-3 pt-2">
              <a href="#" class="hover:text-emerald-400" aria-label="Facebook"><i class="fab fa-facebook text-lg"></i></a>
              <a href="#" class="hover:text-emerald-400" aria-label="Instagram"><i class="fab fa-instagram text-lg"></i></a>
              <a href="#" class="hover:text-emerald-400" aria-label="Twitter"><i class="fab fa-x-twitter text-lg"></i></a>
            </li>
          </ul>
        </div>
      </div>
      <div class="border-t border-slate-700 mt-10 pt-6 text-center text-sm text-slate-400">
        &copy; <?= date('Y'); ?> FitFuel. All rights reserved.
      </div>
    </div>
  </footer>

  <!-- Toast -->
  <div id="notification" class="fixed top-20 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50 max-w-sm">
    <div class="flex items-center"><i class="fas fa-check-circle mr-2"></i><span id="notification-message">Product added to cart!</span></div>
  </div>

  <script>
    function swapImage(src){ document.getElementById('mainImage').src = src; }

    // Qty stepper logic (hard-capped by max=stock)
    const qtyInput = document.getElementById('qty');
    const minusBtn = document.getElementById('qtyMinus');
    const plusBtn  = document.getElementById('qtyPlus');
    const qtyHint  = document.getElementById('qtyHint');

    function getMin(){ return parseInt(qtyInput?.min || '1', 10); }
    function getMax(){ return qtyInput?.max ? parseInt(qtyInput.max, 10) : Infinity; }

    function clampQty(v){
      const n = isNaN(v) ? getMin() : v;
      return Math.min(getMax(), Math.max(getMin(), n));
    }
    function updateStepperState(){
      if (!qtyInput) return;
      const val = parseInt(qtyInput.value || '1', 10);
      if (minusBtn) minusBtn.disabled = (val <= getMin());
      if (plusBtn)  plusBtn.disabled  = (val >= getMax());
      if (getMax() !== Infinity && val >= getMax()) { qtyHint.textContent = `Max ${getMax()} reached`; }
      else { qtyHint.textContent = ''; }
    }
    function adjustQty(delta){
      if (!qtyInput) return;
      qtyInput.value = clampQty((parseInt(qtyInput.value, 10) || getMin()) + delta);
      updateStepperState();
    }
    minusBtn?.addEventListener('click', () => adjustQty(-1));
    plusBtn ?.addEventListener('click', () => adjustQty(+1));
    qtyInput?.addEventListener('input', () => { qtyInput.value = clampQty(parseInt(qtyInput.value,10)); updateStepperState(); });
    qtyInput?.addEventListener('change', updateStepperState);
    updateStepperState();

    function showNotification(message, type = 'success') {
      const n = document.getElementById('notification');
      const m = document.getElementById('notification-message');
      m.textContent = message;
      n.classList.remove('bg-emerald-500','bg-red-500');
      n.classList.add(type === 'error' ? 'bg-red-500' : 'bg-emerald-500');
      n.style.transform = 'translateX(0)';
      setTimeout(() => { n.style.transform = 'translateX(100%)'; }, 3000);
    }
    function updateCartCount() {
      fetch('get_cart_count.php')
        .then(r => r.json())
        .then(d => {
          if (!d.success) return;
          const cartIcon = document.querySelector('a[href="cart.php"]');
          let badge = cartIcon.querySelector('.cart-count-badge');
          if (d.count > 0) {
            if (!badge) {
              badge = document.createElement('span');
              badge.className = 'cart-count-badge bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center';
              cartIcon.appendChild(badge);
            }
            badge.textContent = d.count;
          } else if (badge) { badge.remove(); }
        }).catch(()=>{});
    }
    function addToCartDetail(productId) {
      <?php if (empty($_SESSION['user_id'])): ?>
        window.location.href = 'login.php'; return;
      <?php endif; ?>
      const qty = clampQty(parseInt(qtyInput?.value || '1', 10)); // why: guard client-side
      if (qtyInput) { qtyInput.value = qty; updateStepperState(); }
      fetch('add_to_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ product_id: productId, quantity: qty })
      })
      .then(r => r.json())
      .then(d => {
        if (d.success) { showNotification('Product added to cart!'); updateCartCount(); }
        else { showNotification('Error: ' + (d.message || 'Could not add to cart'), 'error'); }
      })
      .catch(() => showNotification('Network error adding to cart', 'error'));
    }
  </script>
</body>
</html>
