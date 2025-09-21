<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$order_id_param = isset($_GET['order_id']) ? $_GET['order_id'] : '';

// Log order success page access
error_log("Order success page accessed - Order ID: '$order_id_param', User: " . $_SESSION['user_id']);

if (!$order_id_param) {
    header('Location: index.html');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    
    // Check if the order_id is numeric (auto-increment) or custom format
    if (is_numeric($order_id_param)) {
        // It's a numeric order_id (auto-increment)
        $order_sql = "SELECT o.*, u.first_name, u.last_name, u.email 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.user_id 
                      WHERE o.order_id = ? AND o.user_id = ?";
        $order_stmt = $pdo->prepare($order_sql);
        $order_stmt->execute([(int)$order_id_param, $user_id]);
    } else {
        // It's a custom_order_id (string like FF-20250910-00002)
        $order_sql = "SELECT o.*, u.first_name, u.last_name, u.email 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.user_id 
                      WHERE o.custom_order_id = ? AND o.user_id = ?";
        $order_stmt = $pdo->prepare($order_sql);
        $order_stmt->execute([$order_id_param, $user_id]);
    }
    
    $order = $order_stmt->fetch();
    
    if (!$order) {
        error_log("Order not found in database - User: $user_id, Order ID: $order_id_param");
        // Show success page with the custom order ID from URL parameter
        $order = null;
        $order_items = [];
        $shipping_address = [];
        $error = null; // No error, just show success with the ID
    } else {
        $error = null;
    }
    
    // Always use the order_id_param as the custom_order_id for display
    $custom_order_id = $order_id_param;
    
    // Debug logging
    error_log("Order Success Debug - order_id_param: '$order_id_param', custom_order_id: '$custom_order_id'");
    
    if ($order) {
        // Get order items (use the actual order_id from the database)
        $items_sql = "SELECT oi.*, p.name, p.price, p.sale_percentage, p.images,
                            CASE 
                                WHEN p.sale_percentage > 0 THEN p.price * (1 - p.sale_percentage / 100)
                                ELSE p.price
                            END as final_price
                      FROM order_items oi 
                      JOIN products p ON oi.product_id = p.product_id 
                      WHERE oi.order_id = ?";
        
        $items_stmt = $pdo->prepare($items_sql);
        $items_stmt->execute([$order['order_id']]);
        $order_items = $items_stmt->fetchAll();
        
        // Parse shipping address
        $shipping_address = json_decode($order['shipping_address'], true);
    } else {
        $order_items = [];
        $shipping_address = [];
    }
    
} catch (PDOException $e) {
    $order = null;
    $order_items = [];
    $shipping_address = [];
    $custom_order_id = $order_id_param; // Ensure custom_order_id is preserved even in error
    $error = "Database error: " . $e->getMessage();
}

