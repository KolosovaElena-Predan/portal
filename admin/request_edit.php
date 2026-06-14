<?php
$activePage = 'requests';
$pageTitle = 'Заявка #' . $id;

require_once 'includes/auth_check.php';

// ============================================
// ПОИСК ФАЙЛА notifications.php
// ============================================
$notificationsPath = null;

// Вариант 1: ../mip/includes/notifications.php
if (file_exists(__DIR__ . '/../mip/includes/notifications.php')) {
    $notificationsPath = __DIR__ . '/../mip/includes/notifications.php';
}
// Вариант 2: ../includes/notifications.php
elseif (file_exists(__DIR__ . '/../includes/notifications.php')) {
    $notificationsPath = __DIR__ . '/../includes/notifications.php';
}
// Вариант 3: ./includes/notifications.php
elseif (file_exists(__DIR__ . '/includes/notifications.php')) {
    $notificationsPath = __DIR__ . '/includes/notifications.php';
}
// Вариант 4: ../../includes/notifications.php
elseif (file_exists(__DIR__ . '/../../includes/notifications.php')) {
    $notificationsPath = __DIR__ . '/../../includes/notifications.php';
}
// Вариант 5: ../../mip/includes/notifications.php
elseif (file_exists(__DIR__ . '/../../mip/includes/notifications.php')) {
    $notificationsPath = __DIR__ . '/../../mip/includes/notifications.php';
}

if ($notificationsPath) {
    require_once $notificationsPath;
} else {
    // Если файл не найден, создаём заглушки для функций
    error_log("Файл notifications.php не найден. Создаём заглушки.");
    
    if (!function_exists('addNotification')) {
        function addNotification($pdo, $userId, $type, $title, $message, $link = null) {
            try {
                $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, link, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$userId, $type, $title, $message, $link]);
                return $pdo->lastInsertId();
            } catch (PDOException $e) {
                error_log("Add notification error: " . $e->getMessage());
                return false;
            }
        }
    }
    
    if (!function_exists('sendEmailNotification')) {
        function sendEmailNotification($email, $name, $subject, $htmlMessage) {
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'example.com') . "\r\n";
            mail($email, $subject, $htmlMessage, $headers);
        }
    }
}

// Загружаем динамические статусы
$statusLabels = [];
$statusColors = [];
$statusList = [];

$stmtStatuses = $pdo->query("SELECT * FROM request_statuses WHERE is_active = 1 ORDER BY sort_order");
$statusesList = $stmtStatuses->fetchAll(PDO::FETCH_ASSOC);
foreach ($statusesList as $s) {
    $statusLabels[$s['code']] = $s['name'];
    $statusColors[$s['code']] = $s['color'];
    $statusList[$s['code']] = $s;
}

$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';

