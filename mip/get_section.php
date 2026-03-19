<?php
require_once 'config.php';

$section = $_GET['section'] ?? 'dashboard';
$allowed = ['dashboard', 'equipment', 'news', 'users', 'requests'];

if (!in_array($section, $allowed)) {
    die('Недопустимый раздел');
}

$file = __DIR__ . "/partials/{$section}.php";
if (file_exists($file)) {
    include $file;
} else {
    echo "<p>Раздел «" . htmlspecialchars($section) . "» временно недоступен.</p>";
}