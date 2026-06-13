<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'Database.php';
require_once 'UserRepository.php';
require_once 'Auth.php';

$database = new Database();
$userRepo = new UserRepository($database);
$auth = new Auth($userRepo);
$pdo = $database->getPdo();

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Отсутствует токен для восстановления пароля';
} else {
    $stmt = $pdo->prepare("SELECT id, name, email, reset_token_expires FROM user WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $error = 'Неверный или устаревший токен';
    } elseif (strtotime($user['reset_token_expires']) < time()) {
        $error = 'Срок действия ссылки истёк. Запросите восстановление пароля заново.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } else {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE user SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);
            $success = 'Пароль успешно изменён! Теперь вы можете <a href="authorization.php">войти</a>.';
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            $error = 'Ошибка при сохранении пароля';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сброс пароля</title>
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
        .success {
            background: #e6f7ec;
            color: #15803d;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #22c55e;
        }
        .success a {
            color: #00a896;
            font-weight: 600;
            text-decoration: none;
        }
        .success a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Создание нового пароля</h1>
        <p class="subtitle">Придумайте новый пароль</p>
        
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (!$error && !$success && $user): ?>
            <form method="POST">
                <input type="password" name="password" class="input-field" placeholder="Новый пароль" required>
                <input type="password" name="password_confirm" class="input-field" placeholder="Подтвердите пароль" required>
                <button type="submit" class="btn">Сохранить пароль</button>
            </form>
        <?php endif; ?>
        
        <?php if (!$success): ?>
            <div class="back-link">
                <a href="authorization.php">← Вернуться ко входу</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>