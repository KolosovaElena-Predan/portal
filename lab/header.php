<header class="view-6">
    <a href="main_lab.php">
        <img class="image-3" src="img/image.png" alt="Логотип" />
    </a>

    <a href="main_lab.php" class="header-title-link">
        <div class="text-wrapper-36">Лаборатория перспективных энергетических технологий</div>
    </a>

    <!-- Пункт меню "О нас" с выпадающим списком -->
     <!-- Пункт меню "О нас" с выпадающим списком -->
        <div class="menu-item dropdown">
            <a href="about.php" class="text-wrapper-17">
                О нас
                <i class="dropdown-arrow">▼</i>
            </a>
            <div class="dropdown-menu">
                <a href="about.php#directions">Направления работы</a>
                <a href="about.php#equipment">Используемое оборудование</a>
                <a href="about.php#team">Команда</a>
                <a href="about.php#education">Образовательная деятельность</a>
            </div>
        </div>
    <a href="projects.php" class="text-wrapper-17">Проекты</a>
    <a href="services.php" class="text-wrapper-17">Услуги</a>
    <a href="news.php" class="text-wrapper-17">Новости</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="question.php" class="text-wrapper-17">Поддержка</a>
    <?php else: ?>
        <a href="question.php" class="text-wrapper-17">Поддержка</a>
    <?php endif; ?>

    <button class="btn btn-2" onclick="location.href='check_auth.php'">
        <i class="fa fa-user"></i>
        Личный кабинет
    </button>
</header>