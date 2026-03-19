<?php
require_once 'Database.php';
require_once 'User.php';
require_once 'UserRepository.php';
require_once 'Auth.php';

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
        $auth->login($user);
        header("Location: " . $user->getDashboardUrl());
        exit;
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
                // Создаём пользователя
                $newUser = $userRepo->create([
                    'login' => $reg_login,
                    'email' => $reg_email,
                    'name' => $reg_name,
                    'password' => $reg_password,
                    'role' => 'client' // По умолчанию клиент
                ]);

                if ($newUser) {
                    $user = $auth->attempt($reg_login, $reg_password);
                    if ($user && !($user instanceof GuestUser)) {
                        $auth->login($user);
                        header("Location: " . $user->getDashboardUrl());
                        exit;
                    }
                } else {
                    $error = 'Ошибка активации аккаунта';
                }
            }
        } catch (Exception $e) {
            error_log("Ошибка регистрации: " . $e->getMessage());
            $error = 'Произошла ошибка при регистрации';
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
        .auth-tabs {
            display: flex;
            margin-bottom: 25px;
            border-bottom: 2px solid #e0e0e0;
        }
        .auth-tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            font-family: "Inter-Medium", sans-serif;
            font-size: 18px;
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
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            font-size: 14px;
        }
        .privacy-check {
            margin: 15px 0;
            font-size: 13px;
            color: #555;
            line-height: 1.4;
        }
        .privacy-check label {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
            user-select: none;
        }
        .privacy-check input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-top: 2px;
            flex-shrink: 0;
            cursor: pointer;
        }
        .privacy-check a {
            color: #1a1982;
            text-decoration: none;
            border-bottom: 1px dotted #1a1982;
        }
        .privacy-check a:hover {
            text-decoration: underline;
            color: #0d0a4d;
        }
        .required-mark {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="screen">
        <div class="div">
            <div class="login-container">
                <div class="login-box" style="height: auto; min-height: 600px; padding-bottom: 30px;">
                    <h2 class="login-title">Личный кабинет</h2>

                    <!-- Сообщения об ошибках/успехе -->
                    <?php if ($error): ?>
                        <div class="error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <!-- Вкладки -->
                    <div class="auth-tabs">
                        <div class="auth-tab active" onclick="switchTab('login')">Вход</div>
                        <div class="auth-tab" onclick="switchTab('register')">Регистрация</div>
                    </div>

                    <!-- Форма входа -->
                    <form method="POST" class="login-form auth-form active" id="form-login">
                        <input type="hidden" name="action" value="login">
                        
                        <input type="text" name="login" placeholder="Логин" class="input-field" value="<?= htmlspecialchars($login) ?>" required />
                        <input type="password" name="password" placeholder="Пароль" class="input-field" required />
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Войти</button>
                    </form>

                    <!-- Форма регистрации -->
                    <form method="POST" class="login-form auth-form" id="form-register">
                        <input type="hidden" name="action" value="register">
                        
                        <input type="text" name="reg_name" placeholder="Ваше имя *" class="input-field" value="<?= htmlspecialchars($reg_name) ?>" required />
                        <input type="text" name="reg_login" placeholder="Придумайте логин *" class="input-field" value="<?= htmlspecialchars($reg_login) ?>" required />
                        <input type="email" name="reg_email" placeholder="Email *" class="input-field" value="<?= htmlspecialchars($reg_email) ?>" required />
                        <input type="password" name="reg_password" placeholder="Пароль (мин. 6 символов) *" class="input-field" required />
                        <input type="password" name="reg_password_confirm" placeholder="Повторите пароль *" class="input-field" required />
                        
                        <!-- === ГАЛОЧКА СОГЛАСИЯ === -->
                        <div class="privacy-check">
                            <label>
                                <input type="checkbox" name="privacy_accepted" value="1" required>
                                <span>
                                    Я согласен на обработку 
                                    <a href="privacy.php" target="_blank">персональных данных</a> 
                                    и принимаю 
                                    <a href="policy.php" target="_blank">политику конфиденциальности</a>
                                    <span class="required-mark">*</span>
                                </span>
                            </label>
                        </div>
                        <!-- === КОНЕЦ ГАЛОЧКИ === -->
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Зарегистрироваться</button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
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
            
            const errorDiv = document.querySelector('.error');
            const successDiv = document.querySelector('.success');
            if (errorDiv) errorDiv.style.display = 'none';
            if (successDiv) successDiv.style.display = 'none';
        }
    </script>
</body>
</html>