<?php
$activePage = 'categories';
$pageTitle = 'Категории | Админ-панель';
require_once 'includes/auth_check.php';
$error = '';
$success = '';

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
        
        $placeholders = implode(',', array_fill(0, count($validIds), '?'));
        
        // Проверяем, есть ли у категорий товары
        $stmt = $pdo->prepare("
            SELECT c.id, COUNT(p.id) as products_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            WHERE c.id IN ($placeholders) 
            GROUP BY c.id
        ");
        $stmt->execute($validIds);
        $categoriesWithProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $deletableIds = [];
        $blockedIds = [];
        
        foreach ($categoriesWithProducts as $cat) {
            if ($cat['products_count'] > 0) {
                $blockedIds[] = $cat['id'];
            } else {
                $deletableIds[] = $cat['id'];
            }
        }
        
        if (!empty($deletableIds)) {
            $delPlaceholders = implode(',', array_fill(0, count($deletableIds), '?'));
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id IN ($delPlaceholders)");
            $stmt->execute($deletableIds);
            $deletedCount = $stmt->rowCount();
        } else {
            $deletedCount = 0;
        }
        
        $pdo->commit();
        
        $message = "Успешно удалено категорий: $deletedCount";
        if (!empty($blockedIds)) {
            $message .= " (Категории с товарами не удалены: ID " . implode(', ', $blockedIds) . ")";
        }
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'deleted_count' => $deletedCount,
            'deleted_ids' => $deletableIds,
            'blocked_ids' => $blockedIds
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Bulk delete error in categories.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных при удалении']);
    }
    exit;
}

// ==========================================
// 2. ОБРАБОТКА МАССОВОГО РЕДАКТИРОВАНИЯ (AJAX)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_edit') {
    header('Content-Type: application/json');
    
    $updates = $_POST['updates'] ?? [];
    
    if (empty($updates) || !is_array($updates)) {
        echo json_encode(['success' => false, 'message' => 'Нет данных для обновления']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        $updatedCount = 0;
        
        foreach ($updates as $update) {
            $id = (int)($update['id'] ?? 0);
            $name = trim($update['name'] ?? '');
            $slug = trim($update['slug'] ?? '');
            $sort_order = (int)($update['sort_order'] ?? 0);
            $is_active = (int)($update['is_active'] ?? 1);
            
            if ($id > 0 && $name) {
                if (!$slug) {
                    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
                    $slug = trim($slug, '-');
                }
                $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, sort_order=?, is_active=? WHERE id=?");
                $stmt->execute([$name, $slug, $sort_order, $is_active, $id]);
                $updatedCount += $stmt->rowCount();
            }
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => "Обновлено категорий: $updatedCount",
            'updated_count' => $updatedCount
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Bulk edit error in categories.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных при обновлении']);
    }
    exit;
}

// ==========================================
// 3. ОБЫЧНЫЕ ОПЕРАЦИИ С КАТЕГОРИЯМИ
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_action'])) {
    $action = $_POST['category_action'];
    try {
        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            if ($name) {
                if (!$slug) {
                    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
                    $slug = trim($slug, '-');
                }
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, sort_order, is_active) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $slug, (int)($_POST['sort_order'] ?? 0), $is_active]);
                $success = 'Категория добавлена!';
            }
        } elseif ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            if ($name && $id > 0) {
                if (!$slug) {
                    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
                    $slug = trim($slug, '-');
                }
                $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, sort_order=?, is_active=? WHERE id=?");
                $stmt->execute([$name, $slug, (int)($_POST['sort_order'] ?? 0), $is_active, $id]);
                $success = 'Категория обновлена!';
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() == 0) {
                $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
                $success = 'Категория удалена!';
            } else {
                $error = 'Нельзя удалить категорию с товарами!';
            }
        }
    } catch (PDOException $e) {
        $error = 'Ошибка БД: ' . $e->getMessage();
    }
}

