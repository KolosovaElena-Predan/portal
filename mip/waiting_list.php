<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: authorization.php');
    exit;
}

// Функция для получения остатка товара с учётом комплектации
function getProductStock($pdo, $productId, $configurationId = null) {
    if ($configurationId) {
        $stmt = $pdo->prepare("SELECT characteristics FROM product_configurations WHERE id = ? AND product_id = ?");
        $stmt->execute([$configurationId, $productId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($config && !empty($config['characteristics'])) {
            $chars = json_decode($config['characteristics'], true);
            if (isset($chars['stock'])) {
                return (int)$chars['stock'];
            }
        }
    }
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    return $product ? (int)$product['stock'] : 0;
}

// Получаем товары из листа ожидания (тип 'wl')
$stmt = $pdo->prepare("
    SELECT r.*, p.name as product_name, p.base_price,
    (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as product_img
    FROM request r
    JOIN products p ON r.product_id = p.id
    WHERE r.user_id = ? AND r.type = 'wl' AND r.status = 'waiting'
    ORDER BY r.datetime DESC
");
$stmt->execute([$_SESSION['user_id']]);
$waitingItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка удаления из листа ожидания
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $stmt = $pdo->prepare("DELETE FROM request WHERE id = ? AND user_id = ? AND type = 'wl'");
    $stmt->execute([$_GET['remove'], $_SESSION['user_id']]);
    header('Location: waiting_list.php');
    exit;
}

// Обработка удаления нескольких заявок
if (isset($_POST['action']) && $_POST['action'] === 'remove_multiple') {
    header('Content-Type: application/json');
    $ids = json_decode($_POST['ids'] ?? '[]', true);
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM request WHERE id IN ($placeholders) AND user_id = ? AND type = 'wl'");
        $params = array_merge($ids, [$_SESSION['user_id']]);
        $stmt->execute($params);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Обработка обновления количества
if (isset($_POST['action']) && $_POST['action'] === 'update_quantity') {
    header('Content-Type: application/json');
    $requestId = (int)$_POST['request_id'];
    $newQuantity = max(1, (int)$_POST['quantity']);
    
    $stmt = $pdo->prepare("SELECT message FROM request WHERE id = ? AND user_id = ? AND type = 'wl'");
    $stmt->execute([$requestId, $_SESSION['user_id']]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($request) {
        $messageData = json_decode($request['message'], true);
        $messageData['quantity'] = $newQuantity;
        $newMessage = json_encode($messageData, JSON_UNESCAPED_UNICODE);
        
        $stmt = $pdo->prepare("UPDATE request SET message = ?, requested_quantity = ? WHERE id = ?");
        $stmt->execute([$newMessage, $newQuantity, $requestId]);
        echo json_encode(['success' => true, 'new_quantity' => $newQuantity]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Запись не найдена']);
    }
    exit;
}

// Обработка добавления в корзину из листа ожидания
if (isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    header('Content-Type: application/json');
    $requestId = (int)$_POST['request_id'];
    
    $stmt = $pdo->prepare("SELECT * FROM request WHERE id = ? AND user_id = ? AND type = 'wl'");
    $stmt->execute([$requestId, $_SESSION['user_id']]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($request) {
        $messageData = json_decode($request['message'], true);
        $productId = $request['product_id'];
        $configurationId = $messageData['configuration']['id'] ?? null;
        $requestedQuantity = (int)$messageData['quantity'];
        
        $currentStock = getProductStock($pdo, $productId, $configurationId);
        
        if ($currentStock <= 0) {
            echo json_encode(['success' => false, 'error' => 'Товара нет в наличии']);
            exit;
        }
        
        $addToCartQuantity = min($requestedQuantity, $currentStock);
        $remainingQuantity = $requestedQuantity - $addToCartQuantity;
        
        // Формируем ключ для корзины
        $configKey = 'prod_' . $productId;
        if ($configurationId) {
            $configKey .= '_cfg_' . $configurationId;
        }
        if (!empty($messageData['modifications'])) {
            foreach ($messageData['modifications'] as $mod) {
                $configKey .= '_mod_' . md5(($mod['variant']['name'] ?? '') . ($mod['property']['name'] ?? ''));
            }
        }
        $configKey = md5($configKey);
        
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        
        if (isset($_SESSION['cart'][$configKey])) {
            $_SESSION['cart'][$configKey]['quantity'] += $addToCartQuantity;
        } else {
            $_SESSION['cart'][$configKey] = [
                'config_key' => $configKey,
                'product_id' => $productId,
                'name' => $request['product_name'],
                'base_price' => (float)($messageData['unit_price'] ?? $request['base_price']),
                'configuration' => $messageData['configuration'] ?? null,
                'configuration_id' => $configurationId,
                'configuration_name' => $messageData['configuration_name'] ?? '',
                'modifications' => $messageData['modifications'] ?? [],
                'total_price' => (float)($messageData['unit_price'] ?? $request['base_price']),
                'quantity' => $addToCartQuantity,
                'img_url' => '',
                'added_at' => date('Y-m-d H:i:s'),
                'stock_at_add' => $currentStock,
                'is_made_to_order' => false
            ];
            
            $stmtImg = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? LIMIT 1");
            $stmtImg->execute([$productId]);
            $_SESSION['cart'][$configKey]['img_url'] = $stmtImg->fetchColumn() ?: 'img/placeholder.jpg';
            $_SESSION['selected_items'][$configKey] = true;
        }
        
        // Обновляем или удаляем заявку
        if ($remainingQuantity > 0) {
            $messageData['quantity'] = $remainingQuantity;
            $newMessage = json_encode($messageData, JSON_UNESCAPED_UNICODE);
            $stmt = $pdo->prepare("UPDATE request SET message = ?, requested_quantity = ? WHERE id = ?");
            $stmt->execute([$newMessage, $remainingQuantity, $requestId]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM request WHERE id = ? AND user_id = ? AND type = 'wl'");
            $stmt->execute([$requestId, $_SESSION['user_id']]);
        }
        
        session_write_close();
        
        echo json_encode(['success' => true, 'redirect' => 'cart.php']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Запись не найдена']);
    }
    exit;
}

// Функция для получения уникального ключа группировки
function getItemKey($item, $itemData) {
    $key = $item['product_id'];
    $key .= '_' . ($itemData['configuration']['id'] ?? '');
    if (!empty($itemData['modifications'])) {
        $mods = [];
        foreach ($itemData['modifications'] as $mod) {
            $modStr = $mod['group'] . ':' . $mod['variant']['name'];
            if (!empty($mod['property']['name'])) {
                $modStr .= ':' . $mod['property']['name'];
            }
            $mods[] = $modStr;
        }
        sort($mods);
        $key .= '_' . implode('|', $mods);
    }
    return md5($key);
}

// Функция для парсинга данных из JSON
function parseWaitingItemData($message) {
    $data = json_decode($message, true);
    if (!$data) return [];
    return [
        'quantity' => $data['quantity'] ?? 1,
        'configuration' => $data['configuration'] ?? null,
        'configuration_name' => $data['configuration_name'] ?? '',
        'modifications' => $data['modifications'] ?? [],
        'unit_price' => $data['unit_price'] ?? $data['line_total'] ?? 0,
        'is_made_to_order' => $data['is_made_to_order'] ?? false,
        'lead_time' => $data['lead_time'] ?? ''
    ];
}

// Группируем товары (только общие данные, без списка заявок)
$groupedItems = [];

foreach ($waitingItems as $item) {
    $itemData = parseWaitingItemData($item['message']);
    $key = getItemKey($item, $itemData);
    
    if (!isset($groupedItems[$key])) {
        $configurationId = $itemData['configuration']['id'] ?? null;
        $currentStock = getProductStock($pdo, $item['product_id'], $configurationId);
        
        $groupedItems[$key] = [
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'],
            'product_img' => $item['product_img'],
            'configuration' => $itemData['configuration'],
            'configuration_name' => $itemData['configuration_name'],
            'modifications' => $itemData['modifications'],
            'unit_price' => $itemData['unit_price'] ?: $item['base_price'],
            'is_made_to_order' => $itemData['is_made_to_order'],
            'lead_time' => $itemData['lead_time'],
            'total_quantity' => 0,
            'current_stock' => $currentStock,
            'request_ids' => [], // храним ID заявок для удаления
            'first_date' => $item['datetime']
        ];
    }
    
    $groupedItems[$key]['total_quantity'] += $itemData['quantity'];
    $groupedItems[$key]['request_ids'][] = $item['id'];
    
    if (strtotime($item['datetime']) < strtotime($groupedItems[$key]['first_date'])) {
        $groupedItems[$key]['first_date'] = $item['datetime'];
    }
}

uasort($groupedItems, function($a, $b) {
    return strtotime($b['first_date']) - strtotime($a['first_date']);
});
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style_header_footer.css" />
<link rel="stylesheet" href="css/style_main.css" />
<link rel="stylesheet" href="css/style_mip.css" />
<link rel="stylesheet" href="css/header_mip.css" />
<style>
* {
    font-family: 'Inter', sans-serif;
}

.waiting-page {
    max-width: 1600px;
    margin: 40px auto 80px;
    padding-left: 60px;
    padding-right: 60px;
    box-sizing: border-box;
}

.back-link {
    display: inline-block;
    background: #f0f6f4;
    border: 1px solid #e0e8e5;
    color: #4a6a65;
    padding: 8px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    margin-bottom: 30px;
}
.back-link:hover {
    background: #e0e8e5;
    color: #00302e;
}

.waiting-title {
    font-size: 32px;
    font-weight: 700;
    color: #00302e;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e8e5;
}

.waiting-grid {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.waiting-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
    border: 1px solid #e0e8e5;
    transition: all 0.3s ease;
    width: 100%;
}
.waiting-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,168,150,0.1);
    border-color: #00a896;
}

.waiting-card-content {
    display: flex;
    padding: 28px 32px;
    gap: 32px;
    flex-wrap: wrap;
}

.waiting-card-img {
    width: 130px;
    height: 130px;
    object-fit: cover;
    border-radius: 12px;
    background: #f0f6f4;
}

.waiting-card-info {
    flex: 1;
    min-width: 300px;
}

.waiting-card-name {
    font-size: 20px;
    font-weight: 600;
    color: #00302e;
    margin-bottom: 12px;
}

.waiting-card-config {
    font-size: 14px;
    color: #4a6a65;
    margin-bottom: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}

.waiting-card-quantity {
    font-size: 15px;
    color: #00302e;
    margin: 15px 0 12px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.waiting-card-quantity strong {
    color: #4a6a65;
    font-weight: 600;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 8px;
}
.quantity-control button {
    width: 32px;
    height: 32px;
    background: #f0f6f4;
    border: 1px solid #e0e8e5;
    border-radius: 6px;
    cursor: pointer;
    font-size: 18px;
    font-weight: 600;
    color: #4a6a65;
    transition: all 0.3s ease;
}
.quantity-control button:hover {
    background: #e0e8e5;
    color: #00302e;
}
.quantity-control input {
    width: 70px;
    height: 34px;
    text-align: center;
    border: 1px solid #e0e8e5;
    border-radius: 6px;
    font-size: 15px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
}

.btn-save-qty {
    background: #f0f6f4;
    border: 1px solid #e0e8e5;
    color: #4a6a65;
    padding: 7px 18px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s ease;
}
.btn-save-qty:hover {
    background: #e0e8e5;
    color: #00302e;
}

.waiting-card-price {
    font-size: 16px;
    font-weight: 700;
    color: #00a896;
    margin: 10px 0;
}

.lead-time-info {
    font-size: 12px;
    color: #4a6a65;
    background: #f0f6f4;
    padding: 5px 12px;
    border-radius: 6px;
    display: inline-block;
    margin: 8px 0;
}

.waiting-card-status {
    display: inline-block;
    background: #fff8e1;
    color: #e65100;
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 500;
    margin: 12px 0;
}
.waiting-card-status.stock-available {
    background: #e8f5e9;
    color: #2e7d32;
}

.waiting-card-actions {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.btn-waiting-remove {
    background: #f0f6f4;
    color: #e53935;
    border: 1px solid #e0e8e5;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s ease;
}
.btn-waiting-remove:hover {
    background: #ffebee;
    border-color: #e53935;
}

.btn-waiting-catalog {
    background: #f0f6f4;
    border: 1px solid #e0e8e5;
    color: #4a6a65;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}
.btn-waiting-catalog:hover {
    background: #e0e8e5;
    color: #00302e;
}

.btn-add-to-cart {
    background: #00a896;
    color: #fff;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s ease;
}
.btn-add-to-cart:hover:not(:disabled) {
    background: #008a7a;
}
.btn-add-to-cart:disabled {
    background: #ccc;
    cursor: not-allowed;
    opacity: 0.6;
}

.empty-waiting {
    text-align: center;
    padding: 80px 30px;
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e0e8e5;
}
.empty-waiting p {
    font-size: 18px;
    font-weight: 500;
    color: #4a6a65;
    margin-bottom: 15px;
}
.empty-waiting .empty-subtext {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 30px;
}

.info-block {
    margin-top: 30px;
    padding: 18px 24px;
    background: #f0f6f4;
    border-radius: 12px;
    text-align: center;
    font-size: 14px;
    color: #4a6a65;
}

.cart-link-btn {
    display: inline-block;
    background: #00a896;
    color: white;
    padding: 12px 32px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    margin-top: 25px;
    text-align: center;
    transition: all 0.3s ease;
    font-size: 15px;
}
.cart-link-btn:hover {
    background: #008a7a;
}

.text-center {
    text-align: center;
}

@media (max-width: 1200px) {
    .waiting-page {
        padding-left: 40px;
        padding-right: 40px;
    }
    .waiting-card-content {
        padding: 24px;
        gap: 24px;
    }
}

@media (max-width: 900px) {
    .waiting-page {
        padding-left: 30px;
        padding-right: 30px;
    }
}

@media (max-width: 768px) {
    .waiting-page {
        padding-left: 20px;
        padding-right: 20px;
        margin-top: 30px;
    }
    .waiting-card-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    .waiting-card-img {
        margin: 0 auto;
    }
    .waiting-card-actions {
        justify-content: center;
    }
    .waiting-title {
        font-size: 28px;
        text-align: center;
    }
    .waiting-card-quantity {
        flex-direction: column;
        align-items: center;
    }
}

@media (max-width: 480px) {
    .waiting-title {
        font-size: 24px;
    }
    .waiting-card-name {
        font-size: 18px;
    }
    .waiting-card-actions {
        flex-direction: column;
        width: 100%;
    }
    .waiting-card-actions .btn-waiting-catalog,
    .waiting-card-actions .btn-add-to-cart,
    .waiting-card-actions .btn-waiting-remove {
        width: 100%;
        justify-content: center;
    }
    .waiting-card-content {
        padding: 16px;
    }
    .waiting-card-img {
        width: 100px;
        height: 100px;
    }
}
</style>
<title>Лист ожидания</title>
</head>
<body>
<div class="screen">
<div class="div">
<?php $context = 'mip'; require_once '../header.php'; ?>
<div class="waiting-page">
    <a href="lk_user.php" class="back-link">
        ← Вернуться в личный кабинет
    </a>
    
    <div class="waiting-title">
        Лист ожидания
    </div>
    
    <?php if (empty($groupedItems)): ?>
    <div class="empty-waiting">
        <p>У вас нет товаров в листе ожидания</p>
        <p class="empty-subtext">Когда товар появится на складе, мы отправим вам уведомление</p>
        <a href="catalog.php" class="btn-waiting-catalog">
            Перейти в каталог
        </a>
    </div>
    <?php else: ?>
    <div class="waiting-grid">
        <?php foreach ($groupedItems as $group):
            $totalQuantity = $group['total_quantity'];
            $totalPrice = $group['unit_price'] * $totalQuantity;
            $isMadeToOrder = $group['is_made_to_order'];
            $leadTime = $group['lead_time'];
            $currentStock = $group['current_stock'];
            $canOrder = $currentStock > 0;
            $availableToOrder = $canOrder ? min($totalQuantity, $currentStock) : 0;
            $requestIds = implode(',', $group['request_ids']);
        ?>
        <div class="waiting-card">
            <div class="waiting-card-content">
                <img src="<?= htmlspecialchars($group['product_img'] ?: 'img/placeholder.jpg') ?>"
                     alt="<?= htmlspecialchars($group['product_name']) ?>"
                     class="waiting-card-img"
                     onerror="this.src='img/placeholder.jpg'">
                
                <div class="waiting-card-info">
                    <div class="waiting-card-name">
                        <?= htmlspecialchars($group['product_name']) ?>
                    </div>
                    
                    <?php if (!empty($group['configuration_name'])): ?>
                    <div class="waiting-card-config">
                        Комплектация: <?= htmlspecialchars($group['configuration_name']) ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($group['modifications'])): ?>
                    <div class="waiting-card-config">
                        Опции:
                        <?php
                        $mods = [];
                        foreach ($group['modifications'] as $mod) {
                            $modStr = htmlspecialchars($mod['group'] . ': ' . $mod['variant']['name']);
                            if (!empty($mod['property']['name'])) {
                                $modStr .= ' + ' . htmlspecialchars($mod['property']['name']);
                            }
                            $mods[] = $modStr;
                        }
                        echo implode(', ', $mods);
                        ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="waiting-card-quantity">
                        <strong>Ожидаемое количество:</strong>
                        <div class="quantity-control">
                            <button class="qty-decr" onclick="changeQuantity(this, -1, '<?= $requestIds ?>')">−</button>
                            <input type="number" class="group-qty" value="<?= $totalQuantity ?>" min="1" data-request-ids="<?= $requestIds ?>" data-original="<?= $totalQuantity ?>">
                            <button class="qty-incr" onclick="changeQuantity(this, 1, '<?= $requestIds ?>')">+</button>
                        </div>
                        <button class="btn-save-qty" onclick="saveQuantity('<?= $requestIds ?>', <?= $totalQuantity ?>)">
                            Сохранить
                        </button>
                    </div>
                    
                    <?php if ($group['unit_price'] > 0): ?>
                    <div class="waiting-card-price">
                        <?= number_format($totalPrice, 2, ',', ' ') ?> ₽
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($isMadeToOrder && $leadTime): ?>
                    <div class="lead-time-info">
                        Срок изготовления: <?= htmlspecialchars($leadTime) ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="waiting-card-status <?= $canOrder ? 'stock-available' : '' ?>">
                        <?php if ($canOrder): ?>
                            Есть в наличии: <?= $currentStock ?> шт.
                        <?php else: ?>
                            Товар ещё не поступил на склад
                        <?php endif; ?>
                    </div>
                    
                    <div class="waiting-card-actions">
                        <a href="product.php?id=<?= $group['product_id'] ?>" class="btn-waiting-catalog">
                            Подробнее
                        </a>
                        
                        <?php if ($canOrder): ?>
                            <button class="btn-add-to-cart" onclick="addToCart('<?= $requestIds ?>', <?= $availableToOrder ?>, '<?= htmlspecialchars($group['product_name']) ?>')">
                                Заказать (<?= $availableToOrder ?> шт.)
                            </button>
                        <?php else: ?>
                            <button class="btn-add-to-cart" disabled>
                                Нет в наличии
                            </button>
                        <?php endif; ?>
                        
                        <button class="btn-waiting-remove" onclick="confirmRemoveAll('<?= $requestIds ?>', '<?= htmlspecialchars($group['product_name']) ?>')">
                            Отменить ожидание
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="info-block">
        Когда товар появится на складе, мы отправим вам уведомление
    </div>
    
    <div class="text-center">
        <a href="cart.php" class="cart-link-btn">
            Перейти в корзину
        </a>
    </div>
    <?php endif; ?>
</div>
<?php require_once '../footer.php'; ?>
</div>
</div>

<script>
function changeQuantity(btn, delta, requestIds) {
    const container = btn.closest('.waiting-card-quantity');
    const input = container.querySelector('.group-qty');
    let newVal = (parseInt(input.value) || 1) + delta;
    newVal = Math.max(1, newVal);
    if (newVal === parseInt(input.value)) return;
    input.value = newVal;
}

async function saveQuantity(requestIds, totalQuantity) {
    const input = document.querySelector(`.group-qty[data-request-ids="${requestIds}"]`);
    const newQuantity = parseInt(input.value) || 1;
    const originalQuantity = parseInt(input.dataset.original) || 1;
    
    if (newQuantity === originalQuantity) {
        alert('Количество не изменилось');
        return;
    }
    
    const btn = input.parentElement.nextElementSibling;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Сохранение...';
    
    // Разбиваем ID заявок и обновляем каждую
    const ids = requestIds.split(',');
    let success = true;
    
    for (const id of ids) {
        const formData = new FormData();
        formData.append('action', 'update_quantity');
        formData.append('request_id', id);
        formData.append('quantity', Math.floor(newQuantity / ids.length));
        
        try {
            const res = await fetch('waiting_list.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (!data.success) {
                success = false;
            }
        } catch (err) {
            success = false;
        }
    }
    
    if (success) {
        input.dataset.original = newQuantity;
        alert(`Количество обновлено: ${newQuantity} шт.`);
        location.reload();
    } else {
        alert('Ошибка при обновлении количества');
        input.value = originalQuantity;
    }
    
    btn.disabled = false;
    btn.innerHTML = originalText;
}

async function addToCart(requestIds, quantity, productName) {
    if (!confirm(`Добавить товар "${productName}" (${quantity} шт.) в корзину?`)) {
        return;
    }
    
    const btn = event.target.closest('.btn-add-to-cart');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Добавление...';
    
    const ids = requestIds.split(',');
    let success = true;
    
    for (const id of ids) {
        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('request_id', id);
        formData.append('quantity', quantity);
        
        try {
            const res = await fetch('waiting_list.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
            } else {
                success = false;
            }
        } catch (err) {
            success = false;
        }
    }
    
    if (success) {
        window.location.href = 'cart.php';
    } else {
        alert('Ошибка при добавлении в корзину');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function confirmRemoveAll(requestIds, productName) {
    const ids = requestIds.split(',');
    if (confirm(`Вы уверены, что хотите отменить ожидание товара "${productName}"?`)) {
        const formData = new FormData();
        formData.append('action', 'remove_multiple');
        formData.append('ids', JSON.stringify(ids));
        fetch('waiting_list.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else alert('Ошибка при удалении');
            })
            .catch(err => {
                console.error(err);
                alert('Ошибка');
            });
    }
}
</script>
</body>
</html>