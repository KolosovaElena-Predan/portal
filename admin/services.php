<?php
$activePage = 'services';
$pageTitle = 'Услуги | Админ-панель';
require_once 'includes/auth_check.php';

$services = $pdo->query("SELECT * FROM services ORDER BY sort_order, id DESC")->fetchAll();
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
                    <a href="service_add.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Добавить</a>
                </div>
                <div class="card-body">
                    <table id="servicesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width:50px">ID</th>
                                <th>Название</th>
                                <th style="width:100px">Цена</th>
                                <th style="width:100px">Изображение</th>
                                <th style="width:100px">Статус</th>
                                <th style="width:140px">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($services)): ?>
                                <tr><td colspan="6" class="text-center text-muted">Услуг пока нет</td></tr>
                            <?php else: ?>
                                <?php foreach ($services as $s): ?>
                                <tr>
                                    <td><?= $s['id'] ?></td>
                                    <td><?= e(mb_strimwidth($s['name'], 0, 50, '...')) ?></td>
                                    <td><?= number_format($s['price'], 0, '.', ' ') ?> ₽</td>
                                    <td>
                                        <?php if (!empty($s['img_url'])): ?>
                                            <img src="../mip/<?= e($s['img_url']) ?>" alt="Превью" style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6" loading="lazy">
                                        <?php else: ?>
                                            <div style="width:60px;height:60px;background:#f8f9fa;display:flex;align-items:center;justify-content:center;border-radius:6px;border:1px dashed #ced4da;color:#adb5bd"><i class="fas fa-image fa-lg"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $s['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $s['is_active'] ? 'Активна' : 'Скрыта' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="service_edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-primary" title="Редактировать"><i class="fas fa-edit"></i></a>
                                        <a href="../mip/service_view.php?id=<?= $s['id'] ?>" target="_blank" class="btn btn-sm btn-success" title="Открыть на сайте"><i class="fas fa-eye"></i></a>
                                        <a href="service_delete.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить услугу?')" title="Удалить"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>