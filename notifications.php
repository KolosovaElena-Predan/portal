<?php
session_start();
require_once 'config.php';
require_once 'includes/notifications.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: authorization.php');
    exit;
}

$userId = $_SESSION['user_id'];
$all_notifications = getAllNotifications($pdo, $userId, 100);

$context = 'mip';
require_once 'header.php';
?>

<style>
/* ========================================
   Страница уведомлений — минималистичный стиль
   ======================================== */

.notifications-page {
    max-width: 900px;
    margin: 140px auto 80px;
    padding: 0 20px;
}

/* Кнопка "Назад" */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 30px;
    color: #00a896;
    text-decoration: none;
    font-family: "Inter-Medium", sans-serif;
    font-size: 14px;
    transition: gap 0.3s;
}

.back-link:hover {
    gap: 12px;
    color: #008a7a;
}

.back-link i {
    font-size: 12px;
}

/* Заголовок страницы */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
}

.page-header h1 {
    font-family: "Inter-Bold", sans-serif;
    font-size: 28px;
    font-weight: 700;
    color: #000000;
    margin: 0;
}

/* Кнопка "Отметить все" */
.mark-all-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: none;
    border: none;
    color: #00a896;
    font-family: "Inter-Medium", sans-serif;
    font-size: 14px;
    cursor: pointer;
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.3s;
}

.mark-all-link:hover {
    background: #f0fbfb;
    color: #008a7a;
}

.mark-all-link i {
    font-size: 13px;
}

/* Список уведомлений */
.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Карточка уведомления */
.notification-item-page {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}

.notification-item-page:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border-color: #00a896;
}

/* Непрочитанное уведомление */
.notification-item-page.unread {
    background: #f0fbfb;
    border-left: 4px solid #00a896;
}

.notification-item-page.unread:hover {
    background: #e8f8f7;
}

