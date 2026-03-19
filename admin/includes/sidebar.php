<?php
/**
 * Левое боковое меню
 * $activePage - имя текущей страницы (например: 'products', 'services')
 */
$activePage = $activePage ?? '';
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="lk_admin.php" class="brand-link">
        <span class="brand-text font-weight-light">Админка МИП</span>
    </a>
    
    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <a href="#" class="d-block"><?= e($_SESSION['name'] ?? 'Админ') ?></a>
            </div>
        </div>
        
        <!-- Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                <li class="nav-item">
                    <a href="lk_admin.php" class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Главная</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="products.php" class="nav-link <?= $activePage === 'products' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-box"></i>
                        <p>Товары</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="services.php" class="nav-link <?= $activePage === 'services' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-concierge-bell"></i>
                        <p>Услуги</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="news.php" class="nav-link <?= $activePage === 'news' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-newspaper"></i>
                        <p>Новости</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link <?= $activePage === 'users' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Пользователи</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="requests.php" class="nav-link <?= $activePage === 'requests' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Заявки</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>