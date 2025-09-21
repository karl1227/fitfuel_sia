<?php
require_once 'config/database.php';

// Get search, filter, and sort parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'featured';

// Build the SQL query
$where_conditions = ["p.status = 'active'"];
$params = [];

// Search functionality
if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Category filter
if ($category > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category;
}

// Price range filter
switch ($price_range) {
    case 'under_1000':
        $where_conditions[] = "p.price < 1000";
        break;
    case '1000_2000':
        $where_conditions[] = "p.price >= 1000 AND p.price < 2000";
        break;
    case '2000_3000':
        $where_conditions[] = "p.price >= 2000 AND p.price < 3000";
        break;
    case 'above_3000':
        $where_conditions[] = "p.price >= 3000";
        break;
}

// Sort functionality
$order_by = "p.created_at DESC"; // Default: newest first
switch ($sort) {
    case 'price_low_high':
        $order_by = "p.price ASC";
        break;
    case 'price_high_low':
        $order_by = "p.price DESC";
        break;
    case 'name_a_z':
        $order_by = "p.name ASC";
        break;
    case 'newest':
        $order_by = "p.created_at DESC";
        break;
    case 'featured':
        $order_by = "p.is_popular DESC, p.is_best_seller DESC, p.created_at DESC";
        break;
}

// Execute query
try {
    $pdo = getDBConnection();
    
    // Get categories for sidebar
    $categories_stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $categories_stmt->fetchAll();
    
    // Get products
    $sql = "SELECT p.*, c.name as category_name, sc.name as subcategory_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            LEFT JOIN subcategories sc ON p.subcategory_id = sc.subcategory_id 
            WHERE " . implode(' AND ', $where_conditions) . " 
            ORDER BY $order_by";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $products = [];
    $categories = [];
    $error = "Database error: " . $e->getMessage();
}

