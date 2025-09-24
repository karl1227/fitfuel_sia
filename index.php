<?php
// =============================================================================
// HERO BANNERS - DYNAMIC CONTENT FETCHING
// =============================================================================
// This section fetches published hero banners from the database
// and makes them available for the hero carousel section below
// =============================================================================

require_once __DIR__ . '/includes/db.php';

// Start session and initialize cart count
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Check if admin is trying to access customer homepage
if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager', 'staff'])) {
    header('Location: admin/dashboard.php');
    exit();
}

// Cart count in header
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

try {
	// Fetch published hero content: prefer homepage items with hero placement
	$primaryStmt = $pdo->prepare(
		"
		SELECT content_id, title, description, short_description, COALESCE(image, image_path) AS image_path
		FROM contents
		WHERE status = 'published'
		  AND type = 'homepage'
		  AND (placement = 'hero' OR placement IS NULL)
		  AND (schedule_start IS NULL OR schedule_start <= NOW())
		  AND (schedule_end IS NULL OR schedule_end >= NOW())
		ORDER BY updated_at DESC
		LIMIT 10
		"
	);
	$primaryStmt->execute();
	$heroBanners = $primaryStmt->fetchAll(PDO::FETCH_ASSOC);

	// Fallback 1: published banners (if no homepage hero items)
	if (empty($heroBanners)) {
		$bannerStmt = $pdo->prepare(
			"
			SELECT content_id, title, description, short_description, COALESCE(image, image_path) AS image_path
			FROM contents
			WHERE status = 'published'
			  AND type = 'banner'
			  AND (schedule_start IS NULL OR schedule_start <= NOW())
			  AND (schedule_end IS NULL OR schedule_end >= NOW())
			ORDER BY updated_at DESC
			LIMIT 10
			"
		);
		$bannerStmt->execute();
		$heroBanners = $bannerStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
	}

	// Fallback 2: any published homepage sections (no placement filter)
	if (empty($heroBanners)) {
		$fallbackStmt = $pdo->prepare(
			"
			SELECT content_id, title, description, short_description, COALESCE(image, image_path) AS image_path
			FROM contents
			WHERE status = 'published' AND type = 'homepage'
			ORDER BY updated_at DESC
			LIMIT 10
			"
		);
		$fallbackStmt->execute();
		$heroBanners = $fallbackStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
	}

	// Final safety: if still nothing, show a single default local banner so the hero area is never blank
	if (empty($heroBanners)) {
		$heroBanners = [
			['content_id' => 0, 'title' => 'Built to Move', 'description' => null, 'short_description' => 'Gear up for your best session yet', 'image_path' => 'img/carousel/C1.png']
		];
	}
	
	// Ensure image paths are properly formatted without breaking local img/ assets
	foreach ($heroBanners as &$banner) {
		if (!empty($banner['image_path'])) {
			$path = trim($banner['image_path']);
			// Keep absolute URLs
			if (preg_match('#^https?://#i', $path)) {
				$banner['image_path'] = $path;
				continue;
			}
			// Normalize leading slash
			$path = ltrim($path, '/');
			// If already under known static dirs (uploads/ or img/), keep as is
			if (strpos($path, 'uploads/') === 0 || strpos($path, 'img/') === 0) {
				$banner['image_path'] = $path;
				continue;
			}
			// Otherwise, assume it belongs in uploads/
			$banner['image_path'] = 'uploads/' . $path;
		}
	}
	unset($banner); // Break the reference
	
	// =============================================================================
	// BEST SELLING PRODUCTS - FETCH BASED ON ACTUAL SALES DATA
	// =============================================================================
	$bestSellingStmt = $pdo->prepare(
		"SELECT p.product_id, p.name, p.price, p.images, p.is_popular, p.is_best_seller, p.created_at,
		 COALESCE(SUM(oi.quantity), 0) as total_sold
		 FROM products p
		 LEFT JOIN order_items oi ON p.product_id = oi.product_id
		 LEFT JOIN orders o ON oi.order_id = o.order_id AND o.status IN ('delivered', 'shipped', 'processing')
		 WHERE p.status = 'active'
		 GROUP BY p.product_id, p.name, p.price, p.images, p.is_popular, p.is_best_seller, p.created_at
		 ORDER BY total_sold DESC, p.is_best_seller DESC, p.is_popular DESC, p.created_at DESC
		 LIMIT 12"
	);
	$bestSellingStmt->execute();
	$bestSellingRows = $bestSellingStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

	$bestSellingDataFlat = [];
	foreach ($bestSellingRows as $row) {
		$images = [];
		if (!empty($row['images'])) {
			$decoded = json_decode($row['images'], true);
			if (is_array($decoded)) { $images = $decoded; }
		}
		$imageUrl = !empty($images) ? $images[0] : 'img/placeholder.svg';
		// Normalize relative URLs
		if (!preg_match('#^https?://#i', $imageUrl)) {
			$imageUrl = ltrim($imageUrl, '/');
		}
		$bestSellingDataFlat[] = [
			'product_id' => (int)$row['product_id'],
			'name' => $row['name'],
			'price' => number_format((float)$row['price'], 2),
			'imagePath' => $imageUrl,
			'alt' => $row['name'],
			'badge' => ($row['total_sold'] > 0 ? 'Best Seller' : ($row['is_best_seller'] ? 'Best Seller' : ($row['is_popular'] ? 'Popular' : ''))),
		];
	}
	// Chunk into pages of 4 to match homepage pagination
	$bestSellingProductsPages = array_chunk($bestSellingDataFlat, 4);
	
} catch (PDOException $e) {
	// Log error and fall back to empty array
	error_log("Error fetching hero banners or products: " . $e->getMessage());
	$heroBanners = [];
	$bestSellingProductsPages = [];
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - FitFuel</title>
	<link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Minimal internal CSS to guarantee carousel visibility control */
        .carousel-slide { display: none; }
        .carousel-slide.active { display: block; }
        
        /* Cart count badge styling */
        .cart-count-badge {
            position: absolute;
            top: -0.25rem;
            right: -0.25rem;
        }
        
        /* Profile dropdown styling */
        #profileDropdown {
            z-index: 9999 !important;
        }
        
        /* Pagination styling */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 2rem;
        }
        
        .pagination button {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            background: white;
            color: #64748b;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .pagination button:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        
        .pagination button.active {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        
        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination button:disabled:hover {
            background: white;
            border-color: #e2e8f0;
        }
    </style>
    <script>
        // Provide best selling products (paged) from the database to homepage JS
        window.bestSellingProductsData = <?php echo json_encode($bestSellingProductsPages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    </script>
  </head>
  <body class="font-body bg-white text-slate-600">
    <!-- First Navigation Bar -->
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

	<!-- =============================================================================
	     HERO CAROUSEL SECTION - HOMEPAGE CONTENT FROM CMS
	     =============================================================================
	     This section displays homepage content dynamically from the CMS.
	     ============================================================================= -->
	     <section class="relative h-screen min-h-[600px] overflow-hidden">
    <?php if (!empty($heroBanners)): ?>
        <!-- HOMEPAGE CONTENT: Display content from CMS homepage type -->
        <?php foreach ($heroBanners as $idx => $banner): ?>
			<div class="carousel-slide <?php echo $idx === 0 ? 'active' : ''; ?> relative h-full">
                <div class="hero-slide-content relative h-full">
					<?php if (!empty($banner['image_path'])): ?>
						<img src="<?php echo htmlspecialchars($banner['image_path']); ?>" 
							 alt="<?php echo htmlspecialchars($banner['title']); ?>" 
							 class="absolute inset-0 w-full h-full object-cover">
                    <?php else: ?>
						<div class="absolute inset-0 w-full h-full flex items-center justify-center bg-gray-100">
                            <span class="text-gray-500">No Image Available</span>
            </div>
                    <?php endif; ?>

                    <!-- Overlay -->
					<div class="absolute inset-0 flex items-end justify-center z-10 px-4 pb-24">
						<div class="text-white text-center max-w-2xl">
							<?php if (!empty($banner['short_description'])): ?>
								<p class="text-lg md:text-xl font-light m-0 p-0">
									<?php echo htmlspecialchars($banner['short_description']); ?>
								</p>
								<h1 class="text-4xl md:text-6xl font-extrabold mb-6 leading-tight">
									<?php echo htmlspecialchars($banner['title']); ?>
								</h1>
							<?php elseif (!empty($banner['description'])): ?>
								<p class="text-xl md:text-2xl font-light mb-8">
									<?php echo htmlspecialchars($banner['description']); ?>
								</p>
							<?php endif; ?>

							<a href="shop.php" 
							class="bg-white text-black font-semibold px-8 py-3 rounded-lg shadow-lg border border-transparent transition duration-300
									hover:bg-transparent hover:text-white hover:border-white">
								Shop Now
              </a>
            </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Carousel navigation -->
    <?php if (!empty($heroBanners) && count($heroBanners) > 1): ?>
        <button id="hero-prev" onclick="previousSlide()" 
                class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white rounded-full p-3 shadow-lg transition-all z-20"
                role="button" aria-label="Previous Slide">
        <i class="fas fa-chevron-left text-emerald-600"></i>
      </button>
        <button id="hero-next" onclick="nextSlide()" 
                class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white rounded-full p-3 shadow-lg transition-all z-20"
                role="button" aria-label="Next Slide">
        <i class="fas fa-chevron-right text-emerald-600"></i>
      </button>
    <?php endif; ?>

    <!-- Carousel indicators -->
    <?php if (!empty($heroBanners) && count($heroBanners) > 1): ?>
        <div id="hero-indicators" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-20">
            <?php foreach ($heroBanners as $i => $banner): ?>
                <button onclick="currentSlide(<?php echo $i+1; ?>)" data-index="<?php echo $i; ?>"
								class="carousel-indicator w-3 h-3 rounded-full <?php echo $i===0 ? 'bg-emerald-600' : 'bg-white bg-opacity-50'; ?>"
                        aria-label="Go to banner <?php echo $i+1; ?>"
                        <?php echo $i===0 ? 'aria-current="true"' : ''; ?>>
                </button>
            <?php endforeach; ?>
      </div>
    <?php endif; ?>
    </section>


	<!-- Promo banner -->
    <section class="py-8 bg-gradient-to-r from-black to-gray-800">
      <div class="container mx-auto px-4">
        <div class="text-center text-white">
          <h3 class="font-heading text-2xl font-bold mb-2">🔥 Limited Time Offer!</h3>
          <p class="text-lg mb-4">Get 20% OFF on all gym equipment + FREE shipping on orders over $100</p>
          <div class="flex justify-center items-center space-x-4">
            <span class="bg-white text-emerald-600 px-4 py-2 rounded-lg font-bold">Use Code: FITFUEL20</span>
            <a href="shop.php" class="bg-white text-emerald-600 px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition-colors">Shop Now</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="py-16 bg-white">
      <div class="w-full">
        <div class="text-center mb-12">
          <h2 class="font-heading text-4xl font-bold text-slate-800 mb-4">Featured Products</h2>
          <p class="text-xl text-slate-600">Discover our most popular fitness essentials</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 max-w-full h-[1300px]">
				<div class="product-card relative shadow-lg overflow-hidden ">
					<img src="img/Featured/1.png" alt="Essentials" class="w-full h-full object-cover">
            <div class="absolute inset-0 flex flex-col items-start justify-end text-start p-4">
              <h3 class="font-semibold text-lg text-white mb-4">Essentials That Push You Further</h3>
						<a href="shop.php" class="bg-white text-black px-4 py-2 rounded-lg hover:bg-stone-200 transition-colors inline-block">SHOP NOW</a>
            </div>
          </div>
          <div class="product-card relative shadow-lg overflow-hidden">
					<img src="img/Featured/3.png" alt="Performance" class="w-full h-full object-cover">
            <div class="absolute inset-0 flex flex-col items-start justify-end text-start p-4">
              <h3 class="font-semibold text-lg text-white mb-4">Power Up Your Performance</h3>
						<a href="shop.php" class="bg-white text-black px-4 py-2 rounded-lg hover:bg-stone-200 transition-colors inline-block">SHOP NOW</a>
            </div>
          </div>
          <div class="product-card relative shadow-lg overflow-hidden">
					<img src="img/Featured/4.png" alt="Strength" class="w-full h-full object-cover">
            <div class="absolute inset-0 flex flex-col items-start justify-end text-start p-4">
              <h3 class="font-semibold text-lg text-white mb-4">Strength Anywhere, Anytime</h3>
						<a href="shop.php" class="bg-white text-black px-4 py-2 rounded-lg hover:bg-stone-200 transition-colors inline-block">SHOP NOW</a>
            </div>
          </div>
          <div class="product-card relative shadow-lg overflow-hidden">
					<img src="img/Featured/2.png" alt="Train Hard" class="w-full h-full object-cover">
            <div class="absolute inset-0 flex flex-col items-start justify-end text-start p-4">
              <h3 class="font-semibold text-lg text-white mb-4">Train Hard. Stay Strong</h3>
						<a href="shop.php" class="bg-white text-black px-4 py-2 rounded-lg hover:bg-stone-200 transition-colors inline-block">SHOP NOW</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Best Selling Products -->
    <section class="py-16 bg-slate-50">
      <div class="container mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="font-heading text-4xl font-bold text-slate-800 mb-4">Best Selling Products</h2>
          <p class="text-xl text-slate-600">Top-performing items based on actual sales data</p>
        </div>
        <div id="best-selling-products" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8"></div>
        <div class="pagination">
          <button onclick="changePage('prev')" id="prev-btn"><i class="fas fa-chevron-left"></i></button>
          <button onclick="goToPage(1)" class="page-btn active" data-page="1">1</button>
          <button onclick="goToPage(2)" class="page-btn" data-page="2">2</button>
          <button onclick="goToPage(3)" class="page-btn" data-page="3">3</button>
          <button onclick="changePage('next')" id="next-btn"><i class="fas fa-chevron-right"></i></button>
        </div>
      </div>
    </section>

	<!-- Banner Image Carousel -->
	<section class="relative w-full h-[400px] overflow-hidden">
		<div class="banner-slide active absolute inset-0">
			<img src="img/Banner/1.png" alt="Banner 1" class="w-full h-full object-cover">
		</div>
		<div class="banner-slide absolute inset-0">
			<img src="img/Banner/2.png" alt="Banner 2" class="w-full h-full object-cover">
		</div>
		<div class="banner-slide absolute inset-0">
			<img src="img/Banner/3.png" alt="Banner 3" class="w-full h-full object-cover">
		</div>
		<div class="banner-slide absolute inset-0">
			<img src="img/Banner/4.png" alt="Banner 4" class="w-full h-full object-cover">
		</div>
		<div class="banner-slide absolute inset-0">
			<img src="img/Banner/5.png" alt="Banner 5" class="w-full h-full object-cover">
		</div>
		<div class="banner-slide absolute inset-0">
			<img src="img/Banner/6.png" alt="Banner 6" class="w-full h-full object-cover">
		</div>
		<button onclick="prevBannerSlide()" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/70 hover:bg-white p-3 rounded-full shadow">
			<i class="fas fa-chevron-left text-emerald-600"></i>
		</button>
		<button onclick="nextBannerSlide()" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/70 hover:bg-white p-3 rounded-full shadow">
			<i class="fas fa-chevron-right text-emerald-600"></i>
		</button>
		<div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
			<button onclick="currentBannerSlide(0)" class="banner-indicator active w-3 h-3 rounded-full bg-emerald-600"></button>
			<button onclick="currentBannerSlide(1)" class="banner-indicator w-3 h-3 rounded-full bg-white bg-opacity-50"></button>
			<button onclick="currentBannerSlide(2)" class="banner-indicator w-3 h-3 rounded-full bg-white bg-opacity-50"></button>
			<button onclick="currentBannerSlide(3)" class="banner-indicator w-3 h-3 rounded-full bg-white bg-opacity-50"></button>
			<button onclick="currentBannerSlide(4)" class="banner-indicator w-3 h-3 rounded-full bg-white bg-opacity-50"></button>
			<button onclick="currentBannerSlide(5)" class="banner-indicator w-3 h-3 rounded-full bg-white bg-opacity-50"></button>
      </div>
    </section>

	<section class="text-center px-4 py-12 bg-white">
		<h2 class="uppercase tracking-tight font-extrabold text-black md:text-7xl text-4xl mb-6">FUELED-UP. WORKOUT-READY.</h2>
		<p class="text-base md:text-lg text-slate-600 mb-6 max-w-2xl mx-auto">Hydration meets hustle. Gear up with gym essentials, from supplements to equipment.</p>
		<a href="shop.php" class="bg-black text-white px-6 py-3 rounded-lg font-semibold text-lg hover:bg-gray-800 transition-colors inline-block">Shop All Fitness</a>
    </section>

    <!-- Shop by Categories -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="font-heading text-4xl font-bold text-slate-800 mb-4">Shop by Categories</h2>
          <p class="text-xl text-slate-600">Find exactly what you need for your fitness journey</p>
        </div>
			<div class="grid grid-cols-1 md:grid-cols-3 gap-9">
          <div class="group relative overflow-hidden rounded-lg shadow-lg cursor-pointer">
					<img src="img/SHOP-cat/MC-Equipment.png" alt="Gym Equipment" class="w-full h-120 object-cover group-hover:scale-105 transition-transform duration-300">
            <div class="absolute inset-0 bg-black bg-opacity-40 group-hover:bg-opacity-50 transition-all duration-300"></div>
            <div class="absolute inset-0 flex items-center justify-center">
              <div class="text-center text-white">
                <h3 class="font-heading text-2xl font-bold mb-2">Gym Equipment</h3>
                <p class="text-lg">Professional grade equipment</p>
							<a href="shop.php" class="mt-4 bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 transition-colors inline-block">Shop Now</a>
              </div>
            </div>
          </div>
          <div class="group relative overflow-hidden rounded-lg shadow-lg cursor-pointer">
					<img src="img/SHOP-cat/MC-Supplement.png" alt="Supplements" class="w-full h-120 object-cover group-hover:scale-105 transition-transform duration-300">
            <div class="absolute inset-0 bg-black bg-opacity-40 group-hover:bg-opacity-50 transition-all duration-300"></div>
            <div class="absolute inset-0 flex items-center justify-center">
              <div class="text-center text-white">
                <h3 class="font-heading text-2xl font-bold mb-2">Supplements</h3>
                <p class="text-lg">Premium nutrition products</p>
							<a href="shop.php" class="mt-4 bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 transition-colors inline-block">Shop Now</a>
              </div>
            </div>
          </div>
          <div class="group relative overflow-hidden rounded-lg shadow-lg cursor-pointer">
					<img src="img/SHOP-cat/MC-Accessories.png" alt="Accessories" class="w-full h-120 object-cover group-hover:scale-105 transition-transform duration-300">
            <div class="absolute inset-0 bg-black bg-opacity-40 group-hover:bg-opacity-50 transition-all duration-300"></div>
            <div class="absolute inset-0 flex items-center justify-center">
              <div class="text-center text-white">
                <h3 class="font-heading text-2xl font-bold mb-2">Accessories</h3>
                <p class="text-lg">Essential workout gear</p>
							<a href="shop.php" class="mt-4 bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 transition-colors inline-block">Shop Now</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Product Features -->
    <section class="py-16 bg-slate-50">
      <div class="container mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="font-heading text-4xl font-bold text-slate-800 mb-4">Why Choose FitFuel?</h2>
          <p class="text-xl text-slate-600">We're committed to your fitness success</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="text-center">
            <div class="bg-emerald-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-shipping-fast text-2xl text-emerald-600"></i>
            </div>
            <h3 class="font-heading text-xl font-semibold text-slate-800 mb-2">Free Shipping</h3>
            <p class="text-slate-600">Free shipping on orders over $75. Fast and reliable delivery nationwide.</p>
          </div>
          <div class="text-center">
            <div class="bg-emerald-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-medal text-2xl text-emerald-600"></i>
            </div>
            <h3 class="font-heading text-xl font-semibold text-slate-800 mb-2">Premium Quality</h3>
            <p class="text-slate-600">Only the highest quality products from trusted brands and manufacturers.</p>
          </div>
          <div class="text-center">
            <div class="bg-emerald-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-headset text-2xl text-emerald-600"></i>
            </div>
            <h3 class="font-heading text-xl font-semibold text-slate-800 mb-2">Expert Support</h3>
            <p class="text-slate-600">Get personalized advice from our fitness experts and nutritionists.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Newsletter -->
    <section class="py-16 bg-emerald-600">
      <div class="container mx-auto px-4 text-center">
        <h2 class="font-heading text-3xl font-bold text-white mb-4">Stay Updated</h2>
        <p class="text-emerald-100 mb-8 text-lg">Get the latest fitness tips, product updates, and exclusive offers</p>
        <div class="max-w-md mx-auto flex">
				<input type="email" placeholder="Enter your email" class="flex-1 px-4 py-3 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-emerald-300">
          <button class="bg-slate-800 text-white px-6 py-3 rounded-r-lg hover:bg-slate-700 transition-colors">Subscribe</button>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-800 text-white py-12">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div>
            <h3 class="font-heading text-2xl font-bold text-White-400 mb-4">FitFuel</h3>
            <p class="text-slate-300 mb-4">Your ultimate destination for premium fitness equipment, supplements, and accessories.</p>
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
              <li><a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors">Gym Equipment</a></li>
              <li><a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors">Supplements</a></li>
              <li><a href="#" class="text-slate-300 hover:text-emerald-400 transition-colors">Accessories</a></li>
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
				<p class="text-slate-300">&copy; <?php echo date('Y'); ?> FitFuel. All rights reserved. | Privacy Policy | Terms of Service</p>
        </div>
      </div>
    </footer>

	<script src="JS/index.js"></script>
	
	<!-- Profile Dropdown JavaScript -->
	<script>
		// Profile dropdown - exact logic from shop.php with debugging
		(function () {
			const btn  = document.getElementById('profileBtn');
			const menu = document.getElementById('profileDropdown');
			console.log('Profile dropdown elements:', { btn, menu });
			
			if (!btn || !menu) {
				console.error('Profile dropdown elements not found!');
				return;
			}
			
			const close = () => { 
				menu.style.display = 'none';
				btn.setAttribute('aria-expanded','false'); 
				console.log('Profile dropdown closed');
			};
			const open  = () => { 
				menu.style.display = 'block';
				btn.setAttribute('aria-expanded','true');  
				console.log('Profile dropdown opened');
			};
			
			btn.addEventListener('click', (e) => { 
				e.stopPropagation(); 
				console.log('Profile button clicked');
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


