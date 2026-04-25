<?php
$activePage = 'lab_projects';
$pageTitle = 'Проекты | Админ-панель';
require_once 'includes/auth_check.php';

if (isset($_GET['delete']) && filter_var($_GET['delete'], FILTER_VALIDATE_INT)) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT img_url FROM lab_projects WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if ($p && $p['img_url']) {
        $path = __DIR__ . '/../lab/' . $p['img_url'];
        if (file_exists($path)) @unlink($path);
    }
    $pdo->prepare("DELETE FROM lab_projects WHERE id = ?")->execute([$id]);
    header("Location: lab_projects.php?deleted=1");
    exit;
}

$projects = $pdo->query("SELECT * FROM lab_projects ORDER BY start_date DESC")->fetchAll();
$pageScript = '$("#projectsTable").DataTable({ "responsive": true, "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"} });';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Проекты лаборатории</h1></div></div>
    <section class="content">
        <div class="container-fluid">
            <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Проект удален</div><?php endif; ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Список проектов</h3>
                    <a href="lab_project_add.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Добавить</a>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="projectsTable" class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Фото</th>
                                <th>Название</th>
                                <th>Период</th>
                                <th>Статус</th>
                                <th>Бюджет</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <!-- ✅ Колонка с изображением -->
                                <td>
                                    <?php if (!empty($p['img_url'])): ?>
                                        <img src="../lab/<?= e($p['img_url']) ?>" 
                                             alt="<?= e($p['name']) ?>" 
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;" 
                                             loading="lazy">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px dashed #ced4da; color: #adb5bd;">
                                            <i class="fas fa-image fa-lg"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= e($p['name']) ?></strong><br>
                                    <small class="text-muted"><?= e(mb_strimwidth($p['short_description'] ?? '', 0, 80, '...')) ?></small>
                                </td>
                                <td>
                                    <?= $p['start_date'] ? date('d.m.Y', strtotime($p['start_date'])) : '—' ?> — 
                                    <?= $p['end_date'] ? date('d.m.Y', strtotime($p['end_date'])) : '...' ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= ['active'=>'success','completed'=>'secondary','planned'=>'info'][$p['status']] ?? 'info' ?>">
                                        <?= e($p['status']) ?>
                                    </span>
                                </td>
                                <td><?= $p['budget'] ? number_format($p['budget'], 0, '.', ' ') . ' ₽' : '—' ?></td>
                                <td>
                                    <a href="lab_project_edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                    <a href="lab_projects.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить проект?')"><i class="fas fa-trash"></i></a>
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