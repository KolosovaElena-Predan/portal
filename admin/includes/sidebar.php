<?php
/**
 * Левое боковое меню
 * $activePage - имя текущей страницы (например: 'products', 'services')
 */
$activePage = $activePage ?? '';
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="lk_admin.php" class="brand-link">
        <span class="brand-text font-weight-light">Админ-панель</span>
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
                        
                        <p>Главная</p>
                    </a>
                </li>
                <!-- Товары -->
				<li class="nav-item has-treeview <?= $activePage === 'products' || $activePage === 'categories' ? 'menu-open' : '' ?>">
					<a href="#" class="nav-link <?= $activePage === 'products' || $activePage === 'categories' ? 'active' : '' ?>">
						
						<p>
							Товары
							<i class="right fas fa-angle-left"></i>
						</p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item">
							<a href="products.php" class="nav-link <?= $activePage === 'products' ? 'active' : '' ?>">
								
								<p>Список товаров</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="categories.php" class="nav-link <?= $activePage === 'categories' ? 'active' : '' ?>">
								
								<p>Категории</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="services_products_links.php" class="nav-link <?= $activePage === 'services_products_links' ? 'active' : '' ?>">
								<i class="nav-icon fas fa-link"></i>
								<p>Связь товаров и услуг</p>
							</a>
						</li>
						<li class="nav-item">
    <a href="request_statuses.php" class="nav-link <?= $activePage === 'request_statuses' ? 'active' : '' ?>">
        <i class="fas fa-tags nav-icon"></i>
        <p>Статусы заявок</p>
    </a>
</li>
					</ul>
				</li>
                <li class="nav-item">
                    <a href="services.php" class="nav-link <?= $activePage === 'services' ? 'active' : '' ?>">
                        
                        <p>Услуги</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="news.php" class="nav-link <?= $activePage === 'news' ? 'active' : '' ?>">
                        
                        <p>Новости</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link <?= $activePage === 'users' ? 'active' : '' ?>">
                        
                        <p>Пользователи</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="requests.php" class="nav-link <?= $activePage === 'requests' ? 'active' : '' ?>">
                        
                        <p>Заявки</p>
                    </a>
                </li>
				<!-- О лаборатории -->
				<li class="nav-item has-treeview <?= in_array($activePage, ['team', 'lab_projects', 'directions', 'lab_services']) ? 'menu-open' : '' ?>">
					<a href="#" class="nav-link <?= in_array($activePage, ['team', 'lab_projects', 'directions', 'lab_services']) ? 'active' : '' ?>">
						
						<p>
							О лаборатории
							
						</p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item"><a href="team.php" class="nav-link <?= $activePage === 'team' ? 'active' : '' ?>"><p>Команда</p></a></li>
						<li class="nav-item"><a href="lab_projects.php" class="nav-link <?= $activePage === 'lab_projects' ? 'active' : '' ?>"><p>Проекты</p></a></li>
						<li class="nav-item"><a href="directions.php" class="nav-link <?= $activePage === 'directions' ? 'active' : '' ?>"><p>Направления</p></a></li>
						<li class="nav-item"><a href="lab_services.php" class="nav-link <?= $activePage === 'lab_services' ? 'active' : '' ?>"><p>Услуги</p></a></li>
					</ul>
				</li>
				<!-- В конце меню, перед закрывающим </ul> -->
				<li class="nav-item">
					<a href="analytics.php" class="nav-link <?= $activePage === 'analytics' ? 'active' : '' ?>">
						
						<p>Статистика</p>
					</a>
				</li>

            </ul>
        </nav>
    </div>
</aside>