<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';

$pdo = getDBConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid audit log ID']);
    exit;
}

$auditId = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT al.*, u.username as user_username, u.email as user_email, u.role as user_role
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.user_id
        WHERE al.audit_id = ?
    ");
    
    $stmt->execute([$auditId]);
    $log = $stmt->fetch();
    
    if (!$log) {
        http_response_code(404);
        echo json_encode(['error' => 'Audit log not found']);
        exit;
    }
    
    // Parse JSON data
    $oldValues = $log['old_values'] ? json_decode($log['old_values'], true) : null;
    $newValues = $log['new_values'] ? json_decode($log['new_values'], true) : null;
    
    function getStatusBadgeClass($status) {
        switch($status) {
            case 'success': return 'bg-green-100 text-green-800 border-green-200';
            case 'failed': return 'bg-red-100 text-red-800 border-red-200';
            case 'warning': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            default: return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    }
    
    function formatValue($value) {
        if (is_array($value)) {
            return '<pre class="text-xs bg-gray-100 p-2 rounded overflow-auto max-h-32">' . htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT)) . '</pre>';
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_null($value)) {
            return '<span class="text-gray-400 italic">null</span>';
        } else {
            return htmlspecialchars($value);
        }
    }
    
    ob_start();
    ?>
    <div class="space-y-4">
        <!-- Basic Information -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Audit ID</label>
                <p class="text-sm text-gray-900"><?php echo $log['audit_id']; ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Timestamp</label>
                <p class="text-sm text-gray-900"><?php echo date('F j, Y \a\t g:i A', strtotime($log['created_at'])); ?></p>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">User</label>
                <div class="text-sm text-gray-900">
                    <?php if ($log['user_id']): ?>
                        <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-500 text-xs"></i>
                            </div>
                            <div>
                                <div class="font-medium"><?php echo htmlspecialchars($log['username']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($log['user_email']); ?></div>
                                <div class="text-xs text-blue-600"><?php echo ucfirst($log['user_role']); ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="text-gray-400 italic">System</span>
                    <?php endif; ?>
                </div>
            </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Action Type</label>
                <p class="text-sm text-gray-900"><?php echo ucwords(str_replace('_', ' ', $log['action_type'])); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Module</label>
                <p class="text-sm text-gray-900"><?php echo ucwords(str_replace('_', ' ', $log['module'])); ?></p>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?php echo getStatusBadgeClass($log['status']); ?>">
                    <?php echo ucfirst($log['status']); ?>
                </span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Reference Type</label>
                <p class="text-sm text-gray-900"><?php echo ucfirst($log['reference_type']); ?></p>
            </div>
        </div>
        
        <?php if ($log['reference_id'] && $log['reference_type']): ?>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Reference ID</label>
                <p class="text-sm text-gray-900"><?php echo $log['reference_id']; ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Description -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($log['description']); ?></p>
            </div>
        </div>
        
        <!-- Old Values -->
        <?php if ($oldValues): ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Old Values</label>
            <div class="bg-red-50 rounded-lg p-3 border border-red-200">
                <?php if (is_array($oldValues)): ?>
                    <div class="space-y-2">
                        <?php foreach ($oldValues as $key => $value): ?>
                            <div class="flex items-start space-x-2">
                                <span class="text-xs font-medium text-red-700 min-w-0 flex-shrink-0"><?php echo htmlspecialchars($key); ?>:</span>
                                <div class="text-xs text-red-900 min-w-0 flex-1"><?php echo formatValue($value); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <pre class="text-xs text-red-900 whitespace-pre-wrap"><?php echo htmlspecialchars($log['old_values']); ?></pre>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- New Values -->
        <?php if ($newValues): ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">New Values</label>
            <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                <?php if (is_array($newValues)): ?>
                    <div class="space-y-2">
                        <?php foreach ($newValues as $key => $value): ?>
                            <div class="flex items-start space-x-2">
                                <span class="text-xs font-medium text-green-700 min-w-0 flex-shrink-0"><?php echo htmlspecialchars($key); ?>:</span>
                                <div class="text-xs text-green-900 min-w-0 flex-1"><?php echo formatValue($value); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <pre class="text-xs text-green-900 whitespace-pre-wrap"><?php echo htmlspecialchars($log['new_values']); ?></pre>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="mt-6 flex justify-end">
        <button onclick="closeLogDetails()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
            Close
        </button>
    </div>
    <?php
    
    $html = ob_get_clean();
    
    header('Content-Type: application/json');
    echo json_encode(['html' => $html]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
