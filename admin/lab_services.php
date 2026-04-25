<?php
$activePage = 'lab_services';
$pageTitle = 'Услуги лаборатории | Админ-панель';
require_once 'includes/auth_check.php';

if (isset($_GET['delete']) && filter_var($_GET['delete'], FILTER_VALIDATE_INT)) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT img_url FROM lab_services WHERE id = ?");
    $stmt->execute([$id]);
    $s = $stmt->fetch();
    if ($s && $s['img_url']) {
        $path = __DIR__ . '/../mip/' . $s['img_url'];
        if (file_exists($path)) @unlink($path);
    }
    $pdo->prepare("DELETE FROM lab_services WHERE id = ?")->execute([$id]);
    header("Location: lab_services.php?deleted=1");
    exit;
}

$services = $pdo->query("SELECT * FROM lab_services ORDER BY sort_order, id ASC")->fetchAll();
$pageScript = '$("#labServicesTable").DataTable({ "responsive": true, "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"} });';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Услуги лаборатории</h1></div></div>
    <section class="content">
        <div class="container-fluid">
            <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Услуга удалена</div><?php endif; ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Список услуг</h3>
                    <a href="lab_service_add.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Добавить</a>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="labServicesTable" class="table table-hover table-bordered">
                        <thead><tr><th>ID</th><th>Название</th><th>Цена</th><th>Срок</th><th>Статус</th><th>Порядок</th><th>Действия</th></tr></thead>
                        <tbody>
                            <?php foreach ($services as $s): ?>
                            <tr>
                                <td><?= $s['id'] ?></td>
                                <td><strong><?= e($s['name']) ?></strong><br><small class="text-muted"><?= e(mb_strimwidth($s['description'] ?? '', 0, 80, '...')) ?></small></td>
                                <td><?= $s['price'] ? number_format($s['price'], 0, '.', ' ') . ' ₽' : 'Договорная' ?></td>
                                <td><?= e($s['duration'] ?? '—') ?></td>
                                <td><span class="badge badge-<?= $s['is_active'] ? 'success' : 'secondary' ?>"><?= $s['is_active'] ? 'Активна' : 'Скрыта' ?></span></td>
                                <td><?= $s['sort_order'] ?? 0 ?></td>
                                <td>
                                    <a href="lab_service_edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                    <a href="lab_services.php?delete=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')"><i class="fas fa-trash"></i></a>
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