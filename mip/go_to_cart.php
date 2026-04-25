<?php
session_start();
if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'client') {
    header('Location: cart.php');
    exit;
} else {
    header('Location: /portal/authorization.php?redirect=cart');
    exit;
}
?>