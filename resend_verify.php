<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'Database.php';
require_once 'UserRepository.php';
require_once 'mip/includes/email_config.php';

$database = new Database();
$userRepo = new UserRepository($database);
$pdo = $database->getPdo();

$error = '';
$success = '';
$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Введите email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, is_verified FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $error = 'Пользователь с таким email не найден';
            } elseif ($user['is_verified']) {
                $error = 'Этот email уже подтверждён. Можете <a href="authorization.php">войти</a>.';
            } else {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                $stmt = $pdo->prepare("UPDATE user SET verification_token = ?, token_expires_at = ? WHERE id = ?");
                $stmt->execute([$token, $expires, $user['id']]);
                
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $verifyLink = "{$protocol}://{$host}/verify_email.php?token={$token}";
                
                $subject = "Подтверждение регистрации на сайте МИП «НПЦ ПИТиА»";
                $htmlMessage = '
                <!DOCTYPE html>
                <html>
                <head><meta charset="UTF-8"></head>
                <body style="font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px;">
                    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px;">
                        <h2 style="color: #00a896;">Подтверждение регистрации</h2>
                        <p>Здравствуйте, <strong>' . htmlspecialchars($user['name']) . '</strong>!</p>
                        <p>Вы запросили повторную отправку ссылки для подтверждения email.</p>
                        <p style="text-align: center; margin: 30px 0;">
                            <a href="' . $verifyLink . '" style="display: inline-block; padding: 12px 30px; background: #00a896; color: white; text-decoration: none; border-radius: 8px;">Подтвердить email</a>
                        </p>
                        <p style="font-size: 14px; color: #666;">
                            <strong>Важно:</strong> Ссылка действительна в течение 24 часов.<br>
                            Если вы не регистрировались на нашем сайте, просто проигнорируйте это письмо.
                        </p>
                        <hr>
                        <p style="font-size: 12px; color: #999;">© ' . date('Y') . ' ООО МИП «НПЦ ПИТиА».</p>
                    </div>
                </body>
                </html>';
                
                if (sendEmailNotification($user['email'], $user['name'], $subject, $htmlMessage)) {
                    $success = 'Новое письмо отправлено на ' . htmlspecialchars($email) . '. Перейдите по ссылке для подтверждения.';
                    $email = '';
                } else {
                    $error = 'Ошибка отправки письма. Попробуйте позже.';
                }
            }
        } catch (Exception $e) {
            error_log("Resend verify error: " . $e->getMessage());
            $error = 'Произошла ошибка. Попробуйте позже.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Повторная отправка письма</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fc 0%, #eef2f8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 450px;
            width: 100%;
            background: white;
            border-radius: 32px;
            padding: 40px;
            box-shadow: 0 20px 35px -12px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 30px;
        }
        .input-field {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 15px;
            margin-bottom: 20px;
        }
        .input-field:focus {
            border-color: #00a896;
            outline: none;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #00a896 0%, #008a7a 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn:hover {
            background: linear-gradient(135deg, #008a7a 0%, #007a6a 100%);
            transform: translateY(-2px);
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link a:hover {
            color: #00a896;
        }
        .error {
            background: #fee9e6;
            color: #c2410c;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #f97316;
        }
        .error a {
            color: #00a896;
            font-weight: 600;
            text-decoration: none;
        }
        .success {
            background: #e6f7ec;
            color: #15803d;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #22c55e;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Повторная отправка письма</h1>
        <p class="subtitle">Введите email, указанный при регистрации</p>
        
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="email" name="email" class="input-field" placeholder="Email" value="<?= htmlspecialchars($email) ?>" required>
            <button type="submit" class="btn">Отправить письмо</button>
        </form>
        
        <div class="back-link">
            <a href="authorization.php">← Вернуться ко входу</a>
        </div>
    </div>
</body>
</html>