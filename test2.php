<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. Начало<br>";

// Проверяем путь к файлу
$configFile = __DIR__ . '/mip/includes/email_config.php';
echo "2. Ищу файл: " . $configFile . "<br>";

if (file_exists($configFile)) {
    echo "3. Файл найден<br>";
} else {
    echo "3. ФАЙЛ НЕ НАЙДЕН!<br>";
    exit;
}

// Подключаем без выполнения функций
echo "4. Подключаю файл...<br>";
$content = file_get_contents($configFile);
echo "5. Файл прочитан, размер: " . strlen($content) . " байт<br>";

// Проверяем синтаксис
echo "6. Проверяю синтаксис...<br>";
$output = shell_exec("php -l " . escapeshellarg($configFile) . " 2>&1");
echo "7. Результат проверки: " . nl2br($output) . "<br>";

echo "8. Тест завершен";