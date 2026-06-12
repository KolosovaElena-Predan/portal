<?php
/**
 * Проверяет лист ожидания для конкретного товара и создает уведомления,
 * если товара достаточно.
 *
 * @param PDO $pdo
 * @param int $productId ID товара
 * @param int $currentStock Текущий остаток на складе
 */
function checkWaitingListAndNotify($pdo, $productId, $currentStock) {
    if ($currentStock <= 0) return;

    // Ищем все активные заявки в ожидании для этого товара, которые еще не уведомлены
    $stmt = $pdo->prepare("
        SELECT id, user_id, message, requested_quantity 
        FROM request 
        WHERE product_id = ? 
        AND type = 'wl' 
        AND status = 'waiting' 
        AND is_notified = 0
    ");
    $stmt->execute([$productId]);
    $waitingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($waitingRequests as $req) {
        $messageData = json_decode($req['message'], true);
        $requestedQty = $req['requested_quantity'] ?? ($messageData['quantity'] ?? 0);

        // Если на складе достаточно товара для этой конкретной заявки
        if ($currentStock >= $requestedQty) {
            try {
                $pdo->beginTransaction();

                // 1. Создаем уведомление
                $productName = $messageData['product_name'] ?? 'Товар';
                $notifTitle = "Товар '{$productName}' поступил в наличии!";
                $notifMessage = "Здравствуйте! Товар, который вы ждали ({$requestedQty} шт.), теперь доступен для заказа. Успейте оформить покупку.";
                $link = "/mip/product.php?id=" . $productId;

                $notifStmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
                    VALUES (?, 'stock_available', ?, ?, ?, 0, NOW())
                ");
                $notifStmt->execute([$req['user_id'], $notifTitle, $notifMessage, $link]);

                // 2. Помечаем заявку в листе ожидания как уведомленную
                $updateReq = $pdo->prepare("UPDATE request SET is_notified = 1 WHERE id = ?");
                $updateReq->execute([$req['id']]);

                $pdo->commit();
                
                // Опционально: Здесь можно добавить код для отправки Email
                // sendEmailNotification($req['user_id'], $notifTitle, $notifMessage);

            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Ошибка при создании уведомления для user_id {$req['user_id']}: " . $e->getMessage());
            }
        }
    }
}
?>