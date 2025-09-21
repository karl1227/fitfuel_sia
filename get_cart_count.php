<?php
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Get cart count for logged in user - count total quantity of items, not just unique items
    $cart_sql = "SELECT COALESCE(SUM(ci.quantity), 0) as count 
                 FROM cart c 
                 LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id 
                 WHERE c.user_id = ?";
    $cart_stmt = $pdo->prepare($cart_sql);
    $cart_stmt->execute([$_SESSION['user_id']]);
    $cart_result = $cart_stmt->fetch();
    $cart_count = $cart_result['count'] ?? 0;
    
    echo json_encode(['success' => true, 'count' => $cart_count]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
