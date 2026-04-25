<?php
/**
 * notifications.php - функции для работы с уведомлениями
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

// ✅ ТОЛЬКО НЕПРОЧИТАННЫЕ (для выпадающего меню)
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

// ✅ ВСЕ УВЕДОМЛЕНИЯ (для страницы "Все уведомления")
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

function markNotificationAsRead($pdo, $notificationId, $userId) {
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE id = ? AND user_id = ?
    ");
    return $stmt->execute([$notificationId, $userId]);
}

function markAllNotificationsAsRead($pdo, $userId) {
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE user_id = ? AND is_read = 0
    ");
    return $stmt->execute([$userId]);
}

function getUnreadCount($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM notifications 
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}