<?php
//session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/UserRepository.php';
require_once __DIR__ . '/Auth.php';

$database = new Database();
$userRepo = new UserRepository($database);
$auth = new Auth($userRepo);

$token = $_GET['token'] ?? '';
$message = '';
$success = false;
$redirectUrl = 'authorization.php';

if ($token) {
    $user = $userRepo->findByVerificationToken($token);
    
    if ($user && $user->isVerified() === false) {
        // Подтверждаем email
        $userRepo->verifyUser($user->id);
        
        $auth->login($user);
        
        $message = "Email успешно подтверждён! Выполняется автоматический вход...";
        $success = true;
        
        // Перенаправляем в личный кабинет 
        $redirectUrl = $user->getDashboardUrl();
        
    } elseif ($user && $user->isVerified() === true) {
        $message = "Email уже был подтверждён ранее. Выполняется автоматический вход...";
        $success = true;
        
        // Автоматический вход для уже подтверждённого пользователя
        $auth->login($user);
        $redirectUrl = $user->getDashboardUrl();
        
    } else {
        $message = "Ссылка недействительна или истекла. Запросите новое письмо.";
        $success = false;
    }
} else {
    $message = "Не указан токен подтверждения.";
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подтверждение email</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #f5f5f5; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .verify-box { 
            background: white; 
            padding: 40px; 
            border-radius: 16px; 
            text-align: center; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
            max-width: 500px; 
        }
        .success { 
            color: #155724; 
            background: #d4edda; 
            padding: 15px; 
            border-radius: 8px; 
        }
        .error { 
            color: #721c24; 
            background: #f8d7da; 
            padding: 15px; 
            border-radius: 8px; 
        }
        .btn { 
            display: inline-block; 
            margin-top: 20px; 
            padding: 10px 20px; 
            background: #1a1982; 
            color: white; 
            text-decoration: none; 
            border-radius: 6px; 
        }
        .btn:hover { 
            background: #14136b; 
        }
        .loader {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #1a1982;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
            vertical-align: middle;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .auto-redirect {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="verify-box">
        <div class="<?= $success ? 'success' : 'error' ?>">
            <?= $message ?>
            <?php if ($success): ?>
                <div class="loader"></div>
            <?php endif; ?>
        </div>
        
        <?php if ($success): ?>
            <div class="auto-redirect">
                Перенаправление в личный кабинет через <span id="countdown">3</span> секунд...
            </div>
            <a href="<?= $redirectUrl ?>" class="btn">Перейти сейчас</a>
            
            <script>
                let seconds = 3;
                const countdownEl = document.getElementById('countdown');
                const interval = setInterval(function() {
                    seconds--;
                    if (countdownEl) countdownEl.textContent = seconds;
                    if (seconds <= 0) {
                        clearInterval(interval);
                        window.location.href = '<?= $redirectUrl ?>';
                    }
                }, 1000);
            </script>
        <?php else: ?>
            <a href="authorization.php" class="btn">Перейти ко входу</a>
            <a href="resend_verify.php" class="btn" style="background: #6c757d; margin-left: 10px;">Запросить новое письмо</a>
        <?php endif; ?>
    </div>
</body>
</html>