<?php
session_start();
require_once 'config.php';
require_once 'includes/notifications.php';

if (isset($_SESSION['user_id'])) {
    markAllNotificationsAsRead($pdo, $_SESSION['user_id']);
}