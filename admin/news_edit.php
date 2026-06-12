<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$activePage = 'news';
$pageTitle = 'Редактирование новости';
$summernote = true;
require_once 'includes/auth_check.php';
$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';

$uploadBaseDir = __DIR__ . '/../img/news/';
if (!is_dir($uploadBaseDir)) mkdir($uploadBaseDir, 0777, true);

// 1. Удаление существующего изображения

if (isset($_GET['delete_image']) && isset($_GET['image_id'])) {
    $imageId = (int)$_GET['image_id'];
    try {
        $stmt = $pdo->prepare("SELECT image_url FROM news_images WHERE id = ? AND news_id = ?");
        $stmt->execute([$imageId, $id]);
        $img = $stmt->fetch();
        if ($img) {
            $filePath = $uploadBaseDir . basename($img['image_url']);
            if (file_exists($filePath)) @unlink($filePath);
        }
        $pdo->prepare("DELETE FROM news_images WHERE id = ? AND news_id = ?")->execute([$imageId, $id]);
        $success = 'Изображение удалено!';
    } catch (PDOException $e) { $error = 'Ошибка: ' . $e->getMessage(); }
    header("Location: news_edit.php?id=$id"); exit;
}


// 2. Загрузка временных изображений

if (isset($_POST['ajax_upload']) && isset($_FILES['images'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'images' => []];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!isset($_SESSION['temp_news_images'])) $_SESSION['temp_news_images'] = [];
    foreach ($_FILES['images']['tmp_name'] as $k => $tmp) {
        if ($_FILES['images']['error'][$k] === UPLOAD_ERR_OK) {
            $fType = $_FILES['images']['type'][$k];
            $fSize = $_FILES['images']['size'][$k];
            if (in_array($fType, $allowed) && $fSize <= 5*1024*1024) {
                $ext = pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION);
                $fname = 'temp_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($tmp, $uploadBaseDir . $fname)) {
                    $tid = 'temp_' . uniqid();
                    $_SESSION['temp_news_images'][] = ['temp_id' => $tid, 'file_name' => $fname, 'image_url' => 'img/news/' . $fname];
                    $response['images'][] = ['temp_id' => $tid, 'image_url' => 'img/news/' . $fname];
                }
            }
        }
    }
    $response['success'] = !empty($response['images']);
    echo json_encode($response); exit;
}


// 3.  Удаление временного изображения

if (isset($_POST['ajax_remove'])) {
    header('Content-Type: application/json');
    $tid = $_POST['temp_id'] ?? '';
    $found = false;
    if (isset($_SESSION['temp_news_images'])) {
        foreach ($_SESSION['temp_news_images'] as $k => $v) {
            if ($v['temp_id'] === $tid) {
                $fp = $uploadBaseDir . $v['file_name'];
                if (file_exists($fp)) @unlink($fp);
                unset($_SESSION['temp_news_images'][$k]);
                $_SESSION['temp_news_images'] = array_values($_SESSION['temp_news_images']);
                $found = true; break;
            }
        }
    }
    echo json_encode(['success' => $found]); exit;
}


// 4. Получаем новость и её изображения

try {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $news = $stmt->fetch();
    if (!$news) { header('Location: news.php'); exit; }
    $stmt = $pdo->prepare("SELECT * FROM news_images WHERE news_id = ? ORDER BY sort_order, is_main DESC");
    $stmt->execute([$id]);
    $existingImages = $stmt->fetchAll();
} catch (PDOException $e) { die("Ошибка загрузки: " . $e->getMessage()); }


