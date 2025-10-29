<?php
require_once __DIR__ . '/includes/db.php';
require_once 'config/currency_helper.php';

// Start session
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Fetch About Us content from CMS (if available)
try {
    // Try to fetch from CMS - type='about' or type='homepage' with placement='about'
    $contentStmt = $pdo->prepare("
        SELECT content_id, title, description, image, image_path 
        FROM contents 
        WHERE status = 'published' 
        AND (type = 'about' OR (type = 'homepage' AND placement = 'about'))
        ORDER BY updated_at DESC
        LIMIT 5
    ");
    $contentStmt->execute();
    $cmsContent = $contentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Map CMS content to sections if available
    $aboutContent = null;
    $missionVisionContent = null;
    $valuesContent = null;
    
    if (!empty($cmsContent)) {
        foreach ($cmsContent as $item) {
            if (stripos($item['title'], 'about') !== false && !$aboutContent) {
                $aboutContent = $item;
            } elseif (stripos($item['title'], 'mission') !== false && !$missionVisionContent) {
                $missionVisionContent = $item;
            } elseif (stripos($item['title'], 'value') !== false && !$valuesContent) {
                $valuesContent = $item;
            }
        }
    }
} catch (PDOException $e) {
    // If CMS not set up yet, use default content
    $aboutContent = null;
    $missionVisionContent = null;
    $valuesContent = null;
}

$pageTitle = "About Us";
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
        .section-image {
            object-fit: cover;
            width: 100%;
            height: 100%;
            min-height: 400px;
        }
        .value-item {
            margin-bottom: 2rem;
        }
        .value-item:last-child {
            margin-bottom: 0;
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

    <!-- About Us Section 1: About Us -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <!-- Text Content (Left) -->
                <div class="order-2 md:order-1">
                    <h1 class="text-4xl md:text-5xl font-bold text-black mb-6">
                        <?php echo $aboutContent ? htmlspecialchars($aboutContent['title']) : 'About Us'; ?>
                    </h1>
                    <p class="text-base md:text-lg text-slate-700 leading-relaxed">
                        <?php 
                        if ($aboutContent && !empty($aboutContent['description'])) {
                            echo nl2br(htmlspecialchars($aboutContent['description']));
                        } else {
                            echo 'At Fit fuel we are dedicated to providing premium gym supplements designed to support and elevate your fitness journey. Our carefully curated selection of products meets the highest industry standards, ensuring optimal quality, safety, and effectiveness. Whether you\'re an athlete, a fitness professional, or someone committed to personal wellness, we offer the tools you need to achieve your performance and health goals. We pride ourselves on delivering trusted solutions that help you unlock your full potential.';
                        }
                        ?>
                    </p>
                </div>
                <!-- Image (Right) -->
                <div class="order-1 md:order-2">
                    <?php 
                    $aboutImage = $aboutContent ? ($aboutContent['image'] ?? $aboutContent['image_path'] ?? null) : null;
                    $aboutImageUrl = $aboutImage ? (strpos($aboutImage, 'http') === 0 ? $aboutImage : ltrim($aboutImage, '/')) : 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=800&q=80';
                    ?>
                    <img src="<?php echo htmlspecialchars($aboutImageUrl); ?>" 
                         alt="Fitness Professional" 
                         class="section-image rounded-lg shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section 2: Mission & Vision -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <!-- Image (Left) -->
                <div class="order-2 md:order-1">
                    <?php 
                    $missionImage = $missionVisionContent ? ($missionVisionContent['image'] ?? $missionVisionContent['image_path'] ?? null) : null;
                    $missionImageUrl = $missionImage ? (strpos($missionImage, 'http') === 0 ? $missionImage : ltrim($missionImage, '/')) : 'https://images.unsplash.com/photo-1540497077202-7c8a3999166f?w=800&q=80';
                    ?>
                    <img src="<?php echo htmlspecialchars($missionImageUrl); ?>" 
                         alt="Athlete" 
                         class="section-image rounded-lg shadow-lg">
                </div>
                <!-- Text Content (Right) -->
                <div class="order-1 md:order-2">
                    <h2 class="text-4xl md:text-5xl font-bold text-black mb-6">
                        <?php echo $missionVisionContent ? htmlspecialchars($missionVisionContent['title']) : 'Our Mission & Vision'; ?>
                    </h2>
                    <div class="space-y-6">
                        <?php 
                        if ($missionVisionContent && !empty($missionVisionContent['description'])) {
                            echo '<div class="text-base md:text-lg text-slate-700 leading-relaxed">' . nl2br(htmlspecialchars($missionVisionContent['description'])) . '</div>';
                        } else {
                            echo '
                        <div>
                            <h3 class="text-2xl font-semibold text-black mb-3">Our Mission</h3>
                            <p class="text-base md:text-lg text-slate-700 leading-relaxed">
                                Our mission is to empower individuals to achieve their fitness goals by offering scientifically backed, high-quality gym supplements that enhance performance, accelerate recovery, and support overall well-being. We are committed to delivering excellence in every product and experience, fostering a healthier, more active lifestyle for all.
                            </p>
                        </div>
                        <div>
                            <h3 class="text-2xl font-semibold text-black mb-3">Our Vision</h3>
                            <p class="text-base md:text-lg text-slate-700 leading-relaxed">
                                Our vision is to become the leading e-commerce platform for gym supplements, recognized for our commitment to excellence, innovation, and customer satisfaction. We aspire to build a trusted community where individuals are equipped with the knowledge and products they need to optimize their health, performance, and quality of life.
                            </p>
                        </div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section 3: Core Values -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
                <!-- Text Content (Left) -->
                <div>
                    <h2 class="text-4xl md:text-5xl font-bold text-black mb-8">
                        <?php echo $valuesContent ? htmlspecialchars($valuesContent['title']) : 'Our Core Values'; ?>
                    </h2>
                    <div class="space-y-8">
                        <?php 
                        if ($valuesContent && !empty($valuesContent['description'])) {
                            echo '<div class="text-base md:text-lg text-slate-700 leading-relaxed">' . nl2br(htmlspecialchars($valuesContent['description'])) . '</div>';
                        } else {
                            echo '
                        <!-- Excellence -->
                        <div class="value-item">
                            <h3 class="text-2xl font-semibold text-black mb-3">Excellence</h3>
                            <p class="text-base md:text-lg text-slate-700 leading-relaxed">
                                We are unwavering in our commitment to providing superior products that meet rigorous standards of quality, potency, and safety.
                            </p>
                        </div>
                        <!-- Integrity -->
                        <div class="value-item">
                            <h3 class="text-2xl font-semibold text-black mb-3">Integrity</h3>
                            <p class="text-base md:text-lg text-slate-700 leading-relaxed">
                                We uphold transparency, honesty, and ethical business practices, ensuring our customers can make informed decisions with confidence.
                            </p>
                        </div>
                        <!-- Customer Focus -->
                        <div class="value-item">
                            <h3 class="text-2xl font-semibold text-black mb-3">Customer Focus</h3>
                            <p class="text-base md:text-lg text-slate-700 leading-relaxed">
                                We prioritize the needs and satisfaction of our customers, striving to exceed expectations with exceptional service and tailored experiences.
                            </p>
                        </div>
                        <!-- Innovation -->
                        <div class="value-item">
                            <h3 class="text-2xl font-semibold text-black mb-3">Innovation</h3>
                            <p class="text-base md:text-lg text-slate-700 leading-relaxed">
                                We continuously explore advancements in sports nutrition and fitness science to offer cutting-edge products that deliver results.
                            </p>
                        </div>';
                        }
                        ?>
                    </div>
                </div>
                <!-- Image (Right) -->
                <div>
                    <?php 
                    $valuesImage = $valuesContent ? ($valuesContent['image'] ?? $valuesContent['image_path'] ?? null) : null;
                    $valuesImageUrl = $valuesImage ? (strpos($valuesImage, 'http') === 0 ? $valuesImage : ltrim($valuesImage, '/')) : 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&q=80';
                    ?>
                    <img src="<?php echo htmlspecialchars($valuesImageUrl); ?>" 
                         alt="Fitness Training" 
                         class="section-image rounded-lg shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

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

