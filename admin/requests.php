<?php
$activePage = 'requests';
$pageTitle = 'Заявки | Админ-панель';

require_once 'includes/auth_check.php';

$statusFilter = $_GET['status'] ?? '';
$where = $statusFilter ? "WHERE r.status = ?" : "";
$params = $statusFilter ? [$statusFilter] : [];

$stmt = $pdo->prepare("SELECT r.*, u.name as user_name, u.email as user_email, p.name as product_name FROM request r LEFT JOIN user u ON r.user_id = u.id LEFT JOIN products p ON r.product_id = p.id $where ORDER BY r.datetime DESC");
$stmt->execute($params);
$requests = $stmt->fetchAll();

$pageScript = '$("#requestsTable").DataTable({ "order": [[0, "desc"]], "responsive": true, "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"} });';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<style>
    .badge-q { background: #17a2b8; color: white; }
    .badge-r { background: #ffc107; color: #212529; }
    .badge-s { background: #28a745; color: white; }
    .badge-new { background: #dc3545; color: white; }
    .badge-processed { background: #fd7e14; color: white; }
    .badge-closed { background: #28a745; color: white; }
    .badge-cancelled { background: #6c757d; color: white; }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">Заявки пользователей</h1></div>
                <div class="col-sm-6 text-right">
                    <a href="?status=" class="btn btn-sm btn-<?= !$statusFilter ? 'primary' : 'outline-secondary' ?>">Все</a>
                    <a href="?status=new" class="btn btn-sm btn-<?= $statusFilter === 'new' ? 'primary' : 'outline-secondary' ?>">Новые</a>
                    <a href="?status=processed" class="btn btn-sm btn-<?= $statusFilter === 'processed' ? 'primary' : 'outline-secondary' ?>">В работе</a>
                    <a href="?status=closed" class="btn btn-sm btn-<?= $statusFilter === 'closed' ? 'primary' : 'outline-secondary' ?>">Закрытые</a>
                    <a href="?status=cancelled" class="btn btn-sm btn-<?= $statusFilter === 'cancelled' ? 'primary' : 'outline-secondary' ?>">Отклонённые</a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Список заявок</h3> <span class="badge badge-info"><?= count($requests) ?></span></div>
                <div class="card-body">
                    <table id="requestsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Пользователь</th>
                                <th>Тип</th>
                                <th>Статус</th>
                                <th>Товар/Услуга</th>
                                <th>Кол-во</th>
                                <th>Сумма</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $r): 
                                $decoded = json_decode($r['message'], true);
                                $isJson = (json_last_error() === JSON_ERROR_NONE && is_array($decoded));
                                
                                $productName = '';
                                $quantity = '';
                                $totalPrice = '';
                                
                                if ($r['type'] === 'r' && $isJson) {
                                    $productName = $decoded['product_name'] ?? $r['product_name'] ?? '';
                                    $quantity = $decoded['quantity'] ?? '';
                                    $totalPrice = isset($decoded['total_price']) ? number_format($decoded['total_price'], 0, '', ' ') . ' ₽' : (isset($decoded['line_total']) ? number_format($decoded['line_total'], 0, '', ' ') . ' ₽' : '');
                                } elseif ($r['type'] === 's' && $isJson) {
                                    $productName = $decoded['service_name'] ?? '';
                                    $quantity = '1';
                                } elseif ($r['type'] === 'q') {
                                    $productName = $r['product_name'] ?? '';
                                }
                            ?>
                            <tr>
                                <td><?= $r['id'] ?></td>
                                <td>
                                    <strong><?= e($r['user_name'] ?? 'Гость') ?></strong><br>
                                    <small class="text-muted"><?= e($r['user_email'] ?? '') ?></small>
                                </td>
                                <td><span class="badge badge-<?= $r['type'] ?>"><?= ['q'=>'Вопрос','r'=>'Заказ','s'=>'Услуга'][$r['type']] ?? $r['type'] ?></span></td>
                                <td><span class="badge badge-<?= $r['status'] ?>"><?= ['new'=>'Новая','processed'=>'В работе','closed'=>'Закрыта','cancelled'=>'Отклонена'][$r['status']] ?? $r['status'] ?></span></td>
                                <td><?= e(mb_strimwidth($productName, 0, 40, '...')) ?></td>
                                <td class="text-center"><?= $quantity ?></td>
                                <td class="text-right"><?= $totalPrice ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($r['datetime'])) ?></td>
                                <td>
                                    <a href="request_edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                    <a href="request_delete.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>