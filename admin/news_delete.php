<?php
require_once '../config.php';

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'admin') {
    redirect('../authorization.php');
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM news_images WHERE news_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);
        $pdo->commit();
        redirect('news.php?success=deleted');
    } catch (PDOException $e) {
        $pdo->rollBack();
        redirect('news.php?error=delete_failed');
    }
}

redirect('news.php');
?>