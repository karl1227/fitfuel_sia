<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';

$pdo = getDBConnection();

// Get order ID from URL parameter
$order_id = (int)($_GET['id'] ?? 0);

if (!$order_id) {
    header('Location: orders.php');
    exit;
}

// Fetch order details with customer information
$orderQuery = "
    SELECT 
        o.*,
        u.username,
        u.email
    FROM orders o 
    JOIN users u ON u.user_id = o.user_id 
    WHERE o.order_id = ?
";

$orderStmt = $pdo->prepare($orderQuery);
$orderStmt->execute([$order_id]);
$order = $orderStmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Fetch order items with product details
$itemsQuery = "
    SELECT 
        oi.*,
        p.name as product_name,
        p.images,
        p.description as product_description,
        c.name as category_name,
        sc.name as subcategory_name
    FROM order_items oi
    JOIN products p ON p.product_id = oi.product_id
    LEFT JOIN categories c ON c.category_id = p.category_id
    LEFT JOIN subcategories sc ON sc.subcategory_id = p.subcategory_id
    WHERE oi.order_id = ?
";

$itemsStmt = $pdo->prepare($itemsQuery);
$itemsStmt->execute([$order_id]);
$order_items = $itemsStmt->fetchAll();

// Handle form submissions
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_status') {
            $new_status = $_POST['status'] ?? '';
            $payment_status = $_POST['payment_status'] ?? '';
            $payment_method = $_POST['payment_method'] ?? '';
            
            $allowed_statuses = ['pending','processing','shipped','delivered','cancelled','returned','refunded'];
            $allowed_payment_statuses = ['pending','paid','refunded','failed'];
            
            if (in_array($new_status, $allowed_statuses) && in_array($payment_status, $allowed_payment_statuses)) {
                $updateStmt = $pdo->prepare("
                    UPDATE orders 
                    SET status = ?, payment_status = ?, payment_method = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE order_id = ?
                ");
                $updateStmt->execute([$new_status, $payment_status, $payment_method, $order_id]);
                $message = 'Order status updated successfully.';
                
                // Refresh order data
                $orderStmt->execute([$order_id]);
                $order = $orderStmt->fetch();
            } else {
                $error = 'Invalid status values.';
            }
        }
        
        if ($action === 'cancel_order') {
            if (!in_array($order['status'], ['processing','shipped','delivered'])) {
                $cancelStmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE order_id = ?");
                $cancelStmt->execute([$order_id]);
                $message = 'Order cancelled successfully.';
                
                // Refresh order data
                $orderStmt->execute([$order_id]);
                $order = $orderStmt->fetch();
            } else {
                $error = 'Cannot cancel orders that are already processing, shipped, or delivered.';
            }
        }
        
    } catch (Exception $e) {
        $error = 'An error occurred: ' . $e->getMessage();
    }
}

