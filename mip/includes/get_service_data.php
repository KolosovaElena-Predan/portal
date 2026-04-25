<?php
$serviceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service = null;

if ($serviceId > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, name, short_description, full_description, price, img_url, duration, is_active
            FROM services
            WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([$serviceId]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Ошибка загрузки услуги: " . $e->getMessage());
    }
}

// Если услуга не найдена, перенаправляем на каталог
if (!$service) {
    header('Location: services_catalog.php');
    exit;
}
?>