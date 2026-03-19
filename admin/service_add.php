<?php
$activePage = 'services';
$pageTitle = 'Добавление услуги';
$summernote = true;

require_once 'includes/auth_check.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = trim($_POST['name'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $full_description = trim($_POST['full_description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $img_url = trim($_POST['img_url'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (!$name) {
        $error = 'Название обязательно';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO services (name, short_description, full_description, price, img_url, duration, is_active, sort_order, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, '1', NOW(), NOW())");
            $stmt->execute([$name, $short_description, $full_description, $price, $img_url, $duration, $is_active, $sort_order]);
            $success = 'Услуга добавлена!';
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

$pageScript = '$(".summernote").summernote({ height: 300, lang: "ru-RU" });';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Добавление услуги</h1>
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
                                <input type="text" name="name" class="form-control" value="<?= e($_POST['name'] ?? '') ?>" required>
                            </div>
                            <div class="form-section">
                                <label class="form-label">Краткое описание</label>
                                <textarea name="short_description" class="form-control" rows="3"><?= e($_POST['short_description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-section">
                                <label class="form-label">Полное описание</label>
                                <textarea name="full_description" class="form-control summernote" rows="10"><?= e($_POST['full_description'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-section">
                                <label class="form-label">Цена (₽)</label>
                                <input type="number" step="0.01" name="price" class="form-control" value="<?= $_POST['price'] ?? 0 ?>">
                            </div>
                            <div class="form-section">
                                <label class="form-label">Длительность</label>
                                <input type="text" name="duration" class="form-control" value="<?= e($_POST['duration'] ?? '') ?>" placeholder="1-3 дня">
                            </div>
                            <div class="form-section">
                                <label class="form-label">URL изображения</label>
                                <input type="text" name="img_url" class="form-control" value="<?= e($_POST['img_url'] ?? '') ?>" placeholder="img/serv/s0.png">
                            </div>
                            <div class="form-section">
                                <label class="form-label">Сортировка</label>
                                <input type="number" name="sort_order" class="form-control" value="<?= $_POST['sort_order'] ?? 0 ?>">
                            </div>
                            <div class="form-section">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" name="is_active" id="is_active" value="1" <?= ($_POST['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    <label for="is_active" class="custom-control-label">Активна</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="save" class="btn btn-primary">Добавить</button>
                    <a href="services.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>