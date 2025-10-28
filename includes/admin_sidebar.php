<?php
/**
 * Render the admin sidebar navigation with role-based menu visibility
 * @param string $current_page The current page identifier
 */
function renderAdminSidebar($current_page = 'dashboard') {
    $role = $_SESSION['role'] ?? 'customer';
    
    // Define menu items with their access requirements
    $menu_items = [
        [
            'module' => 'dashboard',
            'href' => 'dashboard.php',
            'icon' => 'fa-th-large',
            'text' => 'Dashboard',
            'accessible_to' => ['admin', 'manager', 'staff']
        ],
        [
            'module' => 'products',
            'href' => 'product.php',
            'icon' => 'fa-cube',
            'text' => 'Products',
            'accessible_to' => ['admin']
        ],
        [
            'module' => 'orders',
            'href' => 'orders.php',
            'icon' => 'fa-shopping-cart',
            'text' => 'Orders',
            'accessible_to' => ['admin', 'staff']
        ],
        [
            'module' => 'inventory',
            'href' => 'inventory.php',
            'icon' => 'fa-archive',
            'text' => 'Inventory',
            'accessible_to' => ['admin', 'staff']
        ],
        [
            'module' => 'users',
            'href' => 'users.php',
            'icon' => 'fa-users',
            'text' => 'Users',
            'accessible_to' => ['admin']
        ],
        [
            'module' => 'analytics',
            'href' => 'analytics.php',
            'icon' => 'fa-chart-line',
            'text' => 'Analytics',
            'accessible_to' => ['admin', 'manager']
        ],
        [
            'module' => 'content',
            'href' => 'content.php',
            'icon' => 'fa-file-alt',
            'text' => 'Contents',
            'accessible_to' => ['admin']
        ],
        [
            'module' => 'audit_logs',
            'href' => 'audit_logs.php',
            'icon' => 'fa-history',
            'text' => 'Audit Trail',
            'accessible_to' => ['admin']
        ],
        [
            'module' => 'notifications',
            'href' => '#',
            'icon' => 'fa-bell',
            'text' => 'Notifications',
            'accessible_to' => ['admin', 'manager', 'staff']
        ],
        [
            'module' => 'settings',
            'href' => 'settings.php',
            'icon' => 'fa-cog',
            'text' => 'Settings',
            'accessible_to' => ['admin', 'manager', 'staff']
        ]
    ];
    ?>
    <!-- Sidebar -->
    <aside class="fixed left-0 top-16 bottom-0 w-64 bg-white border-r border-gray-200 overflow-y-auto">
        <nav class="p-4">
            <ul class="space-y-2">
                <?php foreach ($menu_items as $item): ?>
                    <?php if (in_array($role, $item['accessible_to'])): ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($item['href']); ?>" 
                           class="sidebar-item <?php echo ($current_page === $item['module'] ? 'active' : ''); ?> flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                            <i class="fas <?php echo htmlspecialchars($item['icon']); ?> text-gray-600"></i>
                            <span><?php echo htmlspecialchars($item['text']); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </nav>
    </aside>
    <?php
}
?>

