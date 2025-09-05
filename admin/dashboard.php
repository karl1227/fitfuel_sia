<?php
require_once '../admin_auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FitFuel</title>
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
        .metric-card {
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        .metric-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .chart-placeholder {
            background: linear-gradient(45deg, #f3f4f6 25%, transparent 25%), 
                        linear-gradient(-45deg, #f3f4f6 25%, transparent 25%), 
                        linear-gradient(45deg, transparent 75%, #f3f4f6 75%), 
                        linear-gradient(-45deg, transparent 75%, #f3f4f6 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
        }
    </style>
    <script>
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
                    <a href="dashboard.php" class="sidebar-item active flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
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
                    <p class="text-sm text-gray-300">Admin@Fitfuel.com</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 pt-16 pb-6 px-6 pt-24">
        <!-- Dashboard Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard</h1>
            <p class="text-gray-600">Welcome Back! Here's What's Happening With Your Store.</p>
        </div>

        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="metric-card bg-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Revenue</h3>
                        <div class="flex items-center mt-2">
                            <span class="text-2xl font-bold text-gray-900">₱0</span>
                            <i class="fas fa-chart-line text-gray-400 ml-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="metric-card bg-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Orders</h3>
                        <div class="flex items-center mt-2">
                            <span class="text-2xl font-bold text-gray-900">0</span>
                            <i class="fas fa-shopping-cart text-gray-400 ml-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="metric-card bg-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Products</h3>
                        <div class="flex items-center mt-2">
                            <span class="text-2xl font-bold text-gray-900">0</span>
                            <i class="fas fa-cube text-gray-400 ml-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="metric-card bg-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Active Users</h3>
                        <div class="flex items-center mt-2">
                            <span class="text-2xl font-bold text-gray-900">0</span>
                            <i class="fas fa-users text-gray-400 ml-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Information Panels -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Orders</h3>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-shopping-cart text-4xl mb-4"></i>
                    <p>No recent orders</p>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Low Stock Alerts</h3>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
                    <p>No low stock items</p>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Selling Products</h3>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-trophy text-4xl mb-4"></i>
                    <p>No sales data available</p>
                </div>
            </div>
        </div>

        <!-- Sales Overview Chart -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">SALES OVERVIEW</h3>
            <div class="chart-placeholder h-64 rounded-lg flex items-center justify-center">
                <div class="text-center text-gray-500">
                    <i class="fas fa-chart-area text-6xl mb-4"></i>
                    <p class="text-lg">Sales chart will be displayed here</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Add click handlers for sidebar items
        document.querySelectorAll('.sidebar-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Only prevent default for items without href or with href="#"
                if (this.getAttribute('href') === '#' || !this.getAttribute('href')) {
                    e.preventDefault();
                }
                
                // Remove active class from all items
                document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
                
                // Add active class to clicked item
                this.classList.add('active');
            });
        });

        // Add logout functionality to user icon
        document.querySelector('header button:last-child').addEventListener('click', function() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../logout.php';
            }
        });
    </script>
</body>
</html>