<?php
session_start();
require_once 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 0);

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$isJsonAction = isset($_GET['action']) || isset($_POST['action']);

if ($isAjax || $isJsonAction) {
    header('Content-Type: application/json; charset=utf-8');
}

if (!isset($_SESSION['user_id'])) {
    if ($isAjax || $isJsonAction) {
        echo json_encode(['success' => false, 'error' => 'Не авторизован']);
        exit;
    }
    header('Location: authorization.php');
    exit;
}

if (!isset($_SESSION['selected_items'])) $_SESSION['selected_items'] = [];
if (!isset($_SESSION['selected_services'])) $_SESSION['selected_services'] = [];
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// --- ПОЛУЧЕНИЕ АДРЕСА ПОЛЬЗОВАТЕЛЯ ИЗ БД ---
function getUserAddress($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT address FROM user WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && !empty($result['address'])) {
        $address = json_decode($result['address'], true);
        if (is_array($address)) {
            return $address;
        }
    }
    return ['city' => '', 'street' => '', 'house' => ''];
}

$userAddress = getUserAddress($pdo, $_SESSION['user_id']);
$userAddressJson = json_encode($userAddress);

// --- ФУНКЦИИ ВАЛИДАЦИИ АДРЕСА ---
function validateAddressField($value, $fieldName, $required = true) {
    $value = trim($value);
    if ($required && empty($value)) {
        return ['valid' => false, 'error' => "Поле '$fieldName' обязательно для заполнения"];
    }
    if (!empty($value) && $fieldName === 'Город' && !preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]+$/u', $value)) {
        return ['valid' => false, 'error' => 'Город должен содержать только буквы, пробелы и дефисы'];
    }
    if (!empty($value) && $fieldName === 'Город' && mb_strlen($value) < 2) {
        return ['valid' => false, 'error' => 'Город должен содержать минимум 2 символа'];
    }
    if (!empty($value) && $fieldName === 'Улица' && mb_strlen($value) < 2) {
        return ['valid' => false, 'error' => 'Улица должна содержать минимум 2 символа'];
    }
    if ($fieldName === 'Индекс' && !empty($value) && !preg_match('/^\d{6}$/', $value)) {
        return ['valid' => false, 'error' => 'Индекс должен состоять из 6 цифр'];
    }
    return ['valid' => true, 'error' => null];
}

function validateAddress($zip, $city, $street, $house, $deliveryMethod) {
    $errors = [];
    
    if ($deliveryMethod === 'post') {
        $zipValidation = validateAddressField($zip, 'Индекс', true);
        if (!$zipValidation['valid']) $errors[] = $zipValidation['error'];
    }
    
    $cityValidation = validateAddressField($city, 'Город', true);
    if (!$cityValidation['valid']) $errors[] = $cityValidation['error'];
    
    $streetValidation = validateAddressField($street, 'Улица', true);
    if (!$streetValidation['valid']) $errors[] = $streetValidation['error'];
    
    $house = trim($house);
    if (empty($house)) {
        $errors[] = "Поле 'Дом/Квартира' обязательно для заполнения";
    }
    
    return $errors;
}

