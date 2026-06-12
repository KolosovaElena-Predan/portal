<?php
// header.php — Единая шапка

if (!isset($context)) {
    $context = 'lab'; 
}

$BASE = '';
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

$show_cart = ($is_logged && $user_role === 'client' && $context === 'mip');

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

$lk_link = '#';
if ($context === 'mip') {
    switch ($user_role) {
        case 'admin': $lk_link = '/admin/lk_admin.php'; break;
        case 'support_specialist': $lk_link = '/lk_support.php'; break;
        case 'client': $lk_link = '/mip/lk_user.php'; break;
        default: $lk_link = '/mip/mip.php';
    }
} else {
    switch ($user_role) {
        case 'admin': $lk_link = '/admin/lk_admin.php'; break;
        case 'support_specialist': $lk_link = '/lk_support.php'; break;
        case 'client': $lk_link = '/lab/lk_lab.php'; break;
        default: $lk_link = '/lab/main_lab.php';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="css/header.css">
</head>
<body class="<?= $theme_class ?>">

<!-- ===== Мобильное меню ===== -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
<div class="mobile-menu-panel" id="mobileMenuPanel">
    <div class="mobile-menu-header">
        <span>Меню</span>
        <button class="mobile-menu-close-btn" id="mobileMenuClose"><i class="fas fa-times"></i></button>
    </div>
    
    <nav class="mobile-menu-links">
        <!-- Лаборатория ПЭТ -->
        <div class="mobile-menu-group">
            <div class="mobile-menu-group-header">
                <a href="<?= $lab_path ?>/main_lab.php" class="mobile-menu-group-title">Лаборатория ПЭТ</a>
                <button class="mobile-menu-group-toggle" data-section="lab">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="mobile-menu-submenu" id="lab-submenu">
                <a href="<?= $lab_path ?>/about.php">О нас</a>
                <a href="<?= $lab_path ?>/projects.php">Проекты</a>
                <a href="<?= $lab_path ?>/news.php">Новости</a>
                <a href="<?= $lab_path ?>/question.php">Контакты</a>
            </div>
        </div>

        <!-- ООО МИП -->
        <div class="mobile-menu-group">
            <div class="mobile-menu-group-header">
                <a href="<?= $mip_path ?>/mip.php" class="mobile-menu-group-title">ООО МИП</a>
                <button class="mobile-menu-group-toggle" data-section="mip">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="mobile-menu-submenu" id="mip-submenu">
                <a href="<?= $mip_path ?>/catalog.php">Каталог</a>
                <a href="<?= $mip_path ?>/services_catalog.php">Услуги</a>
                <a href="<?= $mip_path ?>/news.php">Новости</a>
                <a href="<?= $mip_path ?>/question.php">Контакты</a>
            </div>
        </div>
    </nav>
    
    <div class="mobile-menu-user-section">
        <?php if ($is_logged): ?>
            <a href="<?= $lk_link ?>">
                <i class="fas fa-user-circle"></i> <?= htmlspecialchars($user_name) ?>
            </a>
        <?php else: ?>
            <a href="<?= $BASE ?>/authorization.php">
                <i class="fas fa-sign-in-alt"></i> Войти
            </a>
        <?php endif; ?>
    </div>
</div>

<header class="portal-header">
    <!-- ВЕРХНЯЯ СТРОКА -->
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
                <div class="auth-info">
                    <?php if ($is_logged): ?>
                        <a href="<?= $lk_link ?>" class="btn-login">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($user_name) ?>
                        </a>
                    <?php else: ?>
                        <a href="<?= $BASE ?>/authorization.php" class="btn-login">
                            <i class="fas fa-sign-in-alt"></i> Войти
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- НИЖНЯЯ СТРОКА -->
    <div class="bottom-bar">
        <div class="bottom-bar-content">
            <button class="burger-menu-button" id="burgerMenuBtn">
                <i class="fas fa-bars"></i>
            </button>

            <div class="search-box-mobile" id="mobileSearchBox">
                <input type="text" placeholder="Поиск...">
                <button><i class="fas fa-search"></i></button>
            </div>

            <nav class="main-nav">
                <?php if ($context === 'lab'): ?>
                    <a href="<?= $lab_path ?>/about.php" class="nav-link">О нас</a>
                    <a href="<?= $lab_path ?>/projects.php" class="nav-link">Проекты</a>
                    <a href="<?= $lab_path ?>/news.php" class="nav-link">Новости</a>
                    <a href="<?= $lab_path ?>/question.php" class="nav-link">Контакты</a>
                <?php else: ?>
                    <a href="<?= $mip_path ?>/catalog.php" class="nav-link">Каталог</a>
                    <a href="<?= $mip_path ?>/services_catalog.php" class="nav-link">Услуги</a>
                    <a href="<?= $mip_path ?>/news.php" class="nav-link">Новости</a>
                    <a href="<?= $mip_path ?>/question.php" class="nav-link">Контакты</a>
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
document.addEventListener('DOMContentLoaded', function() {
    // Мобильное меню
    var burgerMenuBtn = document.getElementById('burgerMenuBtn');
    var mobileMenuPanel = document.getElementById('mobileMenuPanel');
    var mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
    var mobileMenuClose = document.getElementById('mobileMenuClose');

    function openMobileMenu() {
        if (mobileMenuPanel) mobileMenuPanel.classList.add('active');
        if (mobileMenuOverlay) mobileMenuOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeMobileMenu() {
        if (mobileMenuPanel) mobileMenuPanel.classList.remove('active');
        if (mobileMenuOverlay) mobileMenuOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (burgerMenuBtn) burgerMenuBtn.onclick = openMobileMenu;
    if (mobileMenuClose) mobileMenuClose.onclick = closeMobileMenu;
    if (mobileMenuOverlay) mobileMenuOverlay.onclick = closeMobileMenu;

    // Toggle для подменю в мобильном меню
    var toggles = document.querySelectorAll('.mobile-menu-group-toggle');
    for (var i = 0; i < toggles.length; i++) {
        toggles[i].onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            var group = this.closest('.mobile-menu-group');
            var submenu = group.querySelector('.mobile-menu-submenu');
            var icon = this.querySelector('i');
            
            if (submenu.classList.contains('open')) {
                submenu.classList.remove('open');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                submenu.classList.add('open');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        };
    }

    // По умолчанию открываем активную секцию
    var currentContext = '<?= $context ?>';
    if (currentContext === 'lab') {
        var labSubmenu = document.getElementById('lab-submenu');
        var labToggle = document.querySelector('[data-section="lab"]');
        if (labSubmenu && labToggle) {
            labSubmenu.classList.add('open');
            var labIcon = labToggle.querySelector('i');
            if (labIcon) {
                labIcon.classList.remove('fa-chevron-down');
                labIcon.classList.add('fa-chevron-up');
            }
        }
    } else {
        var mipSubmenu = document.getElementById('mip-submenu');
        var mipToggle = document.querySelector('[data-section="mip"]');
        if (mipSubmenu && mipToggle) {
            mipSubmenu.classList.add('open');
            var mipIcon = mipToggle.querySelector('i');
            if (mipIcon) {
                mipIcon.classList.remove('fa-chevron-down');
                mipIcon.classList.add('fa-chevron-up');
            }
        }
    }

    // Уведомления
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

    var notifItems = document.querySelectorAll('.notification-item-dropdown');
    for (var i = 0; i < notifItems.length; i++) {
        notifItems[i].onclick = function(e) {
            if (e.target.closest('.notification-link')) return;
            var notifId = this.dataset.id;
            var link = this.dataset.link;
            if (notifId && !this.classList.contains('read')) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/mark_notification_read.php', false);
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
            fetch('/mark_all_notifications_read.php', { method: 'POST' })
                .then(function() { location.reload(); })
                .catch(function() { console.error('Ошибка отметки всех прочитанными'); });
        };
    }

    // Мобильный поиск
    var mobileSearchBox = document.getElementById('mobileSearchBox');
    
    function openSearchModalFromMobile() {
        if (typeof window.openSearchModal === 'function') {
            window.openSearchModal();
        }
    }
    
    if (mobileSearchBox) {
        var mobileInput = mobileSearchBox.querySelector('input');
        var mobileBtn = mobileSearchBox.querySelector('button');
        if (mobileInput) {
            mobileInput.onclick = openSearchModalFromMobile;
            mobileInput.onkeydown = function(e) {
                e.preventDefault();
                openSearchModalFromMobile();
            };
        }
        if (mobileBtn) {
            mobileBtn.onclick = openSearchModalFromMobile;
        }
    }

    <?php if ($is_logged && $context === 'mip' && $user_role === 'client'): ?>
    setInterval(function() {
        fetch('/check_notifications.php')
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.count > 0) {
                    var badge = document.querySelector('.notifications-badge');
                    if (badge) {
                        badge.textContent = data.count;
                        badge.style.display = 'flex';
                    }
                    if (data.html) {
                        var list = document.querySelector('.notifications-list-dropdown');
                        if (list) list.innerHTML = data.html;
                    }
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
    var desktopSearchInput = document.querySelector('.top-actions .search-box input');
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
        fetch('/search_global.php?q=' + encodeURIComponent(query))
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
                                '<div class="search-result-desc">' + (item.content ? escapeHtml(item.content.substring(0,100)) : '') + '...</div>' +
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
                                '<div class="search-result-desc">' + (item.content ? escapeHtml(item.content.substring(0,100)) : '') + '...</div>' +
                            '</div>' +
                        '</a>';
                    }
                    html += '</div>';
                }
                searchResults.innerHTML = html;
            })
            .catch(function() { 
                searchResults.innerHTML = '<div class="search-empty">Ошибка поиска</div>'; 
            });
    }

    window.openSearchModal = function() {
        if (modal) {
            modal.classList.add('active');
            modal.style.display = 'flex';
            if (modalSearchInput) {
                var currentValue = desktopSearchInput ? desktopSearchInput.value : '';
                modalSearchInput.value = currentValue;
                modalSearchInput.focus();
                if (currentValue.length >= 2) performGlobalSearch(currentValue);
                else searchResults.innerHTML = '<div class="search-empty"><i class="fas fa-search"></i><p>Начните вводить поисковый запрос</p></div>';
            }
        }
    };

    function closeSearchModal() {
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
        }
    }

    if (desktopSearchInput) {
        desktopSearchInput.onclick = window.openSearchModal;
        desktopSearchInput.onkeydown = function(e) {
            e.preventDefault();
            window.openSearchModal();
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