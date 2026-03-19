<?php
session_start();
require_once 'config.php';

$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $sql = "
        SELECT id, name, short_description, full_description, price, img_url, duration, is_active
        FROM services
        WHERE is_active = 1
    ";
    
    $params = [];
    
    if (!empty($searchQuery)) {
        $escaped = preg_quote($searchQuery, '/');
        $sql .= " AND (
            name REGEXP :search 
            OR short_description REGEXP :search 
            OR full_description REGEXP :search
        )";
        $params[':search'] = $escaped;
    }
    
    $sql .= " ORDER BY sort_order ASC, created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $services = [];
    error_log("Ошибка загрузки услуг: " . $e->getMessage());
}
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
    <title>Каталог услуг</title>
</head>
<body>
    <div class="screen">
        <div class="div">
            <?php require_once 'header_mip.php'; ?>

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
                        <a href="service_detail.php?id=<?= $service['id'] ?>" class="service-card-link">
                            <div class="service-card">
                                <div class="service-image-wrap">
                                    <img class="service-image"
                                         src="<?= htmlspecialchars($service['img_url'] ?: 'img/placeholder.jpg') ?>"
                                         alt="<?= htmlspecialchars($service['name']) ?>"
                                         onerror="this.src='https://via.placeholder.com/240x180?text=Услуга'">
                                </div>
                                <div class="service-content">
                                    <h3 class="service-name"><?= htmlspecialchars($service['name']) ?></h3>
                                    <p class="service-desc"><?= htmlspecialchars(mb_strimwidth(strip_tags($service['short_description']), 0, 120, '...')) ?></p>
                                    <?php if (!empty($service['duration'])): ?>
                                        <div class="service-duration"><?= htmlspecialchars($service['duration']) ?></div>
                                    <?php endif; ?>
                                    <div class="service-price"><?= number_format($service['price'], 2, ',', ' ') ?> ₽</div>
                                    
                                    <!-- Кнопка "Заказать" — добавляет в личный кабинет -->
                                    <button class="btn-order" 
                                            data-service-id="<?= $service['id'] ?>"
                                            data-service-name="<?= htmlspecialchars($service['name']) ?>"
                                            data-service-price="<?= $service['price'] ?>">
                                        Заказать
                                    </button>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php require_once 'footer_mip.php'; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Валидация поиска
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('searchInput');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                const query = searchInput.value.trim();
                if (query.length > 0 && query.length < 2) {
                    e.preventDefault();
                    alert('Введите минимум 2 символа для поиска');
                    searchInput.focus();
                }
            });
        }
        
        // Найдите обработчик кнопки и замените на:
document.querySelectorAll('.btn-order').forEach(btn => {
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
    });
    </script>
</body>
</html>