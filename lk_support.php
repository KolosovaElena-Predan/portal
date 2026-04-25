<?php
session_start();
require_once 'config.php';

// Устанавливаем контекст для шапки и подвала (раздел МИП)
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
// Обработка AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    try {
        $action = $_POST['action'];
        if ($action === 'update_status') {
            $requestId = (int)($_POST['request_id'] ?? 0);
            $newStatus = $_POST['status'] ?? '';
            $comment = trim($_POST['comment'] ?? '');
            if ($requestId && in_array($newStatus, ['new', 'processed', 'closed', 'cancelled'])) {
                $pdo->prepare("UPDATE request SET status = ? WHERE id = ?")->execute([$newStatus, $requestId]);
                $pdo->prepare("INSERT INTO request_status_history (request_id, status, comment, created_by) VALUES (?, ?, ?, ?)")
                    ->execute([$requestId, $newStatus, $comment, $currentUser['id']]);
                // ✅ Добавляем уведомление клиенту
                require_once 'includes/notifications.php';
                $statusLabels = ['new' => 'Новый', 'processed' => 'В обработке', 'closed' => 'Закрыт', 'cancelled' => 'Отклонён'];
                $title = "Статус заявки #{$requestId} изменён";
                $message = "Статус вашей заявки изменён на: {$statusLabels[$newStatus]}";
                if ($comment) {
                    $message .= "
Комментарий: " . $comment;
                }
                // Получаем user_id из заявки
                $stmt = $pdo->prepare("SELECT user_id FROM request WHERE id = ?");
                $stmt->execute([$requestId]);
                $requestData = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($requestData) {
                    // ✅ Ссылка с якорем на статус
                    addNotification($pdo, $requestData['user_id'], 'status_change', $title, $message, '/portal/mip/lk_user.php#request-' . $requestId . '-status');
                }
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка']);
            }
            exit;
        }
        if ($action === 'get_messages') {
            $requestId = (int)($_POST['request_id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM request_messages WHERE request_id = ? ORDER BY created_at ASC");
            $stmt->execute([$requestId]);
            echo json_encode(['success' => true, 'messages' => $stmt->fetchAll()]);
            exit;
        }
        if ($action === 'send_message') {
            $requestId = (int)($_POST['request_id'] ?? 0);
            $message = trim($_POST['message'] ?? '');
            if ($requestId && $message) {
                $pdo->prepare("INSERT INTO request_messages (request_id, sender_type, sender_id, message) VALUES (?, 'support', ?, ?)")
                    ->execute([$requestId, $currentUser['id'], $message]);
                // ✅ Добавляем уведомление клиенту о новом сообщении
                require_once 'includes/notifications.php';
                $stmt = $pdo->prepare("SELECT user_id FROM request WHERE id = ?");
                $stmt->execute([$requestId]);
                $requestData = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($requestData) {
                    // ✅ Ссылка с якорем на чат
                    addNotification($pdo, $requestData['user_id'], 'new_message', 'Новое сообщение в чате', "Специалист поддержки ответил на ваше обращение #{$requestId}", '/portal/mip/lk_user.php#request-' . $requestId . '-chat');
                }
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}
// Загрузка данных с сортировкой
$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'desc';
$sql = "SELECT r.*, u.name AS client_name, u.email AS client_email, p.name AS product_name
FROM request r
LEFT JOIN user u ON r.user_id = u.id
LEFT JOIN products p ON r.product_id = p.id
WHERE 1=1";
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
$requests = $stmt->fetchAll();
// История статусов
function getStatusHistory($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM request_status_history WHERE request_id = ? ORDER BY created_at ASC");
    $stmt->execute([$id]);
    return $stmt->fetchAll();
}
// Сообщения чата
function getChatMessages($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM request_messages WHERE request_id = ? ORDER BY created_at ASC");
    $stmt->execute([$id]);
    return $stmt->fetchAll();
}
// Парсинг JSON в таблицу
// Парсинг JSON в таблицу
function parseJsonToTable($message, $type) {
    $data = json_decode($message, true);
    if (!is_array($data)) {
        return '<div class="json-data">' . htmlspecialchars($message) . '</div>';
    }
    $html = '<table class="json-table">';
    
    if ($type === 'r') {
        // Основные поля
        if (!empty($data['product_name'])) {
            $html .= '<tr><td class="json-label">Товар</td><td class="json-value">' . htmlspecialchars($data['product_name']) . '</td></tr>';
        }
        if (!empty($data['quantity'])) {
            $html .= '<tr><td class="json-label">Количество</td><td class="json-value">' . htmlspecialchars($data['quantity']) . ' шт.</td></tr>';
        }
        if (!empty($data['configuration_name']) || !empty($data['configuration'])) {
            $configName = htmlspecialchars($data['configuration_name'] ?? 'Стандартная');
            $configPrice = !empty($data['configuration']['price']) ? number_format($data['configuration']['price'], 0, '.', ' ') . ' ₽' : '';
            $html .= '<tr><td class="json-label">Конфигурация</td><td class="json-value">' . $configName . ($configPrice ? ' <span style="color:#888; font-size:12px;">(' . $configPrice . ')</span>' : '') . '</td></tr>';
        }
        
        // 🔹 Модификации (на кнопку, выводятся столбиком)
        if (!empty($data['modifications']) && is_array($data['modifications'])) {
            $modsHtml = '<div class="mods-column-list">';
            foreach ($data['modifications'] as $mod) {
                $group = htmlspecialchars($mod['group'] ?? '');
                $variant = htmlspecialchars($mod['variant']['name'] ?? '');
                $vPrice = !empty($mod['variant']['price']) ? '+' . number_format($mod['variant']['price'], 0, '.', ' ') . ' ₽' : '';
                $prop = htmlspecialchars($mod['property']['name'] ?? '');
                $pPrice = !empty($mod['property']['price']) ? '+' . number_format($mod['property']['price'], 0, '.', ' ') . ' ₽' : '';
                
                $modsHtml .= '<div class="mod-item">';
                if ($group) $modsHtml .= '<div class="mod-group">' . $group . '</div>';
                if ($variant) $modsHtml .= '<div class="mod-row"><span class="mod-name">' . $variant . '</span>' . ($vPrice ? ' <span class="mod-price">' . $vPrice . '</span>' : '') . '</div>';
                if ($prop) $modsHtml .= '<div class="mod-row"><span class="mod-name">' . $prop . '</span>' . ($pPrice ? ' <span class="mod-price">' . $pPrice . '</span>' : '') . '</div>';
                $modsHtml .= '</div>';
            }
            $modsHtml .= '</div>';
            
            $html .= '<tr><td colspan="2" style="padding: 12px 0;">';
            $html .= '<button type="button" class="btn-toggle-mods" onclick="const c=this.nextElementSibling; c.style.display = (c.style.display === \'none\') ? \'block\' : \'none\'; this.textContent = (c.style.display === \'none\') ? \'Показать модификации\' : \'Скрыть модификации\';">Показать модификации</button>';
            $html .= '<div class="mods-wrapper" style="display:none; margin-top:10px;">' . $modsHtml . '</div>';
            $html .= '</td></tr>';
        }
        
        // Адрес и Итого
        if (!empty($data['address'])) {
            $html .= '<tr><td class="json-label">Адрес</td><td class="json-value">' . htmlspecialchars($data['address']) . '</td></tr>';
        }
        if (!empty($data['total_price'])) {
            $html .= '<tr><td class="json-label">Итого</td><td class="json-value"><strong>' . number_format($data['total_price'], 0, '.', ' ') . ' ₽</strong></td></tr>';
        }
        
    } elseif ($type === 's') {
        if (!empty($data['service_name'])) {
            $html .= '<tr><td class="json-label">Услуга</td><td class="json-value">' . htmlspecialchars($data['service_name']) . '</td></tr>';
        }
        if (!empty($data['price']) && is_numeric($data['price'])) {
            $html .= '<tr><td class="json-label">Стоимость</td><td class="json-value">' . number_format($data['price'], 0, '.', ' ') . ' ₽</td></tr>';
        }
        
    } elseif ($type === 'q') {
        // 🔹 Вопросы (скрыты IP и User-Agent)
        $exclude = ['subject', 'question', 'category', 'user_agent', 'ip_address', 'ip', 'type'];
        if (!empty($data['subject'])) $html .= '<tr><td class="json-label">Тема</td><td class="json-value">' . htmlspecialchars($data['subject']) . '</td></tr>';
        if (!empty($data['question'])) $html .= '<tr><td class="json-label">Вопрос</td><td class="json-value">' . nl2br(htmlspecialchars($data['question'])) . '</td></tr>';
        if (!empty($data['category'])) $html .= '<tr><td class="json-label">Категория</td><td class="json-value">' . htmlspecialchars($data['category']) . '</td></tr>';
        foreach ($data as $key => $value) {
            if (!in_array($key, $exclude) && !empty($value) && !is_array($value)) {
                $html .= '<tr><td class="json-label">' . htmlspecialchars(ucfirst($key)) . '</td><td class="json-value">' . htmlspecialchars($value) . '</td></tr>';
            }
        }
    }
    
    $html .= '</table>';
    return $html;
}
$statusLabels = ['new' => 'Новый', 'processed' => 'В обработке', 'closed' => 'Закрыт', 'cancelled' => 'Отклонён'];
?>
<?php include 'header.php'; ?>

<!-- Подключение специфичных стилей ЛК поддержки -->
<link rel="stylesheet" href="mip/css/style_header_footer.css">
<link rel="stylesheet" href="mip/css/style_mip.css">
<link rel="stylesheet" href="css/style_support.css">
<style>
.email-with-copy { display: inline-flex; align-items: center; gap: 8px; }
.btn-copy-email { background: #e9ecef; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 14px; transition: all 0.2s; }
.btn-copy-email:hover { background: #dee2e6; }
.btn-copy-email:active { transform: scale(0.95); }
.status-badge { background: #f0f0f0; color: #333; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; border: 1px solid #ddd; }
</style>

<div class="lk-content">
<div class="lk-content">
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
    <h1 class="lk-title" style="margin-bottom: 0;">Личный кабинет сотрудника</h1>
    <a href="logout.php" class="btn btn-danger">
        <i class="fas fa-sign-out-alt"></i> Выйти
    </a>
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
<?php if (empty($requests)): ?>
    <div class="no-requests"><p>Нет обращений</p></div>
<?php else: ?>
<?php foreach ($requests as $req):
    $history = getStatusHistory($pdo, $req['id']);
    $messages = getChatMessages($pdo, $req['id']);
?>
<!-- ✅ Добавлен data-request-id -->
<div class="request-card" data-request-id="<?= $req['id'] ?>">
    <div class="request-header">
        <span class="request-id">№<?= $req['id'] ?></span>
        <span class="status-badge">
            <?= $statusLabels[$req['status']] ?? $req['status'] ?>
        </span>
    </div>
    <div class="request-info-grid">
        <div class="info-row">
            <span class="info-label">Клиент</span>
            <span><?= htmlspecialchars($req['client_name'] ?? 'Аноним') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Email</span>
            <span class="email-with-copy">
                <?= htmlspecialchars($req['client_email'] ?? '') ?>
                <?php if (!empty($req['client_email'])): ?>
                <button class="btn-copy-email"
                    onclick="copyToClipboard('<?= htmlspecialchars($req['client_email']) ?>', this)"
                    title="Скопировать email">
                    Копировать
                </button>
                <?php endif; ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Тип</span>
            <span><?= $req['type'] === 'r' ? 'Заказ' : ($req['type'] === 's' ? 'Услуга' : 'Вопрос') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Дата</span>
            <span><?= date('d.m.Y H:i', strtotime($req['datetime'])) ?></span>
        </div>
    </div>
    <div class="request-details">
        <?= parseJsonToTable($req['message'], $req['type']) ?>
    </div>
    <div class="btn-actions">
        <?php if ($req['type'] !== 'q'): ?>
            <button class="btn btn-primary" onclick="openModal('status', <?= $req['id'] ?>)">Статус</button>
            <button class="btn btn-primary" onclick="openModal('chat', <?= $req['id'] ?>)">Чат</button>
            <?php if ($req['status'] !== 'Отклонён'): ?>
                <button class="btn btn-danger" onclick="openModal('reject', <?= $req['id'] ?>)">Отклонить</button>
            <?php endif; ?>
        <?php endif; ?>
        <button class="btn btn-secondary" onclick="toggleHistory(<?= $req['id'] ?>)">История</button>
    </div>
    <div class="status-history" id="history-<?= $req['id'] ?>">
        <div class="history-title">История статусов</div>
        <div class="status-timeline">
        <?php if (!empty($history)): ?>
            <?php foreach ($history as $i => $h): ?>
            <div class="timeline-item <?= $i === count($history) - 1 ? 'current' : 'completed' ?>">
                <div class="timeline-date"><?= date('d.m.Y H:i', strtotime($h['created_at'])) ?></div>
                <div class="timeline-status"><?= $statusLabels[$h['status']] ?? $h['status'] ?></div>
                <?php if ($h['comment']): ?>
                <div class="timeline-comment"><?= htmlspecialchars($h['comment']) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="timeline-item current">
                <div class="timeline-date"><?= date('d.m.Y H:i', strtotime($req['datetime'])) ?></div>
                <div class="timeline-status"><?= $statusLabels[$req['status']] ?></div>
                <div class="timeline-comment">Заказ создан</div>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>
</div>

<!-- Модальные окна -->
<!-- Модальное окно "Статус" -->
<div class="modal-overlay" id="status-overlay" onclick="closeModal('status')"></div>
<div class="modal" id="status-modal">
    <h3>Изменить статус</h3>
    <input type="hidden" id="status-request-id" />
    <select id="status-select" class="modal-input">
        <option value="new">Новый</option>
        <option value="processed">В обработке</option>
        <option value="closed">Закрыт</option>
    </select>
    <textarea id="status-comment" class="modal-input" rows="3" placeholder="Комментарий..."></textarea>
    <div class="modal-buttons">
        <button class="btn btn-secondary" onclick="closeModal('status')">Отмена</button>
        <button class="btn btn-primary" onclick="saveStatus()">Сохранить</button>
    </div>
</div>
<!-- Модальное окно "Отклонить" -->
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
    <div class="chat-input">
        <input type="text" id="chat-message-input" placeholder="Сообщение..." />
        <button class="btn btn-primary" onclick="sendMessage()">Отправить</button>
    </div>
    <div class="modal-buttons">
        <button class="btn btn-secondary" onclick="closeModal('chat')">Закрыть</button>
    </div>
</div>

<script>
function openModal(type, id) {
    document.getElementById(type + '-request-id').value = id;
    document.getElementById(type + '-overlay').style.display = 'block';
    document.getElementById(type + '-modal').style.display = 'block';
    if (type === 'chat') loadMessages(id);
}
function closeModal(type) {
    document.getElementById(type + '-overlay').style.display = 'none';
    document.getElementById(type + '-modal').style.display = 'none';
}
function toggleHistory(id) {
    const el = document.getElementById('history-' + id);
    el.classList.toggle('visible');
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
    } else alert('Ошибка');
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
    } else alert('Ошибка');
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
        data.messages.forEach(m => {
            const cls = m.sender_type === 'support' ? 'chat-support' : 'chat-user';
            box.innerHTML += '<div class="chat-message ' + cls + '"><div>' + m.message + '</div><div class="chat-time">' + new Date(m.created_at).toLocaleString('ru-RU') + '</div></div>';
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
    if (!msg) return;
    const fd = new FormData();
    fd.append('action', 'send_message');
    fd.append('request_id', id);
    fd.append('message', msg);
    const res = await fetch('lk_support.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
        input.value = '';
        loadMessages(id);
        alert('Сообщение отправлено, клиент получит уведомление');
    } else alert('Ошибка');
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
// ✅ ОБРАБОТКА ПЕРЕХОДА К КОНКРЕТНОЙ ЗАЯВКЕ ИЗ УВЕДОМЛЕНИЯ
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash && window.location.hash.startsWith('#request-')) {
        const hash = window.location.hash;
        // Парсим: #request-71-chat или #request-71-status
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
            let targetCard = document.querySelector(`.request-card[data-request-id="${requestId}"]`);
            if (!targetCard) {
                const cards = document.querySelectorAll('.request-card');
                for (let card of cards) {
                    if (card.textContent.includes(`№${requestId}`)) {
                        targetCard = card;
                        break;
                    }
                }
            }
            if (targetCard) {
                // Плавная прокрутка
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Подсветка
                targetCard.style.transition = 'background 0.3s';
                targetCard.style.background = '#fff8e1';
                setTimeout(() => {
                    targetCard.style.background = '';
                }, 2000);
                setTimeout(() => {
                    if (targetType === 'status') {
                        // Открываем модальное окно статуса
                        const statusBtn = targetCard.querySelector('.btn-primary[onclick*="status"]');
                        if (statusBtn) {
                            statusBtn.click();
                        }
                    } else {
                        // Открываем чат
                        const chatBtn = targetCard.querySelector('.btn-primary[onclick*="chat"]');
                        if (chatBtn) {
                            chatBtn.click();
                        }
                    }
                }, 500);
            }
        }
    }
});
</script>

<?php include 'footer.php'; ?>

