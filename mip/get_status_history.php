<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

$requestId = (int)($_GET['request_id'] ?? 0);
if (!$requestId) {
    echo json_encode(['success' => false, 'error' => 'No request ID']);
    exit;
}

// Проверяем, что заявка принадлежит пользователю
$stmt = $pdo->prepare("SELECT user_id FROM request WHERE id = ?");
$stmt->execute([$requestId]);
$req = $stmt->fetch();
if (!$req || $req['user_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

$stmt = $pdo->prepare("SELECT status, comment, created_at FROM request_status_history WHERE request_id = ? ORDER BY created_at ASC");
$stmt->execute([$requestId]);
$history = $stmt->fetchAll();

$statusLabels = ['new' => 'Новый', 'processed' => 'В обработке', 'closed' => 'Закрыт', 'cancelled' => 'Отменён'];

foreach ($history as &$item) {
    $item['status_text'] = $statusLabels[$item['status']] ?? $item['status'];
    $item['created_at'] = date('d.m.Y H:i', strtotime($item['created_at']));
}

echo json_encode(['success' => true, 'history' => $history]);