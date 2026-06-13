<?php
require_once 'mip/includes/email_config.php';

$result = sendEmailNotification(
    'kolosov.kek.279@gmail.com',
    'Тест',
    'Тест SMTP на сервере',
    '<h2>✅ Письмо отправлено!</h2><p>Всё работает.</p><p>Время: ' . date('Y-m-d H:i:s') . '</p>'
);

echo $result ? '✅ Письмо отправлено! Проверьте почту.' : '❌ Ошибка. Смотрите логи.';