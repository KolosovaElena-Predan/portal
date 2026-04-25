<?php
// C:\xampp\htdocs\portal\test_mail.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаем PHPMailer вручную
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = require __DIR__ . '/config_mail.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $config['smtp_host'];
    $mail->SMTPAuth   = $config['smtp_auth'];
    $mail->Username   = $config['username'];
    $mail->Password   = $config['password'];
    $mail->SMTPSecure = $config['smtp_secure'];
    $mail->Port       = $config['smtp_port'];
    $mail->CharSet    = $config['charset'];
    
    // 🔹 КРИТИЧНО: From должен совпадать с username для Mail.ru
    $mail->setFrom($config['username'], $config['from_name']);
    
    // 🔹 Получатель (можно тот же самый, для теста)
    $mail->addAddress($config['username']);
    
    // 🔹 Добавляем заголовок, чтобы снизить спам-рейтинг
    $mail->addCustomHeader('X-Mailer', 'PHPMailer via MIП-Portal');
    $mail->addCustomHeader('X-Priority', '3');
    
    // 🔹 Контент письма
    $mail->isHTML(true);
    $mail->Subject = 'Тест отправки с сайта МИП';
    
    // 🔹 HTML-версия (сделали более «человеческой»)
    $mail->Body = '
        <html>
        <head><meta charset="utf-8"></head>
        <body style="font-family: Arial, sans-serif; color: #333;">
            <h2 style="color: #1a1982;">Тестовое письмо</h2>
            <p>Здравствуйте!</p>
            <p>Это тестовое сообщение с сайта <strong>ООО МИП «НПЦ ПИТиА»</strong>.</p>
            <p>Если вы получили это письмо, значит настройка SMTP завершена успешно. ✅</p>
            <hr>
            <p style="font-size: 12px; color: #666;">
                Письмо сгенерировано автоматически. Не отвечайте на него.<br>
                © 2026 МИП НПЦ ПИТиА
            </p>
        </body>
        </html>
    ';
    
    // 🔹 Plain text альтернатива (ОБЯЗАТЕЛЬНО для снижения спам-рейтинга!)
    $mail->AltBody = "Тестовое письмо с сайта МИП НПЦ ПИТиА.\n\nЕсли вы видите этот текст, значит настройка SMTP работает корректно.\n\n© 2026 МИП НПЦ ПИТиА";
    
    // 🔹 Отладка (можно убрать после успешной отправки)
    $mail->SMTPDebug = 0; // Поставьте 2, если снова будут ошибки

    $mail->send();
    echo '<h2 style="color: green;">✅ Письмо успешно отправлено!</h2>';
    echo '<p>Проверьте ящик <strong>' . htmlspecialchars($config['username']) . '</strong></p>';
    echo '<p>⚠️ Если письма нет во «Входящих», проверьте папку <strong>«Спам»</strong>.</p>';
    
} catch (Exception $e) {
    echo '<h2 style="color: red;">❌ Ошибка отправки</h2>';
    echo '<pre>' . htmlspecialchars($mail->ErrorInfo) . '</pre>';
    
    // 🔹 Подсказка для отладки
    echo '<details><summary>🔧 Включить подробный лог?</summary>';
    echo '<p>Добавьте эти строки перед $mail->send() в коде:</p>';
    echo '<pre>$mail->SMTPDebug = 2;
$mail->Debugoutput = function($str) { echo htmlspecialchars($str)."<br>"; };</pre>';
    echo '</details>';
}