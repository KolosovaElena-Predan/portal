<?php
// includes/track_visit.php

function trackVisit($pdo) {
    if (!$pdo) return;
    
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    // Пропускаем админку и статику
    if (strpos($uri, '/admin') === 0 || preg_match('/\.(css|js|png|jpg|gif|svg|ico|webp)$/i', $uri)) {
        return;
    }
    
    // Простая проверка на ботов
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/bot|crawler|spider|curl|wget/i', $ua)) return;
    
    // Определяем устройство
    $device = 'desktop';
    if (preg_match('/Mobile|Android|Silk|Kindle|BlackBerry|Opera Mini/i', $ua)) {
        $device = 'mobile';
    } elseif (preg_match('/Tablet|iPad|Android(?!.*Mobile)/i', $ua)) {
        $device = 'tablet';
    }
    
    // Определяем браузер
    $browser = 'Unknown';
    if (preg_match('/Edg/i', $ua)) $browser = 'Edge';
    elseif (preg_match('/Chrome/i', $ua)) $browser = 'Chrome';
    elseif (preg_match('/Firefox/i', $ua)) $browser = 'Firefox';
    elseif (preg_match('/Safari/i', $ua)) $browser = 'Safari';
    elseif (preg_match('/Opera|OPR/i', $ua)) $browser = 'Opera';
    
    // IP и гео (упрощённо, без внешних API)
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? 
          $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ip = explode(',', $ip)[0];
    
    // Простая эвристика для страны/города (можно заменить на ipapi.co или ipinfo.io)
    $country = $city = null;
    if (preg_match('/^\d{1,3}\./', $ip)) {
        // Здесь можно добавить вызов API, например:
        // $geo = json_decode(file_get_contents("https://ipapi.co/$ip/json/"), true);
        // $country = $geo['country_name'] ?? null;
        // $city = $geo['city'] ?? null;
    }
    
    $pageUrl = substr($uri, 0, 500);
    $referer = substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500);
    $userId = $_SESSION['user_id'] ?? null;
    
    try {
        // Дедупликация: не писать тот же IP + страницу за 5 минут
        $stmt = $pdo->prepare("SELECT id FROM visits WHERE ip_address = ? AND page_url = ? AND created_at > NOW() - INTERVAL 5 MINUTE LIMIT 1");
        $stmt->execute([$ip, $pageUrl]);
        if ($stmt->fetch()) return;
        
        // Запись (используем INSERT IGNORE для избежания дублей)
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO visits 
            (ip_address, user_agent, country, city, device_type, browser, page_url, referer, user_id, is_bot, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([
            $ip,
            substr($ua, 0, 255),
            $country,
            $city,
            $device,
            $browser,
            $pageUrl,
            $referer,
            $userId
        ]);
    } catch (Exception $e) {
        // Тихо игнорируем ошибки, чтобы не ломать сайт
        error_log("Visit tracking error: " . $e->getMessage());
    }
}
?>