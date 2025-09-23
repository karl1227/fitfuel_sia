<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's orders
try {
    $pdo = getDBConnection();
    $sql = "SELECT order_id, custom_order_id, total_amount, status, created_at
            FROM orders
            WHERE user_id = ?
            ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Orders - FitFuel</title>
  <link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-body text-slate-700">

  <div class="container mx-auto px-4 py-8">
    <!-- Back Button + Title -->
    <div class="flex items-center mb-6">
      <a href="profile.php" class="flex items-center text-emerald-600 hover:text-emerald-800">
        <i class="fas fa-arrow-left mr-2"></i> Back
      </a>
      <h1 class="text-2xl font-bold text-slate-900 ml-4">My Orders</h1>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-6 py-3 text-sm font-semibold">Order ID</th>
            <th class="px-6 py-3 text-sm font-semibold">Total</th>
            <th class="px-6 py-3 text-sm font-semibold">Status</th>
            <th class="px-6 py-3 text-sm font-semibold">Date</th>
            <th class="px-6 py-3 text-sm font-semibold">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
              <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-slate-800">
                  <?php echo htmlspecialchars($order['custom_order_id']); ?>
                </td>
                <td class="px-6 py-4">
                  ₱<?php echo number_format($order['total_amount'], 2); ?>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 rounded-full text-xs font-semibold 
                    <?php echo $order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                               ($order['status'] === 'processing' ? 'bg-blue-100 text-blue-800' : 
                               ($order['status'] === 'shipped' ? 'bg-indigo-100 text-indigo-800' : 
                               ($order['status'] === 'delivered' ? 'bg-green-100 text-green-800' : 
                               'bg-red-100 text-red-800'))); ?>">
                    <?php echo ucfirst($order['status']); ?>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <?php echo date("M d, Y", strtotime($order['created_at'])); ?>
                </td>
                <td class="px-6 py-4">
                  <a href="order_details.php?order_id=<?php echo urlencode($order['custom_order_id']); ?>" 
                     class="text-emerald-600 hover:underline">View Details</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                You don’t have any orders yet.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
