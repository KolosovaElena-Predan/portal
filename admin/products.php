<?php
$activePage = 'products';
$pageTitle = 'Товары | Админ-панель';
require_once 'includes/auth_check.php';
$error = '';
$success = '';

$products = $pdo->query("
    SELECT p.*, c.name as category_name,
           (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as images_count
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
")->fetchAll();

$pageScript = '$("#productsTable").DataTable({ "responsive": true, "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"} });';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>
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
                                <th>ID</th>
                                <th>Фото</th>
                                <th>Название</th>
                                <th>Категория</th>
                                <th>Цена</th>
                                <th>Остаток</th>
                                <th>Статус</th>
                                <th>Новинка</th>
                                <th>Слайдер</th>
                                <th>Изображений</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_main = 1 LIMIT 1");
                                    $stmt->execute([$p['id']]);
                                    $mainImg = $stmt->fetch();
                                    if ($mainImg):
                                    ?>
                                    <img src="../mip/<?= e($mainImg['image_url']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                    <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($p['name']) ?></td>
                                <td><span class="badge badge-info"><?= e($p['category_name'] ?? '—') ?></span></td>
                                <td><?= number_format($p['base_price'], 0, '.', ' ') ?> ₽</td>
                                <td>
                                    <span class="badge badge-<?= $p['stock'] > 10 ? 'success' : ($p['stock'] > 0 ? 'warning' : 'danger') ?>">
                                        <?= $p['stock'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= ['active'=>'success','inactive'=>'secondary','draft'=>'warning'][$p['status']] ?? 'info' ?>">
                                        <?= e($p['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $p['is_new'] ? 'success' : 'secondary' ?>">
                                        <?= $p['is_new'] ? '✓' : '—' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $p['is_slider'] ? 'success' : 'secondary' ?>">
                                        <?= $p['is_slider'] ? '✓' : '—' ?>
                                    </span>
                                </td>
                                <td><span class="badge badge-info"><?= $p['images_count'] ?></span></td>
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
                                    <a href="product_delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')" title="Удалить">
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
<?php require_once 'includes/footer.php'; ?>