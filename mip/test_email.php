<?php
/**
 * test_email.php - Тестирование email-уведомлений
 * Разместите этот файл в папке portal/mip/
 * 
 * Доступ:
 * - Только для администратора
 * - URL: http://localhost/portal/mip/test_email.php
 */

session_start();
require_once 'includes/email_config.php';
require_once '../config.php';

// Проверка прав администратора
$isAdmin = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT role FROM user WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $isAdmin = ($user && $user['role'] === 'admin');
}

if (!$isAdmin) {
    die('❌ Доступ запрещён. Только для администратора.');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Тестирование email-уведомлений</title>
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 { color: #00a896; border-bottom: 2px solid #00a896; padding-bottom: 10px; }
        h2 { color: #00302e; margin-top: 30px; }
        .test-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #00a896;
        }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #17a2b8; }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 12px;
        }
        button {
            background: #00a896;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover { background: #008a7a; }
        input, select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-right: 10px;
        }
        .form-group {
            margin: 15px 0;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-error { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>

<h1>📧 Тестирование email-уведомлений</h1>

<div class="test-card">
    <h2>🔧 1. Проверка системных требований</h2>
    <?php
    echo "<p><strong>PHPMailer:</strong> ";
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo '<span class="success">✅ Установлен</span>';
    } else {
        echo '<span class="error">❌ НЕ УСТАНОВЛЕН! Запустите: composer require phpmailer/phpmailer</span>';
    }
    echo "</p>";
    
    echo "<p><strong>SMTP настройки:</strong><br>";
    echo "Host: smtp.mail.ru<br>";
    echo "Port: 465<br>";
    echo "Username: elena.kolosova.04@mail.ru<br>";
    echo "SSL: Да<br>";
    echo "</p>";
    
    // Проверка наличия записей в листе ожидания
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM request WHERE type = 'wl' AND is_notified = 0");
    $stmt->execute();
    $waitingCount = $stmt->fetchColumn();
    echo "<p><strong>Лист ожидания:</strong> ";
    if ($waitingCount > 0) {
        echo '<span class="success">✅ Найдено ' . $waitingCount . ' записей, ожидающих уведомления</span>';
    } else {
        echo '<span class="info">ℹ️ Нет записей в листе ожидания</span>';
    }
    echo "</p>";
    ?>
</div>

<div class="test-card">
    <h2>📨 2. Отправка тестового письма</h2>
    <form method="POST" style="margin-bottom: 15px;">
        <div class="form-group">
            <label>Email получателя:</label>
            <input type="email" name="test_email" value="elena.kolosova.04@mail.ru" required style="width: 300px;">
        </div>
        <button type="submit" name="send_test">Отправить тестовое письмо</button>
    </form>
    
    <?php
    if (isset($_POST['send_test'])) {
        $testEmail = $_POST['test_email'];
        $subject = "Тест SMTP от " . date('Y-m-d H:i:s');
        $message = '
            <!DOCTYPE html>
            <html>
            <head><meta charset="UTF-8"></head>
            <body style="font-family: Arial, sans-serif;">
                <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
                    <h2 style="color: #00a896;">✅ SMTP работает корректно!</h2>
                    <p>Это тестовое письмо с сайта <strong>ООО МИП «НПЦ ПИТиА»</strong>.</p>
                    <p>Время отправки: <strong>' . date('d.m.Y H:i:s') . '</strong></p>
                    <hr style="border: none; border-top: 1px solid #eee;">
                    <p style="font-size: 12px; color: #666;">Если вы получили это письмо, значит настройка SMTP завершена успешно.</p>
                </div>
            </body>
            </html>
        ';
        
        $result = sendEmailNotification($testEmail, 'Тестовый пользователь', $subject, $message);
        
        if ($result) {
            echo '<p class="success">✅ Письмо успешно отправлено на ' . htmlspecialchars($testEmail) . '!</p>';
            echo '<p class="info">📬 Проверьте почту (возможно, письмо попало в спам).</p>';
        } else {
            echo '<p class="error">❌ Ошибка отправки письма. Проверьте логи сервера.</p>';
        }
    }
    ?>
</div>

<div class="test-card">
    <h2>📦 3. Тестирование уведомлений о поступлении товара</h2>
    <form method="POST">
        <div class="form-group">
            <label>Выберите товар:</label>
            <select name="product_id" required>
                <option value="">-- Выберите товар --</option>
                <?php
                $stmt = $pdo->query("SELECT id, name FROM products WHERE status = 'active' ORDER BY name");
                while ($row = $stmt->fetch()) {
                    echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>Новый остаток на складе:</label>
            <input type="number" name="new_stock" value="5" min="1" required>
        </div>
        <button type="submit" name="test_stock_notification">Тестировать уведомление</button>
    </form>
    
    <?php
    if (isset($_POST['test_stock_notification'])) {
        $productId = (int)$_POST['product_id'];
        $newStock = (int)$_POST['new_stock'];
        
        if ($productId <= 0) {
            echo '<p class="error">❌ Выберите товар</p>';
        } else {
            // Получаем информацию о товаре
            $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if ($product) {
                // Получаем главное изображение
                $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_main = 1 LIMIT 1");
                $stmt->execute([$productId]);
                $productImage = $stmt->fetchColumn();
                
                // Вызываем функцию уведомления
                $notified = notifyWaitingUsersProductAvailable($pdo, $productId, $product['name'], $newStock, $productImage);
                
                if ($notified > 0) {
                    echo '<p class="success">✅ Отправлено уведомлений: ' . $notified . '</p>';
                } else {
                    echo '<p class="info">ℹ️ Нет пользователей в листе ожидания для этого товара.</p>';
                }
            } else {
                echo '<p class="error">❌ Товар не найден</p>';
            }
        }
    }
    ?>
</div>

<div class="test-card">
    <h2>📋 4. Просмотр шаблонов писем</h2>
    <button onclick="showTemplate('product')">Показать шаблон "Поступление товара"</button>
    <button onclick="showTemplate('status')">Показать шаблон "Статус заказа"</button>
    <button onclick="showTemplate('chat')">Показать шаблон "Новое сообщение"</button>
    
    <div id="templatePreview" style="margin-top: 15px; display: none;">
        <h3>Предпросмотр шаблона:</h3>
        <div id="templateContent" style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; background: white;"></div>
    </div>
</div>

<div class="test-card">
    <h2>📊 5. Статистика уведомлений</h2>
    <?php
    // Статистика по уведомлениям
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
            DATE(created_at) as date
        FROM notifications
        GROUP BY DATE(created_at)
        ORDER BY date DESC
        LIMIT 10
    ");
    $stmt->execute();
    $stats = $stmt->fetchAll();
    
    if (!empty($stats)) {
        echo '<table style="width:100%; border-collapse: collapse;">';
        echo '<tr style="background: #f0fbfb;"><th>Дата</th><th>Всего</th><th>Непрочитанных</th></tr>';
        foreach ($stats as $stat) {
            echo '<tr>';
            echo '<td>' . date('d.m.Y', strtotime($stat['date'])) . '</td>';
            echo '<td>' . $stat['total'] . '</td>';
            echo '<td>' . $stat['unread'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p class="info">ℹ️ Нет данных об уведомлениях</p>';
    }
    ?>
</div>

<div class="test-card">
    <h2>🔍 6. Проверка листа ожидания</h2>
    <?php
    $stmt = $pdo->prepare("
        SELECT r.id, r.user_id, r.product_id, p.name as product_name, r.message, r.is_notified, u.email, u.name
        FROM request r
        JOIN products p ON r.product_id = p.id
        JOIN user u ON r.user_id = u.id
        WHERE r.type = 'wl'
        ORDER BY r.datetime DESC
        LIMIT 10
    ");
    $stmt->execute();
    $waitingList = $stmt->fetchAll();
    
    if (!empty($waitingList)) {
        echo '<table style="width:100%; border-collapse: collapse;">';
        echo '<tr style="background: #f0fbfb;"><th>ID</th><th>Пользователь</th><th>Email</th><th>Товар</th><th>Уведомлён</th></tr>';
        foreach ($waitingList as $item) {
            $userData = json_decode($item['message'], true);
            $quantity = $userData['quantity'] ?? 1;
            echo '<tr>';
            echo '<td>' . $item['id'] . '</td>';
            echo '<td>' . htmlspecialchars($item['name']) . '</td>';
            echo '<td>' . htmlspecialchars($item['email']) . '</td>';
            echo '<td>' . htmlspecialchars($item['product_name']) . ' (' . $quantity . ' шт.)</td>';
            echo '<td>' . ($item['is_notified'] ? '<span class="badge badge-success">Да</span>' : '<span class="badge badge-warning">Нет</span>') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p class="info">ℹ️ Лист ожидания пуст</p>';
    }
    ?>
</div>

<script>
function showTemplate(type) {
    const previewDiv = document.getElementById('templatePreview');
    const contentDiv = document.getElementById('templateContent');
    const siteUrl = window.location.origin + '/portal/mip';
    
    if (type === 'product') {
        contentDiv.innerHTML = `<?php 
            $template = getProductAvailableEmailTemplate('Тестовый товар', 'https://example.com/product.php?id=1', 5, '');
            echo addslashes($template);
        ?>`;
    } else if (type === 'status') {
        contentDiv.innerHTML = `<?php 
            $template = getOrderStatusEmailTemplate('12345', 'В обработке', 'Ваш заказ передан в доставку');
            echo addslashes($template);
        ?>`;
    } else if (type === 'chat') {
        contentDiv.innerHTML = `<?php 
            $template = getNewMessageEmailTemplate('12345', 'Здравствуйте! Ваш заказ будет доставлен завтра.', 'Специалист поддержки');
            echo addslashes($template);
        ?>`;
    }
    
    previewDiv.style.display = 'block';
}
</script>

</body>
</html>