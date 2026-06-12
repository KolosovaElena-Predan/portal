<?php
$activePage = 'products';
$pageTitle = 'Товары | Админ-панель';
require_once 'includes/auth_check.php';
$error = '';
$success = '';

// Обработка массовых действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['bulk_action']) && isset($_POST['product_ids']) && is_array($_POST['product_ids'])) {
        $productIds = array_filter($_POST['product_ids'], 'is_numeric');
        if (!empty($productIds)) {
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            
            switch ($_POST['bulk_action']) {
				case 'set_made_to_order':
    $pdo->prepare("UPDATE products SET is_made_to_order = 1, stock = -1 WHERE id IN ($placeholders)")->execute($productIds);
    $success = 'Товары отмечены как "под заказ"';
    break;
case 'unset_made_to_order':
    $pdo->prepare("UPDATE products SET is_made_to_order = 0 WHERE id IN ($placeholders)")->execute($productIds);
    $success = 'Снята отметка "под заказ"';
    break;
                case 'delete':
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id IN ($placeholders)");
                    $stmt->execute($productIds);
                    if ($stmt->fetchColumn() > 0) {
                        $error = 'Некоторые товары есть в заказах и не могут быть удалены';
                    } else {
                        $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id IN ($placeholders)");
                        $stmt->execute($productIds);
                        while ($img = $stmt->fetch()) {
                            @unlink('../mip/' . $img['image_url']);
                        }
                        $pdo->prepare("DELETE FROM product_images WHERE product_id IN ($placeholders)")->execute($productIds);
                        $pdo->prepare("DELETE FROM products WHERE id IN ($placeholders)")->execute($productIds);
                        $success = 'Товары успешно удалены';
                    }
                    break;
                case 'active':
                    $pdo->prepare("UPDATE products SET status = 'active' WHERE id IN ($placeholders)")->execute($productIds);
                    $success = 'Товары активированы';
                    break;
                case 'inactive':
                    $pdo->prepare("UPDATE products SET status = 'inactive' WHERE id IN ($placeholders)")->execute($productIds);
                    $success = 'Товары деактивированы';
                    break;
                case 'set_new':
                    $pdo->prepare("UPDATE products SET is_new = 1 WHERE id IN ($placeholders)")->execute($productIds);
                    $success = 'Товары отмечены как новинки';
                    break;
                case 'unset_new':
                    $pdo->prepare("UPDATE products SET is_new = 0 WHERE id IN ($placeholders)")->execute($productIds);
                    $success = 'Новинки сняты с товаров';
                    break;
                case 'set_slider':
                    $pdo->prepare("UPDATE products SET is_slider = 1 WHERE id IN ($placeholders)")->execute($productIds);
                    $success = 'Товары добавлены в слайдер';
                    break;
                case 'unset_slider':
                    $pdo->prepare("UPDATE products SET is_slider = 0 WHERE id IN ($placeholders)")->execute($productIds);
                    $success = 'Товары удалены из слайдера';
                    break;
            }
        }
    }
}

$products = $pdo->query("
    SELECT p.*, c.name as category_name,
           (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as images_count
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
")->fetchAll();

$pageScript = '
$("#productsTable").DataTable({ 
    "responsive": true, 
    "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"},
    "columnDefs": [
        { "orderable": false, "targets": [1, 11] }
    ]
});

// Выделение строк при выборе чекбокса
$("#productsTable tbody").on("change", ".product-checkbox", function() {
    $(this).closest("tr").toggleClass("selected", this.checked);
});

// Выделить всё
$("#select-all").on("change", function() {
    var isChecked = this.checked;
    $(".product-checkbox").each(function() {
        this.checked = isChecked;
        $(this).closest("tr").toggleClass("selected", isChecked);
    });
    updateBulkActionsVisibility();
});

// Обновление видимости кнопок массовых действий
function updateBulkActionsVisibility() {
    var checkedCount = $(".product-checkbox:checked").length;
    if (checkedCount > 0) {
        $("#bulk-actions-panel").show();
        $("#selected-count").text(checkedCount);
    } else {
        $("#bulk-actions-panel").hide();
    }
}

// Обновление счетчика при изменении
$(document).on("change", ".product-checkbox", function() {
    updateBulkActionsVisibility();
});
';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<style>
#bulk-actions-panel {
    display: none;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px 15px;
    margin-bottom: 20px;
}

