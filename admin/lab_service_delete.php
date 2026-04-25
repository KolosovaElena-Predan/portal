<?php
require_once '../config.php';
if (!isLoggedIn() || !in_array($_SESSION['role'] ?? '', ['admin', 'editor'])) redirect('../authorization.php');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        // Удаляем изображение
        $stmt = $pdo->prepare("SELECT img_url FROM lab_services WHERE id = ?");
        $stmt->execute([$id]);
        $s = $stmt->fetch();
        if ($s && $s['img_url']) {
            $path = __DIR__ . '/../mip/' . $s['img_url'];
            if (file_exists($path)) @unlink($path);
        }
        $pdo->prepare("DELETE FROM lab_services WHERE id = ?")->execute([$id]);
    } catch (PDOException $e) {
        error_log("Lab service delete error: " . $e->getMessage());
    }
}
redirect('lab_services.php');
?>