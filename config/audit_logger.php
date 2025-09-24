<?php
/**
 * AuditLogger Class
 * Handles logging of all important user and admin actions for security, accountability, and troubleshooting
 */

require_once __DIR__ . '/database.php';

class AuditLogger {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    /**
     * Log an action to the audit trail
     * 
     * @param string $actionType The type of action performed
     * @param string $module The module/feature where action occurred
     * @param string $description Detailed description of the action
     * @param array $oldValues Old values before change (optional)
     * @param array $newValues New values after change (optional)
     * @param int $referenceId ID of the record being modified (optional)
     * @param string $referenceType Type of reference (optional)
     * @param string $severity Severity level (low, medium, high, critical)
     * @param string $status Status of the action (success, failed, warning)
     * @param int $userId User ID who performed the action (optional, defaults to session user)
     * @return bool Success status
     */
    public function log($actionType, $module, $description, $oldValues = null, $newValues = null, $referenceId = null, $referenceType = null, $severity = 'low', $status = 'success', $userId = null) {
        try {
            // Get user info from session if not provided
            if ($userId === null) {
                $userId = $_SESSION['user_id'] ?? null;
            }
            
            $username = $_SESSION['username'] ?? null;
            $ipAddress = $this->getClientIP();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            // Convert arrays to JSON
            $oldValuesJson = $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null;
            $newValuesJson = $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null;
            
            $stmt = $this->pdo->prepare("
                INSERT INTO audit_logs (
                    user_id, username, action_type, module, description, 
                    old_values, new_values, ip_address, user_agent, 
                    reference_id, reference_type, severity, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $userId,
                $username,
                $actionType,
                $module,
                $description,
                $oldValuesJson,
                $newValuesJson,
                $ipAddress,
                $userAgent,
                $referenceId,
                $referenceType,
                $severity,
                $status
            ]);
            
        } catch (Exception $e) {
            // Log error but don't break the application
            error_log("AuditLogger Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log user authentication events
     */
    public function logLogin($username, $success = true, $userId = null) {
        $actionType = $success ? 'login_success' : 'login_failed';
        $status = $success ? 'success' : 'failed';
        $severity = $success ? 'low' : 'medium';
        
        return $this->log(
            $actionType,
            'authentication',
            $success ? "User logged in successfully" : "Failed login attempt",
            null,
            ['username' => $username],
            $userId,
            'user',
            $severity,
            $status,
            $userId
        );
    }
    
    public function logLogout($userId = null) {
        return $this->log(
            'logout',
            'authentication',
            "User logged out",
            null,
            null,
            $userId,
            'user',
            'low',
            'success',
            $userId
        );
    }
    
    public function logPasswordChange($userId, $success = true) {
        $actionType = 'password_change';
        $status = $success ? 'success' : 'failed';
        $severity = $success ? 'medium' : 'high';
        
        return $this->log(
            $actionType,
            'authentication',
            $success ? "Password changed successfully" : "Password change failed",
            null,
            null,
            $userId,
            'user',
            $severity,
            $status,
            $userId
        );
    }
    
    public function logPasswordReset($email, $success = true) {
        $actionType = $success ? 'password_reset_complete' : 'password_reset_request';
        $status = $success ? 'success' : 'success'; // Request is still a success
        $severity = 'medium';
        
        return $this->log(
            $actionType,
            'authentication',
            $success ? "Password reset completed" : "Password reset requested",
            null,
            ['email' => $email],
            null,
            'user',
            $severity,
            $status
        );
    }
    
    /**
     * Log user management actions
     */
    public function logUserCreate($userId, $userData) {
        return $this->log(
            'user_create',
            'users',
            "New user created",
            null,
            $userData,
            $userId,
            'user',
            'medium',
            'success'
        );
    }
    
    public function logUserUpdate($userId, $oldData, $newData) {
        return $this->log(
            'user_update',
            'users',
            "User information updated",
            $oldData,
            $newData,
            $userId,
            'user',
            'medium',
            'success'
        );
    }
    
    public function logUserDelete($userId, $userData) {
        return $this->log(
            'user_delete',
            'users',
            "User account deleted",
            $userData,
            null,
            $userId,
            'user',
            'high',
            'success'
        );
    }
    
    public function logUserStatusChange($userId, $oldStatus, $newStatus) {
        return $this->log(
            'user_status_change',
            'users',
            "User status changed from {$oldStatus} to {$newStatus}",
            ['status' => $oldStatus],
            ['status' => $newStatus],
            $userId,
            'user',
            'high',
            'success'
        );
    }
    
    /**
     * Log product management actions
     */
    public function logProductCreate($productId, $productData) {
        return $this->log(
            'product_create',
            'products',
            "New product created",
            null,
            $productData,
            $productId,
            'product',
            'medium',
            'success'
        );
    }
    
    public function logProductUpdate($productId, $oldData, $newData) {
        return $this->log(
            'product_update',
            'products',
            "Product information updated",
            $oldData,
            $newData,
            $productId,
            'product',
            'medium',
            'success'
        );
    }
    
    public function logProductDelete($productId, $productData) {
        return $this->log(
            'product_delete',
            'products',
            "Product deleted",
            $productData,
            null,
            $productId,
            'product',
            'high',
            'success'
        );
    }
    
    public function logProductStatusChange($productId, $oldStatus, $newStatus) {
        return $this->log(
            'product_status_change',
            'products',
            "Product status changed from {$oldStatus} to {$newStatus}",
            ['status' => $oldStatus],
            ['status' => $newStatus],
            $productId,
            'product',
            'medium',
            'success'
        );
    }
    
    /**
     * Log inventory actions
     */
    public function logInventoryAdjustment($productId, $oldQuantity, $newQuantity, $reason = '') {
        return $this->log(
            'inventory_adjustment',
            'inventory',
            "Inventory adjusted: {$oldQuantity} → {$newQuantity}" . ($reason ? " ({$reason})" : ""),
            ['stock_quantity' => $oldQuantity],
            ['stock_quantity' => $newQuantity],
            $productId,
            'product',
            'medium',
            'success'
        );
    }
    
    /**
     * Log order management actions
     */
    public function logOrderCreate($orderId, $orderData) {
        return $this->log(
            'order_create',
            'orders',
            "New order created",
            null,
            $orderData,
            $orderId,
            'order',
            'medium',
            'success'
        );
    }
    
    public function logOrderUpdate($orderId, $oldData, $newData) {
        return $this->log(
            'order_update',
            'orders',
            "Order information updated",
            $oldData,
            $newData,
            $orderId,
            'order',
            'medium',
            'success'
        );
    }
    
    public function logOrderStatusChange($orderId, $oldStatus, $newStatus) {
        return $this->log(
            'order_status_change',
            'orders',
            "Order status changed from {$oldStatus} to {$newStatus}",
            ['status' => $oldStatus],
            ['status' => $newStatus],
            $orderId,
            'order',
            'medium',
            'success'
        );
    }
    
    public function logOrderCancel($orderId, $reason = '') {
        return $this->log(
            'order_cancel',
            'orders',
            "Order cancelled" . ($reason ? " - {$reason}" : ""),
            null,
            ['cancelled' => true, 'reason' => $reason],
            $orderId,
            'order',
            'high',
            'success'
        );
    }
    
    public function logOrderRefund($orderId, $refundAmount, $reason = '') {
        return $this->log(
            'order_refund',
            'orders',
            "Order refunded: ₱" . number_format($refundAmount, 2) . ($reason ? " - {$reason}" : ""),
            null,
            ['refund_amount' => $refundAmount, 'reason' => $reason],
            $orderId,
            'order',
            'high',
            'success'
        );
    }
    
    /**
     * Log payment actions
     */
    public function logPaymentProcess($orderId, $amount, $method, $success = true) {
        $status = $success ? 'success' : 'failed';
        $severity = $success ? 'medium' : 'high';
        
        return $this->log(
            'payment_process',
            'payments',
            $success ? "Payment processed successfully" : "Payment processing failed",
            null,
            ['amount' => $amount, 'method' => $method],
            $orderId,
            'order',
            $severity,
            $status
        );
    }
    
    public function logPaymentRefund($orderId, $refundAmount, $reason = '') {
        return $this->log(
            'payment_refund',
            'payments',
            "Payment refunded: ₱" . number_format($refundAmount, 2) . ($reason ? " - {$reason}" : ""),
            null,
            ['refund_amount' => $refundAmount, 'reason' => $reason],
            $orderId,
            'order',
            'high',
            'success'
        );
    }
    
    /**
     * Log promo code actions
     */
    public function logPromoCreate($promoId, $promoData) {
        return $this->log(
            'promo_create',
            'promo_codes',
            "New promo code created",
            null,
            $promoData,
            $promoId,
            'promo',
            'medium',
            'success'
        );
    }
    
    public function logPromoUpdate($promoId, $oldData, $newData) {
        return $this->log(
            'promo_update',
            'promo_codes',
            "Promo code updated",
            $oldData,
            $newData,
            $promoId,
            'promo',
            'medium',
            'success'
        );
    }
    
    public function logPromoDelete($promoId, $promoData) {
        return $this->log(
            'promo_delete',
            'promo_codes',
            "Promo code deleted",
            $promoData,
            null,
            $promoId,
            'promo',
            'high',
            'success'
        );
    }
    
    /**
     * Log category management actions
     */
    public function logCategoryCreate($categoryId, $categoryData) {
        return $this->log(
            'category_create',
            'categories',
            "New category created",
            null,
            $categoryData,
            $categoryId,
            'category',
            'medium',
            'success'
        );
    }
    
    public function logCategoryUpdate($categoryId, $oldData, $newData) {
        return $this->log(
            'category_update',
            'categories',
            "Category updated",
            $oldData,
            $newData,
            $categoryId,
            'category',
            'medium',
            'success'
        );
    }
    
    public function logCategoryDelete($categoryId, $categoryData) {
        return $this->log(
            'category_delete',
            'categories',
            "Category deleted",
            $categoryData,
            null,
            $categoryId,
            'category',
            'high',
            'success'
        );
    }
    
    /**
     * Log system settings changes
     */
    public function logSystemSettingsChange($setting, $oldValue, $newValue) {
        return $this->log(
            'system_settings_change',
            'system',
            "System setting '{$setting}' changed",
            [$setting => $oldValue],
            [$setting => $newValue],
            null,
            'system',
            'high',
            'success'
        );
    }
    
    /**
     * Log admin access
     */
    public function logAdminAccess($action, $details = '') {
        return $this->log(
            'admin_access',
            'admin',
            "Admin access: {$action}" . ($details ? " - {$details}" : ""),
            null,
            ['action' => $action, 'details' => $details],
            null,
            'admin',
            'medium',
            'success'
        );
    }
    
    /**
     * Log data export/import
     */
    public function logDataExport($type, $recordCount) {
        return $this->log(
            'data_export',
            'data_management',
            "Data exported: {$type} ({$recordCount} records)",
            null,
            ['type' => $type, 'count' => $recordCount],
            null,
            'data',
            'medium',
            'success'
        );
    }
    
    public function logDataImport($type, $recordCount, $success = true) {
        $status = $success ? 'success' : 'failed';
        $severity = $success ? 'medium' : 'high';
        
        return $this->log(
            'data_import',
            'data_management',
            $success ? "Data imported: {$type} ({$recordCount} records)" : "Data import failed: {$type}",
            null,
            ['type' => $type, 'count' => $recordCount],
            null,
            'data',
            $severity,
            $status
        );
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Get audit logs with filtering
     */
    public function getLogs($filters = [], $limit = 50, $offset = 0) {
        $whereConditions = [];
        $params = [];
        
        // Build WHERE conditions based on filters
        if (!empty($filters['user_id'])) {
            $whereConditions[] = "al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action_type'])) {
            $whereConditions[] = "al.action_type = ?";
            $params[] = $filters['action_type'];
        }
        
        if (!empty($filters['module'])) {
            $whereConditions[] = "al.module = ?";
            $params[] = $filters['module'];
        }
        
        if (!empty($filters['severity'])) {
            $whereConditions[] = "al.severity = ?";
            $params[] = $filters['severity'];
        }
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "al.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "al.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "al.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(al.description LIKE ? OR al.username LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $sql = "
            SELECT al.*, u.username as user_username, u.email as user_email
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.user_id
            {$whereClause}
            ORDER BY al.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get audit log statistics
     */
    public function getLogStats($filters = []) {
        $whereConditions = [];
        $params = [];
        
        // Build WHERE conditions based on filters (same as getLogs)
        if (!empty($filters['user_id'])) {
            $whereConditions[] = "user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action_type'])) {
            $whereConditions[] = "action_type = ?";
            $params[] = $filters['action_type'];
        }
        
        if (!empty($filters['module'])) {
            $whereConditions[] = "module = ?";
            $params[] = $filters['module'];
        }
        
        if (!empty($filters['severity'])) {
            $whereConditions[] = "severity = ?";
            $params[] = $filters['severity'];
        }
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $sql = "
            SELECT 
                COUNT(*) as total_logs,
                COUNT(CASE WHEN severity = 'critical' THEN 1 END) as critical_logs,
                COUNT(CASE WHEN severity = 'high' THEN 1 END) as high_logs,
                COUNT(CASE WHEN severity = 'medium' THEN 1 END) as medium_logs,
                COUNT(CASE WHEN severity = 'low' THEN 1 END) as low_logs,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_logs,
                COUNT(CASE WHEN action_type LIKE '%login%' THEN 1 END) as login_logs,
                COUNT(CASE WHEN action_type LIKE '%delete%' THEN 1 END) as delete_logs
            FROM audit_logs
            {$whereClause}
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch();
    }
}
?>
