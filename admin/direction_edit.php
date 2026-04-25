<?php
$activePage = 'directions';
$pageTitle = 'Редактирование направления';
require_once 'includes/auth_check.php';
$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';

// Загружаем направление
try {
    $stmt = $pdo->prepare("SELECT * FROM directions WHERE id = ?");
    $stmt->execute([$id]);
    $dir = $stmt->fetch();
    if (!$dir) redirect('directions.php');
} catch (PDOException $e) { die("Ошибка: " . $e->getMessage()); }

// Обработка сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (!$name) {
        $error = 'Название обязательно';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE directions SET name=?, description=?, sort_order=? WHERE id=?");
            $stmt->execute([$name, $description, $sort_order, $id]);
            $success = 'Направление обновлено!';
            // Обновляем данные
            $stmt = $pdo->prepare("SELECT * FROM directions WHERE id = ?");
            $stmt->execute([$id]);
            $dir = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Редактирование: <?= e($dir['name']) ?></h1></div></div>
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?> <a href="directions.php">К списку</a></div><?php endif; ?>
            <form method="POST" class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label>Название *</label><input type="text" name="name" class="form-control" value="<?= e($dir['name']) ?>" required></div>
                            <div class="form-group"><label>Описание</label><textarea name="description" class="form-control" rows="4"><?= e($dir['description']) ?></textarea></div>
							<div class="form-group"><label>Порядок сортировки</label><input type="number" name="sort_order" class="form-control" value="<?= $dir['sort_order'] ?? 0 ?>"></div>
                        
						</div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="save" class="btn btn-primary">Сохранить</button>
                    <a href="directions.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </section>
</div>
<?php require_once 'includes/footer.php'; ?>