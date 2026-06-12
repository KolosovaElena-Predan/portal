<?php
// Выход из аккаунта
session_start();
session_destroy();


$referer = $_SERVER['HTTP_REFERER'] ?? 'mip.php';
if (strpos($referer, $_SERVER['HTTP_HOST']) === false) {
    $referer = 'mip.php';
}
header('Location: ' . $referer);
exit;
?>