<?php
$activePage = 'pages';
$pageTitle = 'Редактирование страницы | Админ-панель';
$summernote = true;
require_once 'includes/auth_check.php';

$pageId = (int)($_GET['id'] ?? 0);
if ($pageId <= 0) { header('Location: pages.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
$stmt->execute([$pageId]);
$page = $stmt->fetch();
if (!$page) { header('Location: pages.php'); exit; }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $content = $_POST['content'] ?? '';
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $status = $_POST['status'] ?? 'draft';

    if (!$title) {
        $error = 'Название обязательно';
    } elseif (!$slug || !preg_match('/^[a-z0-9\-_]+$/', $slug)) {
        $error = 'Некорректный формат Slug';
    } else {
        try {
            $pdo->prepare("UPDATE pages SET title = ?, slug = ?, content = ?, meta_title = ?, meta_description = ?, status = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$title, $slug, $content, $meta_title, $meta_description, $status, $pageId]);
            $success = 'Страница обновлена';
            // Перезагружаем данные
            $stmt->execute([$pageId]);
            $page = $stmt->fetch();
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
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Редактирование: <?= htmlspecialchars($page['title']) ?></h1></div></div>
    <section class="content"><div class="container-fluid">
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <form method="POST" class="card" id="pageForm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-section mb-3">
                            <label>Название страницы *</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($page['title']) ?>" required id="pageTitle">
                        </div>
                        <div class="form-section mb-3">
                            <label>Содержимое (HTML)</label>
                            <textarea name="content" class="form-control summernote" rows="10"><?= htmlspecialchars($page['content']) ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-section mb-3">
                            <label>Slug (URL) *</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">/</span></div>
                                <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($page['slug']) ?>" required id="pageSlug">
                            </div>
                        </div>
                        <div class="form-section mb-3">
                            <label>Статус</label>
                            <select name="status" class="form-control">
                                <option value="draft" <?= $page['status'] === 'draft' ? 'selected' : '' ?>>Черновик</option>
                                <option value="active" <?= $page['status'] === 'active' ? 'selected' : '' ?>>Активна</option>
                                <option value="inactive" <?= $page['status'] === 'inactive' ? 'selected' : '' ?>>Неактивна</option>
                            </select>
                        </div>
                        <hr>
                        <div class="form-section mb-3"><label>Meta Title</label><input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($page['meta_title']) ?>" maxlength="70"></div>
                        <div class="form-section mb-3"><label>Meta Description</label><textarea name="meta_description" class="form-control" rows="3" maxlength="160"><?= htmlspecialchars($page['meta_description']) ?></textarea></div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить изменения</button>
                <a href="pages.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Отмена</a>
            </div>
        </form>
    </div></section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('pageTitle');
    const slugInput = document.getElementById('pageSlug');
    if(titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            let slug = this.value.toLowerCase().replace(/[^a-zа-яё0-9\s]/gi, '').replace(/\s+/g, '-').replace(/ё/g, 'e');
            const map = {'а':'a','б':'b','в':'v','г':'g','д':'d','е':'e','ж':'zh','з':'z','и':'i','й':'y','к':'k','л':'l','м':'m','н':'n','о':'o','п':'p','р':'r','с':'s','т':'t','у':'u','ф':'f','х':'h','ц':'ts','ч':'ch','ш':'sh','щ':'sch','ъ':'','ы':'y','ь':'','э':'e','ю':'yu','я':'ya'};
            slug = slug.replace(/[а-яё]/g, c => map[c] || c);
            slugInput.value = slug;
        });
    }
});
</script>
<?php require_once 'includes/footer.php'; ?>