// Parse shipping address
$shipping_address = [];
if ($order['shipping_address']) {
    try {
        $shipping_address = json_decode($order['shipping_address'], true) ?: [];
    } catch (Exception $e) {
        $shipping_address = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order #<?php echo htmlspecialchars($order['custom_order_id'] ?? $order['order_id']); ?> - Admin</title>
    <link rel="icon" href="../img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .sidebar-item.active { background-color: #f3f4f6; border-right: 3px solid #000; }
        .sidebar-item:hover { background-color: #f9fafb; }
        .badge { padding: 2px 8px; border-radius: 9999px; font-size: 12px; }
        .product-image { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
    </style>
    <script>
        function toggleUserMenu(){
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const userButton = event.target.closest('[onclick="toggleUserMenu()"]');
            if (!userButton && userMenu && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
            }
        });
        
        function generateInvoice() {
            window.open('generate_invoice.php?id=<?php echo $order_id; ?>', '_blank');
        }
        
        function confirmCancel() {
            return confirm('Are you sure you want to cancel this order? This action cannot be undone.');
        }
    </script>
</head>
<body class="font-body bg-gray-50">
    <header class="bg-black text-white fixed top-0 left-0 right-0 z-50 h-16 flex items-center justify-between px-6">
        <div class="flex items-center space-x-3">
            <img src="../img/LOGO-Fitfuel.png" alt="FitFuel Logo" class="w-8 h-8 object-contain">
            <div class="w-px h-6 bg-white"></div>
            <h1 class="text-xl font-bold uppercase">Admin</h1>
        </div>
        <div class="flex items-center space-x-4">
            <button class="p-2 hover:bg-gray-800 rounded-lg transition-colors relative">
                <i class="fas fa-bell text-xl"></i>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
            </button>
            <div class="relative">
                <button onclick="toggleUserMenu()" class="flex items-center space-x-2 p-2 hover:bg-gray-800 rounded-lg transition-colors">
                    <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <span class="hidden md:block text-sm"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                    <div class="px-4 py-2 border-b border-gray-200">
                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                        <span class="inline-block mt-1 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full"><?php echo ucfirst($_SESSION['role'] ?? 'admin'); ?></span>
                    </div>
                    <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user-cog mr-3 text-gray-400"></i>
                        Profile Settings
                    </a>
                    <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-cog mr-3 text-gray-400"></i>
                        Preferences
                    </a>
                    <div class="border-t border-gray-200 mt-2"></div>
                    <a href="../logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt mr-3 text-red-500"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <aside class="fixed left-0 top-16 bottom-0 w-64 bg-white border-r border-gray-200 overflow-y-auto">
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="dashboard.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-th-large text-gray-600"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="product.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-cube text-gray-600"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li>
                    <a href="orders.php" class="sidebar-item active flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-shopping-cart text-gray-600"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li>
                    <a href="inventory.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-archive text-gray-600"></i>
                        <span>Inventory</span>
                    </a>
                </li>
                <li>
                    <a href="users.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-users text-gray-600"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-chart-line text-gray-600"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="content.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-file-alt text-gray-600"></i>
                        <span>Contents</span>
                    </a>
                </li>
                <li>
                    <a href="audit_logs.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-history text-gray-600"></i>
                        <span>Audit Trail</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-bell text-gray-600"></i>
                        <span>Notifications</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-cog text-gray-600"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>
    
    <main class="ml-64 pt-24 pb-6 px-6">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="orders.php" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Orders
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">View Order</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Order Header -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Order #<?php echo htmlspecialchars($order['custom_order_id'] ?? $order['order_id']); ?>
                    </h1>
                    <p class="text-gray-600">Created on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-600">Order:</span>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold <?php 
                            $statusClass = '';
                            switch($order['status']) {
                                case 'delivered':
                                    $statusClass = 'bg-green-100 text-green-800 border border-green-200';
                                    break;
                                case 'processing':
                                    $statusClass = 'bg-blue-100 text-blue-800 border border-blue-200';
                                    break;
                                case 'shipped':
                                    $statusClass = 'bg-purple-100 text-purple-800 border border-purple-200';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'bg-red-100 text-red-800 border border-red-200';
                                    break;
                                case 'returned':
                                    $statusClass = 'bg-orange-100 text-orange-800 border border-orange-200';
                                    break;
                                case 'refunded':
                                    $statusClass = 'bg-gray-100 text-gray-800 border border-gray-200';
                                    break;
                                default:
                                    $statusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                                    break;
                            }
                            echo $statusClass;
                        ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-600">Payment:</span>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold <?php 
                            $paymentStatusClass = '';
                            switch($order['payment_status']) {
                                case 'paid':
                                    $paymentStatusClass = 'bg-green-100 text-green-800 border border-green-200';
                                    break;
                                case 'pending':
                                    $paymentStatusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                                    break;
                                case 'refunded':
                                    $paymentStatusClass = 'bg-gray-100 text-gray-800 border border-gray-200';
                                    break;
                                default:
                                    $paymentStatusClass = 'bg-red-100 text-red-800 border border-red-200';
                                    break;
                            }
                            echo $paymentStatusClass;
                        ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <button onclick="generateInvoice()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-file-pdf mr-2"></i>
                        View Invoice
                    </button>
                    <?php if (!in_array($order['status'], ['processing','shipped','delivered','cancelled'])): ?>
                    <form method="post" class="inline" onsubmit="return confirmCancel();">
                        <input type="hidden" name="action" value="cancel_order">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Cancel Order
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <a href="orders.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Orders
                </a>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Order Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Customer Information -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-user mr-2"></i>
                        Customer Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Customer Name</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($order['username']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($order['email']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <?php 
                                $paymentMethod = $order['payment_method'] ?? 'Not specified';
                                echo htmlspecialchars(ucfirst(str_replace('_', ' ', $paymentMethod)));
                                ?>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Order Date</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Shipping Address -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-shipping-fast mr-2"></i>
                        Shipping Address
                    </h2>
                    <?php if (!empty($shipping_address)): ?>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-900">
                            <strong><?php echo htmlspecialchars($shipping_address['full_name'] ?? ''); ?></strong>
                        </p>
                        <?php if (!empty($shipping_address['phone'])): ?>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($shipping_address['phone']); ?></p>
                        <?php endif; ?>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($shipping_address['address'] ?? ''); ?></p>
                        <p class="text-sm text-gray-600">
                            <?php echo htmlspecialchars($shipping_address['city'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($shipping_address['state'] ?? ''); ?> 
                            <?php echo htmlspecialchars($shipping_address['postal_code'] ?? ''); ?>
                        </p>
                    </div>
                    <?php else: ?>
                    <p class="text-sm text-gray-500">No shipping address provided</p>
                    <?php endif; ?>
                </div>
                
                <!-- Order Items -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-shopping-bag mr-2"></i>
                        Order Items
                    </h2>
                    <div class="space-y-3">
                        <?php foreach ($order_items as $item): ?>
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <?php 
                            $images = json_decode($item['images'] ?? '[]', true);
                            $image_url = !empty($images) ? '../' . $images[0] : '../img/placeholder-product.jpg';
                            ?>
                            <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 class="w-16 h-16 object-cover rounded-lg border border-gray-200">
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    <?php echo htmlspecialchars($item['category_name'] ?? ''); ?>
                                    <?php if ($item['subcategory_name']): ?>
                                        • <?php echo htmlspecialchars($item['subcategory_name']); ?>
                                    <?php endif; ?>
                                </p>
                                <div class="flex items-center space-x-4 text-sm">
                                    <span class="text-gray-600">
                                        <span class="font-medium">Qty:</span> <?php echo (int)$item['quantity']; ?>
                                    </span>
                                    <span class="text-gray-600">
                                        <span class="font-medium">Unit Price:</span> ₱<?php echo number_format((float)$item['price'], 2); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-gray-900">
                                    ₱<?php echo number_format((float)$item['price'] * (int)$item['quantity'], 2); ?>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Total</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Order Total -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <?php 
                        // Calculate actual subtotal from items
                        $calculated_subtotal = 0;
                        foreach ($order_items as $item) {
                            $calculated_subtotal += (float)$item['price'] * (int)$item['quantity'];
                        }
                        
                        // Calculate shipping fee (total - subtotal)
                        $shipping_fee = (float)$order['total_amount'] - $calculated_subtotal;
                        ?>
                        <div class="flex justify-between items-center">
                            <div class="text-right">
                                <div class="text-sm text-gray-600 mb-1">Subtotal</div>
                                <div class="text-sm text-gray-600 mb-1">Shipping</div>
                                <div class="text-lg font-semibold text-gray-900 mt-2">Total</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900 mb-1">₱<?php echo number_format($calculated_subtotal, 2); ?></div>
                                <div class="text-sm font-medium text-gray-900 mb-1">₱<?php echo number_format($shipping_fee, 2); ?></div>
                                <div class="text-xl font-bold text-gray-900 mt-2">₱<?php echo number_format((float)$order['total_amount'], 2); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Management -->
            <div class="space-y-6">
                <!-- Order Summary -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-receipt mr-2"></i>
                        Order Summary
                    </h2>
                    <div class="space-y-3">
                        <?php 
                        // Calculate actual subtotal from items
                        $summary_subtotal = 0;
                        foreach ($order_items as $item) {
                            $summary_subtotal += (float)$item['price'] * (int)$item['quantity'];
                        }
                        
                        // Calculate shipping fee (total - subtotal)
                        $summary_shipping = (float)$order['total_amount'] - $summary_subtotal;
                        ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="text-gray-900">₱<?php echo number_format($summary_subtotal, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Shipping</span>
                            <span class="text-gray-900">₱<?php echo number_format($summary_shipping, 2); ?></span>
                        </div>
                        <div class="border-t pt-3">
                            <div class="flex justify-between">
                                <span class="text-base font-semibold text-gray-900">Total</span>
                                <span class="text-base font-semibold text-gray-900">₱<?php echo number_format((float)$order['total_amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Status Management -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-cog mr-2"></i>
                        Order Management
                    </h2>
                    <form method="post" class="space-y-4">
                        <input type="hidden" name="action" value="update_status">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Order Status</label>
                            <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="returned" <?php echo $order['status'] === 'returned' ? 'selected' : ''; ?>>Returned</option>
                                <option value="refunded" <?php echo $order['status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                            <select name="payment_status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="refunded" <?php echo $order['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                            <select name="payment_method" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Method</option>
                                <option value="cod" <?php echo $order['payment_method'] === 'cod' ? 'selected' : ''; ?>>Cash on Delivery</option>
                                <option value="paypal" <?php echo $order['payment_method'] === 'paypal' ? 'selected' : ''; ?>>PayPal</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            Update Order
                        </button>
                    </form>
                </div>
                
                <!-- Order Information -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-info-circle mr-2"></i>
                        Order Information
                    </h2>
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-gray-600">Order ID:</span>
                            <span class="text-gray-900 font-mono"><?php echo htmlspecialchars($order['custom_order_id'] ?? $order['order_id']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Created:</span>
                            <span class="text-gray-900"><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Last Updated:</span>
                            <span class="text-gray-900"><?php echo date('M j, Y g:i A', strtotime($order['updated_at'])); ?></span>
                        </div>
                        <?php if ($order['estimated_delivery_date']): ?>
                        <div>
                            <span class="text-gray-600">Estimated Delivery:</span>
                            <span class="text-gray-900"><?php echo date('M j, Y', strtotime($order['estimated_delivery_date'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
