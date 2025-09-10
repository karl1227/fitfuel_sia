<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to apply promo code']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['promo_code'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$promo_code = strtoupper(trim($input['promo_code']));

try {
    $pdo = getDBConnection();
    
    // Get user's cart total
    $cart_sql = "SELECT COALESCE(SUM(ci.quantity * p.price), 0) as subtotal
                 FROM cart c 
                 LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id 
                 LEFT JOIN products p ON ci.product_id = p.product_id 
                 WHERE c.user_id = ?";
    
    $cart_stmt = $pdo->prepare($cart_sql);
    $cart_stmt->execute([$_SESSION['user_id']]);
    $cart_result = $cart_stmt->fetch();
    $subtotal = $cart_result['subtotal'];
    
    // Check promo code
    $promo_sql = "SELECT * FROM promo_codes 
                  WHERE code = ? AND is_active = 1 
                  AND valid_from <= NOW() AND valid_until >= NOW()
                  AND (usage_limit IS NULL OR used_count < usage_limit)
                  AND minimum_amount <= ?";
    
    $promo_stmt = $pdo->prepare($promo_sql);
    $promo_stmt->execute([$promo_code, $subtotal]);
    $promo = $promo_stmt->fetch();
    
    if (!$promo) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired promo code']);
        exit();
    }
    
    // Calculate discount
    $discount_amount = 0;
    if ($promo['discount_type'] === 'percentage') {
        $discount_amount = ($subtotal * $promo['discount_value']) / 100;
        if ($promo['maximum_discount'] && $discount_amount > $promo['maximum_discount']) {
            $discount_amount = $promo['maximum_discount'];
        }
    } else {
        $discount_amount = $promo['discount_value'];
    }
    
    // Round to 2 decimal places
    $discount_amount = round($discount_amount, 2);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Promo code applied successfully!',
        'discount_amount' => number_format($discount_amount, 2),
        'promo_description' => $promo['description']
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
