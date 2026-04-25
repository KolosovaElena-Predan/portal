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
                    $verifyLink = "{$protocol}://{$host}/portal/verify_email.php?token={$token}";

                    $subject = "Подтверждение регистрации на сайте МИП «НПЦ ПИТиА»";
                    $message = "
                        <html>
                        <head><title>Подтверждение email</title></head>
                        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                                <h2 style='color: #1a1982;'>Здравствуйте, " . htmlspecialchars($reg_name) . "!</h2>
                                <p>Благодарим вас за регистрацию на сайте <strong>ООО МИП «НПЦ ПИТиА»</strong>.</p>
                                <p>Для завершения регистрации yfcnfykb активируйте ваш аккаунт, перейдя по ссылке ниже:</p>
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

                    // ============================================
                    // ОТПРАВКА ЧЕРЕЗ SMTP (Mail.ru)
                    // ============================================
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
    <style>
        /* Вкладки авторизации */
        .auth-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .auth-tab {
            flex: 1;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            font-family: "Inter-Medium", sans-serif;
            font-size: 16px;
            color: #666;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        .auth-tab.active {
            color: #1a1982;
            border-bottom-color: #1a1982;
            font-weight: 600;
        }
        .auth-tab:hover {
            color: #1a1982;
        }
        .auth-form {
            display: none;
        }
        .auth-form.active {
            display: block;
        }
        
        /* Сообщения */
        .error, .success {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
            line-height: 1.5;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .success strong {
            font-weight: 600;
        }
        
        /* Поля ввода */
        .input-field {
            width: 100%;
            padding: 12px 18px;
            margin: 0 0 14px 0;
            border: 1px solid #d0d1e0;
            border-radius: 8px;
            font-family: "Inter-Regular", sans-serif;
            font-size: 15px;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .input-field:focus {
            border-color: #1a1982;
            box-shadow: 0 0 0 3px rgba(26, 25, 130, 0.15);
            outline: none;
        }
        .input-field::placeholder {
            color: #999;
        }
        
        /* Дополнительный отступ справа для формы */
        .login-form {
            padding-right: 4px;
        }
        
        /* Кнопки */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-family: "Inter-Medium", sans-serif;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1a1982 0%, #0d0c4a 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(26, 25, 130, 0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #14136b 0%, #0a093a 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(26, 25, 130, 0.4);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        
        /* Блок согласия с политикой */
        .privacy-check {
            margin: 18px 0 22px;
            padding: 5px 0;
            font-size: 15px;
            font-family: "Inter-Regular", sans-serif;
            color: #333;
            line-height: 1.6;
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
            width: 17px;
            height: 17px;
            margin-top: 2px;
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
            border-bottom: 1px dotted #1a1982;
            font-weight: 500;
        }
        .privacy-check a:hover {
            text-decoration: underline;
            color: #0d0a4d;
        }
        .required-mark {
            color: #dc3545;
            font-weight: 700;
            margin-left: 3px;
        }
        
        /* Адаптивность */
        @media (max-width: 600px) {
            .auth-tabs {
                flex-wrap: wrap;
            }
            .auth-tab {
                font-size: 15px;
                padding: 9px;
            }
            .input-field {
                font-size: 14px;
                padding: 11px 15px;
            }
            .privacy-check {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="screen">
        <div class="div">
            <div class="login-container">
                <div class="login-box" style="height: auto; min-height: 480px; padding-bottom: 25px;">
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
                    <form method="POST" class="login-form auth-form active" id="form-login">
                        <input type="hidden" name="action" value="login">
                        
                        <input type="text" name="login" placeholder="Логин" class="input-field" value="<?= htmlspecialchars($login) ?>" required autocomplete="username" />
                        <input type="password" name="password" placeholder="Пароль" class="input-field" required autocomplete="current-password" />
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Войти</button>
                    </form>

                    <!-- Форма регистрации -->
                    <form method="POST" class="login-form auth-form" id="form-register">
                        <input type="hidden" name="action" value="register">
                        
                        <input type="text" name="reg_name" placeholder="Ваше имя *" class="input-field" value="<?= htmlspecialchars($reg_name) ?>" required autocomplete="name" />
                        <input type="text" name="reg_login" placeholder="Придумайте логин *" class="input-field" value="<?= htmlspecialchars($reg_login) ?>" required autocomplete="username" />
                        <input type="email" name="reg_email" placeholder="Email *" class="input-field" value="<?= htmlspecialchars($reg_email) ?>" required autocomplete="email" />
                        <input type="password" name="reg_password" placeholder="Пароль (мин. 6 символов) *" class="input-field" required autocomplete="new-password" />
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
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Переключение вкладок
        function switchTab(tabName) {
            document.querySelectorAll('.auth-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
            
            if (tabName === 'login') {
                document.querySelector('.auth-tab:nth-child(1)').classList.add('active');
                document.getElementById('form-login').classList.add('active');
            } else {
                document.querySelector('.auth-tab:nth-child(2)').classList.add('active');
                document.getElementById('form-register').classList.add('active');
            }
            
            // Скрываем сообщения при переключении
            const errorDiv = document.querySelector('.error');
            const successDiv = document.querySelector('.success');
            if (errorDiv) errorDiv.style.display = 'none';
            if (successDiv) successDiv.style.display = 'none';
        }
        
        // Автофокус на первом поле при загрузке
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