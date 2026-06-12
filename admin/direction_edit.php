<?php
$activePage = 'directions';
$pageTitle = 'Массовое редактирование направлений';
require_once 'includes/auth_check.php';

// Получаем ID выбранных направлений из GET-параметра
$idsParam = isset($_GET['ids']) ? $_GET['ids'] : '';
$selectedIds = $idsParam ? explode(',', $idsParam) : [];
$selectedIds = array_filter($selectedIds, function($id) {
    return filter_var($id, FILTER_VALIDATE_INT);
});

if (empty($selectedIds)) {
    redirect('directions.php');
}

$error = '';
$success = '';

// Получаем данные выбранных направлений
try {
    $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
    $stmt = $pdo->prepare("SELECT * FROM directions WHERE id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
    $stmt->execute($selectedIds);
    $directions = $stmt->fetchAll();
    
    if (empty($directions)) {
        redirect('directions.php');
    }
} catch (PDOException $e) { 
    die("Ошибка загрузки данных: " . $e->getMessage()); 
}

// Обработка массового сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all'])) {
    try {
        $pdo->beginTransaction();
        $updatedCount = 0;
        
        foreach ($directions as $dir) {
            $id = $dir['id'];
            
            $name = trim($_POST['name_' . $id] ?? '');
            $description = trim($_POST['description_' . $id] ?? '');
            $sort_order = (int)($_POST['sort_order_' . $id] ?? 0);
            
            if ($name) {
                $stmt = $pdo->prepare("UPDATE directions SET name=?, description=?, sort_order=? WHERE id=?");
                $stmt->execute([$name, $description, $sort_order, $id]);
                $updatedCount++;
            }
        }
        
        $pdo->commit();
        $success = "Успешно обновлено направлений: $updatedCount";
        
        // Перезагружаем данные
        $stmt = $pdo->prepare("SELECT * FROM directions WHERE id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
        $stmt->execute($selectedIds);
        $directions = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Ошибка БД: ' . $e->getMessage();
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<style>
.edit-direction-card {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 30px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.edit-direction-card .card-header-custom {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.edit-direction-card .card-header-custom h4 {
    margin: 0;
    color: #2c3e50;
}
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Массовое редактирование направлений</h1>
            <p class="text-muted mt-2">Редактирование выбранных направлений: <?= count($directions) ?> шт.</p>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="directions.php">Вернуться к списку</a></div>
            <?php endif; ?>
            
            <form method="POST">
                <?php foreach ($directions as $dir): ?>
                <div class="edit-direction-card">
                    <div class="card-header-custom">
                        <h4>
                            <i class="fas fa-directions"></i> 
                            Направление #<?= $dir['id'] ?>: <?= htmlspecialchars($dir['name'] ?? '') ?>
                        </h4>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Название *</label>
                                <input type="text" name="name_<?= $dir['id'] ?>" class="form-control" value="<?= htmlspecialchars($dir['name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Описание</label>
                                <textarea name="description_<?= $dir['id'] ?>" class="form-control" rows="4"><?= htmlspecialchars($dir['description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Порядок сортировки</label>
                                <input type="number" name="sort_order_<?= $dir['id'] ?>" class="form-control" value="<?= $dir['sort_order'] ?? 0 ?>">
                                <small class="text-muted">Меньше значение = выше в списке</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="card">
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">* — обязательные поля</span>
                            <div>
                                <button type="submit" name="save_all" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Сохранить все изменения
                                </button>
                                <a href="directions.php" class="btn btn-secondary ml-2">
                                    <i class="fas fa-times"></i> Отмена
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>