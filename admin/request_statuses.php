<?php
$activePage = 'request_statuses';
$pageTitle = 'Статусы заявок | Админ-панель';
require_once 'includes/auth_check.php';
$error = '';
$success = '';

// Обработка операций со статусами
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            $code = trim($_POST['code'] ?? '');
            $color = trim($_POST['color'] ?? '#6c757d');
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $is_default = isset($_POST['is_default']) ? 1 : 0;
            $is_closed = isset($_POST['is_closed']) ? 1 : 0;
            
            if (!$name || !$code) {
                $error = 'Название и код обязательны';
            } else {
                $code = strtolower(preg_replace('/[^a-z_]/', '_', $code));
                $stmt = $pdo->prepare("INSERT INTO request_statuses (name, code, color, sort_order, is_default, is_closed) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $code, $color, $sort_order, $is_default, $is_closed]);
                
                // Если это статус по умолчанию, сбрасываем у других
                if ($is_default) {
                    $pdo->prepare("UPDATE request_statuses SET is_default = 0 WHERE id != ?")->execute([$pdo->lastInsertId()]);
                }
                $success = 'Статус добавлен';
            }
        } elseif ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $code = trim($_POST['code'] ?? '');
            $color = trim($_POST['color'] ?? '#6c757d');
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $is_default = isset($_POST['is_default']) ? 1 : 0;
            $is_closed = isset($_POST['is_closed']) ? 1 : 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if (!$name || !$code) {
                $error = 'Название и код обязательны';
            } else {
                $code = strtolower(preg_replace('/[^a-z_]/', '_', $code));
                $stmt = $pdo->prepare("UPDATE request_statuses SET name=?, code=?, color=?, sort_order=?, is_default=?, is_closed=?, is_active=? WHERE id=?");
                $stmt->execute([$name, $code, $color, $sort_order, $is_default, $is_closed, $is_active, $id]);
                
                if ($is_default) {
                    $pdo->prepare("UPDATE request_statuses SET is_default = 0 WHERE id != ?")->execute([$id]);
                }
                $success = 'Статус обновлён';
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            
            // Проверяем, есть ли заявки с таким статусом
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM request WHERE status = (SELECT code FROM request_statuses WHERE id = ?)");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $error = "Невозможно удалить статус - он используется в $count заявках";
            } else {
                $pdo->prepare("DELETE FROM request_statuses WHERE id = ?")->execute([$id]);
                $success = 'Статус удалён';
            }
        }
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $error = 'Код статуса уже существует';
        } else {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

$statuses = $pdo->query("SELECT * FROM request_statuses ORDER BY sort_order, id")->fetchAll();

$pageScript = '
$("#statusesTable").DataTable({ 
    "responsive": true, 
    "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"},
    "columnDefs": [{"orderable": false, "targets": [6]}]
});
';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<style>
.color-preview {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 4px;
    margin-right: 8px;
    vertical-align: middle;
    border: 1px solid #ddd;
}
.badge-custom {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Статусы заявок</h1>
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
                    <h3 class="card-title">Список статусов</h3>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#statusModal" onclick="openStatusModal('add')">
                        <i class="fas fa-plus"></i> Добавить статус
                    </button>
                </div>
                
                <div class="card-body">
                    <table id="statusesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Код</th>
                                <th>Цвет</th>
                                <th>Сортировка</th>
                                <th>По умолчанию</th>
                                <th>Завершающий</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statuses as $s): ?>
                            <tr>
                                <td><?= $s['id'] ?></td>
                                <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                                <td><code><?= htmlspecialchars($s['code']) ?></code></td>
                                <td>
                                    <div class="color-preview" style="background-color: <?= htmlspecialchars($s['color']) ?>;"></div>
                                    <?= htmlspecialchars($s['color']) ?>
                                </td>
                                <td><?= $s['sort_order'] ?></td>
                                <td class="text-center">
                                    <?php if ($s['is_default']): ?>
                                        <span class="badge badge-success">Да</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Нет</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($s['is_closed']): ?>
                                        <span class="badge badge-warning">Завершающий</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Активный</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($s['is_active']): ?>
                                        <span class="badge badge-success">Активен</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Неактивен</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="openStatusModal('edit', <?= htmlspecialchars(json_encode($s)) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteStatus(<?= $s['id'] ?>, '<?= htmlspecialchars($s['name']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
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

