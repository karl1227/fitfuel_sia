<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';

// PayPal payment was cancelled by user
$paypal_order_id = $_GET['token'] ?? '';

if (!empty($paypal_order_id)) {
    try {
        require_once 'config/database.php';
        $pdo = getDBConnection();
        
        // Find and cancel the order
        $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE payment_reference = ? AND user_id = ?");
        $orderStmt->execute([$paypal_order_id, $_SESSION['user_id']]);
        $order = $orderStmt->fetch();
        
        if ($order) {
            // Update order status to cancelled
            $updateStmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?");
            $updateStmt->execute([$order['order_id']]);
        }
        
    } catch (Exception $e) {
        error_log("PayPal cancel handler error: " . $e->getMessage());
    }
}

// Redirect back to checkout with error message
header('Location: checkout.php?error=payment_cancelled');
exit();
?>
