<?php
session_start();
echo "<!-- DEBUG: user_id = " . ($_SESSION['user_id'] ?? 'NOT SET') . ", role = " . ($_SESSION['role'] ?? 'NOT SET') . " -->";
$context = 'mip';
require_once 'config.php';

// Подключаем шапку
require_once '../header.php';

function getImageUrl($url) {
    if (empty($url)) return 'img/placeholder.png';
    $url = ltrim($url, './');
    return file_exists($url) ? $url : 'img/placeholder.png';
}

// Инициализация переменных по умолчанию
$sliderProducts = [];
$services = [];
$newItems = [];
$popularItems = [];
$news = [];

// Подключаем получение данных
require_once 'includes/get_slider_data.php';
require_once 'includes/get_services_data.php';
require_once 'includes/get_news_data.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style_header_footer.css" />
    <link rel="stylesheet" href="css/style_main.css" />
    <link rel="stylesheet" href="css/style_mip.css" />
    <link rel="stylesheet" href="css/header_mip.css" />
    <link rel="stylesheet" href="css/modals.css" />
    <title>ООО МИП "НПЦ ПИТиА" — Главная</title>
</head>
<body>
    <div class="screen">
        <div class="div">
            
            <!-- Шапка -->
            <?php 
                $context = 'mip';
                require_once '../header.php'; 
            ?>
            
            <!-- Hero блок -->
            <div class="overlap-4">
                <div class="text-wrapper-7">ООО МИП "НПЦ ПИТиА"</div>
                <p class="text-wrapper-6">
                    Малое инновационное предприятие "Научно-производственный центр передовых интеллектуальных технологий и автоматизации"
                </p>
            </div>

            <!-- Слайдер -->
            <section class="view-2">
                <div class="overlap-2">
                    <a href="catalog.php">
                        <h2 class="text-wrapper-2">НАШИ РАЗРАБОТКИ</h2>
                    </a>
                    <div class="slider-wrapper">
                        <button class="btn2 btn2-2" id="prevBtn" type="button">&#10094;</button>
                        <button class="btn2 btn2-1" id="nextBtn" type="button">&#10095;</button>
                        <div class="slider-container" id="productsSlider">
                            <?php if (!empty($sliderProducts)): ?>
                                <?php foreach ($sliderProducts as $index => $prod): ?>
                                    <div class="rectangle-2 slider-slide <?= $index === 0 ? 'active' : '' ?>">
                                        <div class="product-flex-layout">
                                            <div class="product-image-side">
                                                <img src="<?= htmlspecialchars(getImageUrl($prod['img_url'])) ?>"
                                                     alt="<?= htmlspecialchars($prod['name']) ?>"
                                                     loading="lazy">
                                            </div>
                                            <div class="product-content-side">
                                                <h3 class="text-wrapper-5"><?= htmlspecialchars($prod['name']) ?></h3>
                                                <p class="text-wrapper-4">
                                                    <?= htmlspecialchars(mb_strimwidth(strip_tags($prod['description']), 0, 250, '...')) ?>
                                                </p>
                                                <div class="product-price">
													<?php if (isset($prod['stock']) && $prod['stock'] == -1): ?>
														<span style="color: #00a896; font-weight: 700; font-size: 18px;">На заказ</span>
													<?php else: ?>
														от <?= number_format($prod['price'], 0, ',', ' ') ?> ₽
													<?php endif; ?>
												</div>
                                                <a href="product.php?id=<?= (int)$prod['id'] ?>" class="text-wrapper-3">Подробнее</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="text-align:center;padding:50px;">Нет товаров в слайдере</p>
                            <?php endif; ?>
                        </div>
                        <div class="slider-dots" id="sliderDots"></div>
                    </div>
                </div>
            </section>

            <!-- Услуги -->
            <section class="services-section">
                <div class="view">
                    <div class="overlap-group">
                        <a href="services_catalog.php">
                            <h2 class="text-wrapper-2">УСЛУГИ</h2>
                        </a>
                        <div class="services-grid">
                            <?php if (!empty($services)): ?>
                                <?php foreach ($services as $svc): ?>
                                    <article class="service-card">
                                        <img src="<?= htmlspecialchars(getImageUrl($svc['img_url'])) ?>" 
                                             alt="<?= htmlspecialchars($svc['name']) ?>"
                                             class="service-img"
                                             loading="lazy">
                                        <div class="service-info">
                                            <h3 class="service-name"><?= htmlspecialchars($svc['name']) ?></h3>
                                            <?php if (!empty($svc['description'])): ?>
                                                <p style="font-size:18px;color:#555;margin-bottom:10px;">
                                                    <?= htmlspecialchars(mb_strimwidth($svc['description'], 0, 100, '...')) ?>
                                                </p>
                                            <?php endif; ?>
                                            <div class="service-price">от <?= number_format($svc['price'], 2, ',', ' ') ?> ₽</div>
                                            <button class="service-link" 
                                                    data-service-id="<?= (int)$svc['id'] ?>"
                                                    data-service-name="<?= htmlspecialchars($svc['name']) ?>"
                                                    data-service-price="<?= (float)$svc['price'] ?>">
                                                Заказать
                                            </button>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="text-align:center;color:#777;grid-column:1/-1;">Услуги временно недоступны</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Новинки/Популярное + Новости -->
            <section class="split-section">
                <div class="split-left">
                    <div class="split-block">
                        <h3 class="split-title">НОВИНКИ</h3>
                        <div class="product-mini-list">
                            <?php if (!empty($newItems)): ?>
                                <?php foreach ($newItems as $item): ?>
                                    <a href="product.php?id=<?= (int)$item['id'] ?>" class="product-mini-card">
                                        <img src="<?= htmlspecialchars(getImageUrl($item['img_url'])) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>"
                                             loading="lazy">
                                        <span><?= htmlspecialchars($item['name']) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="empty-msg">Новинок пока нет</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="split-block">
                        <h3 class="split-title">ПОПУЛЯРНОЕ</h3>
                        <div class="product-mini-list">
                            <?php if (!empty($popularItems)): ?>
                                <?php foreach ($popularItems as $item): ?>
                                    <a href="product.php?id=<?= (int)$item['id'] ?>" class="product-mini-card">
                                        <img src="<?= htmlspecialchars(getImageUrl($item['img_url'])) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>"
                                             loading="lazy">
                                        <span><?= htmlspecialchars($item['name']) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="empty-msg">Популярных товаров пока нет</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="split-right">
                    <a href="news.php">
                        <h3 class="split-title">НОВОСТИ</h3>
                    </a>
                    <div class="news-mini-list">
                        <?php if (!empty($news)): ?>
                            <?php foreach ($news as $item): ?>
                                <article class="news-mini-card">
                                    <?php if (!empty($item['main_image'])): ?>
                                        <div class="news-img-box">
                                            <img src="<?= htmlspecialchars($item['main_image']) ?>" 
                                                 alt="<?= htmlspecialchars($item['title']) ?>"
                                                 loading="lazy">
                                        </div>
                                    <?php endif; ?>
                                    <h4 class="news-mini-title"><?= htmlspecialchars($item['title']) ?></h4>
                                    <p class="news-mini-excerpt">
                                        <?= htmlspecialchars(mb_strimwidth(strip_tags($item['content']), 0, 120, '...')) ?>
                                    </p>
                                    <a href="news_view.php?id=<?= (int)$item['id'] ?>" class="news-read-more">Читать далее</a>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Подвал -->
            <?php require_once '../footer.php'; ?>
            
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
                <p>У вас уже есть аккаунт или нужно зарегистрироваться?</p>
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
	
	<!-- Модальное окно для авторизации -->
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

<!-- Модальное окно подтверждения заказа услуги -->
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

<!-- Модальное окно для ошибки роли -->
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

    <script src="js/mip.js"></script>
</body>
</html>