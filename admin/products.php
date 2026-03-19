<?php
$activePage = 'products';
$pageTitle = 'Товары | Админ-панель';

require_once 'includes/auth_check.php';

$error = '';
$success = '';

// Обработка категорий
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
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC")->fetchAll();

$pageScript = '$("#productsTable").DataTable({ "responsive": true, "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"} });';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Товары и категории</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Категории</h3>
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#categoryModal" onclick="openCategoryModal('add')"><i class="fas fa-plus"></i></button>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php foreach ($categories as $cat): ?>
                                <div class="category-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= e($cat['name']) ?></strong><br>
                                        <small class="text-muted"><?= $cat['products_count'] ?> тов.</small>
                                    </div>
                                    <div class="category-actions">
                                        <button class="btn btn-xs btn-info" onclick='openCategoryModal("edit", <?= json_encode($cat) ?>)'><i class="fas fa-edit"></i></button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить?')">
                                            <input type="hidden" name="category_action" value="delete">
                                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                            <button type="submit" class="btn btn-xs btn-danger" <?= $cat['products_count'] > 0 ? 'disabled' : '' ?>><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Список товаров</h3>
                            <a href="product_add.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Добавить</a>
                        </div>
                        <div class="card-body">
                            <table id="productsTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr><th>ID</th><th>Название</th><th>Категория</th><th>Цена</th><th>Остаток</th><th>Статус</th><th>Действия</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td><?= $p['id'] ?></td>
                                        <td><?= e($p['name']) ?></td>
                                        <td><span class="badge badge-info"><?= e($p['category_name'] ?? '—') ?></span></td>
                                        <td><?= number_format($p['base_price'], 0, '.', ' ') ?> ₽</td>
                                        <td><span class="badge badge-<?= $p['stock'] > 10 ? 'success' : ($p['stock'] > 0 ? 'warning' : 'danger') ?>"><?= $p['stock'] ?></span></td>
                                        <td><span class="badge badge-<?= ['active'=>'success','inactive'=>'secondary','draft'=>'warning'][$p['status']] ?? 'info' ?>"><?= e($p['status']) ?></span></td>
                                        <td>
                                            <a href="product_edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                            <a href="product_delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
                <div class="modal-header"><h5 class="modal-title" id="modalTitle">Категория</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body">
                    <div class="form-group"><label>Название *</label><input type="text" name="name" id="modal_name" class="form-control" required></div>
                    <div class="form-group"><label>Slug</label><input type="text" name="slug" id="modal_slug" class="form-control" placeholder="auto"></div>
                    <div class="form-group"><label>Сортировка</label><input type="number" name="sort_order" id="modal_sort_order" class="form-control" value="0"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button><button type="submit" class="btn btn-primary">Сохранить</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function openCategoryModal(action, data = null) {
    $('#modal_action').val(action);
    if (action === 'add') {
        $('#modalTitle').text('Добавить категорию');
        $('#modal_id').val(''); $('#modal_name').val(''); $('#modal_slug').val(''); $('#modal_sort_order').val('0');
    } else if (data) {
        $('#modalTitle').text('Редактировать категорию');
        $('#modal_id').val(data.id); $('#modal_name').val(data.name); $('#modal_slug').val(data.slug); $('#modal_sort_order').val(data.sort_order);
    }
    $('#categoryModal').modal('show');
}
</script>

<?php require_once 'includes/footer.php'; ?>