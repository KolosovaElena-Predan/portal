<?php
session_start();
require_once 'config.php';

// Разрешаем только POST запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Метод не поддерживается');
}

// 1. Получаем и очищаем данные
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$question = trim($_POST['question'] ?? '');
$consent = isset($_POST['consent']);

// 2. Валидация
$errors = [];

if (empty($full_name)) {
    $errors[] = 'Введите ваше имя';
}

if (empty($email)) {
    $errors[] = 'Введите email';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Введите корректный email';
}

if (empty($question)) {
    $errors[] = 'Введите вопрос';
} elseif (mb_strlen($question, 'UTF-8') < 10) {
    $errors[] = 'Вопрос должен содержать минимум 10 символов';
}

if (!$consent) {
    $errors[] = 'Необходимо согласие на обработку данных';
}

// Если есть ошибки — возвращаем назад
if (!empty($errors)) {
    $_SESSION['error'] = implode(', ', $errors);
    header('Location: question.php');
    exit;
}

try {
    $pdo->beginTransaction();

    $user_id = null;

    // 3. ЛОГИКА РАБОТЫ С ПОЛЬЗОВАТЕЛЕМ
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        // АВТОРИЗОВАННЫЙ ПОЛЬЗОВАТЕЛЬ
        $user_id = (int)$_SESSION['user_id'];
        
        // Обновляем имя и email в профиле
        $stmt = $pdo->prepare("UPDATE `user` SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$full_name, $email, $user_id]);
        
    } else {
        // ГОСТЬ (Неавторизованный)
        
        // Проверяем, нет ли уже пользователя с таким email
        $stmt = $pdo->prepare("SELECT id FROM `user` WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            // Пользователь уже есть
            $user_id = (int)$existingUser['id'];
            
            // Обновляем имя, если оно изменилось
            $stmt = $pdo->prepare("UPDATE `user` SET name = ? WHERE id = ?");
            $stmt->execute([$full_name, $user_id]);
        } else {
            // Создаём НОВОГО пользователя-гостя
            // password = NULL, role = 'guest'
            $stmt = $pdo->prepare("
                INSERT INTO `user` (email, name, password, role) 
                VALUES (?, ?, NULL, 'guest')
            ");
            $stmt->execute([$email, $full_name]);
            
            $user_id = (int)$pdo->lastInsertId();
        }
        
        
    }

    // 4. СОХРАНЕНИЕ ВОПРОСА В ТАБЛИЦУ request
    $stmt = $pdo->prepare("
        INSERT INTO `request` (
            user_id, 
            device_type_id, 
            product_id, 
            device_id, 
            message, 
            status, 
            datetime, 
            type, 
            user_clientsupport_id
        ) VALUES (?, NULL, NULL, NULL, ?, 'new', NOW(), 'q', NULL)
    ");
    
    $stmt->execute([
        $user_id,
        $question
    ]);

    $pdo->commit();

    // Успех
    $_SESSION['success'] = '✅ Ваш вопрос успешно отправлен! Мы ответим вам в ближайшее время.';
    header('Location: question.php');
    exit;

} catch (Exception $e) {
    // Откат транзакции при ошибке
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Логирование ошибки
    error_log("Ошибка отправки вопроса: " . $e->getMessage());
    
    // Сообщение пользователю
    $_SESSION['error'] = 'Произошла техническая ошибка. Попробуйте позже.';
    header('Location: question.php');
    exit;
}
?>