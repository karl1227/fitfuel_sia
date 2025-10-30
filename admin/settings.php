<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';
require_once '../config/audit_logger.php';
require_once '../includes/admin_sidebar.php';

// Check role-based access
requireAccess('settings');

// Ensure settings table exists
function ensureSettingsSchema(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        key_name VARCHAR(100) NOT NULL,
        value TEXT,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (setting_id),
        UNIQUE KEY (key_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
}

// Initialize default settings if they don't exist
function initializeDefaultSettings(PDO $pdo) {
    $defaults = [
        'currency_code' => 'PHP',
        'currency_symbol' => '₱',
        'currency_position' => 'before',
        'site_name' => 'FitFuel',
        'site_email' => 'info@fitfuel.com',
        'site_phone' => '+63 123 456 7890',
        'paypal_enabled' => '1',
        'cash_on_delivery_enabled' => '1',
        'bank_transfer_enabled' => '1',
        'tax_enabled' => '1',
        'tax_rate' => '0.12',
        'maintenance_mode' => '0'
    ];
    
    foreach ($defaults as $key => $value) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO settings (key_name, value) VALUES (?, ?)");
        $stmt->execute([$key, $value]);
    }
}

$pdo = getDBConnection();
$auditLogger = new AuditLogger();
ensureSettingsSchema($pdo);

$message = '';
$error = '';

