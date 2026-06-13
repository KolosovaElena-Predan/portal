<?php
session_start();
require_once 'config.php';
require_once 'includes/notifications.php';
require_once 'mip/includes/email_config.php';

// Устанавливаем контекст для шапки и подвала
$context = 'lab';

// Проверка авторизации
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'support_specialist') {
    header('Location: authorization.php');
    exit;
}
$currentUser = [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'] ?? 'Специалист',
    'role' => $_SESSION['role']
];

// ============================================
// ЗАГРУЗКА ДИНАМИЧЕСКИХ СТАТУСОВ ИЗ БД
// ============================================
$statusLabels = [];
$statusColors = [];
$statusList = [];

try {
    $stmtStatuses = $pdo->query("SELECT * FROM request_statuses WHERE is_active = 1 ORDER BY sort_order");
    $statusesList = $stmtStatuses->fetchAll(PDO::FETCH_ASSOC);
    foreach ($statusesList as $s) {
        $statusLabels[$s['code']] = $s['name'];
        $statusColors[$s['code']] = $s['color'];
        $statusList[$s['code']] = $s;
    }
} catch (PDOException $e) {
    // Если таблица не создана, используем стандартные статусы
    $statusLabels = ['new' => 'Новый', 'processed' => 'В обработке', 'closed' => 'Закрыт', 'cancelled' => 'Отклонён'];
    $statusColors = ['new' => '#ffc107', 'processed' => '#17a2b8', 'closed' => '#28a745', 'cancelled' => '#dc3545'];
}

