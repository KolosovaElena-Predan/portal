<?php
$activePage = 'services';
$pageTitle = 'Редактирование услуги';
$summernote = true;
require_once 'includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';
$uploadBaseDir = __DIR__ . '/../mip/img/services/';
if (!is_dir($uploadBaseDir)) mkdir($uploadBaseDir, 0777, true);

// ============================================
// AJAX: Загрузка изображения с ПК
// ============================================
if (isset($_POST['ajax_upload_service'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Ошибка загрузки'];

    if (!empty($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['image_file']['type'];

        if (in_array($fileType, $allowedTypes)) {
            $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
            $fileName = 'service_' . uniqid() . '.' . $ext;
            $filePath = $uploadBaseDir . $fileName;
            $dbPath = 'img/services/' . $fileName;

            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $filePath)) {
                $response = ['success' => true, 'url' => $dbPath];
            } else {
                $response['message'] = 'Не удалось переместить файл';
            }
        } else {
            $response['message'] = 'Неподдерживаемый тип файла';
        }
    }
    echo json_encode($response);
    exit;
}

// Получаем текущую услугу
try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch();
    if (!$service) redirect('services.php');
} catch (PDOException $e) { die("Ошибка: " . $e->getMessage()); }

// ============================================
// Обработка сохранения
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = trim($_POST['name'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $full_description = trim($_POST['full_description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $new_img_url = trim($_POST['img_url'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (!$name) {
        $error = 'Название обязательно';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Логика удаления старой картинки
            $old_img_url = $service['img_url'] ?? '';
            if ($new_img_url !== $old_img_url) {
                // Если старая картинка была, удаляем её файл с диска
                if ($old_img_url) {
                    $oldFilePath = $uploadBaseDir . basename($old_img_url);
                    if (file_exists($oldFilePath)) @unlink($oldFilePath);
                }
            }

            $stmt = $pdo->prepare("UPDATE services SET name=?, short_description=?, full_description=?, price=?, img_url=?, duration=?, is_active=?, sort_order=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$name, $short_description, $full_description, $price, $new_img_url, $duration, $is_active, $sort_order, $id]);
            
            $pdo->commit();
            $success = 'Услуга обновлена!';
            
            // Обновляем данные для отображения в форме
            $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
            $stmt->execute([$id]);
            $service = $stmt->fetch();
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
                                <textarea name="full_description" class="form-control summernote" rows="10"><?= e($service['full_description'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-section">
                                <label class="form-label">Цена (₽)</label>
                                <input type="number" step="0.01" name="price" class="form-control" value="<?= $service['price'] ?>">
                            </div>
                            
                            <!-- Секция загрузки изображения -->
                            <div class="form-section">
                                <label class="form-label">Изображение</label>
                                <!-- Скрытое поле хранит URL для БД -->
                                <input type="hidden" name="img_url" id="serviceImageUrl" value="<?= e($service['img_url'] ?? '') ?>">
                                <!-- Поле выбора файла -->
                                <input type="file" id="serviceImageInput" class="form-control" accept="image/*">
                                <small class="text-muted">Выберите новый файл для замены</small>
                                
                                <?php if (!empty($service['img_url'])): ?>
                                <div id="imagePreviewContainer" class="mt-3" style="display: block;">
                                    <img src="../mip/<?= e($service['img_url']) ?>" id="imagePreview" class="img-fluid rounded border" style="max-height: 200px;">
                                    <button type="button" id="removeImageBtn" class="btn btn-sm btn-danger mt-2">
                                        <i class="fas fa-trash"></i> Удалить
                                    </button>
                                </div>
                                <?php else: ?>
                                <div id="imagePreviewContainer" class="mt-3" style="display: none;">
                                    <img src="" id="imagePreview" class="img-fluid rounded border" style="max-height: 200px;">
                                    <button type="button" id="removeImageBtn" class="btn btn-sm btn-danger mt-2">Удалить</button>
                                </div>
                                <?php endif; ?>
                                
                                <div id="uploadProgress" class="mt-2" style="display: none;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div> Загрузка...
                                </div>
                            </div>
                            <!-- Конец секции -->

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
                    <a href="../mip/service_view.php?id=<?= $id ?>" target="_blank" class="btn btn-success"><i class="fas fa-external-link-alt"></i> Открыть</a>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('serviceImageInput');
    const hiddenInput = document.getElementById('serviceImageUrl');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImg = document.getElementById('imagePreview');
    const removeBtn = document.getElementById('removeImageBtn');
    const progress = document.getElementById('uploadProgress');

    // Загрузка при выборе файла
    fileInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const formData = new FormData();
            formData.append('ajax_upload_service', '1');
            formData.append('image_file', this.files[0]);

            progress.style.display = 'block';
            fileInput.disabled = true;

            fetch(window.location.href, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    progress.style.display = 'none';
                    fileInput.disabled = false;
                    fileInput.value = '';

                    if (data.success) {
                        hiddenInput.value = data.url;
                        previewImg.src = '../mip/' + data.url;
                        previewContainer.style.display = 'block';
                    } else {
                        alert('❌ ' + data.message);
                    }
                })
                .catch(err => {
                    progress.style.display = 'none';
                    fileInput.disabled = false;
                    alert('❌ Ошибка соединения');
                });
        }
    });

    // Удаление (очистка поля)
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