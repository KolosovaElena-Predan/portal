<?php
session_start();
require_once 'config.php';
require_once 'includes/notifications.php';

if (!isset($_SESSION['user_id'])) {
    die('Пользователь не авторизован');
}

$userId = $_SESSION['user_id'];
$notifications = getAllNotifications($pdo, $userId, 100);

echo "<h1>Тест уведомлений</h1>";
echo "<p>User ID: $userId</p>";
echo "<p>Всего уведомлений: " . count($notifications) . "</p>";

if (empty($notifications)) {
    echo "<p style='color:red'>Нет уведомлений</p>";
} else {
    echo "<ul>";
    foreach ($notifications as $n) {
        echo "<li>ID: {$n['id']}, прочитано: {$n['is_read']}, title: " . htmlspecialchars($n['title']) . "</li>";
    }
    echo "</ul>";
}
?>