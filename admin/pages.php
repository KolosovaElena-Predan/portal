<?php
$activePage = 'pages';
$pageTitle = 'Управление страницами | Админ-панель';
require_once 'includes/auth_check.php';

$deleteSuccess = false;
if (isset($_GET['delete']) && filter_var($_GET['delete'], FILTER_VALIDATE_INT)) {
    $pdo->prepare("DELETE FROM pages WHERE id = ?")->execute([$_GET['delete']]);
    $deleteSuccess = true;
    header("Location: pages.php?deleted=1");
    exit;
}

$pages = $pdo->query("SELECT * FROM pages ORDER BY created_at DESC")->fetchAll();

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Страницы сайта</h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">Страница удалена</div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Список страниц</h3>
                    <a href="page_add.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Добавить страницу</a>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th>Название</th>
                                <th>Slug (URL)</th>
                                <th style="width: 10%">Статус</th>
                                <th style="width: 15%">Дата создания</th>
                                <th style="width: 12%">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pages): foreach ($pages as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
                                <td><code>/<?= htmlspecialchars($p['slug']) ?></code></td>
                                <td>
                                    <span class="badge badge-<?= match($p['status']) {
                                        'active' => 'success',
                                        'draft' => 'warning',
                                        'inactive' => 'danger'
                                    } ?>">
                                        <?= match($p['status']) {
                                            'active' => 'Активна',
                                            'draft' => 'Черновик',
                                            'inactive' => 'Неактивна'
                                        } ?>
                                    </span>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($p['created_at'])) ?></td>
                                <td>
                                    <a href="page_edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                    <a href="pages.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить страницу?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Страниц пока нет</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
<?php require_once 'includes/footer.php'; ?>