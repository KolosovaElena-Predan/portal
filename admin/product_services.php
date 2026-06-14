<?php
$activePage = 'products';
$pageTitle = 'Привязка услуг к товарам | Админ-панель';
require_once 'includes/auth_check.php';

$productId = (int)($_GET['product_id'] ?? 0);
$error = '';
$success = '';

// Получаем информацию о товаре
$product = null;
if ($productId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$product) {
    header('Location: products.php');
    exit;
}

// Получаем список всех услуг
$services = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY name")->fetchAll();

// Получаем уже привязанные услуги
$attachedServices = [];
$stmt = $pdo->prepare("SELECT service_id FROM product_services WHERE product_id = ? AND is_active = 1");
$stmt->execute([$productId]);
$attachedServices = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Обработка сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedServices = $_POST['services'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        // Удаляем старые привязки
        $pdo->prepare("DELETE FROM product_services WHERE product_id = ?")->execute([$productId]);
        
        // Добавляем новые привязки
        $stmt = $pdo->prepare("INSERT INTO product_services (product_id, service_id, is_active) VALUES (?, ?, 1)");
        foreach ($selectedServices as $serviceId) {
            $stmt->execute([$productId, $serviceId]);
        }
        
        $pdo->commit();
        $success = 'Привязка услуг сохранена!';
        
        // Обновляем список привязанных услуг
        $attachedServices = $selectedServices;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Ошибка: ' . $e->getMessage();
    }
}

$pageScript = '
$("#servicesTable").DataTable({ 
    "responsive": true, 
    "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"},
    "order": [[1, "asc"]]
});
';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<style>
.card-header {
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
}
.table thead th {
    background: #f8fafc;
    font-weight: 600;
    font-size: 0.8rem;
    color: #64748b;
}
.service-checkbox {
    width: 40px;
    text-align: center;
}
.service-checkbox input {
    cursor: pointer;
    width: 18px;
    height: 18px;
}
.service-price {
    font-weight: 600;
    color: #059669;
}
.service-info {
    font-size: 0.85rem;
    color: #64748b;
}
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">
                Привязка услуг к товару
                <small class="text-muted"><?= e($product['name']) ?></small>
            </h1>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Доступные услуги</h3>
                        <a href="product_edit.php?id=<?= $productId ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Вернуться к товару
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                Отметьте услуги, которые будут доступны для заказа вместе с этим товаром.
                                При оформлении заказа товара, клиент сможет выбрать эти услуги.
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table id="servicesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th class="service-checkbox">
                                            <input type="checkbox" id="selectAll" title="Выбрать все">
                                        </th>
                                        <th>ID</th>
                                        <th>Название услуги</th>
                                        <th>Стоимость</th>
                                        <th>Срок</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($services as $service): ?>
                                    <?php $isChecked = in_array($service['id'], $attachedServices); ?>
                                    <tr>
                                        <td class="service-checkbox">
                                            <input type="checkbox" name="services[]" value="<?= $service['id'] ?>" class="service-checkbox-item" <?= $isChecked ? 'checked' : '' ?>>
                                        </td>
                                        <td><?= $service['id'] ?></td>
                                        <td>
                                            <strong><?= e($service['name']) ?></strong>
                                            <?php if (!empty($service['short_description'])): ?>
                                                <div class="service-info"><?= e(mb_substr($service['short_description'], 0, 100)) ?>...</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="service-price">
                                            <?php if ($service['price'] > 0): ?>
                                                <?= number_format($service['price'], 0, '.', ' ') ?> ₽
                                            <?php else: ?>
                                                <span class="text-muted">По запросу</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e($service['duration'] ?? '—') ?></td>
                                        <td>
                                            <span class="badge badge-<?= $service['is_active'] ? 'success' : 'danger' ?>">
                                                <?= $service['is_active'] ? 'Активна' : 'Неактивна' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllBtn">
                                    <i class="fas fa-check-square"></i> Выбрать все
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">
                                    <i class="fas fa-square"></i> Снять все
                                </button>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Сохранить привязку
                                </button>
                                <a href="product_edit.php?id=<?= $productId ?>" class="btn btn-secondary">
                                    Отмена
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    // Выделить все
    $('#selectAll').on('change', function() {
        $('.service-checkbox-item').prop('checked', this.checked);
    });
    
    $('#selectAllBtn').on('click', function() {
        $('.service-checkbox-item').prop('checked', true);
        $('#selectAll').prop('checked', true);
    });
    
    $('#deselectAllBtn').on('click', function() {
        $('.service-checkbox-item').prop('checked', false);
        $('#selectAll').prop('checked', false);
    });
    
    // Обновление состояния "Выбрать все" при изменении чекбоксов
    $('.service-checkbox-item').on('change', function() {
        const allChecked = $('.service-checkbox-item:checked').length === $('.service-checkbox-item').length;
        $('#selectAll').prop('checked', allChecked);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>