<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';
require_once '../config/analytics.php';

$analytics = new Analytics();

// Handle export requests
if (isset($_GET['export'])) {
    $filters = [
        'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
        'end_date' => $_GET['end_date'] ?? date('Y-m-t 23:59:59'),
        'group_by' => $_GET['group_by'] ?? 'day'
    ];
    
    $data = $analytics->getDashboardData($filters);
    
    switch ($_GET['export']) {
        case 'csv':
            $exportData = [];
            foreach ($data['revenue_trend'] as $row) {
                $exportData[] = [
                    'Date' => $row['period'],
                    'Revenue' => $row['revenue'],
                    'Orders' => $row['orders'],
                    'Customers' => $row['unique_customers']
                ];
            }
            $analytics->exportToCSV($exportData, 'analytics_report_' . date('Y-m-d') . '.csv', 
                ['Date', 'Revenue', 'Orders', 'Customers']);
            break;
            
        case 'excel':
            $exportData = [];
            foreach ($data['revenue_trend'] as $row) {
                $exportData[] = [
                    'Date' => $row['period'],
                    'Revenue' => $row['revenue'],
                    'Orders' => $row['orders'],
                    'Customers' => $row['unique_customers']
                ];
            }
            $analytics->exportToExcel($exportData, 'analytics_report_' . date('Y-m-d') . '.xls', 
                ['Date', 'Revenue', 'Orders', 'Customers']);
            break;
            
        case 'pdf':
            $html = '<h1>FitFuel Analytics Report</h1>';
            $html .= '<h2>KPIs</h2>';
            $html .= '<p>Revenue (30 Days): ₱' . number_format($data['kpis']['revenue_30_days'], 2) . '</p>';
            $html .= '<p>Orders (30 Days): ' . number_format($data['kpis']['orders_30_days']) . '</p>';
            $html .= '<p>Conversion Rate: ' . $data['kpis']['conversion_rate'] . '%</p>';
            $html .= '<p>Active Customers: ' . number_format($data['kpis']['active_customers']) . '</p>';
            $analytics->generatePDF($html, 'analytics_report_' . date('Y-m-d') . '.pdf');
            break;
    }
}

// Get filters from request
$filters = [
    'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
    'end_date' => $_GET['end_date'] ?? date('Y-m-t 23:59:59'),
    'group_by' => $_GET['group_by'] ?? 'day'
];

