<?php
$activePage = 'team';
$pageTitle = 'Редактирование сотрудника';
require_once 'includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';
$uploadBaseDir = __DIR__ . '/../lab/img/team/';
if (!is_dir($uploadBaseDir)) mkdir($uploadBaseDir, 0777, true);

// ============================================
// AJAX: Загрузка фото
// ============================================
if (isset($_POST['ajax_upload_team'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Ошибка загрузки'];
    if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['photo']['type'], $allowedTypes)) {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $fileName = 'team_' . uniqid() . '.' . $ext;
            $filePath = $uploadBaseDir . $fileName;
            $dbPath = 'img/team/' . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                $response = ['success' => true, 'url' => $dbPath];
            } else {
                $response['message'] = 'Не удалось переместить файл. Проверьте права папки.';
            }
        } else {
            $response['message'] = 'Неподдерживаемый тип файла (только картинки)';
        }
    }
    echo json_encode($response);
    exit;
}

// ============================================
// Получаем данные сотрудника
// ============================================
try {
    // Используем SELECT *, чтобы захватить все новые поля (email, phone, bio, education)
    $stmt = $pdo->prepare("SELECT * FROM team WHERE id = ?");
    $stmt->execute([$id]);
    $member = $stmt->fetch();
    
    if (!$member) {
        redirect('team.php');
    }
} catch (PDOException $e) { 
    die("Ошибка загрузки данных: " . $e->getMessage()); 
}

// ============================================
// Обработка сохранения
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    // Собираем все поля из формы
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $education = trim($_POST['education'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $new_photo = trim($_POST['photo_url'] ?? '');

    if (!$name || !$position) {
        $error = 'ФИО и должность обязательны';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Логика удаления старого фото при замене
            $old_photo = $member['photo_url'] ?? '';
            if ($new_photo && $new_photo !== $old_photo && $old_photo) {
                $oldPath = $uploadBaseDir . basename($old_photo);
                if (file_exists($oldPath)) @unlink($oldPath);
            }
            
            // Обновляем ВСЕ поля, включая новые
            $stmt = $pdo->prepare("UPDATE team SET 
                name=?, 
                position=?, 
                photo_url=?, 
                email=?, 
                phone=?, 
                bio=?, 
                education=?, 
                sort_order=? 
                WHERE id=?");
                
            $stmt->execute([
                $name, 
                $position, 
                $new_photo, 
                $email, 
                $phone, 
                $bio, 
                $education, 
                $sort_order, 
                $id
            ]);
            
            $pdo->commit();
            $success = 'Сотрудник успешно обновлён!';
            
            // Перезагружаем данные для отображения в форме
            $stmt = $pdo->prepare("SELECT * FROM team WHERE id = ?");
            $stmt->execute([$id]);
            $member = $stmt->fetch();
            
        } catch (PDOException $e) {
            $pdo->rollBack();
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
            <h1 class="m-0">Редактирование сотрудника #<?= $member['id'] ?></h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
            
            <form method="POST" class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ФИО *</label>
                                <!-- Используем null coalescing operator (??) для безопасности, если поле NULL в БД -->
                                <input type="text" name="name" class="form-control" value="<?= e($member['name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Должность *</label>
                                <input type="text" name="position" class="form-control" value="<?= e($member['position'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?= e($member['email'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Телефон</label>
                                <input type="text" name="phone" class="form-control" value="<?= e($member['phone'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Фотография</label>
                                <input type="hidden" name="photo_url" id="teamPhotoUrl" value="<?= e($member['photo_url'] ?? '') ?>">
                                <input type="file" id="teamPhotoInput" class="form-control" accept="image/*">
                                
                                <div id="imagePreviewContainer" class="mt-3" style="display: <?= !empty($member['photo_url']) ? 'block' : 'none' ?>;">
                                    <img src="../lab/<?= e($member['photo_url'] ?? '') ?>" id="imagePreview" class="img-fluid rounded border" style="max-height: 200px;">
                                    <button type="button" id="removeImageBtn" class="btn btn-sm btn-danger mt-2"><i class="fas fa-trash"></i> Удалить</button>
                                </div>
                                <div id="uploadProgress" class="mt-2" style="display: none;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div> Загрузка...
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Порядок сортировки</label>
                                <input type="number" name="sort_order" class="form-control" value="<?= $member['sort_order'] ?? 0 ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Образование / Степень</label>
                                <textarea name="education" class="form-control" rows="2"><?= e($member['education'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>О себе (Bio)</label>
                                <textarea name="bio" class="form-control" rows="3"><?= e($member['bio'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="save" class="btn btn-primary">Сохранить</button>
                    <a href="team.php" class="btn btn-secondary"> Назад</a>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('teamPhotoInput');
    const hiddenInput = document.getElementById('teamPhotoUrl');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImg = document.getElementById('imagePreview');
    const removeBtn = document.getElementById('removeImageBtn');
    const progress = document.getElementById('uploadProgress');

    // Загрузка при выборе файла
    fileInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const formData = new FormData();
            formData.append('ajax_upload_team', '1');
            formData.append('photo', this.files[0]);
            
            progress.style.display = 'block';
            fileInput.disabled = true;
            
            fetch(window.location.href, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    progress.style.display = 'none';
                    fileInput.disabled = false;
                    fileInput.value = ''; // Сброс input
                    
                    if (data.success) {
                        hiddenInput.value = data.url;
                        previewImg.src = '../lab/' + data.url;
                        previewContainer.style.display = 'block';
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => {
                    progress.style.display = 'none';
                    fileInput.disabled = false;
                    alert('Ошибка соединения');
                });
        }
    });

    // Удаление изображения
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            hiddenInput.value = '';
            previewContainer.style.display = 'none';
            previewImg.src = '';
        });
    }
});
</script>
<?php require_once 'includes/footer.php'; ?>