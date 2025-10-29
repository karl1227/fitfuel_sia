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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    try {
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $review_text = trim($_POST['review_text'] ?? '');
        
        // Validation
        if ($rating < 1 || $rating > 5) {
            throw new Exception('Please select a rating');
        }
        
        // Check if already reviewed
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE order_item_id = ? AND user_id = ?");
        $checkStmt->execute([$order_item_id, $user_id]);
        if ($checkStmt->fetchColumn() > 0) {
            throw new Exception('You have already reviewed this product');
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
        
        // Ensure rating columns exist in products table (BEFORE transaction)
        // ALTER TABLE statements can't be in transactions on some MySQL versions
        try {
            $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products'")
                        ->fetchAll(PDO::FETCH_COLUMN);
            if (is_array($cols)) {
                $rating_columns = [
                    'average_rating' => 'decimal(3,2) NOT NULL DEFAULT 0.00',
                    'total_reviews' => 'int(11) NOT NULL DEFAULT 0',
                    'rating_5_count' => 'int(11) NOT NULL DEFAULT 0',
                    'rating_4_count' => 'int(11) NOT NULL DEFAULT 0',
                    'rating_3_count' => 'int(11) NOT NULL DEFAULT 0',
                    'rating_2_count' => 'int(11) NOT NULL DEFAULT 0',
                    'rating_1_count' => 'int(11) NOT NULL DEFAULT 0'
                ];
                
                foreach ($rating_columns as $col => $def) {
                    if (!in_array($col, $cols, true)) {
                        $pdo->exec("ALTER TABLE products ADD COLUMN `$col` $def");
                    }
                }
            }
        } catch (Throwable $e) {
            error_log("Rating column creation error: " . $e->getMessage());
            throw new Exception('Failed to prepare database for review');
        }
        
        $pdo->beginTransaction();
        
        // Insert review
        $insertStmt = $pdo->prepare("INSERT INTO reviews (order_id, order_item_id, product_id, user_id, rating, review_text, is_verified_purchase) 
                                    VALUES (?, ?, ?, ?, ?, ?, 1)");
        $insertStmt->execute([$order_id, $order_item_id, $product_id, $user_id, $rating, $review_text]);
        $review_id = $pdo->lastInsertId();
        
        // Handle image uploads (max 4 images)
        $image_count = 0;
        if (isset($_FILES['review_images']) && is_array($_FILES['review_images']['name'])) {
            $upload_dir = 'uploads/reviews/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            for ($i = 0; $i < count($_FILES['review_images']['name']) && $image_count < 4; $i++) {
                if ($_FILES['review_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['review_images']['tmp_name'][$i];
                    $file_name = $_FILES['review_images']['name'][$i];
                    $file_size = $_FILES['review_images']['size'][$i];
                    
                    // Validate file
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $file_type = $_FILES['review_images']['type'][$i];
                    
                    if (!in_array($file_type, $allowed_types)) {
                        continue; // Skip invalid files
                    }
                    
                    if ($file_size > 5000000) { // 5MB limit
                        continue; // Skip large files
                    }
                    
                    // Generate unique filename
                    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_filename = 'review_' . $review_id . '_' . ($image_count + 1) . '_' . time() . '.' . $ext;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $imageStmt = $pdo->prepare("INSERT INTO review_images (review_id, image_path, upload_order) VALUES (?, ?, ?)");
                        $imageStmt->execute([$review_id, $upload_path, $image_count + 1]);
                        $image_count++;
                    }
                }
            }
        }
        
        // Update product rating statistics
        // Get current counts and average
        $statsStmt = $pdo->prepare("SELECT 
            COUNT(*) as total_reviews,
            AVG(rating) as average_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
            FROM reviews WHERE product_id = ?");
        $statsStmt->execute([$product_id]);
        $stats = $statsStmt->fetch();
        
        // Update product table
        $updateStmt = $pdo->prepare("UPDATE products SET 
            average_rating = ?,
            total_reviews = ?,
            rating_5_count = ?,
            rating_4_count = ?,
            rating_3_count = ?,
            rating_2_count = ?,
            rating_1_count = ?
            WHERE product_id = ?");
        $updateStmt->execute([
            round($stats['average_rating'], 2),
            (int)$stats['total_reviews'],
            (int)$stats['rating_5'],
            (int)$stats['rating_4'],
            (int)$stats['rating_3'],
            (int)$stats['rating_2'],
            (int)$stats['rating_1'],
            $product_id
        ]);
        
        $pdo->commit();
        
        $_SESSION['flash_success'] = 'Review submitted successfully!';
        header('Location: my_orders.php');
        exit();
        
    } catch (Exception $e) {
        // Only rollback if there's an active transaction
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
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
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Review Product - FitFuel</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-[#f6f6f6] text-slate-700 min-h-screen flex flex-col">
  <!-- Navigation -->
  <nav class="bg-white text-black py-2">
    <div class="container mx-auto px-4">
      <div class="flex justify-end space-x-6 text-sm">
        <a href="#" class="hover:text-emerald-400 transition-colors">Review</a>
        <a href="#" class="hover:text-emerald-400 transition-colors">Help</a>
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
      <h1 class="text-2xl font-semibold text-slate-900 mb-6">Write a Review</h1>
      
      <?php if ($error): ?>
        <div class="mb-4 p-4 bg-red-50 text-red-800 border border-red-200 rounded-lg">
          <?= htmlspecialchars($error) ?>
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
      
      <form method="post" enctype="multipart/form-data" class="space-y-6">
        <!-- Rating -->
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Your Rating *</label>
          <div class="flex items-center gap-2" id="rating-container">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <button type="button" class="rating-star text-3xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="<?= $i ?>">
                <i class="far fa-star"></i>
              </button>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="rating" id="rating-input" required>
          <p class="text-sm text-red-600 mt-1" id="rating-error"></p>
        </div>
        
        <!-- Review Text -->
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Your Review</label>
          <textarea name="review_text" rows="5" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    placeholder="Share your experience with this product..."></textarea>
        </div>
        
        <!-- Image Upload -->
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Upload Photos (optional, max 4)</label>
          <input type="file" name="review_images[]" accept="image/*" multiple 
                 class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
          <p class="text-xs text-slate-500 mt-1">You can upload up to 4 images (max 5MB each)</p>
          <div id="image-preview" class="mt-4 grid grid-cols-2 gap-4"></div>
        </div>
        
        <!-- Submit Button -->
        <div class="flex gap-4">
          <button type="submit" name="submit_review" 
                  class="flex-1 bg-emerald-600 text-white px-6 py-3 rounded-lg hover:bg-emerald-700 transition-colors font-semibold">
            Submit Review
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
    // Rating selection
    const ratingContainer = document.getElementById('rating-container');
    const ratingInput = document.getElementById('rating-input');
    const ratingError = document.getElementById('rating-error');
    let selectedRating = 0;
    
    ratingContainer.querySelectorAll('.rating-star').forEach(star => {
      star.addEventListener('click', function() {
        const rating = parseInt(this.dataset.rating);
        selectedRating = rating;
        ratingInput.value = rating;
        
        ratingContainer.querySelectorAll('.rating-star').forEach((s, i) => {
          s.querySelector('i').classList.remove('fas', 'fa-star', 'far');
          if (i < rating) {
            s.querySelector('i').classList.add('fas', 'fa-star', 'text-yellow-400');
          } else {
            s.querySelector('i').classList.add('far', 'fa-star', 'text-gray-300');
          }
        });
        
        ratingError.textContent = '';
      });
      
      star.addEventListener('mouseenter', function() {
        const rating = parseInt(this.dataset.rating);
        ratingContainer.querySelectorAll('.rating-star').forEach((s, i) => {
          s.querySelector('i').classList.remove('fas', 'fa-star', 'far');
          if (i < rating) {
            s.querySelector('i').classList.add('fas', 'fa-star', 'text-yellow-400');
          } else {
            s.querySelector('i').classList.add('far', 'fa-star', 'text-gray-300');
          }
        });
      });
    });
    
    ratingContainer.addEventListener('mouseleave', function() {
      ratingContainer.querySelectorAll('.rating-star').forEach((s, i) => {
        s.querySelector('i').classList.remove('fas', 'fa-star', 'far');
        if (i < selectedRating) {
          s.querySelector('i').classList.add('fas', 'fa-star', 'text-yellow-400');
        } else {
          s.querySelector('i').classList.add('far', 'fa-star', 'text-gray-300');
        }
      });
    });
    
    // Image preview
    document.querySelector('input[type="file"]').addEventListener('change', function(e) {
      const preview = document.getElementById('image-preview');
      preview.innerHTML = '';
      
      const files = e.target.files;
      for (let i = 0; i < Math.min(files.length, 4); i++) {
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
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      if (!selectedRating) {
        e.preventDefault();
        ratingError.textContent = 'Please select a rating';
      }
    });
  </script>
</body>
</html>

