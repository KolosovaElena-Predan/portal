<?php
require_once '../config.php';
if (!isLoggedIn() || !in_array($_SESSION['role'] ?? '', ['admin', 'editor'])) redirect('../authorization.php');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        // Удаляем изображение с диска
        $stmt = $pdo->prepare("SELECT img_url FROM lab_projects WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if ($p && $p['img_url']) {
            $path = __DIR__ . '/../mip/' . $p['img_url'];
            if (file_exists($path)) @unlink($path);
        }
        $pdo->prepare("DELETE FROM lab_projects WHERE id = ?")->execute([$id]);
    } catch (PDOException $e) {
        error_log("Project delete error: " . $e->getMessage());
    }
}
redirect('lab_projects.php');
?>