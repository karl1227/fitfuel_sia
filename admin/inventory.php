<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';

$pdo = getDBConnection();
$message = '';
$error = '';

// Handle inventory updates (stock quantity and minimum stock level)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_inventory') {
        $product_id = intval($_POST['product_id']);
        $current_stock = intval($_POST['current_stock']);
        $new_stock_quantity = intval($_POST['stock_quantity']);
        $min_stock_level = intval($_POST['min_stock_level']);
        $quantity_changed = $new_stock_quantity - $current_stock;
        try {
            $pdo->beginTransaction();
            // Update product stock and min level
            $stmt = $pdo->prepare("UPDATE products SET stock_quantity = ?, min_stock_level = ? WHERE product_id = ?");
            $stmt->execute([$new_stock_quantity, $min_stock_level, $product_id]);
            // Log as adjustment (read/update only, but keep audit trail)
            if ($quantity_changed !== 0) {
                $stmt = $pdo->prepare("INSERT INTO inventory (product_id, change_type, quantity, created_by) VALUES (?, 'adjustment', ?, ?)");
                $stmt->execute([$product_id, abs($quantity_changed), $_SESSION['user_id']]);
            }
            $pdo->commit();
            $message = "Inventory updated successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to update inventory: " . $e->getMessage();
        }
    }
}

// Get inventory summary data
$total_products = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
$low_stock_items = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= min_stock_level AND status = 'active'")->fetchColumn();
$critical_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity = 0 AND status = 'active'")->fetchColumn();
$stock_value = 0; // Removed from UI

