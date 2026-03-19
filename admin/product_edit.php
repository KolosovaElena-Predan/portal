<?php
$activePage = 'products';
$pageTitle = 'Редактирование товара';
$summernote = true;

require_once 'includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
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

    if (!$name) {
        $error = 'Название обязательно';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE products SET name=?, category_id=?, base_price=?, stock=?, status=?, short_description=?, full_description=?, is_new=?, is_slider=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$name, $category_id, $base_price, $stock, $status, $short_description, $full_description, $is_new, $is_slider, $id]);
            $success = 'Товар обновлён!';
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

if (!$product) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) redirect('products.php');
}

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

$pageScript = '$(".summernote").summernote({ height: 300, lang: "ru-RU" });';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Редактирование товара #<?= $product['id'] ?></h1>
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
                                <label class="form-label">Название *</label>
                                <input type="text" name="name" class="form-control" value="<?= e($product['name']) ?>" required>
                            </div>
                            <div class="form-section">
                                <label class="form-label">Краткое описание</label>
                                <textarea name="short_description" class="form-control" rows="3"><?= e($product['short_description']) ?></textarea>
                            </div>
                            <div class="form-section">
                                <label class="form-label">Полное описание</label>
                                <textarea name="full_description" class="form-control summernote" rows="10"><?= e($product['full_description']) ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-section">
                                <label class="form-label">Категория</label>
                                <select name="category_id" class="form-control">
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-section">
                                <label class="form-label">Цена (₽)</label>
                                <input type="number" step="0.01" name="base_price" class="form-control" value="<?= $product['base_price'] ?>">
                            </div>
                            <div class="form-section">
                                <label class="form-label">Остаток</label>
                                <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>">
                            </div>
                            <div class="form-section">
                                <label class="form-label">Статус</label>
                                <select name="status" class="form-control">
                                    <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>✅ Активен</option>
                                    <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>⏸ Неактивен</option>
                                    <option value="draft" <?= $product['status'] === 'draft' ? 'selected' : '' ?>>📝 Черновик</option>
                                </select>
                            </div>
                            <div class="form-section">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input class="custom-control-input" type="checkbox" name="is_new" id="is_new" value="1" <?= $product['is_new'] ? 'checked' : '' ?>>
                                    <label for="is_new" class="custom-control-label">🆕 Новинка</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" name="is_slider" id="is_slider" value="1" <?= $product['is_slider'] ? 'checked' : '' ?>>
                                    <label for="is_slider" class="custom-control-label">🎠 В слайдере</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="save" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить</button>
                    <a href="products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Отмена</a>
                </div>
            </form>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>