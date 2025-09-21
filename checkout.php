<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    
    // Get selected cart items from session or URL parameter
    $selected_items = [];
    if (isset($_GET['selected_items'])) {
        $selected_items = json_decode($_GET['selected_items'], true);
    }
    
    if (empty($selected_items)) {
        header('Location: cart.php');
        exit();
    }
    
    // Get user's selected cart items
    $placeholders = str_repeat('?,', count($selected_items) - 1) . '?';
    $cart_sql = "SELECT c.cart_id, ci.cart_item_id, ci.product_id, ci.quantity, 
                        p.name, p.price, p.sale_percentage, p.images,
                        CASE 
                            WHEN p.sale_percentage > 0 THEN p.price * (1 - p.sale_percentage / 100)
                            ELSE p.price
                        END as final_price
                 FROM cart c 
                 LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id 
                 LEFT JOIN products p ON ci.product_id = p.product_id 
                 WHERE c.user_id = ? AND ci.cart_item_id IN ($placeholders)
                 ORDER BY ci.added_at DESC";
    
    $params = array_merge([$user_id], $selected_items);
    $cart_stmt = $pdo->prepare($cart_sql);
    $cart_stmt->execute($params);
    $cart_items = $cart_stmt->fetchAll();
    
    // Check if cart is empty
    if (empty($cart_items)) {
        header('Location: cart.php');
        exit();
    }
    
    // Get user's default shipping address
    $address_sql = "SELECT * FROM shipping_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1";
    $address_stmt = $pdo->prepare($address_sql);
    $address_stmt->execute([$user_id]);
    $default_address = $address_stmt->fetch();
    
    // If no default address, get the most recent one
    if (!$default_address) {
        $address_sql = "SELECT * FROM shipping_addresses WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
        $address_stmt = $pdo->prepare($address_sql);
        $address_stmt->execute([$user_id]);
        $default_address = $address_stmt->fetch();
    }
    
    // Calculate totals
    $subtotal = 0;
    $shipping_fee = 100.00; // Default shipping fee
    $total_items = 0;
    
    foreach ($cart_items as $item) {
        $subtotal += $item['final_price'] * $item['quantity'];
        $total_items += $item['quantity'];
    }
    
    $total = $subtotal + $shipping_fee;
    
} catch (PDOException $e) {
    $cart_items = [];
    $subtotal = 0;
    $shipping_fee = 100.00;
    $total = 0;
    $total_items = 0;
    $default_address = null;
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
    <title>Checkout - FitFuel</title>
    <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/shop.css">
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
        <div class="max-w-6xl mx-auto">
            <!-- Breadcrumb -->
            <div class="mb-8">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="index.html" class="text-gray-700 hover:text-emerald-600">
                                <i class="fas fa-home mr-2"></i>
                                Home
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                                <a href="cart.php" class="text-gray-700 hover:text-emerald-600">Cart</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                                <span class="text-gray-500">Checkout</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Checkout Form -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Delivery Address & Payment -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Delivery Address Section -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-slate-800">Delivery Address</h2>
                            <button onclick="openAddressModal()" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Address
                            </button>
                        </div>
                        
                        <?php if ($default_address): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-slate-800 mb-2"><?php echo htmlspecialchars($default_address['full_name']); ?></h3>
                                        <p class="text-gray-600 mb-1"><?php echo htmlspecialchars($default_address['phone']); ?></p>
                                        <p class="text-gray-600 mb-1">
                                            <?php echo htmlspecialchars($default_address['address_line1']); ?>
                                            <?php if ($default_address['address_line2']): ?>
                                                <br><?php echo htmlspecialchars($default_address['address_line2']); ?>
                                            <?php endif; ?>
                                        </p>
                                        <p class="text-gray-600">
                                            <?php echo htmlspecialchars($default_address['city']); ?>, 
                                            <?php echo htmlspecialchars($default_address['state']); ?> 
                                            <?php echo htmlspecialchars($default_address['postal_code']); ?>
                                        </p>
                                    </div>
                                    <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded-full">Default</span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                                <i class="fas fa-map-marker-alt text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-500 mb-4">No delivery address found</p>
                                <button onclick="openAddressModal()" class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 transition-colors">
                                    Add Address
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Payment Method Section -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-slate-800 mb-6">Payment Method</h2>
                        
                        <div class="space-y-4">
                            <!-- Cash on Delivery -->
                            <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="payment_method" value="cod" class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500" checked>
                                <div class="ml-3">
                                    <div class="flex items-center">
                                        <i class="fas fa-money-bill-wave text-green-600 text-xl mr-3"></i>
                                        <span class="font-semibold text-slate-800">Cash on Delivery</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Pay when your order arrives</p>
                                </div>
                            </label>

                            <!-- PayPal -->
                            <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="payment_method" value="paypal" class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500">
                                <div class="ml-3">
                                    <div class="flex items-center">
                                        <i class="fab fa-paypal text-blue-600 text-xl mr-3"></i>
                                        <span class="font-semibold text-slate-800">PayPal</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Pay securely with PayPal</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-lg p-6 sticky top-24">
                        <h2 class="text-xl font-semibold text-slate-800 mb-6">Order Summary</h2>
                        
                        <!-- Products -->
                        <div class="space-y-4 mb-6">
                            <?php foreach ($cart_items as $item): ?>
                                <?php
                                $image_url = 'img/Featured/1.png'; // Default image
                                if ($item['images']) {
                                    $images = json_decode($item['images'], true);
                                    if (is_array($images) && !empty($images)) {
                                        $image_url = $images[0]; // Images already include full path
                                    }
                                }
                                ?>
                                <div class="flex items-center space-x-3">
                                    <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="w-12 h-12 object-cover rounded-lg">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-slate-800 text-sm"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p class="text-gray-600 text-sm">Qty: <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <div class="text-right">
                                        <?php if ($item['sale_percentage'] > 0): ?>
                                            <p class="font-semibold text-red-600">₱<?php echo number_format($item['final_price'] * $item['quantity'], 2); ?></p>
                                            <p class="text-xs text-gray-500 line-through">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                            <p class="text-xs text-gray-500">₱<?php echo number_format($item['final_price'], 2); ?> each</p>
                                        <?php else: ?>
                                            <p class="font-semibold text-emerald-600">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                            <p class="text-xs text-gray-500">₱<?php echo number_format($item['price'], 2); ?> each</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Promo Code -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-800 mb-2">Promo Code</label>
                            <div class="flex space-x-2">
                                <input type="text" id="promo_code" placeholder="Enter promo code" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <button onclick="applyPromoCode()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                                    Apply
                                </button>
                            </div>
                            <div id="promo_message" class="mt-2 text-sm"></div>
                        </div>

                        <!-- Order Totals -->
                        <div class="border-t border-gray-200 pt-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal (<?php echo $total_items; ?> items)</span>
                                <span class="font-semibold">₱<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-semibold">₱<?php echo number_format($shipping_fee, 2); ?></span>
                            </div>
                            <div id="promo_discount" class="flex justify-between text-emerald-600 hidden">
                                <span>Discount</span>
                                <span class="font-semibold">-₱<span id="discount_amount">0.00</span></span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
                                <span>Total</span>
                                <span class="text-emerald-600">₱<span id="total_amount"><?php echo number_format($total, 2); ?></span></span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <button onclick="processCheckout()" class="w-full bg-emerald-600 text-white py-3 rounded-lg font-semibold hover:bg-emerald-700 transition-colors mt-6">
                            <i class="fas fa-lock mr-2"></i>
                            Complete Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Address Modal -->
    <div id="addressModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-slate-800">Edit Delivery Address</h3>
                        <button onclick="closeAddressModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <form id="addressForm" onsubmit="saveAddress(event)">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-800 mb-2">Full Name *</label>
                                <input type="text" name="full_name" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                       value="<?php echo $default_address['full_name'] ?? ''; ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-800 mb-2">Phone Number *</label>
                                <input type="tel" name="phone" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                       value="<?php echo $default_address['phone'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-800 mb-2">Region *</label>
                            <select name="region" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">Select Region</option>
                                <option value="Metro Manila" <?php echo ($default_address['state'] ?? '') === 'Metro Manila' ? 'selected' : ''; ?>>Metro Manila</option>
                                <option value="Mindanao" <?php echo ($default_address['state'] ?? '') === 'Mindanao' ? 'selected' : ''; ?>>Mindanao</option>
                                <option value="North Luzon" <?php echo ($default_address['state'] ?? '') === 'North Luzon' ? 'selected' : ''; ?>>North Luzon</option>
                                <option value="South Luzon" <?php echo ($default_address['state'] ?? '') === 'South Luzon' ? 'selected' : ''; ?>>South Luzon</option>
                                <option value="Visayas" <?php echo ($default_address['state'] ?? '') === 'Visayas' ? 'selected' : ''; ?>>Visayas</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-800 mb-2">Province *</label>
                                <input type="text" name="province" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                       value="<?php echo $default_address['state'] ?? ''; ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-800 mb-2">City *</label>
                                <input type="text" name="city" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                       value="<?php echo $default_address['city'] ?? ''; ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-800 mb-2">Barangay *</label>
                                <input type="text" name="barangay" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                       value="<?php echo $default_address['city'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-800 mb-2">Postal Code *</label>
                                <input type="text" name="postal_code" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                       value="<?php echo $default_address['postal_code'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-800 mb-2">Street Name, Building, House No. *</label>
                            <input type="text" name="street_address" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                   value="<?php echo $default_address['address_line1'] ?? ''; ?>">
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeAddressModal()" 
                                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                                Save Address
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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

    <script>
        // Address Modal Functions
        function openAddressModal() {
            document.getElementById('addressModal').classList.remove('hidden');
        }

        function closeAddressModal() {
            document.getElementById('addressModal').classList.add('hidden');
        }

        // Save Address Function
        function saveAddress(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const addressData = {
                full_name: formData.get('full_name'),
                phone: formData.get('phone'),
                region: formData.get('region'),
                province: formData.get('province'),
                city: formData.get('city'),
                barangay: formData.get('barangay'),
                postal_code: formData.get('postal_code'),
                street_address: formData.get('street_address'),
                is_default: 1
            };
            
            fetch('save_address.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(addressData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAddressModal();
                    location.reload(); // Reload to show updated address
                } else {
                    alert('Error saving address: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving address');
            });
        }

        // Promo Code Functions
        function applyPromoCode() {
            const promoCode = document.getElementById('promo_code').value.trim();
            if (!promoCode) {
                document.getElementById('promo_message').textContent = 'Please enter a promo code';
                document.getElementById('promo_message').className = 'mt-2 text-sm text-red-600';
                return;
            }
            
            fetch('apply_promo_code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ promo_code: promoCode })
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('promo_message');
                if (data.success) {
                    messageDiv.textContent = data.message;
                    messageDiv.className = 'mt-2 text-sm text-green-600';
                    
                    // Show discount
                    document.getElementById('promo_discount').classList.remove('hidden');
                    document.getElementById('discount_amount').textContent = data.discount_amount;
                    
                    // Update total
                    const currentTotal = parseFloat(document.getElementById('total_amount').textContent.replace(',', ''));
                    const newTotal = currentTotal - parseFloat(data.discount_amount);
                    document.getElementById('total_amount').textContent = newTotal.toFixed(2);
                } else {
                    messageDiv.textContent = data.message;
                    messageDiv.className = 'mt-2 text-sm text-red-600';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('promo_message').textContent = 'Error applying promo code';
                document.getElementById('promo_message').className = 'mt-2 text-sm text-red-600';
            });
        }

        // Checkout Processing
        function processCheckout() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            if (!paymentMethod) {
                alert('Please select a payment method');
                return;
            }
            
            // Check if address exists
            <?php if (!$default_address): ?>
                alert('Please add a delivery address before proceeding');
                return;
            <?php endif; ?>
            
            if (confirm('Are you sure you want to complete this order?')) {
                const checkoutData = {
                    payment_method: paymentMethod,
                    promo_code: document.getElementById('promo_code').value.trim(),
                    selected_items: <?php echo json_encode($selected_items); ?>
                };
                
                fetch('process_checkout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(checkoutData)
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Checkout response:', data);
                    if (data.success) {
                        if (paymentMethod === 'paypal') {
                            // Redirect to PayPal
                            window.location.href = data.paypal_url;
           } else {
               // Redirect to success page
               // Use custom_order_id if available, otherwise use order_id
               const redirectId = data.custom_order_id || data.order_id;
               window.location.href = 'order_success.php?order_id=' + redirectId;
           }
                    } else {
                        alert('Error processing order: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error processing order');
                });
            }
        }
    </script>
</body>
</html>