// Initialize defaults
initializeDefaultSettings($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_settings') {
            $settings = [
                'currency_code' => $_POST['currency_code'] ?? 'PHP',
                'currency_symbol' => $_POST['currency_symbol'] ?? '₱',
                'currency_position' => $_POST['currency_position'] ?? 'before',
                'site_name' => $_POST['site_name'] ?? 'FitFuel',
                'site_email' => $_POST['site_email'] ?? '',
                'site_phone' => $_POST['site_phone'] ?? '',
                'paypal_enabled' => isset($_POST['paypal_enabled']) ? '1' : '0',
                'cash_on_delivery_enabled' => isset($_POST['cash_on_delivery_enabled']) ? '1' : '0',
                'bank_transfer_enabled' => isset($_POST['bank_transfer_enabled']) ? '1' : '0',
                'tax_enabled' => isset($_POST['tax_enabled']) ? '1' : '0',
                'tax_rate' => $_POST['tax_rate'] ?? '0.12',
                'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0'
            ];
            
            // Log old settings
            $oldSettings = [];
            $stmt = $pdo->query("SELECT key_name, value FROM settings");
            while ($row = $stmt->fetch()) {
                $oldSettings[$row['key_name']] = $row['value'];
            }
            
            // Update settings
            $updateStmt = $pdo->prepare("UPDATE settings SET value = ? WHERE key_name = ?");
            foreach ($settings as $key => $value) {
                $updateStmt->execute([$value, $key]);
            }
            
            // Log settings update
            $auditLogger->log(
                'system_settings_change',
                'settings',
                'Platform settings updated',
                $oldSettings,
                $settings,
                null,
                'settings',
                'high',
                'success',
                $_SESSION['user_id']
            );
            
            $message = 'Settings updated successfully';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch all settings
$settingsStmt = $pdo->query("SELECT key_name, value FROM settings");
$settings = [];
while ($row = $settingsStmt->fetch()) {
    $settings[$row['key_name']] = $row['value'];
}

// Helper function to get setting value
function setting($key, $default = '') {
    global $settings;
    return $settings[$key] ?? $default;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - FitFuel Admin</title>
    <link rel="icon" href="../img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .sidebar-item.active { background-color: #f3f4f6; border-right: 3px solid #000; }
        .sidebar-item:hover { background-color: #f9fafb; }
        .settings-section { border-bottom: 2px solid #e5e7eb; }
    </style>
</head>
<body class="font-body bg-gray-50">
    <?php require_once '../includes/admin_header.php'; ?>

    <?php renderAdminSidebar('settings'); ?>

    <main class="ml-64 pt-24 pb-6 px-6">
        <div class="mb-8">
            <div class="flex items-center space-x-3 mb-2">
                <i class="fas fa-cog text-2xl text-gray-600"></i>
                <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
            </div>
            <p class="text-gray-600">Manage platform-wide settings including currency, payment options, tax, and site information.</p>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white rounded-lg border border-gray-200">
            <input type="hidden" name="action" value="update_settings">
            
            <!-- Currency Settings -->
            <div class="settings-section p-6">
                <div class="flex items-center space-x-2 mb-4">
                    <i class="fas fa-coins text-xl text-gray-600"></i>
                    <h2 class="text-xl font-semibold text-gray-900">Currency Settings</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Currency Code</label>
                        <input type="text" name="currency_code" value="<?php echo htmlspecialchars(setting('currency_code', 'PHP')); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Currency Symbol</label>
                        <input type="text" name="currency_symbol" value="<?php echo htmlspecialchars(setting('currency_symbol', '₱')); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Symbol Position</label>
                        <select name="currency_position" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="before" <?php echo setting('currency_position') === 'before' ? 'selected' : ''; ?>>Before (₱100)</option>
                            <option value="after" <?php echo setting('currency_position') === 'after' ? 'selected' : ''; ?>>After (100₱)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Site Information -->
            <div class="settings-section p-6">
                <div class="flex items-center space-x-2 mb-4">
                    <i class="fas fa-info-circle text-xl text-gray-600"></i>
                    <h2 class="text-xl font-semibold text-gray-900">Site Information</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Site Name</label>
                        <input type="text" name="site_name" value="<?php echo htmlspecialchars(setting('site_name', 'FitFuel')); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                        <input type="email" name="site_email" value="<?php echo htmlspecialchars(setting('site_email', '')); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                        <input type="text" name="site_phone" value="<?php echo htmlspecialchars(setting('site_phone', '')); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                </div>
            </div>

            <!-- Payment Settings -->
            <div class="settings-section p-6">
                <div class="flex items-center space-x-2 mb-4">
                    <i class="fas fa-credit-card text-xl text-gray-600"></i>
                    <h2 class="text-xl font-semibold text-gray-900">Payment Settings</h2>
                </div>
                <div class="space-y-6">
                    <!-- PayPal Settings -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" name="paypal_enabled" id="paypal_enabled" value="1" 
                                   <?php echo setting('paypal_enabled') === '1' ? 'checked' : ''; ?> 
                                   class="w-5 h-5 text-black border-gray-300 rounded focus:ring-black">
                            <label for="paypal_enabled" class="text-sm font-semibold text-gray-900">Enable PayPal</label>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">PayPal credentials are configured in <code class="bg-gray-100 px-1 py-0.5 rounded">config/paypal_config.php</code></p>
                    </div>

                    <!-- Cash on Delivery -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" name="cash_on_delivery_enabled" id="cash_on_delivery_enabled" value="1" 
                                   <?php echo setting('cash_on_delivery_enabled') === '1' ? 'checked' : ''; ?> 
                                   class="w-5 h-5 text-black border-gray-300 rounded focus:ring-black">
                            <label for="cash_on_delivery_enabled" class="text-sm font-semibold text-gray-900">Enable Cash on Delivery (COD)</label>
                        </div>
                    </div>

                    <!-- Bank Transfer -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" name="bank_transfer_enabled" id="bank_transfer_enabled" value="1" 
                                   <?php echo setting('bank_transfer_enabled') === '1' ? 'checked' : ''; ?> 
                                   class="w-5 h-5 text-black border-gray-300 rounded focus:ring-black">
                            <label for="bank_transfer_enabled" class="text-sm font-semibold text-gray-900">Enable Bank Transfer</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax Settings -->
            <div class="settings-section p-6">
                <div class="flex items-center space-x-2 mb-4">
                    <i class="fas fa-file-invoice text-xl text-gray-600"></i>
                    <h2 class="text-xl font-semibold text-gray-900">Tax Settings</h2>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" name="tax_enabled" id="tax_enabled" value="1" 
                               <?php echo setting('tax_enabled') === '1' ? 'checked' : ''; ?> 
                               class="w-5 h-5 text-black border-gray-300 rounded focus:ring-black">
                        <label for="tax_enabled" class="text-sm font-medium text-gray-700">Enable Tax</label>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%)</label>
                            <div class="relative">
                                <input type="number" name="tax_rate" value="<?php echo htmlspecialchars(setting('tax_rate', '0.12')); ?>" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2" step="0.01" min="0" max="1">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Enter as decimal (e.g., 0.12 for 12%)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Mode -->
            <div class="settings-section p-6">
                <div class="flex items-center space-x-2 mb-4">
                    <i class="fas fa-wrench text-xl text-gray-600"></i>
                    <h2 class="text-xl font-semibold text-gray-900">Maintenance</h2>
                </div>
                <div class="flex items-center space-x-3">
                    <input type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1" 
                           <?php echo setting('maintenance_mode') === '1' ? 'checked' : ''; ?> 
                           class="w-5 h-5 text-black border-gray-300 rounded focus:ring-black">
                    <label for="maintenance_mode" class="text-sm font-medium text-gray-700">Enable Maintenance Mode</label>
                </div>
                <p class="text-xs text-gray-500 mt-2">When enabled, only administrators can access the site.</p>
            </div>

            <!-- Submit Button -->
            <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <button type="submit" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800 flex items-center space-x-2">
                    <i class="fas fa-save"></i>
                    <span>Save Settings</span>
                </button>
            </div>
        </form>
    </main>

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

