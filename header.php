<?php
// header.php — Единая шапка

if (!isset($context)) {
    $context = 'lab'; 
}

$BASE = '/portal';
$lab_path = $BASE . '/lab';
$mip_path = $BASE . '/mip';

require_once __DIR__ . '/includes/track_visit.php';
if (isset($pdo)) trackVisit($pdo);

//session_start();
$is_logged = isset($_SESSION['user_id']);
$user_name = $_SESSION['name'] ?? $_SESSION['user_name'] ?? 'Гость';
$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['role'] ?? '';
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Проверяем, можно ли показывать корзину (только для client)
$show_cart = ($is_logged && $user_role === 'client' && $context === 'mip');

// Уведомления (только для выпадающего меню)
$unreadCount = 0;
$notifications_dropdown = [];

if ($is_logged && $context === 'mip' && $user_role === 'client') {
    if (file_exists(__DIR__ . '/includes/notifications.php')) {
        require_once __DIR__ . '/includes/notifications.php';
        global $pdo;
        $unreadCount = getUnreadCount($pdo, $user_id);
        $notifications_dropdown = getUnreadNotifications($pdo, $user_id);
    }
}

$theme_class = ($context === 'mip') ? 'theme-mip' : 'theme-lab';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <style>
        /* Все стили (уведомления, поиск, страница уведомлений) */
        .notifications-wrapper { position: relative; display: inline-block; }
        .notifications-btn { background: transparent; border: none; cursor: pointer; padding: 6px 10px; border-radius: 8px; position: relative; font-size: 20px; color: white; }
        .notifications-btn:hover { background: rgba(255,255,255,0.15); }
        .notifications-badge { position: absolute; top: -4px; right: -4px; background: #ff4757; color: white; font-size: 10px; border-radius: 50%; min-width: 17px; height: 17px; display: flex; align-items: center; justify-content: center; padding: 0 4px; }
        .notifications-dropdown { display: none; position: absolute; right: 0; top: 38px; width: 360px; max-height: 480px; background: white; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.15); z-index: 1000; overflow: hidden; flex-direction: column; }
        .notifications-dropdown.show { display: flex; }
        .notifications-header { display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; border-bottom: 1px solid #e0e8e5; font-weight: 600; background: white; color: #00302e; }
        .mark-read-btn { background: none; border: none; color: #00a896; cursor: pointer; font-size: 12px; }
        .notifications-list-dropdown { overflow-y: auto; max-height: 400px; }
        .notification-item-dropdown { display: flex; gap: 12px; padding: 14px 18px; border-bottom: 1px solid #f0f6f4; cursor: pointer; }
        .notification-item-dropdown.unread { background: #f0f9f7; border-left: 3px solid #00a896; }
        .notification-icon { width: 36px; height: 36px; background: #e0f7f4; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #00a896; font-size: 16px; }
        .notification-content-dropdown { flex: 1; }
        .notification-title-dropdown { font-weight: 600; font-size: 14px; color: #00302e; }
        .notification-message-dropdown { font-size: 13px; color: #4a6a65; }
        .notification-time-dropdown { font-size: 10px; color: #8aa9a3; margin-top: 5px; }
        .notification-empty { text-align: center; padding: 40px 20px; color: #8aa9a3; }
        .notifications-footer { padding: 12px 18px; border-top: 1px solid #e0e8e5; text-align: center; background: white; }
        .notifications-footer a { color: #00a896; text-decoration: none; font-size: 13px; }
        
        /* Модальное окно поиска */
        .search-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,48,46,0.9); z-index: 2000; justify-content: center; align-items: flex-start; padding-top: 100px; }
        .search-modal-overlay.active { display: flex; }
        .search-modal { width: 90%; max-width: 800px; background: white; border-radius: 20px; overflow: hidden; }
        .search-modal-header { padding: 20px 24px; background: #f0f6f4; display: flex; gap: 15px; }
        .search-modal-header input { flex: 1; padding: 14px 18px; border: 2px solid #e0e8e5; border-radius: 12px; font-size: 16px; }
        .search-modal-header input:focus { border-color: #00a896; outline: none; }
        .search-modal-header button { background: #00a896; border: none; padding: 14px 24px; border-radius: 12px; color: white; cursor: pointer; }
        .search-modal-header .close-btn { background: #e0e8e5; color: #4a6a65; }
        .search-modal-body { padding: 20px; max-height: 500px; overflow-y: auto; }
        .search-loading { text-align: center; padding: 40px; color: #00a896; }
        .search-results-section { margin-bottom: 25px; }
        .search-section-title { font-size: 16px; font-weight: 600; color: #00302e; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 2px solid #00a896; display: inline-block; }
        .search-result-item { display: block; padding: 12px 15px; border-radius: 12px; margin-bottom: 8px; text-decoration: none; background: #fafafc; }
        .search-result-item:hover { background: #e6f4f2; transform: translateX(5px); }
        .search-result-type { font-size: 11px; color: #00a896; margin-bottom: 4px; }
        .search-result-title { font-weight: 600; color: #00302e; }
        .search-result-desc { font-size: 13px; color: #4a6a65; }
        .search-result-price { font-weight: bold; color: #00a896; margin-top: 5px; }
        .search-empty { text-align: center; padding: 40px; color: #8aa9a3; }
        
        /* Страница всех уведомлений */
        .notifications-page { max-width: 750px; margin: 120px auto 60px; padding: 0 20px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
        .page-header h1 { font-size: 28px; font-weight: 700; color: #00302e; margin: 0; }
        .mark-all-link { background: #e0f7f4; color: #00a896; border: none; padding: 8px 18px; border-radius: 30px; font-size: 13px; cursor: pointer; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 25px; color: #00a896; text-decoration: none; font-size: 14px; }
        .notifications-list { display: flex; flex-direction: column; gap: 12px; }
        .notification-item-page { background: white; border-radius: 14px; padding: 18px 22px; cursor: pointer; box-shadow: 0 2px 6px rgba(0,168,150,0.05); border: 1px solid #e0e8e5; }
        .notification-item-page.unread { background: #f0f9f7; border-left: 4px solid #00a896; }
        .notification-item-page.read { opacity: 0.85; }
        .notification-item-page:hover { transform: translateX(5px); box-shadow: 0 6px 14px rgba(0,168,150,0.12); border-color: #00a896; }
        .notification-title-page { font-weight: 700; font-size: 16px; margin-bottom: 8px; color: #00302e; }
        .notification-message-page { font-size: 14px; color: #4a6a65; margin-bottom: 10px; line-height: 1.5; white-space: pre-line; }
        .notification-link-page { font-size: 13px; color: #00a896; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-weight: 500; }
        .notification-time-page { font-size: 11px; color: #8aa9a3; margin-top: 10px; display: flex; align-items: center; gap: 6px; }
        .empty-page { text-align: center; padding: 70px 20px; background: white; border-radius: 20px; color: #8aa9a3; border: 1px solid #e0e8e5; }
        
        @media (max-width: 600px) {
            .notifications-page { margin: 100px auto 40px; }
            .page-header h1 { font-size: 24px; }
            .notification-item-page { padding: 14px 18px; }
        }
    </style>
</head>
<body class="<?= $theme_class ?>">

<header class="portal-header">
    <div class="top-bar">
        <div class="top-bar-content">
            <div class="logos-group">
                <?php if ($context === 'lab'): ?>
                    <a href="<?= $lab_path ?>/main_lab.php" class="org-logo active">
                        <img src="<?= $lab_path ?>/img/image.png" alt="Лаб">
                        <span>Лаборатория ПЭТ</span>
                    </a>
                    <div class="divider"></div>
                    <a href="<?= $mip_path ?>/mip.php" class="org-logo">
                        <img src="<?= $mip_path ?>/img/logo_mip.png" alt="МИП">
                        <span>ООО МИП</span>
                    </a>
                <?php else: ?>
                    <a href="<?= $mip_path ?>/mip.php" class="org-logo active">
                        <img src="<?= $mip_path ?>/img/logo_mip.png" alt="МИП">
                        <span>ООО МИП</span>
                    </a>
                    <div class="divider"></div>
                    <a href="<?= $lab_path ?>/main_lab.php" class="org-logo">
                        <img src="<?= $lab_path ?>/img/image.png" alt="Лаб">
                        <span>Лаборатория ПЭТ</span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="top-actions">
                <div class="search-box">
                    <input type="text" placeholder="Поиск по порталу...">
                    <button><i class="fas fa-search"></i></button>
                </div>
                <?php if ($is_logged): ?>
                    <div class="auth-info">
                        <?php
                        // Определяем ссылку на личный кабинет в зависимости от роли и контекста
                        $lk_link = '#';
                        if ($context === 'mip') {
                            switch ($user_role) {
                                case 'admin':
                                    $lk_link = '/portal/admin/index.php';
                                    break;
                                case 'support_specialist':
                                    $lk_link = '/portal/lk_support.php';
                                    break;
                                case 'client':
                                    $lk_link = '/portal/mip/lk_user.php';
                                    break;
                                default:
                                    $lk_link = '/portal/mip/mip.php';
                            }
                        } else { // lab context
                            switch ($user_role) {
                                case 'admin':
                                    $lk_link = '/portal/admin/index.php';
                                    break;
                                case 'support_specialist':
                                    $lk_link = '/portal/lk_support.php';
                                    break;
                                case 'client':
                                    $lk_link = '/portal/lab/lk_lab.php';
                                    break;
                                default:
                                    $lk_link = '/portal/lab/main_lab.php';
                            }
                        }
                        ?>
                        <a href="<?= $lk_link ?>" class="btn-login">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($user_name) ?>
                            <?php if ($user_role === 'admin'): ?>
                                <!--<span style="font-size: 10px; background: #ff4757; padding: 2px 6px; border-radius: 10px; margin-left: 5px;">Admin</span>-->
                            <?php elseif ($user_role === 'support_specialist'): ?>
                                <!--<span style="font-size: 10px; background: #00a896; padding: 2px 6px; border-radius: 10px; margin-left: 5px;">Support</span>-->
                            <?php endif; ?>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="<?= $BASE ?>/authorization.php" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Войти
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bottom-bar">
        <div class="bottom-bar-content">
            <nav class="main-nav">
                <?php if ($context === 'lab'): ?>
                    <a href="<?= $lab_path ?>/about.php" class="nav-link">О нас</a>
                    <a href="<?= $lab_path ?>/projects.php" class="nav-link">Проекты</a>
                    <a href="<?= $lab_path ?>/services.php" class="nav-link">Услуги</a>
                    <a href="<?= $lab_path ?>/news.php" class="nav-link">Новости</a>
                    <a href="<?= $lab_path ?>/question.php" class="nav-link">Контакты</a>
                <?php else: ?>
                    <a href="<?= $mip_path ?>/catalog.php" class="nav-link">Каталог</a>
                    <a href="<?= $mip_path ?>/services_catalog.php" class="nav-link">Услуги</a>
                    <a href="<?= $mip_path ?>/news.php" class="nav-link">Новости</a>
                    <a href="<?= $mip_path ?>/question.php" class="nav-link">Поддержка</a>
                <?php endif; ?>
            </nav>

            <?php if ($context === 'mip'): ?>
                <div class="bottom-actions">
                    <div class="notifications-wrapper">
                        <button class="notifications-btn" id="notificationsBtn">
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
                            <div class="notifications-list-dropdown">
                                <?php if (empty($notifications_dropdown)): ?>
                                    <div class="notification-empty">Нет новых уведомлений</div>
                                <?php else: ?>
                                    <?php foreach ($notifications_dropdown as $notif): ?>
                                        <div class="notification-item-dropdown <?= $notif['is_read'] ? 'read' : 'unread' ?>" 
                                             data-id="<?= $notif['id'] ?>"
                                             data-link="<?= htmlspecialchars($notif['link'] ?? '') ?>">
                                            <div class="notification-icon"><i class="fas fa-bell"></i></div>
                                            <div class="notification-content-dropdown">
                                                <div class="notification-title-dropdown"><?= htmlspecialchars($notif['title']) ?></div>
                                                <div class="notification-message-dropdown"><?= htmlspecialchars($notif['message']) ?></div>
                                                <div class="notification-time-dropdown"><?= date('d.m.Y H:i', strtotime($notif['created_at'])) ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="notifications-footer">
                                <a href="<?= $BASE ?>/notifications.php">Все уведомления</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- КОРЗИНА - показываем только для авторизованных клиентов -->
                    <?php if ($show_cart): ?>
                        <a href="<?= $mip_path ?>/cart.php" class="cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-label">Корзина</span>
                            <?php if ($cart_count > 0): ?>
                                <span class="badge"><?= $cart_count ?></span>
                            <?php endif; ?>
                        </a>
                    <?php elseif (!$is_logged): ?>
                        <a href="<?= $BASE ?>/authorization.php?redirect=cart" class="cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-label">Корзина</span>
                            <?php if ($cart_count > 0): ?>
                                <span class="badge"><?= $cart_count ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="content-spacer"></div>

<script>
// Ждем полной загрузки DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен');
    
    // === УВЕДОМЛЕНИЯ ===
    var notifBtn = document.getElementById('notificationsBtn');
    var notifDropdown = document.getElementById('notificationsDropdown');
    var markAllBtn = document.getElementById('markAllRead');
    
    if (notifBtn && notifDropdown) {
        notifBtn.onclick = function(e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
        };
        
        document.onclick = function(e) {
            if (notifBtn && notifDropdown) {
                if (!notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
                    notifDropdown.classList.remove('show');
                }
            }
        };
    }
    
    // Обработка уведомлений
    var notifItems = document.querySelectorAll('.notification-item-dropdown');
    for (var i = 0; i < notifItems.length; i++) {
        notifItems[i].onclick = function(e) {
            if (e.target.closest('.notification-link')) return;
            var notifId = this.dataset.id;
            var link = this.dataset.link;
            if (notifId && !this.classList.contains('read')) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/portal/mark_notification_read.php', false);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send('id=' + notifId);
                this.classList.add('read');
                this.classList.remove('unread');
                var badge = document.querySelector('.notifications-badge');
                if (badge) {
                    var count = parseInt(badge.textContent) - 1;
                    if (count <= 0) badge.style.display = 'none';
                    else badge.textContent = count;
                }
            }
            if (link && link !== '') window.location.href = link;
        };
    }
    
    if (markAllBtn) {
        markAllBtn.onclick = function(e) {
            e.stopPropagation();
            fetch('/portal/mark_all_notifications_read.php', { method: 'POST' })
                .then(function() { location.reload(); });
        };
    }
    
    <?php if ($is_logged && $context === 'mip' && $user_role === 'client'): ?>
    setInterval(function() {
        fetch('/portal/check_notifications.php')
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.count > 0) {
                    var badge = document.querySelector('.notifications-badge');
                    if (badge) {
                        badge.textContent = data.count;
                        badge.style.display = 'flex';
                    }
                    var list = document.querySelector('.notifications-list-dropdown');
                    if (list && data.html) list.innerHTML = data.html;
                }
            })
            .catch(function(err) { console.error('Error:', err); });
    }, 30000);
    <?php endif; ?>
});

// Модальное окно поиска
(function() {
    var modalHTML = `
        <div id="searchModal" class="search-modal-overlay">
            <div class="search-modal">
                <div class="search-modal-header">
                    <input type="text" id="searchInputModal" placeholder="Поиск по порталу..." autocomplete="off">
                    <button id="searchCloseModal" class="close-btn">Отмена</button>
                </div>
                <div class="search-modal-body" id="searchResults">
                    <div class="search-empty"><i class="fas fa-search"></i><p>Начните вводить поисковый запрос</p></div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    var modal = document.getElementById('searchModal');
    var searchInput = document.querySelector('.search-box input');
    var modalSearchInput = document.getElementById('searchInputModal');
    var searchResults = document.getElementById('searchResults');
    var closeBtn = document.getElementById('searchCloseModal');
    var searchTimeout;
    
    function escapeHtml(text) { 
        var div = document.createElement('div'); 
        div.textContent = text; 
        return div.innerHTML; 
    }
    
    function performGlobalSearch(query) {
        if (query.length < 2) {
            searchResults.innerHTML = '<div class="search-empty"><i class="fas fa-search"></i><p>Введите минимум 2 символа</p></div>';
            return;
        }
        searchResults.innerHTML = '<div class="search-loading"><i class="fas fa-spinner fa-pulse"></i> Поиск...</div>';
        fetch('/portal/search_global.php?q=' + encodeURIComponent(query))
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.count === 0) {
                    searchResults.innerHTML = '<div class="search-empty">Ничего не найдено</div>';
                    return;
                }
                var html = '';
                var mipResults = data.results.filter(function(r) { return r.section === 'mip'; });
                var labResults = data.results.filter(function(r) { return r.section === 'lab'; });
                if (mipResults.length > 0) {
                    html += '<div class="search-results-section"><div class="search-section-title">МИП «НПЦ ПИТиА»</div>';
                    for (var i = 0; i < mipResults.length; i++) {
                        var item = mipResults[i];
                        var typeLabel = item.type === 'product' ? 'Товар' : (item.type === 'service' ? 'Услуга' : 'Новость');
                        var priceHtml = (item.price && item.type !== 'news') ? '<div class="search-result-price">' + Number(item.price).toLocaleString('ru-RU') + ' ₽</div>' : '';
                        html += '<a href="' + item.url + '" class="search-result-item">' +
                            '<div class="search-result-content">' +
                                '<div class="search-result-type">' + typeLabel + '</div>' +
                                '<div class="search-result-title">' + escapeHtml(item.name) + '</div>' +
                                '<div class="search-result-desc">' + escapeHtml(item.content.substring(0,100)) + '...</div>' +
                                priceHtml +
                            '</div>' +
                        '</a>';
                    }
                    html += '</div>';
                }
                if (labResults.length > 0) {
                    html += '<div class="search-results-section"><div class="search-section-title">Лаборатория ПЭТ</div>';
                    for (var i = 0; i < labResults.length; i++) {
                        var item = labResults[i];
                        var typeLabel = item.type === 'project' ? 'Проект' : (item.type === 'service' ? 'Услуга' : 'Новость');
                        html += '<a href="' + item.url + '" class="search-result-item">' +
                            '<div class="search-result-content">' +
                                '<div class="search-result-type">' + typeLabel + '</div>' +
                                '<div class="search-result-title">' + escapeHtml(item.name) + '</div>' +
                                '<div class="search-result-desc">' + escapeHtml(item.content.substring(0,100)) + '...</div>' +
                            '</div>' +
                        '</a>';
                    }
                    html += '</div>';
                }
                searchResults.innerHTML = html;
            })
            .catch(function() { searchResults.innerHTML = '<div class="search-empty">Ошибка поиска</div>'; });
    }
    
    function openSearchModal() {
        if (modal) {
            modal.classList.add('active');
            modal.style.display = 'flex';
            if (modalSearchInput) {
                modalSearchInput.value = searchInput ? searchInput.value : '';
                modalSearchInput.focus();
                if (modalSearchInput.value.length >= 2) performGlobalSearch(modalSearchInput.value);
            }
        }
    }
    
    function closeSearchModal() { 
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
        }
    }
    
    if (searchInput) {
        searchInput.onclick = openSearchModal;
        searchInput.onkeydown = function(e) { 
            e.preventDefault(); 
            openSearchModal(); 
        };
    }
    if (closeBtn) closeBtn.onclick = closeSearchModal;
    if (modal) {
        modal.onclick = function(e) { 
            if (e.target === modal) closeSearchModal(); 
        };
    }
    if (modalSearchInput) {
        modalSearchInput.oninput = function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() { performGlobalSearch(modalSearchInput.value.trim()); }, 300);
        };
        modalSearchInput.onkeypress = function(e) {
            if (e.key === 'Enter') { 
                e.preventDefault(); 
                performGlobalSearch(modalSearchInput.value.trim()); 
            }
        };
    }
    document.onkeydown = function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) closeSearchModal();
    };
})();
</script>
</body>
</html>