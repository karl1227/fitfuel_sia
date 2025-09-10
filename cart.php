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
    
    // Get user's cart and items
    $cart_sql = "SELECT c.cart_id, ci.cart_item_id, ci.product_id, ci.quantity, 
                        p.name, p.price, p.images
                 FROM cart c 
                 LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id 
                 LEFT JOIN products p ON ci.product_id = p.product_id 
                 WHERE c.user_id = ? AND ci.cart_item_id IS NOT NULL
                 ORDER BY ci.added_at DESC";
    
    $cart_stmt = $pdo->prepare($cart_sql);
    $cart_stmt->execute([$user_id]);
    $cart_items = $cart_stmt->fetchAll();
    
    // Calculate totals
    $subtotal = 0;
    $shipping = 100.00; // Fixed shipping cost
    $total_items = 0;
    
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $total_items += $item['quantity'];
    }
    
    $total = $subtotal + $shipping;
    
} catch (PDOException $e) {
    $cart_items = [];
    $subtotal = 0;
    $shipping = 100.00;
    $total = 0;
    $total_items = 0;
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
    <title>Cart - FitFuel</title>
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
                <div class="hidden md:flex space-x-8">
                    <a href="shop.php" class="font-medium text-white hover:text-emerald-600 transition-colors">Shop</a>
                    <a href="shop.php" class="font-medium text-white hover:text-emerald-600 transition-colors">Categories</a>
                    <a href="#" class="font-medium text-white hover:text-emerald-600 transition-colors">About</a>
                </div>

                <!-- Search and Icons -->
                <div class="flex items-center space-x-4">
                    <!-- Search Bar -->
                    <div class="relative hidden md:block">
                        <input type="text" placeholder="Search products..." 
                               class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                    </div>

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

    <!-- Breadcrumbs -->
    <div class="bg-gray-50 py-3">
        <div class="container mx-auto px-4">
            <nav class="text-sm">
                <a href="index.html" class="text-gray-500 hover:text-emerald-600">Home</a>
                <span class="mx-2 text-gray-400">></span>
                <a href="shop.php" class="text-gray-500 hover:text-emerald-600">Shop</a>
                <span class="mx-2 text-gray-400">></span>
                <span class="text-gray-700 font-medium">Cart</span>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-slate-800 mb-2">Your Cart</h1>
            <p class="text-lg text-slate-600">Review your items and proceed to checkout</p>
        </div>

        <?php if (empty($cart_items)): ?>
            <!-- Empty Cart -->
            <div class="text-center py-16">
                <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-semibold text-slate-600 mb-2">Your cart is empty</h3>
                <p class="text-slate-500 mb-6">Add some products to get started</p>
                <a href="shop.php" class="bg-emerald-600 text-white px-8 py-3 rounded-lg hover:bg-emerald-700 transition-colors">
                    Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" id="select-all" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                <h2 class="text-xl font-semibold text-slate-800">Items in your cart</h2>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-500"><?php echo $total_items; ?> items</span>
                                <button onclick="removeSelected()" id="remove-selected-btn" class="text-red-500 hover:text-red-700 text-sm font-medium flex items-center hidden">
                                    <i class="fas fa-trash mr-1"></i>
                                    Remove Selected
                                </button>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <?php foreach ($cart_items as $item): ?>
                                <?php 
                                $images = json_decode($item['images'], true);
                                $image_url = !empty($images) ? $images[0] : 'img/placeholder.svg';
                                ?>
                                <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                                    <!-- Checkbox -->
                                    <input type="checkbox" class="item-checkbox w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500" 
                                           data-item-id="<?php echo $item['cart_item_id']; ?>" 
                                           data-price="<?php echo $item['price']; ?>">
                                    
                                    <!-- Product Image -->
                                    <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="w-16 h-16 object-cover rounded-lg">
                                    
                                    <!-- Product Info -->
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-slate-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p class="text-emerald-600 font-semibold">₱<?php echo number_format($item['price'], 2); ?></p>
                                    </div>
                                    
                                    <!-- Quantity Controls -->
                                    <div class="flex items-center space-x-2">
                                        <button onclick="updateQuantity(<?php echo $item['cart_item_id']; ?>, -1)" 
                                                class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">
                                            <i class="fas fa-minus text-xs"></i>
                                        </button>
                                        <span class="quantity-display w-8 text-center font-semibold" 
                                              data-item-id="<?php echo $item['cart_item_id']; ?>"><?php echo $item['quantity']; ?></span>
                                        <button onclick="updateQuantity(<?php echo $item['cart_item_id']; ?>, 1)" 
                                                class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">
                                            <i class="fas fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Remove Button -->
                                    <button onclick="removeItem(<?php echo $item['cart_item_id']; ?>)" 
                                            class="text-red-500 hover:text-red-700 p-2">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Continue Shopping -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <a href="shop.php" class="inline-flex items-center text-emerald-600 hover:text-emerald-700 font-semibold">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-lg p-6 sticky top-24">
                        <h2 class="text-xl font-semibold text-slate-800 mb-6">Order Summary</h2>
                        
                        <!-- Promo Code -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Promo Code</label>
                            <div class="flex">
                                <input type="text" id="promo-code" placeholder="Enter promo code" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <button onclick="applyPromoCode()" 
                                        class="px-4 py-2 bg-gray-800 text-white rounded-r-lg hover:bg-gray-700 transition-colors">
                                    Apply
                                </button>
                            </div>
                            <div id="promo-message" class="mt-2 text-sm hidden"></div>
                        </div>

                        <!-- Order Totals -->
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-semibold" id="subtotal">₱<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-semibold" id="shipping">₱<?php echo number_format($shipping, 2); ?></span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-3">
                                <span>Total</span>
                                <span id="total">₱<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <button onclick="proceedToCheckout()" 
                                class="w-full bg-black text-white py-3 rounded-lg font-semibold hover:bg-gray-800 transition-colors mb-4">
                            Checkout
                        </button>

                        <!-- Security Message -->
                        <div class="flex items-center justify-center text-sm text-gray-500">
                            <i class="fas fa-lock mr-2"></i>
                            <span>Secure checkout with SSL encryption</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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


    <script>
        // Update quantity function
        function updateQuantity(cartItemId, change) {
            const quantityDisplay = document.querySelector(`[data-item-id="${cartItemId}"].quantity-display`);
            const currentQuantity = parseInt(quantityDisplay.textContent);
            const newQuantity = currentQuantity + change;
            
            if (newQuantity < 1) {
                removeItem(cartItemId);
                return;
            }
            
            // Update display immediately
            quantityDisplay.textContent = newQuantity;
            updateTotals();
            
            // Update cart counter in header
            updateCartCounter();
            
            // Send request to server
            fetch('update_cart_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart_item_id: cartItemId,
                    quantity: newQuantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Server error:', data.message);
                    // Optionally reload page to sync with server
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error updating quantity:', error);
                // Optionally reload page to sync with server
                window.location.reload();
            });
        }

        // Update cart counter in header
        function updateCartCounter() {
            // Calculate total items in cart
            let totalItems = 0;
            document.querySelectorAll('.quantity-display').forEach(display => {
                totalItems += parseInt(display.textContent);
            });
            
            // Update the cart counter in header
            const cartBadge = document.querySelector('a[href="cart.php"] .bg-emerald-500');
            if (cartBadge) {
                if (totalItems > 0) {
                    cartBadge.textContent = totalItems;
                    cartBadge.style.display = 'flex';
                } else {
                    cartBadge.style.display = 'none';
                }
            }
            
            // Also update the item count text in cart page
            const itemCountSpan = document.querySelector('.text-sm.text-gray-500');
            if (itemCountSpan) {
                itemCountSpan.textContent = `${totalItems} items`;
            }
        }

        // Remove item function
        function removeItem(cartItemId) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }
            
            console.log('Removing cart item:', cartItemId);
            
            // Find the checkbox for this item (same approach as removeSelected)
            const checkbox = document.querySelector(`[data-item-id="${cartItemId}"].item-checkbox`);
            
            if (!checkbox) {
                console.error('Checkbox not found for cart item:', cartItemId);
                alert('Error: Item not found');
                return;
            }
            
            // Remove the item from the DOM immediately (same as removeSelected)
            const itemElement = checkbox.closest('.flex');
            itemElement.remove();
            
            // Update totals and select all state
            updateTotals();
            updateSelectAll();
            
            // Update item count in header (same approach as removeSelected)
            const itemCountSpan = document.querySelector('.text-sm.text-gray-500');
            if (itemCountSpan) {
                const remainingItems = document.querySelectorAll('.item-checkbox').length;
                itemCountSpan.textContent = `${remainingItems} items`;
            }
            
            // Send request to server
            fetch('remove_cart_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart_item_id: cartItemId
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Server response:', data);
                if (!data.success) {
                    console.error('Server error:', data.message);
                    alert('Error removing item: ' + data.message);
                    // Reload page to sync with server
                    window.location.reload();
                } else {
                    console.log('Item removed successfully from server');
                }
                
                // Reload page if cart is empty
                if (document.querySelectorAll('.item-checkbox').length === 0) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error removing item:', error);
                alert('Network error removing item');
                // Reload page to sync with server
                window.location.reload();
            });
        }

        // Update totals based on checked items
        function updateTotals() {
            let subtotal = 0;
            let totalItems = 0;
            
            document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
                const price = parseFloat(checkbox.dataset.price);
                const quantityElement = document.querySelector(`[data-item-id="${checkbox.dataset.itemId}"].quantity-display`);
                const quantity = parseInt(quantityElement.textContent);
                
                subtotal += price * quantity;
                totalItems += quantity;
            });
            
            const shipping = 100.00;
            const total = subtotal + shipping;
            
            document.getElementById('subtotal').textContent = '₱' + subtotal.toFixed(2);
            document.getElementById('total').textContent = '₱' + total.toFixed(2);
        }

        // Apply promo code
        function applyPromoCode() {
            const promoCode = document.getElementById('promo-code').value.trim();
            const messageEl = document.getElementById('promo-message');
            
            if (!promoCode) {
                messageEl.textContent = 'Please enter a promo code';
                messageEl.className = 'mt-2 text-sm text-red-500';
                messageEl.classList.remove('hidden');
                return;
            }
            
            // Simple promo code logic (you can expand this)
            const validCodes = {
                'FITFUEL10': 0.10,
                'WELCOME20': 0.20,
                'SAVE15': 0.15
            };
            
            if (validCodes[promoCode.toUpperCase()]) {
                const discount = validCodes[promoCode.toUpperCase()];
                const currentSubtotal = parseFloat(document.getElementById('subtotal').textContent.replace('₱', '').replace(',', ''));
                const discountAmount = currentSubtotal * discount;
                const newSubtotal = currentSubtotal - discountAmount;
                const newTotal = newSubtotal + 100.00;
                
                document.getElementById('subtotal').textContent = '₱' + newSubtotal.toFixed(2);
                document.getElementById('total').textContent = '₱' + newTotal.toFixed(2);
                
                messageEl.textContent = `Promo code applied! You saved ₱${discountAmount.toFixed(2)}`;
                messageEl.className = 'mt-2 text-sm text-green-500';
                messageEl.classList.remove('hidden');
            } else {
                messageEl.textContent = 'Invalid promo code';
                messageEl.className = 'mt-2 text-sm text-red-500';
                messageEl.classList.remove('hidden');
            }
        }

        // Proceed to checkout
        function proceedToCheckout() {
            const checkedItems = document.querySelectorAll('.item-checkbox:checked');
            if (checkedItems.length === 0) {
                showNotification('Please select at least one item to checkout', 'error');
                return;
            }
            
            // For now, just show an alert. You can implement actual checkout later
            alert('Checkout functionality coming soon!');
        }


        // Remove selected items
        function removeSelected() {
            const checkedItems = document.querySelectorAll('.item-checkbox:checked');
            if (checkedItems.length === 0) {
                return;
            }
            
            if (!confirm(`Are you sure you want to remove ${checkedItems.length} selected item(s)?`)) {
                return;
            }
            
            // Remove items from DOM immediately
            const cartItemIds = [];
            checkedItems.forEach(checkbox => {
                const cartItemId = checkbox.dataset.itemId;
                cartItemIds.push(cartItemId);
                
                // Remove the item from the DOM
                const itemElement = checkbox.closest('.flex');
                itemElement.remove();
            });
            
            // Update totals and select all state
            updateTotals();
            updateSelectAll();
            
            // Update item count in header
            const itemCountSpan = document.querySelector('.text-sm.text-gray-500');
            const remainingItems = document.querySelectorAll('.item-checkbox').length;
            itemCountSpan.textContent = `${remainingItems} items`;
            
            // Send requests to server
            const promises = cartItemIds.map(cartItemId => {
                return fetch('remove_cart_item.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cart_item_id: cartItemId
                    })
                });
            });
            
            Promise.all(promises)
                .then(responses => Promise.all(responses.map(r => r.json())))
                .then(results => {
                    const successCount = results.filter(r => r.success).length;
                    if (successCount !== cartItemIds.length) {
                        console.error('Some items failed to remove from server');
                        // Optionally reload page to sync with server
                        window.location.reload();
                    }
                    
                    // Reload page if cart is empty
                    if (document.querySelectorAll('.item-checkbox').length === 0) {
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error removing items:', error);
                    // Optionally reload page to sync with server
                    window.location.reload();
                });
        }

        // Select all functionality
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('select-all');
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            const removeSelectedBtn = document.getElementById('remove-selected-btn');
            
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            if (selectAllCheckbox.checked) {
                removeSelectedBtn.classList.remove('hidden');
            } else {
                removeSelectedBtn.classList.add('hidden');
            }
            
            updateTotals();
        }

        // Update select all checkbox based on individual checkboxes
        function updateSelectAll() {
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            const selectAllCheckbox = document.getElementById('select-all');
            const removeSelectedBtn = document.getElementById('remove-selected-btn');
            
            const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
            const totalCount = itemCheckboxes.length;
            
            if (checkedCount === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
                removeSelectedBtn.classList.add('hidden');
            } else if (checkedCount === totalCount) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
                removeSelectedBtn.classList.remove('hidden');
            } else {
                selectAllCheckbox.indeterminate = true;
                selectAllCheckbox.checked = false;
                removeSelectedBtn.classList.remove('hidden');
            }
            
            updateTotals();
        }

        // Initialize checkbox change listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Select all checkbox
            document.getElementById('select-all').addEventListener('change', toggleSelectAll);
            
            // Individual item checkboxes
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectAll);
            });
            
            // Initial total calculation (should be 0 since nothing is checked)
            updateTotals();
        });
    </script>
</body>
</html>
