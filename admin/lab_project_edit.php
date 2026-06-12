<?php
$activePage = 'lab_projects';
$pageTitle = 'Массовое редактирование проектов';
$summernote = true;

require_once 'includes/auth_check.php';

// Получаем ID выбранных проектов из GET-параметра
$idsParam = isset($_GET['ids']) ? $_GET['ids'] : '';
$selectedIds = $idsParam ? explode(',', $idsParam) : [];
$selectedIds = array_filter($selectedIds, function($id) {
    return filter_var($id, FILTER_VALIDATE_INT);
});

if (empty($selectedIds)) {
    redirect('lab_projects.php');
}

$error = '';
$success = '';
$uploadBaseDir = __DIR__ . '/../lab/img/projects/';
if (!is_dir($uploadBaseDir)) mkdir($uploadBaseDir, 0777, true);

// AJAX: Загрузка изображения для конкретного проекта
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    header('Content-Type: application/json');
    $projectId = (int)($_POST['project_id'] ?? 0);
    $response = ['success' => false, 'message' => 'Ошибка загрузки'];
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES['image']['tmp_name']);
    finfo_close($finfo);
    
    if (in_array($mimeType, $allowedTypes)) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = 'project_' . $projectId . '_' . uniqid() . '.' . $ext;
        $targetPath = $uploadBaseDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $response = ['success' => true, 'url' => 'img/projects/' . $fileName, 'project_id' => $projectId];
        } else {
            $response['message'] = 'Не удалось сохранить файл. Проверьте права на папку.';
        }
    } else {
        $response['message'] = 'Неподдерживаемый тип файла. Разрешены: JPG, PNG, GIF, WEBP';
    }
    
    echo json_encode($response);
    exit;
}

// Получаем данные выбранных проектов
try {
    $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
    $stmt = $pdo->prepare("SELECT * FROM lab_projects WHERE id IN ($placeholders) ORDER BY start_date DESC, sort_order ASC");
    $stmt->execute($selectedIds);
    $projects = $stmt->fetchAll();
    
    if (empty($projects)) {
        redirect('lab_projects.php');
    }
} catch (PDOException $e) { 
    die("Ошибка загрузки данных: " . $e->getMessage()); 
}

