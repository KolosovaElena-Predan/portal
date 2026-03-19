<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("SELECT role FROM user WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            if ($user['role'] === 'client') {
                header('Location: lk_user.php');
            } else {
                header('Location: lk_support.php');
            }
        } else {
            header('Location: authorization.php');
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        header('Location: authorization.php');
    }
} else {
    header('Location: authorization.php');
}

exit;
?>