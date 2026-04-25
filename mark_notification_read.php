<?php
session_start();
require_once 'config.php';
require_once 'includes/notifications.php';

if (isset($_SESSION['user_id']) && isset($_POST['id'])) {
    markNotificationAsRead($pdo, (int)$_POST['id'], $_SESSION['user_id']);
}