function getProductStock($pdo, $productId, $configurationId = null) {
    if ($configurationId) {
        $stmt = $pdo->prepare("SELECT characteristics FROM product_configurations WHERE id = ? AND product_id = ?");
        $stmt->execute([$configurationId, $productId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($config && !empty($config['characteristics'])) {
            $chars = json_decode($config['characteristics'], true);
            if (isset($chars['stock'])) return (int)$chars['stock'];
        }
    }
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    return $product ? (int)$product['stock'] : 0;
}

function updateProductStock($pdo, $productId, $configurationId, $newStock) {
    if ($configurationId) {
        $stmt = $pdo->prepare("SELECT characteristics FROM product_configurations WHERE id = ? AND product_id = ? FOR UPDATE");
        $stmt->execute([$configurationId, $productId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($config && !empty($config['characteristics'])) {
            $chars = json_decode($config['characteristics'], true);
            $chars['stock'] = $newStock;
            $updatedChars = json_encode($chars, JSON_UNESCAPED_UNICODE);
            
            $stmt = $pdo->prepare("UPDATE product_configurations SET characteristics = ? WHERE id = ? AND product_id = ?");
            $stmt->execute([$updatedChars, $configurationId, $productId]);
            return true;
        }
        return false;
    } else {
        $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt->execute([$newStock, $productId]);
        return true;
    }
}

function isMadeToOrder($pdo, $productId, $configurationId = null, $hasModifications = false) {
    if ($hasModifications) return true;
    if ($configurationId) {
        $stmt = $pdo->prepare("SELECT characteristics FROM product_configurations WHERE id = ? AND product_id = ?");
        $stmt->execute([$configurationId, $productId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($config && !empty($config['characteristics'])) {
            $chars = json_decode($config['characteristics'], true);
            if (isset($chars['made_to_order']) && $chars['made_to_order'] === true) return true;
            if (isset($chars['stock']) && $chars['stock'] == -1) return true;
        }
    }
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    return $product && (int)$product['stock'] == -1;
}

function getLeadTime($pdo, $productId, $configurationId = null) {
    $leadTime = "14-21 дней";
    if ($configurationId) {
        $stmt = $pdo->prepare("SELECT characteristics FROM product_configurations WHERE id = ? AND product_id = ?");
        $stmt->execute([$configurationId, $productId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($config && !empty($config['characteristics'])) {
            $chars = json_decode($config['characteristics'], true);
            if (isset($chars['lead_time'])) return $chars['lead_time'];
        }
    }
    return $leadTime;
}

function getServiceDetails($pdo, $serviceId) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1");
    $stmt->execute([$serviceId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getRecommendedServices($pdo, $productId) {
    $stmt = $pdo->prepare("SELECT DISTINCT s.*, ps.product_id as linked_product_id FROM services s INNER JOIN product_services ps ON s.id = ps.service_id WHERE ps.product_id = ? AND ps.is_active = 1 AND s.is_active = 1 ORDER BY s.sort_order");
    $stmt->execute([$productId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================
// AJAX ОБРАБОТЧИКИ (оставляем без изменений)
// ============================================

if (isset($_POST['action']) && $_POST['action'] === 'add_to_waiting_list') {
    $configKey = $_POST['config_key'] ?? '';
    $desiredQuantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

    if (!isset($_SESSION['cart'][$configKey])) {
        echo json_encode(['success' => false, 'error' => 'Товар не найден в корзине']);
        exit;
    }

    $item = $_SESSION['cart'][$configKey];
    
    if ($item['is_made_to_order'] || !empty($item['modifications'])) {
        echo json_encode(['success' => false, 'error' => 'Товары с модификациями или доступные только под заказ не добавляются в лист ожидания. Оформите заказ напрямую.']);
        exit;
    }

    $configurationId = $item['configuration_id'] ?? null;
    $requestedQuantity = $desiredQuantity > 0 ? $desiredQuantity : (int)$item['quantity'];
    $currentStock = getProductStock($pdo, $item['product_id'], $configurationId);

    $messageData = [
        'quantity' => $requestedQuantity,
        'product_name' => $item['name'],
        'product_id' => $item['product_id'],
        'configuration' => $item['configuration'],
        'configuration_name' => $item['configuration_name'],
        'modifications' => $item['modifications'],
        'unit_price' => (float)$item['total_price'],
        'waiting_list_option' => 'notify_all',
        'stock_at_add' => $currentStock,
        'is_made_to_order' => false,
        'lead_time' => null
    ];

    try {
        $stmt = $pdo->prepare("INSERT INTO request (user_id, product_id, message, status, datetime, type, waiting_list_option, requested_quantity) VALUES (?, ?, ?, 'waiting', NOW(), 'wl', 'notify_all', ?)");
        $stmt->execute([$_SESSION['user_id'], $item['product_id'], json_encode($messageData, JSON_UNESCAPED_UNICODE), $requestedQuantity]);
        
        unset($_SESSION['cart'][$configKey]);
        unset($_SESSION['selected_items'][$configKey]);
        
        echo json_encode(['success' => true, 'message' => "Товар в количестве {$requestedQuantity} шт. добавлен в лист ожидания"]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Ошибка БД: ' . $e->getMessage()]);
    }
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'update_qty') {
    $configKey = $_POST['config_key'] ?? '';
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    if (isset($_SESSION['cart'][$configKey])) {
        $_SESSION['cart'][$configKey]['quantity'] = $quantity;
        
        foreach ($_SESSION['selected_services'] as $serviceKey => $service) {
            if (isset($service['config_key']) && $service['config_key'] == $configKey) {
                $_SESSION['selected_services'][$serviceKey]['quantity'] = $quantity;
                $_SESSION['selected_services'][$serviceKey]['total_price'] = (float)$service['price'] * $quantity;
            }
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Товар не найден']);
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'add' && !empty($_GET['id'])) {
    $productId = (int)$_GET['id'];
    $input = json_decode(file_get_contents('php://input'), true);

    $configKey = 'prod_' . $productId;
    $configurationId = null;
    $hasModifications = !empty($input['modifications']) && is_array($input['modifications']);

    if (!empty($input['configuration'])) {
        $configurationId = $input['configuration']['id'];
        $configKey .= '_cfg_' . $configurationId;
    }

    if ($hasModifications) {
        foreach ($input['modifications'] as $mod) {
            $configKey .= '_mod_' . md5(($mod['variant']['name'] ?? '') . ($mod['property']['name'] ?? ''));
        }
    }
    $configKey = md5($configKey);

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Товар не найден']);
        exit;
    }

    $isMadeToOrderFlag = isMadeToOrder($pdo, $productId, $configurationId, $hasModifications);
    $currentStock = !$isMadeToOrderFlag ? getProductStock($pdo, $productId, $configurationId) : -1;
    $requestedQuantity = (int)($input['quantity'] ?? 1);
    $leadTime = $isMadeToOrderFlag ? getLeadTime($pdo, $productId, $configurationId) : null;

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if (isset($_SESSION['cart'][$configKey])) {
        $newQuantity = $_SESSION['cart'][$configKey]['quantity'] + $requestedQuantity;
        if (!$isMadeToOrderFlag && $currentStock > 0 && $newQuantity > $currentStock) {
            echo json_encode(['success' => false, 'error' => 'Недостаточно товара на складе. Доступно: ' . $currentStock . ' шт.']);
            exit;
        }
        $_SESSION['cart'][$configKey]['quantity'] = $newQuantity;
    } else {
        $_SESSION['cart'][$configKey] = [
            'config_key' => $configKey,
            'product_id' => $productId,
            'name' => $product['name'],
            'base_price' => (float)$product['base_price'],
            'configuration' => $input['configuration'] ?? null,
            'configuration_id' => $configurationId,
            'configuration_name' => $input['configuration_name'] ?? '',
            'modifications' => $input['modifications'] ?? [],
            'total_price' => (float)($input['total_price'] ?? $product['base_price']),
            'quantity' => $requestedQuantity,
            'img_url' => '',
            'added_at' => date('Y-m-d H:i:s'),
            'stock_at_add' => $currentStock,
            'is_made_to_order' => $isMadeToOrderFlag,
            'lead_time' => $leadTime
        ];
        
        $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? LIMIT 1");
        $stmt->execute([$productId]);
        $_SESSION['cart'][$configKey]['img_url'] = $stmt->fetchColumn() ?: 'img/placeholder.jpg';
        
        $_SESSION['selected_items'][$configKey] = true;
    }

    echo json_encode(['success' => true, 'message' => 'Товар добавлен в корзину', 'cart_count' => count($_SESSION['cart'])]);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'toggle_select') {
    $configKey = $_POST['config_key'] ?? '';
    $isSelected = isset($_POST['selected']) && $_POST['selected'] === 'true';

    if ($isSelected) $_SESSION['selected_items'][$configKey] = true;
    else unset($_SESSION['selected_items'][$configKey]);

    $productsTotal = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if (isset($_SESSION['selected_items'][$key])) {
                if ($item['is_made_to_order'] || !empty($item['modifications'])) {
                    $productsTotal += (float)$item['total_price'] * (int)$item['quantity'];
                } else {
                    $stock = $item['stock_at_add'] ?? 0;
                    $quantity = (int)$item['quantity'];
                    $payableQuantity = ($stock > 0 && $quantity > $stock) ? $stock : $quantity;
                    $productsTotal += (float)$item['total_price'] * $payableQuantity;
                }
            }
        }
    }
    
    $servicesTotal = 0;
    if (!empty($_SESSION['selected_services'])) {
        foreach ($_SESSION['selected_services'] as $serviceKey => $service) {
            if ($service['selected']) {
                $servicesTotal += $service['total_price'] ?? (float)$service['price'];
            }
        }
    }

    echo json_encode(['success' => true, 'products_total' => $productsTotal, 'services_total' => $servicesTotal, 'selected_total' => $productsTotal + $servicesTotal]);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'toggle_service') {
    $configKey = $_POST['config_key'] ?? '';
    $serviceId = $_POST['service_id'] ?? '';
    $isSelected = isset($_POST['selected']) && $_POST['selected'] === 'true';
    $serviceKey = $configKey . '_' . $serviceId;

    if ($isSelected) {
        if (!isset($_SESSION['selected_services'][$serviceKey])) {
            $service = getServiceDetails($pdo, $serviceId);
            if ($service) {
                $productQuantity = 0;
                if (isset($_SESSION['cart'][$configKey])) {
                    $productQuantity = (int)$_SESSION['cart'][$configKey]['quantity'];
                }
                
                $_SESSION['selected_services'][$serviceKey] = [
                    'id' => $service['id'],
                    'name' => $service['name'],
                    'price' => (float)$service['price'],
                    'quantity' => $productQuantity,
                    'total_price' => (float)$service['price'] * $productQuantity,
                    'duration' => $service['duration'],
                    'short_description' => $service['short_description'],
                    'full_description' => $service['full_description'],
                    'img_url' => $service['img_url'],
                    'product_id' => $_SESSION['cart'][$configKey]['product_id'],
                    'config_key' => $configKey,
                    'selected' => true
                ];
            }
        }
    } else {
        unset($_SESSION['selected_services'][$serviceKey]);
    }

    $productsTotal = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if (isset($_SESSION['selected_items'][$key])) {
                if ($item['is_made_to_order'] || !empty($item['modifications'])) {
                    $productsTotal += (float)$item['total_price'] * (int)$item['quantity'];
                } else {
                    $stock = $item['stock_at_add'] ?? 0;
                    $quantity = (int)$item['quantity'];
                    $payableQuantity = ($stock > 0 && $quantity > $stock) ? $stock : $quantity;
                    $productsTotal += (float)$item['total_price'] * $payableQuantity;
                }
            }
        }
    }
    
    $servicesTotal = 0;
    if (!empty($_SESSION['selected_services'])) {
        foreach ($_SESSION['selected_services'] as $sKey => $service) {
            if ($service['selected']) {
                $servicesTotal += $service['total_price'] ?? (float)$service['price'];
            }
        }
    }

    echo json_encode(['success' => true, 'products_total' => $productsTotal, 'services_total' => $servicesTotal, 'selected_total' => $productsTotal + $servicesTotal]);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'get_service_details' && !empty($_GET['service_id'])) {
    try {
        $service = getServiceDetails($pdo, (int)$_GET['service_id']);
        if ($service) {
            echo json_encode(['success' => true, 'service' => [
                'id' => $service['id'], 'name' => htmlspecialchars($service['name']), 'price' => (float)$service['price'],
                'duration' => $service['duration'] ?? '', 'short_description' => $service['short_description'] ?? '',
                'full_description' => $service['full_description'] ?? '', 'img_url' => $service['img_url'] ?? ''
            ]]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Услуга не найдена или неактивна']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Ошибка сервера: ' . $e->getMessage()]);
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'remove' && !empty($_GET['key'])) {
    unset($_SESSION['cart'][$_GET['key']]);
    unset($_SESSION['selected_items'][$_GET['key']]);
    
    foreach ($_SESSION['selected_services'] as $serviceKey => $service) {
        if (isset($service['config_key']) && $service['config_key'] == $_GET['key']) {
            unset($_SESSION['selected_services'][$serviceKey]);
        }
    }
    
    header('Location: cart.php');
    exit;
}

// ============================================
// ОФОРМЛЕНИЕ ЗАКАЗА (БЕЗ parent_request_id)
// ============================================

if (isset($_POST['action']) && $_POST['action'] === 'checkout') {
    $deliveryMethod = trim($_POST['delivery_method'] ?? 'pickup');
    $zip = trim($_POST['zip'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $house = trim($_POST['house'] ?? '');

    if ($deliveryMethod !== 'pickup') {
        $errors = validateAddress($zip, $city, $street, $house, $deliveryMethod);
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'error' => implode("\n", $errors)]);
            exit;
        }
    }

    $fullAddress = '';
    $addressParts = [];

    if ($deliveryMethod === 'post' && $zip) $addressParts[] = "Индекс: $zip";
    if ($city) $addressParts[] = "г. $city";
    if ($street) $addressParts[] = "ул. $street";
    if ($house) $addressParts[] = $house;
    $fullAddress = implode(', ', $addressParts);

    if ($deliveryMethod !== 'pickup' && empty($fullAddress)) {
        echo json_encode(['success' => false, 'error' => 'Пожалуйста, заполните все поля адреса доставки']);
        exit;
    }

    $hasSelected = false;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if (isset($_SESSION['selected_items'][$key])) { $hasSelected = true; break; }
        }
    }
    $hasSelectedServices = !empty($_SESSION['selected_services']);

    if (!$hasSelected && !$hasSelectedServices) {
        echo json_encode(['success' => false, 'error' => 'Выберите хотя бы один товар или услугу для оформления']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Маппинг config_key -> ID созданного заказа (храним в памяти)
        $processedCartKeysMap = [];
        $processedCartKeys = [];
        $processedServiceKeys = [];
        $totalOrderAmount = 0;

        // ========== ОБРАБОТКА ТОВАРОВ ==========
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $configKey => $item) {
                if (!isset($_SESSION['selected_items'][$configKey])) continue;

                $configurationId = $item['configuration_id'] ?? null;
                $hasMods = !empty($item['modifications']);
                $isMadeToOrderFlag = $item['is_made_to_order'] || $hasMods;
                
                $requestedQuantity = (int)$item['quantity'];
                $currentStock = !$isMadeToOrderFlag ? getProductStock($pdo, $item['product_id'], $configurationId) : -1;

                if (!$isMadeToOrderFlag && $currentStock <= 0) {
                    $processedCartKeys[] = $configKey;
                    continue;
                }

                $availableToBuy = 0;
                $waitingQuantity = 0;

                if ($isMadeToOrderFlag) {
                    $availableToBuy = $requestedQuantity;
                } else {
                    if ($requestedQuantity > $currentStock) {
                        $availableToBuy = $currentStock;
                        $waitingQuantity = $requestedQuantity - $currentStock;
                    } else {
                        $availableToBuy = $requestedQuantity;
                    }
                }

                if ($availableToBuy == 0 && $waitingQuantity > 0) {
                    $waitingMessageData = [
                        'quantity' => $waitingQuantity,
                        'product_name' => $item['name'],
                        'product_id' => $item['product_id'],
                        'configuration' => $item['configuration'],
                        'configuration_name' => $item['configuration_name'],
                        'modifications' => $item['modifications'],
                        'unit_price' => (float)$item['total_price'],
                        'reason' => 'out_of_stock'
                    ];
                    $stmt = $pdo->prepare("INSERT INTO request (user_id, product_id, message, status, datetime, type, requested_quantity) VALUES (?, ?, ?, 'waiting', NOW(), 'wl', ?)");
                    $stmt->execute([$_SESSION['user_id'], $item['product_id'], json_encode($waitingMessageData, JSON_UNESCAPED_UNICODE), $waitingQuantity]);
                    $processedCartKeys[] = $configKey;
                    continue;
                }

                if ($availableToBuy == 0) {
                    $processedCartKeys[] = $configKey;
                    continue;
                }

                if (!$isMadeToOrderFlag && $availableToBuy > 0) {
                    $newStock = $currentStock - $availableToBuy;
                    updateProductStock($pdo, $item['product_id'], $configurationId, $newStock);
                }

                $lineTotal = (float)$item['total_price'] * $availableToBuy;
                $totalOrderAmount += $lineTotal;
                
                $messageData = [
                    'quantity' => $requestedQuantity,
                    'available_to_buy' => $availableToBuy,
                    'waiting_quantity' => $waitingQuantity,
                    'address' => $fullAddress,
                    'delivery_method' => $deliveryMethod,
                    'product_name' => $item['name'],
                    'product_id' => $item['product_id'],
                    'configuration' => $item['configuration'],
                    'configuration_name' => $item['configuration_name'],
                    'modifications' => $item['modifications'],
                    'unit_price' => (float)$item['total_price'],
                    'line_total' => $lineTotal,
                    'is_made_to_order' => $isMadeToOrderFlag,
                    'lead_time' => $item['lead_time'],
                    'stock_at_order' => $currentStock,
                    'new_stock' => !$isMadeToOrderFlag ? max(0, $currentStock - $availableToBuy) : null
                ];
                $message = json_encode($messageData, JSON_UNESCAPED_UNICODE);

                $orderId = null;
                if ($waitingQuantity > 0) {
                    $stmt = $pdo->prepare("INSERT INTO request (user_id, product_id, message, status, datetime, type, requested_quantity, shipped_quantity) VALUES (?, ?, ?, 'processed', NOW(), 'r', ?, ?)");
                    $stmt->execute([$_SESSION['user_id'], $item['product_id'], $message, $requestedQuantity, $availableToBuy]);
                    $orderId = $pdo->lastInsertId();
                    $parentRequestId = $orderId;

                    $waitingMessageData = [
                        'quantity' => $waitingQuantity,
                        'product_name' => $item['name'],
                        'product_id' => $item['product_id'],
                        'configuration' => $item['configuration'],
                        'configuration_name' => $item['configuration_name'],
                        'modifications' => $item['modifications'],
                        'unit_price' => (float)$item['total_price'],
                        'parent_request_id' => $parentRequestId,
                        'reason' => 'partial_order'
                    ];
                    $stmt = $pdo->prepare("INSERT INTO request (user_id, product_id, message, status, datetime, type, requested_quantity) VALUES (?, ?, ?, 'waiting', NOW(), 'wl', ?)");
                    $stmt->execute([$_SESSION['user_id'], $item['product_id'], json_encode($waitingMessageData, JSON_UNESCAPED_UNICODE), $waitingQuantity]);
                } else {
                    $status = $isMadeToOrderFlag ? 'processed' : 'new';
                    $stmt = $pdo->prepare("INSERT INTO request (user_id, product_id, message, status, datetime, type, requested_quantity, shipped_quantity) VALUES (?, ?, ?, ?, NOW(), 'r', ?, ?)");
                    $stmt->execute([$_SESSION['user_id'], $item['product_id'], $message, $status, $requestedQuantity, $availableToBuy]);
                    $orderId = $pdo->lastInsertId();
                }
                
                if ($orderId) {
                    $processedCartKeysMap[$configKey] = $orderId;
                }
                $processedCartKeys[] = $configKey;
            }
        }

        // ========== ОБРАБОТКА УСЛУГ (связь через JSON) ==========
        if (!empty($_SESSION['selected_services'])) {
            foreach ($_SESSION['selected_services'] as $serviceKey => $service) {
                if ($service['selected']) {
                    $serviceQuantity = $service['quantity'] ?? 1;
                    $serviceTotalPrice = $service['total_price'] ?? ((float)$service['price'] * $serviceQuantity);
                    $totalOrderAmount += $serviceTotalPrice;
                    
                    // Находим ID заказа, к которому привязана услуга
                    $linkedOrderId = null;
                    $configKey = $service['config_key'] ?? null;
                    
                    if ($configKey && isset($processedCartKeysMap[$configKey])) {
                        $linkedOrderId = $processedCartKeysMap[$configKey];
                    }
                    
                    $messageData = [
                        'type' => 'service', 
                        'service_id' => $service['id'], 
                        'service_name' => $service['name'],
                        'price' => (float)$service['price'],
                        'quantity' => $serviceQuantity,
                        'total_price' => $serviceTotalPrice,
                        'product_id' => $service['product_id'],
                        'delivery_method' => $deliveryMethod, 
                        'ordered_at' => date('Y-m-d H:i:s'),
                        'linked_order_id' => $linkedOrderId  // ← связь в JSON
                    ];
                    if ($deliveryMethod !== 'pickup') $messageData['address'] = $fullAddress;

                    // Сохраняем услугу БЕЗ parent_request_id
                    $stmt = $pdo->prepare("INSERT INTO request (user_id, message, status, datetime, type) VALUES (?, ?, 'new', NOW(), 's')");
                    $stmt->execute([$_SESSION['user_id'], json_encode($messageData, JSON_UNESCAPED_UNICODE)]);
                    
                    $processedServiceKeys[] = $serviceKey;
                }
            }
        }

        if ($totalOrderAmount == 0) {
            echo json_encode(['success' => false, 'error' => 'Нет товаров для оплаты. Сумма заказа не может быть 0.']);
            $pdo->rollBack();
            exit;
        }

        $pdo->commit();
        
        foreach ($processedCartKeys as $key) {
            unset($_SESSION['cart'][$key]);
            unset($_SESSION['selected_items'][$key]);
        }
        
        foreach ($processedServiceKeys as $key) {
            unset($_SESSION['selected_services'][$key]);
        }

        echo json_encode(['success' => true, 'total_amount' => $totalOrderAmount]);
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Ошибка оформления: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================
// ПОДГОТОВКА ДАННЫХ ДЛЯ ОТОБРАЖЕНИЯ (оставляем без изменений)
// ============================================

$cartItems = [];
$totalQuantity = 0;
$productsTotal = 0;
$servicesTotal = 0;
$selectedServicesCount = 0;
$stockInfo = [];
$madeToOrderInfo = [];
$leadTimeInfo = [];

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $configKey => $item) {
        $configurationId = $item['configuration_id'] ?? null;
        $hasMods = !empty($item['modifications']);
        $isMadeToOrderFlag = isMadeToOrder($pdo, $item['product_id'], $configurationId, $hasMods);
        
        $madeToOrderInfo[$configKey] = $isMadeToOrderFlag;

        if (!$isMadeToOrderFlag) {
            $currentStock = getProductStock($pdo, $item['product_id'], $configurationId);
            $stockInfo[$configKey] = $currentStock;
            $_SESSION['cart'][$configKey]['stock_at_add'] = $currentStock;
        } else {
            $stockInfo[$configKey] = -1;
            $leadTimeInfo[$configKey] = $item['lead_time'] ?? getLeadTime($pdo, $item['product_id'], $configurationId);
        }
    }
}

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $configKey => $item) {
        $isSelected = isset($_SESSION['selected_items'][$configKey]);
        $requestedQuantity = (int)$item['quantity'];
        $currentStock = $stockInfo[$configKey];
        $isMadeToOrderFlag = $madeToOrderInfo[$configKey];
        $leadTime = $leadTimeInfo[$configKey] ?? null;

        if ($isMadeToOrderFlag) {
            $payableQuantity = $requestedQuantity;
        } else {
            $payableQuantity = ($currentStock > 0 && $requestedQuantity > $currentStock) ? $currentStock : $requestedQuantity;
            if ($currentStock <= 0) $payableQuantity = 0;
        }

        $subtotal = (float)$item['total_price'] * $payableQuantity;
        $totalQuantity += $requestedQuantity;
        if ($isSelected) $productsTotal += $subtotal;

        $isLowStock = !$isMadeToOrderFlag && $currentStock > 0 && $currentStock <= 5;
        $isOutOfStock = !$isMadeToOrderFlag && $currentStock <= 0;
        $exceedsStock = !$isMadeToOrderFlag && $currentStock > 0 && $requestedQuantity > $currentStock;

        $cartItems[] = [
            'config_key' => $configKey,
            'product_id' => $item['product_id'],
            'name' => $item['name'],
            'price' => (float)$item['total_price'],
            'img_url' => $item['img_url'],
            'quantity' => $requestedQuantity,
            'payable_quantity' => $payableQuantity,
            'subtotal' => $subtotal,
            'selected' => $isSelected,
            'configuration_name' => $item['configuration_name'] ?? '',
            'modifications' => $item['modifications'],
            'stock' => $currentStock,
            'is_low_stock' => $isLowStock,
            'is_out_of_stock' => $isOutOfStock,
            'exceeds_stock' => $exceedsStock,
            'is_made_to_order' => $isMadeToOrderFlag,
            'lead_time' => $leadTime
        ];
    }
}

if (!empty($_SESSION['selected_services'])) {
    foreach ($_SESSION['selected_services'] as $serviceKey => $service) {
        if ($service['selected']) {
            $serviceQuantity = $service['quantity'] ?? 1;
            $serviceTotalPrice = $service['total_price'] ?? ((float)$service['price'] * $serviceQuantity);
            
            $servicesTotal += $serviceTotalPrice;
            $selectedServicesCount++;
        }
    }
}

$selectedTotal = $productsTotal + $servicesTotal;

$recommendedServices = [];

foreach ($cartItems as $item) {
    $configKey = $item['config_key'];
    $productId = $item['product_id'];
    
    if (!isset($recommendedServices[$configKey])) {
        $services = getRecommendedServices($pdo, $productId);
        if (!empty($services)) {
            $uniqueServices = [];
            foreach ($services as $service) {
                if (!isset($uniqueServices[$service['id']])) {
                    $uniqueServices[$service['id']] = $service;
                }
            }
            $services = array_values($uniqueServices);
            
            foreach ($services as $index => $service) {
                $serviceKey = $configKey . '_' . $service['id'];
                $services[$index]['is_selected'] = isset($_SESSION['selected_services'][$serviceKey]) && $_SESSION['selected_services'][$serviceKey]['selected'];
                $services[$index]['service_key'] = $serviceKey;
                $services[$index]['config_key'] = $configKey;
            }
            
            $recommendedServices[$configKey] = [
                'product' => $item,
                'services' => $services,
                'quantity' => $item['quantity']
            ];
        }
    }
}

$waitingListCount = 0;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM request WHERE user_id = ? AND type = 'wl' AND status = 'waiting'");
$stmt->execute([$_SESSION['user_id']]);
$waitingListCount = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/style_header_footer.css" />
<link rel="stylesheet" href="css/style_main.css" />
<link rel="stylesheet" href="css/header_mip.css" />
<style>
/* Стили остаются без изменений */
* { font-family: 'Inter', sans-serif; }
.cart-page { max-width: 1400px; margin: 40px auto 80px; padding: 0 40px; }
.cart-title { font-size: 32px; font-weight: 700; color: #00302e; margin-bottom: 30px; }
.empty-cart { text-align: center; padding: 60px; background: #ffffff; border-radius: 16px; border: 1px solid #e0e8e5; }
.empty-text { font-size: 18px; color: #4a6a65; margin-bottom: 20px; }
.select-all { margin-bottom: 24px; padding: 14px 20px; background: #f0f6f4; border-radius: 12px; }
.select-all label { display: flex; align-items: center; gap: 12px; cursor: pointer; font-weight: 500; font-size: 16px; color: #00302e; }
.select-all input[type="checkbox"] { width: 20px; height: 20px; cursor: pointer; accent-color: #00a896; }
.cart-layout { display: flex; gap: 40px; }
.cart-items { flex: 2; }
.cart-summary { flex: 1; background: #ffffff; border-radius: 16px; padding: 24px; border: 1px solid #e0e8e5; box-shadow: 0 4px 12px rgba(0, 168, 150, 0.08); height: fit-content; position: sticky; top: 100px; }
.cart-item { background: #ffffff; border-radius: 16px; margin-bottom: 20px; border: 1px solid #e0e8e5; transition: all 0.3s ease; overflow: hidden; }
.cart-item:hover { border-color: #00a896; box-shadow: 0 4px 12px rgba(0, 168, 150, 0.1); }
.cart-item-top { display: flex; gap: 20px; padding: 20px; border-bottom: 1px solid #e0e8e5; }
.select-checkbox { display: flex; align-items: flex-start; padding-top: 4px; }
.select-checkbox input[type="checkbox"] { width: 22px; height: 22px; cursor: pointer; accent-color: #00a896; }
.cart-image { width: 120px; height: 120px; object-fit: cover; border-radius: 12px; background: #f0f6f4; flex-shrink: 0; }
.cart-info { flex: 1; display: flex; flex-direction: column; gap: 8px; }
.cart-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; }
.cart-name { font-size: 18px; font-weight: 700; color: #00302e; margin: 0; }
.cart-price { font-size: 18px; font-weight: 700; color: #00a896; white-space: nowrap; }
.cart-config { font-size: 14px; color: #4a6a65; background: #f0f6f4; padding: 8px 12px; border-radius: 8px; }
.cart-config strong { color: #00302e; }
.cart-mods { font-size: 14px; color: #4a6a65; }
.cart-mods strong { color: #00302e; }
.cart-mods ul { margin: 6px 0 0 20px; padding: 0; }
.cart-mods li { margin: 4px 0; }
.cart-item-bottom { padding: 0 20px 20px 20px; display: flex; flex-direction: column; gap: 16px; }
.stock-warning { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; font-size: 14px; flex-wrap: wrap; }
.stock-warning.low { background: #fff8e1; color: #e65100; }
.stock-warning.out { background: #ffebee; color: #c62828; }
.stock-warning.exceeds { background: #ffebee; color: #c62828; }
.stock-warning.made-to-order { background: #e0f7fa; color: #006e6a; }
.waiting-list-btn { background: #ffc107; color: #00302e; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s; }
.waiting-list-btn:hover { background: #e0a800; }
.partial-info { margin-top: 8px; padding: 12px 16px; background: #e8f5e9; border-radius: 10px; }
.partial-info p { margin: 4px 0; font-size: 14px; color: #1b5e20; }
.payable-info { color: #2e7d32; font-weight: 700; margin-top: 8px; font-size: 15px; }
.cart-footer-row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding-top: 8px; }
.quantity-controls { display: inline-flex; align-items: center; border: 1px solid #e0e8e5; border-radius: 10px; overflow: hidden; height: 42px; }
.qty-btn { width: 42px; height: 42px; border: none; background: #f8f9fa; color: #00302e; cursor: pointer; font-size: 18px; font-weight: 600; transition: all 0.2s; }
.qty-btn:hover { background: #e0e8e5; }
.qty-btn:disabled { color: #c5cbd1; cursor: not-allowed; }
.qty-input { width: 60px; height: 42px; border: none; border-left: 1px solid #e0e8e5; border-right: 1px solid #e0e8e5; text-align: center; font-size: 16px; font-weight: 500; color: #00302e; background: #fff; }
.qty-input:focus { outline: none; }
.cart-subtotal-display { font-size: 16px; font-weight: 600; color: #00302e; }
.cart-subtotal-display span { font-weight: 700; color: #00a896; }
.cart-subtotal-display small { display: block; font-size: 12px; color: #2e7d32; font-weight: 400; }
.cart-remove { background: none; border: none; color: #c62828; cursor: pointer; font-size: 20px; padding: 8px 12px; border-radius: 8px; transition: all 0.2s; line-height: 1; }
.cart-remove:hover { background: #ffebee; color: #b71c1c; }
.recommended-services { margin-top: 8px; padding-top: 16px; border-top: 1px solid #e0e8e5; width: 100%; }
.recommended-services h4 { font-size: 16px; font-weight: 600; margin-bottom: 16px; color: #00302e; }
.services-list { display: flex; flex-direction: column; gap: 12px; }
.service-recommend-item { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; background: #f8f9fa; border-radius: 12px; border: 1px solid #e0e8e5; transition: all 0.2s; width: 100%; box-sizing: border-box; }
.service-recommend-item:hover { border-color: #00a896; background: #f0f6f4; }
.service-recommend-info { flex: 1; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.service-recommend-name { font-weight: 600; font-size: 15px; color: #00302e; }
.service-recommend-price { font-size: 15px; font-weight: 700; color: #00a896; }
.service-recommend-duration { font-size: 13px; color: #4a6a65; }
.service-recommend-quantity { font-size: 13px; color: #4a6a65; background: #f0f6f4; padding: 4px 10px; border-radius: 20px; }
.service-actions { display: flex; align-items: center; gap: 12px; }
.btn-service-detail { background: none; border: 1px solid #e0e8e5; color: #00a896; cursor: pointer; font-size: 13px; font-weight: 500; padding: 6px 14px; border-radius: 8px; transition: all 0.2s; }
.btn-service-detail:hover { background: #00a896; color: #fff; border-color: #00a896; }
.service-select-checkbox { width: 20px; height: 20px; cursor: pointer; accent-color: #00a896; }
.summary-title { font-size: 20px; font-weight: 700; color: #00302e; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #e0e8e5; }
.summary-row { display: flex; justify-content: space-between; padding: 10px 0; font-size: 15px; color: #4a6a65; }
.summary-label { font-weight: 500; }
.summary-value { font-weight: 600; color: #00302e; }
.services-summary-row { border-top: 1px dashed #e0e8e5; margin-top: 5px; }
.total-row { display: flex; justify-content: space-between; padding: 15px 0; margin-top: 10px; border-top: 1px solid #e0e8e5; font-size: 18px; font-weight: 700; color: #00302e; }
.total-row span:last-child { color: #00a896; }
.delivery-methods { margin: 20px 0; }
.delivery-methods h3 { font-size: 18px; font-weight: 600; color: #00302e; margin-bottom: 15px; }
.delivery-tabs { display: flex; border-bottom: 2px solid #e0e8e5; margin-bottom: 16px; gap: 0; }
.delivery-tab { flex: 1; background: none; border: none; padding: 12px 16px; font-size: 15px; font-weight: 500; color: #4a6a65; cursor: pointer; transition: all 0.2s; border-bottom: 2px solid transparent; margin-bottom: -2px; }
.delivery-tab:hover { color: #00a896; }
.delivery-tab.active { color: #00a896; border-bottom-color: #00a896; font-weight: 600; }
.delivery-panel { display: none; animation: fadeIn 0.2s ease; }
.delivery-panel.active { display: block; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
.pickup-info { padding: 16px; background: #f0f6f4; border: 1px solid #e0e8e5; border-radius: 12px; }
.pickup-details p { margin: 0 0 8px; font-size: 14px; color: #4a6a65; line-height: 1.5; }
.address-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.input-group { display: flex; flex-direction: column; gap: 5px; }
.input-group.full-width { grid-column: 1 / -1; }
.input-group label { font-size: 13px; font-weight: 500; color: #4a6a65; }
.input-field { padding: 12px 14px; border: 1px solid #e0e8e5; border-radius: 10px; font-size: 14px; transition: all 0.2s; }
.input-field:focus { border-color: #00a896; outline: none; }
.input-field.error { border-color: #dc3545; background-color: #fff5f5; }
.error-message { color: #dc3545; font-size: 12px; margin-top: 4px; display: none; }
.checkout-btn { width: 100%; padding: 16px; background: #00a896; color: #fff; border: none; border-radius: 12px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s; margin-top: 20px; }
.checkout-btn:hover { background: #008a7a; }
.service-modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 48, 46, 0.8); justify-content: center; align-items: center; }
.service-modal-content { background: #fff; border-radius: 20px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2); }
.service-modal-header { display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 2px solid #e0e8e5; }
.service-modal-header h3 { margin: 0; font-size: 20px; font-weight: 600; color: #00302e; }
.service-modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #4a6a65; }
.service-modal-close:hover { color: #00a896; }
.service-modal-body { padding: 24px; }
.service-info-block { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f6f4; font-size: 15px; }
.service-info-label { font-weight: 500; color: #4a6a65; }
.service-info-value { color: #00302e; font-weight: 500; }
.service-description { margin-top: 16px; padding-top: 16px; color: #4a6a65; line-height: 1.6; font-size: 14px; }
.service-modal-footer { padding: 16px 24px; border-top: 1px solid #e0e8e5; display: flex; justify-content: flex-end; }
.btn-service-cancel { background: #e0e8e5; color: #4a6a65; border: none; padding: 10px 24px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; }
.btn-service-cancel:hover { background: #d0ddd9; color: #00302e; }
@media (max-width: 1000px) { .cart-layout { flex-direction: column; } .cart-summary { position: static; } .cart-page { padding: 0 20px; } }
@media (max-width: 768px) { .cart-item-top { flex-wrap: wrap; } .cart-image { width: 100%; height: 160px; } .cart-header { flex-direction: column; } .cart-footer-row { flex-direction: column; align-items: flex-start; } .service-recommend-info { flex-direction: column; align-items: flex-start; } .service-actions { flex-direction: column; align-items: flex-end; } .delivery-tabs { flex-direction: column; border-bottom: none; border: 1px solid #e0e8e5; border-radius: 12px; overflow: hidden; } .delivery-tab { border-bottom: 1px solid #e0e8e5; margin-bottom: 0; text-align: left; } .delivery-tab:last-child { border-bottom: none; } .delivery-tab.active { border-bottom-color: #e0e8e5; background: #f0f6f4; } .address-fields { grid-template-columns: 1fr; } .cart-page { margin-top: 30px; padding: 0 15px; } .cart-title { font-size: 26px; } }
</style>
<title>Корзина</title>
</head>
<body>
<div class="screen">
<div class="div">
<?php $context = 'mip'; require_once '../header.php'; ?>

<div class="cart-page">
    <h1 class="cart-title">Ваша корзина</h1>
    
    <?php if (empty($cartItems)): ?>
    <div class="empty-cart">
        <p class="empty-text">Корзина пуста</p>
        <a href="catalog.php" class="checkout-btn" style="display: inline-block; width: auto; padding: 12px 32px; text-decoration: none;">Перейти в каталог</a>
    </div>
    <?php else: ?>
    
    <div class="select-all">
        <label><input type="checkbox" id="selectAllCheckbox"> <span>Выбрать все товары</span></label>
    </div>
    
    <div class="cart-layout">
        <div class="cart-items">
            <?php foreach ($cartItems as $item): ?>
            <div class="cart-item <?= $item['selected'] ? 'selected' : '' ?>" data-config-key="<?= htmlspecialchars($item['config_key']) ?>" data-price="<?= $item['price'] ?>" data-product-id="<?= $item['product_id'] ?>">
                
                <div class="cart-item-top">
                    <div class="select-checkbox">
                        <input type="checkbox" class="item-select" data-config-key="<?= htmlspecialchars($item['config_key']) ?>" <?= $item['selected'] ? 'checked' : '' ?>>
                    </div>
                    <img src="<?= htmlspecialchars($item['img_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-image" onerror="this.src='img/placeholder.jpg'">
                    
                    <div class="cart-info">
                        <div class="cart-header">
                            <h3 class="cart-name"><?= htmlspecialchars($item['name']) ?></h3>
                            <span class="cart-price"><?= number_format($item['price'], 2, ',', ' ') ?> ₽</span>
                        </div>
                        
                        <?php if (!empty($item['configuration_name'])): ?>
                        <div class="cart-config"><strong>Комплектация:</strong> <?= htmlspecialchars($item['configuration_name']) ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['modifications'])): ?>
                        <div class="cart-mods">
                            <strong>Опции:</strong>
                            <ul><?php foreach ($item['modifications'] as $mod): ?>
                                <li><?= htmlspecialchars($mod['group']) ?>: <?= htmlspecialchars($mod['variant']['name']) ?> <?php if (!empty($mod['property'])): ?> (+ <?= htmlspecialchars($mod['property']['name']) ?>)<?php endif; ?></li>
                            <?php endforeach; ?></ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="cart-item-bottom">
                    
                    <?php if ($item['is_made_to_order']): ?>
                    <div class="stock-warning made-to-order">
                        <span>Данный товар изготавливается под заказ. Срок: <?= htmlspecialchars($item['lead_time']) ?></span>
                    </div>
                    <?php elseif ($item['exceeds_stock']): ?>
                    <div class="stock-warning exceeds">
                        <span>В наличии только <?= $item['stock'] ?> шт., а вы заказываете <?= $item['quantity'] ?> шт.</span>
                    </div>
                    <div class="partial-info">
                        <p><strong><?= $item['stock'] ?> шт.</strong> можно приобрести сейчас</p>
                        <p><strong><?= $item['quantity'] - $item['stock'] ?> шт.</strong> будет добавлено в лист ожидания</p>
                        <p class="payable-info">К оплате: <strong><?= number_format($item['price'] * $item['stock'], 2, ',', ' ') ?> ₽</strong> (за <?= $item['stock'] ?> шт.)</p>
                        <button class="waiting-list-btn" onclick="addToWaitingList('<?= htmlspecialchars($item['config_key']) ?>', <?= $item['quantity'] ?>)">Добавить всё в лист ожидания (<?= $item['quantity'] ?> шт.)</button>
                    </div>
                    <?php elseif ($item['is_out_of_stock']): ?>
                    <div class="stock-warning out">
                        <span>Нет в наличии.</span>
                        <button class="waiting-list-btn" onclick="addToWaitingList('<?= htmlspecialchars($item['config_key']) ?>', <?= $item['quantity'] ?>)">Добавить в лист ожидания (<?= $item['quantity'] ?> шт.)</button>
                    </div>
                    <?php elseif ($item['is_low_stock']): ?>
                    <div class="stock-warning low">
                        <span>Осталось всего <?= $item['stock'] ?> шт. Торопитесь!</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="cart-footer-row">
                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="changeQty('<?= htmlspecialchars($item['config_key']) ?>', -1)">−</button>
                            <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1" onchange="updateQty('<?= htmlspecialchars($item['config_key']) ?>', this.value)">
                            <button class="qty-btn" onclick="changeQty('<?= htmlspecialchars($item['config_key']) ?>', 1)">+</button>
                        </div>
                        <div class="cart-subtotal-display">
                            Итого: <span data-subtotal="<?= $item['subtotal'] ?>"><?= number_format($item['subtotal'], 2, ',', ' ') ?> ₽</span>
                            <?php if ($item['exceeds_stock']): ?>
                            <small>(оплачивается <?= $item['stock'] ?> шт. из <?= $item['quantity'] ?>)</small>
                            <?php endif; ?>
                        </div>
                        <button class="cart-remove" onclick="removeItem('<?= htmlspecialchars($item['config_key']) ?>')"><i class="fas fa-times"></i></button>
                    </div>
                    
                    <?php if (isset($recommendedServices[$item['config_key']])): ?>
                    <div class="recommended-services">
                        <h4>Рекомендуем добавить к этому товару:</h4>
                        <div class="services-list" data-config-key="<?= htmlspecialchars($item['config_key']) ?>">
                            <?php foreach ($recommendedServices[$item['config_key']]['services'] as $service): ?>
                            <div class="service-recommend-item" data-service-id="<?= $service['id'] ?>" data-config-key="<?= htmlspecialchars($service['config_key']) ?>">
                                <div class="service-recommend-info">
                                    <span class="service-recommend-name"><?= htmlspecialchars($service['name']) ?></span>
                                    <span class="service-recommend-price"><?= number_format($service['price'], 2, ',', ' ') ?> ₽</span>
                                    <?php if (!empty($service['duration'])): ?>
                                    <span class="service-recommend-duration"><?= htmlspecialchars($service['duration']) ?></span>
                                    <?php endif; ?>
                                    <span class="service-recommend-quantity">× <?= $item['quantity'] ?> шт. = <?= number_format($service['price'] * $item['quantity'], 2, ',', ' ') ?> ₽</span>
                                </div>
                                <div class="service-actions">
                                    <button class="btn-service-detail" onclick="showServiceDetails(<?= $service['id'] ?>)">Подробнее</button>
                                    <input type="checkbox" class="service-select-checkbox" 
                                        data-config-key="<?= htmlspecialchars($service['config_key']) ?>"
                                        data-service-id="<?= $service['id'] ?>" 
                                        <?= $service['is_selected'] ? 'checked' : '' ?>>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-summary">
            <h2 class="summary-title">Оформление заказа</h2>
            <div class="summary-row"><span class="summary-label">Выбрано товаров:</span><span class="summary-value" id="selected-count"><?= count(array_filter($cartItems, function($item) { return $item['selected']; })) ?></span></div>
            <div class="summary-row"><span class="summary-label">Заказано единиц:</span><span class="summary-value" id="total-quantity"><?= $totalQuantity ?></span></div>
            
            <div class="summary-row services-summary-row" id="services-summary-row" <?= $selectedServicesCount > 0 ? '' : 'style="display:none;"' ?>>
                <span class="summary-label">Выбрано услуг: <span id="selected-services-count"><?= $selectedServicesCount ?></span></span>
                <span class="summary-value" id="services-total"><?= number_format($servicesTotal, 2, ',', ' ') ?> ₽</span>
            </div>
            
            <div class="summary-row"><span class="summary-label">К оплате (товары):</span><span class="summary-value" id="products-total"><?= number_format($productsTotal, 2, ',', ' ') ?> ₽</span></div>
            <div class="total-row">Итого к оплате: <span id="total-display"><?= number_format($selectedTotal, 2, ',', ' ') ?> ₽</span></div>
            
            <div class="delivery-methods">
                <h3>Способ получения</h3>
                <div class="delivery-tabs">
                    <button type="button" class="delivery-tab active" data-method="pickup">Самовывоз</button>
                    <button type="button" class="delivery-tab" data-method="city">Доставка по городу</button>
                    <button type="button" class="delivery-tab" data-method="post">Почта России</button>
                </div>
                
                <div class="delivery-panel active" data-panel="pickup">
                    <div class="pickup-info">
                        <div class="pickup-details">
                            <p><strong>Адрес пункта выдачи:</strong><br>г. Чита, ул. Баргузинская, 49</p>
                            <p><strong>График работы:</strong><br>Пн-Пт: 09:00 - 18:00<br>Сб-Вс: 10:00 - 16:00</p>
                            <p>Заказ будет ждать вас в течение 3-х рабочих дней.</p>
                        </div>
                    </div>
                </div>
                
                <div class="delivery-panel" data-panel="city">
                    <div class="address-fields">
                        <div class="input-group full-width">
                            <label>Город <span style="color:#dc3545;">*</span></label>
                            <input type="text" id="addr-city" class="input-field" placeholder="Чита" value="<?= htmlspecialchars($userAddress['city'] ?? '') ?>">
                            <div class="error-message" id="error-city"></div>
                        </div>
                        <div class="input-group full-width">
                            <label>Улица <span style="color:#dc3545;">*</span></label>
                            <input type="text" id="addr-street" class="input-field" placeholder="Ленина" value="<?= htmlspecialchars($userAddress['street'] ?? '') ?>">
                            <div class="error-message" id="error-street"></div>
                        </div>
                        <div class="input-group full-width">
                            <label>Дом/Квартира <span style="color:#dc3545;">*</span></label>
                            <input type="text" id="addr-house" class="input-field" placeholder="д. 15, кв. 5" value="<?= htmlspecialchars($userAddress['house'] ?? '') ?>">
                            <div class="error-message" id="error-house"></div>
                            <small style="font-size: 11px; color: #4a6a65;">Пример: 15, 15а, 15/2, 15 кв.5</small>
                        </div>
                    </div>
                </div>
                
                <div class="delivery-panel" data-panel="post">
                    <div class="address-fields">
                        <div class="input-group full-width">
                            <label>Индекс <span style="color:#dc3545;">*</span></label>
                            <input type="text" id="addr-zip" class="input-field" placeholder="672000">
                            <div class="error-message" id="error-zip"></div>
                        </div>
                        <div class="input-group full-width">
                            <label>Город <span style="color:#dc3545;">*</span></label>
                            <input type="text" id="addr-city-post" class="input-field" placeholder="Чита">
                            <div class="error-message" id="error-city-post"></div>
                        </div>
                        <div class="input-group full-width">
                            <label>Улица <span style="color:#dc3545;">*</span></label>
                            <input type="text" id="addr-street-post" class="input-field" placeholder="Ленина">
                            <div class="error-message" id="error-street-post"></div>
                        </div>
                        <div class="input-group full-width">
                            <label>Дом/Квартира <span style="color:#dc3545;">*</span></label>
                            <input type="text" id="addr-house-post" class="input-field" placeholder="д. 15, кв. 5">
                            <div class="error-message" id="error-house-post"></div>
                            <small style="font-size: 11px; color: #4a6a65;">Пример: 15, 15а, 15/2, 15 кв.5</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="button" class="checkout-btn" onclick="checkout()">Оформить заказ</button>
        </div>
    </div>
    <?php endif; ?>
</div>

<div id="serviceDetailModal" class="service-modal">
    <div class="service-modal-content">
        <div class="service-modal-header"><h3 id="modalServiceTitle">Детали услуги</h3><button class="service-modal-close" onclick="closeServiceDetailModal()">&times;</button></div>
        <div class="service-modal-body" id="serviceDetailBody"></div>
        <div class="service-modal-footer"><button class="btn-service-cancel" onclick="closeServiceDetailModal()">Закрыть</button></div>
    </div>
</div>

<?php require_once '../footer.php'; ?>
</div>

<script>
const prices = <?= json_encode(array_column($cartItems, 'price', 'config_key'), JSON_UNESCAPED_UNICODE) ?>;
let servicesTotal = <?= $servicesTotal ?>;
let selectedServicesCount = <?= $selectedServicesCount ?>;
let currentDeliveryMethod = 'pickup';
const userAddress = <?= $userAddressJson ?>;

function autoFillAddressFromProfile() {
    if (userAddress.city) {
        const cityField = document.getElementById('addr-city');
        const cityFieldPost = document.getElementById('addr-city-post');
        if (cityField) cityField.value = userAddress.city;
        if (cityFieldPost) cityFieldPost.value = userAddress.city;
    }
    if (userAddress.street) {
        const streetField = document.getElementById('addr-street');
        const streetFieldPost = document.getElementById('addr-street-post');
        if (streetField) streetField.value = userAddress.street;
        if (streetFieldPost) streetFieldPost.value = userAddress.street;
    }
    if (userAddress.house) {
        const houseField = document.getElementById('addr-house');
        const houseFieldPost = document.getElementById('addr-house-post');
        if (houseField) houseField.value = userAddress.house;
        if (houseFieldPost) houseFieldPost.value = userAddress.house;
    }
}

function formatPrice(amount) {
    return amount.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽';
}

function recalculateTotals() {
    let selectedCount = 0, productsTotal = 0, totalQty = 0;
    document.querySelectorAll('.cart-item').forEach(item => {
        const configKey = item.dataset.configKey;
        const checkbox = item.querySelector('.item-select');
        const isSelected = checkbox.checked;
        const qtyInput = item.querySelector('.qty-input');
        const quantity = parseInt(qtyInput.value) || 1;
        const price = prices[configKey] || 0;
        
        const stockSpan = item.querySelector('.stock-warning.exceeds');
        let stock = 0;
        if (stockSpan) {
            const stockMatch = stockSpan.innerText.match(/В наличии только (\d+) шт/);
            if (stockMatch) stock = parseInt(stockMatch[1]);
        }
        
        const payableQuantity = (stock > 0 && quantity > stock) ? stock : quantity;
        const subtotal = price * payableQuantity;
        
        const subtotalEl = item.querySelector('.cart-subtotal-display span');
        subtotalEl.textContent = formatPrice(subtotal);
        subtotalEl.dataset.subtotal = subtotal;
        
        totalQty += quantity;
        if (isSelected) {
            selectedCount++;
            productsTotal += subtotal;
        }
        item.classList.toggle('selected', isSelected);
    });
    
    const totalAmount = productsTotal + servicesTotal;
    const selectedCountSpan = document.getElementById('selected-count');
    const totalQtySpan = document.getElementById('total-quantity');
    const productsTotalSpan = document.getElementById('products-total');
    const totalDisplaySpan = document.getElementById('total-display');
    const servicesTotalSpan = document.getElementById('services-total');
    const servicesCountSpan = document.getElementById('selected-services-count');
    const servicesRow = document.getElementById('services-summary-row');
    
    if (selectedCountSpan) selectedCountSpan.textContent = selectedCount;
    if (totalQtySpan) totalQtySpan.textContent = totalQty;
    if (productsTotalSpan) productsTotalSpan.textContent = formatPrice(productsTotal);
    if (totalDisplaySpan) totalDisplaySpan.textContent = formatPrice(totalAmount);
    
    if (servicesTotalSpan) servicesTotalSpan.textContent = formatPrice(servicesTotal);
    if (servicesCountSpan) servicesCountSpan.textContent = selectedServicesCount;
    if (servicesRow) {
        if (selectedServicesCount > 0) {
            servicesRow.style.display = 'flex';
        } else {
            servicesRow.style.display = 'none';
        }
    }
}

function clearAddressErrors() {
    const errorElements = document.querySelectorAll('.error-message');
    errorElements.forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    const inputFields = document.querySelectorAll('.input-field');
    inputFields.forEach(el => el.classList.remove('error'));
}

function showFieldError(fieldId, message) {
    const errorDiv = document.getElementById(fieldId);
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
    const inputField = document.querySelector(`#${fieldId.replace('error-', '')}`);
    if (inputField) inputField.classList.add('error');
}

function validateAddressFields() {
    clearAddressErrors();
    let isValid = true;
    
    if (currentDeliveryMethod === 'post') {
        const zip = document.getElementById('addr-zip')?.value.trim() || '';
        if (!zip) {
            showFieldError('error-zip', 'Индекс обязателен для заполнения');
            isValid = false;
        } else if (!/^\d{6}$/.test(zip)) {
            showFieldError('error-zip', 'Индекс должен состоять из 6 цифр');
            isValid = false;
        }
        
        const city = document.getElementById('addr-city-post')?.value.trim() || '';
        if (!city) {
            showFieldError('error-city-post', 'Город обязателен для заполнения');
            isValid = false;
        } else if (city.length < 2) {
            showFieldError('error-city-post', 'Город должен содержать минимум 2 символа');
            isValid = false;
        } else if (!/^[а-яА-ЯёЁa-zA-Z\s\-]+$/.test(city)) {
            showFieldError('error-city-post', 'Город: только буквы, пробелы и дефисы');
            isValid = false;
        }
        
        const street = document.getElementById('addr-street-post')?.value.trim() || '';
        if (!street) {
            showFieldError('error-street-post', 'Улица обязательна для заполнения');
            isValid = false;
        } else if (street.length < 2) {
            showFieldError('error-street-post', 'Улица должна содержать минимум 2 символа');
            isValid = false;
        }
        
        const house = document.getElementById('addr-house-post')?.value.trim() || '';
        if (!house) {
            showFieldError('error-house-post', 'Дом/Квартира обязателен для заполнения');
            isValid = false;
        }
    } else if (currentDeliveryMethod === 'city') {
        const city = document.getElementById('addr-city')?.value.trim() || '';
        if (!city) {
            showFieldError('error-city', 'Город обязателен для заполнения');
            isValid = false;
        } else if (city.length < 2) {
            showFieldError('error-city', 'Город должен содержать минимум 2 символа');
            isValid = false;
        } else if (!/^[а-яА-ЯёЁa-zA-Z\s\-]+$/.test(city)) {
            showFieldError('error-city', 'Город: только буквы, пробелы и дефисы');
            isValid = false;
        }
        
        const street = document.getElementById('addr-street')?.value.trim() || '';
        if (!street) {
            showFieldError('error-street', 'Улица обязательна для заполнения');
            isValid = false;
        } else if (street.length < 2) {
            showFieldError('error-street', 'Улица должна содержать минимум 2 символа');
            isValid = false;
        }
        
        const house = document.getElementById('addr-house')?.value.trim() || '';
        if (!house) {
            showFieldError('error-house', 'Дом/Квартира обязателен для заполнения');
            isValid = false;
        }
    }
    
    return isValid;
}

async function addToWaitingList(configKey, quantity) {
    if (!confirm(`Добавить товар (${quantity} шт.) в лист ожидания?\nМы уведомим вас, когда товар появится на складе.`)) return;
    const formData = new FormData();
    formData.append('action', 'add_to_waiting_list');
    formData.append('config_key', configKey);
    formData.append('quantity', quantity);
    try {
        const res = await fetch('cart.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) { alert(data.message); location.reload(); }
        else alert('Ошибка: ' + data.error);
    } catch(err) { alert('Ошибка'); }
}

async function changeQty(configKey, delta) {
    const item = document.querySelector(`.cart-item[data-config-key="${configKey}"]`);
    if (!item) return;
    const input = item.querySelector('.qty-input');
    let newVal = (parseInt(input.value) || 1) + delta;
    newVal = Math.max(1, newVal);
    const formData = new FormData();
    formData.append('action', 'update_qty');
    formData.append('config_key', configKey);
    formData.append('quantity', newVal);
    try {
        const res = await fetch('cart.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) location.reload();
        else alert(data.error);
    } catch(err) { console.error(err); }
}

async function updateQty(configKey, quantity) {
    let newVal = Math.max(1, parseInt(quantity) || 1);
    const formData = new FormData();
    formData.append('action', 'update_qty');
    formData.append('config_key', configKey);
    formData.append('quantity', newVal);
    try {
        const res = await fetch('cart.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) location.reload();
        else alert(data.error);
    } catch(err) { console.error(err); }
}

function removeItem(configKey) {
    if (confirm('Удалить товар из корзины?')) window.location.href = `cart.php?action=remove&key=${encodeURIComponent(configKey)}`;
}

const deliveryTabs = document.querySelectorAll('.delivery-tab');
const deliveryPanels = document.querySelectorAll('.delivery-panel');

deliveryTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        const method = tab.dataset.method;
        currentDeliveryMethod = method;
        clearAddressErrors();
        
        deliveryTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        
        deliveryPanels.forEach(p => {
            p.classList.toggle('active', p.dataset.panel === method);
        });
    });
});

function getAddressValues() {
    if (currentDeliveryMethod === 'pickup') {
        return { zip: '', city: '', street: '', house: '' };
    } else if (currentDeliveryMethod === 'city') {
        return {
            zip: '',
            city: document.getElementById('addr-city')?.value.trim() || '',
            street: document.getElementById('addr-street')?.value.trim() || '',
            house: document.getElementById('addr-house')?.value.trim() || ''
        };
    } else {
        return {
            zip: document.getElementById('addr-zip')?.value.trim() || '',
            city: document.getElementById('addr-city-post')?.value.trim() || '',
            street: document.getElementById('addr-street-post')?.value.trim() || '',
            house: document.getElementById('addr-house-post')?.value.trim() || ''
        };
    }
}

async function toggleItemSelect(configKey, isSelected) {
    const formData = new FormData();
    formData.append('action', 'toggle_select'); 
    formData.append('config_key', configKey); 
    formData.append('selected', isSelected ? 'true' : 'false');
    try {
        const res = await fetch('cart.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) { 
            servicesTotal = data.services_total; 
            document.getElementById('products-total').textContent = formatPrice(data.products_total);
            document.getElementById('total-display').textContent = formatPrice(data.selected_total);
            recalculateTotals(); 
        }
    } catch(err) { console.error(err); }
}

async function toggleService(configKey, serviceId, isSelected) {
    const formData = new FormData();
    formData.append('action', 'toggle_service');
    formData.append('config_key', configKey);
    formData.append('service_id', serviceId);
    formData.append('selected', isSelected ? 'true' : 'false');
    try {
        const res = await fetch('cart.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            const cb = document.querySelector(`.service-select-checkbox[data-config-key="${configKey}"][data-service-id="${serviceId}"]`);
            if (cb) cb.checked = isSelected;
            servicesTotal = data.services_total;
            selectedServicesCount = Object.values(<?= json_encode(array_column($recommendedServices, 'services')) ?>).flat().filter(s => s.is_selected).length;
            document.getElementById('products-total').textContent = formatPrice(data.products_total);
            document.getElementById('total-display').textContent = formatPrice(data.selected_total);
            recalculateTotals();
        }
    } catch(err) { console.error(err); }
}

async function showServiceDetails(serviceId) {
    const modal = document.getElementById('serviceDetailModal'), modalBody = document.getElementById('serviceDetailBody');
    modalBody.innerHTML = '<div style="text-align:center;padding:20px;">Загрузка...</div>';
    modal.style.display = 'flex'; document.body.style.overflow = 'hidden';
    try {
        const res = await fetch(`cart.php?action=get_service_details&service_id=${serviceId}&t=${Date.now()}`);
        const data = await res.json();
        if (data.success && data.service) {
            const s = data.service;
            let durHtml = s.duration ? `<div class="service-info-block"><span class="service-info-label">Срок:</span><span class="service-info-value">${s.duration}</span></div>` : '';
            modalBody.innerHTML = `<div class="service-info-block"><span class="service-info-label">Услуга:</span><span class="service-info-value">${s.name}</span></div>
            <div class="service-info-block"><span class="service-info-label">Стоимость:</span><span class="service-info-value">${formatPrice(s.price)}</span></div>${durHtml}
            <div class="service-description"><strong>Описание:</strong><p style="margin-top:8px;line-height:1.6;">${(s.full_description || s.short_description || 'Описание отсутствует.').replace(/\n/g,'<br>')}</p></div>`;
        } else throw new Error(data.error || 'Ошибка загрузки');
    } catch(err) { modalBody.innerHTML = `<div style="text-align:center;padding:20px;color:#e53935;"><p>${err.message}</p></div>`; }
}

function closeServiceDetailModal() { document.getElementById('serviceDetailModal').style.display = 'none'; document.body.style.overflow = 'auto'; }

const selectAll = document.getElementById('selectAllCheckbox');
if (selectAll) {
    selectAll.checked = Array.from(document.querySelectorAll('.item-select')).every(cb => cb.checked);
    selectAll.addEventListener('change', e => { 
        document.querySelectorAll('.item-select').forEach(cb => { 
            if (cb.checked !== e.target.checked) { 
                cb.checked = e.target.checked; 
                toggleItemSelect(cb.dataset.configKey, e.target.checked); 
            } 
        }); 
    });
}

document.querySelectorAll('.item-select').forEach(cb => { 
    cb.addEventListener('change', function() { 
        toggleItemSelect(this.dataset.configKey, this.checked); 
        if (selectAll) selectAll.checked = Array.from(document.querySelectorAll('.item-select')).every(c => c.checked); 
    }); 
});

document.querySelectorAll('.service-select-checkbox').forEach(cb => { 
    cb.addEventListener('change', function() { 
        toggleService(this.dataset.configKey, parseInt(this.dataset.serviceId), this.checked); 
    }); 
});

async function checkout() {
    const method = currentDeliveryMethod;
    
    let hasFullyOutOfStockSelected = false;
    let outOfStockProducts = [];
    
    document.querySelectorAll('.item-select:checked').forEach(cb => {
        const cartItem = cb.closest('.cart-item');
        if (cartItem) {
            const outOfStockWarning = cartItem.querySelector('.stock-warning.out');
            if (outOfStockWarning) {
                const productName = cartItem.querySelector('.cart-name')?.innerText || 'Товар';
                outOfStockProducts.push(productName);
                hasFullyOutOfStockSelected = true;
            }
        }
    });
    
    if (hasFullyOutOfStockSelected) {
        alert(`Оформление заказа невозможно!\n\nСледующие товары полностью отсутствуют на складе:\n${outOfStockProducts.join('\n')}\n\nПожалуйста, уберите их из корзины или добавьте в лист ожидания.`);
        return;
    }
    
    let hasPayableItems = false;
    let hasAnySelectedItem = false;
    
    document.querySelectorAll('.item-select:checked').forEach(cb => {
        hasAnySelectedItem = true;
        const cartItem = cb.closest('.cart-item');
        if (cartItem) {
            const subtotalSpan = cartItem.querySelector('.cart-subtotal-display span');
            if (subtotalSpan) {
                const subtotalText = subtotalSpan.textContent;
                const subtotalValue = parseFloat(subtotalText.replace(/[^0-9,-]/g, '').replace(',', '.'));
                if (subtotalValue > 0) {
                    hasPayableItems = true;
                }
            }
        }
    });
    
    const hasSelectedServices = document.querySelectorAll('.service-select-checkbox:checked').length > 0;
    
    if (hasAnySelectedItem && !hasPayableItems && !hasSelectedServices) {
        alert('Нет товаров, доступных для оплаты.\n\nВыберите товары, которые есть в наличии, или добавьте их в лист ожидания.');
        return;
    }
    
    if (method !== 'pickup') {
        const isValid = validateAddressFields();
        if (!isValid) {
            alert('Пожалуйста, исправьте ошибки в адресных полях');
            return;
        }
    }
    
    const addr = getAddressValues();
    
    const btn = document.querySelector('.checkout-btn'), original = btn.textContent;
    btn.disabled = true; btn.textContent = 'Оформление...';
    document.querySelectorAll('.qty-btn, .cart-remove, .item-select, .service-select-checkbox').forEach(el => el.disabled = true);
    
    try {
        const formData = new FormData();
        formData.append('action', 'checkout'); 
        formData.append('delivery_method', method);
        formData.append('zip', addr.zip); 
        formData.append('city', addr.city); 
        formData.append('street', addr.street); 
        formData.append('house', addr.house);
        
        const res = await fetch('cart.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) { 
            alert('Заказ оформлен!'); 
            window.location.href = 'lk_user.php'; 
        } else {
            alert('Ошибка: ' + (data.error || 'Не удалось оформить заказ'));
        }
    } catch(err) { 
        console.error(err); 
        alert('Произошла ошибка'); 
    } finally { 
        btn.disabled = false; 
        btn.textContent = original; 
        document.querySelectorAll('.qty-btn, .cart-remove, .item-select, .service-select-checkbox').forEach(el => el.disabled = false); 
    }
}

document.addEventListener('DOMContentLoaded', function() {
    recalculateTotals();
    autoFillAddressFromProfile();
});
window.onclick = function(e) { if (e.target === document.getElementById('serviceDetailModal')) closeServiceDetailModal(); }
</script>
</body>
</html>