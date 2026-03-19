<?php
$activePage = 'products';
$pageTitle = 'Добавление товара | Админ-панель';
$summernote = true;

require_once 'includes/auth_check.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 1);
    $base_price = (float)($_POST['base_price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $status = $_POST['status'] ?? 'draft';
    $short_description = trim($_POST['short_description'] ?? '');
    $full_description = trim($_POST['full_description'] ?? '');
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_slider = isset($_POST['is_slider']) ? 1 : 0;
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (!$name) {
        $error = 'Название товара обязательно';
    } elseif ($base_price < 0 || $stock < 0) {
        $error = 'Цена и остаток не могут быть отрицательными';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (category_id, name, short_description, full_description, base_price, stock, is_new, is_slider, sort_order, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$category_id, $name, $short_description, $full_description, $base_price, $stock, $is_new, $is_slider, $sort_order, $status]);
            $success = 'Товар добавлен! ID: ' . $pdo->lastInsertId();
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

$categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order, name")->fetchAll();

$formData = [
    'name' => $_POST['name'] ?? '',
    'category_id' => $_POST['category_id'] ?? 1,
    'base_price' => $_POST['base_price'] ?? 0,
    'stock' => $_POST['stock'] ?? 0,
    'short_description' => $_POST['short_description'] ?? '',
    'full_description' => $_POST['full_description'] ?? '',
    'is_new' => $_POST['is_new'] ?? 0,
    'is_slider' => $_POST['is_slider'] ?? 0,
    'sort_order' => $_POST['sort_order'] ?? 0,
    'status' => $_POST['status'] ?? 'draft',
];

$pageScript = '$(".summernote").summernote({ height: 300, lang: "ru-RU", placeholder: "Введите описание товара..." }); setTimeout(() => $(".alert-success").fadeOut("slow"), 5000);';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Добавление нового товара</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

            <form method="POST" class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-section">
                                <label class="form-label">Название товара *</label>
                                <input type="text" name="name" class="form-control" value="<?= e($formData['name']) ?>" required>
                            </div>
                            <div class="form-section">
                                <label class="form-label">Краткое описание</label>
                                <textarea name="short_description" class="form-control" rows="3"><?= e($formData['short_description']) ?></textarea>
                            </div>
                            <div class="form-section">
                                <label class="form-label">Полное описание</label>
                                <textarea name="full_description" class="form-control summernote" rows="10"><?= e($formData['full_description']) ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-section">
                                <label class="form-label">Категория</label>
                                <select name="category_id" class="form-control">
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $formData['category_id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-section">
                                <label class="form-label">Цена (₽)</label>
                                <input type="number" step="0.01" min="0" name="base_price" class="form-control" value="<?= $formData['base_price'] ?>">
                            </div>
                            <div class="form-section">
                                <label class="form-label">Остаток на складе</label>
                                <input type="number" min="0" name="stock" class="form-control" value="<?= $formData['stock'] ?>">
                            </div>
                            <div class="form-section">
                                <label class="form-label">Статус</label>
                                <select name="status" class="form-control">
                                    <option value="draft" <?= $formData['status'] === 'draft' ? 'selected' : '' ?>>📝 Черновик</option>
                                    <option value="active" <?= $formData['status'] === 'active' ? 'selected' : '' ?>>✅ Активен</option>
                                    <option value="inactive" <?= $formData['status'] === 'inactive' ? 'selected' : '' ?>>⏸ Неактивен</option>
                                </select>
                            </div>
                            <div class="form-section">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input class="custom-control-input" type="checkbox" name="is_new" id="is_new" value="1" <?= $formData['is_new'] ? 'checked' : '' ?>>
                                    <label for="is_new" class="custom-control-label">🆕 Новинка</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" name="is_slider" id="is_slider" value="1" <?= $formData['is_slider'] ? 'checked' : '' ?>>
                                    <label for="is_slider" class="custom-control-label">🎠 В слайдере</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="save" class="btn btn-primary"><i class="fas fa-plus"></i> Добавить</button>
                    <a href="products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Отмена</a>
                </div>
            </form>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>