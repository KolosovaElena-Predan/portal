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
                <div class="notification-item-page <?= $notif['is_read'] ? 'read' : 'unread' ?>" 
                     data-id="<?= $notif['id'] ?>"
                     data-link="<?= htmlspecialchars($notif['link'] ?? '') ?>">
                    <div class="notification-content-page">
                        <div class="notification-title-page"><?= htmlspecialchars($notif['title']) ?></div>
                        <div class="notification-message-page"><?= nl2br(htmlspecialchars($notif['message'])) ?></div>
                        <?php if ($notif['link']): ?>
                            <a href="<?= $notif['link'] ?>" class="notification-link-page">
                                Подробнее <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
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
// Скрипты для страницы уведомлений
document.querySelectorAll('.notification-item-page').forEach(item => {
    item.addEventListener('click', function(e) {
        if (e.target.closest('.notification-link-page')) return;
        
        const notifId = this.dataset.id;
        const link = this.dataset.link;
        
        if (notifId && !this.classList.contains('read')) {
            fetch('/portal/mark_notification_read.php', {
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
        
        if (link && link !== '') {
            window.location.href = link;
        }
    });
});

const markAllBtn = document.getElementById('markAllBtn');
if (markAllBtn) {
    markAllBtn.addEventListener('click', function() {
        fetch('/portal/mark_all_notifications_read.php', { method: 'POST' })
            .then(() => location.reload());
    });
}
</script>

<?php require_once 'footer.php'; ?>