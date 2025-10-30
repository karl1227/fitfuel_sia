<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';

// PayPal payment was cancelled by user; ensure we don't persist anything
$paypal_order_id = $_GET['token'] ?? '';

if (!empty($paypal_order_id)) {
    // Clear any pending checkout context so it doesn't leak
    if (isset($_SESSION['pending_paypal'][$paypal_order_id])) {
        unset($_SESSION['pending_paypal'][$paypal_order_id]);
    }
}

// Redirect back to checkout with error message
header('Location: checkout.php?error=payment_cancelled');
exit();
?>
