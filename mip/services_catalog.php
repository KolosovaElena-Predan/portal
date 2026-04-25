<?php
session_start();
require_once 'config.php';

// Подключаем получение данных
require_once 'includes/get_services.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style_header_footer.css" />
    <link rel="stylesheet" href="css/style_mip.css" />
    <link rel="stylesheet" href="css/style_main.css" />
    <link rel="stylesheet" href="css/style_catalog2.css" />
    <link rel="stylesheet" href="css/header_mip.css" />
    <link rel="stylesheet" href="css/modals.css" />
    <title>Каталог услуг</title>
</head>
<body>
    <div class="screen">
        <div class="div">
            <?php 
                $context = 'mip';
                require_once '../header.php'; 
            ?>

            <div class="catalog-content">
                <h1 class="catalog-title">Каталог услуг</h1>

                <form method="GET" class="search-container" id="searchForm">
                    <input type="text" class="input-field" name="search" id="searchInput"
                           placeholder="Поиск услуги..." value="<?= htmlspecialchars($searchQuery) ?>" autocomplete="off" />
                    <button type="submit" class="btn-search">Найти</button>
                    <?php if (!empty($searchQuery)): ?>
                        <a href="services_catalog.php" class="btn-search" style="background:#6c757d;width:100px;">Сброс</a>
                    <?php endif; ?>
                </form>

                <div class="catalog-wrapper">
                    <?php if (empty($services)): ?>
                        <p class="no-devices">
                            <?= !empty($searchQuery) ? 'Ничего не найдено по запросу "' . htmlspecialchars($searchQuery) . '"' : 'Услуги временно недоступны.' ?>
                        </p>
                    <?php else: ?>
                        <?php foreach ($services as $service): ?>
                        <div class="service-card">
                            <div class="service-image-wrap">
                                <img class="service-image"
                                     src="<?= htmlspecialchars($service['img_url'] ?: 'img/placeholder.jpg') ?>"
                                     alt="<?= htmlspecialchars($service['name']) ?>"
                                     onerror="this.src='img/placeholder.jpg'">
                            </div>
                            <div class="service-content">
                                <h3 class="service-name"><?= htmlspecialchars($service['name']) ?></h3>
                                <p class="service-desc"><?= htmlspecialchars(mb_strimwidth(strip_tags($service['short_description']), 0, 120, '...')) ?></p>
                                <?php if (!empty($service['duration'])): ?>
                                    <div class="service-duration"><?= htmlspecialchars($service['duration']) ?></div>
                                <?php endif; ?>
                                <div class="service-price"><?= number_format($service['price'], 2, ',', ' ') ?> ₽</div>
                                
                                <button class="service-order-btn" 
                                        data-service-id="<?= $service['id'] ?>"
                                        data-service-name="<?= htmlspecialchars($service['name']) ?>"
                                        data-service-price="<?= $service['price'] ?>">
                                    Заказать
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Модальные окна -->
            <div id="authModal" class="auth-modal">
                <div class="auth-modal-content">
                    <div class="auth-modal-header">
                        <h3>Требуется авторизация</h3>
                        <button class="auth-modal-close" onclick="closeAuthModal()">&times;</button>
                    </div>
                    <div class="auth-modal-body">
                        <p>Для заказа услуги необходимо войти в личный кабинет.</p>
                    </div>
                    <div class="auth-modal-footer">
                        <a href="../authorization.php" class="btn-auth btn-login-page">Войти</a>
                        <a href="../authorization.php#register" class="btn-auth btn-register-page">Зарегистрироваться</a>
                        <button class="btn-auth btn-cancel" onclick="closeAuthModal()">Отмена</button>
                    </div>
                </div>
            </div>

            <div id="serviceOrderModal" class="service-modal">
                <div class="service-modal-content">
                    <div class="service-modal-header">
                        <h3>Подтверждение заказа</h3>
                        <button class="service-modal-close" onclick="closeServiceModal()">&times;</button>
                    </div>
                    <div class="service-modal-body">
                        <div class="service-info-block">
                            <span class="service-info-label">Услуга:</span>
                            <span class="service-info-value" id="modalServiceName"></span>
                        </div>
                        <div class="service-info-block">
                            <span class="service-info-label">Стоимость:</span>
                            <span class="service-info-value" id="modalServicePrice"></span>
                        </div>
                        <div class="service-description">
                            <p>Услуга будет добавлена в ваш личный кабинет.</p>
                            <p>После подтверждения заказа с вами свяжется специалист.</p>
                        </div>
                    </div>
                    <div class="service-modal-footer">
                        <button class="btn-service btn-service-confirm" id="confirmOrderBtn">Подтвердить заказ</button>
                        <button class="btn-service btn-service-cancel" onclick="closeServiceModal()">Отмена</button>
                    </div>
                </div>
            </div>

            <div id="roleErrorModal" class="role-modal">
                <div class="role-modal-content">
                    <div class="role-modal-header">
                        <h3>Доступ запрещён</h3>
                        <button class="role-modal-close" onclick="closeRoleErrorModal()">&times;</button>
                    </div>
                    <div class="role-modal-body">
                        <p>Заказ услуг доступен только клиентам.</p>
                        <p>Ваша роль: <strong id="userRole"></strong></p>
                    </div>
                    <div class="role-modal-footer">
                        <button class="btn-role btn-role-logout" onclick="logoutAndRedirect()">Выйти</button>
                        <button class="btn-role btn-role-cancel" onclick="closeRoleErrorModal()">Закрыть</button>
                    </div>
                </div>
            </div>

            <?php
            if (!isset($context)) {
                $context = 'lab';
            }
            require_once '../footer.php';
            ?>
        </div>
    </div>

    <script src="js/services.js"></script>
</body>
</html>