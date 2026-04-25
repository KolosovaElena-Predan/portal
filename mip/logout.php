<?php
// logout.php
session_start();
session_destroy();

// Редирект на главную или страницу, с которой пришёл пользователь
$referer = $_SERVER['HTTP_REFERER'] ?? 'mip.php';
// Защита от открытых редиректов
if (strpos($referer, $_SERVER['HTTP_HOST']) === false) {
    $referer = 'mip.php';
}
header('Location: ' . $referer);
exit;
?>