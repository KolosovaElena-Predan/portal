<?php
/**config.php — Подключение к базе данных lab_db*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'lab_db';
$username = 'siteuser';
$password = '12345';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

	
//Экранирует HTML-спецсимволы
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

//Перенаправление
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

//проверка авторизации
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
?>