<?php
session_start();
require_once '../config.php';
require_once '../Database.php';
require_once '../User.php';
require_once '../UserRepository.php';
require_once '../includes/smtp_config.php'; // <-- ДОБАВЛЯЕМ SMTP

$database = new Database();
$userRepo = new UserRepository($database);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $agree = isset($_POST['agree']);

    // Валидация
    if (!$login || !$email || !$name || !$password) {
        $error = 'Заполните все поля';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен быть не менее 6 символов';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } elseif (!$agree) {
        $error = 'Необходимо согласие на обработку данных';
    } else {
        // Проверка на существование
        if ($userRepo->findByLogin($login)) {
            $error = 'Логин уже занят';
        } elseif ($userRepo->findByEmail($email)) {
            $error = 'Email уже зарегистрирован';
        } else {
            // Генерируем токен подтверждения
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

            // Создаём пользователя
            $userId = $userRepo->create([
                'login' => $login,
                'email' => $email,
                'name' => $name,
                'password' => $password,
                'role' => 'client',
                'is_verified' => 0
            ]);

            if ($userId) {
                // Сохраняем токен
                $userRepo->setVerificationToken($userId, $token, $expires);

                // Формируем ссылку для подтверждения
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $verifyLink = "{$protocol}://{$host}/verify_email.php?token=" . $token;

                $subject = "Подтверждение регистрации на сайте МИП «НПЦ ПИТиА»";
                $message = "
                <html>
                <head><title>Подтверждение email</title></head>
                <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; border-radius: 12px;'>
                        <h2 style='color: #1a1982;'>Здравствуйте, " . htmlspecialchars($name) . "!</h2>
                        <p>Вы зарегистрировались на сайте <strong>ООО МИП «НПЦ ПИТиА»</strong>.</p>
                        <p>Для активации аккаунта перейдите по ссылке ниже:</p>
                        <p style='text-align: center; margin: 30px 0;'>
                            <a href='{$verifyLink}' style='display: inline-block; padding: 12px 30px; background: #1a1982; color: #fff; text-decoration: none; border-radius: 8px;'>
                                Подтвердить email
                            </a>
                        </p>
                        <p style='font-size: 14px; color: #666;'>
                            <strong>Важно:</strong> Ссылка действительна 24 часа.<br>
                            Если вы не регистрировались, просто проигнорируйте это письмо.
                        </p>
                        <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;'>
                        <p style='font-size: 12px; color: #999;'>
                            © " . date('Y') . " ООО МИП «НПЦ ПИТиА». Все права защищены.
                        </p>
                    </div>
                </body>
                </html>
                ";

                // Отправляем через SMTP
                if (sendMailViaSMTP($email, $name, $subject, $message)) {
                    $success = "Регистрация успешна! На email <strong>" . htmlspecialchars($email) . "</strong> отправлено письмо с подтверждением.";
                } else {
                    $error = "Не удалось отправить письмо. Попробуйте позже.";
                    // Откатываем пользователя
                    $pdo = $database->getPdo();
                    $stmt = $pdo->prepare("DELETE FROM user WHERE id = ?");
                    $stmt->execute([$userId]);
                }
            } else {
                $error = 'Ошибка при создании пользователя';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <style>
        .register-container { max-width: 500px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .register-container h2 { text-align: center; color: #1a1982; margin-bottom: 30px; }
        .input-field { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-submit { width: 100%; padding: 12px; background: #1a1982; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; }
        .btn-submit:hover { background: #14136b; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 8px; margin-bottom: 20px; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 8px; margin-bottom: 20px; }
        .login-link { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Регистрация</h2>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
            <div class="login-link"><a href="authorization.php">Перейти ко входу</a></div>
        <?php else: ?>
            <form method="POST">
                <input type="text" name="name" class="input-field" placeholder="Ваше имя" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                <input type="text" name="login" class="input-field" placeholder="Логин" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required>
                <input type="email" name="email" class="input-field" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                <input type="password" name="password" class="input-field" placeholder="Пароль (мин. 6 символов)" required>
                <input type="password" name="password_confirm" class="input-field" placeholder="Повторите пароль" required>
                <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                    <input type="checkbox" name="agree" required> Я согласен с <a href="#">политикой конфиденциальности</a>
                </label>
                <button type="submit" class="btn-submit">Зарегистрироваться</button>
            </form>
            <div class="login-link">Уже есть аккаунт? <a href="authorization.php">Войти</a></div>
        <?php endif; ?>
    </div>
</body>
</html>