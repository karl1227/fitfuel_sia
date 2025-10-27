<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to wishlist']);
    exit();
}

// Prevent admin users from adding to wishlist
if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager', 'staff'])) {
    echo json_encode(['success' => false, 'message' => 'Admins cannot use wishlist']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$product_id = (int)$input['product_id'];
$user_id = (int)$_SESSION['user_id'];

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Check if product exists
    $product_stmt = $pdo->prepare("SELECT product_id, name FROM products WHERE product_id = ? AND status = 'active'");
    $product_stmt->execute([$product_id]);
    $product = $product_stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }
    
    // Check if already in wishlist
    $check_stmt = $pdo->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $check_stmt->execute([$user_id, $product_id]);
    
    if ($check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Product already in wishlist']);
        exit();
    }
    
    // Add to wishlist
    $insert_stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $result = $insert_stmt->execute([$user_id, $product_id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Product added to wishlist']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

