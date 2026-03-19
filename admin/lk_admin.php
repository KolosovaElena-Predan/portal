<?php
$activePage = 'dashboard';
$pageTitle = 'Главная | Админ-панель';

require_once 'includes/auth_check.php';

// Статистика
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn(),
    'requests' => $pdo->query("SELECT COUNT(*) FROM request WHERE status = 'new'")->fetchColumn(),
    'products' => $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn(),
    'services' => $pdo->query("SELECT COUNT(*) FROM services WHERE is_active = 1")->fetchColumn(),
];

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Панель управления</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $stats['users'] ?></h3>
                            <p>Пользователей</p>
                        </div>
                        <a href="users.php" class="small-box-footer">Подробнее <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $stats['requests'] ?></h3>
                            <p>Новых заявок</p>
                        </div>
                        <a href="requests.php" class="small-box-footer">Подробнее <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $stats['products'] ?></h3>
                            <p>Товаров</p>
                        </div>
                        <a href="products.php" class="small-box-footer">Подробнее <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $stats['services'] ?></h3>
                            <p>Услуг</p>
                        </div>
                        <a href="services.php" class="small-box-footer">Подробнее <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>