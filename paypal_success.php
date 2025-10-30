<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';
require_once 'config/paypal_service.php';
require_once 'config/stock_control.php';
require_once 'config/notifications_helper.php';
require_once 'config/audit_logger.php';

$user_id = (int) $_SESSION['user_id'];

// Get PayPal order details from URL parameters
$paypal_order_id = $_GET['token'] ?? '';
$payer_id = $_GET['PayerID'] ?? '';

if (empty($paypal_order_id) || empty($payer_id)) {
    header('Location: checkout.php?error=missing_paypal_data');
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Lookup pending checkout context from session (order not yet created)
    $pending = $_SESSION['pending_paypal'][$paypal_order_id] ?? null;
    if (!$pending || (int)$pending['user_id'] !== $user_id) {
        throw new Exception('Pending checkout not found');
    }
    
    // Capture the PayPal payment
    $paypalService = new PayPalService();
    $captureResult = $paypalService->captureOrder($paypal_order_id);
    
    if ($captureResult['status'] === 'COMPLETED') {
        // Extract payment details
        $payment = $captureResult['purchase_units'][0]['payments']['captures'][0];
        
        // Create the order now that payment is approved
        $pdo->beginTransaction();
        
        // Generate unique custom order ID
        $order_date = date('Ymd');
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $max_attempts = 10; $attempt = 0; $exists = false;
        do {
            $random_code = '';
            for ($i = 0; $i < 5; $i++) { $random_code .= $characters[rand(0, strlen($characters) - 1)]; }
            $custom_order_id = "FF-{$order_date}-{$random_code}";
            $check_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE custom_order_id = ?");
            $check_stmt->execute([$custom_order_id]);
            $exists = $check_stmt->fetch()['count'] > 0;
            $attempt++;
        } while ($exists && $attempt < $max_attempts);
        if ($exists) {
            $timestamp = substr(str_replace('.', '', microtime(true)), -5);
            $custom_order_id = "FF-{$order_date}-{$timestamp}";
        }
        
        $shipping_address = $pending['shipping_address'];
        $shipping_address_text = json_encode([
            'full_name' => $shipping_address['full_name'],
            'phone' => $shipping_address['phone'],
            'address_line1' => $shipping_address['address_line1'],
            'address_line2' => $shipping_address['address_line2'] ?? '',
            'address_line3' => $shipping_address['address_line3'] ?? '',
            'city' => $shipping_address['city_muni_name'] ?? $shipping_address['city'] ?? '',
            'state' => $shipping_address['province_name'] ?? $shipping_address['state'] ?? '',
            'postal_code' => $shipping_address['postal_code'] ?? '',
            'country' => $shipping_address['country'] ?? 'Philippines'
        ]);
        
        $order_sql = "INSERT INTO orders (user_id, status, payment_method, payment_status, shipping_address, total_amount, estimated_delivery_date, custom_order_id, payment_reference) VALUES (?, 'processing', 'paypal', 'paid', ?, ?, DATE_ADD(NOW(), INTERVAL 3 DAY), ?, ?)";
        $order_stmt = $pdo->prepare($order_sql);
        $order_stmt->execute([$user_id, $shipping_address_text, $pending['total_amount'], $custom_order_id, $payment['id']]);
        $order_id = (int)$pdo->lastInsertId();
        
        // Items
        foreach ($pending['cart_items'] as $item) {
            $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)")
                ->execute([$order_id, (int)$item['product_id'], (int)$item['quantity'], (float)$item['price']]);
        }
        
        // Promo usage
        if (!empty($pending['promo']) && $pending['promo']['discount_amount'] > 0) {
            $pdo->prepare("INSERT INTO order_promo_codes (order_id, promo_id, discount_amount) VALUES (?,?,?)")
                ->execute([$order_id, (int)$pending['promo']['promo_id'], (float)$pending['promo']['discount_amount']]);
            $pdo->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE promo_id = ?")
                ->execute([(int)$pending['promo']['promo_id']]);
        }
        
        // Clear cart
        if (!empty($pending['selected_items'])) {
            $placeholders = str_repeat('?,', count($pending['selected_items']) - 1) . '?';
            $clear_sql = "DELETE ci FROM cart_items ci JOIN cart c ON ci.cart_id = c.cart_id WHERE c.user_id = ? AND ci.cart_item_id IN ($placeholders)";
            $params = array_merge([$user_id], $pending['selected_items']);
            $pdo->prepare($clear_sql)->execute($params);
        }
        
        // Deduct stock
        deductStockImmediately($pdo, $pending['cart_items'], $order_id, $user_id);
        
        $pdo->commit();
        
        // Log + notify
        try {
            $auditLogger = new AuditLogger();
            $auditLogger->logOrderCreate($order_id, [
                'user_id' => $user_id,
                'payment_method' => 'paypal',
                'total_amount' => $pending['total_amount'],
                'custom_order_id' => $custom_order_id,
                'items_count' => count($pending['cart_items'])
            ]);
            createNotification($user_id, 'order_status', 'Payment Received', 'Your order ' . $custom_order_id . ' has been paid.', 'order_details.php?order_id=' . $order_id);
        } catch (Throwable $e) {}
        
        unset($_SESSION['pending_paypal'][$paypal_order_id]);
        header('Location: order_success.php?order_id=' . $custom_order_id);
        exit();
    } else {
        throw new Exception('Payment capture failed');
    }
    
} catch (Exception $e) {
    error_log("PayPal success handler error: " . $e->getMessage());
    header('Location: checkout.php?error=payment_failed&message=' . urlencode($e->getMessage()));
    exit();
}
?>
