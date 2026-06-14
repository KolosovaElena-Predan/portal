<?php
$activePage = 'news';
$pageTitle = 'Новости | Админ-панель';

require_once 'includes/auth_check.php';

// Получаем данные новостей
try {
    $news = $pdo->query("
        SELECT n.*, 
               COUNT(ni.id) as images_count, 
               ni_main.image_url as main_image
        FROM news n 
        LEFT JOIN news_images ni ON n.id = ni.news_id 
        LEFT JOIN news_images ni_main ON n.id = ni_main.news_id AND ni_main.is_main = 1
        GROUP BY n.id 
        ORDER BY n.datetime DESC
    ")->fetchAll();
} catch (PDOException $e) {
    $news = [];
    error_log("News fetch error: " . $e->getMessage());
}

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
                    <a href="news_add.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Добавить
                    </a>
                </div>
                <div class="card-body">
                    <table id="newsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th>Заголовок</th>
                                <th style="width: 120px;">Дата</th>
                                <th style="width: 100px;">Главное фото</th>
                                <th style="width: 60px;">Всего</th>
                                <th style="width: 140px;">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($news)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Новостей пока нет
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($news as $n): ?>
                                    <tr>
                                        <td><?= $n['id'] ?></td>
                                        <td><?= e(mb_strimwidth($n['title'], 0, 50, '...')) ?></td>
                                        <td><?= date('d.m.Y H:i', strtotime($n['datetime'])) ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($n['main_image'])): ?>
                                                <img src="../<?= e($n['main_image']) ?>" 
                                                     alt="Превью" 
                                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;"
                                                     loading="lazy">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 60px; background: #f8f9fa; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px dashed #ced4da; color: #adb5bd;">
                                                    <i class="fas fa-image fa-lg"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?= $n['images_count'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="news_edit.php?id=<?= $n['id'] ?>" class="btn btn-sm btn-primary" title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="../<?= $n['section'] ?>/news_view.php?id=<?= $n['id'] ?>" target="_blank" class="btn btn-sm btn-success" title="Открыть на сайте">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="news_delete.php?id=<?= $n['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить новость и все изображения?')" title="Удалить">
                                                <i class="fas fa-trash"></i>
                                            </a>
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

<script>
$(document).ready(function() {
    // Инициализируем DataTables только если есть данные (нет строки с colspan)
    var $table = $("#newsTable");
    var $tbody = $table.find("tbody");
    var hasRealData = $tbody.find("tr").length > 0 && $tbody.find("td[colspan]").length === 0;
    
    if (hasRealData) {
        $table.DataTable({ 
            "order": [[0, "desc"]], 
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"
            },
            "pageLength": 10,
            "responsive": true
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>