<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to checkout']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['payment_method'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

// Debug: Log the received data
error_log("Process checkout request received: " . json_encode($input));

$user_id = $_SESSION['user_id'];
$payment_method = $input['payment_method'];
$promo_code = isset($input['promo_code']) ? strtoupper(trim($input['promo_code'])) : '';
$selected_items = isset($input['selected_items']) ? $input['selected_items'] : [];

error_log("User ID: $user_id, Payment Method: $payment_method, Selected Items: " . json_encode($selected_items));

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    // Get user's selected cart items
    if (empty($selected_items)) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'No items selected']);
        exit();
    }
    
    $placeholders = str_repeat('?,', count($selected_items) - 1) . '?';
    $cart_sql = "SELECT c.cart_id, ci.cart_item_id, ci.product_id, ci.quantity, 
                        p.name, p.price, p.stock_quantity
                 FROM cart c 
                 LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id 
                 LEFT JOIN products p ON ci.product_id = p.product_id 
                 WHERE c.user_id = ? AND ci.cart_item_id IN ($placeholders)";
    
    $params = array_merge([$user_id], $selected_items);
    $cart_stmt = $pdo->prepare($cart_sql);
    $cart_stmt->execute($params);
    $cart_items = $cart_stmt->fetchAll();
    
    if (empty($cart_items)) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit();
    }
    
    // Check stock availability
    foreach ($cart_items as $item) {
        if ($item['quantity'] > $item['stock_quantity']) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Insufficient stock for ' . $item['name']]);
            exit();
        }
    }
    
    // Get user's default shipping address
    $address_sql = "SELECT * FROM shipping_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1";
    $address_stmt = $pdo->prepare($address_sql);
    $address_stmt->execute([$user_id]);
    $shipping_address = $address_stmt->fetch();
    
    if (!$shipping_address) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'No shipping address found']);
        exit();
    }
    
    // Calculate totals
    $subtotal = 0;
    $shipping_fee = 100.00; // Default shipping fee
    $discount_amount = 0;
    
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    // Apply promo code if provided
    if ($promo_code) {
        $promo_sql = "SELECT * FROM promo_codes 
                      WHERE code = ? AND is_active = 1 
                      AND valid_from <= NOW() AND valid_until >= NOW()
                      AND (usage_limit IS NULL OR used_count < usage_limit)
                      AND minimum_amount <= ?";
        
        $promo_stmt = $pdo->prepare($promo_sql);
        $promo_stmt->execute([$promo_code, $subtotal]);
        $promo = $promo_stmt->fetch();
        
        if ($promo) {
            if ($promo['discount_type'] === 'percentage') {
                $discount_amount = ($subtotal * $promo['discount_value']) / 100;
                if ($promo['maximum_discount'] && $discount_amount > $promo['maximum_discount']) {
                    $discount_amount = $promo['maximum_discount'];
                }
            } else {
                $discount_amount = $promo['discount_value'];
            }
            $discount_amount = round($discount_amount, 2);
        }
    }
    
    $total_amount = $subtotal + $shipping_fee - $discount_amount;
    
    // Generate custom order ID with shuffled characters
    $order_date = date('Ymd');
    
    // Generate unique random 5-character code (letters and numbers)
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $max_attempts = 10; // Prevent infinite loop
    $attempt = 0;
    
    do {
        $random_code = '';
        for ($i = 0; $i < 5; $i++) {
            $random_code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        $custom_order_id = "FF-{$order_date}-{$random_code}";
        
        // Check if this custom_order_id already exists
        $check_sql = "SELECT COUNT(*) as count FROM orders WHERE custom_order_id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$custom_order_id]);
        $exists = $check_stmt->fetch()['count'] > 0;
        
        $attempt++;
    } while ($exists && $attempt < $max_attempts);
    
    // If we couldn't generate a unique code, fall back to timestamp-based
    if ($exists) {
        $timestamp = substr(str_replace('.', '', microtime(true)), -5);
        $custom_order_id = "FF-{$order_date}-{$timestamp}";
    }
    
    // Create order with custom order ID
    $order_sql = "INSERT INTO orders 
                  (user_id, status, payment_method, payment_status, shipping_address, total_amount, estimated_delivery_date, custom_order_id) 
                  VALUES (?, 'pending', ?, 'pending', ?, ?, DATE_ADD(NOW(), INTERVAL 3 DAY), ?)";
    
    $shipping_address_text = json_encode([
        'full_name' => $shipping_address['full_name'],
        'phone' => $shipping_address['phone'],
        'address' => $shipping_address['address_line1'],
        'city' => $shipping_address['city'],
        'state' => $shipping_address['state'],
        'postal_code' => $shipping_address['postal_code']
    ]);
    
    $order_stmt = $pdo->prepare($order_sql);
    $result = $order_stmt->execute([
        $user_id,
        $payment_method,
        $shipping_address_text,
        $total_amount,
        $custom_order_id
    ]);
    
    if (!$result) {
        $pdo->rollBack();
        error_log("Failed to create order");
        echo json_encode(['success' => false, 'message' => 'Failed to create order']);
        exit();
    }
    
    $order_id = $pdo->lastInsertId();
    error_log("Order created successfully with ID: $order_id, Custom ID: $custom_order_id");
    
    // Create order items
    foreach ($cart_items as $item) {
        $order_item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $order_item_stmt = $pdo->prepare($order_item_sql);
        $result = $order_item_stmt->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);
        
        if (!$result) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to create order items']);
            exit();
        }
        
        // Deduct stock
        $stock_sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
        $stock_stmt = $pdo->prepare($stock_sql);
        $stock_stmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    // Record promo code usage if applicable
    if ($promo_code && $discount_amount > 0) {
        $promo_record_sql = "INSERT INTO order_promo_codes (order_id, promo_id, discount_amount) VALUES (?, ?, ?)";
        $promo_record_stmt = $pdo->prepare($promo_record_sql);
        $promo_record_stmt->execute([$order_id, $promo['promo_id'], $discount_amount]);
        
        // Update promo code usage count
        $update_promo_sql = "UPDATE promo_codes SET used_count = used_count + 1 WHERE promo_id = ?";
        $update_promo_stmt = $pdo->prepare($update_promo_sql);
        $update_promo_stmt->execute([$promo['promo_id']]);
    }
    
    // Clear selected items from cart
    $placeholders = str_repeat('?,', count($selected_items) - 1) . '?';
    $clear_cart_sql = "DELETE ci FROM cart_items ci 
                       JOIN cart c ON ci.cart_id = c.cart_id 
                       WHERE c.user_id = ? AND ci.cart_item_id IN ($placeholders)";
    $clear_params = array_merge([$user_id], $selected_items);
    $clear_cart_stmt = $pdo->prepare($clear_cart_sql);
    $clear_cart_stmt->execute($clear_params);
    
    $pdo->commit();
    
    error_log("Checkout completed successfully. Order ID: $order_id");
    
    // Handle payment method
    if ($payment_method === 'paypal') {
        // For PayPal integration, you would typically redirect to PayPal
        // For now, we'll just return success with a PayPal URL placeholder
        echo json_encode([
            'success' => true, 
            'message' => 'Order created successfully',
            'order_id' => $order_id,
            'custom_order_id' => $custom_order_id,
            'paypal_url' => 'https://paypal.com/checkout?order_id=' . $order_id
        ]);
    } else {
        // Cash on Delivery - order is created and pending
        echo json_encode([
            'success' => true, 
            'message' => 'Order created successfully',
            'order_id' => $order_id,
            'custom_order_id' => $custom_order_id
        ]);
    }
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("Database error in process_checkout.php: " . $e->getMessage());
    error_log("Error details: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