// Get cart count for logged in users
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $cart_sql = "SELECT COALESCE(SUM(ci.quantity), 0) as count 
                     FROM cart c 
                     LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id 
                     WHERE c.user_id = ?";
        $cart_stmt = $pdo->prepare($cart_sql);
        $cart_stmt->execute([$_SESSION['user_id']]);
        $cart_result = $cart_stmt->fetch();
        $cart_count = $cart_result['count'] ?? 0;
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
    <title>Shop - FitFuel</title>
    <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/shop.css">
    <style>
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="font-body bg-white text-slate-600">
    <!-- First Navigation Bar -->
    <nav class="bg-white text-black py-2">
        <div class="container mx-auto px-4">
            <div class="flex justify-end space-x-6 text-sm">
                <a href="#" class="hover:text-emerald-400 transition-colors">Review</a>
                <a href="#" class="hover:text-emerald-400 transition-colors">Help</a>
                <a href="#" class="hover:text-emerald-400 transition-colors">Account</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="hover:text-emerald-400 transition-colors">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="hover:text-emerald-400 transition-colors">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Second Navigation Bar -->
    <nav class="sticky-nav bg-black border-b border-white py-4">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="index.html">
                        <img src="img/LOGO-Fitfuel.png" width="75" height="auto" alt="LOGO">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex space-x-8">
                    <div class="dropdown">
                        <a href="shop.php?category=1" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Accessories</a>
                        <div class="dropdown-menu">
                            <a href="shop.php?category=1&subcategory=11">Lifting Straps</a>
                            <a href="shop.php?category=1&subcategory=11">Gym Gloves</a>
                            <a href="shop.php?category=1&subcategory=11">Weight Belts</a>
                            <a href="shop.php?category=1&subcategory=11">Knee Wraps</a>
                            <a href="shop.php?category=1&subcategory=11">Wrist Wraps</a>
                            <a href="shop.php?category=1&subcategory=13">Gym Bags</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="shop.php?category=3" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Supplements</a>
                        <div class="dropdown-menu">
                            <a href="shop.php?category=3&subcategory=17">Whey Protein</a>
                            <a href="shop.php?category=3&subcategory=18">Pre-Workout</a>
                            <a href="shop.php?category=3&subcategory=18">Post-Workout</a>
                            <a href="shop.php?category=3&subcategory=18">Creatine</a>
                            <a href="shop.php?category=3&subcategory=19">BCAAs</a>
                            <a href="shop.php?category=3&subcategory=19">Vitamins</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="shop.php?category=2" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Equipment</a>
                        <div class="dropdown-menu">
                            <a href="shop.php?category=2&subcategory=14">Dumbbells</a>
                            <a href="shop.php?category=2&subcategory=14">Barbells</a>
                            <a href="shop.php?category=2&subcategory=15">Resistance Bands</a>
                            <a href="shop.php?category=2&subcategory=14">Kettlebells</a>
                            <a href="shop.php?category=2&subcategory=16">Yoga Mats</a>
                            <a href="shop.php?category=2&subcategory=15">Cardio Equipment</a>
                        </div>
                    </div>
                </div>

                <!-- Search and Icons -->
                <div class="flex items-center space-x-4">
                    <!-- Search Bar -->
                    <form method="GET" class="relative hidden md:block">
                        <input type="text" name="search" placeholder="Search products..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <button type="submit" class="absolute right-3 top-3 text-gray-400 hover:text-emerald-600">
                            <i class="fas fa-search"></i>
                        </button>
                        <!-- Preserve other filters -->
                        <?php if ($category): ?>
                            <input type="hidden" name="category" value="<?php echo $category; ?>">
                        <?php endif; ?>
                        <?php if ($price_range): ?>
                            <input type="hidden" name="price_range" value="<?php echo $price_range; ?>">
                        <?php endif; ?>
                        <?php if ($sort): ?>
                            <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                        <?php endif; ?>
                    </form>

                    <!-- Icons -->
                    <button class="relative p-2 text-white hover:text-emerald-600 transition-colors">
                        <i class="fas fa-bell text-xl"></i>
                    </button>

                    <a href="cart.php" class="relative p-2 text-white hover:text-emerald-600 transition-colors">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>

                    <button class="p-2 text-white hover:text-emerald-600 transition-colors">
                        <i class="fas fa-user text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="flex gap-8">
            <!-- Sidebar -->
            <div class="w-1/4 bg-white rounded-lg shadow-lg p-6 sticky top-24 h-fit">
                <h3 class="font-heading text-xl font-bold text-slate-800 mb-6">Filters</h3>
                
                <!-- Featured Section -->
                <div class="mb-6">
                    <h4 class="font-semibold text-lg text-slate-700 mb-3">Featured</h4>
                    <div class="space-y-2">
                        <a href="shop.php?sort=featured" class="block text-slate-600 hover:text-emerald-600 transition-colors">Best Seller</a>
                        <a href="shop.php?sort=newest" class="block text-slate-600 hover:text-emerald-600 transition-colors">New Arrival</a>
                        <a href="#" class="block text-slate-600 hover:text-emerald-600 transition-colors">Bundle & Deal</a>
                    </div>
                </div>

                <!-- Categories -->
                <div class="mb-6">
                    <h4 class="font-semibold text-lg text-slate-700 mb-3">Categories</h4>
                    <div class="space-y-2">
                        <?php foreach ($categories as $cat): ?>
                            <div class="flex items-center justify-between">
                                <a href="shop.php?category=<?php echo $cat['category_id']; ?>" 
                                   class="text-slate-600 hover:text-emerald-600 transition-colors flex items-center">
                                    <i class="fas fa-dumbbell mr-2 text-sm"></i>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                                <?php if ($category == $cat['category_id']): ?>
                                    <i class="fas fa-check text-emerald-600"></i>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Price Range -->
                <div class="mb-6">
                    <h4 class="font-semibold text-lg text-slate-700 mb-3">Price Range</h4>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="price_range" value="under_1000" 
                                   <?php echo $price_range === 'under_1000' ? 'checked' : ''; ?>
                                   onchange="applyFilters()" class="mr-2">
                            <span class="text-slate-600">Under ₱1,000</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="price_range" value="1000_2000" 
                                   <?php echo $price_range === '1000_2000' ? 'checked' : ''; ?>
                                   onchange="applyFilters()" class="mr-2">
                            <span class="text-slate-600">₱1,000 - ₱2,000</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="price_range" value="2000_3000" 
                                   <?php echo $price_range === '2000_3000' ? 'checked' : ''; ?>
                                   onchange="applyFilters()" class="mr-2">
                            <span class="text-slate-600">₱2,000 - ₱3,000</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="price_range" value="above_3000" 
                                   <?php echo $price_range === 'above_3000' ? 'checked' : ''; ?>
                                   onchange="applyFilters()" class="mr-2">
                            <span class="text-slate-600">Above ₱3,000</span>
                        </label>
                    </div>
                </div>

                <!-- Clear Filters -->
                <button onclick="clearFilters()" class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                    Clear Filters
                </button>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1">
                <!-- Results Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-4">
                        <span class="text-lg text-slate-600">Showing <?php echo count($products); ?> products</span>
                        <?php if ($search): ?>
                            <span class="text-sm text-emerald-600">for "<?php echo htmlspecialchars($search); ?>"</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Filter Dropdown -->
                        <div class="relative">
                            <select onchange="applyFilters()" class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>" <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-400 pointer-events-none"></i>
                        </div>

                        <!-- Sort Dropdown -->
                        <div class="relative">
                            <select onchange="applySort()" class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="featured" <?php echo $sort === 'featured' ? 'selected' : ''; ?>>Sort by: Featured</option>
                                <option value="price_low_high" <?php echo $sort === 'price_low_high' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high_low" <?php echo $sort === 'price_high_low' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="name_a_z" <?php echo $sort === 'name_a_z' ? 'selected' : ''; ?>>Name: A to Z</option>
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($products as $product): ?>
                        <?php 
                        $images = json_decode($product['images'], true);
                        $image_url = !empty($images) ? $images[0] : 'img/placeholder.svg';
                        ?>
                        <div class="product-card bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow flex flex-col h-full">
                            <div class="relative">
                                <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="w-full h-64 object-cover">
                                
                                <!-- Product Badges -->
                                <?php if ($product['sale_percentage'] > 0): ?>
                                    <span class="absolute top-4 left-4 bg-red-500 text-white px-2 py-1 rounded text-sm font-semibold"><?php echo $product['sale_percentage']; ?>% OFF</span>
                                <?php endif; ?>
                                
                            </div>
                            
                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="font-semibold text-lg text-slate-800 mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="text-slate-600 mb-4 flex-grow line-clamp-3"><?php echo htmlspecialchars($product['description']); ?></p>
                                <div class="flex items-end justify-between mt-auto">
                                    <?php if ($product['sale_percentage'] > 0): ?>
                                        <?php 
                                        $original_price = $product['price'];
                                        $sale_price = $original_price * (1 - $product['sale_percentage'] / 100);
                                        ?>
                                        <div class="flex flex-col">
                                            <span class="text-2xl font-bold text-red-600">₱<?php echo number_format($sale_price, 2); ?></span>
                                            <span class="text-sm text-gray-500 line-through">₱<?php echo number_format($original_price, 2); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-2xl font-bold text-emerald-600">₱<?php echo number_format($product['price'], 2); ?></span>
                                    <?php endif; ?>
                                    <button onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                                            class="bg-black text-white p-3 rounded-lg hover:bg-gray-800 transition-colors flex-shrink-0">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- No Products Message -->
                <?php if (empty($products)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-slate-600 mb-2">No products found</h3>
                        <p class="text-slate-500 mb-4">Try adjusting your search or filter criteria</p>
                        <button onclick="clearFilters()" class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 transition-colors">
                            Clear Filters
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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

    <!-- Notification Toast -->
    <div id="notification" class="fixed top-20 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50 max-w-sm">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span id="notification-message">Product added to cart!</span>
        </div>
    </div>

    <script>
        // Filter and sort functionality
        function applyFilters() {
            const url = new URL(window.location);
            const categorySelect = document.querySelector('select[onchange="applyFilters()"]');
            const priceRange = document.querySelector('input[name="price_range"]:checked');
            
            if (categorySelect.value) {
                url.searchParams.set('category', categorySelect.value);
            } else {
                url.searchParams.delete('category');
            }
            
            if (priceRange) {
                url.searchParams.set('price_range', priceRange.value);
            } else {
                url.searchParams.delete('price_range');
            }
            
            window.location.href = url.toString();
        }

        function applySort() {
            const url = new URL(window.location);
            const sortSelect = document.querySelector('select[onchange="applySort()"]');
            url.searchParams.set('sort', sortSelect.value);
            window.location.href = url.toString();
        }

        function clearFilters() {
            window.location.href = 'shop.php';
        }

        // Add to cart functionality
        function addToCart(productId) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            console.log('Adding product to cart:', productId);

            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    showNotification('Product added to cart!');
                    updateCartCount();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showNotification('Error adding product to cart', 'error');
            });
        }

        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const messageEl = document.getElementById('notification-message');
            
            messageEl.textContent = message;
            
            if (type === 'error') {
                notification.className = 'fixed top-20 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50 max-w-sm';
            } else {
                notification.className = 'fixed top-20 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50 max-w-sm';
            }
            
            notification.style.transform = 'translateX(0)';
            
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
            }, 3000);
        }

        function updateCartCount() {
            console.log('Updating cart count...');
            // Make an AJAX call to get updated cart count
            fetch('get_cart_count.php')
                .then(response => {
                    console.log('Cart count response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Cart count response data:', data);
                    if (data.success) {
                        const cartIcon = document.querySelector('a[href="cart.php"]');
                        const badge = cartIcon.querySelector('.cart-count-badge');
                        
                        console.log('Current cart count:', data.count);
                        
                        if (data.count > 0) {
                            if (badge) {
                                badge.textContent = data.count;
                                console.log('Updated existing badge to:', data.count);
                            } else {
                                const newBadge = document.createElement('span');
                                newBadge.className = 'absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center';
                                newBadge.textContent = data.count;
                                cartIcon.appendChild(newBadge);
                                console.log('Created new badge with count:', data.count);
                            }
                        } else {
                            if (badge) {
                                badge.remove();
                                console.log('Removed badge (count is 0)');
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating cart count:', error);
                });
        }

    </script>
</body>
</html>