// Обработка массового сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all'])) {
    try {
        $pdo->beginTransaction();
        $updatedCount = 0;
        
        foreach ($projects as $project) {
            $id = $project['id'];
            
            $name = trim($_POST['name_' . $id] ?? '');
            $short_desc = trim($_POST['short_description_' . $id] ?? '');
            $full_desc = trim($_POST['full_description_' . $id] ?? '');
            $new_img = trim($_POST['img_url_' . $id] ?? '');
            $start_date = $_POST['start_date_' . $id] ?: null;
            $end_date = $_POST['end_date_' . $id] ?: null;
            $status = $_POST['status_' . $id] ?? 'active';
            $budget = $_POST['budget_' . $id] ?: null;
            $sort_order = (int)($_POST['sort_order_' . $id] ?? 0);
            $delete_img = isset($_POST['delete_img_' . $id]) ? true : false;
            
            $old_img = $project['img_url'] ?? '';
            
            if ($delete_img) {
                $new_img = '';
                if ($old_img) {
                    $oldPath = $uploadBaseDir . basename($old_img);
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
            } elseif ($new_img && $new_img !== $old_img && $old_img) {
                $oldPath = $uploadBaseDir . basename($old_img);
                if (file_exists($oldPath)) @unlink($oldPath);
            }
            
            if ($name) {
                $stmt = $pdo->prepare("UPDATE lab_projects SET 
                    name=?, 
                    short_description=?, 
                    full_description=?, 
                    img_url=?, 
                    start_date=?, 
                    end_date=?, 
                    status=?, 
                    budget=?, 
                    sort_order=?, 
                    updated_at=NOW() 
                    WHERE id=?");
                    
                $stmt->execute([
                    $name, $short_desc, $full_desc, $new_img, 
                    $start_date, $end_date, $status, $budget, $sort_order, $id
                ]);
                $updatedCount++;
            }
        }
        
        $pdo->commit();
        $success = "Успешно обновлено проектов: $updatedCount";
        
        // Перезагружаем данные
        $stmt = $pdo->prepare("SELECT * FROM lab_projects WHERE id IN ($placeholders) ORDER BY start_date DESC, sort_order ASC");
        $stmt->execute($selectedIds);
        $projects = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Ошибка БД: ' . $e->getMessage();
    }
}

$pageScript = '$(".summernote").summernote({ height: 200, lang: "ru-RU" });';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<style>
.edit-project-card {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 30px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.edit-project-card .card-header-custom {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.edit-project-card .card-header-custom h4 {
    margin: 0;
    color: #2c3e50;
}
.project-img-preview {
    max-width: 100%;
    max-height: 150px;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}
.photo-progress {
    display: none;
    margin-top: 5px;
}
.photo-progress.active {
    display: block;
}
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Массовое редактирование проектов</h1>
            <p class="text-muted mt-2">Редактирование выбранных проектов: <?= count($projects) ?> шт.</p>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="lab_projects.php">Вернуться к списку</a></div>
            <?php endif; ?>
            
            <form method="POST">
                <?php foreach ($projects as $project): ?>
                <div class="edit-project-card">
                    <div class="card-header-custom">
                        <h4>
                            <i class="fas fa-project-diagram"></i> 
                            Проект #<?= $project['id'] ?>: <?= htmlspecialchars($project['name'] ?? '') ?>
                        </h4>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Название проекта *</label>
                                <input type="text" name="name_<?= $project['id'] ?>" class="form-control" value="<?= htmlspecialchars($project['name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Краткое описание</label>
                                <textarea name="short_description_<?= $project['id'] ?>" class="form-control" rows="2"><?= htmlspecialchars($project['short_description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Полное описание</label>
                                <textarea name="full_description_<?= $project['id'] ?>" class="form-control summernote" rows="8"><?= htmlspecialchars($project['full_description'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Изображение</label>
                                <input type="hidden" name="img_url_<?= $project['id'] ?>" id="img_url_<?= $project['id'] ?>" value="<?= htmlspecialchars($project['img_url'] ?? '') ?>">
                                <input type="file" class="form-control form-control-sm img-input" data-project-id="<?= $project['id'] ?>" accept="image/jpeg,image/png,image/gif,image/webp">
                                <div class="photo-progress" id="progress_<?= $project['id'] ?>">
                                    <small><i class="fas fa-spinner fa-spin"></i> Загрузка...</small>
                                </div>
                                
                                <?php if (!empty($project['img_url'])): ?>
                                <div class="mt-2" id="preview_container_<?= $project['id'] ?>">
                                    <img src="../lab/<?= htmlspecialchars($project['img_url']) ?>" class="project-img-preview" id="preview_<?= $project['id'] ?>">
                                    <div class="form-check mt-1">
                                        <input type="checkbox" name="delete_img_<?= $project['id'] ?>" id="delete_img_<?= $project['id'] ?>" class="form-check-input" value="1">
                                        <label class="form-check-label small text-danger" for="delete_img_<?= $project['id'] ?>">
                                            <i class="fas fa-trash"></i> Удалить изображение
                                        </label>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="mt-2" id="preview_container_<?= $project['id'] ?>" style="display: none;">
                                    <img src="" class="project-img-preview" id="preview_<?= $project['id'] ?>">
                                    <div class="form-check mt-1">
                                        <input type="checkbox" name="delete_img_<?= $project['id'] ?>" id="delete_img_<?= $project['id'] ?>" class="form-check-input" value="1">
                                        <label class="form-check-label small text-danger" for="delete_img_<?= $project['id'] ?>">
                                            <i class="fas fa-trash"></i> Удалить изображение
                                        </label>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label>Дата начала</label>
                                <input type="date" name="start_date_<?= $project['id'] ?>" class="form-control" value="<?= htmlspecialchars($project['start_date'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Дата окончания</label>
                                <input type="date" name="end_date_<?= $project['id'] ?>" class="form-control" value="<?= htmlspecialchars($project['end_date'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Статус</label>
                                <select name="status_<?= $project['id'] ?>" class="form-control">
                                    <option value="active" <?= ($project['status'] ?? '') === 'active' ? 'selected' : '' ?>>Активный</option>
                                    <option value="completed" <?= ($project['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Завершён</option>
                                    <option value="planned" <?= ($project['status'] ?? '') === 'planned' ? 'selected' : '' ?>>Планируется</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Бюджет (руб.)</label>
                                <input type="number" step="0.01" name="budget_<?= $project['id'] ?>" class="form-control" value="<?= htmlspecialchars($project['budget'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Порядок сортировки</label>
                                <input type="number" name="sort_order_<?= $project['id'] ?>" class="form-control" value="<?= $project['sort_order'] ?? 0 ?>">
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
                                <a href="lab_projects.php" class="btn btn-secondary ml-2">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imgInputs = document.querySelectorAll('.img-input');
    
    imgInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const projectId = this.dataset.projectId;
            const file = this.files[0];
            
            if (!file) return;
            
            const formData = new FormData();
            formData.append('image', file);
            formData.append('project_id', projectId);
            
            const progressDiv = document.getElementById('progress_' + projectId);
            progressDiv.classList.add('active');
            input.disabled = true;
            
            fetch(window.location.href, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    progressDiv.classList.remove('active');
                    input.disabled = false;
                    input.value = '';
                    
                    if (data.success) {
                        document.getElementById('img_url_' + projectId).value = data.url;
                        const preview = document.getElementById('preview_' + projectId);
                        preview.src = '../lab/' + data.url;
                        const container = document.getElementById('preview_container_' + projectId);
                        if (container) container.style.display = 'block';
                        
                        const deleteCheckbox = document.getElementById('delete_img_' + projectId);
                        if (deleteCheckbox) deleteCheckbox.checked = false;
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => {
                    progressDiv.classList.remove('active');
                    input.disabled = false;
                    alert('Ошибка соединения');
                });
        });
    });
    
    const deleteCheckboxes = document.querySelectorAll('[id^="delete_img_"]');
    deleteCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const projectId = this.id.replace('delete_img_', '');
            const hiddenInput = document.getElementById('img_url_' + projectId);
            const preview = document.getElementById('preview_' + projectId);
            const container = document.getElementById('preview_container_' + projectId);
            
            if (this.checked) {
                hiddenInput.value = '';
                if (preview) preview.src = '';
                if (container) container.style.display = 'none';
            } else {
                const currentImg = hiddenInput.value;
                if (currentImg && preview) {
                    preview.src = '../lab/' + currentImg;
                    if (container) container.style.display = 'block';
                }
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>