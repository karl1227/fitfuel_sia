<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to remove cart items']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['cart_item_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$cart_item_id = (int)$input['cart_item_id'];

if ($cart_item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Debug: Log the request
    error_log("Remove cart item request - User ID: " . $_SESSION['user_id'] . ", Cart Item ID: " . $cart_item_id);
    
    // Verify the cart item belongs to the user
    $verify_sql = "SELECT ci.cart_item_id 
                   FROM cart_items ci 
                   JOIN cart c ON ci.cart_id = c.cart_id 
                   WHERE ci.cart_item_id = ? AND c.user_id = ?";
    
    $verify_stmt = $pdo->prepare($verify_sql);
    $verify_stmt->execute([$cart_item_id, $_SESSION['user_id']]);
    $cart_item = $verify_stmt->fetch();
    
    if (!$cart_item) {
        error_log("Cart item not found for user " . $_SESSION['user_id'] . " and cart item " . $cart_item_id);
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit();
    }
    
    // Remove the cart item
    $delete_stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
    $result = $delete_stmt->execute([$cart_item_id]);
    
    if (!$result) {
        error_log("Delete statement failed for cart item " . $cart_item_id);
        echo json_encode(['success' => false, 'message' => 'Failed to execute delete statement']);
        exit();
    }
    
    $rows_affected = $delete_stmt->rowCount();
    
    if ($rows_affected == 0) {
        error_log("No rows affected when deleting cart item " . $cart_item_id);
        echo json_encode(['success' => false, 'message' => 'No rows affected - item may not exist']);
        exit();
    }
    
    error_log("Successfully removed cart item " . $cart_item_id . " - rows affected: " . $rows_affected);
    echo json_encode(['success' => true, 'message' => 'Cart item removed successfully', 'rows_affected' => $rows_affected]);
    
} catch (PDOException $e) {
    error_log("Database error removing cart item: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
