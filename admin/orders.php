<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';

// -------------------------------
// Schema bootstrap (idempotent)
// -------------------------------
function ensureOrdersSchema(PDO $pdo): void {
	$pdo->exec("CREATE TABLE IF NOT EXISTS orders (
		order_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT UNSIGNED NOT NULL,
		status ENUM('pending','processing','shipped','delivered','cancelled','returned','refunded') NOT NULL DEFAULT 'pending',
		payment_method VARCHAR(50) DEFAULT NULL,
		payment_status ENUM('pending','paid','refunded','failed') NOT NULL DEFAULT 'pending',
		shipping_address TEXT DEFAULT NULL,
		total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
		estimated_delivery_date DATE DEFAULT NULL,
		return_reason TEXT DEFAULT NULL,
		refund_amount DECIMAL(10,2) DEFAULT NULL,
		stock_deducted TINYINT(1) NOT NULL DEFAULT 0,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (order_id),
		KEY user_id (user_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

	$pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
		order_item_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		order_id BIGINT UNSIGNED NOT NULL,
		product_id BIGINT UNSIGNED NOT NULL,
		quantity INT NOT NULL,
		price DECIMAL(10,2) NOT NULL,
		PRIMARY KEY (order_item_id),
		KEY order_id (order_id),
		KEY product_id (product_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

	// Add FKs if not present (ignore if already exist)
	try { $pdo->exec("ALTER TABLE orders ADD CONSTRAINT orders_users_fk FOREIGN KEY (user_id) REFERENCES users(user_id)"); } catch (Throwable $e) {}
	try { $pdo->exec("ALTER TABLE order_items ADD CONSTRAINT order_items_orders_fk FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE"); } catch (Throwable $e) {}
	try { $pdo->exec("ALTER TABLE order_items ADD CONSTRAINT order_items_products_fk FOREIGN KEY (product_id) REFERENCES products(product_id)"); } catch (Throwable $e) {}
}

/**
 * Deduct stock and log inventory for an order. Idempotent per order via orders.stock_deducted flag.
 */
function applyStockControl(PDO $pdo, int $orderId, int $adminUserId): void {
	$pdo->beginTransaction();
	try {
		$order = $pdo->prepare("SELECT stock_deducted FROM orders WHERE order_id = ? FOR UPDATE");
		$order->execute([$orderId]);
		$row = $order->fetch();
		if (!$row) { throw new RuntimeException('Order not found'); }
		if ((int)$row['stock_deducted'] === 1) {
			$pdo->commit();
			return; // already applied
		}

		$items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
		$items->execute([$orderId]);
		$all = $items->fetchAll();
		foreach ($all as $it) {
			// Reduce product stock
			$upd = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
			$upd->execute([(int)$it['quantity'], (int)$it['product_id']]);
			// Log inventory movement
			$inv = $pdo->prepare("INSERT INTO inventory (product_id, change_type, quantity, reference_id, created_by) VALUES (?, 'stock_out', ?, ?, ?)");
			$inv->execute([(int)$it['product_id'], (int)$it['quantity'], $orderId, $adminUserId]);
		}

		$pdo->prepare("UPDATE orders SET stock_deducted = 1 WHERE order_id = ?")->execute([$orderId]);
		$pdo->commit();
	} catch (Throwable $e) {
		$pdo->rollBack();
		throw $e;
	}
}

$pdo = getDBConnection();
ensureOrdersSchema($pdo);

// -------------------------------
// Actions (CRUD-ish for admin)
// -------------------------------
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$message = null;
$error = null;

try {
	if ($action === 'update_status') {
		$orderId = (int)($_POST['order_id'] ?? 0);
		$newStatus = $_POST['status'] ?? 'pending';
		$paymentStatus = $_POST['payment_status'] ?? null;
		$paymentMethod = $_POST['payment_method'] ?? null;
		$shippingAddress = $_POST['shipping_address'] ?? null;
		$deliveryDate = $_POST['estimated_delivery_date'] ?? null;
		
		$allowed = ['pending','processing','shipped','delivered','cancelled','returned','refunded'];
		if (!in_array($newStatus, $allowed, true)) { throw new InvalidArgumentException('Invalid status'); }
		
		// Update all fields in one query
		$stmt = $pdo->prepare("UPDATE orders SET status = ?, payment_status = COALESCE(?, payment_status), payment_method = COALESCE(?, payment_method), shipping_address = COALESCE(?, shipping_address), estimated_delivery_date = COALESCE(?, estimated_delivery_date) WHERE order_id = ?");
		$stmt->execute([$newStatus, $paymentStatus, $paymentMethod, $shippingAddress, $deliveryDate, $orderId]);
		
		// Stock control on processing or paid
		if (in_array($newStatus, ['processing','shipped','delivered'], true)) {
			applyStockControl($pdo, $orderId, (int)($_SESSION['user_id'] ?? 0));
		}
		$message = 'Order updated successfully.';
	}
	if ($action === 'update_payment') {
		$orderId = (int)($_POST['order_id'] ?? 0);
		$paymentStatus = $_POST['payment_status'] ?? 'pending';
		$paymentMethod = $_POST['payment_method'] ?? null;
		$allowed = ['pending','paid','refunded','failed'];
		if (!in_array($paymentStatus, $allowed, true)) { throw new InvalidArgumentException('Invalid payment status'); }
		$stmt = $pdo->prepare("UPDATE orders SET payment_status = ?, payment_method = COALESCE(?, payment_method) WHERE order_id = ?");
		$stmt->execute([$paymentStatus, $paymentMethod, $orderId]);
		if ($paymentStatus === 'paid') {
			applyStockControl($pdo, $orderId, (int)($_SESSION['user_id'] ?? 0));
		}
		$message = 'Payment updated.';
	}
	if ($action === 'update_shipping') {
		$orderId = (int)($_POST['order_id'] ?? 0);
		$address = trim($_POST['shipping_address'] ?? '');
		$eta = $_POST['estimated_delivery_date'] ?? null;
		$stmt = $pdo->prepare("UPDATE orders SET shipping_address = ?, estimated_delivery_date = ? WHERE order_id = ?");
		$stmt->execute([$address ?: null, $eta ?: null, $orderId]);
		$message = 'Shipping info updated.';
	}
	if ($action === 'approve_return') {
		$orderId = (int)($_POST['order_id'] ?? 0);
		$reason = trim($_POST['return_reason'] ?? '');
		$refund = (float)($_POST['refund_amount'] ?? 0);
		$stmt = $pdo->prepare("UPDATE orders SET status = 'returned', payment_status = CASE WHEN refund_amount IS NULL OR refund_amount = 0 THEN 'refunded' ELSE payment_status END, return_reason = ?, refund_amount = ? WHERE order_id = ?");
		$stmt->execute([$reason ?: null, $refund ?: null, $orderId]);
		$message = 'Return approved.';
	}
	if ($action === 'reject_return') {
		$orderId = (int)($_POST['order_id'] ?? 0);
		$stmt = $pdo->prepare("UPDATE orders SET status = 'processing', return_reason = NULL, refund_amount = NULL WHERE order_id = ?");
		$stmt->execute([$orderId]);
		$message = 'Return rejected.';
	}
	if ($action === 'cancel_order') {
		$orderId = (int)($_POST['order_id'] ?? 0);
		// Do not cancel if already processing or beyond
		$st = $pdo->prepare("SELECT status FROM orders WHERE order_id = ?");
		$st->execute([$orderId]);
		$cur = $st->fetch();
		if ($cur && in_array($cur['status'], ['processing','shipped','delivered'], true)) {
			throw new RuntimeException('Cannot cancel processed/shipped/delivered orders.');
		}
		$pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?")->execute([$orderId]);
		$message = 'Order cancelled.';
	}
	// Optional: endpoint that customer checkout can call to create orders (simple payload for now)
	if ($action === 'create') {
		$userId = (int)($_POST['user_id'] ?? 0);
		$address = trim($_POST['shipping_address'] ?? '');
		$paymentMethod = $_POST['payment_method'] ?? null;
		$items = json_decode($_POST['items'] ?? '[]', true) ?: [];
		$total = 0;
		foreach ($items as $it) { $total += ((float)$it['price']) * ((int)$it['quantity']); }
		$pdo->beginTransaction();
		try {
			$ins = $pdo->prepare("INSERT INTO orders (user_id, payment_method, shipping_address, total_amount) VALUES (?,?,?,?)");
			$ins->execute([$userId, $paymentMethod, $address, $total]);
			$orderId = (int)$pdo->lastInsertId();
			$insIt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)");
			foreach ($items as $it) {
				$insIt->execute([$orderId, (int)$it['product_id'], (int)$it['quantity'], (float)$it['price']]);
			}
			$pdo->commit();
			$message = 'Order created.';
		} catch (Throwable $e) {
			$pdo->rollBack();
			throw $e;
		}
	}
} catch (Throwable $ex) {
	$error = $ex->getMessage();
}

// -------------------------------
// Filters & fetch
// -------------------------------
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$filterDate = $_GET['date'] ?? '';

$where = [];
$params = [];
if ($statusFilter !== '') { $where[] = 'o.status = ?'; $params[] = $statusFilter; }
if ($search !== '') { $where[] = '(u.username LIKE ? OR u.email LIKE ? OR o.order_id = ?)'; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = ctype_digit($search) ? (int)$search : 0; }
if ($filterDate !== '') { $where[] = 'DATE(o.created_at) = ?'; $params[] = $filterDate; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT o.*, u.username, u.email FROM orders o JOIN users u ON u.user_id = o.user_id $whereSql ORDER BY o.created_at DESC LIMIT 500";
$stm = $pdo->prepare($sql);
$stm->execute($params);
$orders = $stm->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Orders - Admin</title>
	<link rel="icon" href="../img/LOGO-Fitfuel.png" type="image/png">
	<script src="https://cdn.tailwindcss.com"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../css/style.css">
	<style>
		.sidebar-item.active { background-color: #f3f4f6; border-right: 3px solid #000; }
		.sidebar-item:hover { background-color: #f9fafb; }
		.badge { padding: 2px 8px; border-radius: 9999px; font-size: 12px; }
		.search-input { background-color: #f8f9fa; border: 1px solid #e9ecef; }
		.search-input:focus { background-color: #ffffff; border-color: #6c757d; }
		.filter-btn { background-color: #f8f9fa; border: 1px solid #e9ecef; color: #374151; }
		.filter-btn:focus { background-color: #ffffff; border-color: #6c757d; }
	</style>
    <script>
        function toggleUserMenu(){
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const userButton = event.target.closest('[onclick="toggleUserMenu()"]');
            if (!userButton && userMenu && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
            }
        });
    </script>
</head>
<body class="font-body bg-gray-50">
	<header class="bg-black text-white fixed top-0 left-0 right-0 z-50 h-16 flex items-center justify-between px-6">
		<div class="flex items-center space-x-3">
            <img src="../img/LOGO-Fitfuel.png" alt="FitFuel Logo" class="w-8 h-8 object-contain">
            <div class="w-px h-6 bg-white"></div>
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
					<a href="orders.php" class="sidebar-item active flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
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
	</aside>
	<main class="ml-64 pt-24 pb-6 px-6">
		<!-- Page Header -->
		<div class="mb-8">
			<div class="flex items-center space-x-3 mb-2">
				<i class="fas fa-shopping-cart text-2xl text-gray-600"></i>
				<h1 class="text-3xl font-bold text-gray-900">Orders</h1>
			</div>
			<p class="text-gray-600">Manage customer orders and fulfillment.</p>
		</div>
		<?php if ($message): ?>
			<div class="mb-4 p-3 bg-green-100 text-green-800 rounded"><?php echo htmlspecialchars($message); ?></div>
		<?php endif; ?>
		<?php if ($error): ?>
			<div class="mb-4 p-3 bg-red-100 text-red-800 rounded"><?php echo htmlspecialchars($error); ?></div>
		<?php endif; ?>

		<div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
		<form id="orderFilters" method="get" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
			<div class="relative md:col-span-6">
				<i class="fas fa-shopping-cart absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
				<input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Orders" class="search-input w-full pl-10 pr-10 py-2 rounded-lg focus:outline-none" />
				<i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
			</div>
			<div class="md:col-span-3">
				<select name="status" class="filter-btn px-3 py-2 rounded-lg w-full focus:outline-none">
					<option value="">All Status</option>
					<?php foreach (['pending','processing','shipped','delivered','cancelled','returned','refunded'] as $st): ?>
						<option value="<?php echo $st; ?>" <?php echo $statusFilter===$st?'selected':''; ?>><?php echo ucfirst($st); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="md:col-span-2">
				<input type="date" name="date" value="<?php echo htmlspecialchars($filterDate); ?>" class="filter-btn px-3 py-2 rounded-lg w-full focus:outline-none" />
			</div>
			<div class="md:col-span-1 md:justify-self-end">
				<a href="orders.php" class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 inline-block whitespace-nowrap">Clear Filters</a>
			</div>
		</form>
		</div>

		<div class="bg-white border border-gray-200 rounded-lg overflow-x-auto">
		<div class="bg-white border border-gray-200 rounded-lg overflow-x-auto">
			<table class="min-w-full text-sm">
				<thead class="bg-gray-50">
					<tr>
						<th class="text-left p-3">Order</th>
						<th class="text-left p-3">Customer</th>
						<th class="text-left p-3">Created</th>
						<th class="text-left p-3">Total</th>
						<th class="text-left p-3">Status</th>
						<th class="text-left p-3">Payment</th>
						<th class="text-left p-3">ETA</th>
						<th class="text-left p-3">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if (!$orders): ?>
						<tr><td colspan="8" class="p-6 text-center text-gray-500">No orders found.</td></tr>
					<?php else: foreach ($orders as $o): ?>
					<tr class="border-t">
						<td class="p-3">#<?php echo (int)$o['order_id']; ?></td>
						<td class="p-3"><?php echo htmlspecialchars($o['username']); ?> <span class="text-gray-500 text-xs"><?php echo htmlspecialchars($o['email']); ?></span></td>
						<td class="p-3"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($o['created_at']))); ?></td>
						<td class="p-3">₱<?php echo number_format((float)$o['total_amount'], 2); ?></td>
						<td class="p-3">
							<span class="px-2 py-1 rounded-full text-xs font-semibold <?php 
								$statusClass = '';
								switch($o['status']) {
									case 'delivered':
										$statusClass = 'bg-green-100 text-green-800 border border-green-200';
										break;
									case 'processing':
										$statusClass = 'bg-blue-100 text-blue-800 border border-blue-200';
										break;
									case 'shipped':
										$statusClass = 'bg-purple-100 text-purple-800 border border-purple-200';
										break;
									case 'cancelled':
										$statusClass = 'bg-red-100 text-red-800 border border-red-200';
										break;
									case 'returned':
										$statusClass = 'bg-orange-100 text-orange-800 border border-orange-200';
										break;
									case 'refunded':
										$statusClass = 'bg-gray-100 text-gray-800 border border-gray-200';
										break;
									default:
										$statusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
										break;
								}
								echo $statusClass;
							?>">
								<?php echo ucfirst($o['status']); ?>
							</span>
						</td>
						<td class="p-3">
							<div class="flex flex-col space-y-1">
								<span class="px-2 py-1 rounded-full text-xs font-semibold <?php 
									$paymentStatusClass = '';
									switch($o['payment_status']) {
										case 'paid':
											$paymentStatusClass = 'bg-green-100 text-green-800 border border-green-200';
											break;
										case 'pending':
											$paymentStatusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
											break;
										case 'refunded':
											$paymentStatusClass = 'bg-gray-100 text-gray-800 border border-gray-200';
											break;
										default:
											$paymentStatusClass = 'bg-red-100 text-red-800 border border-red-200';
											break;
									}
									echo $paymentStatusClass;
								?>">
									<?php echo ucfirst($o['payment_status']); ?>
								</span>
							</div>
						</td>
						<td class="p-3"><?php echo $o['estimated_delivery_date'] ? htmlspecialchars($o['estimated_delivery_date']) : '-'; ?></td>
						<td class="p-3">
							<div class="flex items-center space-x-2">
								<a href="view_order.php?id=<?php echo (int)$o['order_id']; ?>" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
									<i class="fas fa-eye mr-1"></i>
									View Order
								</a>
								<button onclick="openOrderModal(<?php echo htmlspecialchars(json_encode($o)); ?>)" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
									<i class="fas fa-edit mr-1"></i>
									Edit
								</button>
								<?php if (!in_array($o['status'], ['processing','shipped','delivered','cancelled'], true)): ?>
								<form method="post" class="inline" onsubmit="return confirm('Cancel this order?');">
									<input type="hidden" name="action" value="cancel_order" />
									<input type="hidden" name="order_id" value="<?php echo (int)$o['order_id']; ?>" />
									<button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
										<i class="fas fa-times mr-1"></i>
										Cancel
									</button>
								</form>
								<?php endif; ?>
							</div>
						</td>
					</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
	</main>

	<!-- Order Edit Modal -->
	<div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
		<div class="flex items-center justify-center min-h-screen p-4">
			<div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
				<div class="flex items-center justify-between p-6 border-b">
					<h3 class="text-xl font-semibold text-gray-900">Edit Order</h3>
					<button onclick="closeOrderModal()" class="text-gray-400 hover:text-gray-600">
						<i class="fas fa-times text-xl"></i>
					</button>
				</div>
				
				<form id="orderEditForm" method="post" class="p-6 space-y-6">
					<input type="hidden" name="order_id" id="modalOrderId">
					
					<!-- Order Status -->
					<div>
						<label class="block text-sm font-medium text-gray-700 mb-2">Order Status</label>
						<select name="status" id="modalStatus" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
							<option value="pending">Pending</option>
							<option value="processing">Processing</option>
							<option value="shipped">Shipped</option>
							<option value="delivered">Delivered</option>
							<option value="cancelled">Cancelled</option>
							<option value="returned">Returned</option>
							<option value="refunded">Refunded</option>
						</select>
					</div>
					
					<!-- Payment Information -->
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
							<select name="payment_status" id="modalPaymentStatus" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
								<option value="pending">Pending</option>
								<option value="paid">Paid</option>
								<option value="refunded">Refunded</option>
								<option value="failed">Failed</option>
							</select>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
							<select name="payment_method" id="modalPaymentMethod" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
								<option value="">Select Method</option>
								<option value="cod">Cash on Delivery</option>
								<option value="paypal">PayPal</option>
							</select>
						</div>
					</div>
					
					<!-- Shipping Information -->
					<div>
						<label class="block text-sm font-medium text-gray-700 mb-2">Shipping Address</label>
						<div class="space-y-3">
							<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
								<div>
									<label class="block text-xs text-gray-600 mb-1">Full Name</label>
									<input type="text" name="shipping_full_name" id="modalShippingFullName" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Full Name">
								</div>
								<div>
									<label class="block text-xs text-gray-600 mb-1">Phone Number</label>
									<input type="text" name="shipping_phone" id="modalShippingPhone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Phone Number">
								</div>
							</div>
							<div>
								<label class="block text-xs text-gray-600 mb-1">Street Address</label>
								<input type="text" name="shipping_address" id="modalShippingAddress" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Street Address">
							</div>
							<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
								<div>
									<label class="block text-xs text-gray-600 mb-1">City</label>
									<input type="text" name="shipping_city" id="modalShippingCity" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="City">
								</div>
								<div>
									<label class="block text-xs text-gray-600 mb-1">State/Region</label>
									<input type="text" name="shipping_state" id="modalShippingState" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="State/Region">
								</div>
								<div>
									<label class="block text-xs text-gray-600 mb-1">Postal Code</label>
									<input type="text" name="shipping_postal_code" id="modalShippingPostalCode" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Postal Code">
								</div>
							</div>
						</div>
					</div>
					
					<div>
						<label class="block text-sm font-medium text-gray-700 mb-2">Estimated Delivery Date</label>
						<input type="date" name="estimated_delivery_date" id="modalDeliveryDate" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
					</div>
					
					<!-- Return Management -->
					<div class="border-t pt-6">
						<h4 class="text-lg font-medium text-gray-900 mb-4">Return Management</h4>
						<div class="space-y-4">
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-2">Return Reason</label>
								<input type="text" name="return_reason" id="modalReturnReason" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter return reason">
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-2">Refund Amount (₱)</label>
								<input type="number" step="0.01" name="refund_amount" id="modalRefundAmount" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0.00">
							</div>
						</div>
					</div>
					
					<!-- Action Buttons -->
					<div class="flex items-center justify-end space-x-3 pt-6 border-t">
						<button type="button" onclick="closeOrderModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
							Cancel
						</button>
						<button type="submit" name="action" value="update_status" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
							<i class="fas fa-save mr-1"></i>
							Save Changes
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<script>
		// basic UX sugar: keep sidebar active
		document.querySelectorAll('.sidebar-item').forEach(i=>{
			if(i.getAttribute('href')==='#' || !i.getAttribute('href')) return;
			if(location.pathname.endsWith(i.getAttribute('href'))) i.classList.add('active');
		});
		// Auto-submit filters on change
		const filterForm = document.getElementById('orderFilters');
		if (filterForm) {
			filterForm.querySelectorAll('select, input[type="date"]').forEach(el => {
				el.addEventListener('change', () => filterForm.submit());
			});
		}
		
		// Order Modal Functions
		function openOrderModal(orderData) {
			// Populate form fields with order data
			document.getElementById('modalOrderId').value = orderData.order_id;
			document.getElementById('modalStatus').value = orderData.status;
			document.getElementById('modalPaymentStatus').value = orderData.payment_status;
			// Set payment method - handle different formats
			const paymentMethod = orderData.payment_method || '';
			const paymentMethodSelect = document.getElementById('modalPaymentMethod');
			
			// Debug: log the payment method value
			console.log('Payment method from database:', paymentMethod);
			
			// Map different payment method formats to dropdown values
			let selectedValue = '';
			if (paymentMethod === 'cod') {
				selectedValue = 'cod';
			} else if (paymentMethod === 'paypal') {
				selectedValue = 'paypal';
			}
			
			console.log('Selected value for dropdown:', selectedValue);
			paymentMethodSelect.value = selectedValue;
			document.getElementById('modalDeliveryDate').value = orderData.estimated_delivery_date || '';
			document.getElementById('modalReturnReason').value = orderData.return_reason || '';
			document.getElementById('modalRefundAmount').value = orderData.refund_amount || '';
			
			// Parse shipping address JSON
			let shippingData = {};
			try {
				if (orderData.shipping_address) {
					shippingData = JSON.parse(orderData.shipping_address);
				}
			} catch (e) {
				console.log('Error parsing shipping address:', e);
			}
			
			// Populate shipping address fields
			document.getElementById('modalShippingFullName').value = shippingData.full_name || '';
			document.getElementById('modalShippingPhone').value = shippingData.phone || '';
			document.getElementById('modalShippingAddress').value = shippingData.address || '';
			document.getElementById('modalShippingCity').value = shippingData.city || '';
			document.getElementById('modalShippingState').value = shippingData.state || '';
			document.getElementById('modalShippingPostalCode').value = shippingData.postal_code || '';
			
			// Show modal
			document.getElementById('orderModal').classList.remove('hidden');
		}
		
		function closeOrderModal() {
			document.getElementById('orderModal').classList.add('hidden');
		}
		
		// Close modal when clicking outside
		document.getElementById('orderModal').addEventListener('click', function(e) {
			if (e.target === this) {
				closeOrderModal();
			}
		});
		
		// Handle form submission
		document.getElementById('orderEditForm').addEventListener('submit', function(e) {
			// Reconstruct shipping address JSON from individual fields
			const shippingData = {
				full_name: document.getElementById('modalShippingFullName').value,
				phone: document.getElementById('modalShippingPhone').value,
				address: document.getElementById('modalShippingAddress').value,
				city: document.getElementById('modalShippingCity').value,
				state: document.getElementById('modalShippingState').value,
				postal_code: document.getElementById('modalShippingPostalCode').value
			};
			
			// Create hidden input for reconstructed shipping address
			const shippingInput = document.createElement('input');
			shippingInput.type = 'hidden';
			shippingInput.name = 'shipping_address';
			shippingInput.value = JSON.stringify(shippingData);
			this.appendChild(shippingInput);
			
			// Map payment method dropdown value back to database format
			const paymentMethodValue = document.getElementById('modalPaymentMethod').value;
			let dbPaymentMethod = '';
			if (paymentMethodValue === 'cod') {
				dbPaymentMethod = 'cod';
			} else if (paymentMethodValue === 'paypal') {
				dbPaymentMethod = 'paypal';
			}
			
			// Create hidden input for payment method in database format
			const paymentMethodInput = document.createElement('input');
			paymentMethodInput.type = 'hidden';
			paymentMethodInput.name = 'payment_method';
			paymentMethodInput.value = dbPaymentMethod;
			this.appendChild(paymentMethodInput);
			
			// Determine which action to take based on the form data
			const formData = new FormData(this);
			const returnReason = formData.get('return_reason');
			const refundAmount = formData.get('refund_amount');
			
			// Create hidden inputs for the appropriate action
			if (returnReason || refundAmount) {
				// If return fields are filled, handle as return approval
				const returnInput = document.createElement('input');
				returnInput.type = 'hidden';
				returnInput.name = 'action';
				returnInput.value = 'approve_return';
				this.appendChild(returnInput);
			} else {
				// Otherwise, handle as status update
				const statusInput = document.createElement('input');
				statusInput.type = 'hidden';
				statusInput.name = 'action';
				statusInput.value = 'update_status';
				this.appendChild(statusInput);
			}
		});
	</script>
</body>
</html>


