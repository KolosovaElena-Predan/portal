<?php
/**
 * SMTP Configuration for Mail.ru
 * Используется для отправки писем с сайта
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Отправка письма через SMTP (Mail.ru)
 * 
 * @param string $to_email Email получателя
 * @param string $to_name Имя получателя
 * @param string $subject Тема письма
 * @param string $html_message HTML-содержимое письма
 * @return bool true при успешной отправке, false при ошибке
 */
function sendMailViaSMTP($to_email, $to_name, $subject, $html_message) {
    
    // Подключаем PHPMailer (пути относительные от корня portal)
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    
    // ============================================
    // НАСТРОЙКИ MAIL.RU (ЗАМЕНИТЕ НА СВОИ)
    // ============================================
    $mail = new PHPMailer(true);
    
    try {
        // Настройки сервера
        $mail->isSMTP();
        $mail->Host       = 'smtp.mail.ru';           // SMTP сервер Mail.ru
        $mail->SMTPAuth   = true;                     // Включить авторизацию
        $mail->Username   = 'elena.kolosova.04@mail.ru';  // ВАШ EMAIL (полностью)
        $mail->Password   = 'a6G2nmXT0Y8sRTPTwCu0';   // ПАРОЛЬ ПРИЛОЖЕНИЯ (не от аккаунта!)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL (порт 465)
        $mail->Port       = 465;                      // SSL порт
        
        // Кодировка
        $mail->CharSet = 'UTF-8';
        
        // Отправитель (ОБЯЗАТЕЛЬНО совпадает с Username для Mail.ru)
        $mail->setFrom('elena.kolosova.04@mail.ru', 'МИП «НПЦ ПИТиА»');
        
        // Получатель
        $mail->addAddress($to_email, $to_name);
        
        // Email для ответа (можно тот же или другой)
        $mail->addReplyTo('elena.kolosova.04@mail.ru', 'Поддержка МИП');
        
        // Содержимое письма
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_message;
        
        // Текстовая версия (для почтовых клиентов без HTML)
        $mail->AltBody = strip_tags($html_message);
        
        // Отправка
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // Логирование ошибки
        error_log("Ошибка отправки письма: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Альтернативная функция для тестирования SMTP
 * Отправляет тестовое письмо на указанный email
 */
function testMail($to_email = null) {
    if (!$to_email) {
        // Если email не указан, используем email отправителя
        $to_email = 'elena.kolosova.04@mail.ru';
    }
    
    $subject = "Тест SMTP от " . date('Y-m-d H:i:s');
    $message = "
        <html>
        <head><meta charset='utf-8'></head>
        <body style='font-family: Arial, sans-serif;'>
            <h2 style='color: #1a1982;'>✅ SMTP работает корректно!</h2>
            <p>Это тестовое письмо с сайта <strong>ООО МИП «НПЦ ПИТиА»</strong>.</p>
            <p>Время отправки: <strong>" . date('d.m.Y H:i:s') . "</strong></p>
            <hr>
            <p style='font-size: 12px; color: #666;'>Если вы получили это письмо, значит настройка SMTP завершена успешно.</p>
        </body>
        </html>
    ";
    
    return sendMailViaSMTP($to_email, 'Тестовый пользователь', $subject, $message);
}