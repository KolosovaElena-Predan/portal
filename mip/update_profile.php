<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$action = $_POST['action'] ?? '';
if ($action !== 'update_profile') {
    echo json_encode(['success' => false, 'error' => 'Неверное действие']);
    exit;
}

$userId = $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$addressJson = $_POST['address_json'] ?? '{}';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Валидация обязательных полей
if (!$name || !$email) {
    echo json_encode(['success' => false, 'error' => 'Имя и email обязательны']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Некорректный email']);
    exit;
}

// Валидация телефона (если указан)
if (!empty($phone)) {
    // Убираем всё кроме цифр и знака +
    $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
    if (strlen($cleanPhone) < 10) {
        echo json_encode(['success' => false, 'error' => 'Некорректный номер телефона']);
        exit;
    }
}

// Парсим и валидируем адрес
$addressData = json_decode($addressJson, true);
if (!is_array($addressData)) {
    $addressData = ['city' => '', 'street' => '', 'house' => ''];
}

// Убираем лишние пробелы в частях адреса
$addressData = [
    'city' => trim($addressData['city'] ?? ''),
    'street' => trim($addressData['street'] ?? ''),
    'house' => trim($addressData['house'] ?? '')
];

// Если все поля адреса пустые — сохраняем пустую строку
$addressToSave = '';
if (!empty($addressData['city']) || !empty($addressData['street']) || !empty($addressData['house'])) {
    $addressToSave = json_encode($addressData, JSON_UNESCAPED_UNICODE);
}

try {
    $pdo->beginTransaction();
    
    // Обновляем основные данные + телефон + адрес
    $stmt = $pdo->prepare("UPDATE user SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->execute([$name, $email, $phone, $addressToSave, $userId]);
    
    // Обновляем пароль, если указан
    if (!empty($newPassword)) {
        if (strlen($newPassword) < 6) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Пароль должен быть не менее 6 символов']);
            exit;
        }
        if ($newPassword !== $confirmPassword) {
            $pdo->rollBack();
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
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Логируем реальную ошибку, но клиенту отдаём безопасное сообщение
    error_log("Update profile error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных. Попробуйте позже.']);
}