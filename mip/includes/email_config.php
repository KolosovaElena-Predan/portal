<?php
// Временная заглушка - отключаем реальную отправку
function sendEmailNotification($toEmail, $toName, $subject, $htmlMessage) {
    // Записываем в лог вместо отправки
    error_log("EMAIL (тест): to={$toEmail}, subject={$subject}");
    return true; // Всегда успешно
}