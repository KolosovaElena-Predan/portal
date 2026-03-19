<?php
$activePage = 'news';
$pageTitle = 'Редактирование новости';
$summernote = true;

require_once 'includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $news = $stmt->fetch();
    if (!$news) redirect('news.php');
    $stmt = $pdo->prepare("SELECT * FROM news_images WHERE news_id = ? ORDER BY sort_order, is_main DESC");
    $stmt->execute([$id]);
    $images = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (!$title) {
        $error = 'Заголовок обязателен';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE news SET title=?, content=?, datetime=NOW() WHERE id=?");
            $stmt->execute([$title, $content, $id]);
            $success = 'Новость обновлена!';
            $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
            $stmt->execute([$id]);
            $news = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

$pageScript = '$(".summernote").summernote({ height: 400, lang: "ru-RU" });';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Редактирование новости #<?= $news['id'] ?></h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

            <form method="POST" class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Заголовок *</label>
                        <input type="text" name="title" class="form-control" value="<?= e($news['title']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Содержание</label>
                        <textarea name="content" class="form-control summernote" rows="15"><?= e($news['content']) ?></textarea>
                    </div>
                    <?php if (!empty($images)): ?>
                    <div class="form-group">
                        <label class="form-label">Изображения</label>
                        <div class="row">
                            <?php foreach ($images as $img): ?>
                            <div class="col-md-3 mb-3">
                                <img src="../<?= e($img['image_url']) ?>" class="img-fluid rounded" alt="">
                                <small class="d-block"><?= $img['is_main'] ? '📌 Главное' : '' ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <button type="submit" name="save" class="btn btn-primary">Сохранить</button>
                    <a href="news.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>