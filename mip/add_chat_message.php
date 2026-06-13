<?php
session_start();
require_once '../config.php';
require_once '../includes/notifications.php';

// Отключаем вывод ошибок в ответ
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Функция для JSON-ответа
function jsonResponse($success, $error = null, $data = []) {
    $response = ['success' => $success];
    if ($error) $response['error'] = $error;
    echo json_encode(array_merge($response, $data), JSON_UNESCAPED_UNICODE);
    exit;
}

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    jsonResponse(false, 'Не авторизован');
}

$requestId = (int)($_POST['request_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if (!$requestId) {
    jsonResponse(false, 'ID заказа не указан');
}

if (empty($message)) {
    jsonResponse(false, 'Сообщение не может быть пустым');
}

// Проверяем, что заказ принадлежит пользователю
try {
    $stmt = $pdo->prepare("
        SELECT r.id, r.user_id, u.name as client_name 
        FROM request r
        JOIN user u ON r.user_id = u.id
        WHERE r.id = ? AND r.user_id = ?
    ");
    $stmt->execute([$requestId, $_SESSION['user_id']]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        jsonResponse(false, 'Заказ не найден');
    }
    
    // Сохраняем сообщение
    $stmt = $pdo->prepare("
        INSERT INTO request_messages (request_id, sender_type, sender_id, message, created_at) 
        VALUES (?, 'user', ?, ?, NOW())
    ");
    $stmt->execute([$requestId, $_SESSION['user_id'], $message]);
    $messageId = $pdo->lastInsertId();
    
    // ============================================
    // УВЕДОМЛЕНИЕ СПЕЦИАЛИСТАМ ПОДДЕРЖКИ
    // ============================================
    
    // Получаем всех специалистов поддержки
    $stmt = $pdo->prepare("SELECT id, name FROM user WHERE role = 'support_specialist'");
    $stmt->execute();
    $specialists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $title = "Новое сообщение от клиента в заявке #{$requestId}";
    $messagePreview = mb_substr($message, 0, 100);
    $messageText = "Клиент {$request['client_name']} оставил сообщение: " . $messagePreview;
    
    foreach ($specialists as $specialist) {
        // Добавляем уведомление в систему
        addNotification(
            $pdo, 
            $specialist['id'], 
            'new_client_message', 
            $title, 
            $messageText, 
            '/lk_support.php?request_id=' . $requestId
        );
    }
    
    jsonResponse(true, null, [
        'message_id' => $messageId,
        'specialists_notified' => count($specialists)
    ]);
    
} catch (PDOException $e) {
    error_log("Add chat message error: " . $e->getMessage());
    jsonResponse(false, 'Ошибка базы данных');
}
?>