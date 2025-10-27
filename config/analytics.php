<?php
require_once 'database.php';

class Analytics {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    public function getDashboardData($filters = []) {
        $start_date = $filters['start_date'] ?? date('Y-m-01');
        $end_date = $filters['end_date'] ?? date('Y-m-t 23:59:59');
        $group_by = $filters['group_by'] ?? 'day';
        
        // KPIs
        $kpis = $this->getKPIs($start_date, $end_date);
        
        // Revenue trend
        $revenue_trend = $this->getRevenueTrend($start_date, $end_date, $group_by);
        
        // Order status distribution
        $order_status = $this->getOrderStatusDistribution($start_date, $end_date);
        
        // Top products
        $top_products = $this->getTopProducts($start_date, $end_date);
        
        // Customer insights
        $customer_insights = $this->getCustomerInsights($start_date, $end_date);
        
        return [
            'kpis' => $kpis,
            'revenue_trend' => $revenue_trend,
            'order_status' => $order_status,
            'top_products' => $top_products,
            'customer_insights' => $customer_insights
        ];
    }
    
    private function getKPIs($start_date, $end_date) {
        // Revenue (30 days)
        $revenue_30_days = $this->pdo->query("
            SELECT COALESCE(SUM(total_amount), 0) 
            FROM orders 
            WHERE payment_status = 'paid' 
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ")->fetchColumn();
        
        // Orders (30 days)
        $orders_30_days = $this->pdo->query("
            SELECT COUNT(*) 
            FROM orders 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ")->fetchColumn();
        
        // Conversion rate (simplified - orders/users)
        $total_users = $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $conversion_rate = $total_users > 0 ? round(($orders_30_days / $total_users) * 100, 2) : 0;
        
        // Active customers (users with orders in last 30 days)
        $active_customers = $this->pdo->query("
            SELECT COUNT(DISTINCT user_id) 
            FROM orders 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ")->fetchColumn();
        
        return [
            'revenue_30_days' => (float)$revenue_30_days,
            'orders_30_days' => (int)$orders_30_days,
            'conversion_rate' => $conversion_rate,
            'active_customers' => (int)$active_customers
        ];
    }
    
    private function getRevenueTrend($start_date, $end_date, $group_by) {
        $date_format = $this->getDateFormat($group_by);
        
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE_FORMAT(created_at, ?) as period,
                COALESCE(SUM(total_amount), 0) as revenue,
                COUNT(*) as orders,
                COUNT(DISTINCT user_id) as unique_customers
            FROM orders 
            WHERE created_at BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(created_at, ?)
            ORDER BY created_at
        ");
        
        $stmt->execute([$date_format, $start_date, $end_date, $date_format]);
        return $stmt->fetchAll();
    }
    
    private function getOrderStatusDistribution($start_date, $end_date) {
        $stmt = $this->pdo->prepare("
            SELECT 
                payment_status,
                COUNT(*) as count,
                COALESCE(SUM(total_amount), 0) as total_amount
            FROM orders 
            WHERE created_at BETWEEN ? AND ?
            GROUP BY payment_status
        ");
        
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll();
    }
    
    private function getTopProducts($start_date, $end_date, $limit = 5) {
        $stmt = $this->pdo->prepare("
            SELECT 
                p.name,
                SUM(oi.quantity) as units_sold,
                SUM(oi.quantity * oi.price) as revenue,
                COUNT(DISTINCT o.order_id) as orders_count
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            JOIN products p ON oi.product_id = p.product_id
            WHERE o.created_at BETWEEN ? AND ?
            GROUP BY p.product_id, p.name
            ORDER BY units_sold DESC
            LIMIT ?
        ");
        
        $stmt->execute([$start_date, $end_date, $limit]);
        return $stmt->fetchAll();
    }
    
    private function getCustomerInsights($start_date, $end_date) {
        // New vs returning customers
        $new_customers = $this->pdo->prepare("
            SELECT COUNT(DISTINCT o.user_id)
            FROM orders o
            WHERE o.created_at BETWEEN ? AND ?
            AND o.user_id NOT IN (
                SELECT DISTINCT user_id 
                FROM orders 
                WHERE created_at < ?
            )
        ");
        $new_customers->execute([$start_date, $end_date, $start_date]);
        
        $returning_customers = $this->pdo->prepare("
            SELECT COUNT(DISTINCT o.user_id)
            FROM orders o
            WHERE o.created_at BETWEEN ? AND ?
            AND o.user_id IN (
                SELECT DISTINCT user_id 
                FROM orders 
                WHERE created_at < ?
            )
        ");
        $returning_customers->execute([$start_date, $end_date, $start_date]);
        
        // Average order value
        $avg_order_value = $this->pdo->prepare("
            SELECT COALESCE(AVG(total_amount), 0)
            FROM orders
            WHERE created_at BETWEEN ? AND ?
        ");
        $avg_order_value->execute([$start_date, $end_date]);
        
        // Top spenders
        $top_spenders = $this->pdo->prepare("
            SELECT 
                u.username,
                SUM(o.total_amount) as total_spent,
                COUNT(o.order_id) as orders_count
            FROM orders o
            JOIN users u ON o.user_id = u.user_id
            WHERE o.created_at BETWEEN ? AND ?
            GROUP BY o.user_id, u.username
            ORDER BY total_spent DESC
            LIMIT 10
        ");
        $top_spenders->execute([$start_date, $end_date]);
        
        return [
            'insights' => [
                'new_customers' => (int)$new_customers->fetchColumn(),
                'returning_customers' => (int)$returning_customers->fetchColumn(),
                'avg_order_value' => (float)$avg_order_value->fetchColumn()
            ],
            'top_spenders' => $top_spenders->fetchAll()
        ];
    }
    
    private function getDateFormat($group_by) {
        switch ($group_by) {
            case 'day': return '%Y-%m-%d';
            case 'week': return '%Y-%u';
            case 'month': return '%Y-%m';
            case 'year': return '%Y';
            default: return '%Y-%m-%d';
        }
    }
    
    public function exportToCSV($data, $filename, $headers) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    public function exportToExcel($data, $filename, $headers) {
        // Simple Excel export using CSV format with .xls extension
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo '<table border="1">';
        echo '<tr>';
        foreach ($headers as $header) {
            echo '<th>' . htmlspecialchars($header) . '</th>';
        }
        echo '</tr>';
        
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . htmlspecialchars($cell) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }
    
    public function generatePDF($html, $filename) {
        // Simple PDF generation - in production, use a proper PDF library like TCPDF or mPDF
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $html;
        exit;
    }
}
?>
