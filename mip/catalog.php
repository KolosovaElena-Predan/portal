<?php
session_start();
require_once 'config.php';

try {
    // Получаем поисковый запрос
    $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    $sql = "
        SELECT p.*,
               COUNT(DISTINCT pi.id) as images_count,
               MIN(pc.price) as min_price
        FROM products p
        LEFT JOIN product_images pi ON p.id = pi.product_id
        LEFT JOIN product_configurations pc ON p.id = pc.product_id
        WHERE p.status = 'active'
    ";
    
    $params = [];
    
    // Поиск с REGEXP по назанию и описаниям
    if (!empty($searchQuery)) {
        // Экранируем спецсимволы REGEXP для безопасности
        $escaped = preg_quote($searchQuery, '/');
        $sql .= " AND (
            p.name REGEXP :search 
            OR p.short_description REGEXP :search 
            OR p.full_description REGEXP :search
        )";
        $params[':search'] = $escaped;
    }
    
    $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $products = [];
    error_log("Ошибка поиска: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
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
            <?php require_once 'header_mip.php'; ?>

            <div class="catalog-content">
                <h1 class="catalog-title">Каталог продукции</h1>

                <form method="GET" class="search-container" id="searchForm">
                    <input type="text" 
                           class="input-field" 
                           name="search" 
                           id="searchInput"
                           placeholder="Поиск..." 
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                           autocomplete="off" />
                    <button type="submit" class="btn-search">
                         Найти
                    </button>
                    <?php if (!empty($_GET['search'])): ?>
                        <a href="catalog.php" class="btn-search" style="background:#6c757d;width:100px;">
                             Сброс
                        </a>
                    <?php endif; ?>
                </form>

                <div class="catalog-wrapper">
                    <?php if (empty($products)): ?>
                        <p class="no-devices">Продукция временно недоступна.</p>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                        <a href="product.php?id=<?= $product['id'] ?>" class="device-card-link">
                            <div class="device-card" data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>">
                                <?php if ($product['images_count'] > 0): ?>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_main = 1 LIMIT 1");
                                    $stmt->execute([$product['id']]);
                                    $main_img = $stmt->fetchColumn();
                                    if (!$main_img) {
                                        $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? LIMIT 1");
                                        $stmt->execute([$product['id']]);
                                        $main_img = $stmt->fetchColumn();
                                    }
                                    ?>
                                    <img class="device-image"
                                         src="<?= htmlspecialchars($main_img ?? 'img/placeholder.jpg') ?>"
                                         alt="<?= htmlspecialchars($product['name']) ?>"
                                         onerror="this.src='https://via.placeholder.com/284x217?text=Нет+фото'">
                                <?php else: ?>
                                    <img class="device-image"
                                         src="https://via.placeholder.com/284x217?text=Нет+фото"
                                         alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php endif; ?>

                                <div class="device-name"><?= htmlspecialchars($product['name']) ?></div>
                                <p class="device-desc">
                                    <?= htmlspecialchars(mb_strimwidth(strip_tags($product['short_description']), 0, 120, '...')) ?>
                                </p>
                                <div class="device-stock">В наличии</div>
                                <div class="device-price">
                                    от <?= number_format($product['min_price'] ?? $product['base_price'], 2, ',', ' ') ?> ₽
                                </div>
                                <a href="product.php?id=<?= $product['id'] ?>#orderBlock" 
                                   class="btn-order" 
                                   onclick="event.stopPropagation();"
                                   style="display:inline-block;text-align:center;">
                                    Заказать
                                </a>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <?php require_once 'footer_mip.php'; ?>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    
    // Валидация: минимум 2 символа
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
    
    // Кнопки "Заказать" — не переходить по ссылке карточки
    // Кнопки "Заказать" — переход к конфигуратору
    document.querySelectorAll('.btn-order').forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Не предотвращаем переход — просто позволяем ссылке работать
            // Но останавливаем всплытие, чтобы не срабатывал клик по карточке
            e.stopPropagation();
        });
    });
});
</script>
</body>
</html>