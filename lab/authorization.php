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
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="css/main_lab.css" />
    <link rel="stylesheet" href="css/header_lab.css" />
    <link rel="stylesheet" href="css/footer.css" />
    <title>Вход в личный кабинет</title>
</head>
<body>
    <div class="screen">
        <div class="div">
            <?php require_once 'header.php'; ?>

            <div class="login-container">
                <div class="login-box">
                    <h2 class="login-title">Вход в личный кабинет</h2>

                    <?php if ($error): ?>
                        <div class="error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="login-form">
                        <input
                            type="text"
                            name="login"
                            placeholder="Логин"
                            class="input-field"
                            value="<?= htmlspecialchars($login ?? '') ?>"
                            required
                        />
                        <input
                            type="password"
                            name="password"
                            placeholder="Пароль"
                            class="input-field"
                            required
                        />
                        <button type="submit" class="btn btn-primary">Войти</button>
                    </form>
                </div>
            </div>
        </div>
        <!--<?php require_once 'footer.php'; ?>-->
    </div>
</body>
</html>