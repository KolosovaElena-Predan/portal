<?php
$activePage = 'products';
$pageTitle = 'Модификации товаров | Админ-панель';
require_once 'includes/auth_check.php';

$productId = (int)($_GET['product_id'] ?? 0);
$error = '';
$success = '';

// Обработка добавления/редактирования модификации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_mod'])) {
    $group_name = trim($_POST['group_name'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    
    if (!$group_name) {
        $error = 'Название группы обязательно';
    } else {
        try {
            if (isset($_POST['mod_id']) && $_POST['mod_id'] > 0) {
                $stmt = $pdo->prepare("UPDATE product_modifications SET group_name=?, sort_order=?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$group_name, $sort_order, $_POST['mod_id']]);
                $modId = $_POST['mod_id'];
                $success = 'Группа модификаций обновлена!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO product_modifications (product_id, group_name, sort_order, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$productId, $group_name, $sort_order]);
                $modId = $pdo->lastInsertId();
                $success = 'Группа модификаций добавлена!';
            }
            
            // Обработка вариантов
            $allVariants = [];
            if (isset($_POST['variants']) && is_array($_POST['variants'])) {
                foreach ($_POST['variants'] as $idx => $variant) {
                    if (!empty($variant['name'])) {
                        $properties = [];
                        if (!empty($variant['properties']) && is_array($variant['properties'])) {
                            foreach ($variant['properties'] as $prop) {
                                if (!empty($prop['name']) && !empty($prop['price'])) {
                                    $properties[] = [
                                        'name' => $prop['name'],
                                        'price' => (float)$prop['price']
                                    ];
                                }
                            }
                        }
                        
                        $variantData = [
                            'name' => $variant['name'],
                            'price' => (float)($variant['price'] ?? 0),
                            'description' => $variant['description'] ?? '',
                            'properties' => $properties
                        ];
                        
                        $allVariants[] = $variantData;
                    }
                }
                
                if (!empty($allVariants)) {
                    $stmt = $pdo->prepare("UPDATE product_modifications SET options=?, updated_at=NOW() WHERE id=?");
                    $stmt->execute([json_encode($allVariants, JSON_UNESCAPED_UNICODE), $modId]);
                }
            }
        } catch (PDOException $e) {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

// Обработка удаления
if (isset($_POST['delete']) && isset($_POST['id'])) {
    try {
        $pdo->prepare("DELETE FROM product_modifications WHERE id = ?")->execute([$_POST['id']]);
        $success = 'Модификация удалена!';
    } catch (PDOException $e) {
        $error = 'Ошибка удаления: ' . $e->getMessage();
    }
}

// Получаем продукт
$product = null;
if ($productId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
}

// Получаем модификации
$modifications = [];
if ($productId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM product_modifications WHERE product_id = ? ORDER BY sort_order, id");
    $stmt->execute([$productId]);
    $modifications = $stmt->fetchAll();
}

// Для редактирования
$editMod = null;
if (isset($_GET['edit']) && $_GET['edit'] > 0) {
    $stmt = $pdo->prepare("SELECT * FROM product_modifications WHERE id = ? AND product_id = ?");
    $stmt->execute([$_GET['edit'], $productId]);
    $editMod = $stmt->fetch();
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Модификации товара #<?= $productId ?></h1>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
            
            <?php if ($product): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><?= $editMod ? 'Редактировать' : 'Добавить' ?> группу модификаций</h3>
                        </div>
                        <form method="POST" class="card-body" id="modForm">
                            <?php if ($editMod): ?>
                            <input type="hidden" name="mod_id" value="<?= $editMod['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Название группы *</label>
                                        <input type="text" name="group_name" class="form-control" value="<?= e($editMod['group_name'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Порядок сортировки</label>
                                        <input type="number" name="sort_order" class="form-control" value="<?= $editMod['sort_order'] ?? 0 ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-success btn-block" onclick="addVariant()">
                                            <i class="fas fa-plus"></i> Добавить вариант
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h5>Варианты модификации</h5>
                            <div id="variantsContainer">
                                <?php 
                                $variants = $editMod ? json_decode($editMod['options'], true) ?? [] : [];
                                foreach ($variants as $idx => $variant): 
                                ?>
                                <div class="variant-item card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label>Название *</label>
                                                <input type="text" name="variants[<?= $idx ?>][name]" class="form-control" value="<?= e($variant['name']) ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label>Цена (₽)</label>
                                                <input type="number" step="0.01" min="0" name="variants[<?= $idx ?>][price]" class="form-control" value="<?= $variant['price'] ?? 0 ?>">
                                            </div>
                                            <div class="col-md-5">
                                                <label>Описание</label>
                                                <input type="text" name="variants[<?= $idx ?>][description]" class="form-control" value="<?= e($variant['description'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <label>&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-block" onclick="removeVariant(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <label>Дополнительные свойства:</label>
                                            <div class="properties-container">
                                                <?php 
                                                $properties = $variant['properties'] ?? [];
                                                foreach ($properties as $pIdx => $prop): 
                                                ?>
                                                <div class="property-item input-group mb-2">
                                                    <input type="text" name="variants[<?= $idx ?>][properties][<?= $pIdx ?>][name]" class="form-control" placeholder="Название свойства" value="<?= e($prop['name']) ?>">
                                                    <input type="number" step="0.01" min="0" name="variants[<?= $idx ?>][properties][<?= $pIdx ?>][price]" class="form-control" placeholder="Цена" value="<?= $prop['price'] ?? 0 ?>" style="width: 120px;">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-danger" onclick="removeProperty(this)">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="addProperty(this)">
                                                <i class="fas fa-plus"></i> Добавить свойство
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <hr>
                            <button type="submit" name="save_mod" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $editMod ? 'Сохранить' : 'Добавить' ?>
                            </button>
                            <?php if ($editMod): ?>
                            <a href="product_modifications.php?product_id=<?= $productId ?>" class="btn btn-secondary">Отмена</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Список модификаций</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Группа</th>
                                        <th>Варианты</th>
                                        <th>Порядок</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modifications as $mod): ?>
                                    <tr>
                                        <td><strong><?= e($mod['group_name']) ?></strong></td>
                                        <td>
                                            <?php 
                                            $options = json_decode($mod['options'], true) ?? [];
                                            if (!empty($options)):
                                                foreach ($options as $opt): 
                                            ?>
                                            <div class="mb-2">
                                                <small class="badge badge-info d-block">
                                                    <?= e($opt['name']) ?> 
                                                    <span class="text-white">(+<?= number_format($opt['price'], 0, '.', ' ') ?> ₽)</span>
                                                </small>
                                                <?php if (!empty($opt['properties'])): ?>
                                                <small class="text-muted ml-2">
                                                    <?php foreach ($opt['properties'] as $prop): ?>
                                                    <span class="badge badge-light border">
                                                        <?= e($prop['name']) ?>: +<?= number_format($prop['price'], 0, '.', ' ') ?> ₽
                                                    </span>
                                                    <?php endforeach; ?>
                                                </small>
                                                <?php endif; ?>
                                                <?php if (!empty($opt['description'])): ?>
                                                <br><small class="text-muted"><?= e($opt['description']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <?php endforeach; ?>
                                            <?php else: ?>
                                            <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $mod['sort_order'] ?></td>
                                        <td>
                                            <a href="product_modifications.php?product_id=<?= $productId ?>&edit=<?= $mod['id'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить?')">
                                                <input type="hidden" name="delete" value="1">
                                                <input type="hidden" name="id" value="<?= $mod['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <a href="product_edit.php?id=<?= $productId ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Назад к товару
                </a>
            </div>
            <?php else: ?>
            <div class="alert alert-warning">Товар не найден</div>
            <a href="products.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> К списку товаров
            </a>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
let variantIndex = <?= count($variants) ?>;

function addVariant() {
    const container = document.getElementById('variantsContainer');
    const html = `
        <div class="variant-item card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label>Название *</label>
                        <input type="text" name="variants[${variantIndex}][name]" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>Цена (₽)</label>
                        <input type="number" step="0.01" min="0" name="variants[${variantIndex}][price]" class="form-control" value="0">
                    </div>
                    <div class="col-md-5">
                        <label>Описание</label>
                        <input type="text" name="variants[${variantIndex}][description]" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-block" onclick="removeVariant(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="mt-3">
                    <label>Дополнительные свойства:</label>
                    <div class="properties-container"></div>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="addProperty(this)">
                        <i class="fas fa-plus"></i> Добавить свойство
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    variantIndex++;
}

function removeVariant(btn) {
    btn.closest('.variant-item').remove();
}

function addProperty(btn) {
    const container = btn.previousElementSibling;
    const idx = container.querySelectorAll('.property-item').length;
    const variantIdx = btn.closest('.variant-item').querySelector('input[name*="[name]"]').name.match(/\[(\d+)\]/)[1];
    const html = `
        <div class="property-item input-group mb-2">
            <input type="text" name="variants[${variantIdx}][properties][${idx}][name]" class="form-control" placeholder="Название свойства">
            <input type="number" step="0.01" min="0" name="variants[${variantIdx}][properties][${idx}][price]" class="form-control" placeholder="Цена" style="width: 120px;">
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger" onclick="removeProperty(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function removeProperty(btn) {
    btn.closest('.property-item').remove();
}
</script>
<?php require_once 'includes/footer.php'; ?>