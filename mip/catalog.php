<?php
session_start();
$context = 'mip';
require_once 'config.php';

// ✅ СНАЧАЛА подключаем получение данных (где определена функция)
require_once 'includes/get_catalog_data.php';

// ✅ ПОТОМ подключаем шапку
require_once '../header.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style_header_footer.css" />
    <link rel="stylesheet" href="css/style_mip.css" />
    <link rel="stylesheet" href="css/style_main.css" />
    <link rel="stylesheet" href="css/style_catalog.css" />
    <link rel="stylesheet" href="css/header_mip.css" />
    <title>Каталог продукции</title>
</head>
<body>
    <div class="screen">
        <div class="div">
            
            <!-- Шапка -->
            <?php 
                $context = 'mip';
                require_once '../header.php'; 
            ?>

            <div class="catalog-content">
                <h1 class="catalog-title">Каталог продукции</h1>
                
                <!-- Поиск и фильтры -->
                <div class="catalog-filters">
                    <form method="GET" class="search-container" id="searchForm">
                        <input type="text"
                            class="input-field"
                            name="search"
                            id="searchInput"
                            placeholder="Поиск по названию или описанию..."
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                            autocomplete="off" />
                        <button type="submit" class="btn-search">
                            <i class="fas fa-search"></i> Найти
                        </button>
                        <?php if (!empty($_GET['search'])): ?>
                            <a href="catalog.php" class="btn-search btn-reset">
                                Сброс
                            </a>
                        <?php endif; ?>
                    </form>
                    
                    <!-- Фильтр по категориям -->
                    <?php if (!empty($categories)): ?>
                    <div class="category-filter">
                        <span class="filter-label">Категории:</span>
                        <div class="category-buttons">
                            <a href="catalog.php<?= !empty($_GET['search']) ? '?search='.urlencode($_GET['search']) : '' ?>" 
                               class="category-btn <?= empty($_GET['category']) ? 'active' : '' ?>">
                                Все
                            </a>
                            <?php foreach ($categories as $cat): ?>
                            <a href="catalog.php?category=<?= (int)$cat['id'] ?><?= !empty($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?>" 
                               class="category-btn <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'active' : '' ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Счётчик товаров -->
                <div class="products-count">
                    Найдено товаров: <strong><?= count($products) ?></strong>
                </div>
                
                <!-- Сетка карточек -->
                <div class="catalog-wrapper">
                    <?php if (empty($products)): ?>
                        <div class="no-devices">
                            Продукция временно недоступна
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <?php $main_img = getProductMainImage($pdo, $product['id']); ?>
                            <a href="product.php?id=<?= $product['id'] ?>" class="device-card-link">
                                <div class="device-card" data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>">
                                    <img class="device-image"
                                        src="<?= htmlspecialchars($main_img) ?>"
                                        alt="<?= htmlspecialchars($product['name']) ?>"
                                        onerror="this.src='img/placeholder.png'">
                                    
                                    <div class="device-name"><?= htmlspecialchars($product['name']) ?></div>
                                    <p class="device-desc">
                                        <?= htmlspecialchars(mb_strimwidth(strip_tags($product['short_description']), 0, 120, '...')) ?>
                                    </p>
                                    <div class="device-price">
                                        от <?= number_format($product['min_price'] ?? $product['base_price'], 2, ',', ' ') ?> ₽
                                    </div>
                                    <a href="product.php?id=<?= $product['id'] ?>#orderBlock"
                                       class="btn-order"
                                       onclick="event.stopPropagation();">
                                       Заказать
                                    </a>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="js/catalog.js"></script>
</body>
</html>
<?php
// Гарантируем, что контекст определён
if (!isset($context)) {
    $context = 'lab';
}
require_once '../footer.php';
?>