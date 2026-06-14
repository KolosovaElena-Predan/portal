<?php
try {
    // Новинки
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
    // Популярное
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
    // Новости — ИСПРАВЛЕНО: добавлена фильтрация по разделу МИП
    $stmt = $pdo->prepare("
        SELECT 
            n.id, 
            n.title, 
            n.content, 
            n.datetime,
            (SELECT image_url FROM news_images WHERE news_id = n.id ORDER BY is_main DESC, sort_order LIMIT 1) as main_image
        FROM news n
        WHERE n.section = 'mip'
        ORDER BY n.datetime DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $news = [];
    error_log("News error: " . $e->getMessage());
}
?>