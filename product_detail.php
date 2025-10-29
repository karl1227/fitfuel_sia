<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';
require_once 'config/maintenance_check.php';
require_once 'config/currency_helper.php';

// Check maintenance mode
checkMaintenanceMode();

// Check if admin is trying to access customer product details - redirect to admin dashboard
if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager', 'staff'])) {
    header('Location: admin/dashboard.php');
    exit();
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: shop.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get product details
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name, sc.name AS subcategory_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN subcategories sc ON p.subcategory_id = sc.subcategory_id
        WHERE p.product_id = ? AND p.status = 'active'
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: shop.php');
        exit;
    }
    
    // Get additional images
    $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY created_at");
    $stmt->execute([$product_id]);
    $additional_images = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get main image
    $main_images = json_decode($product['images'] ?: '[]', true);
    $main_image = !empty($main_images) ? $main_images[0] : null;
    
    // Combine all images
    $all_images = [];
    if ($main_image) {
        $all_images[] = $main_image;
    }
    $all_images = array_merge($all_images, $additional_images);
    
    // Record a product view for popularity analytics
    try {
        // Create table if it doesn't exist (idempotent)
        $pdo->exec("CREATE TABLE IF NOT EXISTS product_views (
            view_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NULL,
            viewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (view_id),
            KEY product_id (product_id),
            KEY viewed_at (viewed_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $viewerId = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $ins = $pdo->prepare("INSERT INTO product_views (product_id, user_id) VALUES (?, ?)");
        $ins->execute([$product_id, $viewerId]);
    } catch (Throwable $e) {
        // Non-fatal: ignore tracking errors
    }

} catch (PDOException $e) {
    header('Location: shop.php');
    exit;
}

// Check if product is in user's wishlist
$is_in_wishlist = false;
if (!empty($_SESSION['user_id'])) {
    try {
        $wishlist_check = $pdo->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $wishlist_check->execute([$_SESSION['user_id'], $product_id]);
        $is_in_wishlist = (bool)$wishlist_check->fetch();
    } catch (PDOException $e) {
        $is_in_wishlist = false;
    }
}

// Get cart count
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - FitFuel</title>
    <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="font-body bg-white text-slate-600 overflow-x-hidden">
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

    <!-- Main Navigation Bar -->
    <nav class="sticky-nav bg-black border-b border-white py-4">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="index.php" class="flex items-center">
                    <img src="img/LOGO-Fitfuel.png" width="75" height="auto" alt="LOGO">
                </a>

                <!-- Primary Categories -->
                <div class="hidden md:flex space-x-8">
                    <a href="shop.php?category=1" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Accessories</a>
                    <a href="shop.php?category=3" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Supplements</a>
                    <a href="shop.php?category=2" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Equipment</a>
                </div>

                <!-- Right Side -->
                <div class="flex items-center space-x-4">
                    <!-- Cart -->
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
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 hidden z-50"
                             role="menu" aria-labelledby="profileBtn">
                            <?php if (!empty($_SESSION['user_id'])): ?>
                                <a href="profile.php"   class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">My Account</a>
                                <a href="my_orders.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">My Purchase</a>
                                <a href="wishlist.php"  class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">My Wishlist</a>
                                <div class="my-2 border-t border-gray-200"></div>
                                <a href="logout.php"    class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">Logout</a>
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

    <!-- Main Content -->
    <main class="py-8 px-4">
        <div class="max-w-6xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="mb-6">
                <ol class="flex items-center space-x-2 text-sm text-gray-600">
                    <li><a href="index.php" class="hover:text-black">Home</a></li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li><a href="shop.php" class="hover:text-black">Shop</a></li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li><a href="shop.php?category=<?php echo $product['category_id']; ?>" class="hover:text-black"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li class="text-black font-medium"><?php echo htmlspecialchars($product['name']); ?></li>
                </ol>
            </nav>

            <!-- Product Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Product Images -->
                <div class="space-y-4">
                    <!-- Main Image -->
                    <div class="relative">
                        <img id="mainImage" src="<?php echo htmlspecialchars($all_images[0] ?? 'img/placeholder.svg'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="w-full h-96 object-cover rounded-lg border">
                        
                        <?php if ($product['sale_percentage'] > 0): ?>
                            <span class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                <?php echo $product['sale_percentage']; ?>% OFF
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Thumbnail Gallery -->
                    <?php if (count($all_images) > 1): ?>
                        <div class="grid grid-cols-4 gap-2">
                            <?php foreach ($all_images as $index => $image): ?>
                                <button onclick="changeMainImage('<?php echo htmlspecialchars($image); ?>', this)" 
                                        class="w-full h-20 rounded-lg border-2 border-gray-200 overflow-hidden hover:border-black transition-colors <?php echo $index === 0 ? 'border-black' : ''; ?>">
                                    <img src="<?php echo htmlspecialchars($image); ?>" 
                                         alt="Product image <?php echo $index + 1; ?>" 
                                         class="w-full h-full object-cover">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Product Info -->
                <div class="space-y-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($product['category_name']); ?>
                            <?php if ($product['subcategory_name']): ?>
                                / <?php echo htmlspecialchars($product['subcategory_name']); ?>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="text-3xl font-bold">
                        <?php if ($product['sale_percentage'] > 0): ?>
                            <?php 
                            $original_price = $product['price'];
                            $sale_price = $original_price * (1 - $product['sale_percentage'] / 100);
                            ?>
                            <span class="text-red-600"><?php echo formatCurrency($sale_price); ?></span>
                            <span class="text-gray-500 line-through text-lg ml-2"><?php echo formatCurrency($original_price); ?></span>
                        <?php else: ?>
                            <span class="text-gray-900"><?php echo formatCurrency($product['price']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Stock: </span>
                        <span class="font-semibold <?php echo $product['stock'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $product['stock'] > 0 ? $product['stock'] . ' available' : 'Out of stock'; ?>
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                                class="bg-black text-white px-8 py-3 rounded-lg hover:bg-gray-800 transition-colors flex items-center space-x-2 <?php echo $product['stock'] <= 0 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i>
                            <span><?php echo $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?></span>
                        </button>
                        
                        <a href="shop.php" class="border border-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-50 transition-colors">
                            Continue Shopping
                        </a>
                        
                        <button onclick="toggleWishlist(<?php echo $product['product_id']; ?>, this)" 
                                id="wishlistBtn"
                                class="w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-lg hover:border-red-500 hover:bg-red-50 transition-all <?php echo $is_in_wishlist ? 'border-red-500 bg-red-50' : ''; ?>"
                                title="<?php echo $is_in_wishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                            <i class="fa-heart <?php echo $is_in_wishlist ? 'fas text-red-600' : 'far text-gray-600'; ?>"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Reviews Section -->
            <?php
            // Get review statistics
            $reviewStatsStmt = $pdo->prepare("SELECT 
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
                FROM reviews WHERE product_id = ? AND status = 'approved'");
            $reviewStatsStmt->execute([$product_id]);
            $review_stats = $reviewStatsStmt->fetch();
            
            // Get reviews
            $reviewsStmt = $pdo->prepare("SELECT r.*, u.username, u.profile_picture,
                                        (SELECT COUNT(*) FROM review_images ri WHERE ri.review_id = r.review_id) as image_count
                                        FROM reviews r 
                                        JOIN users u ON r.user_id = u.user_id
                                        WHERE r.product_id = ? AND r.status = 'approved'
                                        ORDER BY r.created_at DESC LIMIT 10");
            $reviewsStmt->execute([$product_id]);
            $reviews = $reviewsStmt->fetchAll();
            
            $avg_rating = round($review_stats['average_rating'] ?? 0, 1);
            $total_reviews = (int)($review_stats['total_reviews'] ?? 0);
            ?>
            
            <div class="mt-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Product Ratings</h2>
                
                <!-- Overall Rating Summary -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Average Rating -->
                        <div class="text-center md:text-left">
                            <div class="text-4xl font-bold text-gray-900 mb-2">
                                <?= $avg_rating ?> out of 5
                            </div>
                            <div class="flex justify-center md:justify-start gap-1 mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= round($avg_rating) ? 'text-red-500' : 'text-gray-300' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-sm text-gray-600">Based on <?= $total_reviews ?> reviews</p>
                        </div>
                        
                        <!-- Rating Breakdown -->
                        <div class="md:col-span-2">
                            <?php 
                            $rating_counts = [
                                5 => (int)$review_stats['rating_5'] ?? 0,
                                4 => (int)$review_stats['rating_4'] ?? 0,
                                3 => (int)$review_stats['rating_3'] ?? 0,
                                2 => (int)$review_stats['rating_2'] ?? 0,
                                1 => (int)$review_stats['rating_1'] ?? 0
                            ];
                            
                            for ($i = 5; $i >= 1; $i--): 
                                $count = $rating_counts[$i];
                                $percentage = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
                            ?>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-sm w-16"><?= $i ?> Star</span>
                                    <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-red-500" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                    <span class="text-sm text-gray-600 w-16">(<?= $count ?>)</span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Individual Reviews -->
                <div class="space-y-4">
                    <?php if (empty($reviews)): ?>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                            <i class="fas fa-star text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-600">No reviews yet. Be the first to review this product!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): 
                            // Get review images
                            $reviewImagesStmt = $pdo->prepare("SELECT image_path FROM review_images WHERE review_id = ? ORDER BY upload_order");
                            $reviewImagesStmt->execute([$review['review_id']]);
                            $review_images = $reviewImagesStmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            $profile_pic = 'img/placeholder-user.png'; // Default fallback
                            if (!empty($review['profile_picture'])) {
                                $profile_path = $review['profile_picture'];
                                // If it's already a full URL, use it as is
                                if (str_starts_with($profile_path, 'http://') || str_starts_with($profile_path, 'https://')) {
                                    $profile_pic = $profile_path;
                                } elseif (str_starts_with($profile_path, 'uploads/profile/')) {
                                    // Already has the path prefix
                                    $profile_pic = $profile_path;
                                } else {
                                    // Add path prefix
                                    $profile_pic = 'uploads/profile/' . $profile_path;
                                }
                            }
                        ?>
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <div class="flex items-start gap-4">
                                    <img src="<?= htmlspecialchars($profile_pic) ?>" alt="<?= htmlspecialchars($review['username']) ?>" class="w-12 h-12 rounded-full object-cover border">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($review['username']) ?></h3>
                                            <span class="text-sm text-gray-500"><?= date('M d, Y', strtotime($review['created_at'])) ?></span>
                                            <?php if ($review['is_verified_purchase']): ?>
                                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">Verified Purchase</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex items-center gap-1 mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-red-500' : 'text-gray-300' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <?php if (!empty($review['review_text'])): ?>
                                            <p class="text-gray-700 mb-3"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($review_images)): ?>
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-3">
                                                <?php foreach ($review_images as $img): ?>
                                                    <img src="<?= htmlspecialchars($img) ?>" alt="Review image" class="w-full h-24 object-cover rounded-lg border">
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($review['helpful_count'] > 0): ?>
                                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                                <i class="far fa-thumbs-up"></i>
                                                <span><?= $review['helpful_count'] ?> helpful</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- You may also like Section -->
            <?php
            // Get related products (same category, excluding current product)
            try {
                $related_stmt = $pdo->prepare("
                    SELECT p.*, c.name AS category_name, sc.name AS subcategory_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.category_id
                    LEFT JOIN subcategories sc ON p.subcategory_id = sc.subcategory_id
                    WHERE p.category_id = ? 
                    AND p.product_id != ? 
                    AND p.status = 'active'
                    ORDER BY RAND()
                    LIMIT 3
                ");
                $related_stmt->execute([$product['category_id'], $product_id]);
                $related_products = $related_stmt->fetchAll();
            } catch (PDOException $e) {
                $related_products = [];
            }
            ?>
            
            <?php if (!empty($related_products)): ?>
            <div class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">You may also like</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($related_products as $related): 
                        $related_images = json_decode($related['images'] ?? '[]', true);
                        $related_image_url = (!empty($related_images) && is_array($related_images)) ? $related_images[0] : 'img/placeholder.svg';
                    ?>
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                            <a href="product_detail.php?id=<?php echo $related['product_id']; ?>">
                                <img src="<?php echo htmlspecialchars($related_image_url); ?>" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>" 
                                     class="w-full h-64 object-cover">
                            </a>
                            <div class="p-4">
                                <h3 class="font-semibold text-lg text-gray-900 mb-1">
                                    <a href="product_detail.php?id=<?php echo $related['product_id']; ?>" class="hover:text-blue-600 transition-colors">
                                        <?php echo htmlspecialchars($related['name']); ?>
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    <?php echo htmlspecialchars($related['category_name']); ?>
                                    <?php if ($related['subcategory_name']): ?>
                                        / <?php echo htmlspecialchars($related['subcategory_name']); ?>
                                    <?php endif; ?>
                                </p>
                                <p class="text-lg font-bold text-gray-900">
                                    <?php echo formatCurrency($related['price']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

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

    <!-- Toast -->
    <div id="notification" class="fixed top-20 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50 max-w-sm" style="transform: translateX(100%)">
        <div class="flex items-center"><i class="fas fa-check-circle mr-2"></i><span id="notification-message">Product added to cart!</span></div>
    </div>

    <script>
        function changeMainImage(imageSrc, button) {
            // Update main image
            document.getElementById('mainImage').src = imageSrc;
            
            // Update active thumbnail
            document.querySelectorAll('.grid button').forEach(btn => {
                btn.classList.remove('border-black');
                btn.classList.add('border-gray-200');
            });
            button.classList.remove('border-gray-200');
            button.classList.add('border-black');
        }

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

        function toggleWishlist(productId, button) {
            <?php if (empty($_SESSION['user_id'])): ?>
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            const icon = button.querySelector('i');
            const isFilled = icon.classList.contains('fas');
            
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
                        icon.classList.remove('fas', 'text-red-600');
                        icon.classList.add('far', 'text-gray-600');
                        button.classList.remove('border-red-500', 'bg-red-50');
                        button.classList.add('border-gray-300');
                        button.title = 'Add to Wishlist';
                        showNotification('Removed from wishlist');
                    } else {
                        showNotification('Error: ' + d.message, 'error');
                    }
                })
                .catch(() => showNotification('Network error', 'error'));
            } else {
                // Add to wishlist
                fetch('add_to_wishlist.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ product_id: productId })
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        icon.classList.remove('far', 'text-gray-600');
                        icon.classList.add('fas', 'text-red-600');
                        button.classList.remove('border-gray-300');
                        button.classList.add('border-red-500', 'bg-red-50');
                        button.title = 'Remove from Wishlist';
                        showNotification('Added to wishlist');
                    } else {
                        showNotification('Error: ' + d.message, 'error');
                    }
                })
                .catch(() => showNotification('Network error', 'error'));
            }
        }

        // Profile dropdown
        (function () {
            const btn = document.getElementById('profileBtn');
            const menu = document.getElementById('profileDropdown');
            if (!btn || !menu) return;
            
            const close = () => { 
                menu.classList.add('hidden');  
                btn.setAttribute('aria-expanded', 'false'); 
            };
            const open = () => { 
                menu.classList.remove('hidden'); 
                btn.setAttribute('aria-expanded', 'true');  
            };
            
            btn.addEventListener('click', (e) => { 
                e.stopPropagation(); 
                menu.classList.contains('hidden') ? open() : close(); 
            });
            
            document.addEventListener('click', (e) => { 
                const container = document.getElementById('profileMenu');
                if (container && !container.contains(e.target)) close(); 
            });
            
            document.addEventListener('keydown', (e) => { 
                if (e.key === 'Escape') close(); 
            });
        })();
    </script>
</body>
</html>
