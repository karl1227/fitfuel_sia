<?php
require_once __DIR__ . '/includes/db.php';
require_once 'config/currency_helper.php';

// Start session
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Fetch approved reviews with user information
try {
    $reviewsStmt = $pdo->prepare("
        SELECT 
            r.review_id,
            r.rating,
            r.review_text,
            r.created_at,
            u.user_id,
            u.username,
            u.profile_picture,
            p.name as product_name,
            r.is_verified_purchase
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        JOIN products p ON r.product_id = p.product_id
        WHERE r.status = 'approved' AND r.review_text IS NOT NULL AND r.review_text != ''
        ORDER BY r.created_at DESC
        LIMIT 20
    ");
    $reviewsStmt->execute();
    $reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get review statistics
    $statsStmt = $pdo->query("
        SELECT 
            COUNT(*) as total_reviews,
            AVG(rating) as average_rating,
            COUNT(DISTINCT user_id) as total_customers
        FROM reviews 
        WHERE status = 'approved'
    ");
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error fetching testimonials: " . $e->getMessage());
    $reviews = [];
    $stats = ['total_reviews' => 0, 'average_rating' => 0, 'total_customers' => 0];
}

$pageTitle = "Testimonials";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - FitFuel</title>
    <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .testimonial-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        .star-rating {
            color: #000;
        }
        .profile-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
        }
        @media print {
            .testimonial-card {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body class="font-body bg-white text-slate-600">
    <!-- First Navigation Bar -->
    <nav class="bg-white text-black py-2">
      <div class="container mx-auto px-4">
        <div class="flex justify-end space-x-6 text-sm">
          <a href="testimonials.php" class="hover:text-emerald-400 transition-colors">Review</a>
          <a href="faq.php" class="hover:text-emerald-400 transition-colors">Help</a>
          <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="logout.php" class="hover:text-emerald-400 transition-colors">Logout</a>
          <?php else: ?>
            <a href="login.php" class="hover:text-emerald-400 transition-colors">Login</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>

    <!-- Second Navigation Bar -->
    <nav class="sticky top-0 z-50 bg-black border-b border-white py-4 backdrop-blur">
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

          <!-- Search and Icons -->
          <div class="flex items-center space-x-4">
            <!-- Search -->
            <form method="GET" action="shop.php" class="relative hidden md:block">
              <input type="text" name="search" placeholder="Search products..." class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
              <button type="submit" class="absolute right-3 top-3 text-gray-400 hover:text-emerald-600">
                <i class="fas fa-search"></i>
              </button>
            </form>

            <!-- Bell -->
            <button class="relative p-2 text-white hover:text-emerald-600 transition-colors">
              <i class="fas fa-bell text-xl"></i>
            </button>

            <!-- Cart -->
            <?php
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
            <a href="cart.php" class="relative p-2 text-white hover:text-emerald-600 transition-colors">
              <i class="fas fa-shopping-cart text-xl"></i>
              <?php if ($cart_count > 0): ?>
                <span class="absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
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
                   class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
                   role="menu" aria-labelledby="profileBtn" style="display: none;">
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

    <!-- Testimonials Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Header -->
            <div class="text-center mb-12">
                <p class="uppercase tracking-widest text-sm text-black mb-4 font-semibold">TESTIMONIALS</p>
                <h1 class="text-4xl md:text-6xl font-bold text-black mb-6 uppercase tracking-tight" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">
                    WHAT OUR CLIENT SAY
                </h1>
                <p class="text-base md:text-lg text-slate-600 max-w-3xl mx-auto">
                    We place value on strong relationships and have seen the benefits they bring to our business. 
                    Customer feedback is vital helping us to get it right.
                </p>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="text-center p-6 bg-gray-50 rounded-lg">
                    <div class="text-3xl font-bold text-emerald-600 mb-2"><?php echo number_format($stats['total_reviews']); ?></div>
                    <div class="text-sm text-slate-600 uppercase tracking-wide">Total Reviews</div>
                </div>
                <div class="text-center p-6 bg-gray-50 rounded-lg">
                    <div class="text-3xl font-bold text-emerald-600 mb-2">
                        <?php echo number_format((float)$stats['average_rating'], 1); ?>
                        <i class="fas fa-star text-yellow-400 text-xl"></i>
                    </div>
                    <div class="text-sm text-slate-600 uppercase tracking-wide">Average Rating</div>
                </div>
                <div class="text-center p-6 bg-gray-50 rounded-lg">
                    <div class="text-3xl font-bold text-emerald-600 mb-2"><?php echo number_format($stats['total_customers']); ?></div>
                    <div class="text-sm text-slate-600 uppercase tracking-wide">Happy Customers</div>
                </div>
            </div>

            <!-- Testimonials Grid -->
            <?php if (empty($reviews)): ?>
                <div class="text-center py-16">
                    <i class="fas fa-comments text-6xl text-gray-300 mb-4"></i>
                    <p class="text-xl text-gray-500">No testimonials yet. Be the first to share your experience!</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <?php foreach (array_slice($reviews, 0, 20) as $review): ?>
                        <div class="testimonial-card bg-white rounded-lg border border-gray-200 shadow-lg p-6">
                            <!-- User Info and Rating -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-4">
                                    <!-- Profile Picture -->
                                    <?php if (!empty($review['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($review['profile_picture']); ?>" 
                                             alt="<?php echo htmlspecialchars($review['username']); ?>"
                                             class="profile-image">
                                    <?php else: ?>
                                        <div class="profile-image bg-gradient-to-br from-gray-300 to-gray-400 flex items-center justify-center text-white font-bold text-xl">
                                            <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Name and Username -->
                                    <div>
                                        <div class="font-bold text-black text-base mb-1">
                                            <?php echo htmlspecialchars(ucfirst($review['username'])); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            @<?php echo htmlspecialchars($review['username']); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Star Rating -->
                                <div class="flex space-x-0.5">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star star-rating text-sm <?php echo $i <= $review['rating'] ? 'text-black' : 'text-gray-200'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Review Text -->
                            <div class="text-slate-700 leading-relaxed text-base">
                                <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-12 bg-emerald-600">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">Share Your Experience</h2>
            <p class="text-emerald-100 mb-6 text-lg">Have you purchased from us? We'd love to hear your feedback!</p>
            <a href="shop.php" class="bg-white text-emerald-600 px-8 py-3 rounded-lg font-semibold text-lg hover:bg-gray-100 transition-colors inline-block">
                Shop Now
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-800 text-white py-12 mt-16">
      <div class="container mx-auto px-4 text-center">
        <p class="text-slate-300">&copy; <?php echo date('Y'); ?> FitFuel. All rights reserved.</p>
      </div>
    </footer>

    <script>
      // Profile dropdown
      (function () {
        const btn  = document.getElementById('profileBtn');
        const menu = document.getElementById('profileDropdown');
        
        if (!btn || !menu) return;
        
        const close = () => { 
          menu.style.display = 'none';
          btn.setAttribute('aria-expanded','false'); 
        };
        const open  = () => { 
          menu.style.display = 'block';
          btn.setAttribute('aria-expanded','true');  
        };
        
        btn.addEventListener('click', (e) => { 
          e.stopPropagation(); 
          menu.style.display === 'none' ? open() : close(); 
        });
        
        document.addEventListener('click', (e) => { 
          const c = document.getElementById('profileMenu'); 
          if (!c.contains(e.target)) close(); 
        });
        
        document.addEventListener('keydown', (e) => { 
          if (e.key === 'Escape') close(); 
        });
      })();
    </script>
</body>
</html>

