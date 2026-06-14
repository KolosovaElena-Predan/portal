<?php
$activePage = 'requests';
$pageTitle = 'Заявка #' . $id;

require_once 'includes/auth_check.php';

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
    
    // Проверяем, что статус существует
    if (isset($statusLabels[$newStatus])) {
        try {
            $oldStatus = $request['status'];
            $pdo->prepare("UPDATE request SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
            
            // Записываем в историю
            $pdo->prepare("INSERT INTO request_status_history (request_id, status, comment, created_by) VALUES (?, ?, ?, ?)")
                ->execute([$id, $newStatus, 'Изменено администратором', $_SESSION['user_id']]);
            
            $success = 'Статус обновлён';
            
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
                                            <option value="<?= $code ?>" <?= $request['status'] === $code ? 'selected' : '' ?> style="color: <?= $status['color'] ?>;">
                                                <?= htmlspecialchars($status['name']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-block">Обновить статус</button>
                            <a href="requests.php" class="btn btn-secondary btn-block mt-2">Назад к списку</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>