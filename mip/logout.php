<?php
// logout.php
session_start();
session_destroy();

// Редирект на главную или страницу, с которой пришёл пользователь
$referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
// Защита от открытых редиректов
if (strpos($referer, $_SERVER['HTTP_HOST']) === false) {
    $referer = 'index.php';
}
header('Location: ' . $referer);
exit;
?>