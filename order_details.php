<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Get order ID from URL
if (!isset($_GET['order_id'])) {
    header("Location: my_orders.php");
    exit();
}
$order_id = $_GET['order_id'];

try {
    $pdo = getDBConnection();

    // Fetch order
    $order_sql = "SELECT * FROM orders WHERE custom_order_id = ? AND user_id = ? LIMIT 1";
    $stmt = $pdo->prepare($order_sql);
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        die("Order not found or you don't have permission to view it.");
    }

    // Fetch order items
    $items_sql = "SELECT oi.*, p.name, p.images 
                  FROM order_items oi
                  LEFT JOIN products p ON oi.product_id = p.product_id
                  WHERE oi.order_id = ?";
    $items_stmt = $pdo->prepare($items_sql);
    $items_stmt->execute([$order['order_id']]);
    $items = $items_stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Details - FitFuel</title>
  <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-body">

<div class="container mx-auto px-4 py-8">
  <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Order Details</h1>

    <!-- Order Summary -->
    <div class="mb-6">
      <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['custom_order_id']); ?></p>
      <p><strong>Status:</strong> <span class="text-emerald-600"><?php echo htmlspecialchars($order['status']); ?></span></p>
      <p><strong>Total Cost:</strong> ₱<?php echo number_format($order['total_amount'], 2); ?></p>
      <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>
    </div>

    <!-- Items -->
    <h2 class="text-xl font-semibold mb-4">Items Purchased</h2>
    <div class="space-y-4">
      <?php foreach ($items as $item): ?>
        <?php
          $img = "img/Featured/1.png";
          if (!empty($item['images'])) {
              $imgs = json_decode($item['images'], true);
              if (!empty($imgs)) $img = $imgs[0];
          }
        ?>
        <div class="flex items-center border-b pb-4">
          <img src="<?php echo htmlspecialchars($img); ?>" class="w-16 h-16 rounded object-cover mr-4">
          <div class="flex-1">
            <h3 class="font-semibold text-slate-800"><?php echo htmlspecialchars($item['name']); ?></h3>
            <p class="text-gray-600">Qty: <?php echo (int) $item['quantity']; ?></p>
          </div>
          <div class="text-right">
            <p class="font-semibold">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
            <p class="text-sm text-gray-500">₱<?php echo number_format($item['price'], 2); ?> each</p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-6">
      <a href="my_orders.php" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition">
        Back to Orders
      </a>
    </div>
  </div>
</div>

</body>
</html>
