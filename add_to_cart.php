<?php
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$product_id = (int)$input['product_id'];
$quantity = (int)$input['quantity'];

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Check if product exists and is active
    $product_stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ? AND status = 'active'");
    $product_stmt->execute([$product_id]);
    $product = $product_stmt->fetch();
    
    if (!$product) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Product not found or inactive']);
        exit();
    }
    
    // Check stock availability
    if ($product['stock'] < $quantity) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Insufficient stock. Available: ' . $product['stock']]);
        exit();
    }
    
    // Get or create user's cart - using a more robust approach
    $cart_stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
    $cart_stmt->execute([$_SESSION['user_id']]);
    $cart = $cart_stmt->fetch();
    
    if (!$cart) {
        // Create new cart
        $create_cart_stmt = $pdo->prepare("INSERT INTO cart (user_id) VALUES (?)");
        $create_cart_stmt->execute([$_SESSION['user_id']]);
        $cart_id = $pdo->lastInsertId();
        
        if (!$cart_id) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to create cart']);
            exit();
        }
    } else {
        $cart_id = $cart['cart_id'];
    }
    
    // Check if product already exists in cart
    $existing_item_stmt = $pdo->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $existing_item_stmt->execute([$cart_id, $product_id]);
    $existing_item = $existing_item_stmt->fetch();
    
    if ($existing_item) {
        // Update existing item quantity
        $new_quantity = $existing_item['quantity'] + $quantity;
        
        // Check if new quantity exceeds stock
        if ($new_quantity > $product['stock']) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Cannot add more items. Stock limit reached. Current in cart: ' . $existing_item['quantity'] . ', Available: ' . $product['stock']]);
            exit();
        }
        
        $update_stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
        $result = $update_stmt->execute([$new_quantity, $existing_item['cart_item_id']]);
        
        if (!$result || $update_stmt->rowCount() == 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to update cart item. No rows affected.']);
            exit();
        }
        
        $message = "Product quantity updated in cart (Total: $new_quantity)";
    } else {
        // Add new item to cart
        $insert_stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
        $result = $insert_stmt->execute([$cart_id, $product_id, $quantity]);
        
        if (!$result || $insert_stmt->rowCount() == 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to add item to cart. No rows affected.']);
            exit();
        }
        
        $message = "Product added to cart successfully";
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Verify the operation by checking the cart
    $verify_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart_items WHERE cart_id = ?");
    $verify_stmt->execute([$cart_id]);
    $verify_result = $verify_stmt->fetch();
    
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'cart_count' => $verify_result['count'],
        'cart_id' => $cart_id
    ]);
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>