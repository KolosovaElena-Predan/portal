<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Тест без подключения сложных файлов<br>";

// Проверяем mail()
$to = "kolosov.kek.279@gmail.com";
$subject = "Тест mail() " . date('Y-m-d H:i:s');
$message = "Это тестовое письмо через функцию mail()";
$headers = "From: test@" . $_SERVER['HTTP_HOST'] . "\r\n";

$result = mail($to, $subject, $message, $headers);

if ($result) {
    echo "✅ Письмо через mail() отправлено! Проверьте почту.";
} else {
    echo "❌ Ошибка при отправке через mail()";
}