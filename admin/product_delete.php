<?php
require_once '../config.php';

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'admin') {
    redirect('../authorization.php');
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Отключаем проверки внешних ключей на время удаления
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // 1. Удаляем связанные записи из всех таблиц
        $pdo->prepare("DELETE FROM product_schemes WHERE product_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM product_modifications WHERE product_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM product_files WHERE product_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM product_configurations WHERE product_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM request WHERE product_id = ?")->execute([$id]);
        
        // 2. Удаляем сам товар
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        
        // Включаем проверки обратно
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
    } catch (PDOException $e) {
        // Включаем проверки обратно в случае ошибки
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        redirect('products.php?error=delete_failed&msg=' . urlencode($e->getMessage()));
    }
}

redirect('products.php');
?>