<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';
require_once 'config/paypal_service.php';

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
    
    // Find the order by PayPal order ID
    $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE payment_reference = ? AND user_id = ?");
    $orderStmt->execute([$paypal_order_id, $user_id]);
    $order = $orderStmt->fetch();
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    // Capture the PayPal payment
    $paypalService = new PayPalService();
    $captureResult = $paypalService->captureOrder($paypal_order_id);
    
    if ($captureResult['status'] === 'COMPLETED') {
        // Update order status to processing
        $updateStmt = $pdo->prepare("UPDATE orders SET status = 'processing', payment_status = 'paid' WHERE order_id = ?");
        $updateStmt->execute([$order['order_id']]);
        
        // Get payment details
        $payment = $captureResult['purchase_units'][0]['payments']['captures'][0];
        
        // Store payment details
        $paymentStmt = $pdo->prepare("UPDATE orders SET payment_reference = ? WHERE order_id = ?");
        $paymentStmt->execute([$payment['id'], $order['order_id']]);
        
        // Redirect to success page
        header('Location: order_success.php?order_id=' . $order['custom_order_id']);
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
