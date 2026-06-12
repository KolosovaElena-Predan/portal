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
    header('Location: ../authorization.php');
    exit;
}

require_once 'config.php';

// --- ФУНКЦИИ ПОМОЩНИКИ ---

function getStatusHistory($pdo, $requestId) {
    $stmt = $pdo->prepare("SELECT status, comment, created_at FROM request_status_history WHERE request_id = ? ORDER BY created_at ASC");
    $stmt->execute([$requestId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getChatMessages($pdo, $requestId) {
    $stmt = $pdo->prepare("
        SELECT rm.id, rm.sender_type, rm.message, rm.created_at,
               cf.id as file_id, cf.file_name, cf.file_url, cf.file_size
        FROM request_messages rm
        LEFT JOIN chat_files cf ON rm.id = cf.message_id
        WHERE rm.request_id = ?
        ORDER BY rm.created_at ASC
    ");
    $stmt->execute([$requestId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Группируем файлы по сообщениям
    $messages = [];
    foreach ($results as $row) {
        $msgId = $row['id'];
        if (!isset($messages[$msgId])) {
            $messages[$msgId] = [
                'id' => $row['id'],
                'sender_type' => $row['sender_type'],
                'message' => $row['message'],
                'created_at' => $row['created_at'],
                'files' => []
            ];
        }
        if ($row['file_id']) {
            $messages[$msgId]['files'][] = [
                'id' => $row['file_id'],
                'name' => $row['file_name'],
                'url' => $row['file_url'],
                'size' => $row['file_size']
            ];
        }
    }
    return array_values($messages);
}

// --- ЗАГРУЗКА ДАННЫХ ПОЛЬЗОВАТЕЛЯ И АДРЕСА ---

$stmtUser = $pdo->prepare("SELECT id, name, email, login, phone, address FROM user WHERE id = ?");
$stmtUser->execute([$user->id]);
$userDataDb = $stmtUser->fetch(PDO::FETCH_ASSOC);

$addressData = ['city' => '', 'street' => '', 'house' => ''];
if (!empty($userDataDb['address'])) {
    $decoded = json_decode($userDataDb['address'], true);
    if (is_array($decoded)) {
        $addressData = array_merge($addressData, $decoded);
    } else {
        $addressData['city'] = $userDataDb['address']; 
    }
}

$user_data = [
    'id' => $userDataDb['id'], 
    'name' => $userDataDb['name'], 
    'email' => $userDataDb['email'],
    'login' => $userDataDb['login'],
    'phone' => $userDataDb['phone'] ?? '',
    'address' => $addressData
];

// --- ЗАГРУЗКА ЗАКАЗОВ (оборудование и услуги) ---
$stmt = $pdo->prepare("
SELECT
    r.id, r.datetime, r.status, r.type, r.message, r.product_id,
    p.name AS product_name, p.base_price AS product_price,
    (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS product_img,
    s.name AS service_name, s.price AS service_price, s.img_url AS service_img
FROM request r
LEFT JOIN products p ON r.product_id = p.id AND r.type = 'r'
LEFT JOIN services s ON (r.type = 's' AND JSON_UNQUOTE(JSON_EXTRACT(r.message, '$.service_id')) = s.id)
WHERE r.user_id = ? AND r.type IN ('r', 's')
ORDER BY r.datetime DESC
");
$stmt->execute([$user->id]);
$raw_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$products = [];
$servicesByProductId = []; 

foreach ($raw_requests as $req) {
    if ($req['type'] === 'r') {
        $msgData = json_decode($req['message'], true);
        $price = isset($msgData['line_total']) ? (float)$msgData['line_total'] : ((float)($req['product_price'] ?? 0) * ((int)($msgData['quantity'] ?? 1)));
        
        $products[$req['id']] = [
            'id' => $req['id'], 'datetime' => $req['datetime'], 'status' => $req['status'],
            'type' => 'product', 'product_id' => $req['product_id'],
            'name' => $req['product_name'] ?? 'Товар', 'price' => $price,
            'img' => $req['product_img'] ?: 'img/placeholder.jpg',
            'message' => $msgData,
            'quantity' => (int)($msgData['quantity'] ?? 1),
            'configuration_name' => $msgData['configuration_name'] ?? '',
            'modifications' => $msgData['modifications'] ?? [],
            'address' => $msgData['address'] ?? '',
            'status_history' => getStatusHistory($pdo, $req['id']),
            'chat_messages' => getChatMessages($pdo, $req['id'])
        ];
    } else {
        $serviceDetails = json_decode($req['message'], true);
        $service = [
            'id' => $req['id'], 'datetime' => $req['datetime'], 'status' => $req['status'],
            'type' => 'service', 'name' => $req['service_name'] ?? 'Услуга',
            'price' => (float)($req['service_price'] ?? ($serviceDetails['price'] ?? 0)),
            'img' => $req['service_img'] ?: null,
            'linked_product_id' => $serviceDetails['product_id'] ?? null,
            'status_history' => getStatusHistory($pdo, $req['id']),
            'chat_messages' => getChatMessages($pdo, $req['id'])
        ];
        $linkedProductId = $service['linked_product_id'] ?? 'orphan';
        if (!isset($servicesByProductId[$linkedProductId])) $servicesByProductId[$linkedProductId] = [];
        $servicesByProductId[$linkedProductId][] = $service;
    }
}

// Формируем заказы (группируем товары с услугами)
$orders = [];
$usedServiceIds = [];

foreach ($products as $productId => $product) {
    $order = [
        'id' => $product['id'], 'datetime' => $product['datetime'], 'status' => $product['status'],
        'items' => [], 'total_price' => 0,
        'status_history' => $product['status_history'], 'chat_messages' => $product['chat_messages'],
        'address' => $product['address']
    ];
    $order['items'][] = ['type' => 'product', 'data' => $product];
    $order['total_price'] += $product['price'];
    
    if (isset($servicesByProductId[$product['product_id']])) {
        foreach ($servicesByProductId[$product['product_id']] as $service) {
            $order['items'][] = ['type' => 'service', 'data' => $service];
            $order['total_price'] += $service['price'];
            $usedServiceIds[] = $service['id'];
        }
        unset($servicesByProductId[$product['product_id']]);
    }
    $orders[] = $order;
}

foreach ($servicesByProductId as $groupId => $servicesList) {
    foreach ($servicesList as $service) {
        if (in_array($service['id'], $usedServiceIds)) continue;
        $orders[] = [
            'id' => $service['id'], 'datetime' => $service['datetime'], 'status' => $service['status'],
            'items' => [['type' => 'service', 'data' => $service]],
            'total_price' => $service['price'],
            'status_history' => $service['status_history'], 'chat_messages' => $service['chat_messages'],
            'address' => ''
        ];
    }
}

// Сортируем заказы по дате
usort($orders, function($a, $b) { return strtotime($b['datetime']) - strtotime($a['datetime']); });

// Разделяем заказы на активные и завершенные
$activeOrders = [];
$completedOrders = [];

foreach ($orders as $order) {
    if ($order['status'] === 'closed' || $order['status'] === 'cancelled') {
        $completedOrders[] = $order;
    } else {
        $activeOrders[] = $order;
    }
}

// Получаем количество товаров в листе ожидания
$waitingListCount = 0;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM request WHERE user_id = ? AND type = 'wl' AND status = 'waiting'");
$stmt->execute([$user->id]);
$waitingListCount = $stmt->fetchColumn();

$statusLabels = ['new' => 'Оформление', 'processed' => 'В обработке', 'closed' => 'Завершён', 'cancelled' => 'Отменён', 'waiting' => 'Ожидание'];
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
<link rel="stylesheet" href="css/style_lk.css" />
<style>
/* Дополнительные стили (бирюзовая гамма) */
* {
    font-family: 'Inter', sans-serif;
}

/* Отступы - увеличены слева и справа, уменьшен сверху */
.lk-content {
    margin-top: 40px;
    padding-left: 80px;
    padding-right: 80px;
    width: 100%;
    max-width: 1600px;
    margin-left: auto;
    margin-right: auto;
    box-sizing: border-box;
}

.completed-section {
    margin-top: 40px;
}
.completed-section .type-title {
    color: #4a6a65;
}
.completed-orders-note {
    font-size: 15px;
    font-weight: 400;
    color: #4a6a65;
    margin-bottom: 20px;
    padding: 12px 16px;
    background: #f0f6f4;
    border-radius: 8px;
    text-align: center;
}
.order-card {
    background: #ffffff;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(0, 168, 150, 0.08);
    overflow: hidden;
    border: 1px solid #e0e8e5;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.order-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 168, 150, 0.15);
    border-color: #00a896;
}
.order-header {
    background: #ffffff;
    padding: 18px 24px;
    border-bottom: 1px solid #e0e8e5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
.order-id {
    font-size: 16px;
    font-weight: 500;
    color: #4a6a65;
}
.order-id strong {
    font-weight: 700;
    color: #00302e;
    font-size: 17px;
}
.order-date {
    font-size: 16px;
    font-weight: 400;
    color: #4a6a65;
    margin-left: auto;
}
.order-status {
    display: flex;
    align-items: center;
    gap: 10px;
}
.order-items {
    padding: 18px 24px;
    background: #ffffff;
}
.order-item {
    display: flex;
    gap: 18px;
    padding: 16px 0;
    border-bottom: 1px solid #f0f6f4;
}
.order-item:last-child {
    border-bottom: none;
}
.order-item-img {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border-radius: 12px;
    background: #f0f6f4;
}
.order-item-details {
    flex: 1;
}
.order-item-name {
    font-weight: 600;
    font-size: 18px;
    color: #00302e;
    margin-bottom: 8px;
}
.order-item-meta {
    font-size: 15px;
    font-weight: 400;
    color: #4a6a65;
    margin-bottom: 6px;
}
.order-item-price {
    font-size: 17px;
    font-weight: 700;
    color: #00a896;
}
.order-footer {
    padding: 16px 24px;
    background: #ffffff;
    border-top: 1px solid #e0e8e5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
.order-total {
    font-size: 19px;
    font-weight: 700;
    color: #00302e;
}
.order-actions {
    display: flex;
    gap: 12px;
}
.status-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
}
.status-new { background: #fff3cd; color: #856404; }
.status-processed { background: #cce5ff; color: #004085; }
.status-closed { background: #d4edda; color: #155724; }
.status-cancelled { background: #f8d7da; color: #721c24; }
.status-waiting { background: #fff8e1; color: #e65100; }

/* Кнопки истории и чата - более насыщенные */
.btn-detail {
    background: #e8f4f1;
    border: 1px solid #00a896;
    color: #00a896;
    padding: 9px 18px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.btn-detail:hover {
    background: #00a896;
    color: #fff;
}

.modal-overlay {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 48, 46, 0.8);
    justify-content: center;
    align-items: center;
}
.modal-overlay.active {
    display: flex;
}
.modal-window {
    background: #fff;
    border-radius: 20px;
    max-width: 650px;
    width: 90%;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    margin-top: 0;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 2px solid #e0e8e5;
}
.modal-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #00302e;
}
.modal-close {
    background: none;
    border: none;
    font-size: 26px;
    cursor: pointer;
    color: #4a6a65;
}
.modal-close:hover {
    color: #00a896;
}
.modal-body {
    padding: 24px;
}
.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #e0e8e5;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}
.btn-cancel, .btn-save {
    padding: 10px 24px;
    border-radius: 8px;
    cursor: pointer;
    border: none;
    font-size: 14px;
    font-weight: 500;
}
.btn-cancel {
    background: #e0e8e5;
    color: #4a6a65;
}
.btn-cancel:hover {
    background: #d0ddd9;
    color: #00302e;
}
.btn-save {
    background: #00a896;
    color: #fff;
}
.btn-save:hover {
    background: #008a7a;
}

.status-timeline-modal {
    padding: 10px;
    position: relative;
    padding-left: 30px;
}
.status-timeline-modal::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e8e5;
}
.status-timeline-item {
    position: relative;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px dashed #e0e8e5;
}
.status-timeline-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}
.status-timeline-item::before {
    content: '';
    position: absolute;
    left: -26px;
    top: 4px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #00a896;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #e0e8e5;
}
.status-timeline-item.completed::before {
    background: #28a745;
}
.status-timeline-item.current::before {
    background: #00a896;
    box-shadow: 0 0 0 2px #00a896;
}
.status-timeline-date {
    font-size: 13px;
    font-weight: 400;
    color: #4a6a65;
    margin-bottom: 5px;
}
.status-timeline-text {
    font-size: 16px;
    font-weight: 600;
    color: #00302e;
}
.status-timeline-comment {
    font-size: 14px;
    font-weight: 400;
    color: #4a6a65;
    margin-top: 6px;
    padding: 10px 14px;
    background: #f0f6f4;
    border-radius: 8px;
}

/* Стили для чата с файлами */
.chat-messages-modal {
    max-height: 400px;
    overflow-y: auto;
    margin-bottom: 20px;
    background: #fafafc;
    border-radius: 12px;
    border: 1px solid #e0e8e5;
    padding: 12px;
}
.chat-message {
    padding: 12px 18px;
    margin-bottom: 14px;
    border-radius: 14px;
    font-size: 15px;
    font-weight: 400;
    line-height: 1.5;
}
.chat-message-user {
    background: #d4f5f0;
    color: #00302e;
    text-align: right;
    margin-left: auto;
    border-bottom-right-radius: 4px;
}
.chat-message-support {
    background: #f0f6f4;
    color: #00302e;
    margin-right: auto;
    border-bottom-left-radius: 4px;
}
.chat-message-author {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #00a896;
}
.chat-message-text {
    word-wrap: break-word;
    margin-bottom: 6px;
}
.chat-message-time {
    font-size: 11px;
    font-weight: 400;
    color: #4a6a65;
    margin-top: 6px;
}
.chat-message-files {
    margin-top: 8px;
    padding-top: 6px;
    border-top: 1px solid rgba(0,0,0,0.05);
}
.chat-file-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(0,168,150,0.1);
    padding: 4px 10px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 12px;
    color: #00a896;
    margin-right: 8px;
    margin-bottom: 5px;
    transition: all 0.3s ease;
}
.chat-file-link:hover {
    background: #00a896;
    color: #fff;
}
.chat-file-link i {
    font-size: 12px;
}
.chat-reply-form-modal {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.chat-input-area {
    display: flex;
    gap: 10px;
    align-items: flex-start;
}
.chat-reply-input-modal {
    flex: 1;
    padding: 12px 14px;
    border: 2px solid #e0e8e5;
    border-radius: 12px;
    resize: vertical;
    font-size: 15px;
    font-weight: 400;
    font-family: 'Inter', sans-serif;
}
.chat-reply-input-modal:focus {
    border-color: #00a896;
    outline: none;
}
.chat-reply-btn-modal {
    background: #00a896;
    color: #fff;
    border: none;
    padding: 12px 22px;
    border-radius: 12px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
    white-space: nowrap;
}
.chat-reply-btn-modal:hover {
    background: #008a7a;
}
.chat-file-attach {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.chat-file-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f0f6f4;
    border: 1px solid #e0e8e5;
    color: #4a6a65;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s ease;
}
.chat-file-label:hover {
    background: #e0e8e5;
    color: #00302e;
}
.chat-file-input {
    display: none;
}
.selected-file-name {
    font-size: 12px;
    color: #4a6a65;
    background: #f0f6f4;
    padding: 6px 12px;
    border-radius: 6px;
}
.upload-progress {
    font-size: 12px;
    color: #00a896;
}

.personal-data {
    background: #ffffff;
    padding: 24px;
    border-radius: 16px;
    margin-bottom: 20px;
    border: 1px solid #e0e8e5;
    box-shadow: 0 4px 12px rgba(0, 168, 150, 0.08);
}
.personal-data h3 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 22px;
    font-weight: 600;
    color: #00302e;
}
.personal-data p {
    margin: 14px 0;
    font-size: 16px;
    font-weight: 400;
    color: #4a6a65;
}
.personal-data p strong {
    font-weight: 600;
    color: #00a896;
}
.edit-profile-btn, .cart-link, .waiting-link {
    display: block;
    width: 100%;
    margin-top: 12px;
    padding: 14px 16px;
    background: #00a896;
    color: #fff;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    text-decoration: none;
    font-size: 16px;
    font-weight: 500;
    text-align: center;
    box-sizing: border-box;
}
.edit-profile-btn:hover, .cart-link:hover {
    background: #008a7a;
}
.waiting-link {
    background: #17a2b8;
}
.waiting-link:hover {
    background: #138496;
}
.logout-link {
    background: #dc3545;
}
.logout-link:hover {
    background: #b02a37;
}

.profile-form .form-group {
    margin-bottom: 18px;
}
.profile-form .form-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 15px;
    font-weight: 500;
    color: #00302e;
}
.profile-form .form-group input, .profile-form .form-group textarea {
    width: 100%;
    padding: 12px 14px;
    border: 2px solid #e0e8e5;
    border-radius: 10px;
    box-sizing: border-box;
    font-size: 15px;
    font-weight: 400;
    font-family: 'Inter', sans-serif;
}
.profile-form .form-group input:focus {
    border-color: #00a896;
    outline: none;
}
.profile-form .form-group small {
    font-size: 12px;
    font-weight: 400;
    color: #4a6a65;
}
.address-row {
    display: flex;
    gap: 12px;
}
.address-row .form-group {
    flex: 1;
}

.lk-title {
    font-size: 36px;
    font-weight: 700;
    color: #00302e;
    margin-bottom: 40px;
}
.type-title {
    font-size: 26px;
    font-weight: 700;
    color: #00302e;
    margin: 30px 0 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e0e8e5;
}
.no-orders {
    font-size: 16px;
    font-weight: 400;
    color: #4a6a65;
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e0e8e5;
}

/* Адаптивность */
@media (max-width: 1200px) {
    .lk-content {
        padding-left: 60px;
        padding-right: 60px;
    }
}

@media (max-width: 900px) {
    .lk-content {
        padding-left: 40px;
        padding-right: 40px;
    }
}

@media (max-width: 600px) {
    .chat-reply-input-modal {
        max-width: 100%;
    }
    .lk-content {
        margin-top: 30px;
        padding-left: 20px;
        padding-right: 20px;
    }
    .lk-title {
        font-size: 28px;
    }
    .type-title {
        font-size: 22px;
    }
    .order-item-name {
        font-size: 16px;
    }
    .order-item-price {
        font-size: 15px;
    }
    .chat-input-area {
        flex-direction: column;
    }
    .chat-reply-btn-modal {
        width: 100%;
    }
}
</style>
<title>Личный кабинет</title>
</head>
<body>

<div class="screen">
<div class="div">
<?php $context = 'mip'; require_once '../header.php'; ?>
<div class="lk-content">
<h1 class="lk-title">Личный кабинет</h1>

<div class="lk-main-content">
<div class="device-list-wrapper">

<!-- АКТИВНЫЕ ЗАКАЗЫ -->
<?php if (empty($activeOrders) && empty($completedOrders)): ?>
<div class="no-orders">У вас пока нет заказов</div>
<?php else: ?>
    
    <?php if (!empty($activeOrders)): ?>
        <?php foreach ($activeOrders as $order): ?>
        <div class="order-card">
            <div class="order-header">
                <div class="order-id"><strong>Заказ №<?= $order['id'] ?></strong></div>
                <div class="order-date"><?= date('d.m.Y H:i', strtotime($order['datetime'])) ?></div>
                <div class="order-status">
                    <span class="status-badge status-<?= $order['status'] ?>"><?= $statusLabels[$order['status']] ?? $order['status'] ?></span>
                </div>
            </div>
            
            <div class="order-items">
                <?php foreach ($order['items'] as $item): ?>
                    <?php if ($item['type'] === 'product'): ?>
                        <?php $product = $item['data']; ?>
                        <div class="order-item">
                            <img src="<?= htmlspecialchars($product['img']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="order-item-img" onerror="this.src='img/placeholder.jpg'">
                            <div class="order-item-details">
                                <div class="order-item-name"><?= htmlspecialchars($product['name']) ?></div>
                                <?php if ($product['quantity'] > 1): ?><div class="order-item-meta">Количество: <?= $product['quantity'] ?> шт.</div><?php endif; ?>
                                <?php if (!empty($product['configuration_name'])): ?><div class="order-item-meta">Комплектация: <?= htmlspecialchars($product['configuration_name']) ?></div><?php endif; ?>
                                <div class="order-item-price"><?= number_format($product['price'], 2, ',', ' ') ?> ₽</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php $service = $item['data']; ?>
                        <div class="order-item">
                            <div class="order-item-details">
                                <div class="order-item-name"><?= htmlspecialchars($service['name']) ?></div>
                                <div class="order-item-price"><?= number_format($service['price'], 2, ',', ' ') ?> ₽</div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (!empty($order['address'])): ?>
                <div class="order-item-meta" style="margin-top: 12px; padding-top: 12px;">
                    <strong>Адрес доставки:</strong> <?= htmlspecialchars($order['address']) ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="order-footer">
                <div class="order-total">Итого: <?= number_format($order['total_price'], 2, ',', ' ') ?> ₽</div>
                <div class="order-actions">
                    <button class="btn-detail" onclick="openStatusModal(<?= $order['id'] ?>)">История</button>
                    <button class="btn-detail" onclick="openChatModal(<?= $order['id'] ?>)">Чат</button>
                    <?php 
                    $firstProduct = null;
                    foreach ($order['items'] as $item) { if ($item['type'] === 'product') { $firstProduct = $item['data']; break; } }
                    if ($firstProduct): ?>
                    <a href="product.php?id=<?= $firstProduct['product_id'] ?>" class="btn-detail">Подробнее</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ЗАВЕРШЕННЫЕ ЗАКАЗЫ (внизу) -->
    <?php if (!empty($completedOrders)): ?>
    <div class="completed-section">
        <h3 class="type-title">Завершённые заказы</h3>
        <div class="completed-orders-note">
            Здесь отображаются доставленные и отменённые заказы
        </div>
        <?php foreach ($completedOrders as $order): ?>
        <div class="order-card">
            <div class="order-header">
                <div class="order-id"><strong>Заказ №<?= $order['id'] ?></strong></div>
                <div class="order-date"><?= date('d.m.Y H:i', strtotime($order['datetime'])) ?></div>
                <div class="order-status">
                    <span class="status-badge status-<?= $order['status'] ?>"><?= $statusLabels[$order['status']] ?? $order['status'] ?></span>
                </div>
            </div>
            
            <div class="order-items">
                <?php foreach ($order['items'] as $item): ?>
                    <?php if ($item['type'] === 'product'): ?>
                        <?php $product = $item['data']; ?>
                        <div class="order-item">
                            <img src="<?= htmlspecialchars($product['img']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="order-item-img" onerror="this.src='img/placeholder.jpg'">
                            <div class="order-item-details">
                                <div class="order-item-name"><?= htmlspecialchars($product['name']) ?></div>
                                <?php if ($product['quantity'] > 1): ?><div class="order-item-meta">Количество: <?= $product['quantity'] ?> шт.</div><?php endif; ?>
                                <div class="order-item-price"><?= number_format($product['price'], 2, ',', ' ') ?> ₽</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php $service = $item['data']; ?>
                        <div class="order-item">
                            <div class="order-item-details">
                                <div class="order-item-name"><?= htmlspecialchars($service['name']) ?></div>
                                <div class="order-item-price"><?= number_format($service['price'], 2, ',', ' ') ?> ₽</div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <div class="order-footer">
                <div class="order-total">Итого: <?= number_format($order['total_price'], 2, ',', ' ') ?> ₽</div>
                <div class="order-actions">
                    <button class="btn-detail" onclick="openStatusModal(<?= $order['id'] ?>)">История</button>
                    <button class="btn-detail" onclick="openChatModal(<?= $order['id'] ?>)">Чат</button>
                    <a href="catalog.php" class="btn-detail">В каталог</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

<?php endif; ?>

</div>

<div class="personal-data">
    <h3>Личные данные</h3>
    <p><strong>ФИО:</strong> <?= htmlspecialchars($user_data['name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user_data['email']) ?></p>
    <p><strong>Телефон:</strong> <?= htmlspecialchars($user_data['phone']) ?: '<span style="color:#999">Не указан</span>' ?></p>
    <p><strong>Адрес доставки:</strong> 
        <?php 
        $addrParts = [];
        if(!empty($user_data['address']['city'])) $addrParts[] = $user_data['address']['city'];
        if(!empty($user_data['address']['street'])) $addrParts[] = $user_data['address']['street'];
        if(!empty($user_data['address']['house'])) $addrParts[] = $user_data['address']['house'];
        echo !empty($addrParts) ? htmlspecialchars(implode(', ', $addrParts)) : '<span style="color:#999">Не указан</span>';
        ?>
    </p>
    
    <button class="edit-profile-btn" onclick="openEditProfileModal()">Редактировать профиль</button>
    <a href="cart.php" class="cart-link">Перейти в корзину</a>
    <a href="waiting_list.php" class="waiting-link">Лист ожидания</a>
    <a href="logout.php" class="cart-link logout-link">Выйти</a>
</div>
</div>
</div>
<?php require_once '../footer.php'; ?>
</div>
</div>

<!-- МОДАЛЬНОЕ ОКНО РЕДАКТИРОВАНИЯ ПРОФИЛЯ -->
<div id="editProfileModal" class="modal-overlay" onclick="closeModalIfClickOutside(event, 'editProfileModal')">
    <div class="modal-window">
        <div class="modal-header">
            <h3>Редактирование профиля</h3>
            <button class="modal-close" onclick="closeModal('editProfileModal')">&times;</button>
        </div>
        <form class="modal-body profile-form" id="profileForm" onsubmit="saveProfile(event)">
            <div class="form-group">
                <label>ФИО</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user_data['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>
            </div>
            <div class="form-group">
                <label>Телефон</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($user_data['phone']) ?>" placeholder="+7 (999) 000-00-00">
            </div>
            
            <div class="form-group">
                <label>Адрес доставки</label>
                <div class="address-row">
                    <div class="form-group" style="flex: 2;">
                        <input type="text" name="address_city" placeholder="Город" value="<?= htmlspecialchars($user_data['address']['city']) ?>">
                    </div>
                </div>
                <div class="address-row" style="margin-top: 10px;">
                    <div class="form-group" style="flex: 3;">
                        <input type="text" name="address_street" placeholder="Улица" value="<?= htmlspecialchars($user_data['address']['street']) ?>">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <input type="text" name="address_house" placeholder="Дом/Кв" value="<?= htmlspecialchars($user_data['address']['house']) ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="login" value="<?= htmlspecialchars($user_data['login']) ?>" disabled>
                <small>Логин нельзя изменить</small>
            </div>
            <div class="form-group">
                <label>Новый пароль</label>
                <input type="password" name="new_password" placeholder="Оставьте пустым, чтобы не менять">
            </div>
            <div class="form-group">
                <label>Подтверждение пароля</label>
                <input type="password" name="confirm_password" placeholder="Повторите новый пароль">
            </div>
        </form>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal('editProfileModal')">Отмена</button>
            <button class="btn-save" onclick="document.getElementById('profileForm').requestSubmit()">Сохранить</button>
        </div>
    </div>
</div>

<!-- МОДАЛЬНЫЕ ОКНА ИСТОРИИ И ЧАТА -->
<div id="statusModal" class="modal-overlay" onclick="closeModalIfClickOutside(event, 'statusModal')">
    <div class="modal-window">
        <div class="modal-header"><h3>История статусов</h3><button class="modal-close" onclick="closeModal('statusModal')">&times;</button></div>
        <div class="modal-body" id="statusModalBody"></div>
        <div class="modal-footer"><button class="btn-cancel" onclick="closeModal('statusModal')">Закрыть</button></div>
    </div>
</div>

<div id="chatModal" class="modal-overlay" onclick="closeModalIfClickOutside(event, 'chatModal')">
    <div class="modal-window">
        <div class="modal-header"><h3>Чат с поддержкой</h3><button class="modal-close" onclick="closeModal('chatModal')">&times;</button></div>
        <div class="modal-body" id="chatModalBody">
            <div class="chat-messages-modal" id="chatMessagesModal"></div>
            <form class="chat-reply-form-modal" id="chatReplyFormModal" data-request-id="" enctype="multipart/form-data">
                <div class="chat-input-area">
                    <textarea class="chat-reply-input-modal" id="chatMessageInput" placeholder="Напишите сообщение..." rows="2"></textarea>
                    <button type="submit" class="chat-reply-btn-modal">Отправить</button>
                </div>
                <div class="chat-file-attach">
                    <label class="chat-file-label">
                        <i class="fas fa-paperclip"></i> Прикрепить файл
                        <input type="file" class="chat-file-input" id="chatFileInput" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt">
                    </label>
                    <span class="selected-file-name" id="selectedFileName"></span>
                    <span class="upload-progress" id="uploadProgress"></span>
                </div>
            </form>
        </div>
        <div class="modal-footer"><button class="btn-cancel" onclick="closeModal('chatModal')">Закрыть</button></div>
    </div>
</div>

<script>
let currentChatRequestId = null;
let currentFile = null;

function openStatusModal(requestId) {
    fetch('get_status_history.php?request_id=' + requestId)
        .then(res => res.json())
        .then(data => {
            const modalBody = document.getElementById('statusModalBody');
            if (data.success && data.history && data.history.length > 0) {
                let html = '<div class="status-timeline-modal">';
                data.history.forEach((item, index) => {
                    const isLast = index === data.history.length - 1;
                    html += `<div class="status-timeline-item ${isLast ? 'current' : 'completed'}">
                        <div class="status-timeline-date">${item.created_at}</div>
                        <div class="status-timeline-text">${item.status_text}</div>
                        ${item.comment ? `<div class="status-timeline-comment">${escapeHtml(item.comment)}</div>` : ''}
                    </div>`;
                });
                html += '</div>';
                modalBody.innerHTML = html;
            } else {
                modalBody.innerHTML = '<p>История пуста</p>';
            }
            document.getElementById('statusModal').classList.add('active');
        });
}

function openChatModal(requestId) {
    currentChatRequestId = requestId;
    document.getElementById('chatReplyFormModal').dataset.requestId = requestId;
    loadChatMessages(requestId);
    document.getElementById('chatModal').classList.add('active');
}

function loadChatMessages(requestId) {
    fetch('get_chat_messages.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'request_id=' + requestId })
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('chatMessagesModal');
        if (data.success && data.messages && data.messages.length > 0) {
            container.innerHTML = data.messages.map(msg => {
                let filesHtml = '';
                if (msg.files && msg.files.length > 0) {
                    filesHtml = '<div class="chat-message-files">';
                    msg.files.forEach(file => {
                        const fileSize = file.size ? (file.size / 1024).toFixed(1) + ' KB' : '';
                        filesHtml += `<a href="${file.url}" class="chat-file-link" target="_blank">
                            📎 ${escapeHtml(file.name)} ${fileSize ? '(' + fileSize + ')' : ''}
                        </a>`;
                    });
                    filesHtml += '</div>';
                }
                return `
                    <div class="chat-message ${msg.sender_type === 'user' ? 'chat-message-user' : 'chat-message-support'}">
                        <div class="chat-message-author">${msg.sender_type === 'user' ? 'Вы' : 'Поддержка'}</div>
                        ${msg.message ? `<div class="chat-message-text">${escapeHtml(msg.message).replace(/\n/g, '<br>')}</div>` : ''}
                        ${filesHtml}
                        <div class="chat-message-time">${msg.created_at}</div>
                    </div>`;
            }).join('');
        } else {
            container.innerHTML = '<p>Нет сообщений</p>';
        }
        container.scrollTop = container.scrollHeight;
    });
}

// Обработка выбора файла
document.getElementById('chatFileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const maxSize = 10 * 1024 * 1024; // 10 MB
        if (file.size > maxSize) {
            alert('Файл слишком большой. Максимальный размер 10 MB.');
            this.value = '';
            document.getElementById('selectedFileName').textContent = '';
            currentFile = null;
            return;
        }
        currentFile = file;
        document.getElementById('selectedFileName').textContent = file.name;
    } else {
        currentFile = null;
        document.getElementById('selectedFileName').textContent = '';
    }
});

// Отправка сообщения с файлом
document.getElementById('chatReplyFormModal').addEventListener('submit', async function(e) {
    e.preventDefault();
    const requestId = this.dataset.requestId;
    const message = document.getElementById('chatMessageInput').value.trim();
    
    if (!message && !currentFile) {
        alert('Введите сообщение или прикрепите файл');
        return;
    }
    
    const btn = this.querySelector('.chat-reply-btn-modal');
    const progressSpan = document.getElementById('uploadProgress');
    btn.disabled = true;
    btn.textContent = 'Отправка...';
    
    try {
        let messageId = null;
        
        // Сначала отправляем текстовое сообщение, если есть
        if (message) {
            const textFormData = new FormData();
            textFormData.append('action', 'add_message');
            textFormData.append('request_id', requestId);
            textFormData.append('message', message);
            
            const textRes = await fetch('add_chat_message.php', { method: 'POST', body: textFormData });
            const textData = await textRes.json();
            
            if (!textData.success) {
                throw new Error(textData.error || 'Не удалось отправить сообщение');
            }
            messageId = textData.message_id;
        }
        
        // Затем отправляем файл, если есть
        if (currentFile) {
            progressSpan.textContent = 'Загрузка файла...';
            
            const fileFormData = new FormData();
            fileFormData.append('action', 'upload_file');
            fileFormData.append('request_id', requestId);
            if (messageId) {
                fileFormData.append('message_id', messageId);
            }
            fileFormData.append('file', currentFile);
            
            const fileRes = await fetch('upload_chat_file.php', { method: 'POST', body: fileFormData });
            const fileData = await fileRes.json();
            
            if (!fileData.success) {
                throw new Error(fileData.error || 'Не удалось загрузить файл');
            }
            
            progressSpan.textContent = '';
            document.getElementById('chatFileInput').value = '';
            document.getElementById('selectedFileName').textContent = '';
            currentFile = null;
        }
        
        document.getElementById('chatMessageInput').value = '';
        loadChatMessages(requestId);
        
    } catch (err) {
        alert('Ошибка: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Отправить';
        progressSpan.textContent = '';
    }
});

function openEditProfileModal() {
    document.getElementById('editProfileModal').classList.add('active');
}

function saveProfile(event) {
    event.preventDefault();
    const form = document.getElementById('profileForm');
    const formData = new FormData(form);
    formData.append('action', 'update_profile');
    
    const city = formData.get('address_city');
    const street = formData.get('address_street');
    const house = formData.get('address_house');
    
    formData.delete('address_city');
    formData.delete('address_street');
    formData.delete('address_house');
    
    const addressObj = { city: city, street: street, house: house };
    formData.append('address_json', JSON.stringify(addressObj));
    
    const saveBtn = document.querySelector('#editProfileModal .btn-save');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Сохранение...';
    saveBtn.disabled = true;
    
    fetch('update_profile.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Профиль обновлён!');
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'Не удалось обновить'));
        }
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    })
    .catch(err => {
        console.error(err);
        alert('Ошибка соединения');
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

function closeModal(modalId) { document.getElementById(modalId).classList.remove('active'); }
function closeModalIfClickOutside(event, modalId) { if (event.target === document.getElementById(modalId)) closeModal(modalId); }
function escapeHtml(text) { 
    if (!text) return '';
    const div = document.createElement('div'); 
    div.textContent = text; 
    return div.innerHTML; 
}
</script>
<script>
// Проверяем, нужно ли открыть модальное окно после перехода из уведомлений
document.addEventListener('DOMContentLoaded', function() {
    const openModal = sessionStorage.getItem('openModal');
    const requestId = sessionStorage.getItem('modalRequestId');
    
    if (openModal && requestId) {
        sessionStorage.removeItem('openModal');
        sessionStorage.removeItem('modalRequestId');
        
        setTimeout(function() {
            if (openModal === 'chat') {
                if (typeof openChatModal === 'function') {
                    openChatModal(requestId);
                }
            } else if (openModal === 'status') {
                if (typeof openStatusModal === 'function') {
                    openStatusModal(requestId);
                }
            }
        }, 500);
    }
});
</script>
</body>
</html>