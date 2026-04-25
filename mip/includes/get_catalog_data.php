<?php
// Получение категорий
try {
    $stmt = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}

// Получение товаров
try {
    $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
    $categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
    
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
    
    // Поиск
    if (!empty($searchQuery)) {
        $escaped = preg_quote($searchQuery, '/');
        $sql .= " AND (
            p.name REGEXP :search
            OR p.short_description REGEXP :search
            OR p.full_description REGEXP :search
        )";
        $params[':search'] = $escaped;
    }
    
    // Фильтр по категории
    if ($categoryFilter > 0) {
        $sql .= " AND p.category_id = :category";
        $params[':category'] = $categoryFilter;
    }
    
    $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $products = [];
    error_log("Ошибка каталога: " . $e->getMessage());
}

// Получение главного изображения для товара
function getProductMainImage($pdo, $productId) {
    $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_main = 1 LIMIT 1");
    $stmt->execute([$productId]);
    $img = $stmt->fetchColumn();
    if (!$img) {
        $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? LIMIT 1");
        $stmt->execute([$productId]);
        $img = $stmt->fetchColumn();
    }
    return $img ?: 'img/placeholder.jpg';
}
?>