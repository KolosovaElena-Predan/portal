<?php
//session_start();
require_once 'config.php';

// Получаем данные о пользователе и уведомлениях
$is_logged = isset($_SESSION['user_id']);
$unreadCount = 0;
$notifications = [];

if ($is_logged) {
    require_once '../includes/notifications.php';
    $unreadCount = getUnreadCount($pdo, $_SESSION['user_id']);
    $notifications = getUnreadNotifications($pdo, $_SESSION['user_id']);
}

/*function getImageUrl($url) {
    if (empty($url)) return 'img/placeholder.png';
    $url = ltrim($url, './');
    return file_exists($url) ? $url : 'img/placeholder.png';
}*/

try {
    $stmt = $pdo->prepare("
        SELECT 
            p.id, 
            p.name, 
            p.short_description as description, 
            p.base_price as price,
            p.stock,
            pi.image_url as img_url
        FROM products p
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        WHERE p.is_slider = 1 
            AND p.status = 'active'
        ORDER BY p.sort_order ASC, p.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $sliderProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $sliderProducts = [];
}

// Остальной код получения услуг, новинок, популярного, новостей...
// (оставьте ваш существующий код здесь)
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style_header_footer.css" />
    <link rel="stylesheet" href="css/style_main.css" />
    <link rel="stylesheet" href="css/style_mip.css" />
    <link rel="stylesheet" href="css/header_mip.css" />
    <title>ООО МИП "НПЦ ПИТиА" — Главная</title>
</head>
<body>
    <div class="screen">
        <div class="div">
            
            <!-- Шапка с уведомлениями -->
            <header class="view-6">
                <!-- Логотип -->
                <a href="mip.php">
                    <img class="image-3" src="img/logo_mip.png" alt="Логотип МИП" />
                </a>
                
                <!-- Меню навигации -->
                <nav style="display: flex; gap: 20px; margin-left: 20px;">
                    <a href="catalog.php" class="text-wrapper-17">Каталог</a>
                    <a href="services_catalog.php" class="text-wrapper-17">Услуги</a>
                    <a href="news.php" class="text-wrapper-17">Новости</a>
                    <a href="question.php" class="text-wrapper-17">Поддержка</a>
                </nav>
                
                <!-- Иконки справа -->
                <div class="header-icons">
                    <!-- Корзина -->
                    <a href="cart.php" class="icon-btn" title="Корзина">
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                    
                    <!-- Уведомления (только для авторизованных) -->
                    <?php if ($is_logged): ?>
                    <div class="notifications-wrapper">
                        <button class="notifications-btn" id="notificationsBtn" title="Уведомления">
                            <i class="fas fa-bell"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="notifications-badge"><?= $unreadCount ?></span>
                            <?php endif; ?>
                        </button>
                        
                        <div class="notifications-dropdown" id="notificationsDropdown">
                            <div class="notifications-header">
                                <span>Уведомления</span>
                                <button id="markAllRead" class="mark-read-btn">Все прочитано</button>
                            </div>
                            <div class="notifications-list">
                                <?php if (empty($notifications)): ?>
                                    <div class="notification-empty">Нет новых уведомлений</div>
                                <?php else: ?>
                                    <?php foreach ($notifications as $notif): ?>
                                        <div class="notification-item <?= $notif['is_read'] ? 'read' : 'unread' ?>" 
                                             data-id="<?= $notif['id'] ?>"
                                             data-link="<?= htmlspecialchars($notif['link'] ?? '') ?>">
                                            <div class="notification-icon">
                                                <?php if ($notif['type'] == 'status_change'): ?>
                                                    <i class="fas fa-exchange-alt"></i>
                                                <?php elseif ($notif['type'] == 'new_message'): ?>
                                                    <i class="fas fa-comment-dots"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-bell"></i>
                                                <?php endif; ?>
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
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="notifications-footer">
                                <a href="../notifications.php">Все уведомления</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Личный кабинет / Вход -->
                <?php if ($is_logged): ?>
                    <a href="lk_user.php" class="btn-2">
                        <span>Личный кабинет</span>
                    </a>
                <?php else: ?>
                    <a href="../authorization.php" class="btn-2">
                        <span>Войти</span>
                    </a>
                <?php endif; ?>
            </header>

            <!-- Стили для уведомлений -->
            <style>
            .notifications-wrapper {
                position: relative;
                display: inline-block;
            }
            .notifications-btn {
                position: relative;
                background: transparent;
                border: none;
                font-size: 24px;
                cursor: pointer;
                padding: 8px 12px;
                border-radius: 10px;
                transition: background 0.3s;
                color: #1a1982;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .notifications-btn:hover {
                background: rgba(26, 25, 130, 0.1);
            }
            .notifications-badge {
                position: absolute;
                top: -5px;
                right: -5px;
                background: #dc3545;
                color: white;
                font-size: 10px;
                border-radius: 50%;
                min-width: 18px;
                height: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0 4px;
            }
            .notifications-dropdown {
                display: none;
                position: absolute;
                right: 0;
                top: 45px;
                width: 380px;
                max-height: 500px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                z-index: 1000;
                overflow: hidden;
                flex-direction: column;
            }
            .notifications-dropdown.show {
                display: flex;
            }
            .notifications-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 16px;
                border-bottom: 1px solid #e0e0e0;
                font-weight: bold;
                background: white;
            }
            .mark-read-btn {
                background: none;
                border: none;
                color: #1a1982;
                cursor: pointer;
                font-size: 12px;
            }
            .notifications-list {
                overflow-y: auto;
                max-height: 400px;
            }
            .notification-item {
                display: flex;
                gap: 12px;
                padding: 12px 16px;
                border-bottom: 1px solid #f0f0f0;
                transition: background 0.2s;
                cursor: pointer;
            }
            .notification-item.unread {
                background: #f0f7ff;
            }
            .notification-item:hover {
                background: #f5f5f5;
            }
            .notification-icon {
                flex-shrink: 0;
                width: 36px;
                height: 36px;
                background: #e7e8f3;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #1a1982;
                font-size: 16px;
            }
            .notification-content {
                flex: 1;
            }
            .notification-title {
                font-weight: 600;
                font-size: 14px;
                margin-bottom: 4px;
                color: #1a1982;
            }
            .notification-message {
                font-size: 12px;
                color: #666;
                margin-bottom: 4px;
                white-space: pre-line;
            }
            .notification-link {
                font-size: 11px;
                color: #1a1982;
                text-decoration: none;
            }
            .notification-time {
                font-size: 10px;
                color: #999;
                margin-top: 4px;
            }
            .notification-empty {
                text-align: center;
                padding: 40px 20px;
                color: #999;
            }
            .notifications-footer {
                padding: 10px 16px;
                border-top: 1px solid #e0e0e0;
                text-align: center;
                background: white;
            }
            .notifications-footer a {
                color: #1a1982;
                text-decoration: none;
                font-size: 13px;
            }
            </style>

            <div class="overlap-4">
                <div class="text-wrapper-7">ООО МИП "НПЦ ПИТиА"</div>
                <p class="text-wrapper-6">
                    Малое инновационное предприятие "Научно-производственный центр передовых интеллектуальных технологий и автоматизации"
                </p>
            </div>

            <!-- Остальной контент страницы (оставьте ваш существующий код) -->
            
        </div>
    </div>

    <!-- Скрипты для уведомлений -->
    <script>
    // Открытие/закрытие дропдауна
    document.getElementById('notificationsBtn')?.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('notificationsDropdown');
        dropdown.classList.toggle('show');
    });

    // Закрытие при клике вне области
    document.addEventListener('click', function(e) {
        const wrapper = document.querySelector('.notifications-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            document.getElementById('notificationsDropdown')?.classList.remove('show');
        }
    });

    // Отметить уведомление как прочитанное
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.closest('.notification-link')) return;
            const notifId = this.dataset.id;
            const link = this.dataset.link;
            
            if (notifId && !this.classList.contains('read')) {
                fetch('../mark_notification_read.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + notifId
                }).then(() => {
                    this.classList.add('read');
                    this.classList.remove('unread');
                    const badge = document.querySelector('.notifications-badge');
                    if (badge) {
                        let count = parseInt(badge.textContent) - 1;
                        if (count <= 0) badge.style.display = 'none';
                        else badge.textContent = count;
                    }
                });
            }
            if (link && link !== '') window.location.href = link;
        });
    });

    // Отметить все как прочитанные
    document.getElementById('markAllRead')?.addEventListener('click', function(e) {
        e.stopPropagation();
        fetch('../mark_all_notifications_read.php', { method: 'POST' })
            .then(() => location.reload());
    });

    // Периодическая проверка новых уведомлений (каждые 30 секунд)
    <?php if ($is_logged): ?>
    setInterval(function() {
        fetch('../check_notifications.php')
            .then(res => res.json())
            .then(data => {
                if (data.count > 0) {
                    const badge = document.querySelector('.notifications-badge');
                    if (badge) {
                        badge.textContent = data.count;
                        badge.style.display = 'flex';
                    }
                    const list = document.querySelector('.notifications-list');
                    if (list && data.html) {
                        list.innerHTML = data.html;
                    }
                }
            })
            .catch(err => console.error('Check notifications error:', err));
    }, 30000);
    <?php endif; ?>
    </script>
</body>
</html>