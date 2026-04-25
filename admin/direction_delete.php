<?php
require_once '../config.php';
if (!isLoggedIn() || !in_array($_SESSION['role'] ?? '', ['admin', 'editor'])) redirect('../authorization.php');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $pdo->prepare("DELETE FROM directions WHERE id = ?")->execute([$id]);
    } catch (PDOException $e) {
        error_log("Direction delete error: " . $e->getMessage());
    }
}
redirect('directions.php');
?>