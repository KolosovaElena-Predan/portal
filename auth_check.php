<?php
// auth_check.php — проверка авторизации для шапки
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Возвращаем данные пользователя или null
function getCurrentUserFromSession(): ?array {
    if (isset($_SESSION['user_id'], $_SESSION['name'], $_SESSION['role'])) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['name'],
            'role' => $_SESSION['role'],
            'login' => $_SESSION['login'] ?? ''
        ];
    }
    return null;
}

// URL для редиректа после входа
function getLoginRedirectUrl(): string {
    if (isset($_SESSION['role'])) {
        return match ($_SESSION['role']) {
            'admin' => 'lk_admin.php',
            'support_specialist' => 'lk_support.php',
            'client' => 'index.php',
            default => 'index.php'
        };
    }
    return 'index.php';
}
?>