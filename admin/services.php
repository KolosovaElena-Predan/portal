<?php
$activePage = 'services';
$pageTitle = 'Услуги | Админ-панель';
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
    
    $validIds = array_filter($ids, function($id) {
        return filter_var($id, FILTER_VALIDATE_INT);
    });
    
    if (empty($validIds)) {
        echo json_encode(['success' => false, 'message' => 'Некорректные ID']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        $uploadBaseDir = __DIR__ . '/../mip/img/serv/';
        
        // Получаем изображения для удаления
        $placeholders = implode(',', array_fill(0, count($validIds), '?'));
        $stmt = $pdo->prepare("SELECT img_url FROM services WHERE id IN ($placeholders)");
        $stmt->execute($validIds);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($services as $service) {
            if (!empty($service['img_url'])) {
                $filePath = $uploadBaseDir . basename($service['img_url']);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }
        
        // Удаляем услуги
        $stmt = $pdo->prepare("DELETE FROM services WHERE id IN ($placeholders)");
        $stmt->execute($validIds);
        $deletedCount = $stmt->rowCount();
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => "Успешно удалено услуг: $deletedCount",
            'deleted_count' => $deletedCount,
            'deleted_ids' => $validIds
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Bulk delete error in services.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных при удалении']);
    }
    exit;
}

// ==========================================
// 2. ПОЛУЧЕНИЕ ДАННЫХ ДЛЯ ОТОБРАЖЕНИЯ
// ==========================================
try {
    $services = $pdo->query("SELECT * FROM services ORDER BY sort_order, id DESC")->fetchAll();
} catch (Exception $e) {
    $services = [];
    $dbError = "Ошибка загрузки данных: " . $e->getMessage();
}

$pageScript = '
$(document).ready(function() {
    const table = $("#servicesTable").DataTable({ 
        "responsive": true, 
        "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"},
        "columnDefs": [
            { "orderable": false, "targets": [0, 5] },
            { "className": "text-center", "targets": [0, 3, 4, 5] }
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
            showAlert("warning", "Выберите хотя бы одну услугу");
            return;
        }

        if (!confirm(`Удалить выбранные услуги (${selectedIds.length} шт.)?\nИзображения также будут удалены с сервера.`)) {
            return;
        }

        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.prop("disabled", true).html(\'<i class="fas fa-spinner fa-spin"></i> Удаление...\');

        $.ajax({
            url: "services.php",
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
                    
                    if ($("#servicesTable tbody tr:visible").length === 0) {
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
            <h1 class="m-0">Управление услугами</h1>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    Услуга успешно удалена.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    Произошла ошибка при удалении.
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Список услуг</h3>
                    <div>
                        <button id="bulkDeleteBtn" class="btn btn-danger btn-sm mr-2" disabled>
                            <i class="fas fa-trash"></i> Удалить выбранные (<span id="selectedCount">0</span>)
                        </button>
                        <a href="service_add.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Добавить
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <table id="servicesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">
                                    <input type="checkbox" id="selectAll" title="Выбрать все">
                                </th>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Цена</th>
                                <th style="width: 100px;">Изображение</th>
                                <th style="width: 100px;">Статус</th>
                                <th style="width: 120px;">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($services)): ?>
                                <tr><td colspan="7" class="text-center text-muted">Услуг пока нет</td></tr>
                            <?php else: ?>
                                <?php foreach ($services as $s): ?>
                                <tr data-id="<?= $s['id'] ?>">
                                    <td style="text-align: center;">
                                        <input type="checkbox" class="select-item" value="<?= $s['id'] ?>">
                                    </td>
                                    <td><?= $s['id'] ?></td>
                                    <td><strong><?= e(mb_strimwidth($s['name'], 0, 50, '...')) ?></strong></td>
                                    <td><?= number_format($s['price'], 0, '.', ' ') ?> ₽</td>
                                    <td class="text-center">
                                        <?php if (!empty($s['img_url'])): ?>
                                            <img src="../mip/<?= e($s['img_url']) ?>" alt="Превью" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;" loading="lazy">
                                        <?php else: ?>
                                            <div style="width: 60px; height: 60px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px dashed #ced4da; color: #adb5bd;">
                                                <i class="fas fa-image fa-lg"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-<?= $s['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $s['is_active'] ? 'Активна' : 'Скрыта' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="service_edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-primary" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../mip/service_view.php?id=<?= $s['id'] ?>" target="_blank" class="btn btn-sm btn-success" title="Открыть на сайте">
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