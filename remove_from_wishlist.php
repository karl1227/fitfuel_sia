<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit();
}

// Prevent admin users
if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager', 'staff'])) {
    echo json_encode(['success' => false, 'message' => 'Admins cannot use wishlist']);
    exit();
}

// Get JSON input or GET parameter
$input = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input['product_id']) ? (int)$input['product_id'] : (isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0);
$user_id = (int)$_SESSION['user_id'];

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Remove from wishlist
    $delete_stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $result = $delete_stmt->execute([$user_id, $product_id]);
    
    if ($result && $delete_stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Product removed from wishlist']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found in wishlist']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

