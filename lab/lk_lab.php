<?php
session_start();
require_once '../config.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: /authorization.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? '';
$user_email = $_SESSION['email'] ?? '';
$user_role = $_SESSION['role'] ?? '';

// Получаем данные пользователя из БД
try {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header('Location: /authorization.php');
        exit;
    }
    
    $user_name = $user['name'];
    $user_email = $user['email'];
    $user_login = $user['login'];
} catch (Exception $e) {
    error_log("Ошибка получения данных пользователя: " . $e->getMessage());
}

// Обработка обновления данных
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['name'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $new_password = $_POST['password'] ?? '';
    $new_password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($new_name) || empty($new_email)) {
        $error = 'Заполните имя и email';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email';
    } else {
        try {
            // Обновляем имя и email
            $stmt = $pdo->prepare("UPDATE user SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$new_name, $new_email, $user_id]);
            
            // Обновляем пароль, если указан
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $error = 'Пароль должен быть не менее 6 символов';
                } elseif ($new_password !== $new_password_confirm) {
                    $error = 'Пароли не совпадают';
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $user_id]);
                }
            }
            
            // Обновляем сессию
            $_SESSION['name'] = $new_name;
            $_SESSION['email'] = $new_email;
            
            if (empty($error)) {
                $message = 'Данные успешно обновлены';
                $user_name = $new_name;
                $user_email = $new_email;
            }
        } catch (Exception $e) {
            $error = 'Ошибка при обновлении данных';
            error_log("Ошибка обновления: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет — Лаборатория ПЭТ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/header_lab.css">
    <link rel="stylesheet" href="../css/footer.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f6f6f6;
            color: #161551;
            line-height: 1.6;
        }
        
        .lk-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 140px 20px 80px;
        }
        
        .lk-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(26, 25, 130, 0.1);
        }
        
        .lk-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .lk-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1a1982, #4a49d9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .lk-avatar i {
            font-size: 40px;
            color: white;
        }
        
        .lk-title {
            font-size: 24px;
            font-weight: 700;
            color: #161551;
            margin-bottom: 10px;
        }
        
        .lk-role {
            display: inline-block;
            padding: 4px 12px;
            background: #e7e8f3;
            border-radius: 20px;
            font-size: 12px;
            color: #1a1982;
        }
        
        .message {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .message-success {
            background: #e8f5e9;
            color: #388e3c;
            border: 1px solid #a5d6a7;
        }
        
        .message-error {
            background: #ffebee;
            color: #d32f2f;
            border: 1px solid #ffcdd2;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #1a1982;
            box-shadow: 0 0 0 3px rgba(26, 25, 130, 0.1);
        }
        
        .form-group input:disabled {
            background: #f5f5f5;
            color: #999;
        }
        
        .btn-save {
            width: 100%;
            padding: 14px;
            background: #1a1982;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-save:hover {
            background: #4a49d9;
            transform: translateY(-2px);
        }
        
        .btn-logout {
            width: 100%;
            padding: 14px;
            background: transparent;
            color: #d32f2f;
            border: 1px solid #d32f2f;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }
        
        .btn-logout:hover {
            background: #d32f2f;
            color: white;
        }
        
        .info-note {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }
        
        hr {
            margin: 25px 0;
            border: none;
            border-top: 1px solid #eee;
        }
        
        @media (max-width: 600px) {
            .lk-container { padding: 120px 15px 60px; }
            .lk-card { padding: 25px; }
            .lk-title { font-size: 20px; }
        }
    </style>
</head>
<body>
    <?php 
    $context = 'lab';
    require_once '../header.php'; 
    ?>
    
    <div class="lk-container">
        <div class="lk-card">
            <div class="lk-header">
                <h1 class="lk-title">Личный кабинет</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="message message-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message message-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Логин</label>
                    <input type="text" value="<?= htmlspecialchars($user_login) ?>" disabled>
                    <div class="info-note">Логин нельзя изменить</div>
                </div>
                
                <div class="form-group">
                    <label>Имя *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user_name) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user_email) ?>" required>
                </div>
                
                <hr>
                
                <div class="form-group">
                    <label>Новый пароль (оставьте пустым, если не хотите менять)</label>
                    <input type="password" name="password" placeholder="Введите новый пароль">
                </div>
                
                <div class="form-group">
                    <label>Подтверждение пароля</label>
                    <input type="password" name="password_confirm" placeholder="Повторите новый пароль">
                </div>
                
                <button type="submit" class="btn-save">
                    Сохранить изменения
                </button>
            </form>
            
            <form method="POST" action="/logout.php" style="margin-top: 15px;">
                <button type="submit" class="btn-logout">
                    Выйти
                </button>
            </form>
        </div>
    </div>
    
    <?php
    require_once '../footer.php';
    ?>
</body>
</html>