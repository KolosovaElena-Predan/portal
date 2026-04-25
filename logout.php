<?php
// Выход из аккаунта
session_start();
session_destroy();


$referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
if (strpos($referer, $_SERVER['HTTP_HOST']) === false) {
    $referer = 'index.php';
}
header('Location: ' . $referer);
exit;
?>