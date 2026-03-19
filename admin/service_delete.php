<?php
require_once '../config.php';

// Проверка прав доступа (только админы)
if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'admin') {
    redirect('../authorization.php');
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Полное удаление записи из базы данных
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        
        redirect('services.php?success=deleted');
    } catch (PDOException $e) {
        // Если есть связанные записи (внешние ключи)
        redirect('services.php?error=delete_failed&msg=' . urlencode($e->getMessage()));
    }
}

redirect('services.php');
?>