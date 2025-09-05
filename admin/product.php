<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';

$pdo = getDBConnection();
$message = '';
$error = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $category_id = intval($_POST['category_id']);
            $subcategory_id = intval($_POST['subcategory_id']);
            $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
            $status = $_POST['status'];
            $is_popular = isset($_POST['is_popular']) ? 1 : 0;
            $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;
            
            // Handle image upload
            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($file_extension, $allowed_extensions)) {
                    $file_name = uniqid() . '_' . time() . '.' . $file_extension;
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                        $image_path = 'uploads/products/' . $file_name;
                    }
                }
            }
            
            try {
                $images_json = json_encode($image_path ? [$image_path] : []);
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, subcategory_id, stock_quantity, status, is_popular, is_best_seller, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $price, $category_id, $subcategory_id, $stock_quantity, $status, $is_popular, $is_best_seller, $images_json]);
                $message = "Product added successfully!";
            } catch (PDOException $e) {
                $error = "Failed to add product: " . $e->getMessage();
            }
            break;
            
        case 'edit':
            $product_id = intval($_POST['product_id']);
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $category_id = intval($_POST['category_id']);
            $subcategory_id = intval($_POST['subcategory_id']);
            $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
            $status = $_POST['status'];
            $is_popular = isset($_POST['is_popular']) ? 1 : 0;
            $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;
            
            // Get existing image
            $stmt = $pdo->prepare("SELECT images FROM products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $existing_images = json_decode($stmt->fetchColumn() ?: '[]', true);
            $existing_image = !empty($existing_images) ? $existing_images[0] : '';
            
            // Handle new image upload
            $new_image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($file_extension, $allowed_extensions)) {
                    $file_name = uniqid() . '_' . time() . '.' . $file_extension;
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                        $new_image = 'uploads/products/' . $file_name;
                    }
                }
            }
            
            // Use new image if uploaded, otherwise keep existing
            $final_image = $new_image ?: $existing_image;
            
            try {
                $images_json = json_encode($final_image ? [$final_image] : []);
                $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, category_id=?, subcategory_id=?, stock_quantity=?, status=?, is_popular=?, is_best_seller=?, images=? WHERE product_id=?");
                $stmt->execute([$name, $description, $price, $category_id, $subcategory_id, $stock_quantity, $status, $is_popular, $is_best_seller, $images_json, $product_id]);
                $message = "Product updated successfully!";
            } catch (PDOException $e) {
                $error = "Failed to update product: " . $e->getMessage();
            }
            break;
            
        case 'delete':
            $product_id = intval($_POST['product_id']);
            try {
                $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
                $stmt->execute([$product_id]);
                $message = "Product deleted successfully!";
            } catch (PDOException $e) {
                $error = "Failed to delete product: " . $e->getMessage();
            }
            break;
    }
}

// Get categories and subcategories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$subcategories = $pdo->query("SELECT * FROM subcategories ORDER BY name")->fetchAll();

