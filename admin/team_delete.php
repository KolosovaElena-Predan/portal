<?php
require_once '../config.php';
if (!isLoggedIn() || !in_array($_SESSION['role'] ?? '', ['admin', 'editor'])) redirect('../authorization.php');
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Удаляем фото
    $stmt = $pdo->prepare("SELECT photo_url FROM team WHERE id = ?");
    $stmt->execute([$id]);
    $m = $stmt->fetch();
    if ($m && $m['photo_url']) {
        $path = __DIR__ . '/../lab/' . $m['photo_url'];
        if (file_exists($path)) @unlink($path);
    }
    $pdo->prepare("DELETE FROM team WHERE id = ?")->execute([$id]);
}
redirect('team.php');
?>