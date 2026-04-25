<?php
$activePage = 'team';
$pageTitle = 'Добавление сотрудника';
require_once 'includes/auth_check.php';
$error = '';
$success = '';
$uploadDir = __DIR__ . '/../lab/img/team/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// AJAX загрузка фото
if (isset($_POST['ajax_upload_team'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Ошибка'];
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['photo']['type'], $allowed)) {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $fileName = 'team_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $fileName)) {
                $response = ['success' => true, 'url' => 'img/team/' . $fileName];
            }
        }
    }
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $education = trim($_POST['education'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $photo_url = trim($_POST['photo_url'] ?? '');

    if (!$name || !$position) {
        $error = 'ФИО и должность обязательны';
    } else {
        try {
            $pdo->prepare("INSERT INTO team (name, position, photo_url, email, phone, bio, education, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$name, $position, $photo_url, $email, $phone, $bio, $education, $sort_order]);
            $success = 'Сотрудник добавлен!';
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
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Добавление сотрудника</h1></div></div>
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?> <a href="team.php">К списку</a></div><?php endif; ?>
            <form method="POST" class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label>ФИО *</label><input type="text" name="name" class="form-control" value="<?= e($_POST['name'] ?? '') ?>" required></div>
                            <div class="form-group"><label>Должность *</label><input type="text" name="position" class="form-control" value="<?= e($_POST['position'] ?? '') ?>" required></div>
                            <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>"></div>
                            <div class="form-group"><label>Телефон</label><input type="text" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? '') ?>"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Фотография</label>
                                <input type="hidden" name="photo_url" id="teamPhotoUrl" value="<?= e($_POST['photo_url'] ?? '') ?>">
                                <input type="file" id="teamPhotoInput" class="form-control" accept="image/*">
                                <div id="photoPreview" class="mt-2" style="display: <?= !empty($_POST['photo_url']) ? 'block' : 'none' ?>;">
                                    <img src="../mip/<?= e($_POST['photo_url'] ?? '') ?>" style="max-height: 150px; border-radius: 8px;">
                                    <button type="button" class="btn btn-sm btn-danger mt-1" id="removePhoto">Удалить</button>
                                </div>
                            </div>
                            <div class="form-group"><label>Порядок сортировки</label><input type="number" name="sort_order" class="form-control" value="<?= $_POST['sort_order'] ?? 0 ?>"></div>
                            <div class="form-group"><label>Образование</label><textarea name="education" class="form-control" rows="2"><?= e($_POST['education'] ?? '') ?></textarea></div>
                            <div class="form-group"><label>О себе</label><textarea name="bio" class="form-control" rows="3"><?= e($_POST['bio'] ?? '') ?></textarea></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="team.php" class="btn btn-secondary">Назад</a>
                </div>
            </form>
        </div>
    </section>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('teamPhotoInput');
    const hiddenInput = document.getElementById('teamPhotoUrl');
    const preview = document.getElementById('photoPreview');
    const previewImg = preview?.querySelector('img');
    const removeBtn = document.getElementById('removePhoto');
    
    fileInput?.addEventListener('change', function(e) {
        if (this.files?.[0]) {
            const fd = new FormData();
            fd.append('ajax_upload_team', '1');
            fd.append('photo', this.files[0]);
            fetch(window.location.href, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        hiddenInput.value = data.url;
                        if (previewImg) previewImg.src = '../lab/' + data.url;
                        preview.style.display = 'block';
                    } else alert('❌ ' + data.message);
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