<?php
// ВРЕМЕННО для отладки - удалить перед продакшеном!
ini_set('display_errors', 1);
error_reporting(E_ALL);

$activePage = 'news';
$pageTitle = 'Новости | Админ-панель';
require_once 'includes/auth_check.php';

// ==========================================
// 1. ОБРАБОТКА МАССОВОГО УДАЛЕНИЯ (AJAX)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_delete') {
    header('Content-Type: application/json');
    
    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'Не выбраны записи']);
        exit;
    }
    
    // ИСПРАВЛЕНО: array_values сбрасывает ключи массива
    $validIds = array_values(array_filter($ids, function($id) {
        return filter_var($id, FILTER_VALIDATE_INT);
    }));
    
    if (empty($validIds)) {
        echo json_encode(['success' => false, 'message' => 'Некорректные ID']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        $uploadBaseDir = __DIR__ . '/../img/news/';
        
        // Получаем все изображения для удаления
        $placeholders = implode(',', array_fill(0, count($validIds), '?'));
        $stmt = $pdo->prepare("SELECT ni.image_url FROM news_images ni WHERE ni.news_id IN ($placeholders)");
        $stmt->execute($validIds);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($images as $img) {
            if (!empty($img['image_url'])) {
                $filePath = $uploadBaseDir . basename($img['image_url']);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }
        
        // Удаляем новости (каскадно удалятся и изображения из БД)
        $stmt = $pdo->prepare("DELETE FROM news WHERE id IN ($placeholders)");
        $stmt->execute($validIds);
        $deletedCount = $stmt->rowCount();
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => "Успешно удалено новостей: $deletedCount",
            'deleted_count' => $deletedCount,
            'deleted_ids' => $validIds
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Bulk delete error in news.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных при удалении']);
    }
    exit;
}

// ==========================================
// 2. ПОЛУЧЕНИЕ ДАННЫХ ДЛЯ ОТОБРАЖЕНИЯ
// ==========================================
try {
    // ИСПРАВЛЕНО: MAX() для корректной работы с ONLY_FULL_GROUP_BY
    $news = $pdo->query("
        SELECT n.*,
            COUNT(ni.id) as images_count,
            MAX(ni_main.image_url) as main_image
        FROM news n
        LEFT JOIN news_images ni ON n.id = ni.news_id
        LEFT JOIN news_images ni_main ON n.id = ni_main.news_id AND ni_main.is_main = 1
        GROUP BY n.id
        ORDER BY n.datetime DESC
    ")->fetchAll();
} catch (Exception $e) {
    $news = [];
    $dbError = "Ошибка загрузки данных: " . $e->getMessage();
}

$pageScript = '
$(document).ready(function() {
    const table = $("#newsTable").DataTable({ 
        "order": [[1, "desc"]],
        "responsive": true, 
        "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"},
        "columnDefs": [
            { "orderable": false, "targets": [0, 7] },
            { "className": "text-center", "targets": [0, 5, 6, 7] }
        ],
        "drawCallback": function() {
            updateSelection();
        }
    });

    function updateSelection() {
        const checkedCount = $(".select-item:checked").length;
        $("#selectedCount").text(checkedCount);
        $("#bulkDeleteBtn").prop("disabled", checkedCount === 0);
        
        const totalItems = $(".select-item").length;
        const $selectAll = $("#selectAll");
        if (totalItems > 0) {
            $selectAll.prop("checked", checkedCount === totalItems && checkedCount > 0);
            $selectAll.prop("indeterminate", checkedCount > 0 && checkedCount < totalItems);
        } else {
            $selectAll.prop("checked", false);
            $selectAll.prop("indeterminate", false);
        }
    }

    $(document).on("change", "#selectAll", function() {
        $(".select-item").prop("checked", this.checked);
        updateSelection();
    });

    $(document).on("change", ".select-item", function() {
        updateSelection();
    });

    function showAlert(type, message) {
        $(".custom-alert").remove();
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show custom-alert" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        $("body").append(alertHtml);
        setTimeout(() => {
            $(".custom-alert").fadeOut(300, function() { $(this).remove(); });
        }, 4000);
    }

    // Массовое удаление
    $("#bulkDeleteBtn").off("click").on("click", function() {
        const selectedIds = $(".select-item:checked").map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            showAlert("warning", "Выберите хотя бы одну новость");
            return;
        }

        if (!confirm(`Удалить выбранные новости (${selectedIds.length} шт.)?\nИзображения также будут удалены с сервера.`)) {
            return;
        }

        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.prop("disabled", true).html(\'<i class="fas fa-spinner fa-spin"></i> Удаление...\');

        $.ajax({
            url: "news.php",
            method: "POST",
            data: {
                action: "bulk_delete",
                ids: selectedIds
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    if (response.deleted_ids && response.deleted_ids.length) {
                        response.deleted_ids.forEach(function(id) {
                            const $row = $(\'tr[data-id="\' + id + \'"]\');
                            if ($row.length) {
                                $row.fadeOut(300, function() {
                                    const rowNode = $row[0];
                                    $(this).remove();
                                    if (table && table.row) {
                                        table.row(rowNode).remove().draw();
                                    }
                                });
                            }
                        });
                    }
                    
                    $(".select-item").prop("checked", false);
                    $("#selectAll").prop("checked", false);
                    updateSelection();

                    showAlert("success", response.message);
                    
                    if ($("#newsTable tbody tr:visible").length === 0) {
                        location.reload();
                    }
                } else {
                    showAlert("danger", response.message || "Ошибка при удалении");
                }
                $btn.prop("disabled", false).html(originalHtml);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
                showAlert("danger", "Ошибка соединения с сервером");
                $btn.prop("disabled", false).html(originalHtml);
            }
        });
    });
    
    updateSelection();
});
';

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
            
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    Новость успешно удалена.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    Произошла ошибка при удалении.
                </div>
            <?php endif; ?>
            
            <?php if (isset($dbError)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= htmlspecialchars($dbError) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Список новостей</h3>
                    <div>
                        <button id="bulkDeleteBtn" class="btn btn-danger btn-sm mr-2" disabled>
                            <i class="fas fa-trash"></i> Удалить выбранные (<span id="selectedCount">0</span>)
                        </button>
                        <a href="news_add.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Добавить
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <table id="newsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">
                                    <input type="checkbox" id="selectAll" title="Выбрать все">
                                </th>
                                <th>ID</th>
                                <th>Заголовок</th>
                                <th>Раздел</th>
                                <th style="width: 120px;">Дата</th>
                                <th style="width: 100px;">Главное фото</th>
                                <th style="width: 60px;">Всего</th>
                                <th style="width: 120px;">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($news)): ?>
                                <?php foreach ($news as $n): ?>
                                <tr data-id="<?= $n['id'] ?>">
                                    <td style="text-align: center;">
                                        <input type="checkbox" class="select-item" value="<?= $n['id'] ?>">
                                    </td>
                                    <td><?= $n['id'] ?></td>
                                    <td><strong><?= e(mb_strimwidth($n['title'], 0, 50, '...')) ?></strong></td>
                                    <td>
                                        <span class="badge badge-<?= $n['section'] === 'mip' ? 'primary' : 'info' ?>">
                                            <?= $n['section'] === 'mip' ? 'МИП' : 'Лаборатория' ?>
                                        </span>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($n['datetime'])) ?></td>
                                    <td>
                                        <?php if (!empty($n['main_image'])): ?>
                                            <img src="../<?= e($n['main_image']) ?>" alt="Превью" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;" loading="lazy">
                                        <?php else: ?>
                                            <div style="width: 60px; height: 60px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px dashed #ced4da; color: #adb5bd;">
                                                <i class="fas fa-image fa-lg"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><span class="badge badge-info"><?= $n['images_count'] ?></span></td>
                                    <td class="text-center">
                                        <a href="news_edit.php?id=<?= $n['id'] ?>" class="btn btn-sm btn-primary" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../<?= e($n['section']) ?>/news_view.php?id=<?= $n['id'] ?>" target="_blank" class="btn btn-sm btn-success" title="Открыть на сайте">
                                            <i class="fas fa-eye"></i>
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

<?php require_once 'includes/footer.php'; ?>