// Get inventory items with search and filters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where_conditions = ["p.status = 'active'"];
$params = [];

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.product_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if ($status_filter) {
    switch ($status_filter) {
        case 'low_stock':
            $where_conditions[] = "p.stock_quantity <= p.min_stock_level";
            break;
        case 'out_of_stock':
            $where_conditions[] = "p.stock_quantity = 0";
            break;
        case 'in_stock':
            $where_conditions[] = "p.stock_quantity > p.min_stock_level";
            break;
    }
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Get total count
$count_sql = "SELECT COUNT(*) FROM products p $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Get inventory items
$sql = "SELECT p.*, c.name as category_name, s.name as subcategory_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        LEFT JOIN subcategories s ON p.subcategory_id = s.subcategory_id 
        $where_clause 
        ORDER BY p.stock_quantity ASC, p.name ASC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$inventory_items = $stmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - FitFuel Admin</title>
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
        .status-low {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-critical {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status-good {
            background-color: #d1fae5;
            color: #065f46;
        }
        .summary-card {
            background: white;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        .summary-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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
                    <a href="product.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
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
                    <a href="inventory.php" class="sidebar-item active flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
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
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <i class="fas fa-archive text-2xl text-gray-600"></i>
                        <h1 class="text-3xl font-bold text-gray-900">Inventory</h1>
                    </div>
                    <p class="text-gray-600">Monitor And Manage Your Product Inventory</p>
                </div>
                <!-- Add Stock button removed as per requirements -->
            </div>
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

        <!-- Inventory Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="summary-card rounded-lg p-6">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-cube text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-700">Total Products</h3>
                        <p class="text-2xl font-bold text-blue-600"><?php echo $total_products; ?></p>
                    </div>
                </div>
            </div>

            <div class="summary-card rounded-lg p-6">
                <div class="flex items-center">
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-700">Low Stock Items</h3>
                        <p class="text-2xl font-bold text-yellow-600"><?php echo $low_stock_items; ?></p>
                    </div>
                </div>
            </div>

            <div class="summary-card rounded-lg p-6">
                <div class="flex items-center">
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-700">Critical Stock</h3>
                        <p class="text-2xl font-bold text-red-600"><?php echo $critical_stock; ?></p>
                    </div>
                </div>
            </div>

            <!-- Stock Value card removed as per requirements -->
        </div>

        <!-- Inventory Items -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-archive text-gray-600"></i>
                        <h2 class="text-lg font-semibold text-gray-900">Inventory Items</h2>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <div class="relative">
                            <input type="text" 
                                   placeholder="Search Products..." 
                                   class="search-input w-full sm:w-64 pl-4 pr-10 py-2 rounded-lg focus:outline-none"
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   onkeypress="handleSearch(event)">
                            <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        
                        <select class="filter-btn px-4 py-2 rounded-lg focus:outline-none" onchange="filterInventory()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" <?php echo $category_filter == $category['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select class="filter-btn px-4 py-2 rounded-lg focus:outline-none" onchange="filterInventory()">
                            <option value="">All Status</option>
                            <option value="low_stock" <?php echo $status_filter == 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                            <option value="out_of_stock" <?php echo $status_filter == 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                            <option value="in_stock" <?php echo $status_filter == 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                        </select>
                        
                        <!-- Export button removed as per requirements -->
                    </div>
                </div>
            </div>
            
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min. Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($inventory_items)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-archive text-4xl mb-4"></i>
                                    <p>No inventory items found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inventory_items as $item): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php 
                                            $product_images = json_decode($item['images'] ?: '[]', true);
                                            if (!empty($product_images)): 
                                            ?>
                                                <img src="../<?php echo htmlspecialchars($product_images[0]); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-12 h-12 rounded-lg object-cover mr-4">
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center mr-4">
                                                    <i class="fas fa-cube text-gray-500"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($item['description'], 0, 30)) . '...'; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- SKU column removed -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($item['category_name'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo $item['stock_quantity']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $item['min_stock_level']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ₱<?php echo number_format($item['price'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $stock_status = '';
                                        $status_class = '';
                                        if ($item['stock_quantity'] == 0) {
                                            $stock_status = 'Out of Stock';
                                            $status_class = 'status-critical';
                                        } elseif ($item['stock_quantity'] <= $item['min_stock_level']) {
                                            $stock_status = 'Low Stock';
                                            $status_class = 'status-low';
                                        } else {
                                            $stock_status = 'In Stock';
                                            $status_class = 'status-good';
                                        }
                                        ?>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $status_class; ?>">
                                            <?php echo $stock_status; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="openStockModal(<?php echo $item['product_id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>', <?php echo $item['stock_quantity']; ?>, <?php echo $item['min_stock_level']; ?>)" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i>
                                        </button>
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
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $total_items); ?> of <?php echo $total_items; ?> results
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

    <!-- Inventory Update Modal -->
    <div id="stockModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Update Inventory</h3>
                        <button onclick="closeStockModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <form id="stockForm" method="POST">
                        <input type="hidden" name="action" value="update_inventory">
                        <input type="hidden" name="product_id" id="stockProductId">
                        <input type="hidden" name="current_stock" id="currentStockValue">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                            <div class="p-3 bg-gray-100 rounded-lg">
                                <span id="stockProductName" class="font-medium"></span>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Stock Quantity</label>
                            <input type="number" name="stock_quantity" id="stockQuantityInput" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Stock Level</label>
                            <input type="number" name="min_stock_level" id="minStockInput" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeStockModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:outline-none">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 focus:outline-none">
                                Update Stock
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openStockModal(productId, productName, currentStock, minStock) {
            document.getElementById('stockProductId').value = productId;
            document.getElementById('stockProductName').textContent = productName;
            document.getElementById('currentStockValue').value = currentStock;
            document.getElementById('stockQuantityInput').value = currentStock;
            document.getElementById('minStockInput').value = minStock;
            document.getElementById('stockModal').classList.remove('hidden');
        }
        
        function closeStockModal() {
            document.getElementById('stockModal').classList.add('hidden');
        }
        
        // Calculation removed; only minimum stock level is editable now
        
        // Add Stock flow removed
        
        function handleSearch(event) {
            if (event.key === 'Enter') {
                filterInventory();
            }
        }
        
        function filterInventory() {
            const search = document.querySelector('input[placeholder="Search Products..."]').value;
            const category = document.querySelectorAll('select')[0].value;
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
    </script>
</body>
</html>
