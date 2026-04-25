<?php
// Получение данных для каталога услуг
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