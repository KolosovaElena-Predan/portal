<?php
require_once '../config.php';
if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'admin') redirect('../authorization.php');

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM request WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
}
redirect('requests.php');
?>