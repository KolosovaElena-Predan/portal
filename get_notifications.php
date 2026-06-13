<?php
session_start();
require_once 'config.php';
require_once 'includes/notifications.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['unread_count' => 0, 'notifications' => []]);
    exit;
}

$userId = $_SESSION['user_id'];
$unreadCount = getUnreadCount($pdo, $userId);
$notifications = getUnreadNotifications($pdo, $userId);

foreach ($notifications as &$n) {
    $n['created_at'] = date('d.m.Y H:i', strtotime($n['created_at']));
}

echo json_encode([
    'unread_count' => $unreadCount,
    'notifications' => $notifications
]);
?>