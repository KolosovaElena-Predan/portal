<?php
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
?>