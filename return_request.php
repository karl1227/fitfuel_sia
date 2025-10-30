<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'customer_auth_check.php';
require_once 'config/database.php';
require_once 'config/currency_helper.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);

// Get parameters
$order_item_id = isset($_GET['order_item_id']) ? (int)$_GET['order_item_id'] : 0;
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if (!$order_item_id || !$product_id) {
    header('Location: my_orders.php');
    exit();
}

$pdo = getDBConnection();
$error = null;
$success = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_return'])) {
    try {
        $return_reason = trim($_POST['return_reason'] ?? '');
        $return_type = $_POST['return_type'] ?? 'refund';
        
        // Validation
        if (empty($return_reason)) {
            throw new Exception('Please provide a reason for the return');
        }
        
        // Check if already requested return
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM returns WHERE order_item_id = ? AND user_id = ?");
        $checkStmt->execute([$order_item_id, $user_id]);
        if ($checkStmt->fetchColumn() > 0) {
            throw new Exception('You have already requested a return for this item');
        }
        
        // Check if user already reviewed this item - cannot return if reviewed
        $reviewCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE order_item_id = ? AND user_id = ?");
        $reviewCheckStmt->execute([$order_item_id, $user_id]);
        if ($reviewCheckStmt->fetchColumn() > 0) {
            throw new Exception('Cannot request return for an item you have already reviewed');
        }
        
        // Get order information
        $orderStmt = $pdo->prepare("SELECT o.order_id FROM order_items oi 
                                   JOIN orders o ON o.order_id = oi.order_id 
                                   WHERE oi.order_item_id = ? AND o.user_id = ?");
        $orderStmt->execute([$order_item_id, $user_id]);
        $orderData = $orderStmt->fetch();
        
        if (!$orderData) {
            throw new Exception('Invalid order item');
        }
        
        $order_id = $orderData['order_id'];

        // Ensure helper flags exist on order_items for item-scoped status
        try {
            $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_items'")
                        ->fetchAll(PDO::FETCH_COLUMN);
            if (is_array($cols)) {
                if (!in_array('return_requested', $cols, true)) {
                    $pdo->exec("ALTER TABLE order_items ADD COLUMN return_requested TINYINT(1) NOT NULL DEFAULT 0");
                }
                if (!in_array('review_submitted', $cols, true)) {
                    $pdo->exec("ALTER TABLE order_items ADD COLUMN review_submitted TINYINT(1) NOT NULL DEFAULT 0");
                }
            }
        } catch (Throwable $e) {
            error_log('order_items column ensure failed: ' . $e->getMessage());
            // Proceed without failing hard; UI will still work using returns table
        }

        $pdo->beginTransaction();
        
        // Insert return request
        $insertStmt = $pdo->prepare("INSERT INTO returns (order_id, order_item_id, product_id, user_id, return_reason, return_type, status) 
                                     VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $insertStmt->execute([$order_id, $order_item_id, $product_id, $user_id, $return_reason, $return_type]);
        $return_id = $pdo->lastInsertId();
        
        // Mark the item and order as in return flow so it appears under "Return" tab
        // 1) Flag the order item
        $pdo->prepare("UPDATE order_items SET return_requested = 1 WHERE order_item_id = ? LIMIT 1")->execute([$order_item_id]);
        // 2) Do not change overall order status here; My Orders will classify
        //    an order under the Return tab if it has any return records.
        
        // Handle image uploads
        if (isset($_FILES['return_images']) && is_array($_FILES['return_images']['name'])) {
            $upload_dir = 'uploads/returns/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $image_count = 0;
            for ($i = 0; $i < count($_FILES['return_images']['name']) && $image_count < 5; $i++) {
                if ($_FILES['return_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['return_images']['tmp_name'][$i];
                    $file_name = $_FILES['return_images']['name'][$i];
                    $file_size = $_FILES['return_images']['size'][$i];
                    
                    // Validate file
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $file_type = $_FILES['return_images']['type'][$i];
                    
                    if (!in_array($file_type, $allowed_types)) {
                        continue;
                    }
                    
                    if ($file_size > 5000000) { // 5MB limit
                        continue;
                    }
                    
                    // Generate unique filename
                    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_filename = 'return_' . $return_id . '_' . ($image_count + 1) . '_' . time() . '.' . $ext;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $imageStmt = $pdo->prepare("INSERT INTO return_images (return_id, image_path, upload_order) VALUES (?, ?, ?)");
                        $imageStmt->execute([$return_id, $upload_path, $image_count + 1]);
                        $image_count++;
                    }
                }
            }
        }
        
        $pdo->commit();
        
        $_SESSION['flash_success'] = 'Return request submitted successfully! We will review your request soon.';
        header('Location: my_orders.php');
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Get product details
$productStmt = $pdo->prepare("SELECT name, images FROM products WHERE product_id = ?");
$productStmt->execute([$product_id]);
$product = $productStmt->fetch();

if (!$product) {
    header('Location: my_orders.php');
    exit();
}

// Get order item details
$orderItemStmt = $pdo->prepare("SELECT oi.*, p.name, p.images, o.custom_order_id, o.created_at 
                               FROM order_items oi 
                               JOIN products p ON oi.product_id = p.product_id
                               JOIN orders o ON o.order_id = oi.order_id
                               WHERE oi.order_item_id = ? AND o.user_id = ?");
$orderItemStmt->execute([$order_item_id, $user_id]);
$order_item = $orderItemStmt->fetch();

if (!$order_item) {
    header('Location: my_orders.php');
    exit();
}

// Check if user already reviewed this item
$reviewExistsStmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE order_item_id = ? AND user_id = ?");
$reviewExistsStmt->execute([$order_item_id, $user_id]);
$has_review = (int)$reviewExistsStmt->fetchColumn() > 0;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Request Return - FitFuel</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-[#f6f6f6] text-slate-700 min-h-screen flex flex-col">
  <!-- Navigation -->
  <nav class="bg-white text-black py-2">
    <div class="container mx-auto px-4">
      <div class="flex justify-end space-x-6 text-sm">
        <a href="testimonials.php" class="hover:text-emerald-400 transition-colors">Review</a>
        <a href="faq.php" class="hover:text-emerald-400 transition-colors">Help</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a href="logout.php" class="hover:text-emerald-400 transition-colors">Logout</a>
        <?php else: ?>
          <a href="login.php" class="hover:text-emerald-400 transition-colors">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <nav class="bg-black py-4">
    <div class="container mx-auto px-4 flex items-center justify-between">
      <a href="index.php" class="flex items-center">
        <img src="img/LOGO-Fitfuel.png" width="75" height="auto" alt="FitFuel">
      </a>
      <div class="hidden md:flex space-x-8">
        <a href="index.php" class="text-white hover:text-emerald-600">Home</a>
        <a href="shop.php" class="text-white hover:text-emerald-600">Shop</a>
        <a href="#" class="text-white hover:text-emerald-600">About</a>
        <a href="#" class="text-white hover:text-emerald-600">Contact</a>
      </div>
      <div class="flex items-center space-x-4">
        <a href="cart.php" class="relative p-2 text-white hover:text-emerald-600">
          <i class="fas fa-shopping-cart text-xl"></i>
        </a>
        <a href="profile.php" class="p-2 text-white hover:text-emerald-600">
          <i class="fas fa-user text-xl"></i>
        </a>
      </div>
    </div>
  </nav>

  <main class="flex-1 max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg border border-gray-200 p-6">
      <h1 class="text-2xl font-semibold text-slate-900 mb-6">Request a Return</h1>
      
      <?php if ($error): ?>
        <div class="mb-4 p-4 bg-red-50 text-red-800 border border-red-200 rounded-lg">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      
      <?php if ($has_review): ?>
        <div class="mb-4 p-4 bg-yellow-50 text-yellow-800 border border-yellow-200 rounded-lg">
          <i class="fas fa-exclamation-triangle mr-2"></i>
          You cannot request a return for an item you have already reviewed. Please contact customer service if you need assistance.
        </div>
      <?php endif; ?>
      
      <!-- Product Info -->
      <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <?php 
        $images = json_decode($order_item['images'] ?? '[]', true);
        $product_image = !empty($images) ? $images[0] : 'img/placeholder-product.png';
        ?>
        <div class="flex items-center gap-4">
          <img src="<?= htmlspecialchars($product_image) ?>" alt="<?= htmlspecialchars($order_item['name']) ?>" class="w-20 h-20 rounded-lg object-cover border">
          <div>
            <h3 class="font-semibold text-slate-800"><?= htmlspecialchars($order_item['name']) ?></h3>
            <p class="text-sm text-slate-600">Order #<?= htmlspecialchars($order_item['custom_order_id']) ?></p>
          </div>
        </div>
      </div>
      
      <form method="post" enctype="multipart/form-data" class="space-y-6 <?= $has_review ? 'opacity-50 pointer-events-none' : '' ?>">
        <!-- Return Type -->
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Return Type *</label>
          <select name="return_type" <?= $has_review ? 'disabled' : '' ?>
                  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 <?= $has_review ? 'bg-gray-100 cursor-not-allowed' : '' ?>">
            <option value="return" selected>Return</option>
            <option value="refund">Refund</option>
          </select>
        </div>
        
        <!-- Return Reason -->
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Reason for Return *</label>
          <textarea name="return_reason" rows="5" required <?= $has_review ? 'disabled' : '' ?>
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 <?= $has_review ? 'bg-gray-100 cursor-not-allowed' : '' ?>"
                    placeholder="Please provide details about why you are returning this product..."></textarea>
        </div>
        
        <!-- Image Upload -->
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Upload Photos (optional)</label>
          <input type="file" name="return_images[]" accept="image/*" multiple <?= $has_review ? 'disabled' : '' ?>
                 class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 <?= $has_review ? 'bg-gray-100 cursor-not-allowed' : '' ?>">
          <p class="text-xs text-slate-500 mt-1">Upload photos to help us understand the issue (max 5MB each)</p>
          <div id="image-preview" class="mt-4 grid grid-cols-2 gap-4"></div>
        </div>
        
        <!-- Submit Button -->
        <div class="flex gap-4">
          <button type="submit" name="submit_return" <?= $has_review ? 'disabled' : '' ?>
                  class="flex-1 bg-orange-600 text-white px-6 py-3 rounded-lg hover:bg-orange-700 transition-colors font-semibold <?= $has_review ? 'opacity-50 cursor-not-allowed' : '' ?>">
            Submit Return Request
          </button>
          <a href="my_orders.php" 
             class="flex items-center justify-center px-6 py-3 rounded-lg border border-gray-300 text-slate-700 hover:bg-gray-50 transition-colors">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </main>
  
  <footer class="bg-slate-800 text-white py-12 mt-auto">
    <div class="container mx-auto px-4 text-center">
      <p>&copy; 2024 FitFuel. All rights reserved.</p>
    </div>
  </footer>
  
  <script>
    // Image preview
    document.querySelector('input[type="file"]').addEventListener('change', function(e) {
      const preview = document.getElementById('image-preview');
      preview.innerHTML = '';
      
      const files = e.target.files;
      for (let i = 0; i < files.length; i++) {
        if (files[i].type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-full h-40 object-cover rounded-lg border';
            preview.appendChild(img);
          };
          reader.readAsDataURL(files[i]);
        }
      }
    });
  </script>
</body>
</html>

