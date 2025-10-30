<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
require_once __DIR__ . '/config/notifications_helper.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => true, 'notifications' => [], 'unread' => 0]);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$limit = isset($_GET['limit']) ? max(5, min(50, (int)$_GET['limit'])) : 20;
$notifications = getUserNotifications($userId, $limit);
$unread = 0;
foreach ($notifications as $n) { if ((int)$n['is_read'] === 0) { $unread++; } }

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread' => $unread
]);
?>


