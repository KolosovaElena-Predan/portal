<?php
/**
 * Проверка авторизации и прав администратора
 */
require_once '../config.php';

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'admin') {
    redirect('../authorization.php');
}
?>