<?php
session_start();
require_once 'config.php';

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

function jsonResponse($success, $error = null, $data = []) {
    $response = ['success' => $success];
    if ($error) $response['error'] = $error;
    echo json_encode(array_merge($response, $data), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(false, 'Не авторизован');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Неверный метод');
}

$requestId = (int)($_POST['request_id'] ?? 0);
$messageId = (int)($_POST['message_id'] ?? 0);

if (!$requestId) {
    jsonResponse(false, 'ID заказа не указан');
}

try {
    $stmt = $pdo->prepare("SELECT id FROM request WHERE id = ? AND user_id = ?");
    $stmt->execute([$requestId, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        jsonResponse(false, 'Заказ не найден');
    }
} catch (PDOException $e) {
    jsonResponse(false, 'Ошибка базы данных');
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = 'Файл не загружен';
    if (isset($_FILES['file']['error'])) {
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = 'Файл слишком большой';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMsg = 'Файл загружен частично';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = 'Файл не выбран';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMsg = 'Временная папка отсутствует';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errorMsg = 'Ошибка записи файла';
                break;
        }
    }
    jsonResponse(false, $errorMsg);
}

$file = $_FILES['file'];
$maxSize = 10 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    jsonResponse(false, 'Файл слишком большой (макс. 10 MB)');
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
if (!in_array($file['type'], $allowedTypes)) {
    jsonResponse(false, 'Недопустимый тип файла');
}

// Определяем правильные пути
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/chat/';
$webPath = '/uploads/chat/';

if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        jsonResponse(false, 'Не удалось создать папку для загрузок');
    }
}

$originalName = basename($file['name']);
$safeOriginalName = preg_replace('/[^a-zA-Zа-яА-Я0-9._-]/u', '_', $originalName);
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$safeName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
$filePath = $uploadDir . $safeName;
$dbPath = $webPath . $safeName;

if (move_uploaded_file($file['tmp_name'], $filePath)) {
    try {
        if (!$messageId) {
            $stmt = $pdo->prepare("INSERT INTO request_messages (request_id, sender_type, message, created_at) VALUES (?, 'user', '', NOW())");
            $stmt->execute([$requestId]);
            $messageId = $pdo->lastInsertId();
        }
        
        $stmt = $pdo->prepare("INSERT INTO chat_files (message_id, file_name, file_url, file_size, uploaded_by) VALUES (?, ?, ?, ?, 'user')");
        $stmt->execute([$messageId, $safeOriginalName, $dbPath, $file['size']]);
        
        jsonResponse(true, null, ['message_id' => $messageId]);
    } catch (PDOException $e) {
        if (file_exists($filePath)) unlink($filePath);
        jsonResponse(false, 'Ошибка сохранения в базу данных');
    }
} else {
    jsonResponse(false, 'Не удалось сохранить файл');
}
?>