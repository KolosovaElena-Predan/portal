<?php
$activePage = 'categories';
$pageTitle = 'Категории | Админ-панель';
require_once 'includes/auth_check.php';
$error = '';
$success = '';

// Обработка действий с категориями
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_action'])) {
    $action = $_POST['category_action'];
    try {
        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            if ($name) {
                if (!$slug) $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, sort_order, is_active) VALUES (?, ?, ?, 1)");
                $stmt->execute([$name, $slug, (int)($_POST['sort_order'] ?? 0)]);
                $success = 'Категория добавлена!';
            }
        } elseif ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            if ($name && $id > 0) {
                if (!$slug) $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
                $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, sort_order=? WHERE id=?");
                $stmt->execute([$name, $slug, (int)($_POST['sort_order'] ?? 0), $id]);
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

$categories = $pdo->query("SELECT c.*, COUNT(p.id) as products_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.sort_order, c.name")->fetchAll();

$pageScript = '$("#categoriesTable").DataTable({ "responsive": true, "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"} });';
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
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Список категорий</h3>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#categoryModal" onclick="openCategoryModal('add')">
                        <i class="fas fa-plus"></i> Добавить
                    </button>
                </div>
                <div class="card-body">
                    <table id="categoriesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Slug</th>
                                <th>Сортировка</th>
                                <th>Товаров</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?= $cat['id'] ?></td>
                                <td><?= e($cat['name']) ?></td>
                                <td><?= e($cat['slug']) ?></td>
                                <td><?= $cat['sort_order'] ?></td>
                                <td><span class="badge badge-info"><?= $cat['products_count'] ?></span></td>
                                <td>
                                    <span class="badge badge-<?= $cat['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $cat['is_active'] ? 'Активна' : 'Скрыта' ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick='openCategoryModal("edit", <?= json_encode($cat) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить?')">
                                        <input type="hidden" name="category_action" value="delete">
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" <?= $cat['products_count'] > 0 ? 'disabled' : '' ?>>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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

<!-- Modal для категорий -->
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
                    </div>
                    <div class="form-group">
                        <label>Сортировка</label>
                        <input type="number" name="sort_order" id="modal_sort_order" class="form-control" value="0">
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

<script>
function openCategoryModal(action, data = null) {
    $('#modal_action').val(action);
    if (action === 'add') {
        $('#modalTitle').text('Добавить категорию');
        $('#modal_id').val('');
        $('#modal_name').val('');
        $('#modal_slug').val('');
        $('#modal_sort_order').val('0');
        $('#modal_is_active').prop('checked', true);
    } else if (data) {
        $('#modalTitle').text('Редактировать категорию');
        $('#modal_id').val(data.id);
        $('#modal_name').val(data.name);
        $('#modal_slug').val(data.slug);
        $('#modal_sort_order').val(data.sort_order);
        $('#modal_is_active').prop('checked', data.is_active == 1);
    }
    $('#categoryModal').modal('show');
}
</script>

<?php require_once 'includes/footer.php'; ?>