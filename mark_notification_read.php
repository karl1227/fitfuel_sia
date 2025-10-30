<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
require_once __DIR__ . '/config/notifications_helper.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$notificationId = isset($input['notification_id']) ? (int)$input['notification_id'] : 0;
$all = isset($input['all']) ? (bool)$input['all'] : false;

if ($all) {
    $ok = markAllNotificationsRead($userId);
    echo json_encode(['success' => $ok]);
    exit;
}

if ($notificationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid notification']);
    exit;
}

$ok = markNotificationRead($userId, $notificationId);
echo json_encode(['success' => $ok]);
?>