// Get products with pagination and search
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if ($status_filter) {
    $where_conditions[] = "p.status = ?";
    $params[] = $status_filter;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count
$count_sql = "SELECT COUNT(*) FROM products p $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

// Get products
$sql = "SELECT p.*, c.name as category_name, s.name as subcategory_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        LEFT JOIN subcategories s ON p.subcategory_id = s.subcategory_id 
        $where_clause 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$edit_id]);
    $edit_product = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - FitFuel Admin</title>
    <link rel="icon" href="../img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .sidebar-item.active {
            background-color: #f3f4f6;
            border-right: 3px solid #000;
        }
        .sidebar-item:hover {
            background-color: #f9fafb;
        }
        .search-input {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        .search-input:focus {
            background-color: white;
            border-color: #6c757d;
        }
        .filter-btn {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #6c757d;
        }
        .filter-btn:hover {
            background-color: #e9ecef;
        }
        .add-btn {
            background-color: #000;
            color: white;
        }
        .add-btn:hover {
            background-color: #333;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-popular {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-bestseller {
            background-color: #cce5ff;
            color: #004085;
        }
    </style>
</head>
<body class="font-body bg-gray-50">
    <!-- Header -->
    <header class="bg-black text-white fixed top-0 left-0 right-0 z-50 h-16 flex items-center justify-between px-6">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-white rounded flex items-center justify-center">
                <span class="text-black font-bold text-lg">F</span>
            </div>
            <h1 class="text-xl font-bold uppercase">Admin</h1>
        </div>
        <div class="flex items-center space-x-4">
            <button class="p-2 hover:bg-gray-800 rounded-lg transition-colors relative">
                <i class="fas fa-bell text-xl"></i>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
            </button>
            <div class="relative">
                <button onclick="toggleUserMenu()" class="flex items-center space-x-2 p-2 hover:bg-gray-800 rounded-lg transition-colors">
                    <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <span class="hidden md:block text-sm"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                    <div class="px-4 py-2 border-b border-gray-200">
                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                        <span class="inline-block mt-1 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full"><?php echo ucfirst($_SESSION['role'] ?? 'admin'); ?></span>
                    </div>
                    <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user-cog mr-3 text-gray-400"></i>
                        Profile Settings
                    </a>
                    <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-cog mr-3 text-gray-400"></i>
                        Preferences
                    </a>
                    <div class="border-t border-gray-200 mt-2"></div>
                    <a href="../logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt mr-3 text-red-500"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="fixed left-0 top-16 bottom-0 w-64 bg-white border-r border-gray-200 overflow-y-auto">
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="dashboard.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-th-large text-gray-600"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="product.php" class="sidebar-item active flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-cube text-gray-600"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-shopping-cart text-gray-600"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li>
                    <a href="inventory.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-archive text-gray-600"></i>
                        <span>Inventory</span>
                    </a>
                </li>
                <li>
                    <a href="users.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-users text-gray-600"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-chart-line text-gray-600"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-file-alt text-gray-600"></i>
                        <span>Contents</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-history text-gray-600"></i>
                        <span>Audit Trail</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-bell text-gray-600"></i>
                        <span>Notifications</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-cog text-gray-600"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- User Info Panel -->
        <div class="absolute bottom-0 left-0 right-0 bg-black text-white p-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gray-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-white"></i>
                </div>
                <div>
                    <p class="font-medium">Admin User</p>
                    <p class="text-sm text-gray-300">Admin@fitfuel.com</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 pt-24 pb-6 px-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-3 mb-2">
                <i class="fas fa-cube text-2xl text-gray-600"></i>
                <h1 class="text-3xl font-bold text-gray-900">Products</h1>
            </div>
            <p class="text-gray-600">Manage Your Product Catalog</p>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Action Bar -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0 lg:space-x-4">
                <!-- Search and Filters -->
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 flex-1">
                    <div class="relative flex-1">
                        <i class="fas fa-cube absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" 
                               placeholder="Search Products" 
                               class="search-input w-full pl-10 pr-10 py-2 rounded-lg focus:outline-none"
                               value="<?php echo htmlspecialchars($search); ?>"
                               onkeypress="handleSearch(event)">
                        <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    
                    <select class="filter-btn px-4 py-2 rounded-lg focus:outline-none" onchange="filterProducts()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>" <?php echo $category_filter == $category['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select class="filter-btn px-4 py-2 rounded-lg focus:outline-none" onchange="filterProducts()">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <!-- Add Product Button -->
                <button onclick="openAddModal()" class="add-btn px-6 py-2 rounded-lg flex items-center space-x-2 focus:outline-none">
                    <i class="fas fa-plus text-white"></i>
                    <span>Add Product</span>
                </button>
            </div>
        </div>

        <!-- Product Catalog -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-cube text-gray-600"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Product Catalog</h2>
                </div>
            </div>
            
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-cube text-4xl mb-4"></i>
                                    <p>No products found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php 
                                            $product_images = json_decode($product['images'] ?: '[]', true);
                                            if (!empty($product_images)): 
                                            ?>
                                                <img src="../<?php echo htmlspecialchars($product_images[0]); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-12 h-12 rounded-lg object-cover mr-4">
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center mr-4">
                                                    <i class="fas fa-cube text-gray-500"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></div>
                                                <div class="flex space-x-2 mt-1">
                                                    <?php if ($product['is_popular']): ?>
                                                        <span class="badge-popular px-2 py-1 text-xs rounded-full">Popular</span>
                                                    <?php endif; ?>
                                                    <?php if ($product['is_best_seller']): ?>
                                                        <span class="badge-bestseller px-2 py-1 text-xs rounded-full">Best Seller</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?>
                                        <?php if ($product['subcategory_name']): ?>
                                            <br><span class="text-gray-500"><?php echo htmlspecialchars($product['subcategory_name']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ₱<?php echo number_format($product['price'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $product['stock_quantity']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $product['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="editProduct(<?php echo $product['product_id']; ?>)" class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteProduct(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $total_products); ?> of <?php echo $total_products; ?> results
                        </div>
                        <div class="flex space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>" class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>" class="px-3 py-2 text-sm <?php echo $i == $page ? 'bg-black text-white' : 'bg-white border border-gray-300 hover:bg-gray-50'; ?> rounded-md">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>" class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add/Edit Product Modal -->
    <div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900" id="modalTitle">Add Product</h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <form id="productForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="product_id" id="productId">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
                                <input type="text" name="name" id="productName" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea name="description" id="productDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price (₱)</label>
                                <input type="number" name="price" id="productPrice" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Stock Quantity</label>
                                <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700" id="productStockDisplay">
                                    <span id="stockValue">0</span> units
                                </div>
                                <input type="hidden" name="stock_quantity" id="productStock" value="0">
                                <p class="text-xs text-gray-500 mt-1">Stock managed through Inventory Management</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select name="category_id" id="productCategory" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black" onchange="updateSubcategories()">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subcategory</label>
                                <select name="subcategory_id" id="productSubcategory" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                                    <option value="">Select Subcategory</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" id="productStatus" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Product Image</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                    <input type="file" name="image" id="productImage" accept="image/*" class="hidden" onchange="previewImage(this)">
                                    <div id="imagePreview" class="mb-4"></div>
                                    <button type="button" onclick="document.getElementById('productImage').click()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-upload mr-2"></i>Choose Image
                                    </button>
                                    <p class="text-sm text-gray-500 mt-2">Upload one image (JPG, PNG, GIF, WebP)</p>
                                </div>
                            </div>
                            
                            <div class="md:col-span-2">
                                <div class="flex space-x-6">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_popular" id="productPopular" class="rounded border-gray-300 text-black focus:ring-black">
                                        <span class="ml-2 text-sm text-gray-700">Popular Product</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_best_seller" id="productBestSeller" class="rounded border-gray-300 text-black focus:ring-black">
                                        <span class="ml-2 text-sm text-gray-700">Best Seller</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:outline-none">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 focus:outline-none">
                                Save Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Delete Product</h3>
                    </div>
                    <p class="text-gray-600 mb-6">Are you sure you want to delete "<span id="deleteProductName"></span>"? This action cannot be undone.</p>
                    <form id="deleteForm" method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="product_id" id="deleteProductId">
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:outline-none">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none">
                                Delete
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const subcategories = <?php echo json_encode($subcategories); ?>;
        
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Product';
            document.getElementById('formAction').value = 'add';
            document.getElementById('productForm').reset();
            document.getElementById('imagePreview').innerHTML = '';
            document.getElementById('stockValue').textContent = '0';
            document.getElementById('productStock').value = '0';
            document.getElementById('productModal').classList.remove('hidden');
        }
        
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <div class="relative inline-block">
                            <img src="${e.target.result}" class="w-32 h-32 object-cover rounded-lg">
                            <button type="button" onclick="removeImagePreview()" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function removeImagePreview() {
            document.getElementById('imagePreview').innerHTML = '';
            document.getElementById('productImage').value = '';
        }
        
        function editProduct(productId) {
            // This would typically fetch product data via AJAX
            // For now, redirect to edit mode
            window.location.href = `?edit=${productId}`;
        }
        
        function deleteProduct(productId, productName) {
            document.getElementById('deleteProductId').value = productId;
            document.getElementById('deleteProductName').textContent = productName;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        
        function closeModal() {
            document.getElementById('productModal').classList.add('hidden');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
        
        function updateSubcategories() {
            const categoryId = document.getElementById('productCategory').value;
            const subcategorySelect = document.getElementById('productSubcategory');
            
            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            
            if (categoryId) {
                const filteredSubcategories = subcategories.filter(sub => sub.category_id == categoryId);
                filteredSubcategories.forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub.subcategory_id;
                    option.textContent = sub.name;
                    subcategorySelect.appendChild(option);
                });
            }
        }
        
        function handleSearch(event) {
            if (event.key === 'Enter') {
                filterProducts();
            }
        }
        
        function filterProducts() {
            const search = document.querySelector('input[placeholder="Search Products"]').value;
            const category = document.querySelector('select').value;
            const status = document.querySelectorAll('select')[1].value;
            
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (category) params.append('category', category);
            if (status) params.append('status', status);
            
            window.location.href = '?' + params.toString();
        }
        
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../logout.php';
            }
        }
        
        function toggleUserMenu(){
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }
        
        // Close user menu when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const userButton = event.target.closest('[onclick="toggleUserMenu()"]');
            if (!userButton && userMenu && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
            }
        });
        
        // Handle edit mode
        <?php if ($edit_product): ?>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('modalTitle').textContent = 'Edit Product';
                document.getElementById('formAction').value = 'edit';
                document.getElementById('productId').value = <?php echo $edit_product['product_id']; ?>;
                document.getElementById('productName').value = '<?php echo htmlspecialchars($edit_product['name']); ?>';
                document.getElementById('productDescription').value = '<?php echo htmlspecialchars($edit_product['description']); ?>';
                document.getElementById('productPrice').value = <?php echo $edit_product['price']; ?>;
                document.getElementById('stockValue').textContent = <?php echo $edit_product['stock_quantity']; ?>;
                document.getElementById('productStock').value = <?php echo $edit_product['stock_quantity']; ?>;
                document.getElementById('productCategory').value = <?php echo $edit_product['category_id'] ?? '""'; ?>;
                document.getElementById('productStatus').value = '<?php echo $edit_product['status']; ?>';
                document.getElementById('productPopular').checked = <?php echo $edit_product['is_popular'] ? 'true' : 'false'; ?>;
                document.getElementById('productBestSeller').checked = <?php echo $edit_product['is_best_seller'] ? 'true' : 'false'; ?>;
                
                // Display existing image
                <?php 
                $existing_images = json_decode($edit_product['images'] ?: '[]', true);
                if (!empty($existing_images)): 
                ?>
                    const preview = document.getElementById('imagePreview');
                    preview.innerHTML = `
                        <div class="relative inline-block">
                            <img src="../<?php echo htmlspecialchars($existing_images[0]); ?>" class="w-32 h-32 object-cover rounded-lg">
                            <span class="absolute -top-2 -right-2 bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">
                                <i class="fas fa-check"></i>
                            </span>
                        </div>
                    `;
                <?php endif; ?>
                
                updateSubcategories();
                document.getElementById('productSubcategory').value = <?php echo $edit_product['subcategory_id'] ?? '""'; ?>;
                
                document.getElementById('productModal').classList.remove('hidden');
            });
        <?php endif; ?>
    </script>
</body>
</html>
