<?php
/**
 * email_config.php - Для РЕАЛЬНОГО СЕРВЕРА (ручная установка)
 */

// Подключаем PHPMailer вручную (без Composer)
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Отправка email-уведомления через SMTP
 */
function sendEmailNotification($toEmail, $toName, $subject, $htmlMessage) {
    try {
        $mail = new PHPMailer(true);
        
        // Настройки SMTP (Mail.ru)
        $mail->isSMTP();
        $mail->Host       = 'smtp.mail.ru';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'elena.kolosova.04@mail.ru';
        $mail->Password   = 'a6G2nmXT0Y8sRTPTwCu0'; // ПАРОЛЬ ПРИЛОЖЕНИЯ
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

// Остальные функции-шаблоны (те же, что в предыдущем ответе)
function getVerificationEmailTemplate($name, $verifyLink) {
    return '<!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px;">
        <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px;">
            <h2 style="color: #00a896;">Подтверждение регистрации</h2>
            <p>Здравствуйте, <strong>' . htmlspecialchars($name) . '</strong>!</p>
            <p style="text-align: center;">
                <a href="' . $verifyLink . '" style="display: inline-block; padding: 12px 30px; background: #00a896; color: white; text-decoration: none; border-radius: 8px;">Подтвердить email</a>
            </p>
            <p>Ссылка действительна 24 часа.</p>
            <hr>
            <p style="font-size: 12px; color: #666;">Если вы не регистрировались, проигнорируйте это письмо.</p>
        </div>
    </body>
    </html>';
}

function getProductAvailableEmailTemplate($productName, $productUrl, $requestedQuantity = null, $productImage = null) {
    $quantityText = $requestedQuantity ? " (запрошено: {$requestedQuantity} шт.)" : "";
    return '<!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px;">
        <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px;">
            <h2 style="color: #00a896;">Товар поступил в наличие!</h2>
            <p>Товар <strong>' . htmlspecialchars($productName) . '</strong>' . $quantityText . ' снова доступен для заказа.</p>
            <p style="text-align: center;">
                <a href="' . $productUrl . '" style="display: inline-block; padding: 12px 30px; background: #00a896; color: white; text-decoration: none; border-radius: 8px;">Перейти к товару</a>
            </p>
        </div>
    </body>
    </html>';
}

function getOrderStatusEmailTemplate($orderId, $statusText, $statusComment = null) {
    return '<!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px;">
        <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px;">
            <h2 style="color: #00a896;">Статус заказа изменён</h2>
            <p>Статус вашего заказа №<strong>' . $orderId . '</strong> изменён на: <strong>' . htmlspecialchars($statusText) . '</strong></p>
            ' . ($statusComment ? '<p>Комментарий: ' . nl2br(htmlspecialchars($statusComment)) . '</p>' : '') . '
            <p style="text-align: center;">
                <a href="https://' . $_SERVER['HTTP_HOST'] . '/portal/mip/lk_user.php" style="display: inline-block; padding: 12px 30px; background: #00a896; color: white; text-decoration: none; border-radius: 8px;">Перейти в личный кабинет</a>
            </p>
        </div>
    </body>
    </html>';
}

function getNewMessageEmailTemplate($requestId, $message, $senderName) {
    return '<!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px;">
        <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px;">
            <h2 style="color: #00a896;">Новое сообщение в чате</h2>
            <p><strong>' . htmlspecialchars($senderName) . '</strong> оставил(а) сообщение по заявке №' . $requestId . ':</p>
            <div style="background: #f0fbfb; padding: 15px; border-left: 3px solid #00a896;">' . nl2br(htmlspecialchars($message)) . '</div>
            <p style="text-align: center;">
                <a href="https://' . $_SERVER['HTTP_HOST'] . '/portal/mip/lk_user.php" style="display: inline-block; padding: 12px 30px; background: #00a896; color: white; text-decoration: none; border-radius: 8px;">Ответить в чате</a>
            </p>
        </div>
    </body>
    </html>';
}