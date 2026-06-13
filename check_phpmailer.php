<?php
$paths = [
    __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php',
    __DIR__ . '/vendor/phpmailer/src/PHPMailer.php',
    __DIR__ . '/phpmailer/src/PHPMailer.php',
];

foreach ($paths as $path) {
    if (file_exists($path)) {
        echo "✅ Найден: " . $path . "<br>";
    } else {
        echo "❌ Не найден: " . $path . "<br>";
    }
}