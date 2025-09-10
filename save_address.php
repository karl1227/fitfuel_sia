<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to save address']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid request - no JSON data received']);
    exit();
}

// Debug: Log the received data
error_log("Save address request received: " . json_encode($input));

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    // Check if user already has a default address
    $check_sql = "SELECT address_id FROM shipping_addresses WHERE user_id = ? AND is_default = 1";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$user_id]);
    $existing_address = $check_stmt->fetch();
    
    if ($existing_address) {
        // Update existing default address
        $update_sql = "UPDATE shipping_addresses SET 
                       full_name = ?, phone = ?, address_line1 = ?, address_line2 = ?, 
                       city = ?, state = ?, postal_code = ?, country = 'Philippines',
                       updated_at = CURRENT_TIMESTAMP
                       WHERE address_id = ?";
        
        $address_line1 = $input['street_address'];
        $address_line2 = null; // Can be used for additional address info
        
        $update_stmt = $pdo->prepare($update_sql);
        $result = $update_stmt->execute([
            $input['full_name'],
            $input['phone'],
            $address_line1,
            $address_line2,
            $input['city'],
            $input['region'], // Region goes to state field
            $input['postal_code'],
            $existing_address['address_id']
        ]);
        
        if (!$result) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to update address']);
            exit();
        }
        
    } else {
    // Create new default address
    $insert_sql = "INSERT INTO shipping_addresses 
                   (user_id, full_name, phone, address_line1, address_line2, city, state, postal_code, country, is_default) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Philippines', 1)";
    
    $address_line1 = $input['street_address'];
    $address_line2 = null;
    
    $insert_stmt = $pdo->prepare($insert_sql);
    $result = $insert_stmt->execute([
        $user_id,
        $input['full_name'],
        $input['phone'],
        $address_line1,
        $address_line2,
        $input['city'],
        $input['region'], // Region goes to state field
        $input['postal_code']
    ]);
        
        if (!$result) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to create address']);
            exit();
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Address saved successfully']);
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("Database error in save_address.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
