<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';
require_once 'config/currency_helper.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

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
    // Fetch wishlist items with product details
    $sql = "
        SELECT 
            w.wishlist_id,
            p.product_id,
            p.name,
            p.description,
            p.price,
            p.sale_percentage,
            p.stock,
            p.images,
            p.status,
            c.name as category_name
        FROM wishlist w
        JOIN products p ON p.product_id = w.product_id
        LEFT JOIN categories c ON c.category_id = p.category_id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $wishlist_items = $stmt->fetchAll();
} catch (PDOException $e) {
    $wishlist_items = [];
    $error = "Database error: " . $e->getMessage();
}

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
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Wishlist - FitFuel</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#f6f6f6] text-slate-700 min-h-screen flex flex-col">
  <nav class="bg-white text-black py-2">
    <div class="container mx-auto px-4 flex justify-end space-x-6 text-sm">
      <a href="testimonials.php" class="hover:text-emerald-400">Review</a>
      <a href="faq.php" class="hover:text-emerald-400">Help</a>
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
        <div class="p-6 border-b">
          <h1 class="text-[20px] font-semibold">My Wishlist</h1>
          <p class="text-sm text-gray-600 mt-1">Products you've saved for later</p>
        </div>

        <div class="p-6">
          <?php if (isset($error)): ?>
            <div class="p-4 rounded-lg bg-red-50 text-red-700 border border-red-200">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php elseif (empty($wishlist_items)): ?>
            <div class="p-12 text-center">
              <i class="fas fa-heart text-6xl text-gray-300 mb-4"></i>
              <p class="text-gray-600 mb-2 font-semibold">Your wishlist is empty</p>
              <p class="text-sm text-gray-500 mb-4">Start adding products you love!</p>
              <a href="shop.php" class="inline-block bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                Browse Products
              </a>
            </div>
          <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <?php foreach ($wishlist_items as $item): ?>
                <?php
                  $image_url = first_image($item['images']);
                  $on_sale = ((float)$item['sale_percentage'] > 0);
                  $price = (float)$item['price'];
                  $final = $on_sale ? ($price * (1 - $item['sale_percentage']/100)) : $price;
                  $is_in_stock = (int)$item['stock'] > 0;
                ?>
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                  <div class="relative">
                    <a href="product_detail.php?id=<?php echo $item['product_id']; ?>">
                      <img src="<?php echo htmlspecialchars($image_url); ?>" 
                           alt="<?php echo htmlspecialchars($item['name']); ?>" 
                           class="w-full h-48 object-cover">
                    </a>
                    <button onclick="toggleWishlist(<?php echo $item['product_id']; ?>, this)" 
                            class="absolute top-3 right-3 p-2 bg-white rounded-full shadow-lg hover:bg-red-50 transition-colors">
                      <i class="fas fa-heart text-red-600"></i>
                    </button>
                    <?php if ($on_sale): ?>
                      <span class="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded text-sm font-semibold">
                        <?php echo (int)$item['sale_percentage']; ?>% OFF
                      </span>
                    <?php endif; ?>
                  </div>

                  <div class="p-4">
                    <a href="product_detail.php?id=<?php echo $item['product_id']; ?>" class="block mb-2">
                      <h3 class="font-semibold text-gray-900 hover:text-emerald-600 transition-colors line-clamp-2">
                        <?php echo htmlspecialchars($item['name']); ?>
                      </h3>
                    </a>
                    <p class="text-xs text-gray-500 mb-2"><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></p>

                    <div class="flex items-center justify-between mb-3">
                      <div>
                        <?php if ($on_sale): ?>
                          <span class="text-lg font-bold text-red-600"><?php echo formatCurrency($final); ?></span>
                          <span class="text-sm text-gray-500 line-through ml-2"><?php echo formatCurrency($price); ?></span>
                        <?php else: ?>
                          <span class="text-lg font-bold text-gray-900"><?php echo formatCurrency($price); ?></span>
                        <?php endif; ?>
                      </div>
                      <span class="text-xs font-semibold <?php echo $is_in_stock ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo $is_in_stock ? 'In Stock' : 'Out of Stock'; ?>
                      </span>
                    </div>

                    <div class="flex space-x-2">
                      <button onclick="addToCart(<?php echo $item['product_id']; ?>, this)" 
                              class="flex-1 bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors text-sm font-semibold <?php echo !$is_in_stock ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                              <?php echo !$is_in_stock ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart mr-1"></i>
                        Add to Cart
                      </button>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
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
  function toggleWishlist(productId, button) {
    const icon = button.querySelector('i');
    const isFilled = icon.classList.contains('text-red-600');
    
    if (isFilled) {
      // Remove from wishlist
      fetch('remove_from_wishlist.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ product_id: productId })
      })
      .then(r => r.json())
      .then(d => {
        if (d.success) {
          button.closest('.bg-white').remove();
          updateEmptyState();
        }
      })
      .catch(() => {});
    } else {
      // Add to wishlist (shouldn't happen, but just in case)
      fetch('add_to_wishlist.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ product_id: productId })
      })
      .then(r => r.json())
      .catch(() => {});
    }
  }

  function addToCart(productId, button = null) {
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
        // Remove from wishlist after successful add
        fetch('remove_from_wishlist.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ product_id: productId })
        })
        .then(r2 => r2.json())
        .then(d2 => {
          if (d2.success) {
            // Remove the product card from the DOM
            const button = document.querySelector('button[onclick*="addToCart(' + productId + '"]');
            if (button) {
              const card = button.closest('.bg-white');
              if (card) card.remove();
            }
            updateEmptyState();
          }
        });
        
        showNotification('Product added to cart!');
        updateCartCount();
        // After add to cart, remove from wishlist if button (card) is available
        if (button) {
          fetch('remove_from_wishlist.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ product_id: productId })
          })
          .then(r => r.json())
          .then(resp => {
            if (resp.success) {
              button.closest('.bg-white').remove();
              updateEmptyState && updateEmptyState();
              showNotification('Product removed from wishlist');
            }
          });
        }
      } else {
        showNotification('Error: ' + (d.message || 'Could not add to cart'), 'error');
      }
    })
    .catch(() => showNotification('Network error adding to cart', 'error'));
  }

  function showNotification(message, type = 'success') {
    // Simple notification - create if doesn't exist
    let notif = document.getElementById('wishlist-notification');
    if (!notif) {
      notif = document.createElement('div');
      notif.id = 'wishlist-notification';
      notif.className = 'fixed top-20 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform';
      document.body.appendChild(notif);
    }
    
    notif.textContent = message;
    notif.classList.remove('bg-emerald-500', 'bg-red-500');
    notif.classList.add(type === 'error' ? 'bg-red-500' : 'bg-emerald-500', 'text-white');
    notif.style.transform = 'translateX(0)';
    setTimeout(() => { notif.style.transform = 'translateX(100%)'; }, 3000);
  }

  function updateCartCount() {
    fetch('get_cart_count.php')
      .then(r => r.json())
      .then(d => {
        if (!d.success) return;
        const cartIcon = document.querySelector('a[href="cart.php"]');
        let badge = cartIcon.querySelector('.bg-emerald-500');
        if (d.count > 0) {
          if (!badge) {
            badge = document.createElement('span');
            badge.className = 'absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center';
            cartIcon.appendChild(badge);
          }
          badge.textContent = d.count;
        } else if (badge) {
          badge.remove();
        }
      })
      .catch(() => {});
  }

  function updateEmptyState() {
    const grid = document.querySelector('.grid');
    if (grid && grid.children.length === 0) {
      location.reload();
    }
  }
</script>
</body>
</html>

