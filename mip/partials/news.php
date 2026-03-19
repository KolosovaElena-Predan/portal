<?php
require_once __DIR__ . '/../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    // Удаление
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['error' => 'Неверный ID']);
        exit;
    }

    // Добавление
    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $image_url = '';

        if (!empty($_FILES['image']['tmp_name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']);
            if (in_array($fileType, $allowedTypes)) {
                $uploadDir = __DIR__ . '/../../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'news_' . uniqid() . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image_url = '/uploads/' . $filename;
                }
            }
        }

        if ($title && $content) {
            $pdo->prepare("INSERT INTO news (title, content, image_url, datetime) VALUES (?, ?, ?, NOW())")
                ->execute([$title, $content, $image_url]);
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['error' => 'Заполните обязательные поля']);
        exit;
    }

    // Сохранение изменений
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if ($id <= 0 || !$title || !$content) {
            echo json_encode(['error' => 'Заголовок и текст обязательны']);
            exit;
        }

        $image_url = '';
        if (!empty($_FILES['image']['tmp_name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']);
            if (in_array($fileType, $allowedTypes)) {
                $uploadDir = __DIR__ . '/../../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'news_' . uniqid() . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image_url = '/uploads/' . $filename;
                } else {
                    echo json_encode(['error' => 'Ошибка загрузки изображения']);
                    exit;
                }
            } else {
                echo json_encode(['error' => 'Разрешены только изображения']);
                exit;
            }
        }

        // Если файл не загружен — берём текущий URL
        if ($image_url === '') {
            $current = $pdo->prepare("SELECT image_url FROM news WHERE id = ?");
            $current->execute([$id]);
            $image_url = $current->fetchColumn();
            if ($image_url === false) {
                echo json_encode(['error' => 'Новость не найдена']);
                exit;
            }
        }

        $pdo->prepare("UPDATE news SET title = ?, content = ?, image_url = ? WHERE id = ?")
            ->execute([$title, $content, $image_url, $id]);
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['error' => 'Неизвестное действие']);
    exit;
}

$stmt = $pdo->query("SELECT id, title, content, image_url, datetime FROM news ORDER BY datetime DESC");
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="admin-title">Новости</h2>

<!-- Список новостей -->
<h3 class="form-title">Список новостей</h3>
<table id="newsTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Изображение</th>
            <th>Заголовок</th>
            <th>Текст</th>
            <th>Дата</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($news as $n): ?>
        <tr data-id="<?= (int)$n['id'] ?>">
            <td><?= htmlspecialchars($n['id']) ?></td>
            <td class="image-cell" data-id="<?= (int)$n['id'] ?>">
                <?php if (!empty($n['image_url'])): ?>
                    <img src="<?= htmlspecialchars($n['image_url']) ?>" alt="Изображение" class="admin-img">
                <?php else: ?>
                    <span class="no-image-placeholder">–</span>
                <?php endif; ?>
                <input type="file" name="image" accept="image/*" class="image-input" style="display:none;">
            </td>
            <td contenteditable="true" data-field="title"><?= htmlspecialchars($n['title']) ?></td>
            <td contenteditable="true" data-field="content"><?= htmlspecialchars($n['content']) ?></td>
            <td><?= htmlspecialchars($n['datetime']) ?></td>
            <td>
                <button class="btn-secondary delete-news" data-id="<?= (int)$n['id'] ?>">Удалить</button>
                <button class="btn-primary save-news" data-id="<?= (int)$n['id'] ?>">Сохранить</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Форма добавления -->
<h3 class="form-title">Добавить новость</h3>
<form id="addNewsForm" enctype="multipart/form-data" class="form-section">
    <input type="text" name="title" class="input-field" placeholder="Заголовок *" required><br><br>
    <textarea name="content" class="input-field" placeholder="Текст новости *" required></textarea><br><br>
    <label class="file-label">
        Изображение:
        <input type="file" name="image" accept="image/*">
    </label><br><br>
    <button type="submit" class="btn-primary">Добавить новость</button>
</form>