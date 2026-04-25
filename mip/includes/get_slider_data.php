<?php
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
?>