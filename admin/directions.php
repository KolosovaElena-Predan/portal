<?php
$activePage = 'directions';
$pageTitle = 'Направления | Админ-панель';
require_once 'includes/auth_check.php';

if (isset($_GET['delete']) && filter_var($_GET['delete'], FILTER_VALIDATE_INT)) {
    $pdo->prepare("DELETE FROM directions WHERE id = ?")->execute([(int)$_GET['delete']]);
    header("Location: directions.php?deleted=1");
    exit;
}

$directions = $pdo->query("SELECT * FROM directions ORDER BY sort_order, id ASC")->fetchAll();
$pageScript = '$("#directionsTable").DataTable({ "responsive": true, "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"} });';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Направления работы</h1></div></div>
    <section class="content">
        <div class="container-fluid">
            <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Направление удалено</div><?php endif; ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Список направлений</h3>
                    <a href="direction_add.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Добавить</a>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="directionsTable" class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Описание</th>
                                <th>Порядок</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($directions as $d): ?>
                            <tr>
                                <td><?= $d['id'] ?></td>
                                <td><strong><?= e($d['name']) ?></strong></td>
                                <td><?= e(mb_strimwidth($d['description'] ?? '', 0, 150, '...')) ?></td>
                                <td><?= $d['sort_order'] ?? 0 ?></td>
                                <td>
                                    <a href="direction_edit.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                    <a href="directions.php?delete=<?= $d['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')"><i class="fas fa-trash"></i></a>
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