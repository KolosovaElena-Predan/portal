<?php
// add_service.php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Необходимо авторизоваться']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Метод не поддерживается']);
    exit;
}

$service_id = (int)($_POST['service_id'] ?? 0);
$service_name = trim($_POST['service_name'] ?? '');

if ($service_id <= 0 || empty($service_name)) {
    echo json_encode(['success' => false, 'error' => 'Неверные данные услуги']);
    exit;
}

try {
    // Формируем сообщение с данными услуги
    $messageData = [
        'type' => 'service',
        'service_id' => $service_id,
        'service_name' => $service_name,
        'ordered_at' => date('Y-m-d H:i:s')
    ];
    
    // Вставляем в request: type='s' для услуг, status='new' по умолчанию
    $stmt = $pdo->prepare("
        INSERT INTO request (
            user_id, 
            device_type_id,
            product_id, 
            device_id,
            message, 
            status, 
            datetime, 
            type
        ) VALUES (?, NULL, NULL, NULL, ?, 'new', NOW(), 's')
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        json_encode($messageData, JSON_UNESCAPED_UNICODE)
    ]);
    
    echo json_encode(['success' => true, 'request_id' => $pdo->lastInsertId()]);
    
} catch (Exception $e) {
    error_log("Add service error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера']);
}
?>