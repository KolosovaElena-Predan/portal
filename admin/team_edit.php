<?php
$activePage = 'team';
$pageTitle = 'Массовое редактирование сотрудников';
require_once 'includes/auth_check.php';

// Получаем ID выбранных сотрудников из GET-параметра
$idsParam = isset($_GET['ids']) ? $_GET['ids'] : '';
$selectedIds = $idsParam ? explode(',', $idsParam) : [];
$selectedIds = array_filter($selectedIds, function($id) {
    return filter_var($id, FILTER_VALIDATE_INT);
});

if (empty($selectedIds)) {
    redirect('team.php');
}

$error = '';
$success = '';
$uploadBaseDir = __DIR__ . '/../lab/img/team/';
if (!is_dir($uploadBaseDir)) mkdir($uploadBaseDir, 0777, true);

// ============================================
// AJAX: Загрузка фото для конкретного сотрудника
// ============================================
if (isset($_POST['ajax_upload_team'])) {
    header('Content-Type: application/json');
    $memberId = (int)($_POST['member_id'] ?? 0);
    $response = ['success' => false, 'message' => 'Ошибка загрузки'];
    
    if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['photo']['type'], $allowedTypes)) {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $fileName = 'team_' . $memberId . '_' . uniqid() . '.' . $ext;
            $filePath = $uploadBaseDir . $fileName;
            $dbPath = 'img/team/' . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                $response = ['success' => true, 'url' => $dbPath, 'member_id' => $memberId];
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
// Получаем данные выбранных сотрудников
// ============================================
try {
    $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
    $stmt = $pdo->prepare("SELECT * FROM team WHERE id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
    $stmt->execute($selectedIds);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($members)) {
        redirect('team.php');
    }
} catch (PDOException $e) { 
    die("Ошибка загрузки данных: " . $e->getMessage()); 
}

// ============================================
// Обработка массового сохранения
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all'])) {
    try {
        $pdo->beginTransaction();
        $updatedCount = 0;
        
        foreach ($members as $member) {
            $id = $member['id'];
            
            $name = trim($_POST['name_' . $id] ?? '');
            $position = trim($_POST['position_' . $id] ?? '');
            $email = trim($_POST['email_' . $id] ?? '');
            $phone = trim($_POST['phone_' . $id] ?? '');
            $bio = trim($_POST['bio_' . $id] ?? '');
            $education = trim($_POST['education_' . $id] ?? '');
            $sort_order = (int)($_POST['sort_order_' . $id] ?? 0);
            $new_photo = trim($_POST['photo_url_' . $id] ?? '');
            $delete_photo = isset($_POST['delete_photo_' . $id]) ? true : false;
            
            $old_photo = $member['photo_url'] ?? '';
            
            // Обработка фото
            if ($delete_photo) {
                $new_photo = '';
                if ($old_photo) {
                    $oldPath = $uploadBaseDir . basename($old_photo);
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
            } elseif ($new_photo && $new_photo !== $old_photo && $old_photo) {
                $oldPath = $uploadBaseDir . basename($old_photo);
                if (file_exists($oldPath)) @unlink($oldPath);
            }
            
            if ($name && $position) {
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
                $updatedCount++;
            }
        }
        
        $pdo->commit();
        $success = "Успешно обновлено сотрудников: $updatedCount";
        
        // Перезагружаем данные для отображения
        $stmt = $pdo->prepare("SELECT * FROM team WHERE id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
        $stmt->execute($selectedIds);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
.edit-member-card {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 30px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.edit-member-card .card-header-custom {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.edit-member-card .card-header-custom h4 {
    margin: 0;
    color: #2c3e50;
}
.member-photo-preview {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
}
.photo-controls {
    margin-top: 10px;
}
.photo-progress {
    display: none;
    margin-top: 5px;
}
.photo-progress.active {
    display: block;
}
.delete-photo-checkbox {
    margin-top: 8px;
}
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Массовое редактирование сотрудников</h1>
            <p class="text-muted mt-2">Редактирование выбранных сотрудников: <?= count($members) ?> шт.</p>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <?php foreach ($members as $index => $member): ?>
                <div class="edit-member-card">
                    <div class="card-header-custom">
                        <h4>
                            <i class="fas fa-user-circle"></i> 
                            Сотрудник #<?= $member['id'] ?>: <?= htmlspecialchars($member['name'] ?? '') ?>
                        </h4>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <!-- Фото -->
                            <div class="form-group">
                                <label>Фотография</label>
                                <input type="hidden" name="photo_url_<?= $member['id'] ?>" id="photo_url_<?= $member['id'] ?>" value="<?= htmlspecialchars($member['photo_url'] ?? '') ?>">
                                
                                <?php if (!empty($member['photo_url'])): ?>
                                    <img src="../lab/<?= htmlspecialchars($member['photo_url']) ?>" class="member-photo-preview" id="preview_<?= $member['id'] ?>">
                                <?php else: ?>
                                    <img src="../lab/img/placeholder-user.png" class="member-photo-preview" id="preview_<?= $member['id'] ?>" onerror="this.src='https://via.placeholder.com/100?text=No+Photo'">
                                <?php endif; ?>
                                
                                <div class="photo-controls">
                                    <input type="file" class="form-control form-control-sm photo-input" data-member-id="<?= $member['id'] ?>" accept="image/*">
                                    <div class="photo-progress" id="progress_<?= $member['id'] ?>">
                                        <small><i class="fas fa-spinner fa-spin"></i> Загрузка...</small>
                                    </div>
                                    <div class="form-check delete-photo-checkbox">
                                        <input type="checkbox" name="delete_photo_<?= $member['id'] ?>" id="delete_photo_<?= $member['id'] ?>" class="form-check-input" value="1">
                                        <label class="form-check-label small text-danger" for="delete_photo_<?= $member['id'] ?>">
                                            <i class="fas fa-trash"></i> Удалить фото
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>ФИО *</label>
                                        <input type="text" name="name_<?= $member['id'] ?>" class="form-control" value="<?= htmlspecialchars($member['name'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Должность *</label>
                                        <input type="text" name="position_<?= $member['id'] ?>" class="form-control" value="<?= htmlspecialchars($member['position'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email_<?= $member['id'] ?>" class="form-control" value="<?= htmlspecialchars($member['email'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Телефон</label>
                                        <input type="text" name="phone_<?= $member['id'] ?>" class="form-control" value="<?= htmlspecialchars($member['phone'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Порядок сортировки</label>
                                        <input type="number" name="sort_order_<?= $member['id'] ?>" class="form-control" value="<?= $member['sort_order'] ?? 0 ?>">
                                        <small class="text-muted">Меньше значение = выше в списке</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Образование / Степень</label>
                                        <textarea name="education_<?= $member['id'] ?>" class="form-control" rows="2" placeholder="Например: Программное обеспечение ВТ и АС, специалитет"><?= htmlspecialchars($member['education'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>О себе (Bio)</label>
                                <textarea name="bio_<?= $member['id'] ?>" class="form-control" rows="3" placeholder="Краткая биография, достижения, опыт работы..."><?= htmlspecialchars($member['bio'] ?? '') ?></textarea>
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
                                <a href="team.php" class="btn btn-secondary ml-2">
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
    // Обработка загрузки фото для каждого сотрудника
    const photoInputs = document.querySelectorAll('.photo-input');
    
    photoInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const memberId = this.dataset.memberId;
            const file = this.files[0];
            
            if (!file) return;
            
            const formData = new FormData();
            formData.append('ajax_upload_team', '1');
            formData.append('member_id', memberId);
            formData.append('photo', file);
            
            const progressDiv = document.getElementById('progress_' + memberId);
            progressDiv.classList.add('active');
            input.disabled = true;
            
            fetch(window.location.href, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    progressDiv.classList.remove('active');
                    input.disabled = false;
                    input.value = '';
                    
                    if (data.success) {
                        // Обновляем скрытое поле
                        document.getElementById('photo_url_' + memberId).value = data.url;
                        // Обновляем превью
                        const preview = document.getElementById('preview_' + memberId);
                        preview.src = '../lab/' + data.url;
                        // Снимаем галочку удаления, если была
                        const deleteCheckbox = document.getElementById('delete_photo_' + memberId);
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
    
    // Предпросмотр удаления фото
    const deleteCheckboxes = document.querySelectorAll('[id^="delete_photo_"]');
    deleteCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const memberId = this.id.replace('delete_photo_', '');
            const hiddenInput = document.getElementById('photo_url_' + memberId);
            const preview = document.getElementById('preview_' + memberId);
            
            if (this.checked) {
                hiddenInput.value = '';
                preview.src = '../lab/img/placeholder-user.png';
            } else {
                const currentPhoto = hiddenInput.value;
                if (currentPhoto) {
                    preview.src = '../lab/' + currentPhoto;
                }
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>