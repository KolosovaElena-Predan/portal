<?php
session_start();
require_once 'config.php';

function getImageUrl($url) {
    if (empty($url)) return 'img/placeholder.png';
    $url = ltrim($url, './');
    return file_exists($url) ? $url : 'img/placeholder.png';
}


try {
    $stmt = $pdo->prepare("
        SELECT 
            p.id, 
            p.name, 
            p.short_description as description, 
            p.base_price as price,
            p.stock,
            pi.image_url as img_url
        FROM products p
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        WHERE p.is_slider = 1 
            AND p.status = 'active'
        ORDER BY p.sort_order ASC, p.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $sliderProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $sliderProducts = [];
    error_log("Slider error: " . $e->getMessage());
}


try {
    $stmt = $pdo->prepare("
        SELECT id, name, short_description as description, price, img_url 
        FROM services 
        WHERE is_active = 1 
        ORDER BY sort_order ASC, id DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $services = [];
    error_log("Services error: " . $e->getMessage());
}


try {
    $stmt = $pdo->prepare("
        SELECT 
            p.id, 
            p.name, 
            pi.image_url as img_url,
            p.base_price as price
        FROM products p
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        WHERE p.is_new = 1 
            AND p.status = 'active'
        ORDER BY p.created_at DESC 
        LIMIT 2
    ");
    $stmt->execute();
    $newItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $newItems = [];
}


try {
    $stmt = $pdo->prepare("
        SELECT 
            p.id, 
            p.name, 
            pi.image_url as img_url,
            p.base_price as price,
            p.orders_count
        FROM products p
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        WHERE p.status = 'active'
        ORDER BY p.orders_count DESC, p.views_count DESC 
        LIMIT 2
    ");
    $stmt->execute();
    $popularItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $popularItems = [];
}


try {
    $stmt = $pdo->prepare("
        SELECT 
            n.id, 
            n.title, 
            n.content, 
            n.datetime,
            (SELECT image_url FROM news_images WHERE news_id = n.id ORDER BY is_main DESC, sort_order LIMIT 1) as main_image
        FROM news n
        ORDER BY n.datetime DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $news = [];
    error_log("News error: " . $e->getMessage());
}


$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
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
    <title>ООО МИП "НПЦ ПИТиА" — Главная</title>
</head>
<body>
    <div class="screen">
        <div class="div">
            
            <!-- Шапка -->
            <?php require_once 'header_mip.php'; ?>
            <div class="overlap-4">
			<div class="text-wrapper-7">ООО МИП "НПЦ ПИТиА"</div>
            <p class="text-wrapper-6">
                Малое инновационное предприятие "Научно-производственный центр передовых интеллектуальных технологий и автоматизации"
            </p>
            
            </div>

            <!--  НАШИ РАЗРАБОТКИ (Слайдер) -->
            <section class="view-2">
                <div class="overlap-2">
                    <a href="catalog.php">
                    <h2 class="text-wrapper-2">НАШИ РАЗРАБОТКИ</h2>
                    </a>
                    <div class="slider-wrapper">
                        <button class="btn2 btn2-2" id="prevBtn" type="button">&#10094;</button>
                        <button class="btn2 btn2-1" id="nextBtn" type="button">&#10095;</button>
    
                        <div class="slider-container" id="productsSlider">
                            <?php foreach ($sliderProducts as $index => $prod): ?>
                                <!-- Класс active ТОЛЬКО у первого слайда -->
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
                                                <?= number_format($prod['price'], 2, ',', ' ') ?> ₽
                                            </div>
                                            <a href="product.php?id=<?= (int)$prod['id'] ?>" class="text-wrapper-3">Подробнее</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="slider-dots" id="sliderDots"></div>
                    </div>
                </div>
            </section>

            <!-- УСЛУГИ -->
            <section class="services-section">
                <div class="view">
                    <div class="overlap-group">
                        <a href="services_catalog.php">
                        <h2 class="text-wrapper-1">УСЛУГИ</h2>
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
                                                <p style="font-size:14px;color:#555;margin-bottom:10px;">
                                                    <?= htmlspecialchars(mb_strimwidth($svc['description'], 0, 100, '...')) ?>
                                                </p>
                                            <?php endif; ?>
                                            <div class="service-price"><?= number_format($svc['price'], 2, ',', ' ') ?> ₽</div>
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
                
                <!-- ЛЕВАЯ КОЛОНКА -->
                <div class="split-left">
                    
                    <!-- Новинки -->
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
                    
                    <!-- Популярное -->
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
                
                <!-- ПРАВАЯ КОЛОНКА: Новости -->
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
                                    <time class="news-mini-date" datetime="<?= $item['datetime'] ?>">
                                        <?= date('d.m.Y', strtotime($item['datetime'])) ?>
                                    </time>
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
            <?php require_once 'footer_mip.php'; ?>
            
        </div>
    </div>

    <!-- JavaScript для слайдера -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.slider-slide');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const dotsContainer = document.getElementById('sliderDots');
        
        if (slides.length <= 1) {
            if(prevBtn) prevBtn.style.display = 'none';
            if(nextBtn) nextBtn.style.display = 'none';
            return;
        }
        
        let currentSlide = 0;
        let slideInterval;
        
        // Точки навигации
        slides.forEach((_, index) => {
            const dot = document.createElement('span');
            dot.className = 'slider-dot' + (index === 0 ? ' active' : '');
            dot.setAttribute('role', 'button');
            dot.setAttribute('aria-label', 'Перейти к слайду ' + (index + 1));
            dot.addEventListener('click', () => { goToSlide(index); resetInterval(); });
            dotsContainer.appendChild(dot);
        });
        
        const dots = document.querySelectorAll('.slider-dot');
        
        function goToSlide(index) {
            slides[currentSlide]?.classList.remove('active');
            dots[currentSlide]?.classList.remove('active');
            
            currentSlide = (index + slides.length) % slides.length;
            
            slides[currentSlide]?.classList.add('active');
            dots[currentSlide]?.classList.add('active');
        }
        
        function nextSlide() { goToSlide(currentSlide + 1); }
        function prevSlide() { goToSlide(currentSlide - 1); }
        
        if(nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetInterval(); });
        if(prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetInterval(); });
        
        function startInterval() { slideInterval = setInterval(nextSlide, 5000); }
        function resetInterval() { clearInterval(slideInterval); startInterval(); }
        
        const sliderWrapper = document.querySelector('.slider-wrapper');
        if(sliderWrapper) {
            sliderWrapper.addEventListener('mouseenter', () => clearInterval(slideInterval));
            sliderWrapper.addEventListener('mouseleave', startInterval);
        }
        
        startInterval();
    });
    document.querySelectorAll('.service-link').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const serviceId = this.dataset.serviceId;
        const serviceName = this.closest('.service-card').querySelector('.service-name').textContent;
        
        // ✅ Подтверждение перед заказом
        if (!confirm(`Заказать услугу "${serviceName}"?\n\nОна будет добавлена в ваш личный кабинет.`)) {
            return;
        }
        
        // Визуальная обратная связь
        const originalText = this.textContent;
        this.textContent = 'Добавление...';
        this.disabled = true;
        
        fetch('add_service.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `service_id=${serviceId}&service_name=${encodeURIComponent(serviceName)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                setTimeout(() => {
                    this.textContent = originalText;
                    this.style.background = '';
                    this.disabled = false;
                }, 2000);
                alert('Услуга добавлена в личный кабинет!');
            } else if (data.error === 'Необходимо авторизоваться') {
                // 🔁 Перенаправление на авторизацию
                if (confirm('Для заказа услуги необходимо войти в аккаунт.\n\nПерейти на страницу входа?')) {
                    window.location.href = 'authorization.php?redirect=' + encodeURIComponent(window.location.href);
                } else {
                    this.textContent = originalText;
                    this.disabled = false;
                }
            } else {
                throw new Error(data.error || 'Ошибка');
            }
        })
        .catch(err => {
            console.error(err);
            this.textContent = originalText;
            this.disabled = false;
            alert('Ошибка: ' + err.message);
        });
    });
});
    </script>
</body>
</html>