// Get cart count for header
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $cart_count_sql = "SELECT COALESCE(SUM(ci.quantity), 0) as count 
                          FROM cart c 
                          LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id 
                          WHERE c.user_id = ?";
        $cart_count_stmt = $pdo->prepare($cart_count_sql);
        $cart_count_stmt->execute([$_SESSION['user_id']]);
        $cart_count_result = $cart_count_stmt->fetch();
        $cart_count = $cart_count_result['count'] ?? 0;
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
    <title>Order Success - FitFuel</title>
    <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="font-body bg-white text-slate-600">
    <!-- First Navigation Bar -->
    <nav class="bg-white text-black py-2">
        <div class="container mx-auto px-4">
            <div class="flex justify-end space-x-6 text-sm">
                <a href="#" class="hover:text-emerald-400 transition-colors">Review</a>
                <a href="#" class="hover:text-emerald-400 transition-colors">Help</a>
                <a href="#" class="hover:text-emerald-400 transition-colors">Account</a>
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
                    <a href="index.html">
                        <img src="img/LOGO-Fitfuel.png" width="75" height="auto" alt="LOGO">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.html" class="text-white hover:text-emerald-600 transition-colors">Home</a>
                    <a href="shop.php" class="text-white hover:text-emerald-600 transition-colors">Shop</a>
                    <a href="#" class="text-white hover:text-emerald-600 transition-colors">About</a>
                    <a href="#" class="text-white hover:text-emerald-600 transition-colors">Contact</a>
                </div>

                <!-- Cart Icon -->
                <div class="flex items-center space-x-4">
                    <a href="cart.php" class="relative p-2 text-white hover:text-emerald-600 transition-colors">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <?php if ($order): ?>
                <!-- Success Message -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                        <i class="fas fa-check text-green-600 text-2xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-slate-800 mb-2">Order Placed Successfully!</h1>
                    <p class="text-gray-600">Thank you for your order. We'll send you a confirmation email shortly.</p>
                </div>

                <!-- Order Details -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Order Information -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-slate-800 mb-6">Order Information</h2>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Order Number:</span>
                                <span class="font-semibold text-emerald-600 text-lg"><?php echo htmlspecialchars($custom_order_id ?: 'N/A'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Order Date:</span>
                                <span class="font-semibold"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Method:</span>
                                <span class="font-semibold">
                                    <?php echo $order['payment_method'] === 'cod' ? 'Cash on Delivery' : 'PayPal'; ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Estimated Delivery:</span>
                                <span class="font-semibold"><?php echo date('M d, Y', strtotime($order['estimated_delivery_date'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-slate-800 mb-6">Shipping Address</h2>
                        
                        <?php if ($shipping_address): ?>
                            <div class="space-y-2">
                                <p class="font-semibold"><?php echo htmlspecialchars($shipping_address['full_name']); ?></p>
                                <p class="text-gray-600"><?php echo htmlspecialchars($shipping_address['phone']); ?></p>
                                <p class="text-gray-600">
                                    <?php echo htmlspecialchars($shipping_address['address']); ?><br>
                                    <?php echo htmlspecialchars($shipping_address['city']); ?>, 
                                    <?php echo htmlspecialchars($shipping_address['state']); ?> 
                                    <?php echo htmlspecialchars($shipping_address['postal_code']); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
                    <h2 class="text-xl font-semibold text-slate-800 mb-6">Order Items</h2>
                    
                    <div class="space-y-4">
                        <?php foreach ($order_items as $item): ?>
                            <?php
                            $image_url = 'img/Featured/1.png'; // Default image
                            if ($item['images']) {
                                $images = json_decode($item['images'], true);
                                if (is_array($images) && !empty($images)) {
                                    $image_url = $images[0]; // Images already include full path
                                }
                            }
                            ?>
                            <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg">
                                <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="w-16 h-16 object-cover rounded-lg">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-slate-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="text-gray-600">Quantity: <?php echo $item['quantity']; ?></p>
                                </div>
                                <div class="text-right">
                                    <?php if ($item['sale_percentage'] > 0): ?>
                                        <p class="font-semibold text-red-600">₱<?php echo number_format($item['final_price'] * $item['quantity'], 2); ?></p>
                                        <p class="text-sm text-gray-500 line-through">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                        <p class="text-sm text-gray-500">₱<?php echo number_format($item['final_price'], 2); ?> each</p>
                                    <?php else: ?>
                                        <p class="font-semibold text-emerald-600">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                        <p class="text-sm text-gray-500">₱<?php echo number_format($item['price'], 2); ?> each</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Order Total -->
                    <div class="border-t border-gray-200 pt-4 mt-6">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total Amount:</span>
                            <span class="text-emerald-600">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-8 space-x-4">
                    <a href="shop.php" class="inline-block bg-emerald-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-emerald-700 transition-colors">
                        Continue Shopping
                    </a>
                    <a href="index.html" class="inline-block bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-gray-700 transition-colors">
                        Back to Home
                    </a>
                </div>

            <?php else: ?>
                <!-- Order Success Message -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-full mb-4">
                        <i class="fas fa-check-circle text-emerald-600 text-2xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-slate-800 mb-2">Order Success!</h1>
                    <p class="text-gray-600 mb-6">Your order has been placed successfully.</p>
                    
                    <!-- Order Details -->
                    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md mx-auto mb-6">
                        <h2 class="text-xl font-semibold text-slate-800 mb-4">Order Information</h2>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Order ID:</span>
                                <span class="font-semibold text-emerald-600 text-lg"><?php echo htmlspecialchars($custom_order_id ?: 'N/A'); ?></span>
                            </div>
                            
                            <?php if ($order && isset($order['estimated_delivery_date'])): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Estimated Delivery:</span>
                                <span class="font-semibold"><?php echo date('M d, Y', strtotime($order['estimated_delivery_date'])); ?></span>
                            </div>
                            <?php else: ?>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Estimated Delivery:</span>
                                <span class="font-semibold"><?php echo date('M d, Y', strtotime('+3 days')); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Order Date:</span>
                                <span class="font-semibold"><?php echo date('M d, Y'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-x-4">
                        <a href="shop.php" class="inline-block bg-emerald-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-emerald-700 transition-colors">
                            Continue Shopping
                        </a>
                        <a href="index.html" class="inline-block bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-gray-700 transition-colors">
                            Back to Home
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-black text-white py-12 mt-16">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <img src="img/LOGO-Fitfuel.png" width="100" height="auto" alt="LOGO" class="mb-4">
                    <p class="text-gray-400">Your ultimate fitness companion for a healthier lifestyle.</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="index.html" class="hover:text-white transition-colors">Home</a></li>
                        <li><a href="shop.php" class="hover:text-white transition-colors">Shop</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">About</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold mb-4">Support</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Shipping Info</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Returns</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Size Guide</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold mb-4">Connect</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 FitFuel. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
