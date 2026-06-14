<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Аккаунт заблокирован</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .container { max-width: 500px; margin: 0 auto; background: #fff5f5; padding: 30px; border-radius: 10px; border: 1px solid #dc3545; }
        h1 { color: #dc3545; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-ban"></i> Аккаунт заблокирован</h1>
        <p>Ваш аккаунт был заблокирован администратором.</p>
        <p>Если вы считаете, что это ошибка, свяжитесь с поддержкой.</p>
        <a href="/" class="btn">Вернуться на главную</a>
    </div>
</body>
</html>