<!-- Modal для добавления/редактирования статуса -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" id="modal_action" value="add">
                <input type="hidden" name="id" id="modal_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Добавить статус</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Название *</label>
                        <input type="text" name="name" id="modal_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Код (уникальный идентификатор) *</label>
                        <input type="text" name="code" id="modal_code" class="form-control" required>
                        <small class="text-muted">Только латиница и подчёркивание. Например: in_progress, awaiting_payment</small>
                    </div>
                    <div class="form-group">
                        <label>Цвет</label>
                        <div class="input-group">
                            <input type="color" name="color" id="modal_color" class="form-control" style="width: 80px; padding: 2px;">
                            <input type="text" id="modal_color_text" class="form-control" placeholder="#6c757d">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Порядок сортировки</label>
                        <input type="number" name="sort_order" id="modal_sort_order" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" name="is_default" id="modal_is_default" value="1">
                            <label for="modal_is_default" class="custom-control-label">Статус по умолчанию (для новых заявок)</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" name="is_closed" id="modal_is_closed" value="1">
                            <label for="modal_is_closed" class="custom-control-label">Завершающий статус (закрыт/отменён)</label>
                        </div>
                    </div>
                    <div class="form-group" id="is_active_field">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" name="is_active" id="modal_is_active" value="1" checked>
                            <label for="modal_is_active" class="custom-control-label">Активен</label>
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

<script>
function openStatusModal(action, data = null) {
    document.getElementById('modal_action').value = action;
    const modalTitle = document.getElementById('modalTitle');
    const modalId = document.getElementById('modal_id');
    const modalName = document.getElementById('modal_name');
    const modalCode = document.getElementById('modal_code');
    const modalColor = document.getElementById('modal_color');
    const modalColorText = document.getElementById('modal_color_text');
    const modalSortOrder = document.getElementById('modal_sort_order');
    const modalIsDefault = document.getElementById('modal_is_default');
    const modalIsClosed = document.getElementById('modal_is_closed');
    const modalIsActive = document.getElementById('modal_is_active');
    const isActiveField = document.getElementById('is_active_field');
    
    if (action === 'add') {
        modalTitle.textContent = 'Добавить статус';
        modalId.value = '';
        modalName.value = '';
        modalCode.value = '';
        modalColor.value = '#6c757d';
        modalColorText.value = '#6c757d';
        modalSortOrder.value = '0';
        modalIsDefault.checked = false;
        modalIsClosed.checked = false;
        modalIsActive.checked = true;
        isActiveField.style.display = 'block';
    } else if (data) {
        modalTitle.textContent = 'Редактировать статус';
        modalId.value = data.id;
        modalName.value = data.name;
        modalCode.value = data.code;
        modalColor.value = data.color || '#6c757d';
        modalColorText.value = data.color || '#6c757d';
        modalSortOrder.value = data.sort_order;
        modalIsDefault.checked = data.is_default == 1;
        modalIsClosed.checked = data.is_closed == 1;
        modalIsActive.checked = data.is_active == 1;
        isActiveField.style.display = 'block';
    }
    
    $('#statusModal').modal('show');
}

function deleteStatus(id, name) {
    if (confirm(`Удалить статус "${name}"?\nУдаление возможно только если статус не используется в заявках.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Синхронизация color picker и текстового поля
const colorPicker = document.getElementById('modal_color');
const colorText = document.getElementById('modal_color_text');
if (colorPicker && colorText) {
    colorPicker.addEventListener('input', function() {
        colorText.value = this.value;
    });
    colorText.addEventListener('input', function() {
        colorPicker.value = this.value;
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>