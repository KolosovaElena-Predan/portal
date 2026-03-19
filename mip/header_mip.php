<header class="view-6">
    <a href="mip.php">
        <img class="image-3" src="img/logo_mip.png" alt="Логотип МИП" />
    </a>

    <a href="mip.php" class="header-title-link">
        <div class="text-wrapper-36">ООО МИП "НПЦ ПИТиА"</div>
    </a>

    <a href="catalog.php" class="text-wrapper-17">Каталог</a>
    <a href="services_catalog.php" class="text-wrapper-17">Услуги</a>
    <a href="news.php" class="text-wrapper-17">Новости</a>
    <a href="question.php" class="text-wrapper-17">Поддержка</a>
    <a href="contacts.php" class="text-wrapper-17">Поиск</a>
    <a href="cart.php" class="text-wrapper-17">Корзина</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="lk_user.php" class="btn-2" style="text-decoration: none;">Личный кабинет</a>
    <?php else: ?>
        <a href="../authorization.php" class="btn-2" style="text-decoration: none;">Войти</a>
    <?php endif; ?>
</header>