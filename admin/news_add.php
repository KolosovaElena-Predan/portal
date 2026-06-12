<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$activePage = 'news';
$pageTitle = 'Добавление новости';
$summernote = true;
require_once 'includes/auth_check.php';
$error = '';
$success = '';
$newsId = null;

$uploadBaseDir = __DIR__ . '/../img/news/';
if (!is_dir($uploadBaseDir)) {
    if (!mkdir($uploadBaseDir, 0777, true)) {
        $error = 'Не удалось создать папку для изображений: ' . $uploadBaseDir;
    }
}


//Загрузка изображений

if (isset($_POST['ajax_upload']) && isset($_FILES['images'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => '', 'images' => []];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024;
    
    if (!isset($_SESSION['temp_news_images'])) $_SESSION['temp_news_images'] = [];
    
    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $fType = $_FILES['images']['type'][$key];
            $fSize = $_FILES['images']['size'][$key];
            if (in_array($fType, $allowedTypes) && $fSize <= $maxSize) {
                $ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                $fname = 'temp_' . uniqid() . '.' . $ext;
                $fpath = $uploadBaseDir . $fname;
                $dbPath = 'img/news/' . $fname;
                if (move_uploaded_file($tmpName, $fpath)) {
                    $tid = 'temp_' . uniqid();
                    $_SESSION['temp_news_images'][] = ['temp_id' => $tid, 'file_name' => $fname, 'image_url' => $dbPath];
                    $response['images'][] = ['temp_id' => $tid, 'image_url' => $dbPath];
                } else {
                    $response['message'] .= "Ошибка перемещения файла. Проверьте права папки: $uploadBaseDir\n";
                }
            } else {
                $response['message'] .= "Файл не прошел проверку (тип/размер).\n";
            }
        }
    }
    $response['success'] = !empty($response['images']);
    echo json_encode($response);
    exit;
}


// Удаление временного изображения

if (isset($_POST['ajax_remove'])) {
    header('Content-Type: application/json');
    $tempId = $_POST['temp_id'] ?? '';
    $found = false;
    if (isset($_SESSION['temp_news_images'])) {
        foreach ($_SESSION['temp_news_images'] as $k => $img) {
            if ($img['temp_id'] === $tempId) {
                $fp = $uploadBaseDir . $img['file_name'];
                if (file_exists($fp)) @unlink($fp);
                unset($_SESSION['temp_news_images'][$k]);
                $_SESSION['temp_news_images'] = array_values($_SESSION['temp_news_images']);
                $found = true;
                break;
            }
        }
    }
    echo json_encode(['success' => $found]);
    exit;
}


// Обработка сохранения

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['save']) || isset($_POST['open_preview']))) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $section = $_POST['section'] ?? 'lab'; // Получаем раздел
    
    
    if (preg_match('#^<p>(.*?)</p>$#s', $content, $m)) $content = trim($m[1]);
    
    $mainImageTempId = $_POST['main_image_temp_id'] ?? null;
    $openPreview = isset($_POST['open_preview']);
    
    if (!$title) {
        $error = 'Заголовок обязателен';
    } else {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO news (title, content, section, datetime) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$title, $content, $section]);
            $newsId = $pdo->lastInsertId();
            
            if (!empty($_SESSION['temp_news_images'])) {
                foreach ($_SESSION['temp_news_images'] as $idx => $tempImg) {
                    $isMain = ($tempImg['temp_id'] === $mainImageTempId) ? 1 : 0;
                    $stmt = $pdo->prepare("INSERT INTO news_images (news_id, image_url, is_main, sort_order) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$newsId, $tempImg['image_url'], $isMain, $idx]);
                }
                unset($_SESSION['temp_news_images']);
            }
            $pdo->commit();
            $success = 'Новость добавлена! ID: ' . $newsId;
            
            if ($openPreview && $newsId) {
                
                header('Location: ../' . $section . '/news_view.php?id=' . $newsId);
                exit;
            }
            $_POST = [];
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

$tempImages = $_SESSION['temp_news_images'] ?? [];
$mainImageTempId = $_POST['main_image_temp_id'] ?? ($tempImages[0]['temp_id'] ?? null);
$section = $_POST['section'] ?? 'lab'; 
$formData = ['title' => $_POST['title'] ?? '', 'content' => $_POST['content'] ?? ''];
$pageScript = '$(".summernote").summernote({ height: 400, lang: "ru-RU" });';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Добавление новости</h1></div></div>
    <section class="content"><div class="container-fluid">
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success && $newsId): ?>
            <div class="alert alert-success"><?= e($success) ?> <a href="../<?= e($section) ?>/news_view.php?id=<?= $newsId ?>" target="_blank" class="alert-link"><i class="fas fa-external-link-alt"></i> Открыть на сайте</a></div>
        <?php elseif ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
        
        <form method="POST" class="card" id="newsForm">
            <input type="hidden" name="main_image_temp_id" id="mainImageTempId" value="<?= e($mainImageTempId ?? '') ?>">
            <div class="card-body">
                <div class="form-group">
                    <label>Заголовок *</label>
                    <input type="text" name="title" class="form-control" value="<?= e($formData['title']) ?>" required>
                </div>
                
                <!-- Выбор раздела -->
                <div class="form-group">
                    <label>Раздел *</label>
                    <select name="section" class="form-control" required>
                        <option value="lab" <?= $section === 'lab' ? 'selected' : '' ?>>Лаборатория (lab)</option>
                        <option value="mip" <?= $section === 'mip' ? 'selected' : '' ?>>МИП (mip)</option>
                    </select>
                    <small class="text-muted">Где будет отображаться новость: на сайте лаборатории или МИП</small>
                </div>
                
                <div class="form-group">
                    <label>Содержание</label>
                    <textarea name="content" class="form-control summernote" rows="15"><?= e($formData['content']) ?></textarea>
                </div>
                
                <div class="mt-4">
                    <h5>Изображения (<span id="imageCount"><?= count($tempImages) ?></span>)</h5>
                    <div class="row" id="imagesContainer">
                        <?php if (!empty($tempImages)): ?>
                            <?php foreach ($tempImages as $img): ?>
                                <div class="col-md-3 mb-3 image-card" data-temp-id="<?= e($img['temp_id']) ?>">
                                    <div class="card">
                                        <img src="../<?= e($img['image_url']) ?>" class="card-img-top" style="height: 150px; object-fit: cover;" alt="">
                                        <div class="card-body p-2">
                                            <div class="custom-control custom-radio mb-2">
                                                <input type="radio" name="main_image_radio" class="custom-control-input main-radio" value="<?= e($img['temp_id']) ?>" id="main_<?= e($img['temp_id']) ?>" <?= ($img['temp_id'] === ($mainImageTempId ?? '')) ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="main_<?= e($img['temp_id']) ?>"> Главное</label>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-danger btn-block remove-image" data-temp-id="<?= e($img['temp_id']) ?>"><i class="fas fa-trash"></i> Удалить</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12"><p class="text-muted">Изображений нет</p></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group mt-3">
                        <label>Добавить новые изображения</label>
                        <input type="file" name="images[]" id="imageInput" class="form-control" multiple accept="image/*">
                        <small class="text-muted">Файлы загрузятся сразу после выбора</small>
                        <div id="uploadProgress" class="mt-2" style="display: none;"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="ml-2">Загрузка...</span></div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="save" class="btn btn-primary"><i class="fas fa-plus"></i> Добавить</button>
                <button type="submit" name="open_preview" value="1" class="btn btn-success"><i class="fas fa-external-link-alt"></i> Сохранить и открыть</button>
                <a href="news.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Отмена</a>
            </div>
        </form>
    </div></section>
