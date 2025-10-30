<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/email_config.php';

function getPdo(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = getDBConnection();
    }
    return $pdo;
}

function getNotificationSetting(string $key, string $default = '1'): string {
    try {
        $pdo = getPdo();
        $stmt = $pdo->prepare('SELECT setting_value FROM notification_settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (string)$row['setting_value'] : $default;
    } catch (Throwable $e) {
        return $default;
    }
}

function createNotification(int $userId, string $type, string $title, string $message, ?string $link = null): bool {
    $pdo = getPdo();
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)');
    return $stmt->execute([$userId, $type, $title, $message, $link]);
}

function getUserNotifications(int $userId, int $limit = 20): array {
    $pdo = getPdo();
    $stmt = $pdo->prepare('SELECT notification_id, type, title, message, link, is_read, created_at
                            FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markNotificationRead(int $userId, int $notificationId): bool {
    $pdo = getPdo();
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?');
    return $stmt->execute([$notificationId, $userId]);
}

function markAllNotificationsRead(int $userId): bool {
    $pdo = getPdo();
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0');
    return $stmt->execute([$userId]);
}

function sendOrderConfirmationEmail(int $userId, int $orderId, string $customOrderId): void {
    if (getNotificationSetting('email_order_confirmation_enabled', '1') !== '1') {
        return;
    }
    try {
        $pdo = getPdo();
        $userStmt = $pdo->prepare('SELECT email, full_name FROM users WHERE user_id = ? LIMIT 1');
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) return;

        // Use PHPMailer config wrapper
        $mailer = getMailer();
        $mailer->addAddress($user['email'], $user['full_name'] ?? '');
        $mailer->Subject = 'Your FitFuel Order Confirmation ' . $customOrderId;
        $mailer->isHTML(true);
        $orderLink = sprintf('%s/order_details.php?order_id=%d', getBaseUrl(), $orderId);
        $body = '<p>Hi ' . htmlspecialchars($user['full_name'] ?? 'there') . ',</p>' .
                '<p>Thanks for your order. Your order ID is <strong>' . htmlspecialchars($customOrderId) . '</strong>.</p>' .
                '<p>You can view your order details here: <a href="' . htmlspecialchars($orderLink) . '">Order Details</a></p>' .
                '<p>— FitFuel Team</p>';
        $mailer->Body = $body;
        $mailer->send();
    } catch (Throwable $e) {
        // Swallow email errors to not affect checkout flow
    }
}

function getBaseUrl(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    return $protocol . '://' . $host . ($path ? $path : '');
}

?>


