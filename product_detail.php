<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';

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
    
} catch (PDOException $e) {
    header('Location: shop.php');
    exit;
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
<body class="font-body bg-gray-50">
    <!-- Header -->
    <header class="bg-black text-white fixed top-0 left-0 right-0 z-50 h-16 flex items-center justify-between px-6">
        <div class="flex items-center space-x-3">
            <a href="index.php">
                <img src="img/LOGO-Fitfuel.png" alt="FitFuel Logo" class="w-8 h-8 object-contain">
            </a>
            <div class="w-px h-6 bg-white"></div>
            <h1 class="text-xl font-bold uppercase">FitFuel</h1>
        </div>
        <nav class="hidden md:flex items-center space-x-6">
            <a href="index.php" class="hover:text-gray-300 transition-colors">Home</a>
            <a href="shop.php" class="hover:text-gray-300 transition-colors">Shop</a>
            <a href="cart.php" class="hover:text-gray-300 transition-colors relative">
                <i class="fas fa-shopping-cart text-xl"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
        </nav>
        <div class="flex items-center space-x-4">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="hover:text-gray-300 transition-colors">
                    <i class="fas fa-user text-xl"></i>
                </a>
                <a href="logout.php" class="hover:text-gray-300 transition-colors">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="hover:text-gray-300 transition-colors">Login</a>
                <a href="registration.php" class="hover:text-gray-300 transition-colors">Register</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-24 pb-8 px-6">
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
                            <span class="text-red-600">₱<?php echo number_format($sale_price, 2); ?></span>
                            <span class="text-gray-500 line-through text-lg ml-2">₱<?php echo number_format($original_price, 2); ?></span>
                        <?php else: ?>
                            <span class="text-gray-900">₱<?php echo number_format($product['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Stock: </span>
                        <span class="font-semibold <?php echo $product['stock_quantity'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $product['stock_quantity'] > 0 ? $product['stock_quantity'] . ' available' : 'Out of stock'; ?>
                        </span>
                    </div>

                    <div class="flex space-x-4">
                        <button onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                                class="bg-black text-white px-8 py-3 rounded-lg hover:bg-gray-800 transition-colors flex items-center space-x-2 <?php echo $product['stock_quantity'] <= 0 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i>
                            <span><?php echo $product['stock_quantity'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?></span>
                        </button>
                        
                        <a href="shop.php" class="border border-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-50 transition-colors">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-800 text-white py-12">
        <div class="max-w-6xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <img src="img/LOGO-Fitfuel.png" alt="FitFuel Logo" class="w-12 h-12 mb-4">
                    <p class="text-gray-300">Your premier destination for fitness equipment and supplements.</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="shop.php" class="hover:text-white">Shop</a></li>
                        <li><a href="#" class="hover:text-white">About</a></li>
                        <li><a href="#" class="hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold mb-4">Customer Service</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="#" class="hover:text-white">FAQ</a></li>
                        <li><a href="#" class="hover:text-white">Shipping</a></li>
                        <li><a href="#" class="hover:text-white">Returns</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold mb-4">Follow Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-facebook text-xl"></i></a>
                        <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-instagram text-xl"></i></a>
                        <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-twitter text-xl"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
                <p>&copy; 2024 FitFuel. All rights reserved.</p>
            </div>
        </div>
    </footer>

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
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const button = event.target.closest('button');
                    const originalText = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check"></i><span class="ml-2">Added!</span>';
                    button.classList.add('bg-green-600', 'hover:bg-green-700');
                    button.classList.remove('bg-black', 'hover:bg-gray-800');
                    
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.classList.remove('bg-green-600', 'hover:bg-green-700');
                        button.classList.add('bg-black', 'hover:bg-gray-800');
                    }, 2000);
                    
                    // Update cart count in header
                    const cartCount = document.querySelector('header .fa-shopping-cart').nextElementSibling;
                    if (cartCount && cartCount.classList.contains('bg-red-500')) {
                        const currentCount = parseInt(cartCount.textContent);
                        cartCount.textContent = currentCount + 1;
                    }
                } else {
                    alert('Failed to add item to cart: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add item to cart');
            });
        }
    </script>
</body>
</html>
