<?php
/**
 * config.php — Подключение к базе данных lab_db
 * Версия: 1.1 (исправленная)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// НАСТРОЙКИ БАЗЫ ДАННЫХ (как в рабочем конфиге)
// ============================================
$host = 'localhost';
$dbname = 'lab_db';
$username = 'siteuser';
$password = '12345';

// ============================================
// ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ
// ============================================
try {
    // ✅ Используем charset=utf8 (как в рабочем конфиге)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

// ============================================
// НАСТРОЙКИ СЕССИИ
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// ============================================
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
?>