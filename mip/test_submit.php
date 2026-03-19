<?php
// test_submit.php — отладка формы вопроса
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Отладка формы вопроса</h2>";

echo "<h3>1. POST данные:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

echo "<h3>2. SESSION данные:</h3>";
echo "<pre>" . print_r($_SESSION ?? [], true) . "</pre>";

echo "<h3>3. Проверка подключения к БД:</h3>";
try {
    require_once 'config.php';
    echo "✅ Подключение успешно<br>";
    echo "PDO статус: " . ($pdo ? 'OK' : 'FAIL') . "<br>";
} catch (Exception $e) {
    echo "❌ Ошибка подключения: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Проверка таблицы request:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE request");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='5'><tr><th>Поле</th><th>Тип</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<a href='question.php'>← Вернуться к форме</a>";
?>