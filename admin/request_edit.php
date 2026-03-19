<?php
$activePage = 'requests';
$pageTitle = 'Заявка #' . $id;

require_once 'includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? 'new';
    try {
        $stmt = $pdo->prepare("UPDATE request SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $success = 'Статус обновлён';
    } catch (PDOException $e) {
        $error = 'Ошибка БД: ' . $e->getMessage();
    }
}

try {
    $stmt = $pdo->prepare("SELECT r.*, u.name as user_name, u.email as user_email, p.name as product_name FROM request r LEFT JOIN user u ON r.user_id = u.id LEFT JOIN products p ON r.product_id = p.id WHERE r.id = ?");
    $stmt->execute([$id]);
    $request = $stmt->fetch();
    if (!$request) redirect('requests.php');
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
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
                                <tr><th width="30%">Тип</th><td><?= ['q'=>'Вопрос','r'=>'Заказ','s'=>'Услуга'][$request['type']] ?? e($request['type']) ?></td></tr>
                                <tr><th>Пользователь</th><td><?= e($request['user_name']) ?><br><small class="text-muted"><?= e($request['user_email']) ?></small></td></tr>
                                <tr><th>Товар</th><td><?= e($request['product_name'] ?? '—') ?></td></tr>
                                <tr><th>Дата</th><td><?= date('d.m.Y H:i', strtotime($request['datetime'])) ?></td></tr>
                                <tr><th>Сообщение</th><td><pre style="white-space: pre-wrap;"><?= e($request['message']) ?></pre></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <form method="POST" class="card">
                        <div class="card-header bg-warning"><h3 class="card-title">Статус</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Изменить статус</label>
                                <select name="status" class="form-control">
                                    <option value="new" <?= $request['status'] === 'new' ? 'selected' : '' ?>>Новая</option>
                                    <option value="processed" <?= $request['status'] === 'processed' ? 'selected' : '' ?>>В работе</option>
                                    <option value="closed" <?= $request['status'] === 'closed' ? 'selected' : '' ?>>Закрыта</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-warning btn-block">Обновить</button>
                            <a href="requests.php" class="btn btn-secondary btn-block mt-2">Назад</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>