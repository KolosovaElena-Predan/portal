<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Тест отправки письма</h1>";

require_once 'mip/includes/email_config.php';
echo "✅ email_config.php подключен<br>";

$result = sendEmailNotification(
    'kolosov.kek.279@gmail.com',
    'Тест',
    'Тест SMTP ' . date('Y-m-d H:i:s'),
    '<h2>✅ Тест</h2><p>Письмо отправлено!</p>'
);

echo $result ? '✅ Письмо отправлено!' : '❌ Ошибка';