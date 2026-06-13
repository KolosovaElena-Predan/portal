<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Тест отправки письма</h1>";

// 1. Проверяем email_config.php
echo "<h2>1. Проверка email_config.php</h2>";
$configPath = __DIR__ . '/mip/includes/email_config.php';

if (file_exists($configPath)) {
    echo "✅ Файл email_config.php найден<br>";
    require_once $configPath;
    echo "✅ Файл подключен<br>";
} else {
    echo "❌ Файл НЕ НАЙДЕН: " . $configPath . "<br>";
    exit;
}

// 2. Проверяем функцию
echo "<h2>2. Проверка функции sendEmailNotification</h2>";
if (function_exists('sendEmailNotification')) {
    echo "✅ Функция sendEmailNotification существует<br>";
} else {
    echo "❌ Функция sendEmailNotification НЕ найдена<br>";
    exit;
}

// 3. Проверяем PHPMailer (если есть)
echo "<h2>3. Проверка PHPMailer</h2>";
$phpmailerPath = __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
if (file_exists($phpmailerPath)) {
    echo "✅ PHPMailer найден: " . $phpmailerPath . "<br>";
} else {
    echo "⚠️ PHPMailer НЕ найден, будет использована заглушка<br>";
}

// 4. Отправляем тестовое письмо
echo "<h2>4. Отправка тестового письма</h2>";
$result = sendEmailNotification(
    'kolosov.kek.279@gmail.com',
    'Тестовый пользователь',
    'Тест SMTP ' . date('Y-m-d H:i:s'),
    '<h2 style="color:#00a896;">✅ Тест SMTP</h2>
     <p>Письмо успешно отправлено!</p>
     <p>Время: ' . date('Y-m-d H:i:s') . '</p>'
);

if ($result) {
    echo "<h3 style='color:green'>✅ Письмо отправлено! Проверьте почту.</h3>";
} else {
    echo "<h3 style='color:red'>❌ Ошибка при отправке письма</h3>";
}

// 5. Дополнительная информация
echo "<h2>5. Информация о сервере</h2>";
echo "Документ root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Текущий файл: " . __FILE__ . "<br>";
echo "PHP версия: " . phpversion() . "<br>";