// Функция для получения сообщений с файлами
function getChatMessagesWithFiles($pdo, $requestId) {
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

// Функция для получения списка ID специалистов поддержки
function getSupportSpecialists($pdo) {
    $stmt = $pdo->prepare("SELECT id, name, email FROM user WHERE role = 'support_specialist'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Обработка AJAX-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    try {
        $action = $_POST['action'];
        
        // ДЕЙСТВИЕ: Обновление статуса (с поддержкой динамических статусов)
        if ($action === 'update_status') {
            $requestId = (int)($_POST['request_id'] ?? 0);
            $newStatus = $_POST['status'] ?? '';
            $comment = trim($_POST['comment'] ?? '');
            
            // Проверяем, существует ли такой статус в таблице request_statuses
            $stmtCheck = $pdo->prepare("SELECT code, name FROM request_statuses WHERE code = ? AND is_active = 1");
            $stmtCheck->execute([$newStatus]);
            $validStatus = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($requestId && $validStatus) {
                $pdo->prepare("UPDATE request SET status = ? WHERE id = ?")->execute([$newStatus, $requestId]);
                $pdo->prepare("INSERT INTO request_status_history (request_id, status, comment, created_by) VALUES (?, ?, ?, ?)")
                    ->execute([$requestId, $newStatus, $comment, $currentUser['id']]);
                
                $statusName = $validStatus['name'];
                $title = "Статус заявки #{$requestId} изменён";
                $messageText = "Статус вашей заявки изменён на: {$statusName}";
                if ($comment) $messageText .= "\nКомментарий: " . $comment;
                
                $stmt = $pdo->prepare("SELECT user_id FROM request WHERE id = ?");
                $stmt->execute([$requestId]);
                $requestData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($requestData) {
                    addNotification($pdo, $requestData['user_id'], 'status_change', $title, $messageText, '/mip/lk_user.php#request-' . $requestId);
                    
                    $userStmt = $pdo->prepare("SELECT email, name FROM user WHERE id = ?");
                    $userStmt->execute([$requestData['user_id']]);
                    $userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($userInfo && !empty($userInfo['email'])) {
                        $emailSubject = "Статус заявки #{$requestId} изменён";
                        $emailBody = getOrderStatusEmailTemplate($requestId, $statusName, $comment);
                        sendEmailNotification($userInfo['email'], $userInfo['name'], $emailSubject, $emailBody);
                    }
                }
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Неверный статус']);
            }
            exit;
        }
        
        // ДЕЙСТВИЕ: Получение сообщений чата
        if ($action === 'get_messages') {
            $requestId = (int)($_POST['request_id'] ?? 0);
            $messages = getChatMessagesWithFiles($pdo, $requestId);
            echo json_encode(['success' => true, 'messages' => $messages]);
            exit;
        }
        
        // ДЕЙСТВИЕ: Отправка сообщения в чат
        if ($action === 'send_message') {
            $requestId = (int)($_POST['request_id'] ?? 0);
            $message = trim($_POST['message'] ?? '');
            
            if ($requestId && $message) {
                $pdo->prepare("INSERT INTO request_messages (request_id, sender_type, sender_id, message) VALUES (?, 'support', ?, ?)")
                    ->execute([$requestId, $currentUser['id'], $message]);
                $messageId = $pdo->lastInsertId();
                
                $stmt = $pdo->prepare("SELECT user_id FROM request WHERE id = ?");
                $stmt->execute([$requestId]);
                $requestData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($requestData) {
                    addNotification($pdo, $requestData['user_id'], 'new_message', 'Новое сообщение в чате', "Специалист поддержки ответил на ваше обращение #{$requestId}", '/mip/lk_user.php#request-' . $requestId);
                    
                    $userStmt = $pdo->prepare("SELECT email, name FROM user WHERE id = ?");
                    $userStmt->execute([$requestData['user_id']]);
                    $userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($userInfo && !empty($userInfo['email'])) {
                        $emailSubject = "Новое сообщение в заявке #{$requestId}";
                        $emailBody = getNewMessageEmailTemplate($requestId, $message, $currentUser['name']);
                        sendEmailNotification($userInfo['email'], $userInfo['name'], $emailSubject, $emailBody);
                    }
                }
                echo json_encode(['success' => true, 'message_id' => $messageId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Пустое сообщение']);
            }
            exit;
        }
        
        // ДЕЙСТВИЕ: Загрузка файла в чат
        if ($action === 'upload_file') {
            $requestId = (int)($_POST['request_id'] ?? 0);
            $messageId = (int)($_POST['message_id'] ?? 0);
            
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Файл не загружен']);
                exit;
            }
            
            $file = $_FILES['file'];
            $maxSize = 10 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'Файл слишком большой (макс. 10 MB)']);
                exit;
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Недопустимый тип файла']);
                exit;
            }
            
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/chat/';
            $webPath = '/uploads/chat/';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $originalName = basename($file['name']);
            $safeOriginalName = preg_replace('/[^a-zA-Zа-яА-Я0-9._-]/u', '_', $originalName);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $safeName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $filePath = $uploadDir . $safeName;
            $dbPath = $webPath . $safeName;
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                if (!$messageId) {
                    $stmt = $pdo->prepare("INSERT INTO request_messages (request_id, sender_type, sender_id, message, created_at) VALUES (?, 'support', ?, '', NOW())");
                    $stmt->execute([$requestId, $currentUser['id']]);
                    $messageId = $pdo->lastInsertId();
                }
                
                $stmt = $pdo->prepare("INSERT INTO chat_files (message_id, file_name, file_url, file_size, uploaded_by) VALUES (?, ?, ?, ?, 'support')");
                $stmt->execute([$messageId, $safeOriginalName, $dbPath, $file['size']]);
                
                $stmtReq = $pdo->prepare("SELECT user_id FROM request WHERE id = ?");
                $stmtReq->execute([$requestId]);
                $requestData = $stmtReq->fetch(PDO::FETCH_ASSOC);
                
                if ($requestData) {
                    addNotification($pdo, $requestData['user_id'], 'new_message', 'Новое сообщение в чате', "Специалист поддержки отправил файл в заявке #{$requestId}", '/mip/lk_user.php#request-' . $requestId);
                }
                
                echo json_encode(['success' => true, 'message_id' => $messageId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Не удалось сохранить файл']);
            }
            exit;
        }
    } catch (Exception $e) {
        error_log("AJAX Error in lk_support.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ошибка сервера: ' . $e->getMessage()]);
        exit;
    }
}

// ЗАГРУЗКА ДАННЫХ ДЛЯ ОТОБРАЖЕНИЯ
$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'desc';
$highlightRequestId = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;

$sql = "SELECT r.*, u.name AS client_name, u.email AS client_email, p.name AS product_name
FROM request r
LEFT JOIN user u ON r.user_id = u.id
LEFT JOIN products p ON r.product_id = p.id
WHERE r.type NOT IN ('wl')
AND (r.type IN ('r', 's', 'q'))";
$params = [];

if ($filter === 'orders') $sql .= " AND r.type = 'r'";
elseif ($filter === 'services') $sql .= " AND r.type = 's'";
elseif ($filter === 'questions') $sql .= " AND r.type = 'q'";

if ($search) {
    $sql .= " AND (r.id LIKE :search OR u.name LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%{$search}%";
}

$sql .= " ORDER BY r.datetime " . ($sort === 'asc' ? 'ASC' : 'DESC');

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rawRequests = $stmt->fetchAll();

function getAddressFromMessage($message) {
    $data = json_decode($message, true);
    if (!is_array($data)) return '';
    return $data['address'] ?? '';
}

function canGroupRequests($req1, $req2) {
    $time1 = strtotime($req1['datetime']);
    $time2 = strtotime($req2['datetime']);
    $diff = abs($time1 - $time2);
    return $diff <= 3600;
}

$groupedRequests = [];

foreach ($rawRequests as $req) {
    $address = getAddressFromMessage($req['message']);
    $found = false;
    
    foreach ($groupedRequests as $key => &$group) {
        if ($group['user_id'] == $req['user_id'] && $group['address'] === $address) {
            $canAdd = false;
            foreach ($group['items'] as $existingReq) {
                if (canGroupRequests($existingReq, $req)) {
                    $canAdd = true;
                    break;
                }
            }
            $lastReq = end($group['items']);
            if (canGroupRequests($lastReq, $req)) {
                $canAdd = true;
            }
            
            if ($canAdd) {
                $group['items'][] = $req;
                if (strtotime($req['datetime']) < strtotime($group['first_date'])) {
                    $group['first_date'] = $req['datetime'];
                }
                if (strtotime($req['datetime']) > strtotime($group['latest_date'])) {
                    $group['latest_date'] = $req['datetime'];
                }
                $found = true;
                break;
            }
        }
    }
    
    if (!$found) {
        $groupedRequests[] = [
            'user_id' => $req['user_id'],
            'client_name' => $req['client_name'],
            'client_email' => $req['client_email'],
            'address' => $address,
            'items' => [$req],
            'first_date' => $req['datetime'],
            'latest_date' => $req['datetime']
        ];
    }
}

usort($groupedRequests, function($a, $b) {
    return strtotime($b['latest_date']) - strtotime($a['latest_date']);
});

function getStatusHistory($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT rsh.*, rs.name as status_name, rs.color as status_color
        FROM request_status_history rsh
        LEFT JOIN request_statuses rs ON rsh.status = rs.code
        WHERE rsh.request_id = ?
        ORDER BY rsh.created_at ASC
    ");
    $stmt->execute([$id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function parseJsonToTable($message, $type) {
    $data = json_decode($message, true);
    if (!is_array($data)) {
        return '<div class="json-data">' . htmlspecialchars($message) . '</div>';
    }
    $html = '<table class="json-table">';
    
    if ($type === 'r') {
        if (!empty($data['product_name'])) {
            $html .= '<tr><td class="json-label">Товар</td><td class="json-value">' . htmlspecialchars($data['product_name']) . '</td></tr>';
        }
        if (!empty($data['quantity'])) {
            $html .= '<tr><td class="json-label">Количество</td><td class="json-value">' . htmlspecialchars($data['quantity']) . ' шт.</td></tr>';
        }
        if (!empty($data['configuration_name']) || !empty($data['configuration'])) {
            $configName = htmlspecialchars($data['configuration_name'] ?? 'Стандартная');
            $html .= '<tr><td class="json-label">Конфигурация</td><td class="json-value">' . $configName . '</td></tr>';
        }
        if (!empty($data['line_total']) || !empty($data['total_price'])) {
            $total = $data['line_total'] ?? $data['total_price'] ?? 0;
            $html .= '<tr><td class="json-label">Сумма</td><td class="json-value"><strong>' . number_format($total, 0, '.', ' ') . ' ₽</strong></td></tr>';
        }
    } elseif ($type === 's') {
        if (!empty($data['service_name'])) {
            $html .= '<tr><td class="json-label">Услуга</td><td class="json-value">' . htmlspecialchars($data['service_name']) . '</td></tr>';
        }
        if (!empty($data['price'])) {
            $html .= '<tr><td class="json-label">Стоимость</td><td class="json-value">' . number_format($data['price'], 0, '.', ' ') . ' ₽</span></td></tr>';
        }
    } elseif ($type === 'q') {
        if (!empty($data['subject'])) {
            $html .= '<tr><td class="json-label">Тема</td><td class="json-value">' . htmlspecialchars($data['subject']) . '</td></tr>';
        }
        if (!empty($data['question'])) {
            $html .= '<tr><td class="json-label">Вопрос</td><td class="json-value">' . nl2br(htmlspecialchars($data['question'])) . '</td></tr>';
        }
    }
    
    $html .= '</table>';
    return $html;
}

// Загружаем статусы для выпадающего списка в модальном окне
$statusOptionsHtml = '';
foreach ($statusList as $code => $status) {
    $statusOptionsHtml .= '<option value="' . htmlspecialchars($code) . '">' . htmlspecialchars($status['name']) . '</option>';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/style_header_footer.css">
<link rel="stylesheet" href="css/style_main.css">
<link rel="stylesheet" href="css/style_support.css">
<style>
.email-with-copy { display: inline-flex; align-items: center; gap: 8px; }
.btn-copy-email { background: #e9ecef; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 14px; transition: all 0.2s; }
.btn-copy-email:hover { background: #dee2e6; }
.btn-copy-email:active { transform: scale(0.95); }
.status-badge { background: #f0f0f0; color: #333; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; border: 1px solid #ddd; }
.chat-file-link { display: inline-flex; align-items: center; gap: 5px; background: rgba(44,125,160,0.1); padding: 3px 8px; border-radius: 4px; text-decoration: none; font-size: 11px; color: #2c7da0; margin-top: 5px; margin-right: 5px; }
.chat-file-link:hover { background: #2c7da0; color: white; }
.chat-file-attach { display: flex; align-items: center; gap: 10px; padding: 0 15px 10px 15px; }
.chat-file-label { display: inline-flex; align-items: center; gap: 5px; background: #f0f0f0; border: 1px solid #ddd; color: #333; padding: 5px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; }
.chat-file-label:hover { background: #e0e0e0; }
.chat-file-input { display: none; }
.selected-file-name { font-size: 12px; color: #666; }
.group-badge { background: #e3f2fd; color: #2c7da0; padding: 2px 8px; border-radius: 12px; font-size: 11px; margin-left: 10px; }
.group-items-count { background: #f0f0f0; padding: 2px 6px; border-radius: 10px; font-size: 10px; margin-left: 5px; }
.group-header { background: #f8f9fa; padding: 10px 15px; border-bottom: 1px solid #e0e0e0; margin-bottom: 10px; border-radius: 8px; }
.sub-request { margin-left: 20px; padding: 8px 12px; background: #fafafa; border-radius: 8px; margin-bottom: 8px; border-left: 3px solid #2c7da0; }
.sub-request-title { font-size: 12px; font-weight: 600; color: #2c7da0; margin-bottom: 5px; }
.request-header-left { display: flex; align-items: center; gap: 10px; }
.highlight-request { background: #fff8e1; border: 2px solid #ffc107; animation: pulse 1s ease; }
@keyframes pulse {
    0% { background: #fff8e1; }
    50% { background: #ffeaa7; }
    100% { background: #fff8e1; }
}
</style>
<title>Поддержка</title>
</head>
<body>
<?php include 'header.php'; ?>

<div class="lk-content">
<div class="lk-content">
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
    <h1 class="lk-title" style="margin-bottom: 0;">Личный кабинет сотрудника</h1>
    
    <div style="display: flex; gap: 15px; align-items: center;">
        <!-- Кнопка уведомлений -->
        <div class="notifications-wrapper" style="position: relative;">
            <button class="notifications-btn" id="notificationsBtn" style="background: none; border: none; font-size: 24px; cursor: pointer; position: relative; color: #1a1982;">
                <i class="fas fa-bell"></i>
                <span id="notificationsBadge" class="notifications-badge" style="position: absolute; top: -8px; right: -12px; background: #dc3545; color: white; border-radius: 50%; padding: 2px 6px; font-size: 11px; min-width: 18px; text-align: center; display: none;">0</span>
            </button>
            <div id="notificationsDropdown" class="notifications-dropdown" style="display: none; position: absolute; right: 0; top: 40px; width: 350px; background: white; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.15); z-index: 1000; max-height: 400px; overflow-y: auto;">
                <div style="padding: 12px 15px; border-bottom: 1px solid #eee; font-weight: 600;">Уведомления</div>
                <div id="notificationsList" style="padding: 0;">
                    <div style="padding: 15px; text-align: center; color: #999;">Загрузка...</div>
                </div>
                <div style="padding: 10px 15px; border-top: 1px solid #eee; text-align: center;">
                    <a href="/notifications.php" style="color: #1a1982; text-decoration: none; font-size: 13px;">Все уведомления</a>
                </div>
            </div>
        </div>
        <a href="logout.php" class="btn btn-danger">
            <i class="fas fa-sign-out-alt"></i> Выйти
        </a>
    </div>
</div>
<div class="lk-controls">
    <form method="GET" class="lk-search">
        <input type="text" name="search" placeholder="Поиск по ID, имени, email..." value="<?= htmlspecialchars($search) ?>" />
        <button type="submit" class="btn-search">Поиск</button>
    </form>
    <div class="lk-sort">
        <span>Сортировка:</span>
        <a href="?filter=<?= urlencode($filter) ?>&sort=desc&search=<?= urlencode($search) ?>" class="sort-btn <?= $sort === 'desc' ? 'active' : '' ?>">Сначала новые</a>
        <a href="?filter=<?= urlencode($filter) ?>&sort=asc&search=<?= urlencode($search) ?>" class="sort-btn <?= $sort === 'asc' ? 'active' : '' ?>">Сначала старые</a>
    </div>
</div>
<div class="tabs">
    <a href="?filter=all&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>" class="tab-btn <?= $filter === 'all' ? 'active' : '' ?>">Все</a>
    <a href="?filter=orders&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>" class="tab-btn <?= $filter === 'orders' ? 'active' : '' ?>">Заказы</a>
    <a href="?filter=services&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>" class="tab-btn <?= $filter === 'services' ? 'active' : '' ?>">Услуги</a>
    <a href="?filter=questions&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>" class="tab-btn <?= $filter === 'questions' ? 'active' : '' ?>">Вопросы</a>
</div>
<div class="requests-grid">
<?php if (empty($groupedRequests)): ?>
    <div class="no-requests"><p>Нет обращений</p></div>
<?php else: ?>
<?php foreach ($groupedRequests as $group):
    $hasMultiple = count($group['items']) > 1;
    // Определяем статус группы (наиболее критичный)
    $statuses = array_column($group['items'], 'status');
    if (in_array('new', $statuses)) $groupStatus = 'new';
    elseif (in_array('processed', $statuses)) $groupStatus = 'processed';
    elseif (in_array('cancelled', $statuses)) $groupStatus = 'cancelled';
    else $groupStatus = 'closed';
?>
<div class="request-card" data-user-id="<?= $group['user_id'] ?>">
    <div class="request-header">
        <div class="request-header-left">
            <?php if ($hasMultiple): ?>
                <span class="group-badge"><?= count($group['items']) ?> заявок</span>
            <?php endif; ?>
        </div>
        <span class="status-badge" style="background-color: <?= $statusColors[$groupStatus] ?? '#6c757d' ?>20; color: <?= $statusColors[$groupStatus] ?? '#6c757d' ?>;">
            <?= $statusLabels[$groupStatus] ?? $groupStatus ?>
        </span>
    </div>
    <div class="request-info-grid">
        <div class="info-row">
            <span class="info-label">Клиент</span>
            <span><?= htmlspecialchars($group['client_name'] ?? 'Аноним') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Email</span>
            <span class="email-with-copy">
                <?= htmlspecialchars($group['client_email'] ?? '') ?>
                <?php if (!empty($group['client_email'])): ?>
                <button class="btn-copy-email"
                    onclick="copyToClipboard('<?= htmlspecialchars($group['client_email']) ?>', this)"
                    title="Скопировать email">
                    Копировать
                </button>
                <?php endif; ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Адрес</span>
            <span><?= htmlspecialchars($group['address'] ?: 'Не указан') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Период</span>
            <span><?= date('d.m.Y H:i', strtotime($group['first_date'])) ?>
            <?php if ($hasMultiple): ?>
                → <?= date('d.m.Y H:i', strtotime($group['latest_date'])) ?>
            <?php endif; ?>
            </span>
        </div>
    </div>
    
    <?php if ($hasMultiple): ?>
    <div class="group-header">
        <i class="fas fa-list"></i> Заявки в группе:
    </div>
    <?php foreach ($group['items'] as $req):
        $isHighlighted = ($highlightRequestId == $req['id']);
    ?>
    <div class="sub-request <?= $isHighlighted ? 'highlight-request' : '' ?>" data-request-id="<?= $req['id'] ?>">
        <div class="sub-request-title">
            Заявка №<?= $req['id'] ?> 
            <span class="status-badge" style="font-size: 10px; background-color: <?= $statusColors[$req['status']] ?? '#6c757d' ?>20; color: <?= $statusColors[$req['status']] ?? '#6c757d' ?>;">
                <?= $statusLabels[$req['status']] ?? $req['status'] ?>
            </span>
            <span style="float: right;"><?= date('d.m.Y H:i', strtotime($req['datetime'])) ?></span>
        </div>
        <div class="request-details" style="padding: 0;">
            <?= parseJsonToTable($req['message'], $req['type']) ?>
        </div>
        <div class="btn-actions" style="padding: 10px 0 0 0; border-top: none;">
            <?php if ($req['type'] !== 'q'): ?>
                <button class="btn btn-primary" onclick="openModal('status', <?= $req['id'] ?>)">Статус</button>
                <button class="btn btn-primary" onclick="openModal('chat', <?= $req['id'] ?>)">Чат</button>
                <?php if ($req['status'] !== 'cancelled'): ?>
                    <button class="btn btn-danger" onclick="openModal('reject', <?= $req['id'] ?>)">Отклонить</button>
                <?php endif; ?>
            <?php endif; ?>
            <button class="btn btn-secondary" onclick="toggleHistory(<?= $req['id'] ?>)">История</button>
        </div>
        <div class="status-history" id="history-<?= $req['id'] ?>" style="margin-top: 10px;">
            <div class="history-title">История статусов</div>
            <div class="status-timeline">
            <?php 
            $history = getStatusHistory($pdo, $req['id']);
            if (!empty($history)): ?>
                <?php foreach ($history as $i => $h): ?>
                <div class="timeline-item <?= $i === count($history) - 1 ? 'current' : 'completed' ?>">
                    <div class="timeline-date"><?= date('d.m.Y H:i', strtotime($h['created_at'])) ?></div>
                    <div class="timeline-status" style="color: <?= $h['status_color'] ?? '#333' ?>; font-weight: 600;">
                        <?= $h['status_name'] ?? $h['status'] ?>
                    </div>
                    <?php if ($h['comment']): ?>
                    <div class="timeline-comment"><?= htmlspecialchars($h['comment']) ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="timeline-item current">
                    <div class="timeline-date"><?= date('d.m.Y H:i', strtotime($req['datetime'])) ?></div>
                    <div class="timeline-status"><?= $statusLabels[$req['status']] ?? $req['status'] ?></div>
                    <div class="timeline-comment">Заказ создан</div>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: 
        $req = $group['items'][0];
        $isHighlighted = ($highlightRequestId == $req['id']);
    ?>
    <div class="request-details <?= $isHighlighted ? 'highlight-request' : '' ?>">
        <?= parseJsonToTable($req['message'], $req['type']) ?>
    </div>
    <div class="btn-actions <?= $isHighlighted ? 'highlight-request' : '' ?>">
        <?php if ($req['type'] !== 'q'): ?>
            <button class="btn btn-primary" onclick="openModal('status', <?= $req['id'] ?>)">Статус</button>
            <button class="btn btn-primary" onclick="openModal('chat', <?= $req['id'] ?>)">Чат</button>
            <?php if ($req['status'] !== 'cancelled'): ?>
                <button class="btn btn-danger" onclick="openModal('reject', <?= $req['id'] ?>)">Отклонить</button>
            <?php endif; ?>
        <?php endif; ?>
        <button class="btn btn-secondary" onclick="toggleHistory(<?= $req['id'] ?>)">История</button>
    </div>
    <div class="status-history" id="history-<?= $req['id'] ?>">
        <div class="history-title">История статусов</div>
        <div class="status-timeline">
        <?php 
        $history = getStatusHistory($pdo, $req['id']);
        if (!empty($history)): ?>
            <?php foreach ($history as $i => $h): ?>
            <div class="timeline-item <?= $i === count($history) - 1 ? 'current' : 'completed' ?>">
                <div class="timeline-date"><?= date('d.m.Y H:i', strtotime($h['created_at'])) ?></div>
                <div class="timeline-status" style="color: <?= $h['status_color'] ?? '#333' ?>; font-weight: 600;">
                    <?= $h['status_name'] ?? $h['status'] ?>
                </div>
                <?php if ($h['comment']): ?>
                <div class="timeline-comment"><?= htmlspecialchars($h['comment']) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="timeline-item current">
                <div class="timeline-date"><?= date('d.m.Y H:i', strtotime($req['datetime'])) ?></div>
                <div class="timeline-status"><?= $statusLabels[$req['status']] ?? $req['status'] ?></div>
                <div class="timeline-comment">Заказ создан</div>
            </div>
        <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>
</div>

<!-- Модальное окно "Статус" (с динамическими статусами) -->
<div class="modal-overlay" id="status-overlay" onclick="closeModal('status')"></div>
<div class="modal" id="status-modal">
    <h3>Изменить статус</h3>
    <input type="hidden" id="status-request-id" />
    <select id="status-select" class="modal-input">
        <?= $statusOptionsHtml ?>
    </select>
    <textarea id="status-comment" class="modal-input" rows="3" placeholder="Комментарий..."></textarea>
    <div class="modal-buttons">
        <button class="btn btn-secondary" onclick="closeModal('status')">Отмена</button>
        <button class="btn btn-primary" onclick="saveStatus()">Сохранить</button>
    </div>
</div>

<!-- Модальное окно "Отклонить" (использует статус cancelled) -->
<div class="modal-overlay" id="reject-overlay" onclick="closeModal('reject')"></div>
<div class="modal" id="reject-modal">
    <h3 class="text-danger">Отклонить</h3>
    <input type="hidden" id="reject-request-id" />
    <textarea id="reject-comment" class="modal-input" rows="3" placeholder="Причина..."></textarea>
    <div class="modal-buttons">
        <button class="btn btn-secondary" onclick="closeModal('reject')">Отмена</button>
        <button class="btn btn-danger" onclick="saveReject()">Отклонить</button>
    </div>
</div>

<!-- Модальное окно "Чат" -->
<div class="modal-overlay" id="chat-overlay" onclick="closeModal('chat')"></div>
<div class="modal" id="chat-modal">
    <h3>Чат с клиентом</h3>
    <input type="hidden" id="chat-request-id" />
    <div class="chat-messages" id="chat-messages"></div>
    <div class="chat-file-attach">
        <label class="chat-file-label">
            <i class="fas fa-paperclip"></i> Прикрепить файл
            <input type="file" class="chat-file-input" id="chat-file-input" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt">
        </label>
        <span class="selected-file-name" id="selected-file-name"></span>
    </div>
    <div class="chat-input">
        <input type="text" id="chat-message-input" placeholder="Сообщение..." />
        <button class="btn btn-primary" onclick="sendMessage()">Отправить</button>
    </div>
    <div class="modal-buttons">
        <button class="btn btn-secondary" onclick="closeModal('chat')">Закрыть</button>
    </div>
</div>

<script>
let currentFile = null;

// Уведомления для специалиста поддержки
let notificationsCheckInterval = null;

function loadNotifications() {
    fetch('/get_notifications.php')
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('notificationsBadge');
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
            
            const list = document.getElementById('notificationsList');
            if (data.notifications && data.notifications.length > 0) {
                list.innerHTML = data.notifications.map(n => `
                    <div class="notification-item" data-id="${n.id}" data-link="${n.link}" style="padding: 12px 15px; border-bottom: 1px solid #f0f0f0; cursor: pointer; ${!n.is_read ? 'background: #f0f4ff;' : ''}">
                        <div style="font-weight: 600; font-size: 14px; margin-bottom: 5px;">${escapeHtml(n.title)}</div>
                        <div style="font-size: 12px; color: #666;">${escapeHtml(n.message)}</div>
                        <div style="font-size: 11px; color: #999; margin-top: 5px;">${n.created_at}</div>
                    </div>
                `).join('');
            } else {
                list.innerHTML = '<div style="padding: 15px; text-align: center; color: #999;">Нет уведомлений</div>';
            }
        })
        .catch(err => console.error('Ошибка загрузки уведомлений:', err));
}

function markNotificationRead(id) {
    fetch('/mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    }).catch(err => console.error(err));
}

// Обработчик клика по уведомлению
document.addEventListener('click', function(e) {
    const item = e.target.closest('.notification-item');
    if (item) {
        e.stopPropagation();
        const id = item.dataset.id;
        const link = item.dataset.link;
        if (id) markNotificationRead(id);
        if (link) window.location.href = link;
    }
});

// Кнопка уведомлений
const notifBtn = document.getElementById('notificationsBtn');
const notifDropdown = document.getElementById('notificationsDropdown');
if (notifBtn) {
    notifBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (notifDropdown.style.display === 'block') {
            notifDropdown.style.display = 'none';
        } else {
            loadNotifications();
            notifDropdown.style.display = 'block';
        }
    });
}

document.addEventListener('click', function() {
    if (notifDropdown) notifDropdown.style.display = 'none';
});

loadNotifications();
notificationsCheckInterval = setInterval(loadNotifications, 30000);

document.getElementById('chat-file-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 10 * 1024 * 1024) {
            alert('Файл слишком большой (макс. 10 MB)');
            this.value = '';
            currentFile = null;
            document.getElementById('selected-file-name').textContent = '';
            return;
        }
        currentFile = file;
        document.getElementById('selected-file-name').textContent = file.name;
    } else {
        currentFile = null;
        document.getElementById('selected-file-name').textContent = '';
    }
});

function openModal(type, id) {
    document.getElementById(type + '-request-id').value = id;
    document.getElementById(type + '-overlay').style.display = 'block';
    document.getElementById(type + '-modal').style.display = 'block';
    if (type === 'chat') loadMessages(id);
}

function closeModal(type) {
    document.getElementById(type + '-overlay').style.display = 'none';
    document.getElementById(type + '-modal').style.display = 'none';
    if (type === 'chat') {
        currentFile = null;
        document.getElementById('chat-file-input').value = '';
        document.getElementById('selected-file-name').textContent = '';
    }
}

function toggleHistory(id) {
    const el = document.getElementById('history-' + id);
    if (el) el.classList.toggle('visible');
}

async function saveStatus() {
    const id = document.getElementById('status-request-id').value;
    const status = document.getElementById('status-select').value;
    const comment = document.getElementById('status-comment').value.trim();
    const fd = new FormData();
    fd.append('action', 'update_status');
    fd.append('request_id', id);
    fd.append('status', status);
    fd.append('comment', comment);
    const res = await fetch('lk_support.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
        alert('Статус обновлён, клиент получит уведомление');
        location.reload();
    } else alert('Ошибка: ' + (data.message || 'Неизвестная'));
}

async function saveReject() {
    const id = document.getElementById('reject-request-id').value;
    const comment = document.getElementById('reject-comment').value.trim();
    const fd = new FormData();
    fd.append('action', 'update_status');
    fd.append('request_id', id);
    fd.append('status', 'cancelled');
    fd.append('comment', comment);
    const res = await fetch('lk_support.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
        alert('Заявка отклонена, клиент получит уведомление');
        location.reload();
    } else alert('Ошибка: ' + (data.message || 'Неизвестная'));
}

function formatFileSize(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function loadMessages(id) {
    const fd = new FormData();
    fd.append('action', 'get_messages');
    fd.append('request_id', id);
    const res = await fetch('lk_support.php', { method: 'POST', body: fd });
    const data = await res.json();
    const box = document.getElementById('chat-messages');
    box.innerHTML = '';
    if (data.messages && data.messages.length) {
        data.messages.forEach(msg => {
            const cls = msg.sender_type === 'support' ? 'chat-support' : 'chat-user';
            let filesHtml = '';
            if (msg.files && msg.files.length > 0) {
                filesHtml = '<div style="margin-top:5px;">';
                msg.files.forEach(file => {
                    const fileSize = formatFileSize(file.size);
                    filesHtml += `<a href="${file.url}" class="chat-file-link" target="_blank">
                        📎 ${escapeHtml(file.name)} ${fileSize ? '(' + fileSize + ')' : ''}
                    </a>`;
                });
                filesHtml += '</div>';
            }
            const time = new Date(msg.created_at).toLocaleString('ru-RU');
            box.innerHTML += `
                <div class="chat-message ${cls}">
                    <div>${escapeHtml(msg.message || '')}</div>
                    ${filesHtml}
                    <div class="chat-time">${time}</div>
                </div>`;
        });
        box.scrollTop = box.scrollHeight;
    } else {
        box.innerHTML = '<p style="text-align:center;color:#777">Нет сообщений</p>';
    }
}

async function sendMessage() {
    const id = document.getElementById('chat-request-id').value;
    const input = document.getElementById('chat-message-input');
    const msg = input.value.trim();
    
    if (!msg && !currentFile) {
        alert('Введите сообщение или прикрепите файл');
        return;
    }
    
    let messageId = null;
    
    if (msg) {
        const fd = new FormData();
        fd.append('action', 'send_message');
        fd.append('request_id', id);
        fd.append('message', msg);
        const res = await fetch('lk_support.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            messageId = data.message_id;
            input.value = '';
        } else {
            alert('Ошибка отправки сообщения');
            return;
        }
    }
    
    if (currentFile) {
        const fd = new FormData();
        fd.append('action', 'upload_file');
        fd.append('request_id', id);
        if (messageId) fd.append('message_id', messageId);
        fd.append('file', currentFile);
        const res = await fetch('lk_support.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.success) {
            alert('Ошибка загрузки файла: ' + (data.message || ''));
            return;
        }
        document.getElementById('chat-file-input').value = '';
        document.getElementById('selected-file-name').textContent = '';
        currentFile = null;
    }
    
    loadMessages(id);
}

document.getElementById('chat-message-input').addEventListener('keypress', e => {
    if (e.key === 'Enter') sendMessage();
});

function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const original = btn.textContent;
        btn.textContent = '✓';
        btn.style.background = '#28a745';
        btn.style.color = '#fff';
        setTimeout(() => {
            btn.textContent = original;
            btn.style.background = '';
            btn.style.color = '';
        }, 1500);
    }).catch(err => {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        const original = btn.textContent;
        btn.textContent = '✓';
        btn.style.background = '#28a745';
        btn.style.color = '#fff';
        setTimeout(() => {
            btn.textContent = original;
            btn.style.background = '';
            btn.style.color = '';
        }, 1500);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash && window.location.hash.startsWith('#request-')) {
        const hash = window.location.hash;
        let requestId = null;
        let targetType = 'chat';
        if (hash.includes('-status')) {
            requestId = hash.replace('#request-', '').replace('-status', '');
            targetType = 'status';
        } else if (hash.includes('-chat')) {
            requestId = hash.replace('#request-', '').replace('-chat', '');
            targetType = 'chat';
        } else {
            requestId = hash.replace('#request-', '');
        }
        if (requestId) {
            let targetElement = document.querySelector(`.sub-request[data-request-id="${requestId}"]`);
            if (!targetElement) {
                targetElement = document.querySelector(`.request-card .btn-primary[onclick*="${requestId}"]`)?.closest('.request-card');
            }
            if (targetElement) {
                targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetElement.style.transition = 'background 0.3s';
                targetElement.style.background = '#fff8e1';
                setTimeout(() => { targetElement.style.background = ''; }, 2000);
                setTimeout(() => {
                    if (targetType === 'status') {
                        const statusBtn = targetElement.querySelector('.btn-primary[onclick*="status"]');
                        if (statusBtn) statusBtn.click();
                    } else {
                        const chatBtn = targetElement.querySelector('.btn-primary[onclick*="chat"]');
                        if (chatBtn) chatBtn.click();
                    }
                }, 500);
            }
        }
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    const highlightId = urlParams.get('request_id');
    if (highlightId) {
        const targetElement = document.querySelector(`.sub-request[data-request-id="${highlightId}"], .request-details[data-request-id="${highlightId}"]`);
        if (targetElement) {
            targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            targetElement.classList.add('highlight-request');
            setTimeout(() => {
                targetElement.classList.remove('highlight-request');
            }, 3000);
        }
    }
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>