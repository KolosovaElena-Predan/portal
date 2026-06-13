<?php
header('Content-Type: application/json');

require_once 'Database.php';
require_once 'UserRepository.php';
require_once 'mip/includes/email_config.php';

$database = new Database();
$userRepo = new UserRepository($database);
$pdo = $database->getPdo();

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Некорректный email']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name, email, is_verified FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
        exit;
    }
    
    if ($user['is_verified']) {
        echo json_encode(['success' => false, 'message' => 'Email уже подтверждён']);
        exit;
    }
    
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    $stmt = $pdo->prepare("UPDATE user SET verification_token = ?, token_expires_at = ? WHERE id = ?");
    $stmt->execute([$token, $expires, $user['id']]);
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $verifyLink = "{$protocol}://{$host}/verify_email.php?token={$token}";
    
    $subject = "Подтверждение регистрации на сайте МИП «НПЦ ПИТиА»";
    $htmlMessage = getVerificationEmailTemplate($user['name'], $verifyLink);
    
    if (sendEmailNotification($user['email'], $user['name'], $subject, $htmlMessage)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка отправки письма']);
    }
    
} catch (Exception $e) {
    error_log("Resend error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка']);
}