<?php
$activePage = 'services_products_links';
$pageTitle = 'Связь товаров и услуг | Админ-панель';
require_once 'includes/auth_check.php';

$error = '';
$success = '';

// Получаем список всех товаров
$products = $pdo->query("SELECT id, name FROM products WHERE status = 'active' ORDER BY name")->fetchAll();

// Получаем список всех услуг
$services = $pdo->query("SELECT id, name, price FROM services WHERE is_active = 1 ORDER BY name")->fetchAll();

// Получаем текущие связи
$links = [];
$stmt = $pdo->query("
    SELECT ps.*, p.name as product_name, s.name as service_name, s.price as service_price
    FROM product_services ps
    JOIN products p ON ps.product_id = p.id
    JOIN services s ON ps.service_id = s.id
    ORDER BY p.name, s.name
");
$links = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка удаления связи
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $linkId = (int)$_GET['delete'];
    try {
        $pdo->prepare("DELETE FROM product_services WHERE id = ?")->execute([$linkId]);
        $success = 'Связь удалена';
        header('Location: services_products_links.php');
        exit;
    } catch (PDOException $e) {
        $error = 'Ошибка удаления: ' . $e->getMessage();
    }
}

// Обработка добавления связи
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $serviceId = (int)($_POST['service_id'] ?? 0);
    
    if ($productId > 0 && $serviceId > 0) {
        try {
            // Проверяем, существует ли уже такая связь
            $stmt = $pdo->prepare("SELECT id FROM product_services WHERE product_id = ? AND service_id = ?");
            $stmt->execute([$productId, $serviceId]);
            if ($stmt->fetch()) {
                $error = 'Такая связь уже существует';
            } else {
                $stmt = $pdo->prepare("INSERT INTO product_services (product_id, service_id, is_active) VALUES (?, ?, 1)");
                $stmt->execute([$productId, $serviceId]);
                $success = 'Связь добавлена';
                header('Location: services_products_links.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Ошибка добавления: ' . $e->getMessage();
        }
    } else {
        $error = 'Выберите товар и услугу';
    }
}

// Обработка массового добавления
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_multiple') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $serviceIds = $_POST['service_ids'] ?? [];
    
    if ($productId > 0 && !empty($serviceIds)) {
        try {
            $pdo->beginTransaction();
            $added = 0;
            foreach ($serviceIds as $serviceId) {
                $serviceId = (int)$serviceId;
                if ($serviceId <= 0) continue;
                
                $stmt = $pdo->prepare("SELECT id FROM product_services WHERE product_id = ? AND service_id = ?");
                $stmt->execute([$productId, $serviceId]);
                if (!$stmt->fetch()) {
                    $stmt = $pdo->prepare("INSERT INTO product_services (product_id, service_id, is_active) VALUES (?, ?, 1)");
                    $stmt->execute([$productId, $serviceId]);
                    $added++;
                }
            }
            $pdo->commit();
            $success = "Добавлено $added новых связей";
            header('Location: services_products_links.php');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Ошибка добавления: ' . $e->getMessage();
        }
    } else {
        $error = 'Выберите товар и хотя бы одну услугу';
    }
}

$pageScript = '
$("#linksTable").DataTable({ 
    "responsive": true, 
    "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"},
    "order": [[0, "asc"]]
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
.service-price {
    font-weight: 600;
    color: #059669;
}
.link-row:hover {
    background: #f8fafc;
}
.badge-linked {
    background: #dbeafe;
    color: #1e40af;
}
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Связь товаров и услуг</h1>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <!-- Форма добавления связи -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Добавить связь</h3>
                </div>
                <div class="card-body">
                    <form method="POST" class="row align-items-end">
                        <input type="hidden" name="action" value="add">
                        <div class="col-md-5">
                            <label>Товар</label>
                            <select name="product_id" class="form-control" required>
                                <option value="">-- Выберите товар --</option>
                                <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (ID: <?= $p['id'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label>Услуга</label>
                            <select name="service_id" class="form-control" required>
                                <option value="">-- Выберите услугу --</option>
                                <?php foreach ($services as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= number_format($s['price'], 0, '.', ' ') ?> ₽)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> Добавить
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            
            
            <!-- Список связей -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Существующие связи</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="linksTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Товар</th>
                                    <th>Услуга</th>
                                    <th>Стоимость услуги</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($links as $link): ?>
                                <tr class="link-row">
                                    <td><?= $link['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($link['product_name']) ?></strong>
                                        <br><small class="text-muted">ID: <?= $link['product_id'] ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($link['service_name']) ?></td>
                                    <td class="service-price"><?= number_format($link['service_price'], 0, '.', ' ') ?> ₽</td>
                                    <td>
                                        <span class="badge badge-linked">
                                            <i class="fas fa-link"></i> Привязана
                                        </span>
                                    </td>
                                    <td>
                                        <a href="services_products_links.php?delete=<?= $link['id'] ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Удалить связь товара «<?= htmlspecialchars($link['product_name']) ?>» с услугой «<?= htmlspecialchars($link['service_name']) ?>»?')">
                                            <i class="fas fa-trash"></i> Удалить
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($links)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle"></i> Нет привязанных услуг
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>