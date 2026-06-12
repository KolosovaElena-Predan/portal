<?php
session_start();
require_once '../config.php';
require_once '../includes/notifications.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

$requestId = (int)($_POST['request_id'] ?? 0);
$status = $_POST['status'] ?? '';
$comment = trim($_POST['comment'] ?? '');
$type = $_POST['type'] ?? '';

if (!$requestId) {
    echo json_encode(['success' => false, 'error' => 'No request ID']);
    exit;
}

try {
    // Получаем информацию о заявке
    $stmt = $pdo->prepare("
        SELECT r.user_id, r.product_id, r.type, p.name as product_name
        FROM request r
        LEFT JOIN products p ON r.product_id = p.id
        WHERE r.id = ?
    ");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo json_encode(['success' => false, 'error' => 'Request not found']);
        exit;
    }

    $statusLabels = [
        'new' => 'Новый',
        'processed' => 'В обработке',
        'closed' => 'Закрыт',
        'cancelled' => 'Отменён'
    ];

    // ============================================
    // 1. УВЕДОМЛЕНИЕ ОБ ИЗМЕНЕНИИ СТАТУСА (для клиента)
    // ============================================
    if ($status && isset($statusLabels[$status])) {
        $title = "Статус заявки #{$requestId} изменён";
        $message = "Статус вашей заявки изменён на: {$statusLabels[$status]}";
        if ($comment) {
            $message .= "\nКомментарий: " . $comment;
        }
        
        // ✅ Ссылка с якорем на статус
        $link = "/mip/lk_user.php#request-{$requestId}-status";
        
        addNotification($pdo, $request['user_id'], 'status_change', $title, $message, $link);
        echo json_encode(['success' => true, 'type' => 'status_change']);
        exit;
    }
    
    // ============================================
    // 2. УВЕДОМЛЕНИЕ О НОВОМ СООБЩЕНИИ ОТ ПОЛЬЗОВАТЕЛЯ (для поддержки)
    // ============================================
    if ($type === 'user_message') {
        // Находим специалиста поддержки
        $stmt = $pdo->prepare("
            SELECT id FROM user 
            WHERE role = 'support_specialist' 
            LIMIT 1
        ");
        $stmt->execute();
        $support = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($support) {
            $title = "Новое сообщение в чате";
            $message = "Пользователь оставил сообщение в заявке #{$requestId}";
            // ✅ Ссылка с якорем на чат
            $link = "/mip/lk_support.php#request-{$requestId}-chat";
            
            addNotification($pdo, $support['id'], 'new_message', $title, $message, $link);
            echo json_encode(['success' => true, 'type' => 'new_message']);
            exit;
        }
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);

} catch (Exception $e) {
    error_log("Add notification error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}