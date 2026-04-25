<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

$requestId = (int)($_POST['request_id'] ?? 0);
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

$stmt = $pdo->prepare("SELECT sender_type, message, created_at FROM request_messages WHERE request_id = ? ORDER BY created_at ASC");
$stmt->execute([$requestId]);
$messages = $stmt->fetchAll();

foreach ($messages as &$msg) {
    $msg['created_at'] = date('d.m.Y H:i', strtotime($msg['created_at']));
}

echo json_encode(['success' => true, 'messages' => $messages]);