<?php
// Подключаем классы
require_once '../Database.php';
require_once '../User.php';
require_once '../UserRepository.php';
require_once '../Auth.php';

$database = new Database();
$userRepo = new UserRepository($database);
$auth = new Auth($userRepo);
$user = $auth->getCurrentUser();

if (!($user instanceof ClientUser)) {
    header('Location: authorization.php');
    exit;
}

require_once 'config.php';

// Загружаем заказы (type='r') И услуги (type='s')
// Загружаем заказы (type='r') И услуги (type='s')
$stmt = $pdo->prepare("
    SELECT 
        r.id, 
        r.datetime, 
        r.status, 
        r.type, 
        r.message,
        r.device_type_id,  -- 🔧 ДОБАВИТЬ: явно выбираем device_type_id
        r.product_id,      -- 🔧 ДОБАВИТЬ: явно выбираем product_id
        -- Оборудование из products
        p.name AS product_name,
        p.base_price AS product_price,
        (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS product_img,
        -- Услуги из services
        s.name AS service_name,
        s.price AS service_price,
        s.img_url AS service_img
    FROM request r
    LEFT JOIN products p ON r.product_id = p.id AND r.type = 'r'
    LEFT JOIN services s ON (r.type = 's' AND JSON_EXTRACT(r.message, '$.service_id') = s.id)
    WHERE r.user_id = ? AND r.type IN ('r', 's')
    ORDER BY r.datetime DESC
");
$stmt->execute([$user->id]);
$raw_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Группировка ПО ТИПУ (оборудование / услуги)
$items = [
    'equipment' => [],  // type = 'r'
    'services' => []    // type = 's'
];

foreach ($raw_requests as $req) {
    // Декодируем message для услуг
    if ($req['type'] === 's' && !empty($req['message'])) {
        $req['service_details'] = json_decode($req['message'], true);
    }
    
    $key = ($req['type'] === 's') ? 'services' : 'equipment';
    $items[$key][] = $req;
}

// Статусы для отображения в карточке
$statusLabels = [
    'new' => 'Оформление',
    'processed' => 'В пути',
    'closed' => 'Доставлено'
];

$statusColors = [
    'new' => '#ffc107',      // жёлтый
    'processed' => '#17a2b8', // голубой
    'closed' => '#28a745'    // зелёный
];

$user_data = ['name' => $user->name, 'email' => $user->email];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="css/style_header_footer.css" />
    <link rel="stylesheet" href="css/style_mip.css" />
    <link rel="stylesheet" href="css/style_main.css" />
    <link rel="stylesheet" href="css/header_mip.css" />
    <title>Личный кабинет</title>
    <!-- Ваши стили без изменений + минимальные дополнения -->
    <style>
        .lk-content {
            width: 100%;
            max-width: 1600px;
            margin: 200px auto 80px;
            padding: 0 20px;
            box-sizing: border-box;
        }
        .lk-main-content {
            display: flex;
            gap: 40px;
            align-items: flex-start;
        }
        .device-list-wrapper {
            flex: 2;
            min-width: 0;
        }
        .personal-data {
            flex: 0 0 320px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: sticky;
            top: 20px;
        }
        .personal-data h3 {
            font-family: "Inter-Bold", sans-serif;
            font-size: 24px;
            color: #000;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }
        .personal-data p {
            font-family: "Inter-Medium", sans-serif;
            font-size: 18px;
            color: #000;
            margin: 8px 0;
        }
        .cart-link {
            display: block;
            margin-top: 20px;
            padding: 12px 0;
            background-color: #1a1982;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 18px;
            font-family: "Inter-Medium", sans-serif;
            text-align: center;
            transition: background 0.3s;
        }
        .cart-link:hover {
            background-color: #14136b;
        }
        .type-section {
            margin-bottom: 50px;
        }
        .type-title {
            font-family: "Inter-Bold", sans-serif;
            font-size: 28px;
            color: #000;
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }
        .request-card {
            display: flex;
            gap: 20px;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
        .order-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            background: #f0f0f0;
        }
        .order-info {
            flex: 1;
        }
        .request-device {
            font-family: "Inter-Bold", sans-serif;
            font-size: 22px;
            color: #1a1982;
            margin: 0 0 8px 0;
        }
        /* Статус-бейдж внутри карточки */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            margin-bottom: 10px;
        }
        .request-time {
            font-size: 14px;
            color: #777;
            margin-bottom: 10px;
        }
        .request-message {
            font-size: 16px;
            line-height: 1.5;
            color: #333;
            margin: 10px 0;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 6px;
            display: inline-block;
        }
        .request-price {
            font-family: "Inter-Medium", sans-serif;
            font-size: 18px;
            color: #1a1982;
            font-weight: 600;
            margin: 5px 0;
        }
        .no-orders {
            font-family: "Inter-Light", sans-serif;
            font-size: 18px;
            color: #777;
            text-align: center;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .lk-main-content { flex-direction: column; }
            .device-list-wrapper { width: 100%; }
            .personal-data { flex: none; width: 100%; position: static; }
        }
    </style>
</head>
<body>
<div class="screen">
    <div class="div">
        <?php require_once 'header_mip.php'; ?>

        <div class="lk-content">
            <div class="lk-main-content">
                <div class="device-list-wrapper">
                    <h2 class="lk-title">Мои заказы</h2>

                    <!-- === ОБОРУДОВАНИЕ === -->
                    <div class="type-section">
                        <h3 class="type-title">Устройства</h3>
                        <?php if (empty($items['equipment'])): ?>
                            <div class="no-orders">Нет заказов оборудования</div>
                        <?php else: ?>
                            <?php foreach ($items['equipment'] as $item): ?>
                                <?= renderRequestCard($item, $statusLabels, $statusColors) ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- === УСЛУГИ === -->
                    <div class="type-section">
                        <h3 class="type-title">Услуги</h3>
                        <?php if (empty($items['services'])): ?>
                            <div class="no-orders">Нет заказанных услуг</div>
                        <?php else: ?>
                            <?php foreach ($items['services'] as $item): ?>
                                <?= renderRequestCard($item, $statusLabels, $statusColors) ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>

                <div class="personal-data">
                    <h3>Личные данные</h3>
                    <p><strong>ФИО:</strong> <?= htmlspecialchars($user_data['name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user_data['email']) ?></p>
                    <a href="cart.php" class="cart-link">Перейти в корзину</a>
                </div>
            </div>
        </div>
    </div>
    <?php require_once 'footer_mip.php'; ?>
</div>

<?php
function renderRequestCard($req, $statusLabels, $statusColors) {
    $isService = ($req['type'] === 's');
    
    // === ФОРМИРОВАНИЕ ССЫЛКИ ===
    if ($isService) {
        // Услуги: каталог услуг
        $linkUrl = 'services_catalog.php';
        
        // Данные для отображения
        $name = htmlspecialchars($req['service_name'] ?? 'Услуга');
        $price = $req['service_price'] ?? 0;
        $img = htmlspecialchars($req['service_img'] ?: 'img/placeholder.jpg');
        $message = '';
    } else {
        // Оборудование: логика выбора product_id
        $productId = null;
        
        // 🔧 ПРОВЕРЯЕМ ОБА ПОЛЯ
        if (!empty($req['product_id'])) {
            // Приоритет 1: product_id заполнен
            $productId = (int)$req['product_id'];
        } elseif (!empty($req['device_type_id'])) {
            // Приоритет 2: device_type_id заполнен (старые заказы)
            $productId = (int)$req['device_type_id'];
        }
        
        // Отладка (можно удалить после проверки)
        error_log("product_id: " . ($req['product_id'] ?? 'NULL') . 
                  ", device_type_id: " . ($req['device_type_id'] ?? 'NULL') . 
                  ", итоговый productId: " . ($productId ?? 'NULL'));
        
        // Формируем ссылку
        $linkUrl = $productId ? 'product.php?id=' . $productId : 'catalog.php';
        
        // Данные для отображения
        $name = htmlspecialchars($req['product_name'] ?? 'Товар');
        $msgData = json_decode($req['message'], true);
        $message = htmlspecialchars($msgData['address'] ?? 'Адрес не указан');
        $price = $msgData['price'] ?? $req['product_price'] ?? 0;
        $img = htmlspecialchars($req['product_img'] ?: 'img/placeholder.jpg');
    }
    
    $priceFormatted = number_format($price, 2, ',', ' ') . ' ₽';
    
    // Статус-бейдж
    $status = $req['status'] ?? 'new';
    $statusText = $statusLabels[$status] ?? $status;
    $statusColor = $statusColors[$status] ?? '#6c757d';
    
    $statusBadge = sprintf(
        '<span class="status-badge" style="background:%s">%s</span>',
        $statusColor,
        $statusText
    );

    // === КЛИКАБЕЛЬНАЯ КАРТОЧКА ===
    return <<<HTML
    <a href="{$linkUrl}" style="text-decoration: none; color: inherit; display: block;">
        <div class="request-card" style="transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;">
            <img src="{$img}" alt="{$name}" class="order-image"
                 onerror="this.src='img/placeholder.jpg'; this.onerror=null;"
                 style="pointer-events: none;">
            <div class="order-info" style="pointer-events: none;">
                {$statusBadge}
                <div class="request-device">{$name}</div>
                <div class="request-price">{$priceFormatted}</div>
                <p class="request-message">{$message}</p>
            </div>
        </div>
    </a>
HTML;
}
?>
</body>
</html>