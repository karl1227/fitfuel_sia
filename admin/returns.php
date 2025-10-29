<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';
require_once '../config/stock_control.php';
require_once '../config/audit_logger.php';
require_once '../config/currency_helper.php';
require_once '../includes/admin_sidebar.php';

// Check role-based access
requireAccess('orders');

$pdo = getDBConnection();
$message = null;
$error = null;

// Get order filter if provided
$order_filter = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'approve_return') {
            $return_id = (int)($_POST['return_id'] ?? 0);
            $refund_amount = isset($_POST['refund_amount']) ? (float)$_POST['refund_amount'] : 0;
            $admin_notes = trim($_POST['admin_notes'] ?? '');
            
            // Get return details
            $returnStmt = $pdo->prepare("SELECT r.*, oi.product_id, oi.quantity FROM returns r
                                         JOIN order_items oi ON r.order_item_id = oi.order_item_id
                                         WHERE r.return_id = ?");
            $returnStmt->execute([$return_id]);
            $return = $returnStmt->fetch();
            
            if ($return) {
                $pdo->beginTransaction();
                
                // Update return status
                $updateStmt = $pdo->prepare("UPDATE returns SET status = 'approved', refund_amount = ?, admin_notes = ? WHERE return_id = ?");
                $updateStmt->execute([$refund_amount, $admin_notes, $return_id]);
                
                // Get return type to determine order status
                $rtStmt = $pdo->prepare("SELECT return_type FROM returns WHERE return_id = ?");
                $rtStmt->execute([$return_id]);
                $rtData = $rtStmt->fetch();
                $returnType = $rtData['return_type'] ?? 'return';
                
                // Do NOT change the overall order status when a single item is returned/refunded.
                // Order-level payment_status may still be updated when ALL items are refunded below.
                
                // Accumulate refund amount on the order for reporting consistency
                if ($refund_amount !== null && $refund_amount !== '') {
                    $sumStmt = $pdo->prepare("UPDATE orders SET refund_amount = COALESCE(refund_amount,0) + ? WHERE order_id = ?");
                    $sumStmt->execute([(float)$refund_amount, $return['order_id']]);
                }

                // Update order payment status to refunded if applicable
                // Do not set payment_status to refunded here; it will be set when ALL items in the order are refunded.
                
                $pdo->commit();
                $message = 'Return approved and refund processed.';
                // Redirect to filtered view if applicable, or stay on page
                if ($order_filter || isset($return['order_id'])) {
                    $redirectOrderId = $order_filter ?? $return['order_id'];
                    header("Location: returns.php?order_id=" . $redirectOrderId);
                    exit();
                }
            }
        }
        
        if ($action === 'reject_return') {
            $return_id = (int)($_POST['return_id'] ?? 0);
            $admin_notes = trim($_POST['admin_notes'] ?? '');
            
            $updateStmt = $pdo->prepare("UPDATE returns SET status = 'rejected', admin_notes = ? WHERE return_id = ?");
            $updateStmt->execute([$admin_notes, $return_id]);
            
            // Get order_id from return for redirect
            $rStmt = $pdo->prepare("SELECT order_id FROM returns WHERE return_id = ?");
            $rStmt->execute([$return_id]);
            $retData = $rStmt->fetch();
            
            $message = 'Return rejected.';
            // Redirect to filtered view if applicable
            if ($order_filter || ($retData && isset($retData['order_id']))) {
                $redirectOrderId = $order_filter ?? $retData['order_id'];
                header("Location: returns.php?order_id=" . $redirectOrderId);
                exit();
            }
        }
        
        if ($action === 'process_refund') {
            $return_id = (int)($_POST['return_id'] ?? 0);
            
            // Mark this return as refunded
            $updateStmt = $pdo->prepare("UPDATE returns SET refund_status = 'processed' WHERE return_id = ?");
            $updateStmt->execute([$return_id]);

            // Fetch order id for this return
            $rStmt = $pdo->prepare("SELECT order_id FROM returns WHERE return_id = ?");
            $rStmt->execute([$return_id]);
            $ret = $rStmt->fetch();
            if ($ret) {
                $orderId = (int)$ret['order_id'];
                // Count total items in the order
                $tiStmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
                $tiStmt->execute([$orderId]);
                $totalItems = (int)$tiStmt->fetchColumn();

                // Count distinct items refunded
                $riStmt = $pdo->prepare("SELECT COUNT(DISTINCT order_item_id) FROM returns WHERE order_id = ? AND refund_status = 'processed'");
                $riStmt->execute([$orderId]);
                $refundedItems = (int)$riStmt->fetchColumn();

                if ($totalItems > 0 && $refundedItems >= $totalItems) {
                    // All items refunded -> set order payment_status to refunded
                    $pdo->prepare("UPDATE orders SET payment_status = 'refunded' WHERE order_id = ?")
                        ->execute([$orderId]);
                }
            }

            $message = 'Refund processed.';
            // Redirect to filtered view if applicable
            if ($order_filter || ($ret && isset($ret['order_id']))) {
                $redirectOrderId = $order_filter ?? $ret['order_id'];
                header("Location: returns.php?order_id=" . $redirectOrderId);
                exit();
            }
        }
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e->getMessage();
    }
}