// Получаем текущие данные заявки
try {
    $stmt = $pdo->prepare("
        SELECT r.*, u.name as user_name, u.email as user_email, p.name as product_name 
        FROM request r 
        LEFT JOIN user u ON r.user_id = u.id 
        LEFT JOIN products p ON r.product_id = p.id 
        WHERE r.id = ?
    ");
    $stmt->execute([$id]);
    $request = $stmt->fetch();
    if (!$request) redirect('requests.php');
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}

// Обработка обновления статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? '';
    $comment = trim($_POST['comment'] ?? '');
    
    // Проверяем, что статус существует
    if (isset($statusLabels[$newStatus])) {
        try {
            $oldStatus = $request['status'];
            
            // Обновляем статус
            $pdo->prepare("UPDATE request SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
            
            // Записываем в историю
            $pdo->prepare("INSERT INTO request_status_history (request_id, status, comment, created_by) VALUES (?, ?, ?, ?)")
                ->execute([$id, $newStatus, $comment ?: 'Статус изменён администратором', $_SESSION['user_id']]);
            
            // ============================================
            // ОТПРАВКА УВЕДОМЛЕНИЙ КЛИЕНТУ
            // ============================================
            $title = "Статус заявки #{$id} изменён";
            $message = "Статус вашей заявки изменён на: " . ($statusLabels[$newStatus] ?? $newStatus);
            if ($comment) {
                $message .= "\n\nКомментарий специалиста: " . $comment;
            }
            $link = "/mip/lk_user.php#request-{$id}-status";
            
            // Добавляем уведомление в систему
            if (function_exists('addNotification')) {
                addNotification($pdo, $request['user_id'], 'status_change', $title, $message, $link);
            }
            
            // Отправляем email клиенту
            if (!empty($request['user_email'])) {
                $emailSubject = "Статус заявки #{$id} изменён";
                $emailBody = "
                <html>
                <head><meta charset='utf-8'></head>
                <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
                    <h2 style='color: #1a1982;'>Здравствуйте, " . htmlspecialchars($request['user_name'] ?? 'Клиент') . "!</h2>
                    <p>Статус вашей заявки <strong>#{$id}</strong> изменён:</p>
                    <p style='background: #f8f9fa; padding: 12px; border-radius: 6px; border-left: 4px solid {$statusColors[$newStatus]};'>
                        <strong>" . ($statusLabels[$newStatus] ?? $newStatus) . "</strong>
                    </p>
                    " . ($comment ? "<p><strong>Комментарий специалиста:</strong><br>" . nl2br(htmlspecialchars($comment)) . "</p>" : "") . "
                    <p style='margin-top: 24px;'>
                        <a href='https://" . $_SERVER['HTTP_HOST'] . "/mip/lk_user.php#request-{$id}-status' 
                           style='background: #1a1982; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            Перейти к заявке
                        </a>
                    </p>
                    <hr style='border: none; border-top: 1px solid #eee; margin: 24px 0;'>
                    <p style='font-size: 12px; color: #666;'>
                        Это автоматическое уведомление от МИП «НПЦ ПИТиА».
                    </p>
                </body>
                </html>";
                
                if (function_exists('sendEmailNotification')) {
                    sendEmailNotification($request['user_email'], $request['user_name'] ?? 'Клиент', $emailSubject, $emailBody);
                } else {
                    // Отправляем через стандартную mail()
                    $headers = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                    $headers .= "From: no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'example.com') . "\r\n";
                    mail($request['user_email'], $emailSubject, $emailBody, $headers);
                }
            }
            
            $success = 'Статус обновлён, клиент получил уведомление';
            
            // Перезагружаем данные
            $stmt->execute([$id]);
            $request = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    } else {
        $error = 'Неверный статус';
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Заявка #<?= $request['id'] ?></h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Детали заявки</h3></div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr><th width="30%">Тип</th><td>
                                    <?php 
                                    $typeIcon = ['q' => '❓', 'r' => '🛒', 's' => '⚙️', 'wl' => '⏳'][$request['type']] ?? '';
                                    $typeName = ['q' => 'Вопрос', 'r' => 'Заказ', 's' => 'Услуга', 'wl' => 'Лист ожидания'][$request['type']] ?? e($request['type']);
                                    echo $typeIcon . ' ' . $typeName;
                                    ?>
                                </td></tr>
                                <tr><th>Пользователь</th><td><?= e($request['user_name']) ?><br><small class="text-muted"><?= e($request['user_email']) ?></small></td></tr>
                                <tr><th>Товар/Услуга</th><td><?= e($request['product_name'] ?? '—') ?></td></tr>
                                <tr><th>Дата создания</th><td><?= date('d.m.Y H:i', strtotime($request['datetime'])) ?></td></tr>
                                <tr><th>Сообщение</th><td><pre style="white-space: pre-wrap; background: #f8f9fa; padding: 10px; border-radius: 5px;"><?= e($request['message']) ?></pre></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <form method="POST" class="card">
                        <div class="card-header" style="background: <?= $statusColors[$request['status']] ?? '#6c757d' ?>; color: white;">
                            <h3 class="card-title">Статус: <?= $statusLabels[$request['status']] ?? $request['status'] ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Изменить статус</label>
                                <select name="status" class="form-control">
                                    <?php foreach ($statusList as $code => $status): ?>
                                        <?php if ($code !== 'waiting'): ?>
                                            <option value="<?= $code ?>" <?= $request['status'] === $code ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($status['name']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Комментарий (будет отправлен клиенту)</label>
                                <textarea name="comment" class="form-control" rows="3" placeholder="Необязательно..."></textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-block">Обновить статус</button>
                            <a href="requests.php" class="btn btn-secondary btn-block mt-2">Назад к списку</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- История статусов -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">История изменений статуса</h3></div>
                        <div class="card-body">
                            <?php
                            $stmtHistory = $pdo->prepare("
                                SELECT h.*, rs.name as status_name, rs.color as status_color, u.name as admin_name
                                FROM request_status_history h
                                LEFT JOIN request_statuses rs ON h.status = rs.code
                                LEFT JOIN user u ON h.created_by = u.id
                                WHERE h.request_id = ?
                                ORDER BY h.created_at ASC
                            ");
                            $stmtHistory->execute([$id]);
                            $history = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <?php if (!empty($history)): ?>
                                <div class="timeline">
                                    <?php foreach ($history as $h): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-date"><?= date('d.m.Y H:i', strtotime($h['created_at'])) ?></div>
                                            <div class="timeline-status" style="color: <?= $h['status_color'] ?? '#333' ?>;">
                                                <strong><?= $h['status_name'] ?? $h['status'] ?></strong>
                                            </div>
                                            <?php if ($h['comment']): ?>
                                                <div class="timeline-comment"><?= nl2br(htmlspecialchars($h['comment'])) ?></div>
                                            <?php endif; ?>
                                            <?php if ($h['admin_name']): ?>
                                                <div class="timeline-admin">Администратор: <?= htmlspecialchars($h['admin_name']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Нет записей в истории</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.timeline-item {
    position: relative;
    padding-left: 30px;
    padding-bottom: 20px;
    border-left: 2px solid #e0e8e5;
    margin-bottom: 0;
}
.timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 0;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #00a896;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e0e8e5;
}
.timeline-date {
    font-size: 12px;
    color: #4a6a65;
    margin-bottom: 4px;
}
.timeline-status {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 5px;
}
.timeline-comment {
    font-size: 13px;
    color: #4a6a65;
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 6px;
    margin: 8px 0;
}
.timeline-admin {
    font-size: 11px;
    color: #4a6a65;
    margin-top: 4px;
}
</style>

<?php require_once 'includes/footer.php'; ?>