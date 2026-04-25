<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Необходимо авторизоваться']);
    exit;
}

$requestId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$requestId || !$message) {
    echo json_encode(['success' => false, 'error' => 'Некорректные данные']);
    exit;
}

// Проверка принадлежности заказа пользователю
$stmt = $pdo->prepare("SELECT user_id FROM request WHERE id = ?");
$stmt->execute([$requestId]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request || $request['user_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Доступ запрещён']);
    exit;
}

// Добавление сообщения
$stmt = $pdo->prepare("
    INSERT INTO request_messages (request_id, sender_type, sender_id, message)
    VALUES (?, 'user', ?, ?)
");
$stmt->execute([$requestId, $_SESSION['user_id'], $message]);

echo json_encode(['success' => true]);
?>