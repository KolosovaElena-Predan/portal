<?php
$activePage = 'news';
$pageTitle = 'Добавление новости';
$summernote = true;

require_once 'includes/auth_check.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');

    if (!$title) {
        $error = 'Заголовок обязателен';
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO news (title, content, datetime) VALUES (?, ?, NOW())");
            $stmt->execute([$title, $content]);
            $newsId = $pdo->lastInsertId();
            if ($image_url) {
                $stmt = $pdo->prepare("INSERT INTO news_images (news_id, image_url, is_main, sort_order) VALUES (?, ?, 1, 0)");
                $stmt->execute([$newsId, $image_url]);
            }
            $pdo->commit();
            $success = 'Новость добавлена!';
            $_POST = [];
        } catch (PDOException $e) {
            $pdo->rollBack();
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
            <h1 class="m-0">Добавление новости</h1>
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
                        <input type="text" name="title" class="form-control" value="<?= e($_POST['title'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">URL изображения</label>
                        <input type="text" name="image_url" class="form-control" value="<?= e($_POST['image_url'] ?? '') ?>" placeholder="img/news/news1.png">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Содержание</label>
                        <textarea name="content" class="form-control summernote" rows="15"><?= e($_POST['content'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="save" class="btn btn-primary">Добавить</button>
                    <a href="news.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>