<?php
/**
 * email_config.php - Настройки SMTP для отправки писем
 * Путь: portal/mip/includes/email_config.php
 */

// Подключаем PHPMailer (если установлен через Composer)
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Определяем окружение
$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']);

/**
 * Отправка email-уведомления через SMTP (Mail.ru)
 * 
 * @param string $toEmail Email получателя
 * @param string $toName Имя получателя
 * @param string $subject Тема письма
 * @param string $htmlMessage HTML-содержимое
 * @return bool
 */
function sendEmailNotification($toEmail, $toName, $subject, $htmlMessage) {
    global $isLocal;
    
    // Для локальной разработки используем логирование (реальные письма не отправляем)
    if ($isLocal) {
        error_log("=== ЛОКАЛЬНАЯ РАЗРАБОТКА: Письмо не отправлено ===");
        error_log("Кому: {$toEmail}");
        error_log("Тема: {$subject}");
        error_log("Сообщение: " . strip_tags($htmlMessage));
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
        $mail->Password   = 'a6G2nmXT0Y8sRTPTwCu0'; // ЗАМЕНИТЕ НА ПАРОЛЬ ПРИЛОЖЕНИЯ!
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        // Кодировка
        $mail->CharSet = 'UTF-8';
        
        // Отправитель
        $mail->setFrom('elena.kolosova.04@mail.ru', 'МИП «НПЦ ПИТиА»');
        
        // Получатель
        $mail->addAddress($toEmail, $toName);
        
        // Email для ответа
        $mail->addReplyTo('elena.kolosova.04@mail.ru', 'Поддержка МИП');
        
        // Содержимое
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

/**
 * Шаблон уведомления о поступлении товара
 */
function getProductAvailableEmailTemplate($productName, $productUrl, $requestedQuantity = null, $productImage = null) {
    $quantityText = $requestedQuantity ? " (запрошено: {$requestedQuantity} шт.)" : "";
    $imageHtml = $productImage ? '<img src="' . $productImage . '" alt="' . htmlspecialchars($productName) . '" style="max-width: 200px; margin-bottom: 20px;">' : '';
    
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Товар поступил в наличие</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; margin: 0; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h2 { color: #00a896; margin-top: 0; }
            .btn { display: inline-block; padding: 12px 30px; background: #00a896; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; }
            .footer { font-size: 12px; color: #999; text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Товар поступил в наличие!</h2>
            <p>Здравствуйте!</p>
            <p>Товар <strong>' . htmlspecialchars($productName) . '</strong>' . $quantityText . ' снова доступен для заказа.</p>
            ' . $imageHtml . '
            <p style="text-align: center;">
                <a href="' . $productUrl . '" class="btn">Перейти к товару</a>
            </p>
            <div class="footer">
                Это автоматическое уведомление с сайта ООО МИП «НПЦ ПИТиА».<br>
                Пожалуйста, не отвечайте на это письмо.
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Шаблон уведомления об изменении статуса заказа
 */
function getOrderStatusEmailTemplate($orderId, $statusText, $statusComment = null) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Статус заказа изменён</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; margin: 0; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h2 { color: #00a896; margin-top: 0; }
            .status { font-size: 18px; color: #00a896; font-weight: bold; }
            .btn { display: inline-block; padding: 12px 30px; background: #00a896; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; }
            .footer { font-size: 12px; color: #999; text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Статус заказа изменён</h2>
            <p>Здравствуйте!</p>
            <p>Статус вашего заказа №<strong>' . $orderId . '</strong> изменён на:</p>
            <p class="status">' . htmlspecialchars($statusText) . '</p>
            ' . ($statusComment ? '<p>Комментарий: ' . nl2br(htmlspecialchars($statusComment)) . '</p>' : '') . '
            <p style="text-align: center;">
                <a href="https://' . $_SERVER['HTTP_HOST'] . '/portal/mip/lk_user.php" class="btn">Перейти в личный кабинет</a>
            </p>
            <div class="footer">
                Это автоматическое уведомление. Пожалуйста, не отвечайте на него.
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Шаблон уведомления о новом сообщении в чате
 */
function getNewMessageEmailTemplate($requestId, $message, $senderName) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Новое сообщение в чате</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; margin: 0; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h2 { color: #00a896; margin-top: 0; }
            .message { background: #f0fbfb; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 3px solid #00a896; }
            .btn { display: inline-block; padding: 12px 30px; background: #00a896; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; }
            .footer { font-size: 12px; color: #999; text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Новое сообщение в чате</h2>
            <p>Здравствуйте!</p>
            <p><strong>' . htmlspecialchars($senderName) . '</strong> оставил(а) сообщение по заявке №' . $requestId . ':</p>
            <div class="message">' . nl2br(htmlspecialchars($message)) . '</div>
            <p style="text-align: center;">
                <a href="https://' . $_SERVER['HTTP_HOST'] . '/portal/mip/lk_user.php" class="btn">Ответить в чате</a>
            </p>
            <div class="footer">
                Это автоматическое уведомление. Пожалуйста, не отвечайте на него.
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Шаблон письма с подтверждением регистрации
 */
function getVerificationEmailTemplate($name, $verifyLink) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Подтверждение регистрации</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; margin: 0; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h2 { color: #00a896; margin-top: 0; }
            .btn { display: inline-block; padding: 12px 30px; background: #00a896; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; }
            .footer { font-size: 12px; color: #999; text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Подтверждение регистрации</h2>
            <p>Здравствуйте, <strong>' . htmlspecialchars($name) . '</strong>!</p>
            <p>Благодарим вас за регистрацию на сайте <strong>ООО МИП «НПЦ ПИТиА»</strong>.</p>
            <p>Для завершения регистрации активируйте ваш аккаунт, перейдя по ссылке ниже:</p>
            <p style="text-align: center;">
                <a href="' . $verifyLink . '" class="btn">Подтвердить email</a>
            </p>
            <p><strong>Важно:</strong> Ссылка действительна в течение 24 часов.</p>
            <p>Если вы не регистрировались на нашем сайте, просто проигнорируйте это письмо.</p>
            <div class="footer">
                © ' . date('Y') . ' ООО МИП «НПЦ ПИТиА». Все права защищены.
            </div>
        </div>
    </body>
    </html>';
}