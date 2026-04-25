<?php
$activePage = 'lab_projects';
$pageTitle = 'Редактирование проекта';
$summernote = true; // Для визуального редактора

// ✅ 1. Подключаем авторизацию и БД (ГДЕ ИНИЦИАЛИЗИРУЕТСЯ $pdo)
require_once 'includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: lab_projects.php'); exit; }

$error = '';
$success = '';
$uploadBaseDir = __DIR__ . '/../lab/img/projects/';
if (!is_dir($uploadBaseDir)) mkdir($uploadBaseDir, 0777, true);

// ============================================
// AJAX: Загрузка изображения
// ============================================
if (isset($_POST['ajax_upload_project'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Ошибка загрузки'];
    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['image']['type'], $allowedTypes)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = 'project_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadBaseDir . $fileName)) {
                $response = ['success' => true, 'url' => 'img/projects/' . $fileName];
            }
        }
    }
    echo json_encode($response);
    exit;
}

// ============================================
// 2. Получаем данные проекта
// ============================================
try {
    $stmt = $pdo->prepare("SELECT * FROM lab_projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch();
    if (!$project) { header('Location: lab_projects.php'); exit; }
} catch (PDOException $e) { 
    die("Ошибка загрузки: " . $e->getMessage()); 
}

// ============================================
// 3. Обработка сохранения
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = trim($_POST['name'] ?? '');
    $short_desc = trim($_POST['short_description'] ?? '');
    $full_desc = trim($_POST['full_description'] ?? '');
    $new_img = trim($_POST['img_url'] ?? '');
    $start_date = $_POST['start_date'] ?: null;
    $end_date = $_POST['end_date'] ?: null;
    $status = $_POST['status'] ?? 'active';
    $budget = $_POST['budget'] ?: null;
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (!$name) {
        $error = 'Название обязательно';
    } else {
        try {
            $pdo->beginTransaction();
            // Удаляем старое фото при замене
            $old_img = $project['img_url'] ?? '';
            if ($new_img && $new_img !== $old_img && $old_img) {
                $oldPath = $uploadBaseDir . basename($old_img);
                if (file_exists($oldPath)) @unlink($oldPath);
            }
            $stmt = $pdo->prepare("UPDATE lab_projects SET name=?, short_description=?, full_description=?, img_url=?, start_date=?, end_date=?, status=?, budget=?, sort_order=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$name, $short_desc, $full_desc, $new_img, $start_date, $end_date, $status, $budget, $sort_order, $id]);
            $pdo->commit();
            $success = 'Проект обновлён!';
            // Перезагрузка данных
            $stmt = $pdo->prepare("SELECT * FROM lab_projects WHERE id = ?");
            $stmt->execute([$id]);
            $project = $stmt->fetch();
        } catch (PDOException $e) {
            $pdo->rollBack();
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
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Редактирование: <?= e($project['name']) ?></h1></div></div>
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?> <a href="lab_projects.php">К списку</a></div><?php endif; ?>
            <form method="POST" class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group"><label>Название проекта *</label><input type="text" name="name" class="form-control" value="<?= e($project['name']) ?>" required></div>
                            <div class="form-group"><label>Краткое описание</label><textarea name="short_description" class="form-control" rows="2"><?= e($project['short_description']) ?></textarea></div>
                            <div class="form-group"><label>Полное описание</label><textarea name="full_description" class="form-control summernote" rows="10"><?= e($project['full_description']) ?></textarea></div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Изображение</label>
                                <input type="hidden" name="img_url" id="projectImgUrl" value="<?= e($project['img_url'] ?? '') ?>">
                                <input type="file" id="projectImgInput" class="form-control" accept="image/*">
                                <div id="imagePreviewContainer" class="mt-3" style="display: <?= !empty($project['img_url']) ? 'block' : 'none' ?>;">
                                    <img src="../lab/<?= e($project['img_url'] ?? '') ?>" id="imagePreview" class="img-fluid rounded border" style="max-height: 200px;">
                                    <button type="button" id="removeImageBtn" class="btn btn-sm btn-danger mt-2"><i class="fas fa-trash"></i> Удалить</button>
                                </div>
                                <div id="uploadProgress" class="mt-2" style="display: none;"><div class="spinner-border spinner-border-sm text-primary"></div> Загрузка...</div>
                            </div>
                            <div class="form-group"><label>Дата начала</label><input type="date" name="start_date" class="form-control" value="<?= e($project['start_date']) ?>"></div>
                            <div class="form-group"><label>Дата окончания</label><input type="date" name="end_date" class="form-control" value="<?= e($project['end_date']) ?>"></div>
                            <div class="form-group"><label>Статус</label>
                                <select name="status" class="form-control">
                                    <option value="active" <?= $project['status'] === 'active' ? 'selected' : '' ?>>Активный</option>
                                    <option value="completed" <?= $project['status'] === 'completed' ? 'selected' : '' ?>>Завершён</option>
                                    <option value="planned" <?= $project['status'] === 'planned' ? 'selected' : '' ?>>Планируется</option>
                                </select>
                            </div>
                            <div class="form-group"><label>Бюджет (₽)</label><input type="number" step="0.01" name="budget" class="form-control" value="<?= e($project['budget']) ?>"></div>
                            <div class="form-group"><label>Порядок</label><input type="number" name="sort_order" class="form-control" value="<?= $project['sort_order'] ?? 0 ?>"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="save" class="btn btn-primary">Сохранить</button>
                    <a href="lab_projects.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </section>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('projectImgInput');
    const hiddenInput = document.getElementById('projectImgUrl');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImg = document.getElementById('imagePreview');
    const removeBtn = document.getElementById('removeImageBtn');
    const progress = document.getElementById('uploadProgress');

    fileInput?.addEventListener('change', function(e) {
        if (this.files?.[0]) {
            const fd = new FormData();
            fd.append('ajax_upload_project', '1');
            fd.append('image', this.files[0]);
            progress.style.display = 'block'; fileInput.disabled = true;
            fetch(window.location.href, { method: 'POST', body: fd })
                .then(r => r.json()).then(data => {
                    progress.style.display = 'none'; fileInput.disabled = false; fileInput.value = '';
                    if (data.success) {
                        hiddenInput.value = data.url;
                        previewImg.src = '../lab/' + data.url;
                        previewContainer.style.display = 'block';
                    } else alert('❌ ' + data.message);
                }).catch(() => { progress.style.display = 'none'; fileInput.disabled = false; alert('❌ Ошибка сети'); });
        }
    });
    removeBtn?.addEventListener('click', function() {
        hiddenInput.value = '';
        previewContainer.style.display = 'none';
        previewImg.src = '';
    });
});
</script>
<?php require_once 'includes/footer.php'; ?>