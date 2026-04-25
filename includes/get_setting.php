<?php
// includes/get_setting.php
// Получение значения настройки по ключу

function getSetting($pdo, $key, $default = null) {
    static $cache = [];
    
    // Кэшируем все настройки при первом обращении
    if (empty($cache)) {
        try {
            $stmt = $pdo->query("SELECT `key`, value FROM settings");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cache[$row['key']] = $row['value'];
            }
        } catch (PDOException $e) {
            return $default;
        }
    }
    
    return $cache[$key] ?? $default;
}

// Проверка, включён ли чекбокс-настройка
function settingIsEnabled($pdo, $key) {
    return getSetting($pdo, $key, '0') === '1';
}
?>