#bulk-actions-panel .selected-info {
    display: inline-block;
    margin-right: 15px;
    font-weight: bold;
}

#bulk-actions-panel .btn-group {
    margin-right: 5px;
}

.table tbody tr.selected {
    background-color: #f5f5f5;
}

.checkbox-cell {
    width: 40px;
    text-align: center;
}

.product-checkbox {
    cursor: pointer;
}
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Управление товарами</h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
            
            <!-- Панель массовых действий -->
            <div id="bulk-actions-panel">
                <div class="selected-info">
                    Выбрано: <strong id="selected-count">0</strong> товаров
                </div>
                <div class="btn-group">
                    <form method="POST" id="bulk-form" style="display: inline;">
                        <input type="hidden" name="bulk_action" id="bulk_action" value="">
                        
                        <button type="button" class="btn btn-success btn-sm" onclick="setBulkAction('active')">
                            <i class="fas fa-check"></i> Активировать
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="setBulkAction('inactive')">
                            <i class="fas fa-ban"></i> Деактивировать
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="setBulkAction('set_new')">
                            <i class="fas fa-star"></i> Отметить новинки
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="setBulkAction('unset_new')">
                            <i class="far fa-star"></i> Снять новинки
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="setBulkAction('set_slider')">
                            <i class="fas fa-images"></i> В слайдер
                        </button>
                        <button type="button" class="btn btn-dark btn-sm" onclick="setBulkAction('unset_slider')">
                            <i class="fas fa-sliders-h"></i> Из слайдера
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="setBulkAction('delete')">
                            <i class="fas fa-trash"></i> Удалить
                        </button>
						<button type="button" class="btn btn-info btn-sm" onclick="setBulkAction('set_made_to_order')">
								<i class="fas fa-industry"></i> Под заказ
							</button>
							<button type="button" class="btn btn-light btn-sm" onclick="setBulkAction('unset_made_to_order')">
								<i class="fas fa-box"></i> Со склада
							</button>
                        <button type="button" class="btn btn-default btn-sm" onclick="clearSelection()">
                            <i class="fas fa-times"></i> Очистить
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Список товаров</h3>
                    <a href="product_add.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Добавить товар
                    </a>
                </div>
                <div class="card-body">
                    <table id="productsTable" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="checkbox-cell"><input type="checkbox" id="select-all"></th>
            <th>ID</th>
            <th>Фото</th>
            <th>Название</th>
            <th>Категория</th>
            <th>Цена</th>
            <th>Остаток (общий)</th>
            <th>Комплектаций</th>
            <th>В ожидании</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        // Получаем доп. информацию для каждого товара
        $stmtConfigs = $pdo->prepare("SELECT COUNT(*) FROM product_configurations WHERE product_id = ?");
        $stmtWaiting = $pdo->prepare("SELECT COALESCE(SUM(requested_quantity), 0) FROM request WHERE product_id = ? AND type = 'wl' AND status = 'waiting'");
        $stmtTotalStock = $pdo->prepare("SELECT COALESCE(SUM(stock), 0) FROM product_configurations WHERE product_id = ?");
        
        foreach ($products as $p): 
            $stmtConfigs->execute([$p['id']]);
            $configsCount = $stmtConfigs->fetchColumn();
            
            $stmtWaiting->execute([$p['id']]);
            $waitingQty = $stmtWaiting->fetchColumn();
            
            // Общий остаток: если есть комплектации — суммируем их, иначе берём базовый
            if ($configsCount > 0) {
                $stmtTotalStock->execute([$p['id']]);
                $totalStock = $stmtTotalStock->fetchColumn();
            } else {
                $totalStock = $p['stock'];
            }
            
            $isMadeToOrder = !empty($p['is_made_to_order']) || $totalStock == -1;
        ?>
        <tr>
            <td class="checkbox-cell">
                <input type="checkbox" name="product_ids[]" value="<?= $p['id'] ?>" class="product-checkbox">
            </td>
            <td><?= $p['id'] ?></td>
            <td>
                <?php
                $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_main = 1 LIMIT 1");
                $stmt->execute([$p['id']]);
                $mainImg = $stmt->fetch();
                if ($mainImg): ?>
                    <img src="../mip/<?= e($mainImg['image_url']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                <?php else: ?>
                    <span class="text-muted">—</span>
                <?php endif; ?>
            </td>
            <td>
                <?= e($p['name']) ?>
                <?php if ($isMadeToOrder): ?>
                    <br><small class="badge badge-info"><i class="fas fa-industry"></i> Под заказ</small>
                <?php endif; ?>
            </td>
            <td><span class="badge badge-info"><?= e($p['category_name'] ?? '—') ?></span></td>
            <td><?= number_format($p['base_price'], 0, '.', ' ') ?> ₽</td>
            <td>
                <?php if ($isMadeToOrder): ?>
                    <span class="badge badge-info"><i class="fas fa-industry"></i> Под заказ</span>
                <?php else: ?>
                    <span class="badge badge-<?= $totalStock > 10 ? 'success' : ($totalStock > 0 ? 'warning' : 'danger') ?>">
                        <?= $totalStock ?> шт.
                    </span>
                <?php endif; ?>
            </td>
            <td>
                <a href="product_configurations.php?product_id=<?= $p['id'] ?>" class="badge badge-secondary" title="Управление комплектациями">
                    <i class="fas fa-cogs"></i> <?= $configsCount ?>
                </a>
            </td>
            <td>
                <?php if ($waitingQty > 0): ?>
                    <span class="badge badge-warning" title="Заявок в листе ожидания">
                        <i class="fas fa-bell"></i> <?= $waitingQty ?> шт.
                    </span>
                <?php else: ?>
                    <span class="text-muted">—</span>
                <?php endif; ?>
            </td>
            <td>
                <span class="badge badge-<?= ['active'=>'success','inactive'=>'secondary','draft'=>'warning'][$p['status']] ?? 'info' ?>">
                    <?= e($p['status']) ?>
                </span>
            </td>
            <td>
                <a href="product_edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary" title="Редактировать">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="product_configurations.php?product_id=<?= $p['id'] ?>" class="btn btn-sm btn-info" title="Комплектации">
                    <i class="fas fa-cogs"></i>
                </a>
                <a href="product_modifications.php?product_id=<?= $p['id'] ?>" class="btn btn-sm btn-info" title="Модификации">
                    <i class="fas fa-sliders-h"></i>
                </a>
                <a href="product_delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">
                    <i class="fas fa-trash"></i>
                </a>
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

