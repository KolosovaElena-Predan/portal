<?php
session_start();
require_once 'config.php';

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

function jsonResponse($success, $error = null, $data = []) {
    $response = ['success' => $success];
    if ($error) $response['error'] = $error;
    echo json_encode(array_merge($response, $data), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(false, 'Не авторизован');
}

$requestId = (int)($_POST['request_id'] ?? 0);

if (!$requestId) {
    jsonResponse(false, 'ID заказа не указан');
}

try {
    $stmt = $pdo->prepare("SELECT id FROM request WHERE id = ? AND user_id = ?");
    $stmt->execute([$requestId, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        jsonResponse(false, 'Заказ не найден');
    }
    
    $stmt = $pdo->prepare("
        SELECT rm.id, rm.sender_type, rm.message, rm.created_at,
               cf.id as file_id, cf.file_name, cf.file_url, cf.file_size
        FROM request_messages rm
        LEFT JOIN chat_files cf ON rm.id = cf.message_id
        WHERE rm.request_id = ?
        ORDER BY rm.created_at ASC
    ");
    $stmt->execute([$requestId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $messages = [];
    foreach ($results as $row) {
        $msgId = $row['id'];
        if (!isset($messages[$msgId])) {
            $messages[$msgId] = [
                'id' => $row['id'],
                'sender_type' => $row['sender_type'],
                'message' => $row['message'],
                'created_at' => date('d.m.Y H:i', strtotime($row['created_at'])),
                'files' => []
            ];
        }
        if ($row['file_id']) {
            // Убеждаемся, что URL правильный
            $fileUrl = $row['file_url'];
            // Если URL не начинается с /portal, добавляем
            if (strpos($fileUrl, '/portal/') !== 0 && strpos($fileUrl, 'uploads/') === 0) {
                $fileUrl = '/portal/' . $fileUrl;
            }
            $messages[$msgId]['files'][] = [
                'id' => $row['file_id'],
                'name' => $row['file_name'],
                'url' => $fileUrl,
                'size' => $row['file_size']
            ];
        }
    }
    
    jsonResponse(true, null, ['messages' => array_values($messages)]);
} catch (PDOException $e) {
    jsonResponse(false, 'Ошибка базы данных');
}
?>