<?php
$activePage = 'team';
$pageTitle = 'Команда | Админ-панель';
require_once 'includes/auth_check.php';

// ==========================================
// 1. ОБРАБОТКА МАССОВОГО РЕДАКТИРОВАНИЯ
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
            $sort_order = (int)($update['sort_order'] ?? 0);
            $position = trim($update['position'] ?? '');
            $name = trim($update['name'] ?? '');
            
            if ($id > 0 && $name && $position) {
                $stmt = $pdo->prepare("UPDATE team SET sort_order = ?, position = ?, name = ? WHERE id = ?");
                $stmt->execute([$sort_order, $position, $name, $id]);
                $updatedCount += $stmt->rowCount();
            }
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => "Обновлено сотрудников: $updatedCount",
            'updated_count' => $updatedCount
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Bulk edit error in team.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных при обновлении']);
    }
    exit;
}

// ==========================================
// 2. ОБРАБОТКА МАССОВОГО УДАЛЕНИЯ (AJAX)
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
        $stmt = $pdo->prepare("SELECT photo_url FROM team WHERE id IN ($placeholders)");
        $stmt->execute($validIds);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($members as $member) {
            if (!empty($member['photo_url'])) {
                $cleanPath = ltrim($member['photo_url'], './');
                $filePath = __DIR__ . '/../lab/' . $cleanPath;
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM team WHERE id IN ($placeholders)");
        $stmt->execute($validIds);
        $deletedCount = $stmt->rowCount();
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => "Успешно удалено сотрудников: $deletedCount",
            'deleted_count' => $deletedCount,
            'deleted_ids' => $validIds
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Bulk delete error in team.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных при удалении']);
    }
    exit;
}

// ==========================================
// 3. ОБРАБОТКА ЕДИНИЧНОГО УДАЛЕНИЯ (GET)
// ==========================================
if (isset($_GET['delete']) && filter_var($_GET['delete'], FILTER_VALIDATE_INT)) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("SELECT photo_url FROM team WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($member && !empty($member['photo_url'])) {
            $cleanPath = ltrim($member['photo_url'], './');
            $filePath = __DIR__ . '/../lab/' . $cleanPath;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM team WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: team.php?deleted=1");
        exit;
    } catch (Exception $e) {
        error_log("Single delete error in team.php: " . $e->getMessage());
        header("Location: team.php?error=1");
        exit;
    }
}

// ==========================================
// 4. ПОЛУЧЕНИЕ ДАННЫХ ДЛЯ ОТОБРАЖЕНИЯ
// ==========================================
try {
    $members = $pdo->query("SELECT * FROM team ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $members = [];
    $dbError = "Ошибка загрузки данных: " . $e->getMessage();
}

$pageScript = '
$(document).ready(function() {
    // Инициализация DataTable
    const table = $("#teamTable").DataTable({ 
        "responsive": true, 
        "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"},
        "columnDefs": [
            { "orderable": false, "targets": [0] },
            { "className": "text-center", "targets": [0, 1] }
        ],
        "drawCallback": function() {
            updateSelection();
        }
    });

    function updateSelection() {
        const checkedCount = $(".select-item:checked").length;
        $("#selectedCount").text(checkedCount);
        $("#bulkDeleteBtn").prop("disabled", checkedCount === 0);
        $("#bulkEditBtn").prop("disabled", checkedCount === 0);
        
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

    // Функция для показа уведомлений
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
            $(".custom-alert").fadeOut(300, function() {
                $(this).remove();
            });
        }, 4000);
    }

    // Открытие массового редактирования
    $("#bulkEditBtn").off("click").on("click", function() {
        const selectedIds = $(".select-item:checked").map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            showAlert("warning", "Выберите хотя бы одного сотрудника");
            return;
        }
        
        window.location.href = "team_edit.php?ids=" + selectedIds.join(",");
    });

    // Массовое удаление
    $("#bulkDeleteBtn").off("click").on("click", function() {
        const selectedIds = $(".select-item:checked").map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            showAlert("warning", "Выберите хотя бы одного сотрудника");
            return;
        }

        if (!confirm(`Удалить выбранных сотрудников (${selectedIds.length} шт.)?\nФотографии также будут удалены с сервера.`)) {
            return;
        }

        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.prop("disabled", true).html(\'<i class="fas fa-spinner fa-spin"></i> Удаление...\');

        $.ajax({
            url: "team.php",
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
                    
                    if ($("#teamTable tbody tr:visible").length === 0) {
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
            <h1 class="m-0">Команда лаборатории</h1>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            
            <!-- Уведомления -->
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    Сотрудник успешно удален.
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
                    <h3 class="card-title">Список сотрудников</h3>
                    <div>
                        <button id="bulkEditBtn" class="btn btn-warning btn-sm mr-2" disabled>
                            <i class="fas fa-edit"></i> Редактировать выбранные (<span id="selectedCount">0</span>)
                        </button>
                        <button id="bulkDeleteBtn" class="btn btn-danger btn-sm mr-2" disabled>
                            <i class="fas fa-trash"></i> Удалить выбранные
                        </button>
                        <a href="team_add.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Добавить
                        </a>
                    </div>
                </div>
                
                <div class="card-body table-responsive p-0">
                    <table id="teamTable" class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">
                                    <input type="checkbox" id="selectAll" title="Выбрать все">
                                </th>
                                <th>ID</th>
                                <th>Фото</th>
                                <th>ФИО</th>
                                <th>Должность</th>
                                <th>Контакты</th>
                                <th>Образование</th>
                                <th>Порядок</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($members)): ?>
                                <?php foreach ($members as $m): ?>
                                <tr data-id="<?= $m['id'] ?>">
                                    <td style="text-align: center;">
                                        <input type="checkbox" class="select-item" value="<?= $m['id'] ?>">
                                    </td>
                                    <td><?= $m['id'] ?></td>
                                    <td>
                                        <?php if (!empty($m['photo_url'])): ?>
                                            <img src="../lab/<?= htmlspecialchars($m['photo_url']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="member-name"><strong><?= htmlspecialchars($m['name']) ?></strong></td>
                                    <td class="member-position"><?= htmlspecialchars($m['position']) ?></td>
                                    <td>
                                        <?php if (!empty($m['email'])): ?>
                                            <small><i class="fas fa-envelope"></i> <?= htmlspecialchars($m['email']) ?></small><br>
                                        <?php endif; ?>
                                        <?php if (!empty($m['phone'])): ?>
                                            <small><i class="fas fa-phone"></i> <?= htmlspecialchars($m['phone']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($m['education'])): ?>
                                            <small><?= htmlspecialchars(mb_substr($m['education'], 0, 50)) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="member-sort"><?= $m['sort_order'] ?? 0 ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Сотрудники не найдены</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>