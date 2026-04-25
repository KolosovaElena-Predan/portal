<?php
$activePage = 'lab_projects';
$pageTitle = 'Добавление проекта';
$summernote = true;
require_once 'includes/auth_check.php';
$error = '';
$success = '';
$uploadDir = __DIR__ . '/../mip/img/projects/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// AJAX загрузка изображения
if (isset($_POST['ajax_upload_project'])) {
    header('Content-Type: application/json');
    $response = ['success' => false];
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['image']['type'], $allowed)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = 'project_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                $response = ['success' => true, 'url' => 'img/projects/' . $fileName];
            }
        }
    }
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $short_desc = trim($_POST['short_description'] ?? '');
    $full_desc = trim($_POST['full_description'] ?? '');
    $img_url = trim($_POST['img_url'] ?? '');
    $start_date = $_POST['start_date'] ?: null;
    $end_date = $_POST['end_date'] ?: null;
    $status = $_POST['status'] ?? 'active';
    $budget = $_POST['budget'] ?: null;
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (!$name) {
        $error = 'Название проекта обязательно';
    } else {
        try {
            $pdo->prepare("INSERT INTO lab_projects (name, short_description, full_description, img_url, start_date, end_date, status, budget, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$name, $short_desc, $full_desc, $img_url, $start_date, $end_date, $status, $budget, $sort_order]);
            $success = 'Проект добавлен!';
            $_POST = [];
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
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Добавление проекта</h1></div></div>
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?> <a href="lab_projects.php">К списку</a></div><?php endif; ?>
            <form method="POST" class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group"><label>Название проекта *</label><input type="text" name="name" class="form-control" value="<?= e($_POST['name'] ?? '') ?>" required></div>
                            <div class="form-group"><label>Краткое описание</label><textarea name="short_description" class="form-control" rows="2"><?= e($_POST['short_description'] ?? '') ?></textarea></div>
                            <div class="form-group"><label>Полное описание</label><textarea name="full_description" class="form-control summernote" rows="10"><?= e($_POST['full_description'] ?? '') ?></textarea></div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Изображение</label>
                                <input type="hidden" name="img_url" id="projectImgUrl" value="<?= e($_POST['img_url'] ?? '') ?>">
                                <input type="file" id="projectImgInput" class="form-control" accept="image/*">
                                <div id="imgPreview" class="mt-2" style="display: <?= !empty($_POST['img_url']) ? 'block' : 'none' ?>;">
                                    <img src="../mip/<?= e($_POST['img_url'] ?? '') ?>" style="max-height: 150px; border-radius: 8px;">
                                    <button type="button" class="btn btn-sm btn-danger mt-1" id="removeImg">Удалить</button>
                                </div>
                            </div>
                            <div class="form-group"><label>Дата начала</label><input type="date" name="start_date" class="form-control" value="<?= e($_POST['start_date'] ?? '') ?>"></div>
                            <div class="form-group"><label>Дата окончания</label><input type="date" name="end_date" class="form-control" value="<?= e($_POST['end_date'] ?? '') ?>"></div>
                            <div class="form-group"><label>Статус</label>
                                <select name="status" class="form-control">
                                    <option value="active" <?= ($_POST['status'] ?? '') === 'active' ? 'selected' : '' ?>>Активный</option>
                                    <option value="completed" <?= ($_POST['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Завершён</option>
                                    <option value="planned" <?= ($_POST['status'] ?? '') === 'planned' ? 'selected' : '' ?>>Планируется</option>
                                </select>
                            </div>
                            <div class="form-group"><label>Бюджет (₽)</label><input type="number" step="0.01" name="budget" class="form-control" value="<?= e($_POST['budget'] ?? '') ?>"></div>
                            <div class="form-group"><label>Порядок</label><input type="number" name="sort_order" class="form-control" value="<?= $_POST['sort_order'] ?? 0 ?>"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
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
    const preview = document.getElementById('imgPreview');
    const previewImg = preview?.querySelector('img');
    const removeBtn = document.getElementById('removeImg');
    
    fileInput?.addEventListener('change', function(e) {
        if (this.files?.[0]) {
            const fd = new FormData();
            fd.append('ajax_upload_project', '1');
            fd.append('image', this.files[0]);
            fetch(window.location.href, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        hiddenInput.value = data.url;
                        if (previewImg) previewImg.src = '../mip/' + data.url;
                        preview.style.display = 'block';
                    }
                });
        }
    });
    removeBtn?.addEventListener('click', function() {
        hiddenInput.value = '';
        preview.style.display = 'none';
        fileInput.value = '';
    });
});
</script>
<?php require_once 'includes/footer.php'; ?>