</div>
<script>
const imageInput = document.getElementById('imageInput');
const imagesContainer = document.getElementById('imagesContainer');
const imageCount = document.getElementById('imageCount');
const uploadProgress = document.getElementById('uploadProgress');
const mainImageTempId = document.getElementById('mainImageTempId');

if (imageInput) {
    imageInput.addEventListener('change', function(e) {
        if (this.files && this.files.length > 0) {
            const formData = new FormData();
            formData.append('ajax_upload', '1');
            for (let i = 0; i < this.files.length; i++) formData.append('images[]', this.files[i]);
            uploadProgress.style.display = 'block';
            imageInput.disabled = true;
            fetch(window.location.href, { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    uploadProgress.style.display = 'none';
                    imageInput.disabled = false;
                    imageInput.value = '';
                    if (data.success && data.images.length > 0) {
                        const noMsg = imagesContainer.querySelector('.text-muted');
                        if (noMsg) noMsg.remove();
                        data.images.forEach(img => {
                            const html = `<div class="col-md-3 mb-3 image-card" data-temp-id="${img.temp_id}">
<div class="card"><img src="../${img.image_url}" class="card-img-top" style="height: 150px; object-fit: cover;" alt="">
<div class="card-body p-2"><div class="custom-control custom-radio mb-2">
<input type="radio" name="main_image_radio" class="custom-control-input main-radio" value="${img.temp_id}" id="main_${img.temp_id}" ${document.querySelectorAll('.image-card').length === 0 ? 'checked' : ''}>
<label class="custom-control-label" for="main_${img.temp_id}"> Главное</label></div>
<button type="button" class="btn btn-sm btn-danger btn-block remove-image" data-temp-id="${img.temp_id}"><i class="fas fa-trash"></i> Удалить</button></div></div></div>`;
                            imagesContainer.insertAdjacentHTML('beforeend', html);
                        });
                        imageCount.textContent = document.querySelectorAll('.image-card').length;
                        if (document.querySelectorAll('.image-card').length === 1) {
                            const firstRadio = document.querySelector('.main-radio');
                            if (firstRadio) { firstRadio.checked = true; mainImageTempId.value = firstRadio.value; }
                        }
                    } else if (data.message) { alert(data.message); }
                })
                .catch(err => {
                    uploadProgress.style.display = 'none'; imageInput.disabled = false; alert(' Ошибка сети: ' + err);
                });
        }
    });
}

imagesContainer.addEventListener('click', function(e) {
    if (e.target.closest('.remove-image')) {
        const btn = e.target.closest('.remove-image');
        const tempId = btn.dataset.tempId;
        fetch(window.location.href, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'ajax_remove=1&temp_id=' + encodeURIComponent(tempId) })
            .then(r => r.json()).then(data => {
                if (data.success) {
                    btn.closest('.image-card')?.remove();
                    imageCount.textContent = document.querySelectorAll('.image-card').length;
                    if (tempId === mainImageTempId.value) {
                        const firstRadio = document.querySelector('.main-radio');
                        if (firstRadio) { mainImageTempId.value = firstRadio.value; firstRadio.checked = true; }
                        else { mainImageTempId.value = ''; }
                    }
                    if (document.querySelectorAll('.image-card').length === 0) imagesContainer.innerHTML = '<div class="col-12"><p class="text-muted">Изображений нет</p></div>';
                }
            });
    }
});

imagesContainer.addEventListener('change', function(e) {
    if (e.target.classList.contains('main-radio')) { mainImageTempId.value = e.target.value; }
});
</script>
<?php require_once 'includes/footer.php'; ?>