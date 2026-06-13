<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$requestId = (int)($_GET['request_id'] ?? 0);

if (!$requestId) {
    echo json_encode(['success' => false, 'error' => 'ID заказа не указан']);
    exit;
}

// Проверяем, что заказ принадлежит пользователю
$stmt = $pdo->prepare("SELECT user_id FROM request WHERE id = ?");
$stmt->execute([$requestId]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request || $request['user_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Заказ не найден']);
    exit;
}

// Получаем историю с названиями статусов
$stmt = $pdo->prepare("
    SELECT rsh.status, rsh.comment, rsh.created_at, rs.name as status_text
    FROM request_status_history rsh
    LEFT JOIN request_statuses rs ON rsh.status = rs.code
    WHERE rsh.request_id = ?
    ORDER BY rsh.created_at ASC
");
$stmt->execute([$requestId]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'history' => $history
]);
?>