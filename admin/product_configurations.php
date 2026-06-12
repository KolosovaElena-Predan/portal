<?php
$activePage = 'products';
$pageTitle = 'Комплектации товаров | Админ-панель';
require_once 'includes/auth_check.php';

$productId = (int)($_GET['product_id'] ?? 0);
$error = '';
$success = '';

// Обработка добавления/редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $characteristics = trim($_POST['characteristics'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_main = isset($_POST['is_main']) ? 1 : 0;
    
    // НОВЫЕ ПОЛЯ
    $stock = (int)($_POST['stock'] ?? 0);
    $is_made_to_order = isset($_POST['is_made_to_order']) ? 1 : 0;
    $lead_time = trim($_POST['lead_time'] ?? '');
    if ($is_made_to_order) $stock = -1;

    if (!$name) {
        $error = 'Название комплектации обязательно';
    } elseif ($price < 0) {
        $error = 'Цена не может быть отрицательной';
    } else {
        try {
            $charsArray = [];
            if ($characteristics) {
                foreach (explode("\n", $characteristics) as $line) {
                    $parts = explode(':', trim($line), 2);
                    if (count($parts) === 2) $charsArray[trim($parts[0])] = trim($parts[1]);
                }
            }
            // Добавляем новые поля в JSON
            $charsArray['stock'] = $stock;
            $charsArray['made_to_order'] = (bool)$is_made_to_order;
            if ($lead_time) $charsArray['lead_time'] = $lead_time;
            
            $charsJson = json_encode($charsArray, JSON_UNESCAPED_UNICODE);

            if (isset($_POST['id']) && $_POST['id'] > 0) {
                $stmt = $pdo->prepare("UPDATE product_configurations SET name=?, price=?, characteristics=?, sort_order=?, is_main=?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$name, $price, $charsJson, $sort_order, $is_main, $_POST['id']]);
                $success = 'Комплектация обновлена!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO product_configurations (product_id, name, price, characteristics, sort_order, is_main, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$productId, $name, $price, $charsJson, $sort_order, $is_main]);
                $success = 'Комплектация добавлена!';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

// Обработка удаления
if (isset($_POST['delete']) && isset($_POST['id'])) {
    try {
        $pdo->prepare("DELETE FROM product_configurations WHERE id = ?")->execute([$_POST['id']]);
        $success = 'Комплектация удалена!';
    } catch (PDOException $e) {
        $error = 'Ошибка удаления: ' . $e->getMessage();
    }
}

$product = null;
if ($productId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
}

$configurations = [];
if ($productId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM product_configurations WHERE product_id = ? ORDER BY sort_order, id");
    $stmt->execute([$productId]);
    $configurations = $stmt->fetchAll();
}

$editConfig = null;
if (isset($_GET['edit']) && $_GET['edit'] > 0) {
    $stmt = $pdo->prepare("SELECT * FROM product_configurations WHERE id = ? AND product_id = ?");
    $stmt->execute([$_GET['edit'], $productId]);
    $editConfig = $stmt->fetch();
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Комплектации товара #<?= $productId ?></h1></div></div>
    <section class="content"><div class="container-fluid">
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
        
        <?php if ($product): ?>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><?= $editConfig ? 'Редактировать' : 'Добавить' ?> комплектацию</h3></div>
                    <form method="POST" class="card-body">
                        <?php if ($editConfig): ?><input type="hidden" name="id" value="<?= $editConfig['id'] ?>"><?php endif; ?>
                        
                        <div class="form-group"><label>Название *</label><input type="text" name="name" class="form-control" value="<?= e($editConfig['name'] ?? '') ?>" required></div>
                        <div class="form-group"><label>Цена (₽) *</label><input type="number" step="0.01" min="0" name="price" class="form-control" value="<?= $editConfig['price'] ?? 0 ?>" required></div>
                        
                        <div class="form-group">
                            <label>Остаток на складе *</label>
                            <input type="number" min="-1" name="stock" id="config_stock" class="form-control" value="<?= ($editConfig && !($editConfig['characteristics'] && json_decode($editConfig['characteristics'], true)['made_to_order'] ?? false)) ? (json_decode($editConfig['characteristics'], true)['stock'] ?? 0) : 0 ?>" <?= ($editConfig && (json_decode($editConfig['characteristics'], true)['made_to_order'] ?? false)) ? 'disabled' : '' ?>>
                            <small class="text-muted">-1 = только под заказ</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="is_made_to_order" id="config_made_to_order" value="1" <?= ($editConfig && (json_decode($editConfig['characteristics'], true)['made_to_order'] ?? false)) ? 'checked' : '' ?>>
                                <label for="config_made_to_order" class="custom-control-label"><i class="fas fa-industry"></i> Только под заказ (без листа ожидания)</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Срок изготовления</label>
                            <input type="text" name="lead_time" class="form-control" value="<?= htmlspecialchars(json_decode($editConfig['characteristics'] ?? '{}', true)['lead_time'] ?? '') ?>" placeholder="Напр: 14-21 дней">
                        </div>
                        
                        <div class="form-group"><label>Характеристики (каждая с новой строки)</label><textarea name="characteristics" class="form-control" rows="5" placeholder="Мощность: 1000 Вт&#10;Напряжение: 220В&#10;Вес: 5 кг"><?php 
                            if ($editConfig && $editConfig['characteristics']) {
                                $chars = json_decode($editConfig['characteristics'], true) ?? [];
                                foreach ($chars as $k => $v) {
                                    if (!in_array($k, ['stock', 'made_to_order', 'lead_time'])) echo "$k: $v\n";
                                }
                            }
                        ?></textarea><small class="text-muted">Формат: Ключ: Значение</small></div>
                        
                        <div class="form-group"><label>Порядок сортировки</label><input type="number" name="sort_order" class="form-control" value="<?= $editConfig['sort_order'] ?? 0 ?>"></div>
                        <div class="form-group"><div class="custom-control custom-checkbox"><input class="custom-control-input" type="checkbox" name="is_main" id="is_main" value="1" <?= ($editConfig['is_main'] ?? 0) ? 'checked' : '' ?>><label for="is_main" class="custom-control-label">Основная комплектация</label></div></div>
                        
                        <button type="submit" name="save" class="btn btn-primary"><i class="fas fa-save"></i> <?= $editConfig ? 'Сохранить' : 'Добавить' ?></button>
                        <?php if ($editConfig): ?><a href="product_configurations.php?product_id=<?= $productId ?>" class="btn btn-secondary">Отмена</a><?php endif; ?>
                    </form>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Список комплектаций</h3></div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Цена</th>
                                    <th>Остаток</th>
                                    <th>Под заказ</th>
                                    <th>Срок</th>
                                    <th>Характеристики</th>
                                    <th>Порядок</th>
                                    <th>Основная</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($configurations as $cfg): ?>
                                <?php $chars = json_decode($cfg['characteristics'], true) ?? []; $cStock = $chars['stock'] ?? 0; $cIsMTO = $chars['made_to_order'] ?? false; $cLeadTime = $chars['lead_time'] ?? ''; ?>
                                <tr>
                                    <td><strong><?= e($cfg['name']) ?></strong></td>
                                    <td><?= number_format($cfg['price'], 0, '.', ' ') ?> ₽</td>
                                    <td>
                                        <?php if ($cIsMTO): ?>
                                            <span class="badge badge-info"><i class="fas fa-industry"></i> Под заказ</span>
                                        <?php else: ?>
                                            <span class="badge badge-<?= $cStock > 10 ? 'success' : ($cStock > 0 ? 'warning' : 'danger') ?>"><?= $cStock ?> шт.</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $cIsMTO ? '<span class="badge badge-info">✓</span>' : '<span class="text-muted">—</span>' ?></td>
                                    <td><small><?= e($cLeadTime ?: '—') ?></small></td>
                                    <td><?php foreach ($chars as $k => $v): if (!in_array($k, ['stock', 'made_to_order', 'lead_time'])): ?><small><?= e($k) ?>: <?= e($v) ?></small><br><?php endif; ?><?php endforeach; ?></td>
                                    <td><?= $cfg['sort_order'] ?></td>
                                    <td><?= !empty($cfg['is_main']) ? '<span class="badge badge-success">✓</span>' : '<span class="badge badge-secondary">—</span>' ?></td>
                                    <td>
                                        <a href="product_configurations.php?product_id=<?= $productId ?>&edit=<?= $cfg['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить?')"><input type="hidden" name="delete" value="1"><input type="hidden" name="id" value="<?= $cfg['id'] ?>"><button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3"><a href="product_edit.php?id=<?= $productId ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Назад к товару</a></div>
        <?php else: ?>
        <div class="alert alert-warning">Товар не найден</div>
        <a href="products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> К списку товаров</a>
        <?php endif; ?>
    </div></section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('config_made_to_order');
    const stockInput = document.getElementById('config_stock');
    
    if (checkbox && stockInput) {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                stockInput.value = -1;
                stockInput.disabled = true;
            } else {
                stockInput.value = 0;
                stockInput.disabled = false;
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>