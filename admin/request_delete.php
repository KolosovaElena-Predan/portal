<?php
require_once '../config.php';
if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'admin') redirect('../authorization.php');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Удаляем связанные записи
        $pdo->prepare("DELETE FROM request_messages WHERE request_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM request_status_history WHERE request_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM request WHERE id = ?")->execute([$id]);
        
        $_SESSION['admin_success'] = 'Заявка удалена';
    } catch (PDOException $e) {
        $_SESSION['admin_error'] = 'Ошибка удаления: ' . $e->getMessage();
    }
}

redirect('requests.php');
?>