<script>
function setBulkAction(action) {
    var checkedCount = $('.product-checkbox:checked').length;
    if (checkedCount === 0) {
        alert('Пожалуйста, выберите хотя бы один товар');
        return false;
    }
    
    var confirmMessage = '';
    switch(action) {
        case 'delete':
            confirmMessage = 'Вы уверены, что хотите удалить ' + checkedCount + ' товар(ов)? Это действие необратимо!';
            break;
        case 'active':
            confirmMessage = 'Активировать ' + checkedCount + ' товар(ов)?';
            break;
        case 'inactive':
            confirmMessage = 'Деактивировать ' + checkedCount + ' товар(ов)?';
            break;
        default:
            confirmMessage = 'Применить действие к ' + checkedCount + ' товар(ам)?';
    }
    
    if (confirm(confirmMessage)) {
        var selectedIds = [];
        $('.product-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        var form = $('#bulk-form');
        $('#bulk_action').val(action);
        
        form.find('input[name="product_ids[]"]').remove();
        
        $.each(selectedIds, function(i, id) {
            form.append('<input type="hidden" name="product_ids[]" value="' + id + '">');
        });
        
        form.submit();
    }
}

function clearSelection() {
    $('.product-checkbox').prop('checked', false);
    $('#select-all').prop('checked', false);
    $('.product-checkbox').closest('tr').removeClass('selected');
    updateBulkActionsVisibility();
}

$(document).ready(function() {
    updateBulkActionsVisibility();
});
</script>

<?php require_once 'includes/footer.php'; ?>