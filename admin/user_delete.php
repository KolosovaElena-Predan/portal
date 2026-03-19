<?php
require_once '../config.php';

if (!isLoggedIn()) redirect('../authorization.php');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Нельзя удалить самого себя
    if ($id == $_SESSION['user_id']) {
        redirect('users.php?error=cannot_delete_self');
    }

    $stmt = $pdo->prepare("DELETE FROM user WHERE id = ?");
    $stmt->execute([$id]);
}

redirect('users.php');
?>