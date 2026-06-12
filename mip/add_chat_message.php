<?php
session_start();
require_once 'config.php';

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
    $stmt = $pdo->prepare("SELECT id FROM request WHERE id = ? AND user_id = ?");
    $stmt->execute([$requestId, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        jsonResponse(false, 'Заказ не найден');
    }
    
    $stmt = $pdo->prepare("INSERT INTO request_messages (request_id, sender_type, message, created_at) VALUES (?, 'user', ?, NOW())");
    $stmt->execute([$requestId, $message]);
    $messageId = $pdo->lastInsertId();
    
    jsonResponse(true, null, ['message_id' => $messageId]);
} catch (PDOException $e) {
    jsonResponse(false, 'Ошибка базы данных');
}
?>