// Get dashboard data
$dashboardData = $analytics->getDashboardData($filters);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales & Analytics - FitFuel Admin</title>
    <link rel="icon" href="../img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .sidebar-item.active { background-color: #f3f4f6; border-right: 3px solid #000; }
        .sidebar-item:hover { background-color: #f9fafb; }
        .metric-card { border: 1px solid #e5e7eb; transition: all 0.3s ease; }
        .metric-card:hover { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .chart-container { position: relative; height: 300px; }
    </style>
</head>
<body class="font-body bg-gray-50">
    <!-- Header -->
    <header class="bg-black text-white fixed top-0 left-0 right-0 z-50 h-16 flex items-center justify-between px-6">
        <div class="flex items-center space-x-3">
            <img src="../img/LOGO-Fitfuel.png" alt="FitFuel Logo" class="w-8 h-8 object-contain">
            <div class="w-px h-6 bg-white"></div>
            <h1 class="text-xl font-bold uppercase">Analytics</h1>
        </div>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="text-white hover:text-gray-300">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
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
                    <a href="analytics.php" class="sidebar-item active flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-chart-line text-gray-600"></i>
                        <span>Sales & Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="orders.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-shopping-cart text-gray-600"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li>
                    <a href="product.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-cube text-gray-600"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li>
                    <a href="users.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-users text-gray-600"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="audit_log.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-history text-gray-600"></i>
                        <span>Audit Trail</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 pt-24 pb-6 px-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Sales & Analytics</h1>
                    <p class="text-gray-600">Comprehensive business performance monitoring and reporting</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="printReport()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-print mr-2"></i>Print
                    </button>
                    <div class="relative">
                        <button onclick="toggleExportMenu()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <i class="fas fa-download mr-2"></i>Export
                        </button>
                        <div id="exportMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                            <a href="?export=csv&start_date=<?php echo $filters['start_date']; ?>&end_date=<?php echo $filters['end_date']; ?>&group_by=<?php echo $filters['group_by']; ?>" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-file-csv mr-3 text-gray-400"></i>Export CSV
                            </a>
                            <a href="?export=excel&start_date=<?php echo $filters['start_date']; ?>&end_date=<?php echo $filters['end_date']; ?>&group_by=<?php echo $filters['group_by']; ?>" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-file-excel mr-3 text-gray-400"></i>Export Excel
                            </a>
                            <a href="?export=pdf&start_date=<?php echo $filters['start_date']; ?>&end_date=<?php echo $filters['end_date']; ?>&group_by=<?php echo $filters['group_by']; ?>" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-file-pdf mr-3 text-gray-400"></i>Export PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" name="start_date" value="<?php echo $filters['start_date']; ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" name="end_date" value="<?php echo $filters['end_date']; ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Group By</label>
                    <select name="group_by" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="day" <?php echo $filters['group_by'] === 'day' ? 'selected' : ''; ?>>Daily</option>
                        <option value="week" <?php echo $filters['group_by'] === 'week' ? 'selected' : ''; ?>>Weekly</option>
                        <option value="month" <?php echo $filters['group_by'] === 'month' ? 'selected' : ''; ?>>Monthly</option>
                        <option value="year" <?php echo $filters['group_by'] === 'year' ? 'selected' : ''; ?>>Yearly</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- KPI Cards (Top Row) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="metric-card bg-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Revenue (30 Days)</h3>
                        <div class="flex items-center mt-2">
                            <span class="text-2xl font-bold text-gray-900">₱<?php echo number_format($dashboardData['kpis']['revenue_30_days'], 2); ?></span>
                            <i class="fas fa-dollar-sign text-green-600 ml-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="metric-card bg-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Orders (30 Days)</h3>
                        <div class="flex items-center mt-2">
                            <span class="text-2xl font-bold text-gray-900"><?php echo number_format($dashboardData['kpis']['orders_30_days']); ?></span>
                            <i class="fas fa-shopping-cart text-blue-600 ml-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="metric-card bg-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Conversion Rate</h3>
                        <div class="flex items-center mt-2">
                            <span class="text-2xl font-bold text-gray-900"><?php echo $dashboardData['kpis']['conversion_rate']; ?>%</span>
                            <i class="fas fa-percentage text-purple-600 ml-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="metric-card bg-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Active Customers</h3>
                        <div class="flex items-center mt-2">
                            <span class="text-2xl font-bold text-gray-900"><?php echo number_format($dashboardData['kpis']['active_customers']); ?></span>
                            <i class="fas fa-users text-orange-600 ml-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts & Reports Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Revenue Trend Chart -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trend</h3>
                <div class="chart-container">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>

            <!-- Order Status Distribution -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Status Distribution</h3>
                <div class="chart-container">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Selling Products & Customer Insights -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Top Selling Products -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Selling Products</h3>
                <div class="space-y-4">
                    <?php if (empty($dashboardData['top_products'])): ?>
                        <p class="text-gray-500 text-center py-4">No product data available</p>
                    <?php else: ?>
                        <?php foreach ($dashboardData['top_products'] as $index => $product): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <span class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                                    <?php echo $index + 1; ?>
                                </span>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo number_format($product['units_sold']); ?> units sold</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-gray-900">₱<?php echo number_format($product['revenue'], 2); ?></div>
                                <div class="text-sm text-gray-500"><?php echo $product['orders_count']; ?> orders</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Customer Insights -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Insights</h3>
                <div class="space-y-6">
                    <!-- Customer Types -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600"><?php echo number_format($dashboardData['customer_insights']['insights']['new_customers']); ?></div>
                            <div class="text-sm text-gray-600">New Customers</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600"><?php echo number_format($dashboardData['customer_insights']['insights']['returning_customers']); ?></div>
                            <div class="text-sm text-gray-600">Returning Customers</div>
                        </div>
                    </div>
                    
                    <!-- Average Order Value -->
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">₱<?php echo number_format($dashboardData['customer_insights']['insights']['avg_order_value'], 2); ?></div>
                        <div class="text-sm text-gray-600">Average Order Value</div>
                    </div>
                    
                    <!-- Top Spenders -->
                    <?php if (!empty($dashboardData['customer_insights']['top_spenders'])): ?>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Top Spenders</h4>
                        <div class="space-y-2">
                            <?php foreach (array_slice($dashboardData['customer_insights']['top_spenders'], 0, 5) as $spender): ?>
                            <div class="flex items-center justify-between text-sm">
                                <div>
                                    <div class="font-medium"><?php echo htmlspecialchars($spender['username']); ?></div>
                                    <div class="text-gray-500"><?php echo $spender['orders_count']; ?> orders</div>
                                </div>
                                <div class="font-semibold">₱<?php echo number_format($spender['total_spent'], 2); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Detailed Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue Trend Table -->
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Revenue Trend Details</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customers</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($dashboardData['revenue_trend'])): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">No data available</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($dashboardData['revenue_trend'], -10) as $trend): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($trend['period']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ₱<?php echo number_format($trend['revenue'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo number_format($trend['orders']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo number_format($trend['unique_customers']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order Status Details -->
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Order Status Details</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($dashboardData['order_status'])): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">No data available</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $totalOrders = array_sum(array_column($dashboardData['order_status'], 'count'));
                                foreach ($dashboardData['order_status'] as $status): 
                                    $percentage = $totalOrders > 0 ? ($status['count'] / $totalOrders) * 100 : 0;
                                ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php 
                                            echo $status['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' :
                                                ($status['payment_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                ($status['payment_status'] === 'failed' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'));
                                        ?>">
                                            <?php echo ucfirst($status['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo number_format($status['count']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ₱<?php echo number_format($status['total_amount'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo number_format($percentage, 1); ?>%
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Revenue Trend Chart
        const revenueData = <?php echo json_encode($dashboardData['revenue_trend']); ?>;
        const revenueLabels = revenueData.map(item => item.period);
        const revenueValues = revenueData.map(item => parseFloat(item.revenue) || 0);
        const ordersValues = revenueData.map(item => parseInt(item.orders) || 0);

        const revenueCtx = document.getElementById('revenueTrendChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: revenueValues,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: 'Orders',
                    data: ordersValues,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });

        // Order Status Chart
        const statusData = <?php echo json_encode($dashboardData['order_status']); ?>;
        const statusLabels = statusData.map(item => item.payment_status);
        const statusCounts = statusData.map(item => parseInt(item.count) || 0);

        const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusCounts,
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',   // Paid - Green
                        'rgba(251, 191, 36, 0.8)',  // Pending - Yellow
                        'rgba(239, 68, 68, 0.8)',   // Failed - Red
                        'rgba(107, 114, 128, 0.8)'  // Other - Gray
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Export menu toggle
        function toggleExportMenu() {
            const menu = document.getElementById('exportMenu');
            menu.classList.toggle('hidden');
        }

        // Close export menu when clicking outside
        document.addEventListener('click', function(event) {
            const exportButton = event.target.closest('button[onclick="toggleExportMenu()"]');
            const exportMenu = document.getElementById('exportMenu');
            if (!exportButton && exportMenu && !exportMenu.contains(event.target)) {
                exportMenu.classList.add('hidden');
            }
        });

        // Print report
        function printReport() {
            window.print();
        }
    </script>
</body>
</html>
