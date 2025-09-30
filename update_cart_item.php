<?php
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to update cart']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['cart_item_id']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$cart_item_id = (int)$input['cart_item_id'];
$quantity = (int)$input['quantity'];

if ($cart_item_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item or quantity']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Verify the cart item belongs to the user
    $verify_sql = "SELECT ci.*, p.stock 
                   FROM cart_items ci 
                   JOIN cart c ON ci.cart_id = c.cart_id 
                   JOIN products p ON ci.product_id = p.product_id 
                   WHERE ci.cart_item_id = ? AND c.user_id = ?";
    
    $verify_stmt = $pdo->prepare($verify_sql);
    $verify_stmt->execute([$cart_item_id, $_SESSION['user_id']]);
    $cart_item = $verify_stmt->fetch();
    
    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit();
    }
    
    // Check stock availability
    if ($quantity > $cart_item['stock']) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock. Available: ' . $cart_item['stock']]);
        exit();
    }
    
    // Update quantity
    $update_stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
    $result = $update_stmt->execute([$quantity, $cart_item_id]);
    
    if (!$result || $update_stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart item']);
        exit();
    }
    
    echo json_encode(['success' => true, 'message' => 'Cart item updated successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
