<?php
$activePage = 'news';
$pageTitle = 'Новости | Админ-панель';

require_once 'includes/auth_check.php';

$news = $pdo->query("SELECT n.*, COUNT(ni.id) as images_count FROM news n LEFT JOIN news_images ni ON n.id = ni.news_id GROUP BY n.id ORDER BY n.datetime DESC")->fetchAll();

$pageScript = '$("#newsTable").DataTable({ "order": [[0, "desc"]], "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"} });';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Управление новостями</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Список новостей</h3>
                    <a href="news_add.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Добавить</a>
                </div>
                <div class="card-body">
                    <table id="newsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr><th>ID</th><th>Заголовок</th><th>Дата</th><th>Изображения</th><th>Действия</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($news as $n): ?>
                            <tr>
                                <td><?= $n['id'] ?></td>
                                <td><?= e(mb_strimwidth($n['title'], 0, 50, '...')) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($n['datetime'])) ?></td>
                                <td><span class="badge badge-info"><?= $n['images_count'] ?></span></td>
                                <td>
                                    <a href="news_edit.php?id=<?= $n['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                    <a href="news_delete.php?id=<?= $n['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')"><i class="fas fa-trash"></i></a>
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