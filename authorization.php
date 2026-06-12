<?php
require_once 'Database.php';
require_once 'User.php';
require_once 'UserRepository.php';
require_once 'Auth.php';
require_once 'includes/smtp_config.php'; // ДОБАВЛЯЕМ SMTP

// Создаём зависимости
$database = new Database();
$userRepo = new UserRepository($database);
$auth = new Auth($userRepo);

$error = '';
$success = '';
$login = '';
$reg_login = '';
$reg_email = '';
$reg_name = '';

// Обработка входа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = $auth->attempt($login, $password);
    if ($user && !($user instanceof GuestUser)) {
        if (!$user->isVerified()) {
            $error = 'Подтвердите email перед входом. Проверьте почту или <a href="resend_verify.php">запросите письмо повторно</a>.';
        } else {
            $auth->login($user);
            header("Location: " . $user->getDashboardUrl());
            exit;
        }
    } else {
        $error = 'Неверный логин или пароль';
    }
}

// Обработка регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $reg_login = trim($_POST['reg_login'] ?? '');
    $reg_email = trim($_POST['reg_email'] ?? '');
    $reg_name = trim($_POST['reg_name'] ?? '');
    $reg_password = $_POST['reg_password'] ?? '';
    $reg_password_confirm = $_POST['reg_password_confirm'] ?? '';
    $privacy_accepted = isset($_POST['privacy_accepted']) ? 1 : 0;

    // Валидация
    if (!$reg_login || !$reg_email || !$reg_name || !$reg_password) {
        $error = 'Заполните все поля регистрации';
    } elseif (!filter_var($reg_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email';
    } elseif ($reg_password !== $reg_password_confirm) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($reg_password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } elseif (!$privacy_accepted) {
        $error = 'Необходимо согласие с политикой конфиденциальности';
    } else {
        try {
            // Проверка на существование
            if ($userRepo->findByLogin($reg_login)) {
                $error = 'Пользователь с таким логином уже существует';
            } elseif ($userRepo->findByEmail($reg_email)) {
                $error = 'Пользователь с таким email уже существует';
            } else {
                // Генерируем токен подтверждения
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

                // Создаём пользователя
                $newUserId = $userRepo->create([
                    'login' => $reg_login,
                    'email' => $reg_email,
                    'name' => $reg_name,
                    'password' => $reg_password,
                    'role' => 'client',
                    'is_verified' => 0
                ]);

                if ($newUserId) {
                    // Сохраняем токен в БД
                    $userRepo->setVerificationToken($newUserId, $token, $expires);

                    // Формируем ссылку для подтверждения
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'];
                    $verifyLink = "{$protocol}://{$host}/verify_email.php?token={$token}";

                    $subject = "Подтверждение регистрации на сайте МИП «НПЦ ПИТиА»";
                    $message = "
                        <html>
                        <head><title>Подтверждение email</title></head>
                        <body style='font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333;'>
                            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                                <h2 style='color: #1a1982;'>Здравствуйте, " . htmlspecialchars($reg_name) . "!</h2>
                                <p>Благодарим вас за регистрацию на сайте <strong>ООО МИП «НПЦ ПИТиА»</strong>.</p>
                                <p>Для завершения регистрации активируйте ваш аккаунт, перейдя по ссылке ниже:</p>
                                <p style='text-align: center; margin: 30px 0;'>
                                    <a href='{$verifyLink}' style='display: inline-block; padding: 12px 30px; background: #1a1982; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;'>
                                        Подтвердить адрес электронной почты
                                    </a>
                                </p>
                                <p style='font-size: 14px; color: #666;'>
                                    <strong>Важно:</strong> Ссылка действительна в течение 24 часов.<br>
                                    Если вы не регистрировались на нашем сайте, просто проигнорируйте это письмо.
                                </p>
                                <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;'>
                                <p style='font-size: 12px; color: #999;'>
                                    Это автоматическое сообщение, пожалуйста, не отвечайте на него.<br>
                                    © " . date('Y') . " ООО МИП «НПЦ ПИТиА». Все права защищены.
                                </p>
                            </div>
                        </body>
                        </html>
                    ";

                    if (sendMailViaSMTP($reg_email, $reg_name, $subject, $message)) {
                        $success = "Регистрация успешна! На почту <strong>" . htmlspecialchars($reg_email) . "</strong> отправлено письмо с подтверждением. Перейдите по ссылке в письме для активации аккаунта.";
                        $reg_login = $reg_email = $reg_name = '';
                    } else {
                        $error = "Ошибка отправки письма подтверждения. Попробуйте позже или обратитесь в поддержку.";
                        // При ошибке отправки — удаляем пользователя
                        $pdo = $database->getPdo();
                        $stmt = $pdo->prepare("DELETE FROM user WHERE id = ?");
                        $stmt->execute([$newUserId]);
                    }
                } else {
                    $error = 'Ошибка при создании пользователя';
                }
            }
        } catch (Exception $e) {
            error_log("Ошибка регистрации: " . $e->getMessage());
            $error = 'Произошла ошибка при регистрации. Попробуйте позже.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="mip/css/style_auth.css" />
    <link rel="stylesheet" href="mip/css/style_mip.css" />
    <title>Вход и регистрация</title>
    <!-- Подключение современного шрифта Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fc 0%, #eef2f8 100%);
            min-height: 100vh;
        }

        /* Вкладки авторизации */
        .auth-tabs {
            display: flex;
            margin-bottom: 28px;
            border-bottom: 2px solid #eef2f5;
        }
        .auth-tab {
            flex: 1;
            padding: 14px 10px 12px;
            text-align: center;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 16px;
            letter-spacing: -0.2px;
            color: #8a94a6;
            transition: all 0.25s ease;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        .auth-tab.active {
            color: #1a1982;
            border-bottom-color: #1a1982;
            font-weight: 600;
        }
        .auth-tab:hover:not(.active) {
            color: #4a4f62;
            border-bottom-color: #d0d5e0;
        }
        
        .auth-form {
            display: none;
        }
        .auth-form.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Сообщения */
        .error, .success {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 22px;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.5;
            letter-spacing: -0.2px;
        }
        .error {
            background-color: #fee9e6;
            color: #c2410c;
            border-left: 4px solid #f97316;
        }
        .error a {
            color: #1a1982;
            text-decoration: none;
            font-weight: 600;
        }
        .error a:hover {
            text-decoration: underline;
        }
        .success {
            background-color: #e6f7ec;
            color: #15803d;
            border-left: 4px solid #22c55e;
        }
        .success strong {
            font-weight: 700;
        }
        
        /* Поля ввода */
        .input-field {
            width: 100%;
            padding: 14px 18px;
            margin: 0 0 16px 0;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            font-size: 15px;
            letter-spacing: -0.2px;
            color: #1e293b;
            background: #ffffff;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }
        .input-field::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }
        .input-field:focus {
            border-color: #1a1982;
            box-shadow: 0 0 0 4px rgba(26, 25, 130, 0.1);
            outline: none;
        }
        
        /* Кнопки */
        .btn {
            padding: 14px 24px;
            border: none;
            border-radius: 14px;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: -0.2px;
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1a1982 0%, #12115f 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(26, 25, 130, 0.25);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #12115f 0%, #0c0b48 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 25, 130, 0.3);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        
        /* Блок согласия с политикой */
        .privacy-check {
            margin: 20px 0 24px;
            padding: 6px 0;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            letter-spacing: -0.2px;
            color: #334155;
            line-height: 1.5;
            background: transparent;
            border: none;
        }
        .privacy-check label {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            cursor: pointer;
            user-select: none;
        }
        .privacy-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-top: 1px;
            flex-shrink: 0;
            cursor: pointer;
            accent-color: #1a1982;
        }
        .privacy-check span {
            display: inline;
        }
        .privacy-check a {
            color: #1a1982;
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px dashed #1a1982;
        }
        .privacy-check a:hover {
            border-bottom-style: solid;
            color: #0d0c4a;
        }
        .required-mark {
            color: #e11d48;
            font-weight: 600;
            margin-left: 2px;
        }
        
        /* Ссылки под формой */
        .auth-links {
            text-align: center;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #eef2f5;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: -0.2px;
        }
        .auth-links a {
            color: #1a1982;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .auth-links a:hover {
            text-decoration: underline;
            color: #0d0c4a;
        }
        .auth-links .separator {
            margin: 0 12px;
            color: #cbd5e1;
        }
        .forgot-password {
            margin-top: -8px;
            margin-bottom: 16px;
            text-align: right;
            font-size: 13px;
            font-weight: 500;
        }
        .forgot-password a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s;
            letter-spacing: -0.2px;
        }
        .forgot-password a:hover {
            color: #1a1982;
            text-decoration: underline;
        }
        
        /* Контейнер и карточка */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-box {
            background: white;
            border-radius: 32px;
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 460px;
            padding: 32px 28px;
            transition: all 0.3s ease;
        }
        .login-title {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 28px;
            letter-spacing: -0.5px;
            color: #0f172a;
            text-align: center;
            margin-bottom: 28px;
        }
        
        /* Адаптивность */
        @media (max-width: 520px) {
            .login-box {
                padding: 24px 20px;
                border-radius: 28px;
            }
            .login-title {
                font-size: 24px;
                margin-bottom: 22px;
            }
            .auth-tab {
                font-size: 15px;
                padding: 11px 8px 10px;
            }
            .input-field {
                font-size: 14px;
                padding: 12px 16px;
            }
            .btn {
                font-size: 14px;
                padding: 12px 20px;
            }
            .privacy-check {
                font-size: 13px;
            }
            .auth-links {
                font-size: 13px;
            }
            .forgot-password {
                font-size: 12px;
            }
        }
        
        @media (max-width: 400px) {
            .login-box {
                padding: 20px 16px;
            }
            .login-title {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2 class="login-title">Личный кабинет</h2>

            <!-- Сообщения об ошибках/успехе -->
            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?= $success ?></div>
            <?php endif; ?>

            <!-- Вкладки -->
            <div class="auth-tabs">
                <div class="auth-tab active" onclick="switchTab('login')">Вход</div>
                <div class="auth-tab" onclick="switchTab('register')">Регистрация</div>
            </div>

            <!-- Форма входа -->
            <form method="POST" class="auth-form active" id="form-login">
                <input type="hidden" name="action" value="login">
                
                <input type="text" name="login" placeholder="Логин" class="input-field" value="<?= htmlspecialchars($login) ?>" required autocomplete="username" />
                <input type="password" name="password" placeholder="Пароль" class="input-field" required autocomplete="current-password" />
                
                <div class="forgot-password">
                    <a href="forgot_password.php">Забыли пароль?</a>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Войти</button>
                
                <div class="auth-links">
                    <a href="#" onclick="switchTab('register'); return false;">Нет аккаунта? Зарегистрироваться</a>
                </div>
            </form>

            <!-- Форма регистрации -->
            <form method="POST" class="auth-form" id="form-register">
                <input type="hidden" name="action" value="register">
                
                <input type="text" name="reg_name" placeholder="Ваше имя *" class="input-field" value="<?= htmlspecialchars($reg_name) ?>" required autocomplete="name" />
                <input type="text" name="reg_login" placeholder="Придумайте логин *" class="input-field" value="<?= htmlspecialchars($reg_login) ?>" required autocomplete="username" />
                <input type="email" name="reg_email" placeholder="Email *" class="input-field" value="<?= htmlspecialchars($reg_email) ?>" required autocomplete="email" />
                <input type="password" name="reg_password" placeholder="Пароль *" class="input-field" required autocomplete="new-password" />
                <input type="password" name="reg_password_confirm" placeholder="Повторите пароль *" class="input-field" required autocomplete="new-password" />
                
                <div class="privacy-check">
                    <label>
                        <input type="checkbox" name="privacy_accepted" value="1" required>
                        <span>
                            Я даю согласие на обработку 
                            <a href="privacy.php" target="_blank">персональных данных</a>, 
                            принимаю 
                            <a href="policy.php" target="_blank">политику конфиденциальности</a> 
                            и условия использования сервиса
                            <span class="required-mark">*</span>
                        </span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Зарегистрироваться</button>
                
                <div class="auth-links">
                    <a href="#" onclick="switchTab('login'); return false;">Уже есть аккаунт? Войти</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.auth-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
            
            if (tabName === 'login') {
                document.querySelector('.auth-tab:first-child').classList.add('active');
                document.getElementById('form-login').classList.add('active');
            } else {
                document.querySelector('.auth-tab:last-child').classList.add('active');
                document.getElementById('form-register').classList.add('active');
            }
            
            const errorDiv = document.querySelector('.error');
            const successDiv = document.querySelector('.success');
            if (errorDiv) errorDiv.style.display = 'none';
            if (successDiv) successDiv.style.display = 'none';
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const activeForm = document.querySelector('.auth-form.active');
            if (activeForm) {
                const firstInput = activeForm.querySelector('input:not([type="hidden"])');
                if (firstInput) firstInput.focus();
            }
        });
    </script>
</body>
</html>