/* Контент уведомления */
.notification-content-page {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* Заголовок */
.notification-title-page {
    font-family: "Inter-Bold", sans-serif;
    font-size: 16px;
    font-weight: 600;
    color: #000000;
    line-height: 1.4;
}

.unread .notification-title-page {
    color: #00a896;
}

/* Сообщение */
.notification-message-page {
    font-family: "Inter-Regular", sans-serif;
    font-size: 14px;
    color: #555555;
    line-height: 1.5;
}

/* Время */
.notification-time-page {
    font-family: "Inter-Regular", sans-serif;
    font-size: 12px;
    color: #999999;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #f0f0f0;
}

.notification-time-page i {
    font-size: 11px;
}

/* Пустое состояние */
.empty-page {
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 16px;
    color: #999999;
    font-family: "Inter-Regular", sans-serif;
    font-size: 16px;
}

.empty-page i {
    font-size: 48px;
    margin-bottom: 15px;
    display: block;
    color: #cccccc;
}

/* ========================================
   АДАПТИВНОСТЬ
   ======================================== */

@media (max-width: 768px) {
    .notifications-page {
        margin: 120px auto 60px;
        padding: 0 15px;
    }
    
    .page-header h1 {
        font-size: 24px;
    }
    
    .notification-item-page {
        padding: 16px;
    }
    
    .notification-title-page {
        font-size: 15px;
    }
    
    .notification-message-page {
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .notifications-page {
        margin: 110px auto 50px;
        padding: 0 12px;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .page-header h1 {
        font-size: 22px;
    }
    
    .mark-all-link {
        padding: 6px 12px;
        font-size: 13px;
    }
    
    .notification-item-page {
        padding: 14px;
    }
    
    .notification-title-page {
        font-size: 14px;
    }
    
    .notification-message-page {
        font-size: 12px;
    }
    
    .empty-page {
        padding: 40px 20px;
        font-size: 14px;
    }
    
    .empty-page i {
        font-size: 40px;
    }
}
</style>

<div class="notifications-page">
    <a href="javascript:history.back()" class="back-link">
        <i class="fas fa-arrow-left"></i> Назад
    </a>
    
    <div class="page-header">
        <h1>Все уведомления</h1>
        <?php if (!empty($all_notifications)): ?>
            <button class="mark-all-link" id="markAllBtn">
                <i class="fas fa-check-double"></i> Отметить все как прочитанные
            </button>
        <?php endif; ?>
    </div>
    
    <div class="notifications-list">
        <?php if (empty($all_notifications)): ?>
            <div class="empty-page">
                <i class="fas fa-bell-slash"></i>
                У вас пока нет уведомлений
            </div>
        <?php else: ?>
            <?php foreach ($all_notifications as $notif): ?>
                <?php 
                // Извлекаем ID заявки из уведомления
                $requestId = null;
                $actionType = null;
                $productId = null;
                $redirectUrl = null;
                
                // Поиск ID заявки в сообщении
                if (preg_match('/#(\d+)/', $notif['message'], $matches)) {
                    $requestId = $matches[1];
                } elseif (preg_match('/request-(\d+)/', $notif['link'] ?? '', $matches)) {
                    $requestId = $matches[1];
                }
                
                // Поиск ID товара для уведомлений о поступлении
                if (preg_match('/товар [\'"](.+?)[\'"]/', $notif['message'], $matches)) {
                    $productName = $matches[1];
                }
                if (preg_match('/id(\d+)/', $notif['link'] ?? '', $matches)) {
                    $productId = $matches[1];
                }
                
                // Определяем тип действия
                $notificationType = $notif['type'] ?? '';
                
                if ($notificationType === 'new_message' || strpos($notif['title'], 'Новое сообщение') !== false) {
                    $actionType = 'chat';
                    $redirectUrl = 'lk_user.php';
                } 
                elseif ($notificationType === 'status_change' || strpos($notif['title'], 'Статус') !== false) {
                    $actionType = 'status';
                    $redirectUrl = 'lk_user.php';
                }
                elseif (strpos($notif['title'], 'поступил в наличие') !== false || strpos($notif['message'], 'поступил в наличие') !== false) {
                    // Уведомление о поступлении товара из листа ожидания
                    $actionType = 'product_available';
                    if ($productId) {
                        $redirectUrl = 'product.php?id=' . $productId;
                    } else {
                        $redirectUrl = 'waiting_list.php';
                    }
                }
                elseif (strpos($notif['title'], 'Заказ') !== false || strpos($notif['title'], 'заказ') !== false) {
                    $actionType = 'order';
                    $redirectUrl = 'lk_user.php';
                }
                else {
                    $redirectUrl = 'lk_user.php';
                }
                ?>
                <div class="notification-item-page <?= $notif['is_read'] ? 'read' : 'unread' ?>" 
                     data-id="<?= $notif['id'] ?>"
                     data-request-id="<?= $requestId ?>"
                     data-action-type="<?= $actionType ?>"
                     data-redirect-url="<?= $redirectUrl ?>"
                     data-product-id="<?= $productId ?>">
                    <div class="notification-content-page">
                        <div class="notification-title-page"><?= htmlspecialchars($notif['title']) ?></div>
                        <div class="notification-message-page"><?= nl2br(htmlspecialchars($notif['message'])) ?></div>
                        <div class="notification-time-page">
                            <i class="far fa-clock"></i> <?= date('d.m.Y H:i', strtotime($notif['created_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Обработка кликов по уведомлениям
document.querySelectorAll('.notification-item-page').forEach(item => {
    item.addEventListener('click', function(e) {
        const notifId = this.dataset.id;
        const requestId = this.dataset.requestId;
        const actionType = this.dataset.actionType;
        let redirectUrl = this.dataset.redirectUrl;
        const productId = this.dataset.productId;
        
        // Отмечаем как прочитанное
        if (notifId && !this.classList.contains('read')) {
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + notifId
            }).catch(err => console.error(err));
            this.classList.add('read');
            this.classList.remove('unread');
            
            const badge = document.querySelector('.notifications-badge');
            if (badge) {
                let count = parseInt(badge.textContent) - 1;
                if (count <= 0) badge.style.display = 'none';
                else badge.textContent = count;
            }
        }
        
        // Обработка разных типов уведомлений
        if (actionType === 'product_available') {
            // Переход на страницу товара или лист ожидания
            if (redirectUrl && redirectUrl !== '') {
                window.location.href = 'mip/' + redirectUrl;
            } else {
                window.location.href = 'mip/waiting_list.php';
            }
        } 
        else if (actionType === 'chat' && requestId) {
            // Сохраняем в sessionStorage информацию о том, какое окно открыть
            sessionStorage.setItem('openModal', 'chat');
            sessionStorage.setItem('modalRequestId', requestId);
            window.location.href = 'mip/lk_user.php';
        } 
        else if (actionType === 'status' && requestId) {
            // Сохраняем в sessionStorage информацию о том, какое окно открыть
            sessionStorage.setItem('openModal', 'status');
            sessionStorage.setItem('modalRequestId', requestId);
            window.location.href = 'mip/lk_user.php';
        } 
        else {
            // Обычный переход по ссылке
            if (redirectUrl && redirectUrl !== '') {
                window.location.href = 'mip/' + redirectUrl;
            } else {
                window.location.href = 'mip/lk_user.php';
            }
        }
    });
});

const markAllBtn = document.getElementById('markAllBtn');
if (markAllBtn) {
    markAllBtn.addEventListener('click', function() {
        fetch('mark_all_notifications_read.php', { method: 'POST' })
            .then(() => location.reload());
    });
}
</script>

<?php require_once 'footer.php'; ?>