// ==========================================
// 4. ПОЛУЧЕНИЕ ДАННЫХ ДЛЯ ОТОБРАЖЕНИЯ
// ==========================================
try {
    $categories = $pdo->query("
        SELECT c.*, COUNT(p.id) as products_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id 
        ORDER BY c.sort_order, c.name
    ")->fetchAll();
} catch (Exception $e) {
    $categories = [];
    $dbError = "Ошибка загрузки данных: " . $e->getMessage();
}

$pageScript = '
$(document).ready(function() {
    const table = $("#categoriesTable").DataTable({ 
        "responsive": true, 
        "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"},
        "columnDefs": [
            { "orderable": false, "targets": [0, 6] },
            { "className": "text-center", "targets": [0, 6] }
        ],
        "drawCallback": function() {
            updateSelection();
        }
    });

    function updateSelection() {
        var checkedCount = $(".select-item:checked").length;
        $("#selectedCount").text(checkedCount);
        $("#bulkDeleteBtn").prop("disabled", checkedCount === 0);
        $("#bulkEditBtn").prop("disabled", checkedCount === 0);
        
        var totalItems = $(".select-item").length;
        var $selectAll = $("#selectAll");
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
        var alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show custom-alert" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        $("body").append(alertHtml);
        setTimeout(function() {
            $(".custom-alert").fadeOut(300, function() { $(this).remove(); });
        }, 4000);
    }

    // Открытие массового редактирования
    $("#bulkEditBtn").off("click").on("click", function() {
        var selectedIds = $(".select-item:checked").map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            showAlert("warning", "Выберите хотя бы одну категорию");
            return;
        }
        
        var selectedData = [];
        selectedIds.forEach(function(id) {
            var $row = $(\'tr[data-id="\' + id + \'"]\');
            selectedData.push({
                id: id,
                name: $row.find(".category-name").text(),
                slug: $row.find(".category-slug").text(),
                sort_order: $row.find(".category-sort").text(),
                is_active: $row.find(".category-status").data("active")
            });
        });

        var modalBody = $("#bulkEditModal .modal-body");
        var tableHtml = `
            <div style="max-height: 500px; overflow-y: auto;">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Slug</th>
                            <th>Сортировка</th>
                            <th>Активна</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        selectedData.forEach(function(item) {
            tableHtml += `
                <tr data-id="${item.id}">
                    <td>${item.id}</td>
                    <td><input type="text" name="name_${item.id}" class="form-control form-control-sm edit-name" value="${escapeHtml(item.name)}"></td>
                    <td><input type="text" name="slug_${item.id}" class="form-control form-control-sm edit-slug" value="${escapeHtml(item.slug)}"></td>
                    <td><input type="number" name="sort_${item.id}" class="form-control form-control-sm edit-sort" value="${item.sort_order}" style="width: 80px;"></td>
                    <td class="text-center">
                        <select name="active_${item.id}" class="form-control form-control-sm edit-active" style="width: 100px;">
                            <option value="1" ${item.is_active == 1 ? "selected" : ""}>Активна</option>
                            <option value="0" ${item.is_active == 0 ? "selected" : ""}>Скрыта</option>
                        </select>
                     </td>
                 </tr>
            `;
        });
        
        tableHtml += `</tbody> </table></div>`;
        modalBody.html(tableHtml);
        
        $("#bulkEditModal").modal("show");
    });

    // Сохранение массового редактирования
    $("#saveBulkEdit").off("click").on("click", function() {
        var updates = [];
        
        $("#bulkEditModal tbody tr").each(function() {
            var id = $(this).data("id");
            var name = $(this).find(".edit-name").val();
            var slug = $(this).find(".edit-slug").val();
            var sort_order = $(this).find(".edit-sort").val();
            var is_active = $(this).find(".edit-active").val();
            
            updates.push({
                id: id,
                name: name,
                slug: slug,
                sort_order: sort_order,
                is_active: is_active
            });
        });
        
        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.prop("disabled", true).html(\'<i class="fas fa-spinner fa-spin"></i> Сохранение...\');
        
        $.ajax({
            url: "categories.php",
            method: "POST",
            data: {
                action: "bulk_edit",
                updates: updates
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    showAlert("success", response.message);
                    location.reload();
                } else {
                    showAlert("danger", response.message || "Ошибка при сохранении");
                }
                $btn.prop("disabled", false).html(originalHtml);
                $("#bulkEditModal").modal("hide");
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
                showAlert("danger", "Ошибка соединения с сервером");
                $btn.prop("disabled", false).html(originalHtml);
            }
        });
    });

    // Массовое удаление
    $("#bulkDeleteBtn").off("click").on("click", function() {
        var selectedIds = $(".select-item:checked").map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            showAlert("warning", "Выберите хотя бы одну категорию");
            return;
        }

        var hasProducts = false;
        selectedIds.forEach(function(id) {
            var $row = $(\'tr[data-id="\' + id + \'"]\');
            var productsCount = parseInt($row.find(".products-count").text());
            if (productsCount > 0) {
                hasProducts = true;
            }
        });

        var confirmMessage = `Удалить выбранные категории (${selectedIds.length} шт.)?`;
        if (hasProducts) {
            confirmMessage = `Внимание! Некоторые категории содержат товары. Они не будут удалены.\n\nУдалить выбранные категории без товаров?`;
        }
        
        if (!confirm(confirmMessage)) {
            return;
        }

        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.prop("disabled", true).html(\'<i class="fas fa-spinner fa-spin"></i> Удаление...\');

        $.ajax({
            url: "categories.php",
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
                            var $row = $(\'tr[data-id="\' + id + \'"]\');
                            if ($row.length) {
                                $row.fadeOut(300, function() {
                                    var rowNode = $row[0];
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
                    
                    if ($("#categoriesTable tbody tr:visible").length === 0) {
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
    
    function escapeHtml(text) {
        if (!text) return "";
        return text.replace(/[&<>]/g, function(m) {
            if (m === "&") return "&amp;";
            if (m === "<") return "&lt;";
            if (m === ">") return "&gt;";
            return m;
        });
    }
    
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
            <h1 class="m-0">Управление категориями</h1>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Список категорий</h3>
                    <div>
                        <button id="bulkEditBtn" class="btn btn-warning btn-sm mr-2" disabled>
                            <i class="fas fa-edit"></i> Редактировать выбранные (<span id="selectedCount">0</span>)
                        </button>
                        <button id="bulkDeleteBtn" class="btn btn-danger btn-sm mr-2" disabled>
                            <i class="fas fa-trash"></i> Удалить выбранные
                        </button>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#categoryModal" onclick="openCategoryModal('add')">
                            <i class="fas fa-plus"></i> Добавить
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <table id="categoriesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">
                                    <input type="checkbox" id="selectAll" title="Выбрать все">
                                </th>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Slug</th>
                                <th>Сортировка</th>
                                <th>Товаров</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr data-id="<?= $cat['id'] ?>">
                                <td style="text-align: center;">
                                    <input type="checkbox" class="select-item" value="<?= $cat['id'] ?>">
                                 </td>
                                 <td><?= $cat['id'] ?></td>
                                <td class="category-name"><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                                <td class="category-slug"><code><?= htmlspecialchars($cat['slug']) ?></code></td>
                                <td class="category-sort"><?= $cat['sort_order'] ?></td>
                                <td class="products-count text-center">
                                    <span class="badge badge-<?= $cat['products_count'] > 0 ? 'info' : 'secondary' ?>">
                                        <?= $cat['products_count'] ?>
                                    </span>
                                </td>
                                <td class="category-status text-center" data-active="<?= $cat['is_active'] ?>">
                                    <span class="badge badge-<?= $cat['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $cat['is_active'] ? 'Активна' : 'Скрыта' ?>
                                    </span>
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

<!-- Modal для добавления/редактирования категории -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="category_action" id="modal_action" value="add">
                <input type="hidden" name="id" id="modal_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Категория</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Название *</label>
                        <input type="text" name="name" id="modal_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" id="modal_slug" class="form-control" placeholder="auto">
                        <small class="text-muted">Оставьте пустым для автоматической генерации</small>
                    </div>
                    <div class="form-group">
                        <label>Сортировка</label>
                        <input type="number" name="sort_order" id="modal_sort_order" class="form-control" value="0">
                        <small class="text-muted">Меньше значение = выше в списке</small>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" name="is_active" id="modal_is_active" value="1" checked>
                            <label for="modal_is_active" class="custom-control-label">Активна</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal для массового редактирования -->
<div class="modal fade" id="bulkEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Массовое редактирование категорий</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Содержимое будет заполнено динамически -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="saveBulkEdit">
                    <i class="fas fa-save"></i> Сохранить изменения
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openCategoryModal(action, data = null) {
    var modalAction = document.getElementById('modal_action');
    if (modalAction) modalAction.value = action;
    
    var modalTitle = document.getElementById('modalTitle');
    var modalId = document.getElementById('modal_id');
    var modalName = document.getElementById('modal_name');
    var modalSlug = document.getElementById('modal_slug');
    var modalSortOrder = document.getElementById('modal_sort_order');
    var modalIsActive = document.getElementById('modal_is_active');
    
    if (action === 'add') {
        if (modalTitle) modalTitle.textContent = 'Добавить категорию';
        if (modalId) modalId.value = '';
        if (modalName) modalName.value = '';
        if (modalSlug) modalSlug.value = '';
        if (modalSortOrder) modalSortOrder.value = '0';
        if (modalIsActive) modalIsActive.checked = true;
    } else if (data) {
        if (modalTitle) modalTitle.textContent = 'Редактировать категорию';
        if (modalId) modalId.value = data.id;
        if (modalName) modalName.value = data.name;
        if (modalSlug) modalSlug.value = data.slug;
        if (modalSortOrder) modalSortOrder.value = data.sort_order;
        if (modalIsActive) modalIsActive.checked = data.is_active == 1;
    }
    
    $('#categoryModal').modal('show');
}
</script>

<?php require_once 'includes/footer.php'; ?>