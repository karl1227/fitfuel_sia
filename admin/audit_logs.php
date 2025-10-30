<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';
require_once '../config/audit_logger.php';
require_once '../includes/admin_sidebar.php';

// Check role-based access for audit_logs module
requireAccess('audit_logs');

$pdo = getDBConnection();
$auditLogger = new AuditLogger();

// Log admin access to audit logs
$auditLogger->logAdminAccess('Viewed audit logs', 'Accessed audit trail page');

// Get filter parameters
$filters = [
    'user_id' => $_GET['user_id'] ?? '',
    'action_type' => $_GET['action_type'] ?? '',
    'module' => $_GET['module'] ?? '',
    'severity' => $_GET['severity'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'search' => $_GET['search'] ?? ''
];

// Remove empty filters
$filters = array_filter($filters, function($value) {
    return $value !== '';
});

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

// Get audit logs
$logs = $auditLogger->getLogs($filters, $limit, $offset);
$stats = $auditLogger->getLogStats($filters);

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM audit_logs";
$whereConditions = [];
$countParams = [];

if (!empty($filters)) {
    if (!empty($filters['user_id'])) {
        $whereConditions[] = "user_id = ?";
        $countParams[] = $filters['user_id'];
    }
    if (!empty($filters['action_type'])) {
        $whereConditions[] = "action_type = ?";
        $countParams[] = $filters['action_type'];
    }
    if (!empty($filters['module'])) {
        $whereConditions[] = "module = ?";
        $countParams[] = $filters['module'];
    }
    if (!empty($filters['severity'])) {
        $whereConditions[] = "severity = ?";
        $countParams[] = $filters['severity'];
    }
    if (!empty($filters['date_from'])) {
        $whereConditions[] = "created_at >= ?";
        $countParams[] = $filters['date_from'];
    }
    if (!empty($filters['date_to'])) {
        $whereConditions[] = "created_at <= ?";
        $countParams[] = $filters['date_to'];
    }
    if (!empty($filters['search'])) {
        $whereConditions[] = "(description LIKE ? OR username LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
    }
}

if (!empty($whereConditions)) {
    $countSql .= " WHERE " . implode(' AND ', $whereConditions);
}

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalLogs = $countStmt->fetchColumn();
$totalPages = ceil($totalLogs / $limit);

// Get unique users for filter dropdown
$usersStmt = $pdo->query("SELECT DISTINCT u.user_id, u.username FROM users u INNER JOIN audit_logs al ON u.user_id = al.user_id ORDER BY u.username");
$users = $usersStmt->fetchAll();

// Action types for filter
$actionTypes = [
    'login_success', 'login_failed', 'logout', 'password_change', 'password_reset_request',
    'password_reset_complete', 'profile_update', 'user_create', 'user_update', 'user_delete',
    'user_status_change', 'product_create', 'product_update', 'product_delete', 'product_status_change',
    'inventory_adjustment', 'order_create', 'order_update', 'order_status_change', 'order_cancel',
    'order_refund', 'payment_process', 'payment_refund', 'promo_create', 'promo_update',
    'promo_delete', 'promo_status_change', 'category_create', 'category_update', 'category_delete',
    'subcategory_create', 'subcategory_update', 'subcategory_delete', 'shipping_fee_update',
    'system_settings_change', 'admin_access', 'data_export', 'data_import', 'content_create',
    'content_update', 'content_delete', 'content_archive', 'content_publish', 'other'
];

$modules = ['authentication', 'users', 'products', 'inventory', 'orders', 'payments', 'promo_codes', 'categories', 'system', 'admin', 'data_management'];
$severities = ['low', 'medium', 'high', 'critical'];

function getSeverityBadgeClass($severity) {
    switch($severity) {
        case 'critical': return 'bg-red-100 text-red-800 border-red-200';
        case 'high': return 'bg-orange-100 text-orange-800 border-orange-200';
        case 'medium': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'low': return 'bg-green-100 text-green-800 border-green-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
}

function getActionTypeIcon($actionType) {
    if (strpos($actionType, 'login') !== false) return 'fas fa-sign-in-alt';
    if (strpos($actionType, 'logout') !== false) return 'fas fa-sign-out-alt';
    if (strpos($actionType, 'password') !== false) return 'fas fa-key';
    if (strpos($actionType, 'user') !== false) return 'fas fa-user';
    if (strpos($actionType, 'product') !== false) return 'fas fa-cube';
    if (strpos($actionType, 'inventory') !== false) return 'fas fa-archive';
    if (strpos($actionType, 'order') !== false) return 'fas fa-shopping-cart';
    if (strpos($actionType, 'payment') !== false) return 'fas fa-credit-card';
    if (strpos($actionType, 'promo') !== false) return 'fas fa-tag';
    if (strpos($actionType, 'category') !== false) return 'fas fa-folder';
    if (strpos($actionType, 'admin') !== false) return 'fas fa-user-shield';
    if (strpos($actionType, 'system') !== false) return 'fas fa-cog';
    if (strpos($actionType, 'data') !== false) return 'fas fa-database';
    return 'fas fa-circle';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - FitFuel Admin</title>
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
        .log-row:hover {
            background-color: #f9fafb;
        }
        .filter-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .stats-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
    <script>
        function toggleUserMenu(){
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }
        
        function toggleFilters() {
            const filters = document.getElementById('filtersPanel');
            const button = document.getElementById('toggleFiltersBtn');
            const icon = button.querySelector('i');
            
            filters.classList.toggle('hidden');
            if (filters.classList.contains('hidden')) {
                icon.className = 'fas fa-chevron-down';
                button.innerHTML = '<i class="fas fa-chevron-down mr-2"></i>Show Filters';
            } else {
                icon.className = 'fas fa-chevron-up';
                button.innerHTML = '<i class="fas fa-chevron-up mr-2"></i>Hide Filters';
            }
        }
        
        function clearFilters() {
            const form = document.getElementById('filtersForm');
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else {
                    input.value = '';
                }
            });
        }
        
        function viewLogDetails(auditId) {
            // Open modal with log details
            fetch(`get_audit_log_details.php?id=${auditId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('logDetailsContent').innerHTML = data.html;
                    document.getElementById('logDetailsModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
        
        function closeLogDetails() {
            document.getElementById('logDetailsModal').classList.add('hidden');
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
</head>
<body class="font-body bg-gray-50">
    <?php require_once '../includes/admin_header.php'; ?>

    <?php renderAdminSidebar('audit_logs'); ?>

    <!-- Main Content -->
    <main class="ml-64 pt-16 pb-6 px-6 pt-24">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Audit Trail</h1>
            <p class="text-gray-600">Track All Admin Activities and User Actions.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stats-card bg-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Logs</h3>
                        <div class="flex items-center mt-2">
                            <span class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total_logs']); ?></span>
                            <i class="fas fa-list text-gray-400 ml-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Critical Issues</h3>
                        <div class="flex items-center mt-2">
                            <span class="text-2xl font-bold text-red-600"><?php echo number_format($stats['critical_logs']); ?></span>
                            <i class="fas fa-exclamation-triangle text-red-400 ml-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Failed Actions</h3>
                        <div class="flex items-center mt-2">
                            <span class="text-2xl font-bold text-orange-600"><?php echo number_format($stats['failed_logs']); ?></span>
                            <i class="fas fa-times-circle text-orange-400 ml-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Login Events</h3>
                        <div class="flex items-center mt-2">
                            <span class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['login_logs']); ?></span>
                            <i class="fas fa-sign-in-alt text-blue-400 ml-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg border border-gray-200 mb-6">
            <div class="p-4 border-b border-gray-200">
                <button id="toggleFiltersBtn" onclick="toggleFilters()" class="flex items-center text-gray-700 hover:text-gray-900 font-medium">
                    <i class="fas fa-chevron-down mr-2"></i>Show Filters
                </button>
            </div>
            <div id="filtersPanel" class="hidden p-6">
                <form id="filtersForm" method="GET" action="audit_logs.php">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                            <select name="user_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Users</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['user_id']; ?>" <?php echo ($filters['user_id'] ?? '') == $user['user_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Action Type</label>
                            <select name="action_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Actions</option>
                                <?php foreach ($actionTypes as $actionType): ?>
                                    <option value="<?php echo $actionType; ?>" <?php echo ($filters['action_type'] ?? '') == $actionType ? 'selected' : ''; ?>>
                                        <?php echo ucwords(str_replace('_', ' ', $actionType)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Module</label>
                            <select name="module" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Modules</option>
                                <?php foreach ($modules as $module): ?>
                                    <option value="<?php echo $module; ?>" <?php echo ($filters['module'] ?? '') == $module ? 'selected' : ''; ?>>
                                        <?php echo ucwords(str_replace('_', ' ', $module)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Severity</label>
                            <select name="severity" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Severities</option>
                                <?php foreach ($severities as $severity): ?>
                                    <option value="<?php echo $severity; ?>" <?php echo ($filters['severity'] ?? '') == $severity ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($severity); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                            <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                            <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" placeholder="Search descriptions..." class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4 mt-6">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>Apply Filters
                        </button>
                        <button type="button" onclick="clearFilters()" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                            <i class="fas fa-times mr-2"></i>Clear
                        </button>
                        <a href="audit_logs.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Audit Logs Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Audit Logs</h3>
                <div class="text-sm text-gray-500">
                    Showing <?php echo count($logs); ?> of <?php echo number_format($totalLogs); ?> logs
                </div>
            </div>
            
            <?php if (empty($logs)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No audit logs found</p>
                    <p class="text-gray-400 text-sm">Try adjusting your filters or check back later</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Module</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($logs as $log): ?>
                                <tr class="log-row">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('M j, Y H:i', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                                <i class="fas fa-user text-gray-500 text-xs"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($log['username'] ?: 'System'); ?>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    ID: <?php echo $log['user_id'] ?: 'N/A'; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <i class="<?php echo getActionTypeIcon($log['action_type']); ?> text-gray-400 mr-2"></i>
                                            <span class="text-sm text-gray-900">
                                                <?php echo ucwords(str_replace('_', ' ', $log['action_type'])); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo ucwords(str_replace('_', ' ', $log['module'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                        <?php echo htmlspecialchars($log['description']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?php echo getSeverityBadgeClass($log['severity']); ?>">
                                            <?php echo ucfirst($log['severity']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="viewLogDetails(<?php echo $log['audit_id']; ?>)" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="px-6 py-3 border-t border-gray-200 flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                        </div>
                        <div class="flex items-center space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="px-3 py-2 text-sm <?php echo $i == $page ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'; ?> rounded">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Log Details Modal -->
    <div id="logDetailsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Audit Log Details</h3>
                <button onclick="closeLogDetails()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="logDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Add click handlers for sidebar items
        document.querySelectorAll('.sidebar-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (this.getAttribute('href') === '#' || !this.getAttribute('href')) {
                    e.preventDefault();
                }
                document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>
