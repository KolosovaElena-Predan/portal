<?php
/**
 * email_config.php - Настройки SMTP для отправки писем
 * Версия без Composer (ручное подключение)
 */

// Подключаем PHPMailer вручную (если скачали в папку phpmailer)
if (file_exists(__DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php')) {
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
} else {
    // Если файлов нет - просто логируем и возвращаем false
    error_log("PHPMailer не установлен! Письма не будут отправляться.");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Определяем окружение
$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']);

/**
 * Отправка email-уведомления через SMTP
 */
function sendEmailNotification($toEmail, $toName, $subject, $htmlMessage) {
    global $isLocal;
    
    // Проверяем, что PHPMailer доступен
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer не установлен. Письмо не отправлено: {$toEmail}");
        if ($isLocal) {
            echo "<!-- PHPMailer не установлен. Письмо не отправлено -->";
        }
        return false;
    }
    
    // Для локальной разработки
    if ($isLocal) {
        error_log("=== ЛОКАЛЬНАЯ РАЗРАБОТКА: Письмо не отправлено ===");
        error_log("Кому: {$toEmail}");
        error_log("Тема: {$subject}");
        error_log("=============================================");
        return true;
    }
    
    try {
        $mail = new PHPMailer(true);
        
        // Настройки SMTP (Mail.ru)
        $mail->isSMTP();
        $mail->Host       = 'smtp.mail.ru';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'elena.kolosova.04@mail.ru';
        $mail->Password   = 'a6G2nmXT0Y8sRTPTwCu0'; // ЗАМЕНИТЕ!
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('elena.kolosova.04@mail.ru', 'МИП «НПЦ ПИТиА»');
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo('elena.kolosova.04@mail.ru', 'Поддержка МИП');
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlMessage;
        $mail->AltBody = strip_tags($htmlMessage);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("SMTP Error: " . ($mail->ErrorInfo ?? $e->getMessage()));
        return false;
    }
}