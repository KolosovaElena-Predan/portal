<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

$action = $_POST['action'] ?? '';
if ($action !== 'update_profile') {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

$userId = $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (!$name || !$email) {
    echo json_encode(['success' => false, 'error' => 'Имя и email обязательны']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Некорректный email']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Обновляем имя и email
    $stmt = $pdo->prepare("UPDATE user SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $email, $userId]);
    
    // Обновляем пароль, если указан
    if (!empty($newPassword)) {
        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'error' => 'Пароль должен быть не менее 6 символов']);
            exit;
        }
        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'error' => 'Пароли не совпадают']);
            exit;
        }
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $userId]);
    }
    
    $pdo->commit();
    
    // Обновляем сессию
    $_SESSION['name'] = $name;
    $_SESSION['user_name'] = $name;
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}