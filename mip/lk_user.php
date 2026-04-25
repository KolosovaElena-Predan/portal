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
    header('Location: mip.php');
    exit;
}

require_once 'config.php';

// Загрузка истории статусов
function getStatusHistory($pdo, $requestId) {
    $stmt = $pdo->prepare("
    SELECT status, comment, created_at
    FROM request_status_history
    WHERE request_id = ?
    ORDER BY created_at ASC
    ");
    $stmt->execute([$requestId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Загрузка сообщения поддержки
function getChatMessages($pdo, $requestId) {
    $stmt = $pdo->prepare("
    SELECT sender_type, message, created_at
    FROM request_messages
    WHERE request_id = ?
    ORDER BY created_at ASC
    ");
    $stmt->execute([$requestId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Загрузка заказов (type='r') И услуги (type='s')
$stmt = $pdo->prepare("
SELECT
r.id,
r.datetime,
r.status,
r.type,
r.message,
r.device_type_id,
r.product_id,
p.name AS product_name,
p.base_price AS product_price,
(SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS product_img,
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

$items = ['equipment' => [], 'services' => []];

foreach ($raw_requests as $req) {
    if ($req['type'] === 's' && !empty($req['message'])) {
        $req['service_details'] = json_decode($req['message'], true);
    }
    $req['status_history'] = getStatusHistory($pdo, $req['id']);
    $req['chat_messages'] = getChatMessages($pdo, $req['id']);
    $key = ($req['type'] === 's') ? 'services' : 'equipment';
    $items[$key][] = $req;
}

$statusLabels = [
    'new' => 'Оформление',
    'processed' => 'В пути',
    'closed' => 'Доставлено',
    'cancelled' => 'Отклонено'
];

$user_data = ['id' => $user->id, 'name' => $user->name, 'email' => $user->email];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/style_header_footer.css" />
<link rel="stylesheet" href="css/style_main.css" />
<link rel="stylesheet" href="css/style_mip.css" />
<link rel="stylesheet" href="css/header_mip.css" />
<link rel="stylesheet" href="css/style_lk.css" />
<title>Личный кабинет</title>
</head>
<body>
<div class="screen">
<div class="div">
<?php 
    $context = 'mip';
    require_once '../header.php'; 
?>
<div class="lk-content">
<h1 class="lk-title">Личный кабинет</h1>

<div class="lk-main-content">
<div class="device-list-wrapper">

<!-- ОБОРУДОВАНИЕ -->
<div class="type-section">
<h3 class="type-title">Устройства</h3>
<?php if (empty($items['equipment'])): ?>
<div class="no-orders">Нет заказов оборудования</div>
<?php else: ?>
<?php foreach ($items['equipment'] as $item): ?>
<?= renderRequestCard($item, $statusLabels) ?>
<?php endforeach; ?>
<?php endif; ?>
</div>

<!-- УСЛУГИ -->
<div class="type-section">
<h3 class="type-title">Услуги</h3>
<?php if (empty($items['services'])): ?>
<div class="no-orders">Нет заказанных услуг</div>
<?php else: ?>
<?php foreach ($items['services'] as $item): ?>
<?= renderRequestCard($item, $statusLabels) ?>
<?php endforeach; ?>
<?php endif; ?>
</div>

</div>

<!-- Боковая панель -->
<div class="personal-data">
<h3>Личные данные</h3>
<p><strong>ФИО:</strong> <?= htmlspecialchars($user_data['name']) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($user_data['email']) ?></p>
<button class="edit-profile-btn" onclick="openEditProfileModal()">
    <i class="fas fa-user-edit"></i> Редактировать профиль
</button>
<a href="cart.php" class="cart-link">Перейти в корзину</a>
<a href="logout.php" class="cart-link logout-link">Выйти</a>
</div>
</div>
</div>
<?php require_once '../footer.php'; ?>
</div>
</div>

<!-- МОДАЛЬНОЕ ОКНО ИСТОРИИ СТАТУСОВ -->
<div id="statusModal" class="modal-overlay" onclick="closeModalIfClickOutside(event, 'statusModal')">
    <div class="modal-window">
        <div class="modal-header">
            <h3>История статусов</h3>
            <button class="modal-close" onclick="closeModal('statusModal')">&times;</button>
        </div>
        <div class="modal-body" id="statusModalBody">
            <!-- Содержимое загружается через JS -->
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal('statusModal')">Закрыть</button>
        </div>
    </div>
</div>

<!-- МОДАЛЬНОЕ ОКНО ЧАТА -->
<div id="chatModal" class="modal-overlay" onclick="closeModalIfClickOutside(event, 'chatModal')">
    <div class="modal-window">
        <div class="modal-header">
            <h3>Чат с поддержкой</h3>
            <button class="modal-close" onclick="closeModal('chatModal')">&times;</button>
        </div>
        <div class="modal-body" id="chatModalBody">
            <div class="chat-messages-modal" id="chatMessagesModal"></div>
            <form class="chat-reply-form-modal" id="chatReplyFormModal" data-request-id="">
                <textarea class="chat-reply-input-modal" id="chatMessageInput" placeholder="Напишите сообщение..." rows="2"></textarea>
                <button type="submit" class="chat-reply-btn-modal">Отправить</button>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal('chatModal')">Закрыть</button>
        </div>
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
                <label>Логин</label>
                <input type="text" name="login" value="<?= htmlspecialchars($user->login ?? '') ?>" disabled>
                <small style="color:#4a6a65;">Логин нельзя изменить</small>
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

<script>
// Текущий открытый requestId для чата
let currentChatRequestId = null;

// Открытие модалки истории статусов
function openStatusModal(requestId) {
    // ✅ Удаляем hash из URL, чтобы при перезагрузке окно не открывалось снова
    if (window.location.hash) {
        history.pushState("", document.title, window.location.pathname);
    }
    
    fetch(`get_status_history.php?request_id=${requestId}`)
        .then(res => res.json())
        .then(data => {
            const modalBody = document.getElementById('statusModalBody');
            if (data.success && data.history && data.history.length > 0) {
                let html = '<div class="status-timeline-modal">';
                data.history.forEach((item, index) => {
                    const isLast = index === data.history.length - 1;
                    html += `
                        <div class="status-timeline-item ${isLast ? 'current' : 'completed'}">
                            <div class="status-timeline-date">${item.created_at}</div>
                            <div class="status-timeline-text">${item.status_text}</div>
                            ${item.comment ? `<div class="status-timeline-comment">${escapeHtml(item.comment)}</div>` : ''}
                        </div>
                    `;
                });
                html += '</div>';
                modalBody.innerHTML = html;
            } else {
                modalBody.innerHTML = `
                    <div class="status-empty">
                        <i class="fas fa-history"></i>
                        <p>История статусов пуста</p>
                        <small>Статус заказа ещё не менялся</small>
                    </div>
                `;
            }
            document.getElementById('statusModal').classList.add('active');
        })
        .catch(err => {
            console.error(err);
            const modalBody = document.getElementById('statusModalBody');
            modalBody.innerHTML = `
                <div class="status-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Ошибка загрузки истории</p>
                    <small>Попробуйте позже</small>
                </div>
            `;
            document.getElementById('statusModal').classList.add('active');
        });
}

// Открытие модалки чата
function openChatModal(requestId) {
    // ✅ Удаляем hash из URL
    if (window.location.hash) {
        history.pushState("", document.title, window.location.pathname);
    }
    
    currentChatRequestId = requestId;
    document.getElementById('chatReplyFormModal').dataset.requestId = requestId;
    loadChatMessages(requestId);
    document.getElementById('chatModal').classList.add('active');
}

// Загрузка сообщений чата
// Загрузка сообщений чата
function loadChatMessages(requestId) {
    fetch('get_chat_messages.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'request_id=' + requestId
    })
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('chatMessagesModal');
        if (data.success && data.messages && data.messages.length > 0) {
            container.innerHTML = data.messages.map(msg => `
                <div class="chat-message ${msg.sender_type === 'user' ? 'chat-message-user' : 'chat-message-support'}">
                    <div class="chat-message-author">${msg.sender_type === 'user' ? 'Вы' : 'Поддержка'}</div>
                    <div>${escapeHtml(msg.message).replace(/\n/g, '<br>')}</div>
                    <div class="chat-message-time">${msg.created_at}</div>
                </div>
            `).join('');
        } else {
            // ✅ БАЗОВОЕ ЗНАЧЕНИЕ, КОГДА СООБЩЕНИЙ НЕТ
            container.innerHTML = `
                <div class="chat-empty">
                    <p>Нет сообщений</p>
                    <small>Напишите свой вопрос, и специалист поддержки ответит вам</small>
                </div>
            `;
        }
        container.scrollTop = container.scrollHeight;
    })
    .catch(err => {
        console.error(err);
        const container = document.getElementById('chatMessagesModal');
        container.innerHTML = `
            <div class="chat-error">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Ошибка загрузки чата</p>
                <small>Попробуйте позже</small>
            </div>
        `;
    });
}
// Отправка сообщения в чат
document.getElementById('chatReplyFormModal').addEventListener('submit', function(e) {
    e.preventDefault();
    const requestId = this.dataset.requestId;
    const message = document.getElementById('chatMessageInput').value.trim();
    if (!message) return;
    
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Отправка...';
    
    fetch('add_chat_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `request_id=${requestId}&message=${encodeURIComponent(message)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('chatMessageInput').value = '';
            loadChatMessages(requestId);
            // Уведомление для поддержки
            fetch('add_notification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `request_id=${requestId}&type=user_message`
            });
        } else {
            alert('Ошибка: ' + (data.error || 'Не удалось отправить'));
        }
        btn.disabled = false;
        btn.textContent = originalText;
    })
    .catch(err => {
        console.error(err);
        alert('Ошибка соединения');
        btn.disabled = false;
        btn.textContent = originalText;
    });
});

// Редактирование профиля
function openEditProfileModal() {
    document.getElementById('editProfileModal').classList.add('active');
}

function saveProfile(event) {
    event.preventDefault();
    const form = document.getElementById('profileForm');
    const formData = new FormData(form);
    formData.append('action', 'update_profile');
    
    const saveBtn = document.querySelector('#editProfileModal .btn-save');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Сохранение...';
    saveBtn.disabled = true;
    
    fetch('update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Профиль обновлён!');
            // ✅ Убираем hash из URL перед перезагрузкой
            if (window.location.hash) {
                history.pushState("", document.title, window.location.pathname);
            }
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

// Вспомогательные функции
function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function closeModalIfClickOutside(event, modalId) {
    if (event.target === document.getElementById(modalId)) {
        closeModal(modalId);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Обработка перехода по уведомлению
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
            setTimeout(() => {
                if (targetType === 'status') {
                    openStatusModal(requestId);
                } else {
                    openChatModal(requestId);
                }
            }, 500);
        }
    }
});
</script>

<?php
function renderRequestCard($req, $statusLabels) {
    $isService = ($req['type'] === 's');
    
    if ($isService) {
        $linkUrl = 'services_catalog.php';
        $name = htmlspecialchars($req['service_name'] ?? 'Услуга');
        $price = $req['service_price'] ?? 0;
        $img = htmlspecialchars($req['service_img'] ?: 'img/placeholder.jpg');
        $message = '';
        $priceFormatted = number_format($price, 2, ',', ' ') . ' ₽';
        $detailLink = 'services_catalog.php';
    } else {
        // ✅ ПРАВИЛЬНАЯ ССЫЛКА НА ТОВАР
        $productId = !empty($req['product_id']) ? (int)$req['product_id'] : (!empty($req['device_type_id']) ? (int)$req['device_type_id'] : 0);
        
        if ($productId > 0) {
            $linkUrl = 'product.php?id=' . $productId;
            $detailLink = 'product.php?id=' . $productId;
        } else {
            $linkUrl = 'catalog.php';
            $detailLink = 'catalog.php';
        }
        
        $name = htmlspecialchars($req['product_name'] ?? 'Товар');
        $img = htmlspecialchars($req['product_img'] ?: 'img/placeholder.jpg');
        
        // Берём цену из JSON в поле message
        $msgData = json_decode($req['message'], true);
        
        // Получаем итоговую цену из заказа
        if (isset($msgData['total_price'])) {
            $price = (float)$msgData['total_price'];
        } elseif (isset($msgData['line_total'])) {
            $price = (float)$msgData['line_total'];
        } elseif (isset($msgData['unit_price'])) {
            $price = (float)$msgData['unit_price'];
        } else {
            $price = (float)($req['product_price'] ?? 0);
        }
        
        $priceFormatted = number_format($price, 2, ',', ' ') . ' ₽';
        
        // Адрес доставки
        $message = isset($msgData['address']) ? htmlspecialchars($msgData['address']) : '';
        
        // Количество
        $quantity = isset($msgData['quantity']) ? (int)$msgData['quantity'] : 1;
        if ($quantity > 1) {
            $priceFormatted .= ' x ' . $quantity . ' = ' . number_format($price * $quantity, 2, ',', ' ') . ' ₽';
        }
        
        // Детали конфигурации
        $configDetails = '';
        if (!empty($msgData['configuration_name'])) {
            $configDetails .= '<div style="font-size: 12px; color: #4a6a65; margin-top: 4px;">Комплектация: ' . htmlspecialchars($msgData['configuration_name']) . '</div>';
        }
        if (!empty($msgData['modifications']) && is_array($msgData['modifications'])) {
            $mods = [];
            foreach ($msgData['modifications'] as $mod) {
                $modStr = htmlspecialchars($mod['group'] . ': ' . $mod['variant']['name']);
                if (!empty($mod['property']['name'])) {
                    $modStr .= ' + ' . htmlspecialchars($mod['property']['name']);
                }
                $mods[] = $modStr;
            }
            if (!empty($mods)) {
                $configDetails .= '<div style="font-size: 12px; color: #4a6a65; margin-top: 4px;">Опции: ' . implode(', ', $mods) . '</div>';
            }
        }
    }

    $status = $req['status'] ?? 'new';
    $statusText = $statusLabels[$status] ?? $status;
    $statusClass = $status === 'new' ? 'status-new' : ($status === 'processed' ? 'status-processed' : ($status === 'closed' ? 'status-closed' : 'status-cancelled'));

    $output = '<div class="request-card" data-request-id="' . $req['id'] . '">';
    $output .= '<div style="display: flex;">';
    $output .= '<div class="card-image-wrapper">';
    $output .= '<img src="' . $img . '" alt="' . $name . '" onerror="this.src=\'img/placeholder.jpg\'">';
    $output .= '</div>';
    $output .= '<div class="request-card-body">';
    $output .= '<div class="request-card-header">';
    $output .= '<div class="request-device">' . $name . '</div>';
    $output .= '<span class="status-badge ' . $statusClass . '">' . $statusText . '</span>';
    $output .= '</div>';
    $output .= '<div class="request-price">' . $priceFormatted . '</div>';
    
    // Детали конфигурации
    if (!empty($configDetails)) {
        $output .= $configDetails;
    }
    
    if (!empty($message) && !$isService) {
        $output .= '<div style="font-size: 0.9em; color: #5b8c86; margin-top: 5px;">Адрес: ' . $message . '</div>';
    }
    
    $output .= '<div class="card-actions">';
    $output .= '<button class="btn-detail" onclick="openStatusModal(' . $req['id'] . ')"><i class="fas fa-history"></i> История статусов</button>';
    $output .= '<button class="btn-detail" onclick="openChatModal(' . $req['id'] . ')"><i class="fas fa-comments"></i> Чат с поддержкой</button>';
    // ✅ ПРАВИЛЬНАЯ ССЫЛКА НА ТОВАР (НЕ НА КАТАЛОГ)
    $output .= '<a href="' . $detailLink . '" class="btn-detail"><i class="fas fa-info-circle"></i> Подробнее</a>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';
    
    return $output;
}
?>
</body>
</html>