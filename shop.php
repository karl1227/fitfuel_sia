<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';

/* -------- Debug while building -------- */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* -------- Inputs -------- */
$search      = isset($_GET['search']) ? trim($_GET['search']) : '';
$category    = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$subcategory = isset($_GET['subcategory']) ? (int)$_GET['subcategory'] : 0;
$price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
$sort        = isset($_GET['sort']) ? $_GET['sort'] : 'featured';

/* -------- WHERE builder -------- */
$where  = ["1=1"]; // keep valid even with no filters
$params = [];

/* If you DO have a visibility flag, uncomment this line and set the column name */
// $where[] = "p.is_active = 1";

if ($search !== '') {
    $where[]  = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($category > 0) {
    $where[]  = "p.category_id = ?";
    $params[] = $category;
}
if ($subcategory > 0) {
    $where[]  = "p.subcategory_id = ?";
    $params[] = $subcategory;
}

/* Price range filter */
switch ($price_range) {
    case 'under_1000': $where[] = "p.price < 1000"; break;
    case '1000_2000' : $where[] = "p.price >= 1000 AND p.price < 2000"; break;
    case '2000_3000' : $where[] = "p.price >= 2000 AND p.price < 3000"; break;
    case 'above_3000': $where[] = "p.price >= 3000"; break;
}

/* Sort */
$order_by = "p.created_at DESC";
switch ($sort) {
    case 'price_low_high': $order_by = "p.price ASC"; break;
    case 'price_high_low': $order_by = "p.price DESC"; break;
    case 'name_a_z':       $order_by = "p.name ASC"; break;
    case 'newest':         $order_by = "p.created_at DESC"; break;
    case 'featured':       $order_by = "p.is_popular DESC, p.is_best_seller DESC, p.created_at DESC"; break;
}

/* -------- Query -------- */
try {
    $pdo = getDBConnection();

    // categories (for sidebar + dropdown)
    $categories_stmt = $pdo->query("SELECT category_id, name FROM categories ORDER BY name");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

    // products
    $sql = "SELECT p.*, c.name AS category_name, sc.name AS subcategory_name
            FROM products p
            LEFT JOIN categories c     ON p.category_id    = c.category_id
            LEFT JOIN subcategories sc ON p.subcategory_id = sc.subcategory_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY $order_by";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $error = null;
} catch (PDOException $e) {
    $products = [];
    $categories = [];
    $error = "Database error: " . $e->getMessage();
}

/* -------- Cart count in header -------- */
$cart_count = 0;
if (!empty($_SESSION['user_id'])) {
    try {
        $cart_sql = "SELECT COALESCE(SUM(ci.quantity), 0) AS count
                     FROM cart c
                     LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id
                     WHERE c.user_id = ?";
        $cart_stmt = $pdo->prepare($cart_sql);
        $cart_stmt->execute([$_SESSION['user_id']]);
        $cart_count = (int)($cart_stmt->fetchColumn() ?: 0);
    } catch (PDOException $e) {
        $cart_count = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Shop - FitFuel</title>
  <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/shop.css">
  <style>
    .line-clamp-3{display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
    .cart-count-badge{position:absolute;top:-.25rem;right:-.25rem}
  </style>
</head>
<body class="font-body bg-white text-slate-600">
  <!-- Top bar -->
  <nav class="bg-white text-black py-2">
    <div class="container mx-auto px-4">
      <div class="flex justify-end space-x-6 text-sm">
        <a href="#" class="hover:text-emerald-400 transition-colors">Review</a>
        <a href="#" class="hover:text-emerald-400 transition-colors">Help</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a href="logout.php" class="hover:text-emerald-400 transition-colors">Logout</a>
        <?php else: ?>
          <a href="login.php" class="hover:text-emerald-400 transition-colors">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <!-- Main nav -->
  <nav class="sticky-nav bg-black border-b border-white py-4">
    <div class="container mx-auto px-4">
      <div class="flex items-center justify-between">
        <!-- Logo -->
        <a href="index.php" class="flex items-center">
          <img src="img/LOGO-Fitfuel.png" width="75" height="auto" alt="LOGO">
        </a>

        <!-- Primary categories -->
        <div class="hidden md:flex space-x-8">
          <a href="shop.php?category=1" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Accessories</a>
          <a href="shop.php?category=3" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Supplements</a>
          <a href="shop.php?category=2" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Equipment</a>
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-4">
          <!-- Search -->
          <form method="GET" class="relative hidden md:block">
            <input type="text" name="search" placeholder="Search products..."
                   value="<?php echo htmlspecialchars($search); ?>"
                   class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <?php
              // preserve filters on search
              if ($category)    echo '<input type="hidden" name="category" value="'.(int)$category.'">';
              if ($subcategory) echo '<input type="hidden" name="subcategory" value="'.(int)$subcategory.'">';
              if ($price_range) echo '<input type="hidden" name="price_range" value="'.htmlspecialchars($price_range).'">';
              if ($sort)        echo '<input type="hidden" name="sort" value="'.htmlspecialchars($sort).'">';
            ?>
            <button type="submit" class="absolute right-3 top-3 text-gray-400 hover:text-emerald-600">
              <i class="fas fa-search"></i>
            </button>
          </form>

          <!-- Bell -->
          <button class="relative p-2 text-white hover:text-emerald-600 transition-colors">
            <i class="fas fa-bell text-xl"></i>
          </button>

          <!-- Cart -->
          <a href="cart.php" class="relative p-2 text-white hover:text-emerald-600 transition-colors">
            <i class="fas fa-shopping-cart text-xl"></i>
            <?php if ($cart_count > 0): ?>
              <span class="cart-count-badge bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                <?php echo $cart_count; ?>
              </span>
            <?php endif; ?>
          </a>

          <!-- Profile dropdown -->
          <div class="relative" id="profileMenu">
            <button id="profileBtn"
                    class="p-2 text-white hover:text-emerald-600 transition-colors rounded-full focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-user text-xl"></i>
            </button>
            <div id="profileDropdown"
                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 hidden z-50"
                 role="menu" aria-labelledby="profileBtn">
              <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="profile.php"   class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">My Account</a>
                <a href="my_orders.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">My Purchase</a>
                <a href="wishlist.php"  class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">My Wishlist</a>
              <?php else: ?>
                <a href="login.php"         class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">Login</a>
                <a href="registration.php"  class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">Create Account</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- Any DB error -->
  <?php if (!empty($error)): ?>
    <div class="container mx-auto px-4 mt-4">
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
        <?php echo htmlspecialchars($error); ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Page -->
  <div class="container mx-auto px-4 py-8">
    <div class="flex gap-8">
      <!-- Sidebar filters -->
      <aside class="w-full md:w-1/4 bg-white rounded-lg shadow-lg p-6 sticky top-24 h-fit">
        <h3 class="text-xl font-bold text-slate-800 mb-6">Filters</h3>

        <!-- Featured -->
        <div class="mb-6">
          <h4 class="font-semibold text-lg text-slate-700 mb-3">Featured</h4>
          <div class="space-y-2">
            <a href="shop.php?sort=featured" class="block text-slate-600 hover:text-emerald-600">Best Seller</a>
            <a href="shop.php?sort=newest"   class="block text-slate-600 hover:text-emerald-600">New Arrival</a>
          </div>
        </div>

        <!-- Categories -->
        <div class="mb-6">
          <h4 class="font-semibold text-lg text-slate-700 mb-3">Categories</h4>
          <div class="space-y-2">
            <?php foreach ($categories as $cat): ?>
              <div class="flex items-center justify-between">
                <a href="shop.php?category=<?php echo (int)$cat['category_id']; ?>"
                   class="text-slate-600 hover:text-emerald-600"><?php echo htmlspecialchars($cat['name']); ?></a>
                <?php if ($category == $cat['category_id']): ?>
                  <i class="fas fa-check text-emerald-600"></i>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Price -->
        <div class="mb-6">
          <h4 class="font-semibold text-lg text-slate-700 mb-3">Price Range</h4>
          <?php
            $ranges = [
              'under_1000'  => 'Under ₱1,000',
              '1000_2000'   => '₱1,000 - ₱2,000',
              '2000_3000'   => '₱2,000 - ₱3,000',
              'above_3000'  => 'Above ₱3,000'
            ];
            foreach ($ranges as $key => $label):
          ?>
            <label class="flex items-center space-x-2 mb-2">
              <input type="radio" name="price_range" value="<?php echo $key; ?>"
                     <?php echo $price_range === $key ? 'checked' : ''; ?>
                     onchange="applyFilters()">
              <span class="text-slate-600"><?php echo $label; ?></span>
            </label>
          <?php endforeach; ?>
        </div>

        <button onclick="clearFilters()" class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
          Clear Filters
        </button>
      </aside>

      <!-- Main area -->
      <main class="flex-1">
        <!-- Header with counts and sort -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
          <div class="flex items-center space-x-3">
            <span class="text-lg text-slate-600">Showing <?php echo count($products); ?> products</span>
            <?php if ($search): ?>
              <span class="text-sm text-emerald-600">for “<?php echo htmlspecialchars($search); ?>”</span>
            <?php endif; ?>
          </div>
          <div class="flex items-center space-x-4">
            <!-- Category quick dropdown -->
            <div class="relative">
              <select onchange="applyFilters()" class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-emerald-500" id="catSelect">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo (int)$cat['category_id']; ?>" <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-400 pointer-events-none"></i>
            </div>

            <!-- Sort -->
            <div class="relative">
              <select onchange="applySort()" class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-emerald-500" id="sortSelect">
                <option value="featured"       <?php echo $sort === 'featured' ? 'selected' : ''; ?>>Sort by: Featured</option>
                <option value="price_low_high" <?php echo $sort === 'price_low_high' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high_low" <?php echo $sort === 'price_high_low' ? 'selected' : ''; ?>>Price: High to Low</option>
                <option value="name_a_z"       <?php echo $sort === 'name_a_z' ? 'selected' : ''; ?>>Name: A to Z</option>
                <option value="newest"         <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
              </select>
              <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-400 pointer-events-none"></i>
            </div>
          </div>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($products as $product): ?>
            <?php
              $images = json_decode($product['images'] ?? '[]', true);
              $image_url = (!empty($images) && is_array($images)) ? $images[0] : 'img/placeholder.svg';
              $on_sale = ((float)$product['sale_percentage'] > 0);
              $price   = (float)$product['price'];
              $final   = $on_sale ? ($price * (1 - $product['sale_percentage']/100)) : $price;
            ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow flex flex-col h-full">
              <div class="relative">
                <img src="<?php echo htmlspecialchars($image_url); ?>"
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     class="w-full h-64 object-cover">
                <?php if ($on_sale): ?>
                  <span class="absolute top-4 left-4 bg-red-500 text-white px-2 py-1 rounded text-sm font-semibold">
                    <?php echo (int)$product['sale_percentage']; ?>% OFF
                  </span>
                <?php endif; ?>
              </div>

              <div class="p-6 flex flex-col flex-grow">
                <h3 class="font-semibold text-lg text-slate-800 mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="text-slate-600 mb-4 flex-grow line-clamp-3"><?php echo htmlspecialchars($product['description']); ?></p>

                <div class="flex items-end justify-between mt-auto">
                  <?php if ($on_sale): ?>
                    <div class="flex flex-col">
                      <span class="text-2xl font-bold text-red-600">₱<?php echo number_format($final, 2); ?></span>
                      <span class="text-sm text-gray-500 line-through">₱<?php echo number_format($price, 2); ?></span>
                    </div>
                  <?php else: ?>
                    <span class="text-2xl font-bold text-emerald-600">₱<?php echo number_format($price, 2); ?></span>
                  <?php endif; ?>

                  <button onclick="addToCart(<?php echo (int)$product['product_id']; ?>)"
                          class="bg-black text-white p-3 rounded-lg hover:bg-gray-800 transition-colors flex-shrink-0">
                    <i class="fas fa-shopping-cart"></i>
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Empty state -->
        <?php if (empty($products)): ?>
          <div class="text-center py-12">
            <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-slate-600 mb-2">No products found</h3>
            <p class="text-slate-500 mb-4">Try adjusting your search or filter criteria</p>
            <button onclick="clearFilters()" class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700">Clear Filters</button>
          </div>
        <?php endif; ?>
      </main>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-slate-800 text-white py-12">
    <div class="container mx-auto px-4">
      <div class="text-center">&copy; 2024 FitFuel. All rights reserved.</div>
    </div>
  </footer>

  <!-- Toast -->
  <div id="notification" class="fixed top-20 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50 max-w-sm">
    <div class="flex items-center"><i class="fas fa-check-circle mr-2"></i><span id="notification-message">Product added to cart!</span></div>
  </div>

  <script>
    // Apply filters (category + price radio)
    function applyFilters() {
      const url = new URL(window.location);
      const catSel = document.getElementById('catSelect');
      const price = document.querySelector('input[name="price_range"]:checked');

      if (catSel && catSel.value) url.searchParams.set('category', catSel.value); else url.searchParams.delete('category');
      if (price) url.searchParams.set('price_range', price.value); else url.searchParams.delete('price_range');

      // drop subcategory if category changed
      url.searchParams.delete('subcategory');

      window.location.href = url.toString();
    }

    function applySort() {
      const url = new URL(window.location);
      const sortSel = document.getElementById('sortSelect');
      url.searchParams.set('sort', sortSel.value);
      window.location.href = url.toString();
    }

    function clearFilters() {
      window.location.href = 'shop.php';
    }

    // Add to cart
    function addToCart(productId) {
      <?php if (empty($_SESSION['user_id'])): ?>
        window.location.href = 'login.php';
        return;
      <?php endif; ?>

      fetch('add_to_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ product_id: productId, quantity: 1 })
      })
      .then(r => r.json())
      .then(d => {
        if (d.success) {
          showNotification('Product added to cart!');
          updateCartCount();
        } else {
          showNotification('Error: ' + (d.message || 'Could not add to cart'), 'error');
        }
      })
      .catch(() => showNotification('Network error adding to cart', 'error'));
    }

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
          } else if (badge) {
            badge.remove();
          }
        })
        .catch(() => {});
    }

    // Profile dropdown
    (function () {
      const btn  = document.getElementById('profileBtn');
      const menu = document.getElementById('profileDropdown');
      if (!btn || !menu) return;
      const close = () => { menu.classList.add('hidden');  btn.setAttribute('aria-expanded','false'); };
      const open  = () => { menu.classList.remove('hidden'); btn.setAttribute('aria-expanded','true');  };
      btn.addEventListener('click', (e) => { e.stopPropagation(); menu.classList.contains('hidden') ? open() : close(); });
      document.addEventListener('click', (e) => { const c = document.getElementById('profileMenu'); if (!c.contains(e.target)) close(); });
      document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
    })();
  </script>
</body>
</html>