// Fetch returns - optionally filter by order_id if provided
try {
    if ($order_filter) {
        $returnsStmt = $pdo->prepare("
            SELECT r.*, 
                   u.username, u.email,
                   p.name as product_name, p.images,
                   o.custom_order_id, o.total_amount as order_total,
                   oi.price as item_price, oi.quantity as item_quantity
            FROM returns r
            JOIN users u ON r.user_id = u.user_id
            JOIN products p ON r.product_id = p.product_id
            JOIN orders o ON r.order_id = o.order_id
            JOIN order_items oi ON r.order_item_id = oi.order_item_id
            WHERE r.order_id = ?
            ORDER BY r.created_at DESC
        ");
        $returnsStmt->execute([$order_filter]);
    } else {
        $returnsStmt = $pdo->query("
            SELECT r.*, 
                   u.username, u.email,
                   p.name as product_name, p.images,
                   o.custom_order_id, o.total_amount as order_total,
                   oi.price as item_price, oi.quantity as item_quantity
            FROM returns r
            JOIN users u ON r.user_id = u.user_id
            JOIN products p ON r.product_id = p.product_id
            JOIN orders o ON r.order_id = o.order_id
            JOIN order_items oi ON r.order_item_id = oi.order_item_id
            ORDER BY r.created_at DESC
        ");
    }
    $returns = $returnsStmt->fetchAll();
} catch (Exception $e) {
    $returns = [];
    $error = "Error fetching returns: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returns & Refunds - Admin</title>
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
        .badge {
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 12px;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
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
        
        // Approve Return Modal
        const currencySymbol = '<?= htmlspecialchars(getCurrencySymbol(), ENT_QUOTES) ?>';
        function openApproveModal(data) {
            document.getElementById('modalReturnId').value = data.return_id;
            document.getElementById('modalProductName').textContent = data.product_name;
            document.getElementById('modalItemPrice').textContent = currencySymbol + parseFloat(data.item_price || 0).toFixed(2);
            document.getElementById('modalItemQuantity').textContent = data.item_quantity || 1;
            document.getElementById('modalRefundAmount').value = parseFloat(data.suggested_refund || 0).toFixed(2);
            document.getElementById('modalAdminNotes').value = '';
            document.getElementById('approveReturnModal').classList.remove('hidden');
        }
        
        function closeApproveModal() {
            document.getElementById('approveReturnModal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('approveReturnModal')?.addEventListener('click', function(e) {
            if (e.target.id === 'approveReturnModal') {
                closeApproveModal();
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
    
    <?php renderAdminSidebar('returns'); ?>
    
    <main class="ml-64 pt-24 pb-6 px-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Returns & Refunds Management</h1>
            <?php if ($order_filter): 
                $orderInfoStmt = $pdo->prepare("SELECT custom_order_id FROM orders WHERE order_id = ?");
                $orderInfoStmt->execute([$order_filter]);
                $orderInfo = $orderInfoStmt->fetch();
            ?>
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-sm text-gray-600">Filtered by Order:</span>
                    <a href="view_order.php?id=<?= $order_filter ?>" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        <?= htmlspecialchars($orderInfo['custom_order_id'] ?? 'Order #'.$order_filter) ?>
                    </a>
                    <a href="returns.php" class="text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times ml-2"></i> Clear Filter
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg border border-green-200"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg border border-red-200"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- Returns Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">All Return Requests</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Return ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Refund</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($returns)): ?>
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                    No return requests found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($returns as $return): 
                                $product_images = json_decode($return['images'] ?? '[]', true);
                                $product_image = !empty($product_images) ? '../' . $product_images[0] : '../img/placeholder-product.png';
                            ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">#<?= $return['return_id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($return['custom_order_id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <img src="<?= htmlspecialchars($product_image) ?>" alt="Product" class="w-10 h-10 rounded object-cover">
                                            <span class="text-sm"><?= htmlspecialchars($return['product_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?= htmlspecialchars($return['username']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?= ucfirst($return['return_type']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full <?php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                'processing' => 'bg-blue-100 text-blue-800',
                                                'completed' => 'bg-gray-100 text-gray-800'
                                            ];
                                            echo $statusColors[$return['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>">
                                            <?= ucfirst($return['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if ($return['refund_amount']): ?>
                                            <?= formatCurrency($return['refund_amount']) ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= date('M d, Y', strtotime($return['created_at'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                        <a href="view_order.php?id=<?= $return['order_id'] ?>" class="inline-block text-blue-600 hover:text-blue-800">View Order</a>
                                        <?php if ($return['status'] === 'pending'): ?>
                                            <button onclick="openApproveModal(<?= htmlspecialchars(json_encode([
                                                'return_id' => $return['return_id'],
                                                'product_name' => $return['product_name'],
                                                'item_price' => $return['item_price'] ?? 0,
                                                'item_quantity' => $return['item_quantity'] ?? 1,
                                                'suggested_refund' => ($return['item_price'] ?? 0) * ($return['item_quantity'] ?? 1)
                                            ])) ?>)" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">Approve</button>
                                            <form method="post" class="inline" onsubmit="return confirm('Are you sure you want to reject this return request?');">
                                                <input type="hidden" name="action" value="reject_return">
                                                <input type="hidden" name="return_id" value="<?= (int)$return['return_id'] ?>">
                                                <input type="hidden" name="admin_notes" value="Rejected by admin">
                                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">Reject</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($return['status'] === 'approved' && $return['refund_status'] === 'pending'): ?>
                                            <form method="post" class="inline">
                                                <input type="hidden" name="action" value="process_refund">
                                                <input type="hidden" name="return_id" value="<?= (int)$return['return_id'] ?>">
                                                <button type="submit" class="px-3 py-1 bg-gray-700 text-white rounded hover:bg-gray-800">Mark Refunded</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <!-- Approve Return Modal -->
    <div id="approveReturnModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-900">Approve Return Request</h3>
                        <button onclick="closeApproveModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <form method="post" id="approveReturnForm">
                        <input type="hidden" name="action" value="approve_return">
                        <input type="hidden" name="return_id" id="modalReturnId">
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-2">Product:</p>
                            <p class="text-base font-medium text-gray-900" id="modalProductName"></p>
                        </div>
                        
                        <div class="mb-4 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Item Price:</p>
                                <p class="text-base font-medium text-gray-900" id="modalItemPrice"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Quantity:</p>
                                <p class="text-base font-medium text-gray-900" id="modalItemQuantity"></p>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Refund Amount (<?= getCurrencySymbol() ?>)
                            </label>
                            <input type="number" step="0.01" min="0" name="refund_amount" id="modalRefundAmount" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   required>
                            <p class="mt-1 text-xs text-gray-500">Enter the amount to refund for this item</p>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Admin Notes (Optional)
                            </label>
                            <textarea name="admin_notes" id="modalAdminNotes" rows="3" 
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Add any notes about this approval..."></textarea>
                        </div>
                        
                        <div class="flex items-center justify-end space-x-3">
                            <button type="button" onclick="closeApproveModal()" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-check mr-2"></i>
                                Approve Return
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
