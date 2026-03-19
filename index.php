<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Портал</title>
    <link rel="stylesheet" href="css/style_mainportal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>

    <!-- Шапка -->
    <?php
require_once 'auth_check.php';
$user = getCurrentUserFromSession();
?>

<div class="top-controls">
    <!-- Поиск -->
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Поиск..." id="headerSearch">
    </div>

    <!-- Авторизация -->
    <?php if ($user): ?>
        <!-- Пользователь авторизован -->
        <div class="login-btn">
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
            </div>
                <a href="logout.php" class="logout-link">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
        </div>
    <?php else: ?>
        <!-- Пользователь не авторизован -->
        <a href="authorization.php" class="login-btn">
            <i class="fa-regular fa-user"></i>
            <span>Войти</span>
        </a>
    <?php endif; ?>
</div>

    <!-- Кнопки -->
    <main class="cards-wrapper">

        <!-- Кнопка 1. Лаборатория -->
        <a href="lab/main_lab.php" class="card">
            <div class="card-icon">
                <img src="img/icon_lab.png" alt="Лаборатория" class="custom-icon">
            </div>
            <div class="card-text">
                <h2 class="card-title">Лаборатория перспективных энергетических технологий</h2>
                <p class="card-desc">Научные исследования и разработки</p>
            </div>
        </a>

        <!-- Кнопка 2. МИП -->
        <a href="mip/mip.php" class="card">
            <div class="card-icon">
                <img src="img/icon_mip.png" alt="МИП" class="custom-icon">
            </div>
            <div class="card-text">
                <h2 class="card-title">ООО МИП "НПЦ ПИТиА"</h2>
                <p class="card-desc">Малое инновационное предприятие</p>
            </div>
        </a>

        <!-- Кнопка 3. Мониторинг 
        <a href="lab/main_lab.php" class="card">
            <div class="card-icon">
                <img src="img/icon_monitoring.png" alt="Мониторинг" class="custom-icon">
            </div>
            <div class="card-text">
                <h2 class="card-title">Мониторинг</h2>
            </div>
        </a>-->

    </main>

</body>
</html>