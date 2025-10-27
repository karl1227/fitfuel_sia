<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';

$pdo = getDBConnection();
$success = false;
$message = '';

// THIS PHP FILE IS USED TO CLEAR THE CART ITEMS FOR THE ADMIN USERS

try {
    // Get all admin user IDs
    $stmt = $pdo->query("SELECT user_id FROM users WHERE role IN ('admin', 'manager', 'staff')");
    $admin_users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($admin_users)) {
        $message = 'No admin users found';
    } else {
        $deleted_count = 0;
        
        foreach ($admin_users as $user_id) {
            // Get cart ID for this admin
            $cart_stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
            $cart_stmt->execute([$user_id]);
            $cart = $cart_stmt->fetch();
            
            if ($cart) {
                $cart_id = $cart['cart_id'];
                
                // Delete cart items
                $delete_items_stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
                $delete_items_stmt->execute([$cart_id]);
                $deleted_count += $delete_items_stmt->rowCount();
            }
        }
        
        $message = "Successfully cleared cart items for {$deleted_count} admin user(s)";
        $success = true;
    }
} catch (PDOException $e) {
    $message = "Error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Clear Admin Carts</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-body bg-gray-50 p-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-6">
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php else: ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <i class="fas fa-info-circle mr-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="dashboard.php" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>

