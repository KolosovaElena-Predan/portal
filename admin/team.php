<?php
$activePage = 'team';
$pageTitle = 'Команда | Админ-панель';
require_once 'includes/auth_check.php';

// Удаление
if (isset($_GET['delete']) && filter_var($_GET['delete'], FILTER_VALIDATE_INT)) {
    $id = (int)$_GET['delete'];
    // Удаляем фото с диска
    $stmt = $pdo->prepare("SELECT photo_url FROM team WHERE id = ?");
    $stmt->execute([$id]);
    $member = $stmt->fetch();
    if ($member && $member['photo_url']) {
        $filePath = __DIR__ . '/../lab/img/team/' . basename($member['photo_url']);
        if (file_exists($filePath)) @unlink($filePath);
    }
    $pdo->prepare("DELETE FROM team WHERE id = ?")->execute([$id]);
    header("Location: team.php?deleted=1");
    exit;
}

$members = $pdo->query("SELECT * FROM team ORDER BY sort_order, id ASC")->fetchAll();
$pageScript = '$("#teamTable").DataTable({ "responsive": true, "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"} });';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Команда лаборатории</h1></div></div>
    <section class="content">
        <div class="container-fluid">
            <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Сотрудник удален</div><?php endif; ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Список сотрудников</h3>
                    <a href="team_add.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Добавить</a>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="teamTable" class="table table-hover table-bordered">
                        <thead><tr><th>ID</th><th>Фото</th><th>ФИО</th><th>Должность</th><th>Контакты</th><th>Порядок</th><th>Действия</th></tr></thead>
                        <tbody>
                            <?php foreach ($members as $m): ?>
                            <tr>
                                <td><?= $m['id'] ?></td>
                                <td>
                                    <?php if ($m['photo_url']): ?>
                                        <img src="../lab/<?= e($m['photo_url']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= e($m['name']) ?></strong></td>
                                <td><?= e($m['position']) ?><br><small class="text-muted"><?= e($m['education'] ?? '') ?></small></td>
                                <td>
                                    <?php if ($m['email']): ?><small><i class="fas fa-envelope"></i> <?= e($m['email']) ?></small><br><?php endif; ?>
                                    <?php if ($m['phone']): ?><small><i class="fas fa-phone"></i> <?= e($m['phone']) ?></small><?php endif; ?>
                                </td>
                                <td><?= $m['sort_order'] ?? 0 ?></td>
                                <td>
                                    <a href="team_edit.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                    <a href="team.php?delete=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить сотрудника?')"><i class="fas fa-trash"></i></a>
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