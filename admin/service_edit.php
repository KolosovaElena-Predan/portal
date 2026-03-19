<?php
$activePage = 'services';
$pageTitle = 'Редактирование услуги';
$summernote = true;

require_once 'includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch();
    if (!$service) redirect('services.php');
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $duration = trim($_POST['duration'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (!$name) {
        $error = 'Название обязательно';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE services SET name=?, price=?, duration=?, is_active=?, sort_order=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$name, $price, $duration, $is_active, $sort_order, $id]);
            $success = 'Услуга обновлена!';
            $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
            $stmt->execute([$id]);
            $service = $stmt->fetch();
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
            <h1 class="m-0">Редактирование услуги #<?= $service['id'] ?></h1>
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
                                <input type="text" name="name" class="form-control" value="<?= e($service['name']) ?>" required>
                            </div>
                            <div class="form-section">
                                <label class="form-label">Полное описание</label>
                                <textarea name="full_description" class="form-control summernote" rows="10"><?= e($service['full_description']) ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-section">
                                <label class="form-label">Цена (₽)</label>
                                <input type="number" step="0.01" name="price" class="form-control" value="<?= $service['price'] ?>">
                            </div>
                            <div class="form-section">
                                <label class="form-label">Длительность</label>
                                <input type="text" name="duration" class="form-control" value="<?= e($service['duration']) ?>">
                            </div>
                            <div class="form-section">
                                <label class="form-label">Сортировка</label>
                                <input type="number" name="sort_order" class="form-control" value="<?= $service['sort_order'] ?>">
                            </div>
                            <div class="form-section">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" name="is_active" id="is_active" value="1" <?= $service['is_active'] ? 'checked' : '' ?>>
                                    <label for="is_active" class="custom-control-label">Активна</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="save" class="btn btn-primary">Сохранить</button>
                    <a href="services.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>