<?php
$activePage = 'services';
$pageTitle = 'Услуги | Админ-панель';

require_once 'includes/auth_check.php';

// Получаем услуги
try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY sort_order, id DESC");
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка БД: " . $e->getMessage());
}

$pageScript = '$("#servicesTable").DataTable({ "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"} });';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Управление услугами</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Список услуг</h3>
                    <a href="service_add.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Добавить
                    </a>
                </div>
                <div class="card-body">
                    <table id="servicesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Цена</th>
                                <th>Длительность</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $s): ?>
                            <tr>
                                <td><?= $s['id'] ?></td>
                                <td><?= e($s['name']) ?></td>
                                <td><?= number_format($s['price'], 0, '.', ' ') ?> ₽</td>
                                <td><?= e($s['duration'] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge-<?= $s['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $s['is_active'] ? 'Активна' : 'Скрыта' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="service_edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="service_delete.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Удалить услугу?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>