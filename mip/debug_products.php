<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';

echo "<h2>🔍 Отладка products</h2>";

// 1. Проверка подключения
echo "<p>✅ PDO подключен: " . ($pdo ? 'Да' : 'Нет') . "</p>";

// 2. Сколько всего товаров?
$total = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
echo "<p>📦 Всего товаров: <b>$total</b></p>";

// 3. Товары для слайдера (как в index.php)
echo "<h3>🎠 Запрос слайдера:</h3>";
$sql = "
    SELECT 
        p.id, 
        p.name, 
        p.short_description, 
        p.base_price,
        p.stock,
        p.is_slider,
        p.status,
        pi.image_url as main_img,
        p.img_url as fallback_img
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
    WHERE p.is_slider = 1 AND p.status = 'active'
    ORDER BY p.sort_order ASC, p.created_at DESC 
    LIMIT 5
";
echo "<pre>" . htmlspecialchars($sql) . "</pre>";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>✅ Найдено записей: <b>" . count($results) . "</b></p>";
    
    if (empty($results)) {
        echo "<p style='color:red'>❌ Пустой результат! Проверьте условия WHERE:</p>";
        echo "<ul>";
        echo "<li>is_slider = 1? <b>" . $pdo->query("SELECT COUNT(*) FROM products WHERE is_slider=1")->fetchColumn() . "</b></li>";
        echo "<li>status = 'active'? <b>" . $pdo->query("SELECT COUNT(*) FROM products WHERE status='active'")->fetchColumn() . "</b></li>";
        echo "</ul>";
    } else {
        echo "<pre>";
        foreach ($results as $row) {
            print_r($row);
            echo "<hr>";
        }
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Ошибка запроса: " . $e->getMessage() . "</p>";
}

// 4. Проверка product_images
echo "<h3>🖼️ product_images:</h3>";
$imgs = $pdo->query("SELECT * FROM product_images LIMIT 5")->fetchAll();
foreach ($imgs as $img) { print_r($img); echo "<hr>"; }
?>