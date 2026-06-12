<?php
session_start();
require_once 'config.php';
require_once 'includes/notifications.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$count = getUnreadCount($pdo, $_SESSION['user_id']);
$notifications = getUnreadNotifications($pdo, $_SESSION['user_id']);

ob_start();
foreach ($notifications as $notif): ?>
    <div class="notification-item unread" data-id="<?= $notif['id'] ?>">
        <div class="notification-icon">
            <i class="fas fa-bell"></i>
        </div>
        <div class="notification-content">
            <div class="notification-title"><?= htmlspecialchars($notif['title']) ?></div>
            <div class="notification-message"><?= htmlspecialchars($notif['message']) ?></div>
            <?php if ($notif['link']): ?>
                <a href="<?= $notif['link'] ?>" class="notification-link">Подробнее →</a>
            <?php endif; ?>
            <div class="notification-time"><?= date('d.m.Y H:i', strtotime($notif['created_at'])) ?></div>
        </div>
    </div>
<?php endforeach;
$html = ob_get_clean();

echo json_encode(['count' => $count, 'html' => $html]);