// 5. Обработка сохранения

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['save']) || isset($_POST['open_preview']))) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $section = $_POST['section'] ?? 'lab'; 
    
    if (preg_match('#^<p>(.*?)</p>$#s', $content, $m)) $content = trim($m[1]);
    
    $mainImageId = $_POST['main_image_id'] ?? '';
    $openPreview = isset($_POST['open_preview']);
    
    if (!$title) {
        $error = 'Заголовок обязателен';
    } else {
        try {
            $pdo->beginTransaction();
           
            $pdo->prepare("UPDATE news SET title=?, content=?, section=? WHERE id=?")
                ->execute([$title, $content, $section, $id]);
            
            $pdo->prepare("UPDATE news_images SET is_main=0 WHERE news_id=?")->execute([$id]);
            
            if (substr($mainImageId, 0, 3) === 'db_') {
                $dbImgId = (int)substr($mainImageId, 3);
                $pdo->prepare("UPDATE news_images SET is_main=1 WHERE id=? AND news_id=?")->execute([$dbImgId, $id]);
            }
            
            if (!empty($_SESSION['temp_news_images'])) {
                $maxSort = $pdo->query("SELECT MAX(sort_order) FROM news_images WHERE news_id=$id")->fetchColumn() ?? -1;
                foreach ($_SESSION['temp_news_images'] as $idx => $t) {
                    $isMain = (substr($mainImageId, 0, 5) === 'temp_' && $mainImageId === $t['temp_id']) ? 1 : 0;
                    $pdo->prepare("INSERT INTO news_images (news_id, image_url, is_main, sort_order) VALUES (?, ?, ?, ?)")
                        ->execute([$id, $t['image_url'], $isMain, $maxSort + 1 + $idx]);
                }
                unset($_SESSION['temp_news_images']);
            }
            
            $hasMain = $pdo->query("SELECT COUNT(*) FROM news_images WHERE news_id=$id AND is_main=1")->fetchColumn();
            if ($hasMain == 0) {
                $pdo->prepare("UPDATE news_images SET is_main=1 WHERE news_id=? ORDER BY id ASC LIMIT 1")->execute([$id]);
            }
            
            $pdo->commit();
            $success = 'Новость обновлена!';
            if ($openPreview) {
           
                header('Location: ../' . $section . '/news_view.php?id=' . $id);
                exit;
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

$tempImages = $_SESSION['temp_news_images'] ?? [];
$currentMain = $_POST['main_image_id'] ?? '';
if (!$currentMain) {
    foreach ($existingImages as $img) { if ($img['is_main']) { $currentMain = 'db_' . $img['id']; break; } }
}
$section = $_POST['section'] ?? $news['section'] ?? 'lab';

$pageScript = '$(".summernote").summernote({ height: 400, lang: "ru-RU" });';
require_once 'includes/header.php'; require_once 'includes/navbar.php'; require_once 'includes/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Редактирование новости #<?= $news['id'] ?></h1></div></div>
    <section class="content"><div class="container-fluid">
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
        
        <form method="POST" class="card" id="newsForm">
            <input type="hidden" name="main_image_id" id="mainImageId" value="<?= e($currentMain) ?>">
            <div class="card-body">
                <div class="form-group">
                    <label>Заголовок *</label>
                    <input type="text" name="title" class="form-control" value="<?= e($news['title']) ?>" required>
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
                    <textarea name="content" class="form-control summernote" rows="15"><?= e($news['content']) ?></textarea>
                </div>
                
                <?php if (!empty($existingImages)): ?>
                <div class="mt-4"><h5>Загруженные изображения</h5>
                    <div class="row">
                        <?php foreach ($existingImages as $img): ?>
                        <div class="col-md-3 mb-3 image-card" data-type="db" data-id="<?= $img['id'] ?>">
                            <div class="card <?= $img['is_main'] ? 'border-success' : '' ?>">
                                <img src="../<?= e($img['image_url']) ?>" class="card-img-top" style="height: 150px; object-fit: cover;" alt="">
                                <div class="card-body p-2">
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" name="main_image_radio" class="custom-control-input main-radio" value="db_<?= $img['id'] ?>" id="main_db_<?= $img['id'] ?>" <?= $img['is_main'] ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="main_db_<?= $img['id'] ?>"> Главное</label>
                                    </div>
                                    <a href="?id=<?= $id ?>&delete_image=1&image_id=<?= $img['id'] ?>" class="btn btn-sm btn-danger btn-block" onclick="return confirm('Удалить изображение?')"><i class="fas fa-trash"></i> Удалить</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mt-4"><h5>Добавить новые (<span id="imageCount"><?= count($tempImages) ?></span>)</h5>
                    <div class="row" id="imagesContainer">
                        <?php if (!empty($tempImages)): ?>
                            <?php foreach ($tempImages as $img): ?>
                            <div class="col-md-3 mb-3 image-card" data-type="temp" data-temp-id="<?= e($img['temp_id']) ?>">
                                <div class="card">
                                    <img src="../<?= e($img['image_url']) ?>" class="card-img-top" style="height: 150px; object-fit: cover;" alt="">
                                    <div class="card-body p-2">
                                        <div class="custom-control custom-radio mb-2">
                                            <input type="radio" name="main_image_radio" class="custom-control-input main-radio" value="<?= e($img['temp_id']) ?>" id="main_<?= e($img['temp_id']) ?>" <?= (substr($currentMain, 0, 5) === 'temp_' && $currentMain === $img['temp_id']) ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="main_<?= e($img['temp_id']) ?>"> Главное</label>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger btn-block remove-image" data-temp-id="<?= e($img['temp_id']) ?>"><i class="fas fa-trash"></i> Удалить</button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12"><p class="text-muted">Новых изображений нет</p></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group mt-3">
                        <label>Выбрать файлы</label>
                        <input type="file" name="images[]" id="imageInput" class="form-control" multiple accept="image/*">
                        <div id="uploadProgress" class="mt-2" style="display: none;"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="ml-2">Загрузка...</span></div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="save" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить</button>
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
const mainImageId = document.getElementById('mainImageId');

document.addEventListener('change', function(e) { if (e.target.classList.contains('main-radio')) mainImageId.value = e.target.value; });

if (imageInput) {
    imageInput.addEventListener('change', function(e) {
        if (this.files && this.files.length > 0) {
            const formData = new FormData();
            formData.append('ajax_upload', '1');
            for (let i = 0; i < this.files.length; i++) formData.append('images[]', this.files[i]);
            uploadProgress.style.display = 'block'; imageInput.disabled = true;
            fetch(window.location.href, { method: 'POST', body: formData })
                .then(r => r.json()).then(data => {
                    uploadProgress.style.display = 'none'; imageInput.disabled = false; imageInput.value = '';
                    if (data.success && data.images.length > 0) {
                        const noMsg = imagesContainer.querySelector('.text-muted'); if (noMsg) noMsg.remove();
                        data.images.forEach(img => {
                            const html = `<div class="col-md-3 mb-3 image-card" data-type="temp" data-temp-id="${img.temp_id}"><div class="card"><img src="../${img.image_url}" class="card-img-top" style="height: 150px; object-fit: cover;" alt=""><div class="card-body p-2"><div class="custom-control custom-radio mb-2"><input type="radio" name="main_image_radio" class="custom-control-input main-radio" value="${img.temp_id}" id="main_${img.temp_id}"><label class="custom-control-label" for="main_${img.temp_id}">📌 Главное</label></div><button type="button" class="btn btn-sm btn-danger btn-block remove-image" data-temp-id="${img.temp_id}"><i class="fas fa-trash"></i> Удалить</button></div></div></div>`;
                            imagesContainer.insertAdjacentHTML('beforeend', html);
                        });
                        imageCount.textContent = document.querySelectorAll('#imagesContainer .image-card').length;
                    }
                }).catch(err => { uploadProgress.style.display='none'; imageInput.disabled=false; alert(' Ошибка: '+err); });
        }
    });
}

imagesContainer.addEventListener('click', function(e) {
    if (e.target.closest('.remove-image')) {
        const btn = e.target.closest('.remove-image');
        const tempId = btn.dataset.tempId;
        fetch(window.location.href, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'ajax_remove=1&temp_id='+encodeURIComponent(tempId) })
            .then(r=>r.json()).then(data=>{
                if(data.success){
                    btn.closest('.image-card')?.remove();
                    imageCount.textContent = document.querySelectorAll('#imagesContainer .image-card').length;
                    if (tempId === mainImageId.value) {
                        const first = document.querySelector('.main-radio');
                        if (first) { mainImageId.value = first.value; first.checked = true; }
                        else { mainImageId.value = ''; }
                    }
                    if (document.querySelectorAll('#imagesContainer .image-card').length === 0) imagesContainer.innerHTML = '<div class="col-12"><p class="text-muted">Новых изображений нет</p></div>';
                }
            });
    }
});
</script>
<?php require_once 'includes/footer.php'; ?>