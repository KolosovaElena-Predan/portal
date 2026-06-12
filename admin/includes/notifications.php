<?php
/**
 * notifications.php - функции для работы с уведомлениями
 */

require_once __DIR__ . '/email_config.php';

/**
 * Добавление уведомления в БД
 */
function addNotification($pdo, $userId, $type, $title, $message, $link = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, link, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $type, $title, $message, $link]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Add notification error: " . $e->getMessage());
        return false;
    }
}

/**
 * Добавление уведомления с отправкой email
 */
function addNotificationWithEmail($pdo, $userId, $type, $title, $message, $link = null, $sendEmail = true) {
    // Добавляем в БД
    $notificationId = addNotification($pdo, $userId, $type, $title, $message, $link);
    
    // Отправляем email
    if ($sendEmail) {
        $stmt = $pdo->prepare("SELECT email, name FROM user WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && !empty($user['email'])) {
            $siteUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/portal/mip';
            
            switch ($type) {
                case 'stock_available':
                    preg_match('/id=(\d+)/', $link ?? '', $matches);
                    $productId = $matches[1] ?? null;
                    if ($productId) {
                        $productUrl = $siteUrl . '/product.php?id=' . $productId;
                        $htmlMessage = getProductAvailableEmailTemplate($title, $productUrl);
                        sendEmailNotification($user['email'], $user['name'], $title, $htmlMessage);
                    }
                    break;
                    
                case 'status_change':
                    $orderId = preg_replace('/[^0-9]/', '', $title);
                    $htmlMessage = getOrderStatusEmailTemplate($orderId, $message, null);
                    sendEmailNotification($user['email'], $user['name'], "Статус заказа изменён", $htmlMessage);
                    break;
                    
                case 'new_message':
                    preg_match('/#(\d+)/', $link ?? '', $matches);
                    $requestId = $matches[1] ?? '0';
                    $htmlMessage = getNewMessageEmailTemplate($requestId, $message, 'Специалист поддержки');
                    sendEmailNotification($user['email'], $user['name'], "Новое сообщение в чате", $htmlMessage);
                    break;
            }
        }
    }
    
    return $notificationId;
}

/**
 * Уведомление всех пользователей из листа ожидания о поступлении товара
 */
function notifyWaitingUsersProductAvailable($pdo, $productId, $productName, $newStock, $productImage = null) {
    $stmt = $pdo->prepare("
        SELECT r.id, r.user_id, r.message, u.email, u.name 
        FROM request r
        JOIN user u ON r.user_id = u.id
        WHERE r.product_id = ? AND r.type = 'wl' AND r.status = 'waiting' AND r.is_notified = 0
    ");
    $stmt->execute([$productId]);
    $waitingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($waitingRequests)) return 0;

    $notifiedCount = 0;
    $siteUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/portal/mip';
    $productUrl = $siteUrl . '/product.php?id=' . $productId;
    
    foreach ($waitingRequests as $req) {
        $userData = json_decode($req['message'], true);
        $requestedQty = $userData['quantity'] ?? 1;
        
        if ($newStock >= $requestedQty) {
            $title = "Товар '{$productName}' поступил в наличие!";
            $message = "Запрошенное вами количество ({$requestedQty} шт.) теперь доступно для заказа в каталоге.";
            $link = "/mip/product.php?id=" . $productId;

            // Добавляем уведомление в систему
            addNotification($pdo, $req['user_id'], 'stock_available', $title, $message, $link);
            
            // Отправляем email
            $htmlMessage = getProductAvailableEmailTemplate($productName, $productUrl, $requestedQty, $productImage);
            sendEmailNotification($req['email'], $req['name'], $title, $htmlMessage);
            
            // Отмечаем как уведомлённого
            $updateReq = $pdo->prepare("UPDATE request SET is_notified = 1 WHERE id = ?");
            $updateReq->execute([$req['id']]);
            
            $notifiedCount++;
        }
    }
    
    return $notifiedCount;
}

/**
 * Получение непрочитанных уведомлений (для выпадающего меню)
 */
function getUnreadNotifications($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? AND is_read = 0 
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Получение всех уведомлений (для страницы "Все уведомления")
 */
function getAllNotifications($pdo, $userId, $limit = 50, $offset = 0) {
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Отметить уведомление как прочитанное
 */
function markNotificationAsRead($pdo, $notificationId, $userId) {
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE id = ? AND user_id = ?
    ");
    return $stmt->execute([$notificationId, $userId]);
}

/**
 * Отметить все уведомления как прочитанные
 */
function markAllNotificationsAsRead($pdo, $userId) {
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE user_id = ? AND is_read = 0
    ");
    return $stmt->execute([$userId]);
}

/**
 * Получить количество непрочитанных уведомлений
 */
function getUnreadCount($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM notifications 
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}