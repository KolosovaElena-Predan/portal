<?php
// includes/smtp_config.php
// НЕ включайте этот файл в публичный доступ!

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendMailViaSMTP($to_email, $to_name, $subject, $html_message) {
    // Загружаем PHPMailer
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
    
    // НАСТРОЙКИ MAIL.RU
    $mail = new PHPMailer(true);
    
    try {
        // Настройки сервера
        $mail->isSMTP();
        $mail->Host       = 'smtp.mail.ru';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'elena.kolosova.04@mail.ru';      // ВАШ EMAIL (полностью)
        $mail->Password   = 'a6G2nmXT0Y8sRTPTwCu0';   // ПАРОЛЬ ПРИЛОЖЕНИЯ (не от аккаунта!)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        $mail->Port       = 465;
        
        // Кодировка
        $mail->CharSet = 'UTF-8';
        
        // Отправитель
        $mail->setFrom('elena.kolosova.04@mail.ru', 'МИП «НПЦ ПИТиА»');
        
        // Получатель
        $mail->addAddress($to_email, $to_name);
        
        // Ответить на этот email (можно тот же)
        $mail->addReplyTo('elena.kolosova.04@mail.ru', 'Поддержка МИП');
        
        // Содержимое
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_message;
        $mail->AltBody = strip_tags($html_message); // текстовая версия
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Ошибка отправки письма: " . $mail->ErrorInfo);
        return false;
    }
}
?>