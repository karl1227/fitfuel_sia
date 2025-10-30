<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';
require_once '../includes/admin_sidebar.php';
require_once '../config/notifications_helper.php';

requireAccess('settings');

$pdo = getDBConnection();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle settings save
    if (isset($_POST['save_settings'])) {
        $email_order_confirmation_enabled = isset($_POST['email_order_confirmation_enabled']) ? '1' : '0';
        $notif_order_status_enabled = isset($_POST['notif_order_status_enabled']) ? '1' : '0';
        $notif_promotions_enabled = isset($_POST['notif_promotions_enabled']) ? '1' : '0';
        try {
            $stmt = $pdo->prepare('INSERT INTO notification_settings (setting_key, setting_value) VALUES (?,?)
                                   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            $pdo->beginTransaction();
            $stmt->execute(['email_order_confirmation_enabled', $email_order_confirmation_enabled]);
            $stmt->execute(['notif_order_status_enabled', $notif_order_status_enabled]);
            $stmt->execute(['notif_promotions_enabled', $notif_promotions_enabled]);
            $pdo->commit();
            $message = 'Notification settings updated successfully.';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Failed to update settings: ' . $e->getMessage();
        }
    }
    
    // Handle broadcast
    if (isset($_POST['send_broadcast']) && isset($_POST['broadcast_title']) && isset($_POST['broadcast_message'])) {
        $title = trim($_POST['broadcast_title']);
        $msg = trim($_POST['broadcast_message']);
        if ($title !== '' && $msg !== '') {
            try {
                $users = $pdo->query('SELECT user_id FROM users')->fetchAll(PDO::FETCH_ASSOC);
                $count = 0;
                foreach ($users as $u) {
                    createNotification((int)$u['user_id'], 'system', $title, $msg, null);
                    $count++;
                }
                $message = ($message ? $message . ' ' : '') . "Broadcast sent successfully to {$count} users.";
            } catch (Throwable $e) {
                $error = ($error ? $error . ' ' : '') . 'Broadcast failed: ' . $e->getMessage();
            }
        } else {
            $error = ($error ? $error . ' ' : '') . 'Please fill in both title and message fields.';
        }
    }
}

function getSetting($key, $def='1') {
    return getNotificationSetting($key, $def) === '1';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Admin</title>
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
    </style>
</head>
<body class="font-body bg-gray-50">
    <?php require_once '../includes/admin_header.php'; ?>
    <?php renderAdminSidebar('notifications'); ?>
    <main class="ml-64 pt-24 pb-6 px-6">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Notification Management</h1>
            <p class="text-gray-600">Configure notification settings and send broadcast messages to all users.</p>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Notification Settings -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-cog mr-2 text-gray-600"></i>
                <span>Automated Notifications</span>
            </h2>
            <form method="post" class="space-y-4">
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-3 rounded hover:bg-gray-50 transition-colors cursor-pointer">
                        <input type="checkbox" name="email_order_confirmation_enabled" value="1" <?php echo getSetting('email_order_confirmation_enabled') ? 'checked' : ''; ?> class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">Email: Order Confirmation</div>
                            <div class="text-sm text-gray-500">Send email notifications when orders are placed</div>
                        </div>
                    </label>
                    
                    <label class="flex items-center gap-3 p-3 rounded hover:bg-gray-50 transition-colors cursor-pointer">
                        <input type="checkbox" name="notif_order_status_enabled" value="1" <?php echo getSetting('notif_order_status_enabled') ? 'checked' : ''; ?> class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">In-app: Order Status Updates</div>
                            <div class="text-sm text-gray-500">Notify users about order status changes in the application</div>
                        </div>
                    </label>
                    
                    <label class="flex items-center gap-3 p-3 rounded hover:bg-gray-50 transition-colors cursor-pointer">
                        <input type="checkbox" name="notif_promotions_enabled" value="1" <?php echo getSetting('notif_promotions_enabled') ? 'checked' : ''; ?> class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">In-app: Promotions & Price Drops</div>
                            <div class="text-sm text-gray-500">Notify users about promotions and product price changes</div>
                        </div>
                    </label>
                </div>
                <div class="pt-4 border-t border-gray-200">
                    <button type="submit" name="save_settings" class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors font-medium">
                        <i class="fas fa-save mr-2"></i>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Broadcast Notification -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-bullhorn mr-2 text-gray-600"></i>
                <span>Broadcast Notification</span>
            </h2>
            <form method="post" class="space-y-4">
                <div>
                    <label for="broadcast_title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                    <input type="text" id="broadcast_title" name="broadcast_title" placeholder="Enter notification title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                </div>
                <div>
                    <label for="broadcast_message" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea id="broadcast_message" name="broadcast_message" placeholder="Enter notification message" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required></textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" name="send_broadcast" class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 transition-colors font-medium">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send Broadcast
                    </button>
                    <p class="text-sm text-gray-500 mt-2">This will send a notification to all users in the system.</p>
                </div>
            </form>
        </div>
    </main>
</body>
</html>


