<?php
$activePage = 'lab_services';
$pageTitle = 'Редактирование услуги';
$summernote = true;
require_once 'includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';

// Загружаем услугу
try {
    $stmt = $pdo->prepare("SELECT * FROM lab_services WHERE id = ?");
    $stmt->execute([$id]);
    $svc = $stmt->fetch();
    if (!$svc) redirect('lab_services.php');
} catch (PDOException $e) { 
    die("Ошибка загрузки: " . $e->getMessage()); 
}

// Обработка сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?: null;
    $duration = trim($_POST['duration'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (!$name) {
        $error = 'Название обязательно';
    } else {
        try {
            // Обновляем все поля, кроме img_url (оставляем как есть)
            $stmt = $pdo->prepare("UPDATE lab_services SET 
                name=?, 
                description=?, 
                price=?, 
                duration=?, 
                is_active=?, 
                sort_order=? 
                WHERE id=?");
            $stmt->execute([$name, $description, $price, $duration, $is_active, $sort_order, $id]);
            
            $success = 'Услуга обновлена!';
            
            // Перезагружаем данные для формы
            $stmt = $pdo->prepare("SELECT * FROM lab_services WHERE id = ?");
            $stmt->execute([$id]);
            $svc = $stmt->fetch();
            
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
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Редактирование: <?= e($svc['name']) ?></h1>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= e($success) ?> 
                    <a href="lab_services.php">К списку</a>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Название услуги *</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?= e($svc['name']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Описание</label>
                                <textarea name="description" class="form-control summernote" rows="10"><?= e($svc['description']) ?></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Цена (₽)</label>
                                <input type="number" step="0.01" name="price" class="form-control" 
                                       value="<?= e($svc['price']) ?>" placeholder="0 — по договору">
                            </div>
                            
                            <div class="form-group">
                                <label>Срок оказания</label>
                                <input type="text" name="duration" class="form-control" 
                                       value="<?= e($svc['duration']) ?>" placeholder="1-3 дня">
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" 
                                           name="is_active" id="is_active" value="1" 
                                           <?= $svc['is_active'] ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="is_active">
                                        Отображать на сайте
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Порядок сортировки</label>
                                <input type="number" name="sort_order" class="form-control" 
                                       value="<?= $svc['sort_order'] ?? 0 ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" name="save" class="btn btn-primary">
                        <i class="fas fa-save"></i> Сохранить
                    </button>
                    <a href="lab_services.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Отмена
                    </a>
                </div>
            </form>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>